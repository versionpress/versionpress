<?php

namespace VersionPress\Tests\StorageTests;

use VersionPress\Tests\Utils\HookMock;

class StorageTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        HookMock::setUp(HookMock::WP_MOCK);
    }

    protected function tearDown()
    {
        HookMock::tearDown();
    }
}
