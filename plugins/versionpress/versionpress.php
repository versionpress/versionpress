<?php
/*
Plugin Name: VersionPress
Plugin URI: http://versionpress.net/
Description: Git-versioning plugin for WordPress
Author: Agilio
Version: 1.0
*/

defined('ABSPATH') or die("Direct access not allowed");



//----------------------------------------
// Hooks for VersionPress functionality
//----------------------------------------


if (vp_is_active()) {
    vp_register_hooks();
}

function vp_register_hooks() {
    global $wpdb, $versionPressContainer;
    $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
    $committer = $versionPressContainer->resolve(VersionPressServices::COMMITTER);

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
        if ($hook_extra['type'] == 'core' && $hook_extra['action'] == 'update') return; // handled by different hook
        $pluginName = $hook_extra['plugin'];
        $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'update'));
    }, 10, 2);

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
// uninstallation is handled in uninstall.php


/**
 * Activates the plugin from a WordPress point of view. Note that for VersionPress
 * to become fully active, the Initializer needs to be run.
 *
 * @see Initializer
 */
function vp_activate() {
    copy(dirname(__FILE__) . '/_db.php', WP_CONTENT_DIR . '/db.php');
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

    FileSystem::getWpFilesystem()->rmdir(__DIR__ . '/db');

    global $wpdb;

    $table_prefix = $wpdb->prefix;

    $queries[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
    $queries[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";

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

    if (vp_is_active() || get_current_screen()->id == "versionpress/admin/index") {
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

    if(vp_is_active())
        add_submenu_page(
            'versionpress/admin/index.php',
            'Synchronization',
            'Synchronization',
            'manage_options',
            'versionpress/admin/sync.php'
        );

    // Support for deactivate.php - add it to the internal $_registered_pages array
    // See e.g. http://blog.wpessence.com/wordpress-admin-page-without-menu-item/
    global $_registered_pages;
    $menu_slug = plugin_basename("versionpress/admin/deactivate.php");
    $hookname = get_plugin_page_hookname( $menu_slug, '' );
    $_registered_pages[$hookname] = true;

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




