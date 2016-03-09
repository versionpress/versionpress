<?php

use VersionPress\VersionPress;

defined('ABSPATH') or die("Direct access not allowed");

?>

<div id="vp" class="wrap vp-index">
<?php
if (isset($_GET['init_versionpress']) && !VersionPress::isActive()) {
    require_once("inc/activate.php");
} elseif (!VersionPress::isActive()) {
    require_once("inc/activationPanel.php");
} elseif (VERSIONPRESS_GUI === 'html') {
    require_once("inc/admin.php");
} else {
    require_once("inc/javascriptGui.php");
}
?>
</div>
