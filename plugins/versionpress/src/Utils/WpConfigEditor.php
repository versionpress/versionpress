<?php

namespace VersionPress\Utils;

/**
 * This class is useful for setting constants and variables in wp-config.php or wp-config.common.php.
 * It's used mainly from our internal WP-CLI command `update-config`.
 *
 */
class WpConfigEditor
{

    private $wpConfigPath;
    private $isCommonConfig;

    private static $vpPublicConstants = [
        'VP_VPDB_DIR',
        'VP_PROJECT_ROOT',
        'VP_ENVIRONMENT',
        'VP_GIT_BINARY',
    ];

    public function __construct($wpConfigPath, $isCommonConfig)
    {
        $this->wpConfigPath = $wpConfigPath;
        $this->isCommonConfig = $isCommonConfig;
    }

    /**
     * Sets value of a constant. It creates new one if it's missing.
     * By default it saves string in single quotes. See $usePlainValue.
     *
     * @param $constantName
     * @param string|number|bool $value
     * @param bool $usePlainValue The value is used as-is, without quoting.
     */
    public function updateConfigConstant($constantName, $value, $usePlainValue = false)
    {
        // https://regex101.com/r/jE0eJ6/2
        $constantRegex = "/^(\\s*define\\s*\\(\\s*['\"]" . preg_quote($constantName, '/') .
            "['\"]\\s*,\\s*).*(\\s*\\)\\s*;\\s*)$/m";
        $constantTemplate = "define('{$constantName}', %s);\n";

        self::updateConfig($value, $constantRegex, $constantTemplate, $usePlainValue);
    }

    /**
     * Removes VersionPress public constants from config files.
     * @param array $configFiles List of config file paths from which constants should be removed.
     */
    public static function removeVersionPressConstants($configFiles)
    {
        foreach ($configFiles as $configFile) {
            $constantsForRegex = join('|', self::$vpPublicConstants);
            // https://regex101.com/r/zD3mJ4/2
            $defineRegexPattern = "/(define\\s*\\(\\s*['\"]($constantsForRegex)['\"]\\s*,.*\\)\\s*;)/m";
            $wpConfigContent = file_get_contents($configFile);
            file_put_contents($configFile, preg_replace($defineRegexPattern, '', $wpConfigContent));
        }
    }

    /**
     * Sets value of a variable. It creates new one if it's missing.
     * By default it saves string in single quotes. See $usePlainValue.
     *
     * @param $variableName
     * @param string|number|bool $value
     * @param bool $usePlainValue The value is used as-is, without quoting.
     */
    public function updateConfigVariable($variableName, $value, $usePlainValue = false)
    {
        // https://regex101.com/r/oO7gX7/5
        $variableRegex = "/^(\\\${$variableName}\\s*=\\s*).*(;\\s*)$/m";
        $variableTemplate = "\${$variableName} = %s;\n";

        self::updateConfig($value, $variableRegex, $variableTemplate, $usePlainValue);
    }

    private function updateConfig($value, $replaceRegex, $definitionTemplate, $usePlainValue)
    {
        $wpConfigContent = file_get_contents($this->wpConfigPath);

        $phpizedValue = preg_quote($usePlainValue ? $value : var_export($value, true), '/');

        $configContainsDefinition = preg_match($replaceRegex, $wpConfigContent);

        if ($configContainsDefinition) {
            $wpConfigContent = preg_replace($replaceRegex, "\${1}$phpizedValue\${2}", $wpConfigContent);
        } else {
            $originalContent = $wpConfigContent;
            $endOfEditableSection = $this->isCommonConfig ?
                strlen($originalContent) :
                $this->findPositionForAddingNewDefinition($wpConfigContent);

            if ($endOfEditableSection === false) {
                throw new \Exception('Editable section not found.');
            }

            $wpConfigContent = substr($originalContent, 0, $endOfEditableSection);
            $wpConfigContent .= sprintf($definitionTemplate, $phpizedValue);
            $wpConfigContent .= substr($originalContent, $endOfEditableSection);
        }

        file_put_contents($this->wpConfigPath, $wpConfigContent);
    }

    private function findPositionForAddingNewDefinition($wpConfigContent)
    {
        // https://regex101.com/r/aB8rY4/1
        $thatsAllCommentPattern = "/\\/\\*.*!.*\\*\\//"; // one-line comment containing exclamation mark
        preg_match($thatsAllCommentPattern, $wpConfigContent, $matches, PREG_OFFSET_CAPTURE);

        if ($matches) {
            return $matches[0][1];
        }

        // https://regex101.com/r/fY6eC6/1
        $ifDefinedAbspathPattern = "/if.*defined.*ABSPATH.*/";
        preg_match($ifDefinedAbspathPattern, $wpConfigContent, $matches, PREG_OFFSET_CAPTURE);

        if ($matches) {
            return $matches[0][1];
        }

        // https://regex101.com/r/vG5rB0/1
        $requireWpSettingsPattern = "/require.*wp-settings/";
        preg_match($requireWpSettingsPattern, $wpConfigContent, $matches, PREG_OFFSET_CAPTURE);

        if ($matches) {
            return $matches[0][1];
        }

        return 0;
    }
}
