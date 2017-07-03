<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\SerializedDataCursor;

class SerializedDataCursorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function cursorReadsScalarValue()
    {
        $data = 'value';
        $cursor = new SerializedDataCursor($data, []);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsSerializedScalarValue()
    {
        $data = serialize('value');
        $cursor = new SerializedDataCursor($data, [[]]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSimpleSerializedArray()
    {
        $data = serialize(['value']);
        $cursor = new SerializedDataCursor($data, [[0]]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSerializedNestedArrays()
    {
        $data = serialize(['key' => [3 => 'value']]);
        $cursor = new SerializedDataCursor($data, [['key', 3]]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSerializedArrayWithMixedKeys()
    {
        $data = serialize(['key' => [3 => 'value'], 'another-key' => 'another value']);
        $cursor = new SerializedDataCursor($data, [['key', 3]]);
        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSerializedObjectWithArray()
    {
        $data = new \stdClass();
        $data->key = [3 => 'value'];

        $serializedData = serialize($data);
        $cursor = new SerializedDataCursor($serializedData, [['key', 3]]);

        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorReadsFromSerializedNestedObjects()
    {
        $data = new \stdClass();
        $data->key = new \stdClass();
        $data->key->subkey = 'value';

        $serializedData = serialize($data);
        $cursor = new SerializedDataCursor($serializedData, [['key', 'subkey']]);

        $this->assertEquals('value', $cursor->getValue());
    }

    /**
     * @test
     */
    public function cursorUpdatesScalarValue()
    {
        $data = 'value';

        $cursor = new SerializedDataCursor($data, []);
        $cursor->setValue('another value');

        $this->assertEquals('another value', $data);
    }

    /**
     * @test
     */
    public function cursorUpdatesSimpleSerializedArray()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $originalData = serialize([$originalValue]);
        $expectedData = serialize([$newValue]);

        $cursor = new SerializedDataCursor($originalData, [[0]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesNestedSerializedArray()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $originalData = serialize(['key' => [3 => $originalValue]]);
        $expectedData = serialize(['key' => [3 => $newValue]]);

        $cursor = new SerializedDataCursor($originalData, [['key', 3]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedArrayWithMixedKeys()
    {
        $originalValue = 'value';
        $newValue = 'another value';
        $originalData = serialize(['key' => [3 => $originalValue], 'different-key' => 'different value']);
        $expectedData = serialize(['key' => [3 => $newValue], 'different-key' => 'different value']);

        $cursor = new SerializedDataCursor($originalData, [['key', 3]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedObjectWithArray()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $obj = new \stdClass();
        $obj->key = [3 => $originalValue];

        $originalData = serialize($obj);

        $obj->key = [3 => $newValue];

        $expectedData = serialize($obj);

        $cursor = new SerializedDataCursor($originalData, [['key', 3]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedNestedObjects()
    {
        $originalValue = 'VP is cool';
        $newValue = 'another value';

        $obj = new \stdClass();
        $obj->key = new \stdClass();
        $obj->key->subkey = $originalValue;

        $originalData = serialize($obj);

        $obj->key->subkey = $newValue;
        $expectedData = serialize($obj);

        $cursor = new SerializedDataCursor($originalData, [['key', 'subkey']]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedArrayInSerializedArray()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $originalData = serialize([serialize([$originalValue])]);
        $expectedData = serialize([serialize([$newValue])]);

        $cursor = new SerializedDataCursor($originalData, [[0], [0]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedArrayInSerializedObject()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $obj = new \stdClass();
        $obj->key = serialize(['subkey' => $originalValue]);

        $originalData = serialize($obj);

        $obj->key = serialize(['subkey' => $newValue]);
        $expectedData = serialize($obj);

        $cursor = new SerializedDataCursor($originalData, [['key'], ['subkey']]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorUpdatesSerializedObjectInSerializedObject()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $obj = new \stdClass();
        $nestedObj = new \stdClass();
        $nestedObj->subkey = $originalValue;

        $obj->key = serialize($nestedObj);

        $originalData = serialize($obj);

        $nestedObj->subkey = $newValue;
        $obj->key = serialize($nestedObj);
        $expectedData = serialize($obj);

        $cursor = new SerializedDataCursor($originalData, [['key'], ['subkey']]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }

    /**
     * @test
     */
    public function cursorHandlesThreeLevelsOfSerializedArrays()
    {
        $originalValue = 'value';
        $newValue = 'another value';

        $originalData = serialize(['key' => ['subkey' => serialize(['nested-key' => serialize([$originalValue])])]]);
        $expectedData = serialize(['key' => ['subkey' => serialize(['nested-key' => serialize([$newValue])])]]);

        $cursor = new SerializedDataCursor($originalData, [['key', 'subkey'], ['nested-key'], [0]]);
        $cursor->setValue($newValue);

        $this->assertEquals($expectedData, $originalData);
    }
}
