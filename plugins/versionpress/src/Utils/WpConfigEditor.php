<?php

namespace VersionPress\Utils;

class WpConfigEditor {

    private $wpConfigPath;
    private $isCommonConfig;

    public function __construct($wpConfigPath, $isCommonConfig) {
        $this->wpConfigPath = $wpConfigPath;
        $this->isCommonConfig = $isCommonConfig;
    }

    public function updateConfigConstant($constantName, $value, $usePlainValue) {
        // https://regex101.com/r/jE0eJ6/2
        $constantRegex = "/^(\\s*define\\s*\\(\\s*['\"]" . preg_quote($constantName, '/') . "['\"]\\s*,\\s*).*(\\s*\\)\\s*;\\s*)$/m";
        $constantTemplate = "define('{$constantName}', %s);\n";

        self::updateConfig($value, $constantRegex, $constantTemplate, $usePlainValue);
    }

    public function updateConfigVariable($variableName, $value, $usePlainValue) {
        // https://regex101.com/r/oO7gX7/5
        $variableRegex = "/^(\\\${$variableName}\\s*=\\s*).*(;\\s*)$/m";
        $variableTemplate = "\$%s = %s;\n";

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
