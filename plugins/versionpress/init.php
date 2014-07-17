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

    /** @var Mirror */
    private $mirror;
    /** @var  ChangeInfo */
    private $forcedChangeInfo;

    public function __construct(Mirror $mirror) {
        $this->mirror = $mirror;
    }

    /**
     * Checks if there is any change. If so, it tries to commit.
     */
    public function commit() {
        if ($this->forcedChangeInfo) {
            @unlink(get_home_path() . 'versionpress.maintenance'); // todo: this shouldn't be here...
            Git::commit($this->forcedChangeInfo->getCommitMessage());
            $this->forcedChangeInfo = null;
        } elseif ($this->mirror->wasAffected() && $this->shouldCommit()) {
            $changeList = $this->mirror->getChangeList();
            $commitMessage = $changeList[0]->getCommitMessage();

            Git::commit($commitMessage);
        }
    }

    public function forceChangeInfo(ChangeInfo $changeInfo) {
        $this->forcedChangeInfo = $changeInfo;
    }

    private function shouldCommit() {
        // proof of concept
        if ($this->dbWasUpdated() && $this->existsMaintenanceFile())
            return false;
        return true;
    }

    private function dbWasUpdated() {
        $changes = $this->mirror->getChangeList();
        foreach ($changes as $change) {
            if ($change instanceof EntityChangeInfo &&
                $change->getObjectType() == 'option' &&
                $change->getEntityId() == 'db_version'
            )
                return true;
        }
        return false;
    }

    private function existsMaintenanceFile() {
        $maintenanceFilePattern = get_home_path() . '*.maintenance';
        return count(glob($maintenanceFilePattern)) > 0;
    }
}

$committer = new Committer($mirror);
add_filter('update_feedback', function () {
    touch(get_home_path() . 'versionpress.maintenance');
});
add_action('_core_updated_successfully', function () use ($committer) {
    require(get_home_path() . '/wp-includes/version.php'); // load constants (like $wp_version)
    /** @var string $wp_version */
    $changeInfo = new WordPressUpdateChangeInfo($wp_version);
    $committer->forceChangeInfo($changeInfo);
});

add_action('activated_plugin', function ($pluginName) use ($committer) {
    $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'activate'));
});

add_action('deactivated_plugin', function ($pluginName) use ($committer) {
    $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'deactivate'));
});

add_action('upgrader_process_complete', function ($upgrader, $hook_extra) use ($committer) {
    if($hook_extra['type'] == 'core' && $hook_extra['action'] == 'update') return; // handled by different hook
    $pluginName = $hook_extra['plugin'];
    $committer->forceChangeInfo(new PluginChangeInfo($pluginName, 'update'));
}, 10, 2);

register_shutdown_function(array($committer, 'commit'));