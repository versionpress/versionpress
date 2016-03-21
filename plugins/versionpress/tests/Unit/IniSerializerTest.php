<?php

namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Utils\Serialization\IniSerializer;
use VersionPress\Utils\StringUtils;

/**
 * Tests covering IniSerializer. There are also IniSerializer_Issue* tests which contain
 * tests cases for reported issues.
 */
class IniSerializerTest extends PHPUnit_Framework_TestCase {


    //--------------------------------
    // Invalid inputs
    //--------------------------------

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



    //--------------------------------
    // The most basic test
    //--------------------------------


    /**
     * Simplest possible sectioned INI - anything less that this throws. A couple of required elements can be seen here:
     *
     *   1. Section must be present
     *   2. ... and non-empty
     *   3. There must be a value, at least an empty string (`key = ` throws)
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

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }


    //--------------------------------
    // Supported types
    //--------------------------------


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

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

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

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function numericStringsSerializedAsNumbers() {

        $data = array("Section" => array("key1" => "0", "key2" => "1", "key3" => "11.1"));
        $deserializedData = array("Section" => array("key1" => 0, "key2" => 1, "key3" => 11.1));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = 0
key2 = 1
key3 = 11.1

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($deserializedData, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function arraysAsSquareBrackets() {

        $data = array("Section" => array("key" => array("val1", "val2")));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key[0] = "val1"
key[1] = "val2"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }


    //--------------------------------
    // Escaping / special characters
    //--------------------------------

    // Note: since WP-458 this has been simplified as INI_SCANNER_RAW is used for parse_ini_string()
    // Previously, backslashes were a bit tricky, see WP-289.

    /**
     * @test
     */
    public function backslash_single() {

        $data = array(
            "Section" => array(
                "key1" => "My \\ site"
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "My \\ site"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function backslash_double() {

        $data = array(
            "Section" => array(
                "key1" => "My \\\\ site"
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "My \\\\ site"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function backslash_tripple() {

        $data = array(
            "Section" => array(
                "key1" => "My \\\\\\ site"
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "My \\\\\\ site"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function backslash_atTheEndOfString() {

        $data = array(
            "Section" => array(
                "key1" => "Value \\",
                "key2" => "Value \\",
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "Value \\"
key2 = "Value \\"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function doubleQuoteEscaping() {

        $data = array("Section" => array("key1" => "\"Hello\""));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "\"Hello\""

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * This tests one problematic aspect of parse_ini_string(), see WP-288.
     *
     * @test
     */
    public function doubleQuoteEscapingAtTheEOL() {

        $data = array("Section" => array("key1" => "\"\r\nwhatever\""));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "\"
whatever\""

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function doubleQuoteEscaping_HereDoc() {

        // Note: double quotes in heredoc MUST NOT be escaped with "\", although
        // PHP manual states that it optionally might be
        $data = array("Section" => array("key1" => <<<VAL
"Hello"
VAL
        ));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "\"Hello\""

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function hashSignCommentInsideQuotes() {

        $data = array("Section" => array("key1" => StringUtils::crlfize(<<<VAL
First line of the value
# Continued value - should not be treated as comment
VAL
        )));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "First line of the value
# Continued value - should not be treated as comment"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function dollarSignInsideQuotes() {
        $data = array("Section" => array("key1" => 'some$value', "key2" => 'another${value'));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "some$value"
key2 = "another${value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function semicolonCommentInsideQuotes() {

        $data = array("Section" => array("key1" => StringUtils::crlfize(<<<VAL
First line of the value
; Continued value - should not be treated as comment
VAL
        )));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "First line of the value
; Continued value - should not be treated as comment"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }


    /**
     * @test
     */
    public function newLineHandlingInsideValues_LF() {

        $data = array("Section" => array("key1" => "Hello\nWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\nWorld\"\r\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CR() {

        $data = array("Section" => array("key1" => "Hello\rWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\rWorld\"\r\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CRLF() {

        $data = array("Section" => array("key1" => "Hello\r\nWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\r\nWorld\"\r\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandling_BlankLines() {

        $data = array("Section" => array("key1" => "\r\n"));
        $ini = "[Section]\r\nkey1 = \"\r\n\"\r\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandling_NewLineAfterStringMark() {
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "$
"

INI
        );

        $data = array("Section" => array(
            "key1" => StringUtils::crlfize("$
")
        ));

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function specialCharactersAreTakenLiterally() {

        // e.g., "\n" should not have any special meaning

        $data = array("Section" => array("key1" => '\n'));
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key1 = "\\n"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     * @dataProvider specialCharactersProvider
     */
    public function specialCharactersInSectionName($specialCharacter) {

        $data = array("Sect{$specialCharacter}ion" => array("somekey" => "value"));
        $ini = StringUtils::crlfize(<<<INI
[Sect{$specialCharacter}ion]
somekey = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     * @dataProvider specialCharactersProvider
     */
    public function specialCharacterInKey($specialCharacter) {

        $data = array("Section" => array("some{$specialCharacter}key" => "value"));
        $ini = StringUtils::crlfize(<<<INI
[Section]
some{$specialCharacter}key = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }


    /**
     * @test
     * @dataProvider specialCharactersInValueProvider
     */
    public function specialCharacterInValue($specialCharacter) {

        $data = array("Section" => array("somekey" => "val{$specialCharacter}ue"));
        $ini = StringUtils::crlfize(<<<INI
[Section]
somekey = "val{$specialCharacter}ue"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    public function specialCharactersProvider() {
        return array_map(function ($specialChar) {
            return array($specialChar);
        },
            array(
                "\\", "\"", "[]", "$", "%", "'", ";", "+", "-", "/", "#", "&", "!", ".",
                "~", "^", "`", "?", ":", ",", "*", "<", ">", "(", ")", "@", "{", "}",
                "|", "_", " ", "\t", "ěščřžýáíéúůóďťňôâĺ", "茶", "русский", "حصان", "="));
    }

    public function specialCharactersInValueProvider() {
        // Double quotes and backslashes are escaped see WP-458 and WP-619
        return array_filter($this->specialCharactersProvider(), function ($val) {
            return $val[0] !== "\"" && $val[0] !== "\\";
        });
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

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function sectionWithDotInName() {
        $data = array("Section.Name" => array("key" => "value"));
        $ini = StringUtils::crlfize(<<<'INI'
[Section.Name]
key = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedString() {
        $serializedString = serialize('some string');

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> "some string"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedInteger() {
        $serializedString = serialize(777);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> 777

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedDouble() {
        $serializedString = serialize(1.2);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> 1.2

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedBoolean() {
        $serializedString = serialize(false);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <boolean> false

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedEmptyArray() {
        $serializedString = serialize([]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedArrayWithString() {
        $serializedString = serialize(['some string']);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = "some string"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     * @dataProvider specialCharactersInValueProvider
     */
    public function serializedArrayWithSpecialStrings($str) {
        $serializedString = serialize([$str, $str, $str]);

        $data = ["Section" => ["data" => $serializedString]];

        $ini = StringUtils::crlfize(<<<INI
[Section]
data = <<<serialized>>> <array>
data[0] = "$str"
data[1] = "$str"
data[2] = "$str"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedOption() {
        $sidebarWidgets = [
            'wp_inactive_widgets' => [],
            'sidebar-1' => ['search-2', 'recent-posts-2', 'recent-comments-2'],
            'array_version' => 3,
        ];


        $serializedString = serialize($sidebarWidgets);

        $data = ["sidebar_widgets" => ["option_value" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[sidebar_widgets]
option_value = <<<serialized>>> <array>
option_value["wp_inactive_widgets"] = <array>
option_value["sidebar-1"] = <array>
option_value["sidebar-1"][0] = "search-2"
option_value["sidebar-1"][1] = "recent-posts-2"
option_value["sidebar-1"][2] = "recent-comments-2"
option_value["array_version"] = 3

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedComplexArray() {
        $array = [
            'numeric index',
            'string index' => 1234,
            'nested array' => [0 => 'some', 345 => 'sparse', 1234 => 'array'],
            'even more nested arrays' => [['array', ['in array', ['in array', 'with mixed' => 'keys']]]],
            'and bool' => true
        ];


        $serializedString = serialize($array);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = "numeric index"
data["string index"] = 1234
data["nested array"] = <array>
data["nested array"][0] = "some"
data["nested array"][345] = "sparse"
data["nested array"][1234] = "array"
data["even more nested arrays"] = <array>
data["even more nested arrays"][0] = <array>
data["even more nested arrays"][0][0] = "array"
data["even more nested arrays"][0][1] = <array>
data["even more nested arrays"][0][1][0] = "in array"
data["even more nested arrays"][0][1][1] = <array>
data["even more nested arrays"][0][1][1][0] = "in array"
data["even more nested arrays"][0][1][1]["with mixed"] = "keys"
data["and bool"] = <boolean> true

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedEmptyStdClass() {
        $serializedString = serialize(new \stdClass());

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <stdClass>

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedStdClassWithAttribute() {
        $object = new \stdClass();
        $object->attribute = 'value';

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <stdClass>
data["attribute"] = "value"

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedStdClassWithMultipleAttributes() {
        $object = new \stdClass();
        $object->stringAttribute = 'value';
        $object->numericAttribute = 1.004;
        $object->boolAttribute = false;
        $object->arrayAttribute = ['array'];

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <stdClass>
data["stringAttribute"] = "value"
data["numericAttribute"] = 1.004
data["boolAttribute"] = <boolean> false
data["arrayAttribute"] = <array>
data["arrayAttribute"][0] = "array"

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedCustomClassWithPublicAttribute() {
        $object = new IniSerializer_FooPublic('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <VersionPress\Tests\Unit\IniSerializer_FooPublic>
data["attribute"] = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedCustomClassWithProtectedAttribute() {
        $object = new IniSerializer_FooProtected('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <VersionPress\Tests\Unit\IniSerializer_FooProtected>
data["*attribute"] = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedCustomClassWithPrivateAttribute() {
        $object = new IniSerializer_FooPrivate('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <VersionPress\Tests\Unit\IniSerializer_FooPrivate>
data["-attribute"] = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedCustomClassWithSleepMethod() {
        $object = new IniSerializer_FooWithCleanup('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <VersionPress\Tests\Unit\IniSerializer_FooWithCleanup>
data["attribute"] = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedNull() {
        $serializedString = serialize(null);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <null>

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedMultipleSameObjects() {
        $object = new \stdClass();

        $serializedString = serialize([$object, $object, $object]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <stdClass>
data[1] = <pointer> 2
data[2] = <pointer> 2

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedReferenceToArray() {
        $array = [];
        $array['inception'] = &$array;

        $serializedString = serialize($array);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data["inception"] = <array>
data["inception"]["inception"] = <reference> 2

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMoreComplexArrayReferences() {
        $parent = [];
        $a = ['parent' => &$parent];
        $b = ['parent' => &$parent];

        $serializedString = serialize([$a, $b]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <array>
data[0]["parent"] = <array>
data[1] = <array>
data[1]["parent"] = <reference> 3

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedEvenMoreComplexArrayReferences() {
        $parent = [];
        $a = ['parent' => &$parent];
        $b = ['parent' => &$parent];

        $a['a'] = &$a;
        $a['b'] = &$b;
        $b['a'] = &$a;
        $b['b'] = &$b;

        $serializedString = serialize([$a, $b]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <array>
data[0]["parent"] = <array>
data[0]["a"] = <array>
data[0]["a"]["parent"] = <reference> 3
data[0]["a"]["a"] = <reference> 4
data[0]["a"]["b"] = <array>
data[0]["a"]["b"]["parent"] = <reference> 3
data[0]["a"]["b"]["a"] = <reference> 4
data[0]["a"]["b"]["b"] = <reference> 5
data[0]["b"] = <reference> 5
data[1] = <array>
data[1]["parent"] = <reference> 3
data[1]["a"] = <reference> 4
data[1]["b"] = <reference> 5

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedReferenceToClass() {
        $class = new \stdClass();
        $class->inception = &$class;

        $serializedString = serialize($class);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <stdClass>
data["inception"] = <reference> 1

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMoreComplexClassReferences() {
        $parent = new \stdClass();
        $a = new \stdClass();
        $b = new \stdClass();

        $a->parent = &$parent;
        $a->a = &$a;
        $a->b = &$b;

        $b->parent = &$parent;
        $b->a = &$a;
        $b->b = &$b;

        $serializedString = serialize([$a, $b]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <stdClass>
data[0]["parent"] = <stdClass>
data[0]["a"] = <reference> 2
data[0]["b"] = <stdClass>
data[0]["b"]["parent"] = <reference> 3
data[0]["b"]["a"] = <reference> 2
data[0]["b"]["b"] = <reference> 4
data[1] = <pointer> 4

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }
}
