<?php


namespace VersionPress\Tests\Unit;

use PHPUnit_Framework_TestCase;
use VersionPress\Tests\Utils\OptionsConventionConverter;

class OptionsConventionConverterTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function convertsFromPropertyConventionToEnvVarConvention()
    {
        $this->assertEquals("VP_OPTION_NAME", OptionsConventionConverter::getEnvVarOptionName("optionName"));
        $this->assertEquals("VP_OPTION", OptionsConventionConverter::getEnvVarOptionName("option"));
    }

    /**
     * @test
     */
    public function convertsFromPropertyConventionToCliConvention()
    {
        $this->assertEquals("option-name", OptionsConventionConverter::getCliOptionName("optionName"));
        $this->assertEquals("option", OptionsConventionConverter::getCliOptionName("option"));
    }
}
