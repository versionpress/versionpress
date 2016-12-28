<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Actions\ActionsInfo;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Tests\Utils\HookMock;

class StorageTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        HookMock::setUp(HookMock::WP_MOCK);
    }

    protected function tearDown()
    {
        HookMock::tearDown();
    }

    /**
     * @param array $fields
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityInfo
     */
    protected function createEntityInfoMock($fields, $methods)
    {
        $entityInfo = $this->getMockBuilder(EntityInfo::class)->disableOriginalConstructor()->getMock();

        foreach ($fields as $field => $value) {
            $entityInfo->{$field} = $value;
        }

        foreach ($methods as $methodName => $returnValue) {
            $entityInfo->expects($this->any())->method($methodName)->will($this->returnValue($returnValue));
        }

        return $entityInfo;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ChangeInfoFactory
     */
    protected function createChangeInfoFactoryMock()
    {
        $entityChangeInfo = $this->getMockBuilder(EntityChangeInfo::class)->disableOriginalConstructor()->getMock();

        $changeInfoFactory = $this->getMockBuilder(ChangeInfoFactory::class)->disableOriginalConstructor()->getMock();
        $changeInfoFactory->expects($this->any())->method('createEntityChangeInfo')->will($this->returnValue($entityChangeInfo));

        return $changeInfoFactory;
    }
}
