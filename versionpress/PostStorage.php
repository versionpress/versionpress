<?php

class PostStorage implements EntityStorage {

    private $directory;

    function __construct($directory) {
        $this->directory = $directory;
    }

    function save($data, $restriction) {
        $filename = $this->getFilename($data, $restriction);
        $oldSerializedPost = "";
        if (file_exists($filename))
            $oldSerializedPost = file_get_contents($filename);

        $post = $this->deserializePost($oldSerializedPost);
        $post = array_merge($post, $data);
        file_put_contents($filename, $this->serializePost($post));
    }

    function delete($restriction) {
        // TODO: Implement delete() method.
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