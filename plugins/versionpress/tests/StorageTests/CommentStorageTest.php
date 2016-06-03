<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\Database;
use VersionPress\Database\EntityInfo;
use VersionPress\Storages\CommentStorage;
use VersionPress\Tests\End2End\Utils\AnonymousObject;
use VersionPress\Utils\FileSystem;

class CommentStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var CommentStorage */
    private $storage;

    private $testingComment = [
        "comment_author" => "Mr WordPress",
        "comment_author_email" => "",
        "comment_author_url" => "https://wordpress.org/",
        "comment_author_IP" => "",
        "comment_date" => "2015-02-02 14:19:59",
        "comment_date_gmt" => "2015-02-02 14:19:59",
        "comment_content" => "Hi, this is a comment.",
        "comment_karma" => 0,
        "comment_approved" => 1,
        "comment_agent" => "",
        "comment_type" => "",
        "vp_id" => "927D63C187164CA1BCEAB2B13B29C8F0",
        "vp_comment_post_ID" => "F0E1B6313B7A48E49A1B38DF382B350D",
    ];

    /**
     * @test
     */
    public function savedCommentEqualsLoadedComment()
    {
        $this->storage->save($this->testingComment);
        $loadedComment = $this->storage->loadEntity($this->testingComment['vp_id']);
        $this->assertEquals($this->testingComment, $loadedComment);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->storage->save($this->testingComment);
        $loadedComments = $this->storage->loadAll();
        $this->assertTrue(count($loadedComments) === 1);
        $this->assertTrue($this->testingComment == reset($loadedComments));
    }

    /**
     * @test
     */
    public function savedCommentDoesNotContainVpIdKey()
    {
        $this->storage->save($this->testingComment);
        $fileName = $this->storage->getEntityFilename($this->testingComment['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    protected function setUp()
    {
        parent::setUp();
        $entityInfo = new EntityInfo([
            'comment' => [
                'table' => 'comments',
                'id' => 'ID',
                'changeinfo-fn' => function () {
                },
                'references' => [
                    'comment_post_ID' => 'post',
                    'comment_parent' => 'comment',
                ]
            ]
        ]);
        if (file_exists(__DIR__ . '/comments')) {
            FileSystem::remove(__DIR__ . '/comments');
        }
        mkdir(__DIR__ . '/comments');
        $wpdbFake = new AnonymousObject([
            'prefix' => '',
            'posts' => 'posts',
            'vp_id' => 'vp_id',
            'get_row' => function () {
                return new AnonymousObject(['post_title' => '']);
            }
        ]);
        $database = new Database($wpdbFake);
        $this->storage = new CommentStorage(__DIR__ . '/comments', $entityInfo, $database);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/comments');
    }
}
