<?php

class CommentStorage extends DirectoryStorage implements EntityStorage {

    function __construct($directory) {
        parent::__construct($directory, 'comment', 'comment_ID');
    }
}