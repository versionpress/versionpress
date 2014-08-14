<?php

abstract class WpCliTestCase extends PHPUnit_Framework_TestCase {
    /**
     * Configuration read from `test-config.ini` and set to this variable from phpunit-bootstrap.php.
     *
     * @var TestConfig
     */
    public static $config;
    private static $setUp = false;

    /** @var NConnection */
    private static $db;

    public static function setUpBeforeClass() {
        if (!self::$setUp) {
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
        $result = boolval(self::$db->query("SELECT * FROM wp_vp_id WHERE vp_id=UNHEX('$vpId')"));
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

        foreach ($expectedEntity as $field => $value) {
            if (isset($actualEntity[$field])) {
                $fieldIsOk = $value == $actualEntity[$field];
                $entityIsOk &= $fieldIsOk;
                if (!$fieldIsOk) {
                    $errorMessages[] = "Field '$field' has wrong value";
                }
            } elseif (isset($actualEntity["vp_$field"])) {
                // OK ... there is some VP id
            } else {
                $entityIsOk = false;
                $errorMessages[] = "Field '$field' not found in post";
            }
        }

        if ($entityIsOk) {
            $this->assertTrue(true); // OK
        } else {
            $this->fail(join("\n", $errorMessages));
        }
    }

    protected function getEntityVpId(Commit $commit) {
        list($_, $__, $entityVpId) = explode(
            "/",
            $commit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG)
        );
        return $entityVpId;
    }

    protected function assertEditation($entity, $changes, $expectedAction, $createFunction, $editFunction) {
        $id = call_user_func($createFunction, $entity);
        $creationCommit = $this->getLastCommit();
        $createdPostVpId = $this->getEntityVpId($creationCommit);

        call_user_func($editFunction, $id, $changes);
        $editationCommit = $this->getLastCommit();
        $this->assertStringStartsWith(
            $expectedAction,
            $editationCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG),
            "Expected another action"
        );

        $editedPostVpId = $this->getEntityVpId($editationCommit);
        $this->assertEquals($createdPostVpId, $editedPostVpId, "Edited different entity");

        $commitedEntity = $this->getCommitedEntity($createdPostVpId);
        $newEntity = array_merge($entity, $changes);
        $this->assertEntityEquals($newEntity, $commitedEntity);
    }

    abstract protected function getCommitedEntity($vpId);
} 