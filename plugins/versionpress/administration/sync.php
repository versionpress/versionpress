<?php

function createConstantReplacementDefinition($constantName) {
    return array(
        'regexp' => "/define[^\\w]+$constantName/i",
        'replaceWith' => "define('$constantName', '%s');",
        'value' => constant($constantName)
    );
}

function replaceDatabaseSettings() {
    global $table_prefix;
    $replacedConstants = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_CHARSET', 'DB_COLLATE');
    $replacementDefinitions = array_map("createConstantReplacementDefinition", $replacedConstants);
    $replacementDefinitions[] = array(
        'regexp' => '/\\$table_prefix[^\\w]*=/i',
        'replaceWith' => '$table_prefix = "%s"',
        'value' => $table_prefix
    );

    $configFileLines = file(ABSPATH . '/wp-config.php');
    foreach($configFileLines as $lineNumber => $line) {
        foreach($replacementDefinitions as $i => $replacementDefinition) {
            if(preg_match($replacementDefinition['regexp'],$line)) {
                $configFileLines[$lineNumber] = sprintf($replacementDefinition['replaceWith'], $replacementDefinition['value']);
                unset($replacementDefinitions[$i]);
            }
        }
    }
    file_put_contents(ABSPATH . '/wp-config.php', implode(PHP_EOL, $configFileLines));
}

if (isset($_GET['pull'])) {
    Git::pull();
    replaceDatabaseSettings();
    require_once __DIR__ . '/../../versionpress/sync.php';
}
if (isset($_GET['push'])) {
    Git::pull();
    Git::push();
    file_get_contents("http://localhost/wordpress/wp-content/versionpress/sync.php");
}
?>
<h1>VersionPress</h1>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/administration/sync.php&pull'); ?>">
    <input type="submit" value="Synchronize - pull">
</form>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/administration/sync.php&push'); ?>">
    <input type="submit" value="Synchronize - push">
</form>