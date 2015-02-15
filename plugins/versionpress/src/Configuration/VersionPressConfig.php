<?php

namespace VersionPress\Configuration;

use Nette\Neon\Neon;

/**
 * Parses and makes accessible VersionPress configuration.
 *
 * The configuration comes from two files:
 *
 *  - vpconfig.defaults.neon - here all the options with their defaults are defined
 *  - vpconfig.neon - empty by default but the user can override some or all of the options here
 */
class VersionPressConfig {

    public $defaults = array();
    public $customConfig = array();
    public $mergedConfig = array();

    public $gitBinary;

    function __construct() {

        $defaultsFile = VERSIONPRESS_PLUGIN_DIR . '/vpconfig.defaults.neon';
        $customConfigFile = VERSIONPRESS_PLUGIN_DIR . '/vpconfig.neon';

        $this->defaults = Neon::decode(file_get_contents($defaultsFile));

        if (file_exists($customConfigFile)) {
            $this->customConfig = Neon::decode(file_get_contents($customConfigFile));
            if ($this->customConfig === null) {
                $this->customConfig = array();
            }
        }

        $this->mergedConfig = array_merge($this->defaults, $this->customConfig);

        $this->gitBinary = $this->mergedConfig['git-binary'];

    }


}