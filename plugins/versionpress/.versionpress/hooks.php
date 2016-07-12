<?php

use Nette\Utils\Strings;
use VersionPress\DI\VersionPressServices;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Utils\EntityUtils;
use VersionPress\Utils\StringUtils;

add_filter('vp_entity_should_be_saved_post', function ($shouldBeSaved, $data, $storage) {
    /** @var DirectoryStorage $storage */
    $isExistingEntity = $storage->entityExistedBeforeThisRequest($data);

    // ignore saving draft on preview
    if ($isExistingEntity && isset($_POST['wp-preview']) && $_POST['wp-preview'] === 'dopreview') {
        return false;
    }

    // ignoring ajax autosaves
    if ($isExistingEntity && isset($data['post_status']) && ($data['post_status'] === 'draft' &&
            defined('DOING_AJAX') && DOING_AJAX === true)
    ) {
        return false;
    }

    if (!$isExistingEntity && isset($data['post_type']) && $data['post_type'] === 'attachment' &&
        !isset($data['post_title'])
    ) {
        return false;
    }

    return $shouldBeSaved;

}, 10, 3);

add_filter('vp_entity_should_be_saved_comment', function ($shouldBeSaved, $data, $storage) {
    /** @var DirectoryStorage $storage */
    $isExistingEntity = $storage->entityExistedBeforeThisRequest($data);

    if ($isExistingEntity && isset($data['comment_approved']) && $data['comment_approved'] === 'spam') {
        return true;
    }

    return $shouldBeSaved;
}, 10, 3);

add_filter('vp_entity_should_be_saved_option', function ($shouldBeSaved, $data, $storage) {
    global $wp_taxonomies;

    $name = $data['option_name'];
    $taxonomies = array_keys((array)$wp_taxonomies);

    $taxonomyChildrenSuffix = '_children';
    if (!Strings::endsWith($name, $taxonomyChildrenSuffix)) {
        return $shouldBeSaved;
    }

    $maybeTaxonomyName = Strings::substring($name, 0, Strings::length($name) - Strings::length($taxonomyChildrenSuffix));

    return !in_array($maybeTaxonomyName, $taxonomies);
}, 10, 3);


// === Action filters ===

add_filter('vp_entity_action_post', function ($action, $oldEntity, $newEntity) {

    if ($action === 'edit') { // determine more specific edit action

        $diff = EntityUtils::getDiff($oldEntity, $newEntity);

        if (isset($diff['post_status']) && $diff['post_status'] === 'trash') {
            return 'trash';
        }

        if (isset($diff['post_status']) && $oldEntity['post_status'] === 'trash') {
            return 'untrash';
        }

        if (isset($diff['post_status']) && $oldEntity['post_status'] === 'draft' && $newEntity['post_status'] === 'publish') {
            return 'publish';
        }
    }

    if ($action == 'create' && $newEntity['post_status'] === 'draft') {
        $action = 'draft';
    }

    return $action;

}, 10, 3);

add_filter('vp_entity_files_post', function ($files, $oldEntity, $newEntity) {

    $postType = isset($newEntity['post_type']) ? $newEntity['post_type'] : $oldEntity['post_type'];

    if ($postType !== "attachment") {
        return $files;
    }

    $uploadDir = wp_upload_dir();
    $files[] = ["type" => "path", "path" => path_join($uploadDir['basedir'], '*')];

    return $files;
}, 10, 3);

add_filter('vp_bulk_change_description_post', function ($description, $action, $count, $tags) {

    $postType = $tags[0]['VP-Post-Type'];
    $postTypePlural = StringUtils::pluralize($postType);

    if ($postType === "nav_menu_item") {
        return "Updated menu items";
    }

    switch ($action) {
        case "trash":
            return "Moved $count $postTypePlural to trash";
        case "untrash":
            return "Moved $count $postTypePlural from trash";
        case "edit":
            return "Updated $count $postTypePlural";
    }

    return $description;

}, 10, 4);

add_filter('vp_entity_files_option', function ($files, $oldEntity, $newEntity) {

    $optionName = isset($newEntity['option_name']) ? $newEntity['option_name'] : $oldEntity['option_name'];

    if ($optionName === 'rewrite_rules') {
        $files[] = ["type" => "path", "path" => ABSPATH . '/.htaccess'];
    }

    return $files;
}, 10, 3);

add_filter('vp_entity_action_comment', function ($action, $oldEntity, $newEntity) {

    if ($action === 'create' && $newEntity['comment_approved'] == 0) {
        return 'create-pending';
    }

    if ($action !== 'edit') {
        return $action;
    }

    $diff = EntityUtils::getDiff($oldEntity, $newEntity);

    if (!isset($diff['comment_approved'])) {
        return $action;
    }

    if (($oldEntity['comment_approved'] === 'trash' && $newEntity['comment_approved'] === 'post-trashed') ||
        ($oldEntity['comment_approved'] === 'post-trashed' && $newEntity['comment_approved'] === 'trash')
    ) {
        return 'edit'; // trash -> post-trashed and post-trashed -> trash are not interesting action for us
    }

    if ($diff['comment_approved'] === 'trash') {
        return 'trash';
    }

    if ($oldEntity['comment_approved'] === 'trash') {
        return 'untrash';
    }

    if ($diff['comment_approved'] === 'spam') {
        return 'spam';
    }

    if ($oldEntity['comment_approved'] === 'spam') {
        return 'unspam';
    }

    if ($oldEntity['comment_approved'] == 0 && $newEntity['comment_approved'] == 1) {
        return 'approve';
    }

    if ($oldEntity['comment_approved'] == 1 && $newEntity['comment_approved'] == 0) {
        return 'unapprove';
    }

    return $action;

}, 10, 3);

add_filter('vp_entity_tags_comment', function ($tags, $oldEntity, $newEntity) {

    global $versionPressContainer;

    /** @var \VersionPress\Database\Database $database */
    $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);

    $postId = isset($newEntity['vp_comment_post_ID']) ? $newEntity['vp_comment_post_ID'] : $oldEntity['vp_comment_post_ID'];

    $result = $database->get_row("SELECT post_title FROM {$database->posts} " .
        "JOIN {$database->vp_id} ON {$database->posts}.ID = {$database->vp_id}.id " .
        "WHERE vp_id = UNHEX('$postId')");

    $tags['VP-Comment-PostTitle'] = $result->post_title;

    return $tags;

}, 10, 3);

add_filter('vp_bulk_change_description_comment', function ($description, $action, $count) {

    switch ($action) {
        case "trash":
            return "Moved $count comments into trash";
        case "untrash":
            return "Moved $count comments from trash";
        case "spam":
            return "Marked $count comments as spam";
        case "unspam":
            return "Marked $count comments as not spam";
    }

    return $description;
}, 10, 3);

add_filter('vp_entity_action_term', function ($action, $oldEntity, $newEntity) {

    if ($action === 'edit' && $oldEntity['name'] !== $newEntity['name']) {
        return 'rename';
    }

    return $action;

}, 10, 3);

add_filter('vp_entity_tags_term', function ($tags, $oldEntity, $newEntity, $action) {

    if ($action === 'rename') {
        $tags['VP-Term-OldName'] = $oldEntity['name'];
    }

    return $tags;

}, 10, 3);

add_filter('vp_bulk_change_description_term', function ($description, $action, $count, $tags) {

    if ($this->getAction() === "delete") {
        $taxonomy = str_replace("_", " ", $tags[0]['VP-Term-Taxonomy']);
        $taxonomies = StringUtils::pluralize($taxonomy);
        return "Deleted $count $taxonomies";
    }

    return $description;
}, 10, 4);

add_filter('vp_entity_tags_term_taxonomy', function ($tags, $oldEntity, $newEntity, $action) {

    global $versionPressContainer;
    /** @var \VersionPress\Storages\StorageFactory $storageFactory */
    $storageFactory = $versionPressContainer->resolve(VersionPressServices::STORAGE_FACTORY);
    /** @var \VersionPress\Storages\DirectoryStorage $termStorage */
    $termStorage = $storageFactory->getStorage('term');

    $termId = isset($newEntity['vp_term_id']) ? $newEntity['vp_term_id'] : $oldEntity['vp_term_id'];

    $term = $termStorage->loadEntity($termId);
    $tags['VP-Term-Name'] = $term['name'];

    return $tags;

}, 10, 3);

add_filter('vp_entity_files_term_taxonomy', function ($files, $oldEntity, $newEntity) {

    $files[] = [
        "type" => "all-storage-files",
        "entity" => "option"
    ]; // sometimes term change can affect option (e.g. deleting menu)

    return $files;
}, 10, 3);

add_filter('vp_bulk_change_description_composer', function ($description, $action, $count) {
    return sprintf("%s %d Composer packages", Strings::capitalize(StringUtils::verbToPastTense($action)), $count);
}, 10, 3);


add_filter('vp_bulk_change_description_revert', function ($description, $action, $count) {

    if ($action === 'undo' || $action === 'rollback') {
        return "Reverted" . " $count changes";
    }

    return $description;
}, 10, 3);

add_filter('vp_meta_entity_tags_postmeta', function ($tags, $oldEntity, $newEntity, $action, $oldParent, $newParent) {

    $tags['VP-Post-Type'] = isset($newParent['post_type']) ? $newParent['post_type'] : $oldParent['post_type'];
    $tags['VP-Post-Title'] = isset($newParent['post_title']) ? $newParent['post_title'] : $oldParent['post_title'];

    return $tags;
}, 10, 5);

add_filter('vp_entity_change_description_postmeta', function ($message, $action, $vpid, $tags) {

    if ($tags['VP-PostMeta-Key'] === "_thumbnail_id") { // featured image

        $verbs = [
            'create' => 'Set',
            'edit' => 'Changed',
            'delete' => 'Removed',
        ];

        $verb = $verbs[$action];

        return sprintf("%s featured image for %s '%s'", $verb, $tags['VP-Post-Type'], $tags['VP-Post-Title']);
    }

    return $message;
}, 10, 4);
