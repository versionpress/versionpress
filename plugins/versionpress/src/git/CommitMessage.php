<?php

class CommitMessage {

    private $head;
    private $body;
    private $tags;

    function __construct($head, $body = null) {
        $this->head = $head;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getHead() {
        return $this->head;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Returns all VP tags in associative array.
     * tagName => value
     *
     * @return array
     */
    public function getVersionPressTags() {
        if(!$this->tags) {
            $tagLines = array_filter(
                array_map("trim", explode("\n", $this->getBody())),
                function ($line) {
                    return NStrings::startsWith($line, "VP-");
                }
            );
            $tags = array();
            foreach ($tagLines as $line) {
                list($key, $value) = array_map("trim", explode(":", $line, 2));
                $tags[$key] = $value;
            }

            $this->tags = $tags;
        }
        return $this->tags;
    }

    /**
     * Returns tag with given name.
     *
     * @param $tagName
     * @return string
     */
    public function getVersionPressTag($tagName) {
        $tags = $this->getVersionPressTags();
        return $tags[$tagName];
    }
}