<?php

abstract class PostsAndCommentsBaseSynchronizer {

    /**
     * @var EntityStorage
     */
    protected $postStorage;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var wpdb
     */
    protected $database;

    /**
     * @var string
     */
    protected $idColumnName;

    function __construct(EntityStorage $postStorage, wpdb $database, $tableName, $idColumnName) {
        $this->postStorage = $postStorage;
        $this->database = $database;
        $this->tableName = $tableName;
        $this->idColumnName = $idColumnName;
    }

    function syncPosts() {
        $this->updatePostsInDatabase();
        $this->fixParentIds();
        $this->mirrorDatabaseToFiles();
    }

    protected function doAfterDatabaseUpdate() {

    }

    private function updatePostsInDatabase() {
        $posts = $this->loadAllPostsFromFiles();
        $postWithoutIDs = array_map(function($post){ unset($post[$this->idColumnName]); return $post; }, $posts);

        foreach ($postWithoutIDs as $post) {
            $sql = $this->buildInsertWithUpdateFallbackQuery($post);
            $this->database->query($sql);
        }

        $postVpIds = array_map(function($post){ return $post['vp_id'];  }, $posts);
        $sql = "DELETE FROM {$this->tableName} WHERE vp_id NOT IN (" . implode(', ', $postVpIds) . ")";
        $this->database->query($sql);

        $this->doAfterDatabaseUpdate();
    }

    private function loadAllPostsFromFiles() {
        $posts = $this->postStorage->loadAll();
        return $posts;
    }

    private function buildInsertWithUpdateFallbackQuery($data) {
        $columns = array_keys($data);
        $stringColumns = implode(', ', $columns);
        $safeValues = array_map(function($value){ return "\"$value\""; }, $data);
        $stringValues = implode(', ', $safeValues);
        $updatePairs = array_map(function($column) use ($safeValues){ return "$column = $safeValues[$column]"; }, $columns);
        $updateString = implode(', ', $updatePairs);

        $sql = "INSERT INTO {$this->tableName} ($stringColumns) VALUES ($stringValues)
                ON DUPLICATE KEY UPDATE $updateString";

        return $sql;
    }

    private function fixParentIds() {
        $sql = "SELECT {$this->idColumnName}, post_parent, vp_id, vp_parent_id FROM {$this->tableName}";
        $posts = $this->database->get_results($sql);
        $vpId_ID_map = array();
        foreach($posts as $post) {
            $vpId_ID_map[$post->vp_id] = $post->{$this->idColumnName};
        }

        foreach($posts as $post) {
            $newParent = 0;
            if($post->vp_parent_id != 0){
                $newParent = $vpId_ID_map[$post->vp_parent_id];
            }
            if($post->post_parent != $newParent) {
                $updateSql = "UPDATE {$this->tableName} SET post_parent = $newParent WHERE {$this->idColumnName} = " . $post->{$this->idColumnName};
                $this->database->query($updateSql);
            }
        }
    }

    private function mirrorDatabaseToFiles() {
        $postsInDatabase = $this->loadAllPostsFromDatabase();
        $postsInFiles = $this->loadAllPostsFromFiles();

        $getPostId = function($post){ return $post[$this->idColumnName]; };

        $dbPostIds = array_map($getPostId, $postsInDatabase);
        $filePostIds = array_map($getPostId, $postsInFiles);

        $deletedPostIds =  array_diff($filePostIds, $dbPostIds);

        foreach($deletedPostIds as $deletedPostId) {
            $this->postStorage->delete(array($this->idColumnName => $deletedPostId));
        }

        $this->postStorage->saveAll($postsInDatabase);
    }

    private function loadAllPostsFromDatabase() {
        $sql = "SELECT * FROM {$this->tableName}";
        return $this->database->get_results($sql, ARRAY_A);
    }
}