<?php

namespace VersionPress\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use VersionPress\Utils\WpConfigEditor;

class WpConfigEditorTest extends \PHPUnit_Framework_TestCase
{

    private $configPath;
    private $commonConfigPath;

    protected function setUp()
    {
        $root = vfsStream::setup();
        $virtualConfig = vfsStream::newFile('wp-config.common.php')->at($root);
        $virtualCommonConfig = vfsStream::newFile('wp-config.common.php')->at($root);
        $this->configPath = $virtualConfig->url();
        $this->commonConfigPath = $virtualCommonConfig->url();
    }

// ---------- wp-config.common.php ------------

    /**
     * @test
     */
    public function editorCreatesNewConstantInCommonConfigIfItsMissing()
    {
        file_put_contents($this->commonConfigPath, "<?php\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', 'value');

        $expectedContent = "<?php\ndefine('TEST', 'value');\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesExistingConstantInCommonConfig()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('TEST', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', 'another value');

        $expectedContent = "<?php\ndefine('TEST', 'another value');\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorAppendsNewConstantsAtTheEndOfCommonConfig()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('EXISTING_CONSTANT', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', 'another value');

        $expectedContent = "<?php\ndefine('EXISTING_CONSTANT', 'value');\ndefine('TEST', 'another value');\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesIntValuesAsIntegers()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('TEST', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', 3);

        $expectedContent = "<?php\ndefine('TEST', 3);\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesBoolValuesAsBooleans()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('TEST', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', false);

        $expectedContent = "<?php\ndefine('TEST', false);\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesPlainValuesOfConstantsCorrectly()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('TEST', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', '__DIR__ . "/some-path"', true);

        $expectedContent = "<?php\ndefine('TEST', __DIR__ . \"/some-path\");\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesOnlyDesiredConstantInCommonConfig()
    {
        file_put_contents(
            $this->commonConfigPath,
            "<?php\ndefine('TEST', 'value');\ndefine('MY_CONSTANT', 'value');\n"
        );

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('MY_CONSTANT', 'another value');

        $expectedContent = "<?php\ndefine('TEST', 'value');\ndefine('MY_CONSTANT', 'another value');\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorCreatesNewVariableInCommonConfigIfItsMissing()
    {
        file_put_contents($this->commonConfigPath, "<?php\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', 'value');

        $expectedContent = "<?php\n\$test = 'value';\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesExistingVarialbeInCommonConfig()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$test = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', 'another value');

        $expectedContent = "<?php\n\$test = 'another value';\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorAppendsNewVariablesAtTheEndOfCommonConfig()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$existing_variable = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', 'another value');

        $expectedContent = "<?php\n\$existing_variable = 'value';\n\$test = 'another value';\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesIntVariablesAsIntegers()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$test = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', 3);

        $expectedContent = "<?php\n\$test = 3;\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesBoolVariablesAsBooleans()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$test = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', false);

        $expectedContent = "<?php\n\$test = false;\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesPlainValuesOfVariablesCorrectly()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$test = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('test', '__DIR__ . "/some-path"', true);

        $expectedContent = "<?php\n\$test = __DIR__ . \"/some-path\";\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesOnlyDesiredVariableInCommonConfig()
    {
        file_put_contents($this->commonConfigPath, "<?php\n\$test = 'value';\n\$my_variable = 'value';\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigVariable('my_variable', 'another value');

        $expectedContent = "<?php\n\$test = 'value';\n\$my_variable = 'another value';\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorSavesValueWithRegexReferenceCorrectly()
    {
        file_put_contents($this->commonConfigPath, "<?php\ndefine('TEST', 'value');\n");

        $a = new WpConfigEditor($this->commonConfigPath, true);
        $a->updateConfigConstant('TEST', 'value with regex reference $1');

        $expectedContent = "<?php\ndefine('TEST', 'value with regex reference $1');\n";

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

// ---------- wp-config.php ------------

    /**
     * @test
     */
    public function editorCreatesNewConstantInWpConfigIfItsMissing()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('TEST', 'value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'TEST\', \'value\');
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesExistingConstantInWpConfig()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'TEST\', \'value\');
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('TEST', 'another value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'TEST\', \'another value\');
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesOnlyDesiredConstantInWpConfig()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'MY_CONSTANT\', \'value\');
define(\'TEST\', \'value\');
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('MY_CONSTANT', 'another value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'MY_CONSTANT\', \'another value\');
define(\'TEST\', \'value\');
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorCreatesNewVariableInWpConfigIfItsMissing()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigVariable('test', 'value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



$test = \'value\';
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesExistingValueInWpConfig()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



$test = \'value\';
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigVariable('test', 'another value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



$test = \'another value\';
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorUpdatesOnlyDesiredValueInWpConfig()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';


$my_variable = \'value\';
$test = \'value\';
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigVariable('my_variable', 'another value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';


$my_variable = \'another value\';
$test = \'value\';
/* That\'s all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorWorksWithLocalizedWpConfig()
    {
        file_put_contents($this->commonConfigPath, '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



/* C\'est tout, ne touchez pas à ce qui suit ! Bon blogging ! */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('TEST', 'value');

        $expectedContent = '<?php
// ** MySQL settings ** //
/** The name of the database for WordPress */
define(\'DB_NAME\', \'vp01\');

$table_prefix = \'wp_\';



define(\'TEST\', \'value\');
/* C\'est tout, ne touchez pas à ce qui suit ! Bon blogging ! */

/** Absolute path to the WordPress directory. */
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorWorksWithoutHappyBloggingComment()
    {
        file_put_contents($this->commonConfigPath, '<?php
define(\'DB_NAME\', \'vp01\');
$table_prefix = \'wp_\';

if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');
require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('TEST', 'value');

        $expectedContent = '<?php
define(\'DB_NAME\', \'vp01\');
$table_prefix = \'wp_\';

define(\'TEST\', \'value\');
if ( !defined(\'ABSPATH\') )
    define(\'ABSPATH\', dirname(__FILE__) . \'/\');
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }

    /**
     * @test
     */
    public function editorWorksWithoutDefiningAbspath()
    {
        file_put_contents($this->commonConfigPath, '<?php
define(\'DB_NAME\', \'vp01\');
$table_prefix = \'wp_\';

require_once(ABSPATH . \'wp-settings.php\');
');

        $a = new WpConfigEditor($this->commonConfigPath, false);
        $a->updateConfigConstant('TEST', 'value');

        $expectedContent = '<?php
define(\'DB_NAME\', \'vp01\');
$table_prefix = \'wp_\';

define(\'TEST\', \'value\');
require_once(ABSPATH . \'wp-settings.php\');
';

        $this->assertEquals($expectedContent, file_get_contents($this->commonConfigPath));
    }
}
