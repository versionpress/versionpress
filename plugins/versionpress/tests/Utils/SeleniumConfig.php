<?php

namespace VersionPress\Tests\Utils;

class SeleniumConfig
{

    /**
     * Host where Selenium hub or server runs
     */
    public $host;

    /**
     * How long to wait in Selenium tests after a commit before doing asserts against this commit
     *
     * @var int Milliseconds
     */
    public $postCommitWaitTime;
}
