<?php

interface ChangeInfo {
    /**
     * @return string
     */
    function getObjectType();

    /**
     * @return string
     */
    function getAction();

    /**
     * @return CommitMessage
     */
    function getCommitMessage();
}