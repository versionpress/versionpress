<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Version: DEV
Author: VersionPress
Author URI: http://versionpress.net/
License: GPLv2 or later
*/

use VersionPress\Api\VersionPressApi;
use VersionPress\ChangeInfos\PluginChangeInfo;
use VersionPress\ChangeInfos\TranslationChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\VersionPressChangeInfo;
use VersionPress\ChangeInfos\WordPressUpdateChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\WpdbMirrorBridge;
use VersionPress\Database\VpidRepository;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Committer;
use VersionPress\Git\MergeDriverInstaller;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;
use VersionPress\Initialization\WpdbReplacer;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\BugReporter;
use VersionPress\Utils\CompatibilityChecker;
use VersionPress\Utils\CompatibilityResult;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IdUtil;
use VersionPress\Utils\UninstallationUtil;
use VersionPress\VersionPress;

defined('ABSPATH') or die("Direct access not allowed");

require_once(__DIR__ . '/bootstrap.php');

if (defined('WP_CLI') && WP_CLI) {
    require_once(__DIR__ . '/src/Cli/vp.php');
    WP_CLI::add_command('vp', 'VersionPress\Cli\VPCommand');
}

if (defined('VP_MAINTENANCE')) {
    vp_disable_maintenance();
}

if (!VersionPress::isActive() && is_file(VERSIONPRESS_PLUGIN_DIR . '/.abort-initialization')) {
    if (UninstallationUtil::uninstallationShouldRemoveGitRepo()) {
        FileSystem::remove(VP_PROJECT_ROOT . '/.git');
    }

    FileSystem::remove(VP_VPDB_DIR);
    unlink(VERSIONPRESS_PLUGIN_DIR . '/.abort-initialization');
}

//----------------------------------------
// Hooks for VersionPress functionality
//----------------------------------------


if (VersionPress::isActive()) {
    vp_register_hooks();


//----------------------------------
// Replacing wpdb
//----------------------------------
    register_shutdown_function(function () {
        if (!WpdbReplacer::isReplaced() && !defined('VP_DEACTIVATING')) {
            WpdbReplacer::replaceMethods();
        }
    });

//----------------------------------
// Flushing rewrite rules after clone / pull / push
//----------------------------------
    add_action('wp_loaded', function () {
        if (get_transient('vp_flush_rewrite_rules') && !defined('WP_CLI')) {
            require_once(ABSPATH . 'wp-admin/includes/misc.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            flush_rewrite_rules();
            delete_transient('vp_flush_rewrite_rules');
        }
    });
}

//----------------------------------
// Auto-update
//----------------------------------

add_filter('automatic_updates_is_vcs_checkout', function () {
    $forceUpdate = UninstallationUtil::uninstallationShouldRemoveGitRepo(); // first commit was created by VersionPress
    return !$forceUpdate; // 'false' forces the update
});

function vp_register_hooks() {
    global $wpdb, $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    /** @var Mirror $mirror */
    $mirror = $versionPressContainer->resolve(VersionPressServices::MIRROR);
    /** @var DbSchemaInfo $dbSchemaInfo */
    $dbSchemaInfo = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    /** @var VpidRepository $vpidRepository */
    $vpidRepository = $versionPressContainer->resolve(VersionPressServices::VPID_REPOSITORY);
    /** @var WpdbMirrorBridge $wpdbMirrorBridge */
    $wpdbMirrorBridge = $versionPressContainer->resolve(VersionPressServices::WPDB_MIRROR_BRIDGE);
    /** @var StorageFactory $storageFactory */
    $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);

    /**
     *  Hook for saving taxonomies into files
     *  WordPress creates plain INSERT query and executes it using wpdb::query method instead of wpdb::insert.
     *  It's too difficult to parse every INSERT query, that's why the WordPress hook is used.
     */
    add_action('save_post', vp_create_update_post_terms_hook($mirror, $vpidRepository));

    add_filter('update_feedback', function () {
        touch(ABSPATH . 'versionpress.maintenance');
    });
    add_action('_core_updated_successfully', function () use ($committer, $mirror) {
        require(ABSPATH . WPINC . '/version.php'); // load constants (like $wp_version)
        /** @var string $wp_version */
        $changeInfo = new WordPressUpdateChangeInfo($wp_version);
        $committer->forceChangeInfo($changeInfo);

        $mirror->save('option', array('option_name' => 'db_version', 'option_value' => get_option('db_version'))); // We have to re-save the option because WP upgrader uses $wpdb->query()

        if (!WpdbReplacer::isReplaced()) {
            WpdbReplacer::replaceMethods();
        }
    });

    add_action('activated_plugin', function ($pluginName) use ($committer) {
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'activate'));
    });

    add_action('deactivated_plugin', function ($pluginName) use ($committer) {
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'deactivate'));
    });

    add_action('upgrader_process_complete', function ($upgrader, $hook_extra) use ($committer) {
        if ($hook_extra['type'] === 'theme') {
            $themes = (isset($hook_extra['bulk']) && $hook_extra['bulk'] === true) ? $hook_extra['themes'] : array($upgrader->result['destination_name']);
            foreach ($themes as $theme) {
                $themeName = wp_get_theme($theme)->get('Name');
                if ($themeName === $theme && isset($upgrader->skin->api, $upgrader->skin->api->name)) {
                    $themeName =  $upgrader->skin->api->name;
                }

                $action = $hook_extra['action']; // can be "install" or "update", see WP_Upgrader and search for `'hook_extra' =>`
                $committer->forceChangeInfo(new ThemeChangeInfo($theme, $action, $themeName));
            }
        }

        if (!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'update')) return; // handled by different hook

        if (isset($hook_extra['bulk']) && $hook_extra['bulk'] === true) {
            $plugins = $hook_extra['plugins'];
        } else {
            $plugins = array($hook_extra['plugin']);
        }

        foreach ($plugins as $plugin) {
            $committer->forceChangeInfo(new PluginChangeInfo($plugin, 'update'));
        }
    }, 10, 2);

    add_action('added_option', function ($name) use ($wpdb, $mirror) {
        $option = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}options WHERE option_name='$name'", ARRAY_A);
        $mirror->save("option", $option);
    });

    add_filter('upgrader_pre_install', function ($_, $hook_extra) use ($committer) {
        if (!(isset($hook_extra['type']) && $hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;

        $pluginsBeforeInstallation = get_plugins();
        $postInstallHook = function ($_, $hook_extra) use ($pluginsBeforeInstallation, $committer, &$postInstallHook) {
            if (!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;
            wp_cache_delete('plugins', 'plugins');
            $pluginsAfterInstallation = get_plugins();
            $installedPlugin = array_diff_key($pluginsAfterInstallation, $pluginsBeforeInstallation);
            reset($installedPlugin);
            $pluginName = key($installedPlugin);
            $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'install'));
            remove_filter('upgrader_post_install', $postInstallHook);
        };

        add_filter('upgrader_post_install', $postInstallHook, 10, 2);
    }, 10, 2);

    add_filter('upgrader_pre_download', function($reply, $_, $upgrader) use ($committer) {
        if (!isset($upgrader->skin->language_update)) return $reply;
        $languages = get_available_languages();

        $postInstallHook = function ($_, $hook_extra) use ($committer, $languages, &$postInstallHook) {
            require_once(ABSPATH . 'wp-admin/includes/translation-install.php');

            if (!isset($hook_extra['language_update_type'])) return;
            $translations = wp_get_available_translations();

            $type = $hook_extra['language_update_type'];
            $languageCode = $hook_extra['language_update']->language;
            $languageName = isset($translations[$languageCode])
                ? $translations[$languageCode]['native_name']
                : 'English (United States)';

            $name = $type === "core" ? null : $hook_extra['language_update']->slug;

            $action = in_array($languageCode, $languages) ? "update" : "install";
            $committer->forceChangeInfo(new TranslationChangeInfo($action, $languageCode, $languageName, $type, $name));
            remove_filter('upgrader_post_install', $postInstallHook);
        };

        add_filter('upgrader_post_install', $postInstallHook, 10, 2);
        return false;
    }, 10, 3);

    add_action('switch_theme', function () use ($committer) {
        if (defined('WP_CLI') && WP_CLI) {
            wp_remote_get(admin_url()); //
        } else {
            $committer->disableCommit(); // the change will be committed on next load
        }
    });

    add_action('after_switch_theme', function () use ($committer) {
        $theme = wp_get_theme();
        $stylesheet = $theme->get_stylesheet();
        $themeName = $theme->get('Name');

        $committer->forceChangeInfo(new ThemeChangeInfo($stylesheet, 'switch', $themeName));
    });

    add_action('untrashed_post_comments', function ($postId) use ($wpdb, $dbSchemaInfo, $wpdbMirrorBridge) {
        $commentsTable = $dbSchemaInfo->getPrefixedTableName("comment");
        $commentStatusSql = "select comment_ID, comment_approved from {$commentsTable} where comment_post_ID = {$postId}";
        $comments = $wpdb->get_results($commentStatusSql, ARRAY_A);

        foreach ($comments as $comment) {
            $wpdbMirrorBridge->update($commentsTable,
                array("comment_approved" => $comment["comment_approved"]),
                array("comment_ID" => $comment["comment_ID"]));
        }
    });

    add_action('delete_post_meta', function ($metaIds) use ($wpdbMirrorBridge, $dbSchemaInfo) {
        $idColumnName = $dbSchemaInfo->getEntityInfo("postmeta")->idColumnName;
        foreach ($metaIds as $metaId) {
            $wpdbMirrorBridge->delete($dbSchemaInfo->getPrefixedTableName("postmeta"), array($idColumnName => $metaId));
        }
    });

    add_action('delete_user_meta', function ($metaIds) use ($wpdbMirrorBridge, $dbSchemaInfo) {
        $idColumnName = $dbSchemaInfo->getEntityInfo("usermeta")->idColumnName;
        foreach ($metaIds as $metaId) {
            $wpdbMirrorBridge->delete($dbSchemaInfo->getPrefixedTableName("usermeta"), array($idColumnName => $metaId));
        }
    });

    add_action('wp_ajax_save-widget', function () use ($committer) {
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['delete_widget']) && $_POST['delete_widget']) {
            $committer->postponeCommit('widgets');
        }
    }, 0); // zero because the default WP action with priority 1 calls wp_die()

    function _vp_get_language_name_by_code($code) {
        require_once(ABSPATH . 'wp-admin/includes/translation-install.php');

        $translations = wp_get_available_translations();
        return isset($translations[$code])
            ? $translations[$code]['native_name']
            : 'English (United States)';
    }

    add_action('add_option_WPLANG', function ($option, $value) use ($committer) {
        $defaultLanguage = defined('WPLANG') ? WPLANG : '';
        if ($value === $defaultLanguage) {
            return; // It's just submitted settings form without changing language
        }

        $languageName = _vp_get_language_name_by_code($value);
        $committer->forceChangeInfo(new TranslationChangeInfo("activate", $value, $languageName));
    }, 10, 2);

    add_action('update_option_WPLANG', function ($oldValue, $newValue) use ($committer) {
        $languageName = _vp_get_language_name_by_code($newValue);
        $committer->forceChangeInfo(new TranslationChangeInfo("activate", $newValue, $languageName));
    }, 10, 2);

    add_action('wp_update_nav_menu_item', function($menu_id, $menu_item_db_id) use ($committer) {
        $key = 'menu-item-' . $menu_item_db_id;
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && $_POST['action'] === 'add-menu-item') {
            $committer->postponeCommit($key);
            $committer->commit();
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
            $committer->usePostponedChangeInfos($key);
        }
        if (!defined('DOING_AJAX')) {
            global $versionPressContainer;
            /** @var Mirror $mirror */
            $mirror = $versionPressContainer->resolve(VersionPressServices::MIRROR);
            $vpidRepository = $versionPressContainer->resolve(VersionPressServices::VPID_REPOSITORY);
            $func = vp_create_update_post_terms_hook($mirror, $vpidRepository);
            $func($menu_item_db_id);
        }
    }, 10, 2);

    add_action('pre_delete_term', function ($termId, $taxonomy) use ($committer, $vpidRepository) {
        $termVpid = $vpidRepository->getVpidForEntity('term', $termId);
        $term = get_term($termId, $taxonomy);
        $committer->forceChangeInfo(new \VersionPress\ChangeInfos\TermChangeInfo('delete', $termVpid, $term->name, $taxonomy));
    }, 10, 2);

    add_action('set_object_terms', vp_create_update_post_terms_hook($mirror, $vpidRepository));

    add_filter('plugin_install_action_links', function ($links, $plugin) {
        $compatibility = CompatibilityChecker::testCompatibilityBySlug($plugin['slug']);
        if ($compatibility === CompatibilityResult::COMPATIBLE) {
            $cssClass = 'vp-compatible';
            $compatibilityAdjective = 'Compatible';
        } elseif ($compatibility === CompatibilityResult::INCOMPATIBLE) {
            $cssClass = 'vp-incompatible';
            $compatibilityAdjective = '<a href="http://docs.versionpress.net/en/integrations/plugins" target="_blank" title="This plugin is not compatible with VersionPress. These plugins will not work correctly when used together.">Incompatible</a>';
        } else {
            $cssClass = 'vp-untested';
            $compatibilityAdjective = '<a href="http://docs.versionpress.net/en/integrations/plugins" target="_blank" title="This plugin was not yet tested with VersionPress. Some functionality may not work as intended.">Untested</a>';
        }

        $compatibilityNotice = '<span class="vp-compatibility %s" data-plugin-name="%s"><strong>%s</strong> with VersionPress</span>';
        $links[] = sprintf($compatibilityNotice, $cssClass, $plugin['name'], $compatibilityAdjective);

        return $links;
    }, 10, 2);

    add_filter('plugin_row_meta', function ($plugin_meta, $plugin_file, $plugin_data, $status) {
        if ($status === "dropins") {
            return $plugin_meta;
        }
        $compatibility = CompatibilityChecker::testCompatibilityByPluginFile($plugin_file);
        if ($compatibility === CompatibilityResult::COMPATIBLE) {
            $cssClass = 'vp-compatible';
            $compatibilityAdjective = 'Compatible';
        } elseif ($compatibility === CompatibilityResult::INCOMPATIBLE) {
            $cssClass = 'vp-incompatible';
            $compatibilityAdjective = '<a href="http://docs.versionpress.net/en/integrations/plugins" target="_blank" title="This plugin is not compatible with VersionPress. These plugins will not work correctly when used together.">Incompatible</a>';
        } elseif ($compatibility === CompatibilityResult::UNTESTED) {
            $cssClass = 'vp-untested';
            $compatibilityAdjective = '<a href="http://docs.versionpress.net/en/integrations/plugins" target="_blank" title="This plugin was not yet tested with VersionPress. Some functionality may not work as intended.">Untested</a>';
        } else {
            return $plugin_meta;
        }

        $compatibilityNotice = '<span class="vp-compatibility %s" data-plugin-name="%s"><strong>%s</strong> with VersionPress</span>';
        $plugin_meta[] = sprintf($compatibilityNotice, $cssClass, $plugin_data['Name'], $compatibilityAdjective);

        return $plugin_meta;
    }, 10, 4);

    add_filter('plugin_action_links', function ($actions, $plugin_file) {
        $compatibility = CompatibilityChecker::testCompatibilityByPluginFile($plugin_file);

        if (isset($actions['activate'])) {
            if ($compatibility === CompatibilityResult::UNTESTED) {
                $actions['activate'] = "<span class=\"vp-plugin-list vp-untested\">$actions[activate]</span>";
            } elseif ($compatibility === CompatibilityResult::INCOMPATIBLE) {
                $actions['activate'] = "<span class=\"vp-plugin-list vp-incompatible\">$actions[activate]</span>";
            }
        }
        return $actions;
    }, 10, 2);

    add_action('vp_revert', function () {
        // We have to flush the rewrite rules in the next request, because
        // in the current one the changed rewrite rules are not yet effective.
        set_transient('vp_flush_rewrite_rules', 1);
        vp_flush_regenerable_options();
    });

    add_action('pre_delete_term', function ($term, $taxonomy) use ($wpdb, $wpdbMirrorBridge) {
        if (!is_taxonomy_hierarchical($taxonomy)) {
            return;
        }

        $term = get_term($term, $taxonomy);
        if (is_wp_error($term)) {
            return;
        }

        $wpdbMirrorBridge->update($wpdb->term_taxonomy, array('parent' => $term->parent), array('parent' => $term->term_id));
    }, 10, 2);

    add_action('before_delete_post', function ($postId) use ($wpdb) {
        // Fixing bug in WP (#34803) and WP-CLI (#2246)
        $post = get_post($postId);
        if ( !is_wp_error($post) && $post->post_type === 'nav_menu_item' ) {
            $newParent = get_post_meta($post->ID, '_menu_item_menu_item_parent', true);
            $wpdb->update($wpdb->postmeta,
                array('meta_value' => $newParent),
                array('meta_key' => '_menu_item_menu_item_parent', 'meta_value' => $post->ID)
            );
        }
    });

    //----------------------------------------
    // URL and WP-CLI "hooks"
    //----------------------------------------

    $requestDetector = new \VersionPress\Utils\RequestDetector();

    if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] === 'widgets-order') {
        $committer->usePostponedChangeInfos('widgets');
    }

    if ($requestDetector->isThemeDeleteRequest()) {
        $themeIds = $requestDetector->getThemeStylesheets();
        foreach ($themeIds as $themeId) {
            $committer->forceChangeInfo(new ThemeChangeInfo($themeId, 'delete'));
        }
    }

    if ($requestDetector->isPluginDeleteRequest()) {
        $plugins = $requestDetector->getPluginNames();
        foreach ($plugins as $plugin) {
            $committer->forceChangeInfo(new PluginChangeInfo($plugin, 'delete'));
        }
    }

    if ($requestDetector->isCoreLanguageUninstallRequest()) {
        $languageCode = $requestDetector->getLanguageCode();
        $translations = wp_get_available_translations();
        $languageName = isset($translations[$languageCode])
            ? $translations[$languageCode]['native_name']
            : 'English (United States)';

        $committer->forceChangeInfo(new TranslationChangeInfo('uninstall', $languageCode, $languageName, 'core'));
    }

    if (basename($_SERVER['PHP_SELF']) === 'theme-editor.php' && isset($_GET['updated']) && $_GET['updated'] === 'true') {
        $committer->forceChangeInfo(new ThemeChangeInfo($_GET['theme'], 'edit'));
    }

    if (basename($_SERVER['PHP_SELF']) === 'plugin-editor.php' &&
        ((isset($_POST['action']) && $_POST['action'] === 'update') || isset($_GET['liveupdate'])
        )
    ) {
        $committer->disableCommit();
    }

    if (basename($_SERVER['PHP_SELF']) === 'plugin-editor.php' && isset($_GET['a']) && $_GET['a'] === 'te') {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $editedFile = $_GET['file'];
        $editedFilePathParts = preg_split("~[/\\\]~", $editedFile);
        $plugins = array_keys(get_plugins());
        $bestRank = 0;
        $bestMatch = "";

        foreach ($plugins as $plugin) {
            $rank = 0;
            $pluginPathParts = preg_split("~[/\\\]~", $plugin);
            $maxEqualParts = min(count($editedFilePathParts), count($pluginPathParts));

            for ($part = 0; $part < $maxEqualParts; $part++) {
                if ($editedFilePathParts[$part] !== $pluginPathParts[$part]) break;
                $rank += 1;
            }

            if ($rank > $bestRank) {
                $bestRank = $rank;
                $bestMatch = $plugin;
            }
        }

        $committer->forceChangeInfo(new PluginChangeInfo($bestMatch, 'edit'));
    }

    add_filter('cron_schedules', function ($schedules) use ($dbSchemaInfo) {
        $intervals = $dbSchemaInfo->getIntervalsForFrequentlyWrittenEntities();

        foreach ($intervals as $interval) {
            if (isset($schedules[$interval])) {
                continue;
            }

            $seconds = strtotime($interval, 0);
            $schedules[$interval] = array(
                'interval' => $seconds,
                'display' => $interval
            );
        }

        return $schedules;
    });

    $r = $dbSchemaInfo->getRulesForFrequentlyWrittenEntities();
    $groupedByInterval = array();
    foreach ($r as $entityName => $rules) {
        foreach ($rules as $rule) {
            $groupedByInterval[$rule['interval']][$entityName][] = $rule;
        }
    }

    foreach ($groupedByInterval as $interval => $allRulesInInterval) {
        $actionName = "vp_commit_frequently_written_entities_$interval";
        if (!wp_next_scheduled($actionName)) {
            wp_schedule_event(time(), $interval, $actionName);
        }

        add_action($actionName, function () use ($allRulesInInterval) {
            global $versionPressContainer;
            $dbSchemaInfo = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
            $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
            $wpdb = $versionPressContainer->resolve(VersionPressServices::WPDB);
            $wpdbMirrorBridge = $versionPressContainer->resolve(VersionPressServices::WPDB_MIRROR_BRIDGE);

            foreach ($allRulesInInterval as $entityName => $rulesWithInterval) {
                $storageFactory->getStorage($entityName)->ignoreFrequentlyWrittenEntities = false;

                $table = $dbSchemaInfo->getPrefixedTableName($entityName);
                foreach ($rulesWithInterval as $ruleAndInterval) {
                    $restriction = \VersionPress\Utils\QueryLanguageUtils::createSqlRestrictionFromRule($ruleAndInterval['rule']);
                    $sql = "SELECT * FROM $table WHERE $restriction";

                    $results = $wpdb->get_results($sql, ARRAY_A);
                    foreach ($results as $data) {
                        $wpdbMirrorBridge->update($table, $data, $data);
                    }
                }
            }
        });
    }

    register_shutdown_function(array($committer, 'commit'));
}

function vp_create_update_post_terms_hook(Mirror $mirror, VpidRepository $vpidRepository) {

    return function ($postId) use ($mirror, $vpidRepository) {
        /** @var array $post */
        $post = get_post($postId, ARRAY_A);

        if (!$mirror->shouldBeSaved('post', $post)) {
            return;
        }

        $postType = $post['post_type'];
        $taxonomies = get_object_taxonomies($postType);

        $postVpId = $vpidRepository->getVpidForEntity('post', $postId);

        $postUpdateData = array('vp_id' => $postVpId, 'vp_term_taxonomy' => array());

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);
            if ($terms) {
                $referencedTaxonomies = array_map(function ($term) use ($vpidRepository) {
                    return $vpidRepository->getVpidForEntity('term_taxonomy', $term->term_taxonomy_id);
                }, $terms);

                $postUpdateData['vp_term_taxonomy'] = array_merge($postUpdateData['vp_term_taxonomy'], $referencedTaxonomies);
            }
        }

        if (count($taxonomies) > 0) {
            $mirror->save("post", $postUpdateData);
        }
    };
}


//----------------------------------
// Activation and deactivation
//----------------------------------

register_activation_hook(__FILE__, 'vp_activate');
register_deactivation_hook(__FILE__, 'vp_deactivate');
add_action('admin_post_cancel_deactivation', 'vp_admin_post_cancel_deactivation');
add_action('admin_post_confirm_deactivation', 'vp_admin_post_confirm_deactivation');
add_action('send_headers', 'vp_send_headers');

if (get_transient('vp_just_activated')) {
    add_filter('gettext', 'vp_gettext_filter_plugin_activated', 10, 3);
}
// uninstallation is handled in uninstall.php

/**
 * We hook to `gettext` filter to update the "plugin activated" message with a link
 * to VP initializer. See [WP-66].
 *
 * @param string $translation Translated text.
 * @param string $text Text to translate.
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return string
 */
function vp_gettext_filter_plugin_activated($translation, $text, $domain) {
    if ($text == 'Plugin <strong>activated</strong>.' && get_transient('vp_just_activated')) {
        delete_transient('vp_just_activated');
        return 'VersionPress activated. <strong><a href="' . menu_page_url('versionpress', false) . '" style="text-decoration: underline; font-size: 1.03em;">Continue here</a></strong> to start tracking the site.';
    } else {
        return $translation;
    }
}

/**
 * Activates the plugin from a WordPress point of view. Note that for VersionPress
 * to become fully active, the Initializer needs to be run.
 *
 * @see Initializer
 */
function vp_activate() {
    set_transient('vp_just_activated', '1', 10);
}

/**
 * Deactivation is a two-step process with a warning screen. See
 * `vp_admin_post_cancel_deactivation()` and `vp_admin_post_confirm_deactivation()`
 *
 * @see vp_admin_post_confirm_deactivation()
 * @see vp_admin_post_cancel_deactivation()
 */
function vp_deactivate() {
    if (defined('WP_CLI') || !VersionPress::isActive()) {
        vp_admin_post_confirm_deactivation();
    } else {
        wp_safe_redirect(admin_url('admin.php?page=versionpress/admin/deactivate.php'));
        die();
    }
}

/**
 * Handles a situation where user canceled the deactivation
 */
function vp_admin_post_cancel_deactivation() {
    wp_safe_redirect(admin_url('plugins.php'));
    exit();
}

/**
 * Most of the actual deactivation work is done here. Called either as a response
 * to the user confirming the deactivation on `?page=versionpress/admin/deactivate.php`
 * or is called directly from vp_deactivate() if the confirmation screen was not necessary.
 */
function vp_admin_post_confirm_deactivation() {

    vp_verify_nonce('vp_deactivation');
    vp_check_permissions();

    define('VP_DEACTIVATING', true);

    if (WpdbReplacer::isReplaced()) {
        WpdbReplacer::restoreOriginal();
    }

    if (file_exists(VERSIONPRESS_ACTIVATION_FILE)) {
        FileSystem::remove(VERSIONPRESS_ACTIVATION_FILE);
    }

    FileSystem::remove(VP_VPDB_DIR);


    global $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    $committer->forceChangeInfo(new VersionPressChangeInfo("deactivate"));

    /** @var WpdbMirrorBridge $wpdbMirrorBridge */
    $wpdbMirrorBridge = $versionPressContainer->resolve(VersionPressServices::WPDB_MIRROR_BRIDGE);
    $wpdbMirrorBridge->disable();

    MergeDriverInstaller::uninstallMergeDriver();

    global $wpdb;

    $table_prefix = $wpdb->prefix;

    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";

    $vpOptionsReflection = new ReflectionClass('VersionPress\Initialization\VersionPressOptions');
    $usermetaToDelete = array_values($vpOptionsReflection->getConstants());
    $queryRestriction = '"' . join('", "', $usermetaToDelete) . '"';

    $queries[] = "DELETE FROM `{$table_prefix}usermeta` WHERE meta_key IN ({$queryRestriction})";

    foreach ($queries as $query) {
        $wpdb->query($query);
    }


    delete_option('vp_rest_api_plugin_version');
    deactivate_plugins("versionpress/versionpress.php", true);

    if (defined('WP_ADMIN')) {
        wp_safe_redirect(admin_url("plugins.php"));
        exit();
    }

}

function vp_send_headers() {
    if (isset($_GET['init_versionpress']) && !VersionPress::isActive()) {
        vp_disable_output_buffering();
    }
}

add_action('admin_post_vp_send_bug_report', 'vp_send_bug_report');

function vp_send_bug_report() {
    $email = $_POST['email'];
    $description = $_POST['description'];

    $bugReporter = new BugReporter('http://versionpress.net/report-problem');
    $reportedSuccessfully = $bugReporter->reportBug($email, $description);

    $result = $reportedSuccessfully ? "ok" : "err";
    wp_safe_redirect(add_query_arg('bug-report', $result, menu_page_url('versionpress', false)));
    exit();
}

add_action('admin_notices', 'vp_activation_nag', 4 /* WP update nag is 3, we are just one step less important :) */);

/**
 * Displays the activation nag
 */
function vp_activation_nag() {

    if (VersionPress::isActive() ||
        get_current_screen()->id == "toplevel_page_versionpress" ||
        get_current_screen()->id == "versionpress/admin/index" ||
        get_current_screen()->id == "versionpress/admin/deactivate"
    ) {
        return;
    }

    if (get_transient('vp_just_activated')) {
        return;
    }


    echo "<div class='update-nag vp-activation-nag'>VersionPress is installed but not yet tracking this site. <a href='" . menu_page_url('versionpress', false) . "'>Please finish the activation.</a></div>";

}

add_action("after_plugin_row_versionpress/versionpress.php", 'vp_display_activation_notice', 10, 2);

function vp_display_activation_notice($file, $plugin_data) {
    if (VersionPress::isActive()) {
        return;
    }

    $wp_list_table = _get_list_table('WP_Plugins_List_Table');
    $activationUrl = menu_page_url('versionpress', false);
    echo '<tr class="plugin-update-tr vp-plugin-update-tr updated"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="vp-plugin-update plugin-update colspanchange"><div class="update-message vp-update-message">';
    echo 'VersionPress is installed but not yet tracking this site. <a href="' . $activationUrl . '">Please finish the activation.</a>';
    echo '</div></td></tr>';
}


add_filter('wp_insert_post_data', 'vp_generate_post_guid', '99', 2);
/**
 * Creates random GUID that is not based on URL
 *
 * @param array $data Sanitized post data
 * @param array $postarr Raw post data
 * @return array
 */
function vp_generate_post_guid($data, $postarr) {
    if (!VersionPress::isActive()) {
        return $data;
    }

    if (empty($postarr['ID'])) { // it's insert not update
        $protocol = is_ssl() ? 'https://' : 'http://';
        $data['guid'] = $protocol . IdUtil::newUuid();
    }

    return $data;
}


//----------------------------------
// Menu
//----------------------------------


add_action('admin_menu', 'vp_admin_menu');

function vp_admin_menu() {
    add_menu_page(
        'VersionPress',
        'VersionPress',
        'manage_options',
        'versionpress',
        'versionpress_page',
        null,
        0.001234987
    );

    // Support for PHP files that should not appear in the menu but should still be accessible via URL
    // like `/wp-admin/admin.php?page=versionpress/admin/xyz.php`
    //
    // We need to add it to  $_registered_pages, see e.g. http://blog.wpessence.com/wordpress-admin-page-without-menu-item/

    $directAccessPages = array(
        'deactivate.php',
        'system-info.php',
        'undo.php',
        'index.php'
    );

    global $_registered_pages;
    foreach ($directAccessPages as $directAccessPage) {
        $menu_slug = plugin_basename("versionpress/admin/$directAccessPage");
        $hookname = get_plugin_page_hookname($menu_slug, '');
        $_registered_pages[$hookname] = true;
    }

}

function versionpress_page() {
    require_once(VERSIONPRESS_PLUGIN_DIR . '/admin/index.php');
}

add_action('admin_action_vp_show_undo_confirm', 'vp_show_undo_confirm');

function vp_show_undo_confirm() {
    if (vp_is_ajax()) {
        require_once(VERSIONPRESS_PLUGIN_DIR . '/admin/undo.php');
    } else {
        wp_safe_redirect(admin_url('admin.php?page=versionpress/admin/undo.php&method=' . $_GET['method'] . '&commit=' . $_GET['commit']));
        exit();
    }
}

add_action('admin_action_vp_undo', 'vp_undo');

function vp_undo() {
    _vp_revert('undo');
}

add_action('admin_action_vp_rollback', 'vp_rollback');

function vp_rollback() {
    _vp_revert('rollback');
}

function _vp_revert($reverterMethod) {
    global $versionPressContainer;

    vp_verify_nonce('vp_revert');
    vp_check_permissions();
    
    $commitHash = $_GET['commit'];
    
    if (!preg_match('/^[0-9a-f]+$/', $commitHash)) {
        exit();
    }

        /** @var Reverter $reverter */
    $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

    vp_enable_maintenance();
    $revertStatus = call_user_func(array($reverter, $reverterMethod), $commitHash);
    vp_disable_maintenance();
    $adminPage = menu_page_url('versionpress', false);

    if ($revertStatus !== RevertStatus::OK) {
        wp_safe_redirect(add_query_arg('error', $revertStatus, $adminPage));
    } else {
        wp_safe_redirect($adminPage);
    }
    exit();
}

if (VersionPress::isActive()) {
    add_action('admin_bar_menu', 'vp_admin_bar_warning');
}

function vp_admin_bar_warning(WP_Admin_Bar $adminBar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    $adminBarText = "<span style=\"color:#FF8800;font-weight:bold\">VersionPress EAP running</span>";
    $popoverTitle = "Note";
    $popoverText = "<p style='margin-top: 5px;'>You are running <strong>VersionPress " . VersionPress::getVersion() . "</strong> which is an <strong style='font-size: 1.15em;'>EAP release</strong>. Please understand that EAP releases are early versions of the software and as such might not fully support certain workflows, 3<sup>rd</sup> party plugins, hosts etc.<br /><br /><strong>We recommend that you keep a safe backup of the site at all times</strong></p>";
    $popoverText .= "<p><a href='http://docs.versionpress.net/en/release-notes' target='_blank'>Learn more about VersionPress releases</a></p>";

    $adminBar->add_node(array(
        'id' => 'vp-running',
        'title' => "<a href='#' class='ab-item' id='vp-warning'>$adminBarText</a>
            <script>
            var warning = jQuery('#vp-warning');
            var customPopoverClass = \"versionpress-alpha\"; // used to identify the popover later

            warning.webuiPopover({title:\"$popoverTitle\", content: \"$popoverText\", closeable: true, style: customPopoverClass, width:450});
            </script>",
        'parent' => 'top-secondary'
    ));
}

//----------------------------------
// AJAX handling
//----------------------------------

add_action('wp_ajax_hide_vp_welcome_panel', 'vp_ajax_hide_vp_welcome_panel');

function vp_ajax_hide_vp_welcome_panel() {
    update_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, "0");
    die(); // this is required to return a proper result
}

add_action('wp_ajax_vp_show_undo_confirm', 'vp_show_undo_confirm');

//----------------------------------
// CSS & JS
//----------------------------------

add_action('admin_enqueue_scripts', 'vp_enqueue_styles_and_scripts');
add_action('wp_enqueue_scripts', 'vp_enqueue_styles_and_scripts');
function vp_enqueue_styles_and_scripts() {
    if (is_admin_bar_showing()) {
        wp_enqueue_style('versionpress_popover_style', plugins_url('admin/public/css/jquery.webui-popover.min.css', __FILE__));
        wp_enqueue_style('versionpress_popover_custom_style', plugins_url('admin/public/css/popover-custom.css', __FILE__));

        wp_enqueue_script('jquery');
        wp_enqueue_script('versionpress_popover_script', plugins_url('admin/public/js/jquery.webui-popover.min.js', __FILE__), 'jquery');
    }
}

add_action('admin_enqueue_scripts', 'vp_enqueue_admin_styles_and_scripts');
function vp_enqueue_admin_styles_and_scripts() {
    wp_enqueue_style('versionpress_admin_style', plugins_url('admin/public/css/style.css', __FILE__));
    wp_enqueue_style('versionpress_admin_icons', plugins_url('admin/public/icons/style.css', __FILE__));

    wp_enqueue_script('versionpress_admin_script', plugins_url('admin/public/js/vp-admin.js', __FILE__));
}

//---------------------------------
// API
//---------------------------------
require("src/Api/BundledWpApi/rest-api.php");

add_action('rest_api_init', 'versionpress_api_init');
function versionpress_api_init() {
    global $versionPressContainer;
    $gitRepository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
    $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);
    $vpConfig = $versionPressContainer->resolve(VersionPressServices::VP_CONFIGURATION);
    $synchronizationProcess = $versionPressContainer->resolve(VersionPressServices::SYNCHRONIZATION_PROCESS);

    $vpApi = new VersionPressApi($gitRepository, $reverter, $vpConfig, $synchronizationProcess);
    $vpApi->register_routes();
}
