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
class WpConfigSplitter {

    private static $constantsForExtraction = array(
        'WP_CONTENT_DIR',
        'WP_CONTENT_URL',
        'WP_PLUGIN_DIR',
        'WP_PLUGIN_URL',
        'UPLOADS',
    );

    public static function split($wpConfigPath, $commonConfigName) {

        $commonConfigPath = dirname($wpConfigPath) . '/' . $commonConfigName;

        $include = <<<DOC
// Configuration common to all environments
include_once __DIR__ . '/$commonConfigName';
DOC;

        $configContent = file_get_contents($wpConfigPath);

        if (!Strings::contains($configContent, $include)) {
            $configContent = str_replace('<?php', "<?php\n\n$include\n", $configContent);
            file_put_contents($wpConfigPath, $configContent);
        }

        $configLines = file($wpConfigPath);
        $commonConfigLines = is_file($commonConfigPath) ? file($commonConfigPath) : array("<?php\n");

        // https://regex101.com/r/zD3mJ4/2
        $constantsForRegex = join('|', self::$constantsForExtraction);
        $defineRegexPattern = "/(define\\s*\\(\\s*['\"]($constantsForRegex)['\"]\\s*,.*\\)\\s*;)/m";

        foreach ($configLines as $lineNumber => $line) {
            if (preg_match($defineRegexPattern, $line)) {
                $commonConfigLines[] = $line;
                unset($configLines[$lineNumber]);
            }
        }

        file_put_contents($commonConfigPath, join("", $commonConfigLines));
        file_put_contents($wpConfigPath, join("", $configLines));
    }
}
