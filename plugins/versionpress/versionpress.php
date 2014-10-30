<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Author: VersionPress
Version: 1.0-beta1
*/

defined('ABSPATH') or die("Direct access not allowed");



//----------------------------------------
// Hooks for VersionPress functionality
//----------------------------------------


if (vp_is_active()) {
    vp_register_hooks();
}

function vp_register_hooks() {
    /** @var wpdb $wpdb */
    global $wpdb, $versionPressContainer;
    /** @var EntityStorageFactory $storageFactory */
    $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
    /** @var Committer $committer */
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);
    /** @var Mirror $mirror */
    $mirror = $versionPressContainer->resolve(VersionPressServices::MIRROR);

    /**
     *  Hook for saving taxonomies into files
     *  WordPress creates plain INSERT query and executes it using wpdb::query method instead of wpdb::insert.
     *  It's too difficult to parse every INSERT query, that's why the WordPress hook is used.
     */
    add_action('save_post', createUpdatePostTermsHook($storageFactory->getStorage('posts'), $wpdb));

    add_filter('update_feedback', function () {
        touch(get_home_path() . 'versionpress.maintenance');
    });
    add_action('_core_updated_successfully', function () use ($committer) {
        require(get_home_path() . '/wp-includes/version.php'); // load constants (like $wp_version)
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
        if($hook_extra['type'] === 'theme') {
            $themeName = isset($upgrader->skin->api) ? $upgrader->skin->api->name : null;
            $themeId = $upgrader->result['destination_name'];
            $action = $hook_extra['action']; // can be "install" or "update", see WP_Upgrader and search for `'hook_extra' =>`
            $committer->forceChangeInfo(new ThemeChangeInfo($themeId, $action, $themeName));
        }

        if(!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'update')) return; // handled by different hook
        $pluginName = $hook_extra['plugin'];
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'update'));
    }, 10, 2);

    add_action('added_option', function ($name) use ($wpdb, $mirror) {
        $option = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}options WHERE option_name='$name'", ARRAY_A);
        $mirror->save("options", $option);
    });

    add_filter('upgrader_pre_install', function($_, $hook_extra) use ($committer) {
        if(!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;
        $pluginsBeforeInstallation = get_plugins();
        add_filter('upgrader_post_install', function ($_, $hook_extra) use ($pluginsBeforeInstallation, $committer) {
            if(!($hook_extra['type'] === 'plugin' && $hook_extra['action'] === 'install')) return;
            wp_cache_delete('plugins', 'plugins');
            $pluginsAfterInstallation = get_plugins();
            $installedPlugin = array_diff_key($pluginsAfterInstallation, $pluginsBeforeInstallation);
            reset($installedPlugin);
            $pluginName = key($installedPlugin);
            $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'install'));
        }, 10, 2);
    },10, 2);

    add_action('switch_theme', function($themeName, $theme) use ($committer) {
        $themeId = $theme->stylesheet;
        $committer->forceChangeInfo(new ThemeChangeInfo($themeId, 'switch', $themeName));
    }, 10, 2);

    add_action('customize_save_after', function($customizeManager) use ($committer) {
        $stylesheet = $customizeManager->theme()->stylesheet;
        $committer->forceChangeInfo(new ThemeChangeInfo($stylesheet, 'customize'));
        register_shutdown_function(function () {
            wp_remote_get(admin_url("admin.php"));
        });
    });

    //----------------------------------------
    // URL "hooks"
    //----------------------------------------

    if(basename($_SERVER['PHP_SELF']) === 'themes.php' && isset($_GET['action']) && $_GET['action'] === 'delete') {
        $themeId = $_GET['stylesheet'];
        $committer->forceChangeInfo(new ThemeChangeInfo($themeId, 'delete'));
    }

    if(basename($_SERVER['PHP_SELF']) === 'plugins.php'
        && isset($_GET['action']) && $_GET['action'] === 'delete-selected'
        && isset($_REQUEST['verify-delete'])) {

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = $_REQUEST['checked'];
        $plugin = $plugins[0];

        $committer->forceChangeInfo(new PluginChangeInfo($plugin, 'delete'));
    }

    if(basename($_SERVER['PHP_SELF']) === 'theme-editor.php' && isset($_GET['updated']) && $_GET['updated'] === 'true') {
        $committer->forceChangeInfo(new ThemeChangeInfo($_GET['theme'], 'edit'));
    }

    if(basename($_SERVER['PHP_SELF']) === 'plugin-editor.php' &&
        ((isset($_POST['action']) && $_POST['action'] === 'update') || isset($_GET['liveupdate'])
        )) {
        $committer->disableCommit();
    }

    if(basename($_SERVER['PHP_SELF']) === 'plugin-editor.php' && isset($_GET['a']) && $_GET['a'] === 'te') {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $editedFile = $_GET['file'];
        $editedFilePathParts = preg_split("~[/\\\]~", $editedFile);
        $plugins = array_keys(get_plugins());
        $bestRank = 0;
        $bestMatch = "";

        foreach($plugins as $plugin) {
            $rank = 0;
            $pluginPathParts = preg_split("~[/\\\]~", $plugin);
            $maxEqualParts = min(count($editedFilePathParts), count($pluginPathParts));

            for($part = 0 ; $part < $maxEqualParts; $part++) {
                if($editedFilePathParts[$part] !== $pluginPathParts[$part]) break;
                $rank += 1;
            }

            if($rank > $bestRank) {
                $bestRank = $rank;
                $bestMatch = $plugin;
            }
        }

        $committer->forceChangeInfo(new PluginChangeInfo($bestMatch, 'edit'));
    }

    register_shutdown_function(array($committer, 'commit'));
}

function createUpdatePostTermsHook(EntityStorage $storage, wpdb $wpdb) {

    return function ($postId) use ($storage, $wpdb) {
        $post = get_post($postId);
        $postType = $post->post_type;
        $taxonomies = get_object_taxonomies($postType);

        $vpIdTableName = $wpdb->prefix . 'vp_id';

        $postVpId = $wpdb->get_var("SELECT HEX(vp_id) FROM $vpIdTableName WHERE id = $postId AND `table` = 'posts'");

        $postUpdateData = array('vp_id' => $postVpId);

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);
            if ($terms)
                $postUpdateData[$taxonomy] = array_map(function ($term) use ($wpdb, $vpIdTableName) {
                    return $wpdb->get_var("SELECT HEX(vp_id) FROM $vpIdTableName WHERE id = {$term->term_id} AND `table` = 'terms'");
                }, $terms);
        }

        if (count($taxonomies) > 0)
            $storage->save($postUpdateData);
    };
}



//----------------------------------
// Activation and deactivation
//----------------------------------

register_activation_hook(__FILE__, 'vp_activate');
register_deactivation_hook(__FILE__, 'vp_deactivate');
add_action('admin_post_cancel_deactivation', 'vp_admin_post_cancel_deactivation');
add_action('admin_post_confirm_deactivation', 'vp_admin_post_confirm_deactivation');
if (get_option('vp_just_activated')) {
    add_filter('gettext', 'vp_gettext_filter_plugin_activated', 10, 3);
}
// uninstallation is handled in uninstall.php

/**
 * We hook to `gettext` filter to update the "plugin activated" message with a link
 * to VP initializer. See [WP-66].
 *
 * @param string $translation Translated text.
 * @param string $text        Text to translate.
 * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
 */
function vp_gettext_filter_plugin_activated($translation, $text, $domain) {
    if ($text == 'Plugin <strong>activated</strong>.' && get_option('vp_just_activated')) {
        delete_option('vp_just_activated');
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
    add_option('vp_just_activated', '1');
}

/**
 * Deactivation is a two-step process with a warning screen. See
 * `vp_admin_post_cancel_deactivation()` and `vp_admin_post_confirm_deactivation()`
 *
 * @see vp_admin_post_confirm_deactivation()
 * @see vp_admin_post_cancel_deactivation()
 */
function vp_deactivate() {
    if (vp_is_active()) {
        wp_redirect(admin_url('admin.php?page=versionpress/admin/deactivate.php'));
        die();
    } else {
        vp_admin_post_confirm_deactivation();
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

    if (!file_exists(WP_CONTENT_DIR . '/db.php')) {
        require_once(WP_CONTENT_DIR . '/plugins/versionpress/bootstrap.php');
    }

    if (file_exists(WP_CONTENT_DIR . '/db.php')) {
        unlink(WP_CONTENT_DIR . '/db.php');
    }

    if (file_exists(VERSIONPRESS_ACTIVATION_FILE)) {
        unlink(VERSIONPRESS_ACTIVATION_FILE);
    }

    FileSystem::getWpFilesystem()->rmdir(VERSIONPRESS_MIRRORING_DIR, true);

    global $wpdb;

    $table_prefix = $wpdb->prefix;

    $queries[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";

    $vpOptionsReflection = new ReflectionClass('VersionPressOptions');
    $usermetaToDelete = array_values($vpOptionsReflection->getConstants());
    $queryRestriction = '"' . join('", "', $usermetaToDelete) . '"';

    $queries[] = "DELETE FROM `{$table_prefix}usermeta` WHERE meta_key IN ({$queryRestriction})";

    foreach ($queries as $query) {
        $wpdb->query($query);
    }


    deactivate_plugins("versionpress/versionpress.php", true);
    wp_redirect(admin_url("plugins.php"));

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

    if (vp_is_active() ||
        get_current_screen()->id == "versionpress/admin/index" ||
        get_current_screen()->id == "versionpress/admin/deactivate")
    {
        return;
    }

    if (get_option('vp_just_activated')) {
        return;
    }


    echo "<div class='update-nag'>VersionPress is installed but not yet tracking this site. <a href='" . admin_url('admin.php?page=versionpress/admin/index.php') . "'>Please finish the activation.</a></div>";

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
    if(!vp_is_active()) return $data;

    if(empty($postarr['ID'])) { // it's insert not update
        $data['guid'] = IdUtil::newUuid();
    } elseif(preg_match("~^https?://[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$~i", $data['guid'])) { // it's guid
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

    // Support for deactivate.php - add it to the internal $_registered_pages array
    // See e.g. http://blog.wpessence.com/wordpress-admin-page-without-menu-item/
    global $_registered_pages;
    $menu_slug = plugin_basename("versionpress/admin/deactivate.php");
    $hookname = get_plugin_page_hookname( $menu_slug, '' );
    $_registered_pages[$hookname] = true;

}

add_action('admin_action_vp_undo', 'vp_undo');

function vp_undo () {
    _vp_revert('revert');
}

add_action('admin_action_vp_rollback', 'vp_rollback');

function vp_rollback () {
    _vp_revert('revertAll');
}

function _vp_revert($reverterMethod) {
    global $versionPressContainer;
    /** @var Reverter $reverter */
    $reverter = $versionPressContainer->resolve(VersionPressServices::REVERTER);

    $commitHash = $_GET['commit'];
    $revertStatus = call_user_func(array($reverter, $reverterMethod), $commitHash);
    $adminPage = 'admin.php?page=versionpress/admin/index.php';

    if($revertStatus !== RevertStatus::OK) {
        wp_redirect(admin_url($adminPage . '&error=' . $revertStatus));
    } else {
        wp_redirect($adminPage);
    }
}

if(vp_is_active()) {
    add_action('admin_bar_menu', 'vp_admin_bar_warning');
}

wp_enqueue_style('versionpress_popover_style', plugins_url( 'admin/css/jquery.webui-popover.min.css' , __FILE__ ));
wp_enqueue_style('versionpress_popover_custom_style', plugins_url( 'admin/css/popover-custom.css' , __FILE__ ));
wp_enqueue_script('jquery');
wp_enqueue_script('versionpress_popover_script', plugins_url( 'admin/js/jquery.webui-popover.min.js' , __FILE__ ), 'jquery');
function vp_admin_bar_warning(WP_Admin_Bar $adminBar) {
    $adminBarText = "You are running a <span style=\"color:red;font-weight:bold\">preview version</span> of VersionPress";
    $popoverTitle = "Use for <strong>testing only</strong>";
    $popoverText = "<p>You are running a preview version of VersionPress which means that there <em>are</em> bugs, this site's data may become corrupt etc.<br /><br /><strong style='color: red;'>DON'T USE THIS VERSION IN PRODUCTION OR WITH A PRODUCTION DATABASE.</strong></p>";
    $popoverText .= "<p><a href='http://versionpress.net/docs/en/release-notes' target='_blank'>Learn more about VersionPress releases</a></p>";

    $adminBar->add_node(array(
            'id' => 'vp-running',
            'title' => "<a href='#' class='ab-item' id='vp-warning'>$adminBarText</a>
            <script>
            var warning = jQuery('#vp-warning');
            var customPopoverClass = \"versionpress-alpha\"; // used to identify the popover later

            warning.webuiPopover({title:\"$popoverTitle\", content: \"$popoverText\", closeable: true, style: customPopoverClass, width:450});
            jQuery('body').on('click', function(e) {
                var popopOverSelector = '.webui-popover-' + customPopoverClass;
                if (jQuery(popopOverSelector).length > 0 && jQuery(popopOverSelector).is(':visible') && jQuery(e.target).parents(popopOverSelector).length == 0 &&
                    !jQuery(e.target).is(warning) && jQuery(e.target).parents('#vp-warning').length == 0)
                {
                    // Hide popover if the click was anywhere but in the link or the popover itself.
                    jQuery('#vp-warning').webuiPopover('hide');
                }
            });
            </script>",
            'parent' => 'top-secondary'
        ));
}

//----------------------------------
// AJAX handling
//----------------------------------

add_action( 'wp_ajax_hide_vp_welcome_panel', 'vp_ajax_hide_vp_welcome_panel' );

function vp_ajax_hide_vp_welcome_panel() {
    update_user_meta(get_current_user_id(), VersionPressOptions::USER_META_SHOW_WELCOME_PANEL, "0");
    die(); // this is required to return a proper result
}



//----------------------------------
// Public functions
//----------------------------------

/**
 * Returns true if VersionPress is active. Note that active != activated and being
 * active means that VersionPress is tracking changes.
 *
 * @return bool
 */
function vp_is_active() {
    return defined('VERSIONPRESS_ACTIVATION_FILE') && file_exists(VERSIONPRESS_ACTIVATION_FILE);
}

