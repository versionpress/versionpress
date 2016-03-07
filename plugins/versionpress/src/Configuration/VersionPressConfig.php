<?php

namespace VersionPress\Configuration;

use Symfony\Component\Yaml\Yaml;

/**
 * Parses and makes accessible VersionPress configuration.
 *
 * The configuration comes from two files:
 *
 *  - vpconfig.defaults.yml - here all the options with their defaults are defined
 *  - vpconfig.yml - empty by default but the user can override some or all of the options here
 */
class VersionPressConfig {

    public $defaults = array(
        'gui' => 'javascript',
        'requireApiAuth' => true
    );

    public $customConfig = array();
    public $mergedConfig = array();

    public $gitBinary;

    function __construct() {

        $defaultsFile = VERSIONPRESS_PLUGIN_DIR . '/vpconfig.defaults.yml';
        $customConfigFile = VERSIONPRESS_PLUGIN_DIR . '/vpconfig.yml';

        $this->defaults = array_merge($this->defaults, Yaml::parse(file_get_contents($defaultsFile)));

        if (file_exists($customConfigFile)) {
            $this->customConfig = Yaml::parse(file_get_contents($customConfigFile));
            if ($this->customConfig === null) {
                $this->customConfig = array();
            }
        }

        $this->mergedConfig = array_merge($this->defaults, $this->customConfig);

        $this->gitBinary = $this->mergedConfig['git-binary'];

    }


}
