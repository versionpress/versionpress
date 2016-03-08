<?php

namespace VersionPress\Utils;

/**
 * This class is useful for setting constants and variables in wp-config.php or wp-config.common.php.
 * It's used mainly from our internal WP-CLI command `update-config`.
 *
 */
class WpConfigEditor {

    private $wpConfigPath;
    private $isCommonConfig;

    public function __construct($wpConfigPath, $isCommonConfig) {
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
    public function updateConfigConstant($constantName, $value, $usePlainValue = false) {
        // https://regex101.com/r/jE0eJ6/2
        $constantRegex = "/^(\\s*define\\s*\\(\\s*['\"]" . preg_quote($constantName, '/') . "['\"]\\s*,\\s*).*(\\s*\\)\\s*;\\s*)$/m";
        $constantTemplate = "define('{$constantName}', %s);\n";

        self::updateConfig($value, $constantRegex, $constantTemplate, $usePlainValue);
    }

    /**
     * Sets value of a variable. It creates new one if it's missing.
     * By default it saves string in single quotes. See $usePlainValue.
     *
     * @param $variableName
     * @param string|number|bool $value
     * @param bool $usePlainValue The value is used as-is, without quoting.
     */
    public function updateConfigVariable($variableName, $value, $usePlainValue = false) {
        // https://regex101.com/r/oO7gX7/5
        $variableRegex = "/^(\\\${$variableName}\\s*=\\s*).*(;\\s*)$/m";
        $variableTemplate = "\${$variableName} = %s;\n";

        self::updateConfig($value, $variableRegex, $variableTemplate, $usePlainValue);
    }

    private function updateConfig($value, $replaceRegex, $definitionTemplate, $usePlainValue) {
        $wpConfigContent = file_get_contents($this->wpConfigPath);

        $phpizedValue = $usePlainValue ? $value : var_export($value, true);

        $configContainsDefinition = preg_match($replaceRegex, $wpConfigContent);

        if ($configContainsDefinition) {
            $wpConfigContent = preg_replace($replaceRegex, "\${1}$phpizedValue\${2}", $wpConfigContent);
        } else {
            $originalContent = $wpConfigContent;
            $endOfEditableSection = $this->isCommonConfig ? strlen($originalContent) : strpos($wpConfigContent, '/* That\'s all, stop editing! Happy blogging. */');

            if ($endOfEditableSection === false) {
                throw new \Exception('Editable section not found.');
            }

            $wpConfigContent = substr($originalContent, 0, $endOfEditableSection);
            $wpConfigContent .= sprintf($definitionTemplate, $phpizedValue);
            $wpConfigContent .= substr($originalContent, $endOfEditableSection);
        }

        file_put_contents($this->wpConfigPath, $wpConfigContent);
    }
}
