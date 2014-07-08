<?php

require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/EntityStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/ObservableStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/DirectoryStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/EntityStorageFactory.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/CommentStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/PostStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/SingleFileStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/OptionsStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/TermsStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/TermTaxonomyStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/UserStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/storages/UserMetaStorage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/DbSchemaInfo.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/ExtendedWpdb.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/database/MirroringDatabase.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/IniSerializer.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Git.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Neon.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Arrays.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Strings.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/utils/Uuid.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/Mirror.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/ChangeInfo.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/CommitMessage.php');
require_once(VERSIONPRESS_PLUGIN_DIR . '/src/CommitMessageProvider.php');

global $wpdb, $table_prefix, $storageFactory, $schemaInfo;
$storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
$mirror = new Mirror($storageFactory);
$schemaFile = VERSIONPRESS_PLUGIN_DIR . '/src/database/schema.neon';
$schemaInfo = new DbSchemaInfo($schemaFile, $table_prefix);
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $mirror, $schemaInfo);

// Hook for saving taxonomies into files
// WordPress creates plain INSERT query and executes it using wpdb::query method instead of wpdb::insert.
// It's too difficult to parse every INSERT query, that's why the WordPress hook is used.
add_action('save_post', createUpdatePostTermsHook($storageFactory->getStorage('posts'), $wpdb));

function createUpdatePostTermsHook(EntityStorage $storage, wpdb $wpdb) {

    return function ($postId) use ($storage, $wpdb) {
        global $table_prefix;

        $post = get_post($postId);
        $postType = $post->post_type;
        $taxonomies = get_object_taxonomies($postType);

        $vpIdTableName = $table_prefix . 'vp_id';

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

class Committer {

    /**
     * @var Mirror
     */
    private $mirror;
    /**
     * @var CommitMessageProvider
     */
    private $commitMessageProvider;
    private $forcedCommitMessage;

    public function __construct(Mirror $mirror, CommitMessageProvider $commitMessageProvider) {
        $this->mirror = $mirror;
        $this->commitMessageProvider = $commitMessageProvider;
    }

    /**
     * Checks if some entity has been changed. If so, it tries to commit.
     */
    public function commit () {
        if($this->forcedCommitMessage) {
            @unlink(get_home_path() . 'versionpress.maintenance'); // todo: this shouldn't be here...
            Git::commit($this->forcedCommitMessage);
            $this->forcedCommitMessage = null;
        } elseif ($this->mirror->wasAffected() && $this->shouldCommit()) {
            $changeList = $this->mirror->getChangeList();
            $commitMessage = $this->createCommitMessage($changeList[0]);

            Git::commit($commitMessage);
        }
    }

    public function forceCommitMessage($commitMessage) {
        $this->forcedCommitMessage = $commitMessage;
    }

    /**
     * Converts ChangeInfo to human readable string.
     *
     * Samples:
     * Created post with ID 1
     * Edited post with ID 2
     * Deleted post with ID 3
     *
     * @param ChangeInfo $changeInfo
     * @return string
     */
    private function createCommitMessage(ChangeInfo $changeInfo) {
        return $this->commitMessageProvider->getCommitMessage($changeInfo);
    }

    private function shouldCommit() {
        // proof of concept
        if($this->dbWasUpdated() && $this->existsMaintenanceFile())
            return false;
        return true;
    }

    private function dbWasUpdated() {
        $changes = $this->mirror->getChangeList();
        /** @var $change ChangeInfo */
        foreach ($changes as $change) {
            if ($change->entityType == 'option' && $change->entityId == 'db_version')
                return true;
        }
        return false;
    }

    private function existsMaintenanceFile() {
        $maintenanceFilePattern = get_home_path() . '*.maintenance';
        return count(glob($maintenanceFilePattern)) > 0;
    }
}

$committer = new Committer($mirror, new CommitMessageProvider());
add_filter('update_feedback', function () {
    touch(get_home_path() . 'versionpress.maintenance');
});
add_action('_core_updated_successfully', function() use ($committer) {
    require( get_home_path() . '/wp-includes/version.php' ); // load constants (like $wp_version)
    /** @var $wp_version */
    $committer->forceCommitMessage('WordPress updated to version ' . $wp_version);
});

add_action('activated_plugin', function($pluginName) use ($committer) {
    $committer->forceCommitMessage('Plugin "' . $pluginName .'" was activated');
});

add_action('deactivated_plugin', function($pluginName) use ($committer) {
    $committer->forceCommitMessage('Plugin "' . $pluginName .'" was deactivated');
});

add_action('upgrader_process_complete', function($upgrader, $hook_extra) use ($committer) {
    $pluginName = $hook_extra['plugin'];
    $committer->forceCommitMessage('Plugin "' . $pluginName .'" was updated');
}, 10, 2);

register_shutdown_function(array($committer, 'commit'));