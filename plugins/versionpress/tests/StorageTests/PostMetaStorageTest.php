<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\PostMetaStorage;
use VersionPress\Storages\PostStorage;
use VersionPress\Utils\FileSystem;

class PostMetaStorageTest extends \PHPUnit_Framework_TestCase {
    /** @var PostMetaStorage */
    private $storage;
    /** @var PostStorage */
    private $postStorage;

    private $testingPostMeta = array(
        'meta_key' => 'some-meta',
        'meta_value' => 'value',
        'vp_id' => "F11A5FF2219A3430E099B3838C42EBCA",
        'vp_post_id' => "F0E1B6313B7A48E49A1B38DF382B350D",
    );

    private $testingPost = array(
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
    );

    /**
     * @test
     */
    public function savedPostMetaEqualsLoadedPostMeta() {
        $this->postStorage->save($this->testingPost);
        $this->storage->save($this->testingPostMeta);
        $loadedPost = $this->storage->loadEntity($this->testingPostMeta['vp_id'], $this->testingPost['vp_id']);
        $this->assertTrue($this->testingPostMeta == $loadedPost);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities() {
        $this->postStorage->save($this->testingPost);
        $this->storage->save($this->testingPostMeta);
        $loadedPostMeta = $this->storage->loadAll();
        $this->assertTrue(count($loadedPostMeta) === 1);
        $this->assertTrue($this->testingPostMeta == reset($loadedPostMeta));
    }

    protected function setUp() {
        parent::setUp();
        $postInfo = new EntityInfo(array(
            'post' => array(
                'table' => 'posts',
                'id' => 'ID',
                'references' => array (
                    'post_author' => 'user',
                    'post_parent' => 'post',
                )
            )
        ));

        mkdir(__DIR__ . '/posts');
        $this->postStorage = new PostStorage(__DIR__ . '/posts', $postInfo);
        $this->storage = new PostMetaStorage($this->postStorage);
    }

    protected function tearDown() {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/posts');
    }
}

require_once(__DIR__ . '/fakes.php');