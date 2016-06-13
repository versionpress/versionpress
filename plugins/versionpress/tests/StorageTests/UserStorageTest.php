<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Utils\FileSystem;

class UserStorageTest extends StorageTestCase
{
    /** @var DirectoryStorage */
    private $storage;

    private $testingUser = [
        "user_login" => "admin",
        "user_pass" => '$P$B3hfEaUjEIkzHqzDHQ5kCALiUGv3rt1',
        "user_nicename" => "admin",
        "user_email" => "versionpress@example.com",
        "user_url" => "",
        "user_registered" => "2015-02-02 14:19:58",
        "user_status" => 0,
        "display_name" => "admin",
        "vp_id" => "3EC9EF54CAF94300BBA89111FA833222",
    ];

    /**
     * @test
     */
    public function savedUserEqualsLoadedUser()
    {
        $this->storage->save($this->testingUser);
        $loadedUser = $this->storage->loadEntity($this->testingUser['vp_id']);
        $this->assertTrue($this->testingUser == $loadedUser);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->storage->save($this->testingUser);
        $loadedUsers = $this->storage->loadAll();
        $this->assertTrue(count($loadedUsers) === 1);
        $this->assertTrue($this->testingUser == reset($loadedUsers));
    }

    /**
     * @test
     */
    public function savedUserDoesNotContainVpIdKey()
    {
        $this->storage->save($this->testingUser);
        $fileName = $this->storage->getEntityFilename($this->testingUser['vp_id']);
        $content = file_get_contents($fileName);
        $this->assertFalse(strpos($content, 'vp_id'), 'Entity contains a vp_id key');
    }

    protected function setUp()
    {
        parent::setUp();
        $entityInfo = new EntityInfo([
            'user' => [
                'table' => 'users',
                'id' => 'ID',
                'changeinfo-fn' => function () {
                },
            ]
        ]);

        mkdir(__DIR__ . '/users');
        $this->storage = new DirectoryStorage(__DIR__ . '/users', $entityInfo);
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/users');
    }
}
