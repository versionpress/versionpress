<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\ChangeInfos\BulkChangeInfo;
use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoEnvelope;
use VersionPress\ChangeInfos\CommentChangeInfo;
use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\ChangeInfos\PluginChangeInfo;
use VersionPress\ChangeInfos\PostChangeInfo;
use VersionPress\ChangeInfos\PostMetaChangeInfo;
use VersionPress\ChangeInfos\TermChangeInfo;
use VersionPress\ChangeInfos\ThemeChangeInfo;
use VersionPress\ChangeInfos\TranslationChangeInfo;
use VersionPress\ChangeInfos\UserChangeInfo;
use VersionPress\ChangeInfos\UserMetaChangeInfo;
use VersionPress\ChangeInfos\WordPressUpdateChangeInfo;

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

        $normalPriorityPostChangeInfo = new PostChangeInfo("edit", "postChangeInfo1VPID", "post", "Test title 1");
        $higherPriorityPostChangeInfo = new PostChangeInfo("create", "postChangeInfo2VPID", "post", "Test title 2");

        $input = [$normalPriorityPostChangeInfo, $higherPriorityPostChangeInfo];
        $expectedSorted = [$higherPriorityPostChangeInfo, $normalPriorityPostChangeInfo];

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();

        $this->assertEquals($sortedByChangeInfoEnvelope, $expectedSorted);
    }

    /** @test */
    public function bulkChangeInfoDoesNotAffectChangeInfoOrder()
    {
        $higherPriorityPostChangeInfo = new PostChangeInfo("create", "1234567890", "post", "Test title");
        $lowerPriorityPostChangeInfo1 = new PostChangeInfo("edit", "1234567890", "post", "Other title");
        $lowerPriorityPostChangeInfo2 = new PostChangeInfo("edit", "1234567890", "post", "Different title");

        $input = [$higherPriorityPostChangeInfo, $lowerPriorityPostChangeInfo1, $lowerPriorityPostChangeInfo2];
        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();
        $sortedByChangeInfoEnvelope = $this->ungroupChangeInfos($sortedByChangeInfoEnvelope);
        $this->assertEquals($input, $sortedByChangeInfoEnvelope);
    }


    /** @test */
    public function themeChangeInfoWithSwitchActionHasHigherPriorityThanOtherThemeActions()
    {

        $normalPriorityThemeChangeInfo = new ThemeChangeInfo("testtheme", "edit", "Test theme");
        $higherPriorityThemeChangeInfo = new ThemeChangeInfo("testtheme", "switch", "Test theme");

        $input = [$normalPriorityThemeChangeInfo, $higherPriorityThemeChangeInfo];
        $expectedSorted = [$higherPriorityThemeChangeInfo, $normalPriorityThemeChangeInfo];

        $changeInfoEnvelope = new ChangeInfoEnvelope($input, "1.0");
        $sortedByChangeInfoEnvelope = $changeInfoEnvelope->getReorganizedInfoList();

        $this->assertEquals($sortedByChangeInfoEnvelope, $expectedSorted);
    }

    /**
     * @test
     * @dataProvider changeInfosRepresentingBulkActions
     */
    public function bulkActionsAreGroupedIntoBulkChangeInfo($changeInfos, $expectedClass)
    {
        $changeInfoEnvelope = new ChangeInfoEnvelope($changeInfos, '1.0');
        $groupedChangeInfoList = $changeInfoEnvelope->getReorganizedInfoList();
        $this->assertCount(1, $groupedChangeInfoList);
        $this->assertInstanceOf($expectedClass, $groupedChangeInfoList[0]);
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

        $wordpressUpdateChangeInfo1 = new WordPressUpdateChangeInfo("4.0");
        $wordPressUpdateChangeInfo2 = new WordPressUpdateChangeInfo("4.1");

        $normalPriorityPostChangeInfo1 = new PostChangeInfo("edit", "postChangeInfo1VPID", "post", "Test title 1");
        $normalPriorityPostChangeInfo2 = new PostChangeInfo("edit", "postChangeInfo2VPID", "post", "Test title 2");

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
        return [
            [
                [
                    new CommentChangeInfo('spam', '1234567890', 'author', 'Some post'),
                    new CommentChangeInfo('spam', '0987654321', 'other author', 'Some post'),
                ],
                'VersionPress\ChangeInfos\BulkCommentChangeInfo'
            ],
            [
                [
                    new OptionChangeInfo('edit', 'some_option'),
                    new OptionChangeInfo('edit', 'other_option'),
                ],
                'VersionPress\ChangeInfos\BulkOptionChangeInfo'
            ],
            [
                [
                    new PluginChangeInfo('some-plugin.php', 'delete', 'Some plugin'),
                    new PluginChangeInfo('other-plugin.php', 'delete', 'Other plugin'),
                ],
                'VersionPress\ChangeInfos\BulkPluginChangeInfo'
            ],
            [
                [
                    new TranslationChangeInfo('update', 'en_US', 'theme', 'twentythirteen'),
                    new TranslationChangeInfo('update', 'en_US', 'theme', 'twentyfifteen'),
                ],
                'VersionPress\ChangeInfos\BulkTranslationChangeInfo'
            ],
            [
                [
                    new PostChangeInfo('trash', '1234567890', 'post', 'Some post'),
                    new PostChangeInfo('trash', '0987654321', 'post', 'Other post'),
                    new PostChangeInfo('trash', 'ABCDEFEDCB', 'post', 'Different post'),
                ],
                'VersionPress\ChangeInfos\BulkPostChangeInfo'
            ],
            [
                [
                    new PostMetaChangeInfo('create', '1234567890', 'post', 'Some post', 'ABCDEF', 'some-meta'),
                    new PostMetaChangeInfo('create', '0987654321', 'post', 'Some post', 'ABCDEF', 'other-meta'),
                ],
                'VersionPress\ChangeInfos\BulkPostMetaChangeInfo'
            ],
            [
                [
                    new TermChangeInfo('create', '1234567890', 'Some term', 'category'),
                    new TermChangeInfo('create', '0987654321', 'Other term', 'tag'),
                ],
                'VersionPress\ChangeInfos\BulkTermChangeInfo'
            ],
            [
                [
                    new ThemeChangeInfo('some-theme', 'delete', 'Some theme'),
                    new ThemeChangeInfo('other-theme', 'delete', 'Other theme'),
                ],
                'VersionPress\ChangeInfos\BulkThemeChangeInfo'
            ],
            [
                [
                    new UserChangeInfo('delete', '1234567890', 'some.user'),
                    new UserChangeInfo('delete', '0987654321', 'other.user'),
                ],
                'VersionPress\ChangeInfos\BulkUserChangeInfo'
            ],
            [
                [
                    new UserMetaChangeInfo('create', '1234567890', 'some.user', 'some-meta', 'ABCDEF'),
                    new UserMetaChangeInfo('create', '0987654321', 'some.user', 'other-meta', 'ABCDEF'),
                ],
                'VersionPress\ChangeInfos\BulkUserMetaChangeInfo'
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
}
