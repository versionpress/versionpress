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
    submit_button($label, $type, $action, false, $other_attributes = array("id" => $action) );
    echo "</form>";
}

?>

<div class="wrap">
    <h2 class="vp-deactivation-header">VersionPress deactivation</h2>



    <div class="error below-h2">
        <p>You are about to deactivate VersionPress. Note that in the current version:</p>

        <ul>
            <li><strong>There is no way to re-activate the plugin using the same repository</strong></li>
            <li>Because of that, <strong>we will uninstall the plugin automatically in the next step</strong></li>
        </ul>

        <p>You can choose to either keep or delete the Git repository. If you keep it, the <code>.git</code> folder will be left on your server and you can then e.g. download it and work with it using the command-line tools, etc.</p>

        <div class="vp-pre-uninstall-buttons">

            <?php _vp_button("Cancel", "deactivation_canceled", "delete", "cancel-deactivation"); ?>
            <?php _vp_button("Deactivate, uninstall and REMOVE repository", "deactivation_remove_repo"); ?>
            <?php _vp_button("Deactivate, uninstall and KEEP repository", "deactivation_keep_repo", "primary"); ?>

        </div>

    </div>


</div>
