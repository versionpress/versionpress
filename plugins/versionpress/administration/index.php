<style>
    table th, table td {border-bottom: 1px solid #ccc;}
</style>
<h1>VersionPress</h1>
<?php
$isInitialized = is_file(VERSIONPRESS_PLUGIN_DIR . '/.active');

wp_enqueue_style('versionpress_admin_style', plugins_url( 'style.css' , __FILE__ ));

function initialize() {

    global $wpdb, $table_prefix;

    @mkdir(VERSIONPRESS_MIRRORING_DIR, 0777, true);
    $dbSchema = new DbSchemaInfo(VERSIONPRESS_PLUGIN_DIR . '/src/database/schema.neon', $table_prefix);
    $storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
    $installer = new VersionPressInstaller($wpdb, $dbSchema, $storageFactory, $table_prefix);
    $installer->onProgressChanged[] = 'show_message';
    $installer->install();
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
        if(Git::revert($_GET['revert'])) {
            require_once __DIR__ . '/../../versionpress/sync.php';
        } else {
            echo "<div class='error'>Error: Overwritten changes can not be reverted.</div>";
        }
    }
    if (isset($_GET['revert-all'])) {
        Git::revertAll($_GET['revert-all']);
        require_once __DIR__ . '/../../versionpress/sync.php';
    }
?>
    <table id="versionpress-commits-table" class="wp-list-table widefat fixed posts">
        <tr>
            <th class="manage-column column-date">Date</th>
            <th class="manage-column column-commit-id">ID</th>
            <th class="manage-column column-message">Message</th>
            <th class="manage-column column-actions"></th>
        </tr>
        <tbody id="the-list">
        <?php

        /**
         * @param Commit $commit
         * @return ChangeInfo
         */
        function createChangeInfo(Commit $commit) {
            /** @var ChangeInfo[] $changeInfoClasses */
            $changeInfoClasses = array('PluginChangeInfo', 'WordPressUpdateChangeInfo', 'EntityChangeInfo');
            $matchingChangeInfoClass = 'CustomChangeInfo'; // some fallback
            foreach ($changeInfoClasses as $changeInfoClass) {
                if($changeInfoClass::matchesCommitMessage($commit->getMessage())){
                    $matchingChangeInfoClass = $changeInfoClass;
                    break;
                }
            }
            $changeInfo = $matchingChangeInfoClass::buildFromCommitMessage($commit->getMessage());
            return $changeInfo;
        }

        $commits = Git::log();
        $isFirstCommit = true;

        foreach($commits as $commit) {

            $revertAllSnippet = $isFirstCommit ? "" : "|
                <a href='" . admin_url('admin.php?page=versionpress/administration/index.php&revert-all=' . $commit->getHash()) . "' style='text-decoration:none; white-space:nowrap;' title='Reverts site back to this state; effectively undos all the change up to this commit'>
                Revert to this
            </a>";

            $message = substr($commit->getMessage()->getHead(), 0, 100);
            echo "
        <tr class=\"post-1 type-post status-publish format-standard hentry category-uncategorized alternate level-0\">
            <td>{$commit->getRelativeDate()}</td>
            <td>{$commit->getHash()}</td>
            <td>$message</td>
            <td style=\"text-align: right\">
                <a href='" . admin_url('admin.php?page=versionpress/administration/index.php&revert=' . $commit->getHash()) . "' style='text-decoration:none; white-space:nowrap;' title='Reverts changes done by this commit'>
                Undo this
                </a>
                $revertAllSnippet
            </td>
        </tr>";

            $isFirstCommit = false;
        }
        ?>
        </tbody>
    </table>
<?php
}
?>
