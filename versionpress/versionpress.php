<?php

require_once(dirname(__FILE__) . '/EntityStorage.php');
require_once(dirname(__FILE__) . '/ObservableStorage.php');
require_once(dirname(__FILE__) . '/DirectoryStorage.php');
require_once(dirname(__FILE__) . '/EntityStorageFactory.php');
require_once(dirname(__FILE__) . '/Mirror.php');
require_once(dirname(__FILE__) . '/MirroringDatabase.php');
require_once(dirname(__FILE__) . '/PostStorage.php');
require_once(dirname(__FILE__) . '/CommentStorage.php');
require_once(dirname(__FILE__) . '/SingleFileStorage.php');
require_once(dirname(__FILE__) . '/OptionsStorage.php');
require_once(dirname(__FILE__) . '/IniSerializer.php');
require_once(dirname(__FILE__) . '/Git.php');
require_once(dirname(__FILE__) . '/ChangeInfo.php');

global $wpdb, $table_prefix;
$mirror = new Mirror(new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR));
$wpdb = new MirroringDatabase(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, $mirror);


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


register_shutdown_function(function () use ($mirror, $buildCommitMessage) {
    if ($mirror->wasAffected()) {
        $changeList = $mirror->getChangeList();

        $commitMessage = join(" ", array_map($buildCommitMessage, $changeList));

        Git::commit($commitMessage, dirname(__FILE__) . '/db');
    }
});