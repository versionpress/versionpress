<?php


use VersionPress\Utils\IniSerializer;
use VersionPress\Utils\StringUtils;

/**
 * Tests covering IniSerializer. There is also {@link IniSerializerIssuesTest} which contains
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
        IniSerializer::serializeSectionedData(array("key" => "value"));
    }

    /**
     * @test
     */
    public function throwsOnEmptySection() {
        $this->setExpectedException('Exception');
        IniSerializer::serializeSectionedData(array("Section" => array()));
    }



    //--------------------------------
    // The most basic test
    //--------------------------------


    /**
     * Simplest possible sectioned INI - anything less that this throws. A couple of required elements can be seen here:
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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }


    //--------------------------------
    // Escaping / special characters
    //--------------------------------

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }


    /**
     * @test
     */
    public function newLineHandlingInsideValues_LF() {

        $data = array("Section" => array("key1" => "Hello\nWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\nWorld\"\r\n";

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CR() {

        $data = array("Section" => array("key1" => "Hello\rWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\rWorld\"\r\n";

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandlingInsideValues_CRLF() {

        $data = array("Section" => array("key1" => "Hello\r\nWorld"));
        $ini = "[Section]\r\nkey1 = \"Hello\r\nWorld\"\r\n";

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function newLineHandling_BlankLines() {

        $data = array("Section" => array("key1" => "\r\n"));
        $ini = "[Section]\r\nkey1 = \"\r\n\"\r\n";

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }



    //--------------------------------
    // Subsections
    //--------------------------------

    /**
     * @test
     */
    public function singleSubsection() {

        $data = array("Section" => array("Subsection" => array("key" => "value")));
        $ini = StringUtils::crlfize(<<<'INI'
[Section.Subsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function deepSubsections() {

        $data = array(
            "Section" => array(
                "Subsection" => array(
                    "SubSubsection" => array(
                        "SubSubSubsection" => array("key" => "value")
                    )
                )
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section.Subsection.SubSubsection.SubSubSubsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function deepSubsectionsMixedWithData() {

        $data = array(
            "Section" => array(
                "key" => "value",
                "Subsection" => array(
                    "SubSubsection" => array(
                        "key" => "value",
                        "SubSubSubsection" => array("key" => "value")
                    )
                )
            )
        );
        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key = "value"
[Section.Subsection.SubSubsection]
key = "value"
[Section.Subsection.SubSubsection.SubSubSubsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function twoSubsections() {

        $data = array("Section" => array(
            "Subsection1" => array("key" => "value"),
            "Subsection2" => array("key" => "value")
        ));

        $ini = StringUtils::crlfize(<<<'INI'
[Section.Subsection1]
key = "value"

[Section.Subsection2]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function subsectionMixedWithData() {

        $data = array("Section" => array(
            "key" => "value",
            "Subsection" => array("key" => "value")
        ));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key = "value"
[Section.Subsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }

    /**
     * @test
     */
    public function subsectionMixedWithDataInWrongOrder() {

        // "Wrong" order - key-value must appear before Subsection so that it doesn't belong
        // to the subsection in the INI format
        $data = array("Section" => array(
            "Subsection" => array("key" => "value"),
            "key" => "value"
        ));

        $ini = StringUtils::crlfize(<<<'INI'
[Section]
key = "value"
[Section.Subsection]
key = "value"

INI
        );

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
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

        $this->assertEquals($ini, IniSerializer::serializeSectionedData($data));
        $this->assertEquals($data, IniSerializer::deserialize($ini));

    }


}
