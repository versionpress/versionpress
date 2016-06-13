<?php

namespace VersionPress\Tests\StorageTests;

class StorageTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        \WP_Mock::setUp();
    }

    protected function tearDown()
    {
        \WP_Mock::tearDown();
    }
}
