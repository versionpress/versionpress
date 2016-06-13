<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Database\EntityInfo;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\MetaEntityStorage;
use VersionPress\Storages\UserMetaStorage;
use VersionPress\Storages\UserStorage;
use VersionPress\Utils\FileSystem;

class UserMetaStorageTest extends StorageTestCase
{
    /** @var MetaEntityStorage */
    private $storage;
    /** @var DirectoryStorage */
    private $userStorage;

    private $testingUserMeta = [
        'meta_key' => 'lastname',
        'meta_value' => 'Doe',
        'vp_id' => "F11A5FF2219A3430E099B3838C42EBCA",
        'vp_user_id' => "3EC9EF54CAF94300BBA89111FA833222",
    ];

    private $testingUser = [
        "user_login" => "admin",
        "user_pass" => '$P$B3hfEaUjEIkzHqzDHQ5kCALiUGv3rt1',
        "user_nicename" => "admin",
        "user_email" => "versionpress@example.com",
        "user_url" => "",
        "user_registered" => "2015-02-02 14:19:58",
        "user_activation_key" => "",
        "user_status" => 0,
        "display_name" => "admin",
        "vp_id" => "3EC9EF54CAF94300BBA89111FA833222",
    ];

    /**
     * @test
     */
    public function savedUserMetaEqualsLoadedUserMeta()
    {
        $this->userStorage->save($this->testingUser);
        $this->storage->save($this->testingUserMeta);
        $loadedUserMeta = $this->storage->loadEntity($this->testingUserMeta['vp_id'], $this->testingUser['vp_id']);
        $this->assertTrue($this->testingUserMeta == $loadedUserMeta);
    }

    /**
     * @test
     */
    public function loadAllReturnsOnlyOriginalEntities()
    {
        $this->userStorage->save($this->testingUser);
        $this->storage->save($this->testingUserMeta);
        $loadedUserMeta = $this->storage->loadAll();
        $this->assertTrue(count($loadedUserMeta) === 1);
        $this->assertTrue($this->testingUserMeta == reset($loadedUserMeta));
    }

    protected function setUp()
    {
        parent::setUp();
        $userInfo = new EntityInfo([
            'user' => [
                'table' => 'users',
                'id' => 'ID',
                'changeinfo-fn' => function () {
                },
            ]
        ]);

        $userMetaInfo = new EntityInfo([
            'usermeta' => [
                'id' => 'umeta_id',
                'changeinfo-fn' => function () {
                },
                'parent-reference' => 'user_id',
                'references' => [
                    'user_id' => 'user',
                ]
            ]
        ]);

        mkdir(__DIR__ . '/users');
        $this->userStorage = new DirectoryStorage(__DIR__ . '/users', $userInfo, 'prefix_');
        $this->storage = new MetaEntityStorage($this->userStorage, $userMetaInfo, 'prefix_');
    }

    protected function tearDown()
    {
        parent::tearDown();
        FileSystem::remove(__DIR__ . '/users');
    }
}
