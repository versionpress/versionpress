<?php

abstract class WpCliTestCase extends PHPUnit_Framework_TestCase {
    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;
    /** @var bool */
    public static $skipSetup = false;
    /** @var bool */
    private static $setUp = false;

    /** @var NConnection */
    private static $db;

    public static function setUpBeforeClass() {
        if (!self::$skipSetup && !self::$setUp) {
            WpAutomation::setUpSite();
            WpAutomation::installVersionPress();
            WpAutomation::enableVersionPress();
            self::setUpDatabase();
            self::$setUp = true;
        }
    }

    /**
     * Creates connection to the database.
     */
    private static function setUpDatabase() {
        $dbHost = WpCliTestCase::$config->getDbHost();
        $dbName = WpCliTestCase::$config->getDbName();
        $dbUser = WpCliTestCase::$config->getDbUser();
        $dbPassword = WpCliTestCase::$config->getDbPassword();
        self::$db = new NConnection("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    }

    /**
     * Returns last commit within tested WP site.
     *
     * @return Commit
     */
    protected function getLastCommit() {
        chdir(WpCliTestCase::$config->getSitePath());
        $gitLog = Git::log();
        $lastCommit = $gitLog[0];
        return $lastCommit;
    }

    /**
     * Checks that given VP ID exists in the database.
     *
     * @param $vpId
     */
    protected function assertIdExistsInDatabase($vpId) {
        $prefix = self::$config->getDbPrefix();
        $result = boolval(self::$db->query("SELECT * FROM {$prefix}vp_id WHERE vp_id=UNHEX('$vpId')"));
        $this->assertTrue($result, "vp_id '$vpId' not found in database");
    }

    /**
     * Checks that all keys from $expectedEntity are present in the $actualEntity and also that
     * their values equal. The $actualEntity can have instead of $key (that is in the $expectedEntity)
     * key named vp_$key - because of foreign keys. In this case the value is not checked.
     *
     * @param $expectedEntity
     * @param $actualEntity
     */
    protected function assertEntityEquals($expectedEntity, $actualEntity) {
        $entityIsOk = true;
        $errorMessages = array();

        $actualEntityCopy = $actualEntity;
        foreach ($actualEntityCopy as $field => $value) {
            if(strpos($field, "#") !== false) {
                list($realField) = explode("#", $field);
                unset($actualEntity[$field]);
                $actualEntity[$realField] = $value;
            }
        }

        foreach ($expectedEntity as $field => $value) {
            if (isset($actualEntity[$field])) {
                $fieldIsOk = $value == $actualEntity[$field];
                $entityIsOk &= $fieldIsOk;
                if (!$fieldIsOk) {
                    $errorMessages[] = "Field '$field' has wrong value (expected '$value', actual '$actualEntity[$field]')";
                }
            } elseif (isset($actualEntity["vp_$field"])) {
                // OK ... there is some VP id
            } else {
                $entityIsOk = false;
                $errorMessages[] = "Field '$field' not found in the entity";
            }
        }

        if ($entityIsOk) {
            $this->assertTrue(true); // OK
        } else {
            $this->fail(join("\n", $errorMessages));
        }
    }

    protected function getVpIdFromCommit(Commit $commit) {
        list($_, $__, $entityVpId) = explode(
            "/",
            $commit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG)
        );
        return $entityVpId;
    }

    /**
     * Creates new post, applies changes and checks that actual action corresponds with the expected one.
     * Also checks there was edited the right post.
     *
     * @param array $entity Entity to create
     * @param array $changes Applied changes
     * @param string $expectedAction
     * @param callable $createFunction
     * @param callable $editFunction
     */
    protected function assertEditation($entity, $changes, $expectedAction, $createFunction, $editFunction) {
        $id = call_user_func($createFunction, $entity);
        $creationCommit = $this->getLastCommit();
        $createdPostVpId = $this->getVpIdFromCommit($creationCommit);

        call_user_func($editFunction, $id, $changes);
        $editationCommit = $this->getLastCommit();
        $this->assertStringStartsWith(
            $expectedAction,
            $editationCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG),
            "Expected another action"
        );

        $editedPostVpId = $this->getVpIdFromCommit($editationCommit);
        $this->assertEquals($createdPostVpId, $editedPostVpId, "Edited different entity");

        $commitedEntity = $this->getCommitedEntity($createdPostVpId);
        $newEntity = array_merge($entity, $changes);
        $this->assertEntityEquals($newEntity, $commitedEntity);
    }

    /**
     * Creates new post, deletes it and checks that actual action corresponds with the expected one.
     * Also checks there was deleted the right post.
     *
     * @param array $entity Entity to create
     * @param string $entityName Name of entity (e.g. post, comment etc.)
     * @param callable $createFunction Function that creates new entity (perhaps from WpAutomation)
     * @param callable $deleteFunction Function that deletes new entity (perhaps from WpAutomation)
     */
    protected function assertDeletion($entity, $entityName, $createFunction, $deleteFunction) {
        $id = call_user_func($createFunction, $entity);
        $creationCommit = $this->getLastCommit();
        $createdEntityVpId = $this->getVpIdFromCommit($creationCommit);

        call_user_func($deleteFunction, $id);
        $deleteCommit = $this->getLastCommit();
        $this->assertEquals(
            "$entityName/delete/$createdEntityVpId",
            $deleteCommit->getMessage()->getVersionPressTag(TrackedChangeInfo::ACTION_TAG)
        );

        $deletedEntityVpId = $this->getVpIdFromCommit($deleteCommit);
        $this->assertEquals($createdEntityVpId, $deletedEntityVpId);
    }

    /**
     * Returns entity by its VP ID.
     *
     * @param string $vpId
     * @return array
     */
    abstract protected function getCommitedEntity($vpId);
}
