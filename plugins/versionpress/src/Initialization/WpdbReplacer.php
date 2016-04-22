<?php

namespace VersionPress\Initialization;

use Nette\Utils\Strings;

class WpdbReplacer
{

    private static $methodPrefix = '__wp_';
    private static $vpFirstLineComment = '// Enhanced by VersionPress';
    private static $bootstrapRequire = "
    if (!defined('WP_PLUGIN_DIR')) {
        \$vp_bootstrap = WP_CONTENT_DIR . '/plugins/versionpress/bootstrap.php';
    } else {
        \$vp_bootstrap = WP_PLUGIN_DIR . '/versionpress/bootstrap.php';
    }
    if (file_exists(\$vp_bootstrap)) {
        require_once(\$vp_bootstrap);
    } else {
        register_shutdown_function(array('wpdb', 'vp_restore_original'));
    }";

    public static function replaceMethods()
    {
        $wpdbClassPath = ABSPATH . WPINC . '/wp-db.php';
        $wpdbSource = file_get_contents($wpdbClassPath);

        if (self::isReplaced()) {
            return;
        }

        copy($wpdbClassPath, $wpdbClassPath . '.original');

        $wpdbSource = substr_replace(
            $wpdbSource,
            sprintf("<?php %s\n%s", self::$vpFirstLineComment, self::$bootstrapRequire),
            0,
            strlen('<?php')
        ); // adds the VP comment and require

        $wpdbSource = self::replaceMethod($wpdbSource, 'insert');
        $wpdbSource = self::replaceMethod($wpdbSource, 'update');
        $wpdbSource = self::replaceMethod($wpdbSource, 'delete');
        $wpdbSource = self::replaceMethod($wpdbSource, 'query');
        $wpdbSource = self::injectVersionPressMethods($wpdbSource);

        file_put_contents($wpdbClassPath, $wpdbSource);
    }

    public static function isReplaced()
    {
        $firstLine = fgets(fopen(ABSPATH . WPINC . '/wp-db.php', 'r'));
        return Strings::contains($firstLine, self::$vpFirstLineComment);
    }

    public static function restoreOriginal()
    {
        $original = ABSPATH . WPINC . '/wp-db.php.original';
        if (file_exists($original)) {
            rename($original, ABSPATH . WPINC . '/wp-db.php');
        }
    }

    private static function replaceMethod($source, $method)
    {
        $newName = self::$methodPrefix . $method;
        return str_replace("function $method(", "function $newName(", $source);
    }

    private static function injectVersionPressMethods($wpdbSource)
    {
        $indexOfLastCurlyBracket = strrpos($wpdbSource, '}');
        $codeToInject = self::getCodeToInject();
        $wpdbSource = self::injectCode($wpdbSource, $indexOfLastCurlyBracket, $codeToInject);
        return $wpdbSource;
    }

    private static function injectCode($originalSource, $position, $code)
    {
        return substr($originalSource, 0, $position) . $code . substr($originalSource, $position);
    }

    private static function getCodeToInject()
    {
        $replacerMethodsClassSource = file_get_contents(__DIR__ . '/ReplacerMethods.src.php');
        $methodsStartPosition = strpos($replacerMethodsClassSource, '{') + 1; // after first curly bracket
        $methodsEndPosition = strrpos($replacerMethodsClassSource, '}'); // before last curly bracket
        $methodsSourceLength = $methodsEndPosition - $methodsStartPosition;
        $methodsSource = substr($replacerMethodsClassSource, $methodsStartPosition, $methodsSourceLength);
        return $methodsSource;
    }
}
