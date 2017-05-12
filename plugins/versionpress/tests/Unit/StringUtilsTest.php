<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\StringUtils;

class StringUtilsTest extends \PHPUnit_Framework_TestCase
{

    public function lfDataProvider() {
        return [
            ['\\n', '\\n'], // no conversion
            ['\\r\\n', '\\n'], // simplest conversion
            ['a\\r\\nb', 'a\\nb'], // small real-world example
            ['a\\r\\nb\\r\\n', 'a\\nb\\n'], // ... including trailing blank line
            ['a\\r\\nb\\nc', 'a\\nb\\nc'], // mixed line endings
        ];
    }

    /**
     * @test
     * @dataProvider lfDataProvider
     */
    public function ensureLfWorks($input, $output)
    {
        $this->assertEquals($output, StringUtils::ensureLf($input));
    }

}
