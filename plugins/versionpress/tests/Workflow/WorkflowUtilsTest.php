<?php

namespace VersionPress\Tests\Workflow;

use VersionPress\Utils\WorkflowUtils;

class WorkflowUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function isCloneNameValidReturnsTrueForValidNames()
    {
        $this->assertTrue(WorkflowUtils::isCloneNameValid('a'));
        $this->assertTrue(WorkflowUtils::isCloneNameValid('_'));
        $this->assertTrue(WorkflowUtils::isCloneNameValid('-'));
        $this->assertTrue(WorkflowUtils::isCloneNameValid('1'));
        $this->assertTrue(WorkflowUtils::isCloneNameValid('abc123_-'));
    }

    /**
     * @test
     */
    public function isCloneNameValidReturnsFalseForInvalidNames()
    {
        $this->assertFalse(WorkflowUtils::isCloneNameValid('**'));
        $this->assertFalse(WorkflowUtils::isCloneNameValid(''));
        $this->assertFalse(WorkflowUtils::isCloneNameValid('abc**'));
    }
}
