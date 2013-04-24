<?php

class PostStorage implements EntityStorage {

    private $directory;

    function __construct($directory) {
        $this->directory = $directory;
    }

    function save($data, $restriction = array()) {
        $filename = $this->getFilename($data, $restriction);
        $oldSerializedPost = "";
        $isExistingPost = file_exists($filename);

        if(!$this->shouldBeSaved($isExistingPost, $data))
            return;

        if ($isExistingPost) {
            $oldSerializedPost = file_get_contents($filename);
        }

        $post = $this->deserializePost($oldSerializedPost);
        $post = array_merge($post, $data);
        file_put_contents($filename, $this->serializePost($post));
    }

    function delete($restriction) {
        $fileName = $this->getFilename(array(), $restriction);
        unlink($fileName);
    }

    function loadAll() {
        $postFiles = $this->getPostFiles();
        $posts = $this->loadAllFromFiles($postFiles);
        return $posts;
    }

    function saveAll($posts) {
        foreach($posts as $post) {
            $this->save($post);
        }
    }

    private function shouldBeSaved($isExistingPost, $data) {
        if(isset($data['post_type']) && $data['post_type'] === 'revision')
            return false;

        if(isset($data['post_status']) && $data['post_status'] === 'auto-draft')
            return false;

        if (!$isExistingPost && !isset($data['post_type']))
            return false;

        return true;
    }

    private function getFilename($data, $restriction) {
        $id = isset($data['ID']) ? $data['ID'] : $restriction['ID'];
        return $this->directory . '/' . $id . '.txt';
    }

    private function deserializePost($oldSerializedPost) {
        return IniSerializer::deserialize($oldSerializedPost);
    }

    private function serializePost($post) {
        $post = $this->removeUnwantedColumns($post);
        return IniSerializer::serialize($post);
    }

    private function getPostFiles() {
        if(!is_dir($this->directory))
            return array();
        $excludeList = array('.', '..');
        $files = scandir($this->directory);

        return array_diff($files, $excludeList);
    }

    private function loadAllFromFiles($postFiles) {
        $that = $this;
        return array_map(function($postFile) use ($that){
            return $that->deserializePost(file_get_contents($that->directory . '/' . $postFile));
        }, $postFiles);
    }

    private function removeUnwantedColumns($post) {
        static $excludeList = array('comment_count');
        foreach($excludeList as $excludeKey) {
            unset($post[$excludeKey]);
        }

        return $post;
    }
}