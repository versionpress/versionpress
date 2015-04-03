<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\WordPressUpdateChangeInfo;

class ChangeInfoEnvelopeTest extends PHPUnit_Framework_TestCase {


    /**
     * @test
     * @dataProvider samePriorityExamples
     */
    public function changeInfosWithSamePriorityMaintainOrder($inputChangeInfosSample, $sortedChangeInfosSample) {
        $changeInfoEnvelope = new ChangeInfoEnvelope($inputChangeInfosSample, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getSortedChangeInfoList();

        $this->assertEquals($sortedChangeInfosSample, $sortedByChangeInfoEnvelope);
    }

    /** @test */
    public function entityChangeInfoWithCreateActionHasHigherPriorityThanOtherActions() {

        $normalPriorityPostChangeInfo = new PostChangeInfo("edit", "postChangeInfo1VPID", "post", "Test title 1");
        $higherPriorityPostChangeInfo = new PostChangeInfo("create", "postChangeInfo2VPID", "post", "Test title 2");

        $input = array($normalPriorityPostChangeInfo, $higherPriorityPostChangeInfo);
        $expectedSorted = array($higherPriorityPostChangeInfo, $normalPriorityPostChangeInfo);

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getSortedChangeInfoList();

        $this->assertEquals($sortedByChangeInfoEnvelope, $expectedSorted);
    }


    /** @test */
    public function themeChangeInfoWithSwitchActionHasHigherPriorityThanOtherThemeActions() {

        $normalPriorityThemeChangeInfo = new ThemeChangeInfo("testtheme", "edit", "Test theme");
        $higherPriorityThemeChangeInfo = new ThemeChangeInfo("testtheme", "switch", "Test theme");

        $input = array($normalPriorityThemeChangeInfo, $higherPriorityThemeChangeInfo);
        $expectedSorted = array($higherPriorityThemeChangeInfo, $normalPriorityThemeChangeInfo);

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getSortedChangeInfoList();

        $this->assertEquals($sortedByChangeInfoEnvelope, $expectedSorted);
    }



    //------------------------------------
    // Data providers
    //------------------------------------


    /**
     * Data provider
     *
     * @return array First item in the nested array is the input array, second is the expected sorted array
     */
    public function samePriorityExamples() {

        $wordpressUpdateChangeInfo1 = new WordPressUpdateChangeInfo("4.0");
        $wordPressUpdateChangeInfo2 = new WordPressUpdateChangeInfo("4.1");

        $normalPriorityPostChangeInfo1 = new PostChangeInfo("edit", "postChangeInfo1VPID", "post", "Test title 1");
        $normalPriorityPostChangeInfo2 = new PostChangeInfo("edit", "postChangeInfo2VPID", "post", "Test title 2");

        return array(
            array(
                array($wordpressUpdateChangeInfo1, $wordPressUpdateChangeInfo2),
                array($wordpressUpdateChangeInfo1, $wordPressUpdateChangeInfo2)
            ),

            array(
                array($normalPriorityPostChangeInfo1, $normalPriorityPostChangeInfo2),
                array($normalPriorityPostChangeInfo1, $normalPriorityPostChangeInfo2),
            ),

        );
    }


}
