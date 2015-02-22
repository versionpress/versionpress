<?php

/**
 * Represents test runner options. Options follow these naming conventions:
 *
 *  - `option-name` on command line (written as `--option-name=...`
 *  - `VP_OPTION_NAME` as an environment variable
 *  - `optionName` as a property on this class
 *
 * Command-line parameters take precedence over env variables.
 */
class TestRunnerOptions {

    /**
     * @var TestRunnerOptions
     */
    private static $instance;

    /**
     * @var string
     */
    public $forceSetup;

    protected function __construct() {
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new TestRunnerOptions();
        }
        return self::$instance;
    }

    public function configureInstance($optionsSpecification) {
        foreach ($optionsSpecification as $testingOption => $supportedValues) {

            $envVarValue = $this->getEnvVarValue($testingOption);
            if ($envVarValue !== false) {
                $this->$testingOption = $envVarValue;
            }

            $cliValue = $this->getCliArgValue($testingOption);
            if ($cliValue !== false) {
                $this->$testingOption = $cliValue;
            }

        }
    }

    /**
     * Small wrapper around `getenv()` that properly converts something like 'optionName' to 'VP_OPTION_NAME'.
     *
     * @param string $testingOptionInPropertyConvention Option name in property convention, e.g., 'optionName'
     * @return string|bool False if env var does not exist, string otherwise
     */
    private function getEnvVarValue($testingOptionInPropertyConvention) {
        return getenv(OptionsConventionConverter::getEnvVarOptionName($testingOptionInPropertyConvention));
    }

    /**
     * Wrapper around `getopt()` that supports property naming convention, e.g., 'optionName' instead of '--option-name'.
     *
     * @param $testingOptionInPropertyConvention
     * @return string|bool False if CLI argument was missing or string with its value
     */
    private function getCliArgValue($testingOptionInPropertyConvention) {

        $cliOptionName = OptionsConventionConverter::getCliOptionName($testingOptionInPropertyConvention);
        $cliOptions = getopt("", array("$cliOptionName::"));

        if (!empty($cliOptions)) {
            return $cliOptions[$cliOptionName];
        } else {
            return false;
        }
    }




}
