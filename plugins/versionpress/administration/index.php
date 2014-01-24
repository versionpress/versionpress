<?php
    if(isset($_GET['init']))
        initialize();

function initialize() {
    require_once(VERSIONPRESS_PLUGIN_DIR . '/install.php');

    global $wpdb, $table_prefix;

    @mkdir(VERSIONPRESS_MIRRORING_DIR, 0777, true);
    $dbSchema = new DbSchemaInfo(VERSIONPRESS_PLUGIN_DIR . '/src/database/schema.neon', $table_prefix);
    $storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
    $installer = new VersionPressInstaller($wpdb, $dbSchema, $storageFactory, $table_prefix);
    $installer->onProgressChanged[] = 'show_message';
    $installer->install();
}

?>
<form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/administration/index.php&init'); ?>">
    <input type="submit" value="Initialize">
</form>