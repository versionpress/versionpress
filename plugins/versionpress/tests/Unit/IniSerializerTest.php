<?php

namespace VersionPress\Tests\Unit;

use Faker\Provider\Lorem;
use PHPUnit_Framework_TestCase;
use VersionPress\Storages\Serialization\IniSerializer;
use VersionPress\Utils\StringUtils;

/**
 * Tests covering IniSerializer. There are also IniSerializer_Issue* tests which contain
 * tests cases for reported issues.
 */
class IniSerializerTest extends PHPUnit_Framework_TestCase
{


    //--------------------------------
    // Invalid inputs
    //--------------------------------

    /**
     * @test
     */
    public function throwsOnNonSectionedData()
    {
        $this->setExpectedException('Exception');
        IniSerializer::serialize(["key" => "value"]);
    }

    /**
     * @test
     */
    public function throwsOnEmptySection()
    {
        $this->setExpectedException('Exception');
        IniSerializer::serialize(["Section" => []]);
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
    public function smallestPossibleExample()
    {

        $data = ["Section" => ["key" => ""]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function strings()
    {

        $data = ["Section" => ["key1" => "value1", "key2" => "value2"]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function numericValues()
    {

        $data = ["Section" => ["key1" => 0, "key2" => 1, "key3" => 1.1]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function numericStringsSerializedAsStrings()
    {

        $data = ["Section" => ["key1" => "0", "key2" => "1", "key3" => "11.1"]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
key1 = "0"
key2 = "1"
key3 = "11.1"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function arraysAsSquareBrackets()
    {

        $data = ["Section" => ["key" => ["val1", "val2"]]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function backslash_single()
    {

        $data = [
            "Section" => [
                "key1" => "My \\ site"
            ]
        ];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function backslash_double()
    {

        $data = [
            "Section" => [
                "key1" => "My \\\\ site"
            ]
        ];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function backslash_tripple()
    {

        $data = [
            "Section" => [
                "key1" => "My \\\\\\ site"
            ]
        ];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function backslash_atTheEndOfString()
    {

        $data = [
            "Section" => [
                "key1" => "Value \\",
                "key2" => "Value \\",
            ]
        ];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function doubleQuoteEscaping()
    {

        $data = ["Section" => ["key1" => "\"Hello\""]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function doubleQuoteEscapingAtTheEOL()
    {

        $data = ["Section" => ["key1" => "\"\nwhatever\""]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function doubleQuoteEscaping_HereDoc()
    {

        // Note: double quotes in heredoc MUST NOT be escaped with "\", although
        // PHP manual states that it optionally might be
        $data = [
            "Section" => [
                "key1" => <<<VAL
"Hello"
VAL
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function hashSignCommentInsideQuotes()
    {

        $data = [
            "Section" => [
                "key1" => StringUtils::ensureLf(<<<VAL
First line of the value
# Continued value - should not be treated as comment
VAL
                )
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function dollarSignInsideQuotes()
    {
        $data = ["Section" => ["key1" => 'some$value', "key2" => 'another${value']];

        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function semicolonCommentInsideQuotes()
    {

        $data = [
            "Section" => [
                "key1" => StringUtils::ensureLf(<<<VAL
First line of the value
; Continued value - should not be treated as comment
VAL
                )
            ]
        ];

        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function newLineHandlingInsideValues_LF()
    {

        $data = ["Section" => ["key1" => "Hello\nWorld"]];
        $ini = "[Section]\nkey1 = \"Hello\nWorld\"\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CR()
    {

        $data = ["Section" => ["key1" => "Hello\rWorld"]];
        $ini = "[Section]\nkey1 = \"Hello\rWorld\"\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CRLF()
    {

        $data = ["Section" => ["key1" => "Hello\r\nWorld"]];
        $ini = "[Section]\nkey1 = \"Hello\r\nWorld\"\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandling_BlankLines()
    {

        $data = ["Section" => ["key1" => "\r\n"]];
        $ini = "[Section]\nkey1 = \"\r\n\"\n";

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandling_NewLineAfterStringMark()
    {
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
key1 = "$
"

INI
        );

        $data = [
            "Section" => [
                "key1" => StringUtils::ensureLf("$
")
            ]
        ];

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function newLineHandling_SupportsCRLFOnInput() {

        // See smallestPossibleExample()
        $data = ["Section" => ["key" => ""]];
        $inputWithCRLF = "[Section]\r\nkey = \"\"\r\n";
        $outputWithLF = "[Section]\nkey = \"\"\n";

        $this->assertSame($data, IniSerializer::deserialize($inputWithCRLF));
        $this->assertSame($outputWithLF, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function specialCharactersAreTakenLiterally()
    {

        // e.g., "\n" should not have any special meaning

        $data = ["Section" => ["key1" => '\n']];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function specialCharactersInSectionName($specialCharacter)
    {

        $data = ["Sect{$specialCharacter}ion" => ["somekey" => "value"]];
        $ini = StringUtils::ensureLf(<<<INI
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
    public function specialCharacterInKey($specialCharacter)
    {

        $data = ["Section" => ["some{$specialCharacter}key" => "value"]];
        $ini = StringUtils::ensureLf(<<<INI
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
    public function specialCharacterInValue($specialCharacter)
    {

        $data = ["Section" => ["somekey" => "val{$specialCharacter}ue"]];
        $ini = StringUtils::ensureLf(<<<INI
[Section]
somekey = "val{$specialCharacter}ue"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    public function specialCharactersProvider()
    {
        return array_map(
            function ($specialChar) {
                return [$specialChar];
            },
            [
                "\\",
                "\"",
                "[]",
                "$",
                "%",
                "'",
                ";",
                "+",
                "-",
                "/",
                "#",
                "&",
                "!",
                ".",
                "~",
                "^",
                "`",
                "?",
                ":",
                ",",
                "*",
                "<",
                ">",
                "(",
                ")",
                "@",
                "{",
                "}",
                "|",
                "_",
                " ",
                "\t",
                "ěščřžýáíéúůóďťňôâĺ",
                "茶",
                "русский",
                "حصان",
                "="
            ]
        );
    }

    public function specialCharactersInValueProvider()
    {
        // Double quotes and backslashes are escaped see WP-458 and WP-619
        return array_filter($this->specialCharactersProvider(), function ($val) {
            return $val[0] !== "\"" && $val[0] !== "\\";
        });
    }

    /**
     * @test
     */
    public function twoSections()
    {

        $data = ["Section1" => ["key" => "value"], "Section2" => ["key" => "value"]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function sectionWithDotInName()
    {
        $data = ["Section.Name" => ["key" => "value"]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedString()
    {
        $serializedString = serialize('some string');

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedInteger()
    {
        $serializedString = serialize(777);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedDouble()
    {
        $serializedString = serialize(1.2);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedBoolean()
    {
        $serializedString = serialize(false);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedEmptyArray()
    {
        $serializedString = serialize([]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedArrayWithString()
    {
        $serializedString = serialize(['some string']);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
     */
    public function serializedArrayWithNegativeInteger()
    {
        $serializedString = serialize([-1]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = -1

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     * @dataProvider specialCharactersInValueProvider
     */
    public function serializedArrayWithSpecialStrings($str)
    {
        $serializedString = serialize([$str, $str, $str]);

        $data = ["Section" => ["data" => $serializedString]];

        $ini = StringUtils::ensureLf(<<<INI
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
    public function serializedArrayWithEscapedSpecialStrings()
    {
        $serializedString = serialize(["\\", "\"", "\\\""]);

        $data = ["Section" => ["data" => $serializedString]];

        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = "\\"
data[1] = "\""
data[2] = "\\\""

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedHTMLString()
    {
        $arrayWithHtml = ["meta_key" => "who_is_the_best", "meta_value" => "<p>VersionPress</p>"];
        $serializedString = serialize($arrayWithHtml);
        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data["meta_key"] = "who_is_the_best"
data["meta_value"] = "<p>VersionPress</p>"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function serializedStringWithNewLines()
    {
        $arrayWithNewLines = ["meta_key" => "who_is_the_best", "meta_value" => "VersionPress\nis\nthe\nbest"];
        $serializedString = serialize($arrayWithNewLines);
        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data["meta_key"] = "who_is_the_best"
data["meta_value"] = "VersionPress
is
the
best"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));

    }
    /**
     * @test
     */
    public function serializedOption()
    {
        $sidebarWidgets = [
            'wp_inactive_widgets' => [],
            'sidebar-1' => ['search-2', 'recent-posts-2', 'recent-comments-2'],
            'array_version' => 3,
        ];


        $serializedString = serialize($sidebarWidgets);

        $data = ["sidebar_widgets" => ["option_value" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedComplexArray()
    {
        $array = [
            'numeric index',
            'string index' => 1234,
            'nested array' => [0 => 'some', 345 => 'sparse', 1234 => 'array'],
            'even more nested arrays' => [['array', ['in array', ['in array', 'with mixed' => 'keys']]]],
            'and bool' => true
        ];


        $serializedString = serialize($array);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedEmptyStdClass()
    {
        $serializedString = serialize(new \stdClass());

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedStdClassWithAttribute()
    {
        $object = new \stdClass();
        $object->attribute = 'value';

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedStdClassWithMultipleAttributes()
    {
        $object = new \stdClass();
        $object->stringAttribute = 'value';
        $object->numericAttribute = 1.004;
        $object->boolAttribute = false;
        $object->arrayAttribute = ['array'];

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedCustomClassWithPublicAttribute()
    {
        $object = new IniSerializer_FooPublic('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedCustomClassWithProtectedAttribute()
    {
        $object = new IniSerializer_FooProtected('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedCustomClassWithPrivateAttribute()
    {
        $object = new IniSerializer_FooPrivate('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedCustomClassWithSleepMethod()
    {
        $object = new IniSerializer_FooWithCleanup('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedCustomClassWithPrivateAttributeInParent()
    {
        $object = new IniSerializer_FooPrivateChild('value');

        $serializedString = serialize($object);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <VersionPress\Tests\Unit\IniSerializer_FooPrivateChild>
data["-VersionPress\\Tests\\Unit\\IniSerializer_FooPrivate->attribute"] = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedNull()
    {
        $serializedString = serialize(null);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
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
    public function serializedMultipleSameObjects()
    {
        $object = new \stdClass();

        $serializedString = serialize([$object, $object, $object]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <stdClass>
data[1] = <*pointer*> 2
data[2] = <*pointer*> 2

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMultipleDifferentObjects()
    {
        $serializedString = serialize([new \stdClass(), new \stdClass(), new \stdClass()]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <stdClass>
data[1] = <stdClass>
data[2] = <stdClass>

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedReferenceToArray()
    {
        $array = [];
        $array['inception'] = &$array;

        $serializedString = serialize($array);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data["inception"] = <array>
data["inception"]["inception"] = <*reference*> 2

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMoreComplexArrayReferences()
    {
        $parent = [];
        $a = ['parent' => &$parent];
        $b = ['parent' => &$parent];

        $serializedString = serialize([$a, $b]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <array>
data[0]["parent"] = <array>
data[1] = <array>
data[1]["parent"] = <*reference*> 3

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedEvenMoreComplexArrayReferences()
    {
        $parent = [];
        $a = ['parent' => &$parent];
        $b = ['parent' => &$parent];

        $a['a'] = &$a;
        $a['b'] = &$b;
        $b['a'] = &$a;
        $b['b'] = &$b;

        $serializedString = serialize([$a, $b]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <array>
data[0]["parent"] = <array>
data[0]["a"] = <array>
data[0]["a"]["parent"] = <*reference*> 3
data[0]["a"]["a"] = <*reference*> 4
data[0]["a"]["b"] = <array>
data[0]["a"]["b"]["parent"] = <*reference*> 3
data[0]["a"]["b"]["a"] = <*reference*> 4
data[0]["a"]["b"]["b"] = <*reference*> 5
data[0]["b"] = <*reference*> 5
data[1] = <array>
data[1]["parent"] = <*reference*> 3
data[1]["a"] = <*reference*> 4
data[1]["b"] = <*reference*> 5

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedReferenceToClass()
    {
        $class = new \stdClass();
        $class->inception = &$class;

        $serializedString = serialize($class);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <stdClass>
data["inception"] = <*reference*> 1

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMoreComplexClassReferences()
    {
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
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <stdClass>
data[0]["parent"] = <stdClass>
data[0]["a"] = <*reference*> 2
data[0]["b"] = <stdClass>
data[0]["b"]["parent"] = <*reference*> 3
data[0]["b"]["a"] = <*reference*> 2
data[0]["b"]["b"] = <*reference*> 4
data[1] = <*pointer*> 4

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function serializedMultipleDifferentData()
    {

        $data = [
            123,
            4.5,
            true,
            "VP",
            null,
            new \stdClass(),
            [123, 4.5, true, "VP", null, new \stdClass()],
        ];

        $serializedString = serialize($data);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = 123
data[1] = 4.5
data[2] = <boolean> true
data[3] = "VP"
data[4] = <null>
data[5] = <stdClass>
data[6] = <array>
data[6][0] = 123
data[6][1] = 4.5
data[6][2] = <boolean> true
data[6][3] = "VP"
data[6][4] = <null>
data[6][5] = <stdClass>

INI
        );

        $this->assertSame($data, IniSerializer::deserialize($ini));
        $this->assertSame($ini, IniSerializer::serialize($data));
    }

    /**
     * @test
     */
    public function longString()
    {
        $loremIpsum = StringUtils::ensureLf(Lorem::text(50000));

        $data = ["Section" => ["key" => $loremIpsum]];
        $ini = StringUtils::ensureLf(<<<INI
[Section]
key = "$loremIpsum"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializationDoesntChangeTheOrder()
    {
        $serializedString = serialize(777);

        $data = ["Section" => ["data" => $serializedString, "another_data" => "value"]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> 777
another_data = "value"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializationDoesntChangeTypeOfNumericString()
    {

        $data = ["Section" => ["data" => "777"]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = "777"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializationDoesntChangeTypeOfNumericStringInSerializedData()
    {
        $serializedString = serialize("777");

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> "777"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializationDoesntChangeTypeOfNumericStringInArray()
    {
        $serializedString = serialize(["777"]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = "777"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedArrayInSerializedArray()
    {
        $serializedString = serialize([serialize(['some string'])]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <<<serialized>>> <array>
data[0][0] = "some string"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function serializedStringInSerializedArray()
    {
        $serializedString = serialize([serialize('some string')]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <<<serialized>>> "some string"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function doubleSerializedString()
    {
	    $serializedValue = serialize(serialize('test'));

        $data = ["Section" => ["data" => $serializedValue ] ];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <<<serialized>>> "test"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function doubleSerializedArray()
    {
	    $serializedValue = serialize(serialize(['test']));

        $data = ["Section" => ["data" => $serializedValue ] ];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <<<serialized>>> <array>
data[0] = "test"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function threeNestedSerializedValues()
    {
        $stdClass = new \stdClass();
        $stdClass->data = serialize('some string');
        $serializedString = serialize([serialize($stdClass)]);

        $data = ["Section" => ["data" => $serializedString]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <<<serialized>>> <array>
data[0] = <<<serialized>>> <stdClass>
data[0]["data"] = <<<serialized>>> "some string"

INI
        );

        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function nullValueSerializesCorrectly()
    {
        $data = ["Section" => ["data" => null]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = <null>

INI
        );
        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }

    /**
     * @test
     */
    public function stringContaingNullPlaceholderIsDeserializedToOriginalString()
    {
        $data = ["Section" => ["data" => "<null>"]];
        $ini = StringUtils::ensureLf(<<<'INI'
[Section]
data = "<null>"

INI
        );
        $this->assertSame($ini, IniSerializer::serialize($data));
        $this->assertSame($data, IniSerializer::deserialize($ini));
    }
}
