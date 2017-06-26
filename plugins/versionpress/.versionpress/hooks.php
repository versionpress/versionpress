<?php

use Nette\Utils\Strings;
use VersionPress\Actions\ActionsDefinitionRepository;
use VersionPress\Actions\ActivePluginsVPFilesIterator;
use VersionPress\ChangeInfos\CommitMessageParser;
use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\VpidRepository;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitRepository;
use VersionPress\Initialization\WpdbReplacer;
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
            defined('DOING_AJAX') && DOING_AJAX === true && $_POST['action'] === 'heartbeat')
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

add_filter('vp_entity_tags_post', function ($tags, $oldEntity, $newEntity) {

    global $versionPressContainer;

    /** @var VpidRepository $vpidRepository */
    $vpidRepository = $versionPressContainer->resolve(VersionPressServices::VPID_REPOSITORY);

    $postVpid = isset($newEntity['vp_id']) ? $newEntity['vp_id'] : $oldEntity['vp_id'];
    $postId = $vpidRepository->getIdForVpid($postVpid);

    $postFormat = get_post_format($postId);
    $tags['VP-Post-Format'] = $postFormat ?: $tags['VP-Post-Type'];

    return $tags;
}, 10, 3);

add_filter('vp_entity_files_option', function ($files, $oldEntity, $newEntity) {

    $optionName = isset($newEntity['option_name']) ? $newEntity['option_name'] : $oldEntity['option_name'];

    if ($optionName === 'rewrite_rules') {
        $files[] = ["type" => "path", "path" => ABSPATH . '/.htaccess'];
    }

    return $files;
}, 10, 3);

add_filter('vp_action_priority_option', function ($originalPriority, $action, $optionName, $entity) {
    if ($optionName === 'WPLANG' && $action === 'create' && $entity['option_value'] === '') {
        return 20;
    }

    return $originalPriority;
}, 10, 4);

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

    /** @var Database $database */
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
    global $versionPressContainer;
    /** @var VpidRepository $vpidRepository */
    $vpidRepository = $versionPressContainer->resolve(VersionPressServices::VPID_REPOSITORY);

    if ($action === 'rename') {
        $tags['VP-Term-OldName'] = $oldEntity['name'];
    }

    $termVpid = $newEntity ? $newEntity['vp_id'] : $oldEntity['vp_id'];
    $termId = $vpidRepository->getIdForVpid($termVpid);

    $term = get_term($termId);
    $tags['VP-Term-Taxonomy'] = $term instanceof WP_Term ? $term->taxonomy : 'term';

    return $tags;
}, 10, 4);

add_filter('vp_bulk_change_description_term', function ($description, $action, $count, $tags) {

    if ($action === "delete") {
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
}, 10, 4);

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
}, 10, 6);

add_filter('vp_meta_entity_files_postmeta', function ($files, $oldEntity, $newEntity, $oldParentEntity, $newParentEntity) {

    $postType = isset($newParentEntity['post_type']) ? $newParentEntity['post_type'] : $oldParentEntity['post_type'];

    if ($postType !== "attachment") {
        return $files;
    }

    $uploadDir = wp_upload_dir();
    $files[] = ["type" => "path", "path" => path_join($uploadDir['basedir'], '*')];

    return $files;
}, 10, 5);


add_filter('vp_action_description_postmeta', function ($message, $action, $vpid, $tags) {

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

add_filter('vp_meta_entity_tags_usermeta', function ($tags, $oldEntity, $newEntity, $action, $oldParent, $newParent) {

    $tags['VP-User-Login'] = isset($newParent['user_login']) ? $newParent['user_login'] : $oldParent['user_login'];

    return $tags;
}, 10, 6);


add_filter('vp_entity_files_composer', function ($files) {
    $files[] = ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'];
    $files[] = ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock'];
    return $files;
});

add_action('vp_wordpress_updated', function ($version) {
    global $versionPressContainer;

    $wpFiles = [
        // All files from WP root
        // Git can't add only files from current directory (non-recursively), so we have to add them manually.
        // It should be OK because the list of files didn't change since at least Jan 2013.
        ["type" => "path", "path" => "index.php"],
        ["type" => "path", "path" => "license.txt"],
        ["type" => "path", "path" => "readme.html"],
        ["type" => "path", "path" => "wp-activate.php"],
        ["type" => "path", "path" => "wp-blog-header.php"],
        ["type" => "path", "path" => "wp-comments-post.php"],
        ["type" => "path", "path" => "wp-config-sample.php"],
        ["type" => "path", "path" => "wp-cron.php"],
        ["type" => "path", "path" => "wp-links-opml.php"],
        ["type" => "path", "path" => "wp-load.php"],
        ["type" => "path", "path" => "wp-login.php"],
        ["type" => "path", "path" => "wp-mail.php"],
        ["type" => "path", "path" => "wp-settings.php"],
        ["type" => "path", "path" => "wp-signup.php"],
        ["type" => "path", "path" => "wp-trackback.php"],
        ["type" => "path", "path" => "xmlrpc.php"],

        // wp-includes and wp-admin directories
        ["type" => "path", "path" => ABSPATH . WPINC . '/*'],
        ["type" => "path", "path" => ABSPATH . 'wp-admin/*'],

        // WP themes - we bet that all WP themes begin with "twenty"
        ["type" => "path", "path" => WP_CONTENT_DIR . '/themes/twenty*'],

        // Translations
        ["type" => "path", "path" => WP_CONTENT_DIR . '/languages/*'],

        // Database Schema
        ["type" => "path", "path" => VP_VPDB_DIR . '/.schema/*'],

        // Composer files
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'],
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock'],
    ];

    /** @var DbSchemaInfo $dbSchema */
    $dbSchema = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    $tableSchemaStorage = $versionPressContainer->resolve(VersionPressServices::TABLE_SCHEMA_STORAGE);
    $dbSchema->refreshDbSchema(new ActivePluginsVPFilesIterator('schema.yml'));

    vp_update_table_ddl_scripts($dbSchema, $tableSchemaStorage);

    vp_force_action('wordpress', 'update', $version, [], $wpFiles);

    if (!WpdbReplacer::isReplaced()) {
        WpdbReplacer::replaceMethods();
    }
});

add_action('vp_plugin_changed', function ($action, $pluginFile, $pluginName) {
    global $versionPressContainer;

    /** @var DbSchemaInfo $dbSchema */
    $dbSchema = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    $tableSchemaStorage = $versionPressContainer->resolve(VersionPressServices::TABLE_SCHEMA_STORAGE);
    $dbSchema->refreshDbSchema(new ActivePluginsVPFilesIterator('schema.yml'));

    vp_update_table_ddl_scripts($dbSchema, $tableSchemaStorage);

    if ($action !== 'delete') {
        /** @var ActionsDefinitionRepository $actionsDefinitionRepository */
        $actionsDefinitionRepository = $versionPressContainer->resolve(VersionPressServices::ACTIONS_DEFINITION_REPOSITORY);
        $actionsDefinitionRepository->saveDefinitionForPlugin($pluginFile);
    }

    $pluginPath = WP_PLUGIN_DIR . "/";
    if (dirname($pluginFile) === ".") {
        // single-file plugin like hello.php
        $pluginPath .= $pluginFile;
    } else {
        // multi-file plugin like akismet/...
        $pluginPath .= dirname($pluginFile) . "/*";
    }

    $pluginChange = ["type" => "path", "path" => $pluginPath];
    $vpdbChanges = ["type" => "path", "path" => VP_VPDB_DIR];

    $uploadsChanges = ["type" => "path", "path" => path_join(wp_upload_dir()['basedir'], '*')];

    $composerChanges = [
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'],
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock'],
    ];

    $filesToCommit = array_merge([$pluginChange, $vpdbChanges, $uploadsChanges], $composerChanges);

    vp_force_action('plugin', $action, $pluginFile, ['VP-Plugin-Name' => $pluginName], $filesToCommit);
}, 10, 3);


add_action('vp_theme_changed', function ($action, $stylesheet, $themeName) {
    global $versionPressContainer;

    $themeChange = ["type" => "path", "path" => $path = WP_CONTENT_DIR . "/themes/" . $stylesheet . "/*"];
    $optionChange = ["type" => "all-storage-files", "entity" => "option"];
    $composerChanges = [
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.json'],
        ["type" => "path", "path" => VP_PROJECT_ROOT . '/composer.lock'],
    ];

    $filesToCommit = array_merge([$themeChange, $optionChange], $composerChanges);

    $dbSchema = $versionPressContainer->resolve(VersionPressServices::DB_SCHEMA);
    $tableSchemaStorage = $versionPressContainer->resolve(VersionPressServices::TABLE_SCHEMA_STORAGE);
    $dbSchema->refreshDbSchema(new ActivePluginsVPFilesIterator('schema.yml'));

    vp_update_table_ddl_scripts($dbSchema, $tableSchemaStorage);

    vp_force_action('theme', $action, $stylesheet, ['VP-Theme-Name' => $themeName], $filesToCommit);
}, 10, 3);


add_action('vp_translation_changed', function ($action, $languageCode, $type = 'core', $name = null) {
    require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
    $translations = wp_get_available_translations();
    $languageName = isset($translations[$languageCode]) ? $translations[$languageCode]['native_name'] : 'English (United States)';

    $tags = [
        'VP-Language-Code' => $languageCode,
        'VP-Language-Name' => $languageName,
        'VP-Translation-Type' => $type,
    ];

    if ($name) {
        $tags['VP-Translation-Name'] = $name;
    }

    $path = WP_CONTENT_DIR . "/languages/";

    if ($type === "core") {
        $path .= "*";
    } else {
        $path .= $type . "s/" . $name . "-" . $languageCode . ".*";
    }

    $filesChange = ["type" => "path", "path" => $path];
    $optionChange = ["type" => "all-storage-files", "entity" => "option"];

    $files = [$filesChange, $optionChange];

    vp_force_action('translation', $action, null, $tags, $files);
}, 10, 4);


add_action('vp_versionpress_changed', function ($action, $version) {
    if ($action === 'deactivate') {
        $files = [
            ["type" => "path", "path" => VP_VPDB_DIR . "/*"],
            ["type" => "path", "path" => ABSPATH . WPINC . "/wp-db.php"],
            ["type" => "path", "path" => ABSPATH . WPINC . "/wp-db.php.original"],
            ["type" => "path", "path" => ABSPATH . "/.gitattributes"],
        ];
    } else {
        $files = [["type" => "path", "path" => "*"]];
    }

    vp_force_action('versionpress', $action, $version, [], $files);
}, 10, 2);


add_filter('vp_action_description_versionpress', function ($message, $action, $commitHash) {
    if ($action !== 'undo' && $action !== 'rollback') {
        return $message;
    }

    global $versionPressContainer;
    /** @var GitRepository $gitRepository */
    $gitRepository = $versionPressContainer->resolve(VersionPressServices::GIT_REPOSITORY);
    /** @var CommitMessageParser $commitMessageParser */
    $commitMessageParser = $versionPressContainer->resolve(VersionPressServices::COMMIT_MESSAGE_PARSER);

    $revertedCommit = $gitRepository->getCommit($commitHash);

    if ($action === 'undo') {
        $changeInfo = $commitMessageParser->parse($revertedCommit->getMessage());
        $message = str_replace('%/commit-message/%', $changeInfo->getChangeDescription(), $message);
    } elseif ($action === 'rollback') {
        $message = str_replace('%/commit-date/%', $revertedCommit->getDate()->format('d-M-y H:i:s'), $message);
    }

    return $message;
}, 10, 3);

add_action('vp_before_synchronization_term_taxonomy', function () {
    global $versionPressContainer;

    /** @var Database $database */
    $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
    $database->query("drop index term_id_taxonomy on {$database->term_taxonomy}");
});

add_action('vp_after_synchronization_term_taxonomy', function () {
    global $versionPressContainer;

    /** @var Database $database */
    $database = $versionPressContainer->resolve(VersionPressServices::DATABASE);
    $database->query("create unique index term_id_taxonomy on {$database->term_taxonomy}(term_id, taxonomy)");
});
