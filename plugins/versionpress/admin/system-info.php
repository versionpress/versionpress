<?php

use Utils\SystemInfo;
use VersionPress\Utils\RequirementsChecker;

defined('ABSPATH') or die("Direct access not allowed");

$outputFormat = isset($_GET['f']) ? $_GET['f'] : null;
$supportedOutputFormats = array(
    've',
    'tc'
);
if (!in_array($outputFormat, $supportedOutputFormats)) {
    $outputFormat = $supportedOutputFormats[0];
}

$systemInfo = SystemInfo::getAllInfo();

function displaySystemInfoArray($array, $outputFormat) {

    switch ($outputFormat) {
        case 've': // var_export

            echo '<pre><code style="language-php">';
            echo htmlspecialchars(var_export($array, true));
            echo '</code></pre>';

            break;

        case 'tc':
            \Tracy\Debugger::dump($array);
            break;

    }

}

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/styles/default.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>
<script>
    hljs.configure({languages: []}); // disable automatic language detection
    hljs.initHighlightingOnLoad();
</script>

<style>
    h1 {
        margin-top: 30px;
    }

    h2 {
        margin: 30px 0 0;
    }

    .system-info-toc li {
        margin: 0;
    }

</style>

<h1>System info</h1>

<p>Quick overview of this WordPress installation for VersionPress purposes.</p>


<div>
    Format:
    <a href="<?php echo admin_url('admin.php?page=versionpress/admin/system-info.php&f=ve') ?>">var_export</a> |
    <a href="<?php echo admin_url('admin.php?page=versionpress/admin/system-info.php&f=tc') ?>">tracy</a>

    <br />


    Jump to:
    <a href="#git-info">Git info</a> |
    <a href="#persmission-info">Permission info</a> |
    <a href="#wordpress-info">WordPress info</a> |
    <a href="#php-info">System / PHP info</a>

</div>


<h2 id="summary">Quick summary</h2>
<?php displaySystemInfoArray($systemInfo['summary'], $outputFormat); ?>


<h2 id="git-info">Git</h2>
<?php displaySystemInfoArray($systemInfo['git-info'], $outputFormat); ?>

<h2 id="persmission-info">Permissions</h2>
<?php displaySystemInfoArray($systemInfo['permission-info'], $outputFormat); ?>

<h2 id="wordpress-info">WordPress</h2>
<?php displaySystemInfoArray($systemInfo['wordpress-info'], $outputFormat); ?>


<h2 id="php-info">Server environment / PHP info</h2>
<?php displaySystemInfoArray($systemInfo['php-info'], $outputFormat); ?>

