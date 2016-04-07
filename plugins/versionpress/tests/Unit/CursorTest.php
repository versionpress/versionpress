<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\Cursor;

class CursorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function cursorReadsScalarValue() {
        $data = 'value';
        $cursor = new Cursor($data, []);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSimpleArray() {
        $data = ['value'];
        $cursor = new Cursor($data, [0]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromNestedArrays() {
        $data = ['key' => [3 => 'value']];
        $cursor = new Cursor($data, ['key', 3]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromArrayWithMixedKeys() {
        $data = ['key' => [3 => 'value'], 'another-key' => 'another value'];
        $cursor = new Cursor($data, ['key', 3]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromObjectWithArray() {
        $data = new \stdClass();
        $data->key = [3 => 'value'];

        $cursor = new Cursor($data, ['key', 3]);

        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromNestedObjects() {
        $data = new \stdClass();
        $data->key = new \stdClass();
        $data->key->subkey = 'value';

        $cursor = new Cursor($data, ['key', 'subkey']);

        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorUpdatesScalarValue() {
        $data = 'value';

        $cursor = new Cursor($data, []);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data);
    }

    /**
     * @test
     */
    public function cursorUpdatesSimpleArray() {
        $data = ['value'];

        $cursor = new Cursor($data, [0]);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data[0]);
    }

    /**
     * @test
     */
    public function cursorUpdatesNestedArray() {
        $data = ['key' => [3 => 'value']];

        $cursor = new Cursor($data, ['key', 3]);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data['key'][3]);
    }

    /**
     * @test
     */
    public function cursorUpdatesArrayWithMixedKeys() {
        $data = ['key' => [3 => 'value'], 'another-key' => 'another value'];

        $cursor = new Cursor($data, ['key', 3]);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data['key'][3]);
    }

    /**
     * @test
     */
    public function cursorUpdatesObjectWithArray() {
        $data = new \stdClass();
        $data->key = [3 => 'value'];

        $cursor = new Cursor($data, ['key', 3]);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data->key[3]);
    }

    /**
     * @test
     */
    public function cursorUpdatesNestedObjects() {
        $data = new \stdClass();
        $data->key = new \stdClass();
        $data->key->subkey = 'VP is cool';

        $cursor = new Cursor($data, ['key', 'subkey']);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data->key->subkey);
    }
}
