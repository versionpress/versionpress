<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Initialization\WpConfigSplitter;

class WpConfigSplitterTest extends \PHPUnit_Framework_TestCase
{
    // @codingStandardsIgnoreStart
    private $originalConfig = <<<'DOC'
<?php

// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'vp01');

/** MySQL database username */
define('DB_USER', 'vp01');

/** MySQL database password */
define('DB_PASSWORD', 'vp01');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


$table_prefix = 'wp_';

define( 'WP_CONTENT_DIR', '/Users/johndoe/Sites/wp/app' ); // Do not remove. Removing this line could break your site. Added by Security > Settings > Change Content Directory.
define( 'WP_CONTENT_URL', 'http://localhost/wp/app' ); // Do not remove. Removing this line could break your site. Added by Security > Settings > Change Content Directory.

define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/addons');
define('WP_PLUGIN_URL', 'http://localhost/wp/app/addons');

define('UPLOADS', 'uploads');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'AUTOMATIC_UPDATER_DISABLED', true );

DOC;

    private $filteredConfig = <<<'DOC'
<?php

// Configuration common to all environments
include_once __DIR__ . '/wp-config.common.php';


// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'vp01');

/** MySQL database username */
define('DB_USER', 'vp01');

/** MySQL database password */
define('DB_PASSWORD', 'vp01');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


$table_prefix = 'wp_';

define( 'WP_CONTENT_URL', 'http://localhost/wp/app' ); // Do not remove. Removing this line could break your site. Added by Security > Settings > Change Content Directory.

define('WP_PLUGIN_URL', 'http://localhost/wp/app/addons');


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'AUTOMATIC_UPDATER_DISABLED', true );

DOC;

    private $commonConfig = <<<'DOC'
<?php
// Configuration common to all VersionPress environments, included from `wp-config.php`.
// Learn more at https://docs.versionpress.net/en/getting-started/configuration
define( 'WP_CONTENT_DIR', '/Users/johndoe/Sites/wp/app' ); // Do not remove. Removing this line could break your site. Added by Security > Settings > Change Content Directory.
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/addons');
define('UPLOADS', 'uploads');

DOC;
    // @codingStandardsIgnoreEnd

    private $commonConfigName = 'wp-config.common.php';
    private $wpConfigPath;
    private $commonConfigPath;

    protected function setUp()
    {
        $this->wpConfigPath = __DIR__ . '/wp-config.php';
        $this->commonConfigPath = dirname($this->wpConfigPath) . '/' . $this->commonConfigName;
        file_put_contents($this->wpConfigPath, $this->originalConfig);
        @unlink($this->commonConfigPath);
    }

    protected function tearDown()
    {
        @unlink($this->wpConfigPath);
        @unlink($this->commonConfigPath);
    }

    /**
     * @test
     */
    public function splitterExtractsDesiredConstantsIntoSeparateFile()
    {
        WpConfigSplitter::split($this->wpConfigPath, $this->commonConfigName);
        $filteredConfig = file_get_contents($this->wpConfigPath);
        $commonConfig = file_get_contents($this->commonConfigPath);

        $this->assertEquals($this->filteredConfig, $filteredConfig);
        $this->assertEquals($this->commonConfig, $commonConfig);
    }

    /**
     * @test
     */
    public function runningSplitterMultipleTimesDoesntChangeTheOutput()
    {
        WpConfigSplitter::split($this->wpConfigPath, $this->commonConfigName);
        WpConfigSplitter::split($this->wpConfigPath, $this->commonConfigName);
        WpConfigSplitter::split($this->wpConfigPath, $this->commonConfigName);


        $filteredConfig = file_get_contents($this->wpConfigPath);
        $commonConfig = file_get_contents($this->commonConfigPath);

        $this->assertEquals($this->filteredConfig, $filteredConfig);
        $this->assertEquals($this->commonConfig, $commonConfig);
    }
}
