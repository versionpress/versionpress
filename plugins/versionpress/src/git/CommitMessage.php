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
    public function getBody() {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getHead() {
        return $this->head;
    }
}