<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\PostStorage;
use VersionPress\Utils\FileSystem;

class PostStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var PostStorage */
    private $storage;

    private $testingPost = [
        'post_date' => "2015-02-02 14:19:59",
        'post_date_gmt' => "2015-02-02 14:19:59",
        'post_content' => "Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!",
        'post_title' => "Hello world!",
        'post_excerpt' => "",
        'post_status' => "publish",
        'comment_status' => "open",
        'ping_status' => "open",
        'post_password' => "",
        'post_name' => "hello-world",
        'to_ping' => "",
        'pinged' => "",
        'post_content_filtered' => "",
        'guid' => "http://127.0.0.1/wordpress/?p=1",
        'menu_order' => 0,
        'post_type' => "post",
        'post_mime_type' => "",
        'vp_id' => "F0E1B6313B7A48E49A1B38DF382B350D",
        'vp_post_author' => "3EC9EF54CAF94300BBA89111FA833222",
    ];

    /**
     * @test
     */
    public function savedPostEqualsLoadedPost()
    {
        $this->storage->save($this->testingPost);
        $loadedPost = $this->storage->loadEntity($this->testingPost['vp_id']);
        $this->assertTrue($this->testingPost == $loadedPost);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->storage->save($this->testingPost);
        $loadedPosts = $this->storage->loadAll();
        $this->assertTrue(count($loadedPosts) === 1);
        $this->assertTrue($this->testingPost == reset($loadedPosts));
    }

    /**
     * @test
     */
    public function savedPostDoesNotContainVpIdKey()
    {
        $this->storage->save($this->testingPost);
        $fileName = $this->storage->getEntityFilename($this->testingPost['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    protected function setUp()
    {
        parent::setUp();
        $entityInfo = new EntityInfo([
            'post' => [
                'table' => 'posts',
                'id' => 'ID',
                'references' => [
                    'post_author' => 'user',
                    'post_parent' => 'post',
                ]
            ]
        ]);
        mkdir(__DIR__ . '/posts');
        $this->storage = new PostStorage(__DIR__ . '/posts', $entityInfo);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/posts');
    }
}
