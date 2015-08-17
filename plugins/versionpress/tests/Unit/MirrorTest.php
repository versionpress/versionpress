<?php

namespace VersionPress\Tests\Unit;


use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;

class MirrorTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
    public function mirrorCallsTheReplacerOnSavingEntity() {
        $fakeStorageFactory = $this->getMockBuilder('VersionPress\Storages\StorageFactory')->disableOriginalConstructor()->getMock();
        $fakeReplacer = $this->getMockBuilder('VersionPress\Utils\AbsoluteUrlReplacer')->disableOriginalConstructor()->getMock();

        $fakeStorageFactory->expects($this->any())->method('getStorage')->will($this->returnValue($this->getMockForAbstractClass('VersionPress\Storages\Storage')));
        $fakeReplacer->expects($this->once())->method('replace');

        /**
         * @var StorageFactory $fakeStorageFactory
         * @var AbsoluteUrlReplacer $fakeReplacer
         */
        $mirror = new Mirror($fakeStorageFactory, $fakeReplacer);
        $mirror->save('foo', array());
    }

    /**
     * @test
     */
    public function mirrorUsesRightStorage() {
        $entityName = 'some-entity';
        $someEntity = array('foo' => 'bar');

        $fakeStorageFactory = $this->getMockBuilder('VersionPress\Storages\StorageFactory')->disableOriginalConstructor()->getMock();
        $fakeReplacer = $this->getMockBuilder('VersionPress\Utils\AbsoluteUrlReplacer')->disableOriginalConstructor()->getMock();
        $fakeStorage = $this->getMockForAbstractClass('VersionPress\Storages\Storage');

        $fakeReplacer->expects($this->once())->method('replace')->will($this->returnArgument(0));
        $fakeStorageFactory->expects($this->once())->method('getStorage')->with($entityName)->will($this->returnValue($fakeStorage));
        $fakeStorage->expects($this->once())->method('save')->with($someEntity);

        /**
         * @var StorageFactory $fakeStorageFactory
         * @var AbsoluteUrlReplacer $fakeReplacer
         */
        $mirror = new Mirror($fakeStorageFactory, $fakeReplacer);
        $mirror->save($entityName, $someEntity);
    }
}