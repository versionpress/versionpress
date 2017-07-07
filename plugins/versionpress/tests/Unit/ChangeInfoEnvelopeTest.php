<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\ChangeInfos\BulkChangeInfo;
use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\EntityChangeInfo;
use VersionPress\ChangeInfos\TrackedChangeInfo;
use VersionPress\Database\EntityInfo;

class ChangeInfoEnvelopeTest extends PHPUnit_Framework_TestCase
{


    /**
     * @test
     * @dataProvider samePriorityExamples
     * @param $inputChangeInfosSample
     * @param $sortedChangeInfosSample
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

        $lowerPriorityChangeInfo = new EntityChangeInfo($entityInfo, null, 'update', 'vpid', [], [], 12);
        $normalPriorityChangeInfo = new EntityChangeInfo($entityInfo, null, 'create', 'vpid');

        $input = [$lowerPriorityChangeInfo, $normalPriorityChangeInfo];
        $expectedSorted = [$normalPriorityChangeInfo, $lowerPriorityChangeInfo];

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();

        $this->assertEquals($expectedSorted, $sortedByChangeInfoEnvelope);
    }

    /** @test */
    public function bulkChangeInfoDoesNotAffectChangeInfoOrder()
    {
        $entityInfo = $this->createEntityInfoMock('some_entity');

        $normalPriorityChangeInfo = new EntityChangeInfo($entityInfo, null, 'create', 'vpid');
        $lowerPriorityChangeInfo1 = new EntityChangeInfo($entityInfo, null, 'update', 'vpid', [], [], 12);
        $lowerPriorityChangeInfo2 = new EntityChangeInfo($entityInfo, null, 'update', 'vpid', [], [], 12);

        $input = [$normalPriorityChangeInfo, $lowerPriorityChangeInfo1, $lowerPriorityChangeInfo2];
        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();
        $sortedByChangeInfoEnvelope = $this->ungroupChangeInfos($sortedByChangeInfoEnvelope);
        $this->assertEquals($input, $sortedByChangeInfoEnvelope);
    }

    /**
     * @test
     * @dataProvider changeInfosRepresentingBulkActions
     * @param $changeInfos
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

        $wordpressUpdateChangeInfo1 = new TrackedChangeInfo('wordpress', null, 'update', '4.0', [], [], 12);
        $wordPressUpdateChangeInfo2 = new TrackedChangeInfo('wordpress', null, 'update', '4.1', [], [], 12);

        $normalPriorityPostChangeInfo1 = new EntityChangeInfo($entityInfoMock, null, 'update', 'vpid');
        $normalPriorityPostChangeInfo2 = new EntityChangeInfo($entityInfoMock, null, 'update', 'another vpid');

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

        return [
            [
                [
                    new EntityChangeInfo($entityInfoMock, null, 'update', '1st vpid'),
                    new EntityChangeInfo($entityInfoMock, null, 'update', '2nd vpid'),
                ]
            ],
            [
                [
                    new EntityChangeInfo($entityInfoMock, null, 'update', '1st vpid'),
                    new EntityChangeInfo($entityInfoMock, null, 'update', '2nd vpid'),
                    new EntityChangeInfo($entityInfoMock, null, 'update', '3rd vpid'),
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
}
