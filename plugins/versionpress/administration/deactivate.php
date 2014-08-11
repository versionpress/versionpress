<?php
defined('ABSPATH') or die("Direct access not allowed");

wp_enqueue_style('versionpress_admin_style', plugins_url( 'style.css' , __FILE__ ));

/**
 * Small helper function that outputs every button in its own form. We do this
 * to be able to submit a value of a button in a hidden field so that that the make POST-handling code
 * does not depend on UI strings.
 *
 * @param string $label Label to display
 * @param string $action Action string that must match the admin_post_* hook in versionpress.php. For example,
 *                       the `deactivation_canceled` action will be mapped to `admin_post_deactivation_canceled` hook
 *                       in the main plugin file.
 * @param string $type See `submit_button()`'s $type parameter
 * @param string $cssClass Optional CSS class to be used for the button
 */
function _vp_button($label, $action, $type = "delete", $cssClass = "") {
    echo "<form action='" . admin_url('admin-post.php') . "' method='post' class='$cssClass'>";
    echo "<input type='hidden' name='action' value='$action' />";
    submit_button($label, $type = $type, $wrap = false, $other_attributes = null );
    echo "</form>";
}

?>

<div class="wrap">
    <h2>VersionPress deactivation</h2>



    <div class="error below-h2">
        <p>You are about to deactivate VersionPress. Note that:</p>

        <ul>
            <li>There will be <strong>no way to re-activate the plugin with the current repository</strong>. We will probably support this in the future but currently, once VersionPress stops tracking a site it cannot return to tracking it again using the same repository.</li>
            <li>Since the plugin cannot be sensibly re-activated, you will be taken to the uninstall screen in the next step. Choose now whether to <strong>keep or delete the repository</strong> - the WordPress uninstall screen will look the same in both cases but the <code>.git</code> folder of your website will either be kept or removed.</li>
        </ul>

        <p>If you choose to keep the repository, you can e.g. manually download the <code>.git</code> folder and work with it using the command-line tools etc.</p>

        <div class="vp-pre-uninstall-buttons">

            <?php _vp_button("Cancel", "deactivation_canceled", "delete", "cancel-deactivation"); ?>
            <?php _vp_button("Deactivate and REMOVE repository", "deactivation_remove_repo"); ?>
            <?php _vp_button("Deactivate and KEEP repository", "deactivation_keep_repo", "primary"); ?>

        </div>

    </div>


</div>
