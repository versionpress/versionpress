<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\ChangeInfos\BulkChangeInfo;
use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Git\ActionsInfo;

class ChangeInfoEnvelopeTest extends PHPUnit_Framework_TestCase
{


    /**
     * @test
     * @dataProvider samePriorityExamples
     */
    public function changeInfosWithSamePriorityMaintainOrder($inputChangeInfosSample, $sortedChangeInfosSample)
    {
        $changeInfoEnvelope = new ChangeInfoEnvelope($inputChangeInfosSample, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();
        $sortedByChangeInfoEnvelope = $this->ungroupChangeInfos($sortedByChangeInfoEnvelope);

        $this->assertEquals($sortedChangeInfosSample, $sortedByChangeInfoEnvelope);
    }

    /** @test */
    public function entityChangeInfoWithCreateActionHasHigherPriorityThanOtherActions()
    {

        $entityInfo = $this->createEntityInfoMock('some_entity');
        $lowerPriorityActionsInfo = $this->createActionsInfoMock(15);
        $normalPriorityActionsInfo = $this->createActionsInfoMock(10);

        $lowerPriorityChangeInfo = new EntityChangeInfo($entityInfo, $lowerPriorityActionsInfo, 'edit', 'vpid');
        $normalPriorityChangeInfo = new EntityChangeInfo($entityInfo, $normalPriorityActionsInfo, 'create', 'vpid');

        $input = [$lowerPriorityChangeInfo, $normalPriorityChangeInfo];
        $expectedSorted = [$normalPriorityChangeInfo, $lowerPriorityChangeInfo];

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();

        $this->assertEquals($sortedByChangeInfoEnvelope, $expectedSorted);
    }

    /** @test */
    public function bulkChangeInfoDoesNotAffectChangeInfoOrder()
    {
        $entityInfo = $this->createEntityInfoMock('some_entity');
        $higherPriorityActionsInfo = $this->createActionsInfoMock(10);
        $lowerPriorityActionsInfo = $this->createActionsInfoMock(15);

        $higherPriorityChangeInfo = new EntityChangeInfo($entityInfo, $higherPriorityActionsInfo, 'create', 'vpid');
        $lowerPriorityChangeInfo1 = new EntityChangeInfo($entityInfo, $lowerPriorityActionsInfo, 'edit', 'vpid');
        $lowerPriorityChangeInfo2 = new EntityChangeInfo($entityInfo, $lowerPriorityActionsInfo, 'edit', 'vpid');

        $input = [$higherPriorityChangeInfo, $lowerPriorityChangeInfo1, $lowerPriorityChangeInfo2];
        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();
        $sortedByChangeInfoEnvelope = $this->ungroupChangeInfos($sortedByChangeInfoEnvelope);
        $this->assertEquals($input, $sortedByChangeInfoEnvelope);
    }

    /**
     * @test
     * @dataProvider changeInfosRepresentingBulkActions
     */
    public function bulkActionsAreGroupedIntoBulkChangeInfo($changeInfos)
    {
        $changeInfoEnvelope = new ChangeInfoEnvelope($changeInfos, '1.0');
        $groupedChangeInfoList = $changeInfoEnvelope->getReorganizedInfoList();
        $this->assertCount(1, $groupedChangeInfoList);
        $this->assertInstanceOf(BulkChangeInfo::class, $groupedChangeInfoList[0]);
    }

    //------------------------------------
    // Data providers
    //------------------------------------


    /**
     * Data provider
     *
     * @return array First item in the nested array is the input array, second is the expected sorted array
     */
    public function samePriorityExamples()
    {

        $entityName = 'some_entity';

        $entityInfoMock = $this->createEntityInfoMock($entityName);
        $actionsInfoMock = $this->createActionsInfoMock(10);

        $wordpressUpdateChangeInfo1 = new WordPressUpdateChangeInfo("4.0");
        $wordPressUpdateChangeInfo2 = new WordPressUpdateChangeInfo("4.1");

        $normalPriorityPostChangeInfo1 = new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', 'vpid');
        $normalPriorityPostChangeInfo2 = new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', 'vpid');

        return [
            [
                [$wordpressUpdateChangeInfo1, $wordPressUpdateChangeInfo2],
                [$wordpressUpdateChangeInfo1, $wordPressUpdateChangeInfo2]
            ],

            [
                [$normalPriorityPostChangeInfo1, $normalPriorityPostChangeInfo2],
                [$normalPriorityPostChangeInfo1, $normalPriorityPostChangeInfo2],
            ],

        ];
    }

    public function changeInfosRepresentingBulkActions()
    {
        $entityInfoMock = $this->createEntityInfoMock('some_entity');
        $actionsInfoMock = $this->createActionsInfoMock(10);

        return [
            [
                [
                    new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', '1st vpid'),
                    new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', '2nd vpid'),
                ]
            ],
            [
                [
                    new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', '1st vpid'),
                    new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', '2nd vpid'),
                    new EntityChangeInfo($entityInfoMock, $actionsInfoMock, 'edit', '3rd vpid'),
                ]
            ],
        ];
    }

    /**
     * @param ChangeInfo[] $changeInfos
     * @return ChangeInfo[]
     */
    private function ungroupChangeInfos($changeInfos)
    {
        $ungrouped = [];
        foreach ($changeInfos as $changeInfo) {
            if ($changeInfo instanceof BulkChangeInfo) {
                foreach ($changeInfo->getChangeInfos() as $innerChangeInfo) {
                    $ungrouped[] = $innerChangeInfo;
                }
            } else {
                $ungrouped[] = $changeInfo;
            }
        }

        return $ungrouped;
    }

    /**
     * @param string $entityName
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityInfo
     */
    private function createEntityInfoMock($entityName)
    {
        $entityInfoMock = $this->getMockBuilder(EntityInfo::class)->disableOriginalConstructor()->getMock();
        $entityInfoMock->expects($this->any())->method('__get')->with($this->equalTo('entityName'))->will($this->returnValue($entityName));

        return $entityInfoMock;
    }

    /**
     * @param int $priority
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionsInfo
     */
    private function createActionsInfoMock($priority)
    {
        $actionsInfoMock = $this->getMockBuilder(ActionsInfo::class)->disableOriginalConstructor()->getMock();
        $actionsInfoMock->expects($this->any())->method('getActionPriority')->will($this->returnValue($priority));
        return $actionsInfoMock;
    }
}
