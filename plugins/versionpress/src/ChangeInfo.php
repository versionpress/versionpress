<?php

class ChangeInfo {

    /**
     * Post, comment etc.
     * @var string
     */
    public $entityType;

    /**
     * ID in database
     * @var int
     */
    public $entityId;

    /**
     * Allowed values: create, edit, delete
     * @var string
     */
    public $type;
}