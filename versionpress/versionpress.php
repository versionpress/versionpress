<?php

require_once(dirname(__FILE__) . '/storages/EntityStorage.php');
require_once(dirname(__FILE__) . '/storages/ObservableStorage.php');
require_once(dirname(__FILE__) . '/storages/DirectoryStorage.php');
require_once(dirname(__FILE__) . '/storages/EntityStorageFactory.php');
require_once(dirname(__FILE__) . '/storages/CommentStorage.php');
require_once(dirname(__FILE__) . '/storages/PostStorage.php');
require_once(dirname(__FILE__) . '/storages/SingleFileStorage.php');
require_once(dirname(__FILE__) . '/storages/OptionsStorage.php');
require_once(dirname(__FILE__) . '/storages/TermsStorage.php');
require_once(dirname(__FILE__) . '/storages/TermTaxonomyStorage.php');
require_once(dirname(__FILE__) . '/storages/UserStorage.php');
require_once(dirname(__FILE__) . '/storages/UserMetaStorage.php');
require_once(dirname(__FILE__) . '/database/DbSchemaInfo.php');
require_once(dirname(__FILE__) . '/database/MirroringDatabase.php');
require_once(dirname(__FILE__) . '/utils/IniSerializer.php');
require_once(dirname(__FILE__) . '/utils/Git.php');
require_once(dirname(__FILE__) . '/Mirror.php');
require_once(dirname(__FILE__) . '/ChangeInfo.php');

global $wpdb, $table_prefix;
$storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
$mirror = new Mirror($storageFactory);
$schemaInfo = new DbSchemaInfo();
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $mirror, $schemaInfo);


$buildCommitMessage = function (ChangeInfo $changeInfo) {
    // Samples:
    // Created post with ID 1
    // Edited post with ID 2
    // Deleted post with ID 3
    static $verbs = array(
        'create' => 'Created',
        'edit' => 'Edited',
        'delete' => 'Deleted'
    );

    return sprintf("%s %s with ID %s.", $verbs[$changeInfo->type], $changeInfo->entityType, $changeInfo->entityId);
};

add_action('save_post', createUpdatePostTermsHook($storageFactory->getStorage('posts')));

function createUpdatePostTermsHook(EntityStorage $storage) {

    return function ($postId) use ($storage) {
        $post = get_post($postId);
        $postType = $post->post_type;
        $taxonomies = get_object_taxonomies($postType);

        $postUpdateData = array('ID' => $postId);

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);
            if ($terms)
                $postUpdateData[$taxonomy] = array_map(function ($term) {
                    return $term->vp_id;
                }, $terms);
        }

        if (count($taxonomies) > 0)
            $storage->save($postUpdateData);
    };
}


register_shutdown_function(function () use ($mirror, $buildCommitMessage) {
    if ($mirror->wasAffected()) {
        $changeList = $mirror->getChangeList();

        $commitMessage = join(" ", array_map($buildCommitMessage, $changeList));

        Git::commit($commitMessage, dirname(__FILE__) . '/db');
    }
});