<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');

class PostSynchronizer {


    function syncPosts() {
        $posts = $this->loadAllPostsFromFiles();
        $this->updatePostsInDatabase($posts);
        $this->fixParentIds();
        $this->mirrorDatabaseToFiles();
    }

    private function updatePostsInDatabase($posts) {
        global $table_prefix, $wpdb;
        $postWithoutIDs = array_map(function($post){ unset($post['ID']); return $post; }, $posts);

        foreach ($postWithoutIDs as $post) {
            $sql = $this->buildInsertWithUpdateFallbackQuery($table_prefix . 'posts', $post);
            $wpdb->query($sql);
        }

        $postVpIds = array_map(function($post){ return $post['vp_id'];  }, $posts);
        $sql = "DELETE FROM wp_posts WHERE vp_id NOT IN (" . implode(', ', $postVpIds) . ")";
        $wpdb->query($sql);
    }

    private function loadAllPostsFromFiles() {
        $postStorage = $this->getPostStorage();
        $posts = $postStorage->loadAll();
        return $posts;
    }

    private function getPostStorage() {
        $storageFactory = new EntityStorageFactory(VERSIONPRESS_MIRRORING_DIR);
        $postStorage = $storageFactory->getStorage('posts');
        return $postStorage;
    }

    private function buildInsertWithUpdateFallbackQuery($table, $data) {
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

    private function fixParentIds() {
        global $wpdb;
        $sql = "SELECT ID, post_parent, vp_id, vp_parent_id FROM wp_posts";
        $posts = $wpdb->get_results($sql);
        $vpId_ID_map = array();
        foreach($posts as $post) {
            $vpId_ID_map[$post->vp_id] = $post->ID;
        }

        foreach($posts as $post) {
            $newParent = 0;
            if($post->vp_parent_id != 0){
                $newParent = $vpId_ID_map[$post->vp_parent_id];
            }
            if($post->post_parent != $newParent) {
                $updateSql = "UPDATE wp_posts SET post_parent = $newParent WHERE ID = $post->ID";
                $wpdb->query($updateSql);
            }
        }
    }

    private function mirrorDatabaseToFiles() {
        $postsInDatabase = $this->loadAllPostsFromDatabase();
        $postsInFiles = $this->loadAllPostsFromFiles();

        $getPostId = function($post){ return $post['ID']; };

        $dbPostIds = array_map($getPostId, $postsInDatabase);
        $filePostIds = array_map($getPostId, $postsInFiles);

        $deletedPostIds =  array_diff($filePostIds, $dbPostIds);
        $postStorage = $this->getPostStorage();

        foreach($deletedPostIds as $deletedPostId) {
            $postStorage->delete(array('ID' => $deletedPostId));
        }

        $postStorage->saveAll($postsInDatabase);
    }

    private function loadAllPostsFromDatabase() {
        global $wpdb;
        $sql = "SELECT * FROM wp_posts";
        return $wpdb->get_results($sql, ARRAY_A);
    }
}

$postSynchronizer = new PostSynchronizer();
$postSynchronizer->syncPosts();