<?php

namespace VersionPress\Utils;

class WordPressMissingFunctions
{

    public static function getWpConfigPath()
    {
        $defaultWpConfigPath = realpath(ABSPATH . 'wp-config.php');
        $elevatedWpConfigPath = realpath(dirname(ABSPATH) . '/wp-config.php');

        if (is_file($defaultWpConfigPath)) {
            return $defaultWpConfigPath;
        }

        return $elevatedWpConfigPath;
    }

    public static function renderShortcode($shortcodeTag, $attributes, $shortcodeSelfClosing, $shortcodeContent)
    {
        $renderedAttributes = [];

        foreach ($attributes as $attribute => $value) {
            $renderedAttributes[] = sprintf('%s="%s"', $attribute, $value);
        }

        if (empty($shortcodeSelfClosing)) {
            if (empty($shortcodeContent)) {
                return sprintf('[%s %s][/%s]', $shortcodeTag, join(' ', $renderedAttributes), $shortcodeTag);
            }
            return sprintf('[%s %s]%s[/%s]', $shortcodeTag, join(' ', $renderedAttributes), $shortcodeContent, $shortcodeTag);
        }
        return sprintf('[%s %s /]', $shortcodeTag, join(' ', $renderedAttributes));
    }

    public static function pipeAction($source, $destination, $priority = 10, $acceptedArgs = 1)
    {
        return add_action($source, function (...$args) use ($destination) {
            array_unshift($args, $destination);

            return call_user_func_array('do_action', $args);
        }, $priority, $acceptedArgs);
    }
}
