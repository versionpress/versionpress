<?php


use VersionPress\Utils\IniSerializer;
use VersionPress\Utils\StringUtils;

class IniSerializerTest extends PHPUnit_Framework_TestCase {


    /**
     * @test
     */
    public function throwsOnNonSectionedData() {
        $this->setExpectedException('Exception');
        IniSerializer::serialize(array("key" => "value"));
    }

    /**
     * @test
     */
    public function throwsOnEmptySection() {
        $this->setExpectedException('Exception');
        IniSerializer::serialize(array("Section" => array()));
    }

    /**
     * Simplest possible INI - anything less that this throws. A couple of required elements can be seen here:
     *
     *   1. Section must be present
     *   2. ... and non-empty
     *   3. There must be a value, at least an empty string (`key = `) throws.
     *   4. There must be an empty line after the section
     *
     * @test
     */
    public function smallestPossibleExample() {

        $data = array("Section" => array("key" => ""));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key = ""

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function strings() {

        $data = array("Section" => array("key1" => "value1", "key2" => "value2"));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "value1"
key2 = "value2"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function numericValues() {

        $data = array("Section" => array("key1" => 0, "key2" => 1, "key3" => 1.1));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = 0
key2 = 1
key3 = 1.1

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function numericStringsSerializedAsNumbers() {

        $data = array("Section" => array("key1" => "0", "key2" => "1", "key3" => "11.1"));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = 0
key2 = 1
key3 = 11.1

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function subsection() {

        $data = array("Section" => array("Subsection" => array("key" => "value")));
        $ini = StringUtils::crlfize(<<<'INI'
[Section.Subsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function twoSections() {

        $data = array("Section1" => array("key" => "value"), "Section2" => array("key" => "value"));
        $ini = StringUtils::crlfize(<<<'INI'
[Section1]
key = "value"

[Section2]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serialize($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }


}
