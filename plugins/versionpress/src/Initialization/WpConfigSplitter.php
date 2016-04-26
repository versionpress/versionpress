<?php

namespace VersionPress\Initialization;

use Nette\Utils\Strings;

/**
 * Used for creating wp-config.common.php.
 *
 * Extracts constants necessary for all environments (mostly constants changing directory layout)
 * into tracked file.
 *
 */
class WpConfigSplitter
{

    private static $constantsForExtraction = [
        'WP_CONTENT_DIR',
        'WP_PLUGIN_DIR',
        'UPLOADS',
        'VP_VPDB_DIR',
        'VP_PROJECT_ROOT',
    ];

    public static function split($wpConfigPath, $commonConfigName)
    {
        $wpConfigDir = dirname($wpConfigPath);
        $commonConfigPath = $wpConfigDir . '/' . $commonConfigName;

        self::ensureCommonConfigInclude($wpConfigPath, $commonConfigName);

        $configLines = file($wpConfigPath);
        $commonConfigLines = is_file($commonConfigPath)
            ? file($commonConfigPath)
            : file(__DIR__ . '/wp-config.common.tpl.php');

        // https://regex101.com/r/zD3mJ4/2
        $constantsForRegex = join('|', self::$constantsForExtraction);
        $defineRegexPattern = "/(define\\s*\\(\\s*['\"]($constantsForRegex)['\"]\\s*,.*\\)\\s*;)/m";

        foreach ($configLines as $lineNumber => $line) {
            if (preg_match($defineRegexPattern, $line)) {
                if (Strings::contains($line, $wpConfigDir)) {
                    $positionOfPath = strpos($line, $wpConfigDir) - 1;
                    $line = str_replace($wpConfigDir, '', $line);
                    $line = substr($line, 0, $positionOfPath) . '__DIR__ . ' . substr($line, $positionOfPath);
                }

                $commonConfigLines[] = $line;
                unset($configLines[$lineNumber]);
            }
        }

        file_put_contents($commonConfigPath, join("", $commonConfigLines));
        file_put_contents($wpConfigPath, join("", $configLines));
    }

    /**
     * Adds include of common config if it's missing.
     *
     * @param $wpConfigPath
     * @param $commonConfigName
     */
    public static function ensureCommonConfigInclude($wpConfigPath, $commonConfigName)
    {
        $include = <<<DOC
// Configuration common to all environments
include_once __DIR__ . '/$commonConfigName';
DOC;

        $configContent = file_get_contents($wpConfigPath);

        if (!Strings::contains($configContent, $include)) {
            $configContent = str_replace('<?php', "<?php\n\n$include\n", $configContent);
            file_put_contents($wpConfigPath, $configContent);
        }
    }
}
