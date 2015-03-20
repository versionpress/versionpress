<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Version: 1.0-rc3
Author: VersionPress
Author URI: http://versionpress.net/
License: GPLv2 or later
*/

use Tracy\Debugger;
use VersionPress\ChangeInfos\PluginChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\VersionPressChangeInfo;
use VersionPress\ChangeInfos\WordPressUpdateChangeInfo;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\MirroringDatabase;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\Reverter;
use VersionPress\Git\RevertStatus;
use VersionPress\Initialization\VersionPressOptions;
use VersionPress\Storages\Mirror;
use VersionPress\Utils\BugReporter;
use VersionPress\Utils\FileSystem;
use VersionPress\Utils\IdUtil;
use VersionPress\VersionPress;

defined('ABSPATH') or die("Direct access not allowed");

require_once(__DIR__ . '/bootstrap.php');

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('vp', 'VersionPress\Cli\VPCommand');
}

if (defined('VP_MAINTENANCE')) {
    vp_disable_maintenance();
}

//----------------------------------------
// Hooks for VersionPress functionality
//----------------------------------------


if (VersionPress::isActive()) {
    vp_register_hooks();
}

function vp_register_hooks() {
    /** @var MirroringDatabase $wpdb */
    global $wpdb, $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    /** @var Mirror $mirror */
    $mirror = $versionPressContainer->resolve(VersionPressServices::MIRROR);
    /** @var DbSchemaInfo $dbSchemaInfo */
    $dbSchemaInfo = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);

    /**
     *  Hook for saving taxonomies into files
     *  WordPress creates plain INSERT query and executes it using wpdb::query method instead of wpdb::insert.
     *  It's too difficult to parse every INSERT query, that's why the WordPress hook is used.
     */
    add_action('save_post', createUpdatePostTermsHook($mirror, $wpdb));

    add_filter('update_feedback', function () {
        touch(get_home_path() . 'versionpress.maintenance');
    });
    add_action('_core_updated_successfully', function () use ($committer) {
        require(ABSPATH . 'wp-includes/version.php'); // load constants (like $wp_version)
        /** @var string $wp_version */
        $changeInfo = new WordPressUpdateChangeInfo($wp_version);
        $committer->forceChangeInfo($changeInfo);
    });

    add_action('activated_plugin', function ($pluginName) use ($committer) {
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'activate'));
    });

    add_action('deactivated_plugin', function ($pluginName) use ($committer) {
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'deactivate'));
    });

    add_action('upgrader_process_complete', function ($upgrader, $hook_extra) use ($committer) {
        if ($hook_extra['type'] === 'theme') {
            $themeId = $upgrader->result['destination_name'];
            $themeName = isset($upgrader->skin->api, $upgrader->skin->api->name) ? $upgrader->skin->api->name : wp_get_theme($themeId)->get('Name');
            $action = $hook_extra['action']; // can be "install" or "update", see WP_Upgrader and search for `'hook_extra' =>`
            $committer->forceChangeInfo(new ThemeChangeInfo($themeId, $action, $themeName));
        }

        if (!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'update')) return; // handled by different hook
        $pluginName = $hook_extra['plugin'];
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'update'));
    }, 10, 2);

    add_action('added_option', function ($name) use ($wpdb, $mirror) {
        $option = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}options WHERE option_name='$name'", ARRAY_A);
        $mirror->save("option", $option);
    });

    add_filter('upgrader_pre_install', function ($_, $hook_extra) use ($committer) {
        if (!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;
        $pluginsBeforeInstallation = get_plugins();
        add_filter('upgrader_post_install', function ($_, $hook_extra) use ($pluginsBeforeInstallation, $committer) {
            if (!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;
            wp_cache_delete('plugins', 'plugins');
            $pluginsAfterInstallation = get_plugins();
            $installedPlugin = array_diff_key($pluginsAfterInstallation, $pluginsBeforeInstallation);
            reset($installedPlugin);
            $pluginName = key($installedPlugin);
            $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'install'));
        }, 10, 2);
    }, 10, 2);

    add_action('switch_theme', function () use ($committer) {
        if (defined('WP_CLI') && WP_CLI) {
            file_get_contents(admin_url()); //
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

    add_action('customize_save_after', function ($customizeManager) use ($committer) {
        /** @var WP_Customize_Manager $customizeManager */
        $stylesheet = $customizeManager->theme()->get_stylesheet();
        $committer->forceChangeInfo(new ThemeChangeInfo($stylesheet, 'customize'));
        register_shutdown_function(function () {
            wp_remote_get(admin_url("admin.php"));
        });
    });

    add_action('untrashed_post_comments', function ($postId) use ($wpdb, $dbSchemaInfo) {
        $commentsTable = $dbSchemaInfo->getPrefixedTableName("comment");
        $commentStatusSql = "select comment_ID, comment_approved from {$commentsTable} where comment_post_ID = {$postId}";
        $comments = $wpdb->get_results($commentStatusSql, ARRAY_A);

        foreach ($comments as $comment) {
            $wpdb->update($commentsTable,
                array("comment_approved" => $comment["comment_approved"]),
                array("comment_ID" => $comment["comment_ID"]), null, null, false);
        }
    });

    add_action('delete_post_meta', function ($metaIds) use ($wpdb, $dbSchemaInfo) {
        $idColumnName = $dbSchemaInfo->getEntityInfo("postmeta")->idColumnName;
        foreach ($metaIds as $metaId) {
            $wpdb->delete($dbSchemaInfo->getPrefixedTableName("postmeta"), array($idColumnName => $metaId), null, false);
        }
    });

    add_action('delete_user_meta', function ($metaIds) use ($wpdb, $dbSchemaInfo) {
        $idColumnName = $dbSchemaInfo->getEntityInfo("usermeta")->idColumnName;
        foreach ($metaIds as $metaId) {
            $wpdb->delete($dbSchemaInfo->getPrefixedTableName("usermeta"), array($idColumnName => $metaId), null, false);
        }
    });

    add_action('wp_ajax_save-widget', function () use ($committer) {
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['delete_widget']) && $_POST['delete_widget']) {
            $committer->postponeCommit('widgets');
        }
    }, 0); // zero because the default WP action with priority 1 calls wp_die()

    add_action('permalink_structure_changed', function () use ($committer) {
        $committer->postponeCommit('permalinks');
    });

    add_action('update_option', function ($option) use ($committer) {
       if ($option === 'rewrite_rules') {
           $committer->usePostponedChangeInfos('permalinks');
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
        $themeId = $requestDetector->getThemeStylesheet();
        $committer->forceChangeInfo(new ThemeChangeInfo($themeId, 'delete'));
    }

    if ($requestDetector->isPluginDeleteRequest()) {
        $plugins = $requestDetector->getPluginNames();
        foreach ($plugins as $plugin) {
            $committer->forceChangeInfo(new PluginChangeInfo($plugin, 'delete'));
        }
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

    register_shutdown_function(array($committer, 'commit'));
}

function createUpdatePostTermsHook(Mirror $mirror, wpdb $wpdb) {

    return function ($postId) use ($mirror, $wpdb) {
        /** @var array $post */
        $post = get_post($postId, ARRAY_A);

        if (!$mirror->shouldBeSaved('post', $post)) {
            return;
        }

        $postType = $post['post_type'];
        $taxonomies = get_object_taxonomies($postType);

        $vpIdTableName = $wpdb->prefix . 'vp_id';

        $postVpId = $wpdb->get_var("SELECT HEX(vp_id) FROM $vpIdTableName WHERE id = $postId AND `table` = 'posts'");

        $postUpdateData = array('vp_id' => $postVpId, 'vp_term_taxonomy' => array());

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);
            if ($terms) {
                $referencedTaxonomies = array_map(function ($term) use ($wpdb, $vpIdTableName) {
                    return $wpdb->get_var("SELECT HEX(vp_id) FROM $vpIdTableName WHERE id = {$term->term_taxonomy_id} AND `table` = 'term_taxonomy'");
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
        return 'VersionPress activated. <strong><a href="' . admin_url('admin.php?page=versionpress/admin/index.php') . '" style="text-decoration: underline; font-size: 1.03em;">Continue here</a></strong> to start tracking the site.';
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
        wp_redirect(admin_url('admin.php?page=versionpress/admin/deactivate.php'));
        die();
    }
}

/**
 * Handles a situation where user canceled the deactivation
 */
function vp_admin_post_cancel_deactivation() {
    wp_redirect(admin_url('plugins.php'));
}

/**
 * Most of the actual deactivation work is done here. Called either as a response
 * to the user confirming the deactivation on `?page=versionpress/admin/deactivate.php`
 * or is called directly from vp_deactivate() if the confirmation screen was not necessary.
 */
function vp_admin_post_confirm_deactivation() {

    define('VP_DEACTIVATING', true);

    if (!file_exists(WP_CONTENT_DIR . '/db.php')) {
        require_once(WP_CONTENT_DIR . '/plugins/versionpress/bootstrap.php');
    }

    if (file_exists(WP_CONTENT_DIR . '/db.php')) {
        FileSystem::remove(WP_CONTENT_DIR . '/db.php');
    }

    if (file_exists(VERSIONPRESS_ACTIVATION_FILE)) {
        FileSystem::remove(VERSIONPRESS_ACTIVATION_FILE);
    }

    FileSystem::remove(VERSIONPRESS_MIRRORING_DIR);


    global $versionPressContainer;
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);

    $committer->forceChangeInfo(new VersionPressChangeInfo("deactivate"));



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


    deactivate_plugins("versionpress/versionpress.php", true);

    if (defined('WP_ADMIN')) {
        wp_redirect(admin_url("plugins.php"));
    }

}

function vp_send_headers() {
    if (isset($_GET['init_versionpress']) && !VersionPress::isActive()) {
        _vp_disable_output_buffering();
    }
}

/**
 * Multiple methods of disabling output buffering.
 * @see http://www.binarytides.com/php-output-content-browser-realtime-buffering/
 */
function _vp_disable_output_buffering() {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);

    // Flush (send) the output buffer and turn off output buffering
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    while (@ob_end_flush()) ;

    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);

    //prevent apache from buffering it for deflate/gzip
    header("Content-type: text/plain");
    header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

    for ($i = 0; $i < 1000; $i++) echo ' ';

    ob_flush();
    flush();
}

add_action('admin_post_vp_send_bug_report', 'vp_send_bug_report');

function vp_send_bug_report() {
    $email = $_POST['email'];
    $description = $_POST['description'];

    $bugReporter = new BugReporter('http://versionpress.net/report-problem');
    $reportedSuccessfully = $bugReporter->reportBug($email, $description);

    $result = $reportedSuccessfully ? "ok" : "err";
    wp_redirect(admin_url("admin.php?page=versionpress/admin/index.php&bug-report=$result"));
}

add_action('admin_notices', 'vp_activation_nag', 4 /* WP update nag is 3, we are just one step less important :) */);

/**
 * Displays the activation nag
 */
function vp_activation_nag() {

    if (VersionPress::isActive() ||
        get_current_screen()->id == "versionpress/admin/index" ||
        get_current_screen()->id == "versionpress/admin/deactivate"
    ) {
        return;
    }

    if (get_transient('vp_just_activated')) {
        return;
    }


    echo "<div class='update-nag vp-activation-nag'>VersionPress is installed but not yet tracking this site. <a href='" . admin_url('admin.php?page=versionpress/admin/index.php') . "'>Please finish the activation.</a></div>";

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
    if (!VersionPress::isActive()) return $data;

    if (empty($postarr['ID'])) { // it's insert not update
        $data['guid'] = IdUtil::newUuid();
    } elseif (preg_match("~^https?://[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$~i", $data['guid'])) { // it's guid
        // strip the protocol (it's created by sanitize_post function)
        $data['guid'] = preg_replace("~^https?://(.*)$~i", "$1", $data['guid']);
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
        'versionpress/admin/index.php',
        '',
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
        'undo.php'
    );

    global $_registered_pages;
    foreach ($directAccessPages as $directAccessPage) {
        $menu_slug = plugin_basename("versionpress/admin/$directAccessPage");
        $hookname = get_plugin_page_hookname($menu_slug, '');
        $_registered_pages[$hookname] = true;
    }

}

add_action('admin_action_vp_show_undo_confirm', 'vp_show_undo_confirm');

function vp_show_undo_confirm() {
    if(isAjax()) {
        require_once(WP_CONTENT_DIR . '/plugins/versionpress/admin/undo.php');
    } else {
        wp_redirect(admin_url('admin.php?page=versionpress/admin/undo.php&method=' . $_GET['method'] . '&commit=' . $_GET['commit']));
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
    /** @var Reverter $reverter */
    $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

    $commitHash = $_GET['commit'];
    vp_enable_maintenance();
    $revertStatus = call_user_func(array($reverter, $reverterMethod), $commitHash);
    vp_disable_maintenance();
    $adminPage = 'admin.php?page=versionpress/admin/index.php';

    if ($revertStatus !== RevertStatus::OK) {
        wp_redirect(admin_url($adminPage . '&error=' . $revertStatus));
    } else {
        wp_redirect($adminPage);
    }
}

if (VersionPress::isActive()) {
    add_action('admin_bar_menu', 'vp_admin_bar_warning');
}

wp_enqueue_style('versionpress_popover_style', plugins_url('admin/public/css/jquery.webui-popover.min.css', __FILE__));
wp_enqueue_style('versionpress_popover_custom_style', plugins_url('admin/public/css/popover-custom.css', __FILE__));
wp_enqueue_script('jquery');
wp_enqueue_script('versionpress_popover_script', plugins_url('admin/public/js/jquery.webui-popover.min.js', __FILE__), 'jquery');
function vp_admin_bar_warning(WP_Admin_Bar $adminBar) {
    if (!current_user_can('activate_plugins')) return;

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

function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

add_action('wp_ajax_hide_vp_welcome_panel', 'vp_ajax_hide_vp_welcome_panel');

function vp_ajax_hide_vp_welcome_panel() {
    update_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, "0");
    die(); // this is required to return a proper result
}

add_action('wp_ajax_vp_show_undo_confirm', 'vp_show_undo_confirm');

//----------------------------------
// Private functions
//----------------------------------

function vp_enable_maintenance() {
    $maintenance_string = '<?php define("VP_MAINTENANCE", true); $upgrading = ' . time() . '; ?>';
    file_put_contents(ABSPATH . '/.maintenance', $maintenance_string);
}

function vp_disable_maintenance() {
    FileSystem::remove(ABSPATH . '/.maintenance');
}
