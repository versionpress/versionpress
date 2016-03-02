<?php

namespace VersionPress\Utils;

class WordPressMissingFunctions {

    public static function getWpConfigPath() {
        $defaultWpConfigPath = realpath(ABSPATH . 'wp-config.php');
        $elevatedWpConfigPath = realpath(ABSPATH . '../wp-config.php');

        if (is_file($defaultWpConfigPath)) {
            return $defaultWpConfigPath;
        }

        return $elevatedWpConfigPath;
    }

    public static function updateConfigConstant($wpConfigPath, $constantName, $value, $usePlainValue) {
        // https://regex101.com/r/jE0eJ6/2
        $constantRegex = "/^(\\s*define\\s*\\(\\s*['\"]" . preg_quote($constantName, '/') . "['\"]\\s*,\\s*).*(\\s*\\)\\s*;\\s*)$/m";
        $constantTemplate = "define('{$constantName}', %s);\n";

        self::banan($wpConfigPath, $value, $constantRegex, $constantTemplate, $usePlainValue);
    }

    public static function updateConfigVariable($wpConfigPath, $variableName, $value, $usePlainValue) {
        // https://regex101.com/r/oO7gX7/5
        $variableRegex = "/^(\\\${$variableName}\\s*=\\s*).*(;\\s*)$/m";
        $variableTemplate = "\$%s = %s;\n";

        self::banan($wpConfigPath, $value, $variableRegex, $variableTemplate, $usePlainValue);
    }

    private static function banan($wpConfigPath, $value, $replaceRegex, $definitionTemplate, $usePlainValue) {
        $wpConfigContent = file_get_contents($wpConfigPath);

        $phpizedValue = $usePlainValue ? $value : var_export($value, true);

        $configContainsDefinition = preg_match($replaceRegex, $wpConfigContent);

        if ($configContainsDefinition) {
            $wpConfigContent = preg_replace($replaceRegex, "\${1}$phpizedValue\${2}", $wpConfigContent);
        } else {
            $originalContent = $wpConfigContent;
            $endOfEditableSection = strpos($wpConfigContent, '/* That\'s all, stop editing! Happy blogging. */');

            if ($endOfEditableSection === false) {
                throw new \Exception('Editable section not found.');
            }

            $wpConfigContent = substr($originalContent, 0, $endOfEditableSection);
            $wpConfigContent .= sprintf($definitionTemplate, $phpizedValue);
            $wpConfigContent .= substr($originalContent, $endOfEditableSection);
        }

        file_put_contents($wpConfigPath, $wpConfigContent);
    }
}
