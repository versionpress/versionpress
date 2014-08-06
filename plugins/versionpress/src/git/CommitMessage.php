<?php

class CommitMessage {

    private $head;
    private $body;

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

    public function getVersionPressTags() {
        $tagLines = array_filter(array_map("trim", explode("\n", $this->getBody())), function ($line) {
                return NStrings::startsWith($line, "VP-");
            });
        $tags = array();
        foreach ($tagLines as $line) {
            list($key, $value) = array_map("trim", explode(":", $line, 2));
            $tags[$key] = $value;
        }
        return $tags;
    }
}