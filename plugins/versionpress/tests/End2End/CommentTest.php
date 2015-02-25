<?php

namespace VersionPress\Tests\End2End;

use VersionPress\Tests\Utils\CommitAsserter;

/**
 * Class CommentTest
 * @package VersionPress\Tests\End2End
 *
 */
class CommentTest extends End2EndTestCase {


    /** @var ICommentTestPerformer */
    private static $performer;

    /**
     * Note: public comments from the same IP are throttled by default (one is allowed every 15 seconds),
     * see wp_throttle_comment_flood() and check_comment_flood_db(). Before we find another workaround
     * this public test is run first. All other are done as a logged in user for which the throttling
     * is disabled.
     *
     * @see wp_throttle_comment_flood()
     * @see check_comment_flood_db()
     *
     * @test
     * @testdox Creating comment as an unauthenticated user creates 'comment/create-pending' action
     */
    public function publicCommentAwaitsModeration() {

        self::$performer->prepare_createCommentAwaitingModeration();

        $commitAsserter = new CommitAsserter($this->gitRepository);

        self::$performer->createCommentAwaitingModeration();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("comment/create-pending");
        $commitAsserter->assertCommitPath("A", "%vpdb%/comments/%VPID%.ini");
        $commitAsserter->assertCleanWorkingDirectory();

    }
}
