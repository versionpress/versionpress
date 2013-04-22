<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');


function syncPosts() {
    global $table_prefix, $wpdb;
    $storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
    $postStorage = $storageFactory->getStorage('posts');
    $posts = $postStorage->loadAll();
    $postWithoutIDs = array_map(function($post){ unset($post['ID']); return $post; }, $posts);

    foreach($postWithoutIDs as $post) {
        $sql = buildInsertWithUpdateFallbackQuery($table_prefix . 'posts', $post);
        $wpdb->query($sql);
    }
}

function buildInsertWithUpdateFallbackQuery($table, $data) {
    $columns = array_keys($data);
    $stringColumns = implode(', ', $columns);
    $safeValues = array_map(function($value){ return "\"$value\""; }, $data);
    $stringValues = implode(', ', $safeValues);
    $updatePairs = array_map(function($column) use ($safeValues){ return "$column = $safeValues[$column]"; }, $columns);
    $updateString = implode(', ', $updatePairs);

    $sql = "INSERT INTO $table ($stringColumns) VALUES ($stringValues)
                ON DUPLICATE KEY UPDATE $updateString";

    return $sql;
}

syncPosts();