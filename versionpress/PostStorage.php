<?php

class PostStorage implements EntityStorage {

    private $directory;

    function __construct($directory) {
        $this->directory = $directory;
    }

    function save($data, $restriction) {
        $filename = $this->getFilename($data, $restriction);
        $oldSerializedPost = "";
        $isExistingPost = file_exists($filename);

        if(!$this->shouldBeSaved($isExistingPost, $data, $restriction))
            return;

        if ($isExistingPost) {
            $oldSerializedPost = file_get_contents($filename);
        }

        $post = $this->deserializePost($oldSerializedPost);
        $post = array_merge($post, $data);
        file_put_contents($filename, $this->serializePost($post));
    }

    function delete($restriction) {
        // TODO: Implement delete() method.
    }

    /**
     * @param $isExistingPost bool
     * @param $data array
     * @param $restriction array
     * @return bool
     */
    private function shouldBeSaved($isExistingPost, $data, $restriction) {
        if(isset($data['post_type']) && $data['post_type'] === 'revision')
            return false;

        if (!$isExistingPost && count($restriction) > 0) // update of non-existing post (probably revision)
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
        return IniSerializer::serialize($post);
    }
}

class IniSerializer {
    static function serialize($data) {
        $output = array();
        foreach ($data as $key => $value) {
            $output[] = "$key = " . (is_numeric($value) ? $value : '"' . $value . '"');
        }
        return implode("\r\n", $output);
    }

    static function deserialize($string) {
        return parse_ini_string($string);
    }
}