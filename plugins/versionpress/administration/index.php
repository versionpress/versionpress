<?php
$isInitialized = is_file(VERSIONPRESS_PLUGIN_DIR . '/.active');

function initialize() {
    require_once(VERSIONPRESS_PLUGIN_DIR . '/install.php');

    global $wpdb, $table_prefix;

    @mkdir(VERSIONPRESS_MIRRORING_DIR, 0777, true);
    $dbSchema = new DbSchemaInfo(VERSIONPRESS_PLUGIN_DIR . '/src/database/schema.neon', $table_prefix);
    $storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
    $installer = new VersionPressInstaller($wpdb, $dbSchema, $storageFactory, $table_prefix);
    $installer->onProgressChanged[] = 'show_message';
    $installer->install();

    touch(VERSIONPRESS_PLUGIN_DIR . '/.active');
}


if(isset($_GET['init']) && !$isInitialized) {
    initialize();
?>
    <script type="text/javascript">
        window.location = '<?php echo admin_url('admin.php?page=versionpress/administration/index.php'); ?>';
    </script>
<?php
} elseif(!$isInitialized) {
?>
    <form method="POST" action="<?php echo admin_url('admin.php?page=versionpress/administration/index.php&init'); ?>">
        <input type="submit" value="Initialize">
    </form>
<?php
} else {
    if (isset($_GET['revert'])) {
        Git::revert($_GET['revert']);
        require_once __DIR__ . '/../../versionpress/sync.php';
    }
?>
    <h1>VersionPress</h1>
    <table>
        <tr>
            <th></th>
            <th>ID</th>
            <th>Message</th>
        </tr>
        <?php
        $commits = Git::log();
        foreach($commits as $commit) {
            echo "
        <tr>
            <td><a href='" . admin_url('admin.php?page=versionpress/administration/index.php&revert=' . $commit['id']) . "' style='font-size: 18px;text-decoration:none;'>&#8630;</a></td>
            <td>$commit[id]</td>
            <td>$commit[message]</td>
        </tr>";
        }
        ?>
    </table>
<?php
}
?>
