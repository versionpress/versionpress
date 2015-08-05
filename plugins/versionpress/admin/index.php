<?php
use VersionPress\Configuration\VersionPressConfig;
use VersionPress\DI\VersionPressServices;
use VersionPress\VersionPress;

defined('ABSPATH') or die("Direct access not allowed");

global $versionPressContainer;
/** @var VersionPressConfig $vpConfig */
$vpConfig = $versionPressContainer->resolve(VersionPressServices::VP_CONFIGURATION);
?>

<div id="vp" class="wrap vp-index">
<?php
if (isset($_GET['init_versionpress']) && !VersionPress::isActive()) {
    require_once("inc/activate.php");
} elseif (!VersionPress::isActive()) {
    require_once("inc/activationPanel.php");
} elseif ($vpConfig->mergedConfig['gui'] === 'javascript') {
    require_once("inc/javascriptGUI.php");
} else {
    require_once("inc/admin.php");
}
?>
</div>
