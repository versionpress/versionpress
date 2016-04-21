<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Storages\Mirror;
use VersionPress\Storages\Storage;
use VersionPress\Storages\StorageFactory;
use VersionPress\Utils\AbsoluteUrlReplacer;

class MirrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mirrorCallsTheReplacerOnSavingEntity()
    {
        $fakeStorageFactory = $this->getMockBuilder(StorageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $fakeReplacer = $this->getMockBuilder(AbsoluteUrlReplacer::class)
            ->disableOriginalConstructor()->getMock();
        $fakeStorage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $fakeStorageFactory->expects($this->any())->method('getStorage')->will($this->returnValue($fakeStorage));
        $fakeReplacer->expects($this->once())->method('replace');

        /**
         * @var StorageFactory $fakeStorageFactory
         * @var AbsoluteUrlReplacer $fakeReplacer
         */
        $mirror = new Mirror($fakeStorageFactory, $fakeReplacer);
        $mirror->save('foo', []);
    }

    /**
     * @test
     */
    public function mirrorUsesRightStorage()
    {
        $entityName = 'some-entity';
        $someEntity = ['foo' => 'bar'];

        $fakeStorageFactory = $this->getMockBuilder(StorageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $fakeReplacer = $this->getMockBuilder(AbsoluteUrlReplacer::class)
            ->disableOriginalConstructor()->getMock();
        $fakeStorage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $fakeReplacer->expects($this->once())->method('replace')->will($this->returnArgument(0));
        $fakeStorageFactory->expects($this->once())->method('getStorage')
            ->with($entityName)->will($this->returnValue($fakeStorage));
        $fakeStorage->expects($this->once())->method('save')->with($someEntity);

        /**
         * @var StorageFactory $fakeStorageFactory
         * @var AbsoluteUrlReplacer $fakeReplacer
         */
        $mirror = new Mirror($fakeStorageFactory, $fakeReplacer);
        $mirror->save($entityName, $someEntity);
    }
}
