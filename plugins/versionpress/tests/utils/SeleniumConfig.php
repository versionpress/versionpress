<?php


class SeleniumConfig {

    /**
     * Path to a Firefox binary. Null means that system Firefox should be used instead.
     *
     * @var string
     */
    public $firefoxBinary;

    /**
     * How long to wait in Selenium tests after a commit before doing asserts against this commit
     *
     * @var int Milliseconds
     */
    public $postCommitWaitTime;
}
