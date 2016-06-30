<?php

use Nette\Utils\Strings;
use VersionPress\Storages\DirectoryStorage;

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
