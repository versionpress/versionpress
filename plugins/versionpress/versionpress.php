<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Author: VersionPress
Version: 1.0-alpha1
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
    copy(dirname(__FILE__) . '/_db.php', WP_CONTENT_DIR . '/db.php');
    add_option('vp_just_activated', '1');
}

/**
 * Deactivation is a two-step process with a warning screen. See
 * `versionpress_admin_post_cancel_deactivation()` and `versionpress_admin_post_confirm_deactivation()`
 */
function vp_deactivate() {
    wp_redirect(admin_url('admin.php?page=versionpress/admin/deactivate.php'));
    die();
}

/**
 * Handles a situation where user canceled the deactivation
 */
function vp_admin_post_cancel_deactivation() {
    wp_redirect(admin_url('plugins.php'));
}

/**
 * Handles a situation where user confirmed the deactivation. Most
 * of the actual work is done here.
 */
function vp_admin_post_confirm_deactivation() {

    unlink(WP_CONTENT_DIR . '/db.php');
    unlink(__DIR__ . '/.active');

    FileSystem::getWpFilesystem()->rmdir(__DIR__ . '/db', true);

    global $wpdb;

    $table_prefix = $wpdb->prefix;

    $queries[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";
    $queries[] = "DELETE FROM `{$table_prefix}usermeta` WHERE meta_key LIKE \"vp_%\"";

    foreach ($queries as $query) {
        $wpdb->query($query);
    }


    deactivate_plugins("versionpress/versionpress.php", true);
    wp_redirect(admin_url("plugins.php"));

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

wp_enqueue_style('versionpress_admin_style', plugins_url( 'admin/css/jquery.webui-popover.min.css' , __FILE__ ));
wp_enqueue_script('jquery');
wp_enqueue_script('versionpress_admin_script', plugins_url( 'admin/js/jquery.webui-popover.min.js' , __FILE__ ), 'jquery');
function vp_admin_bar_warning(WP_Admin_Bar $adminBar) {
    $adminBarText = "You are running an <span style=\"color:red;font-weight:bold\">alpha version</span> of VersionPress";
    $thickboxWidth = 600;
    $thickboxHeight = 200;
    $thickboxUrl = "#TB_inline?width=$thickboxWidth&height=$thickboxHeight&inlineId=modal-window-id";
    $popoverTitle = "Use for TESTING ONLY";
    $popoverText = "<p>You are running an alpha version of VersionPress which means that there <em>are</em> bugs, this site's data may become corrupt etc. <strong style='color: red;'>NEVER USE THIS VERSION IN PRODUCTION OR WITH A PRODUCTION DATABASE.</strong></p>";
    $popoverText .= "<p><a href='http://versionpress.net/docs/en/release-notes' target='_blank'>Learn more about VersionPress releases</a></p>";

    $adminBar->add_node(array(
            'id' => 'vp-running',
            'title' => "<a href='#' class='ab-item' id='vp-warning'>$adminBarText</a>
            <script>
            var warning = jQuery('#vp-warning');
            warning.webuiPopover({title:\"$popoverTitle\", content: \"$popoverText\"});
            jQuery('body').on('click', function(e) {
                if(e.target != warning[0])
                    jQuery('#vp-warning').webuiPopover('hide');
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
    return defined('VERSIONPRESS_PLUGIN_DIR') && file_exists(VERSIONPRESS_PLUGIN_DIR . '/.active');
}

