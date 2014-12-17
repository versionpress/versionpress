<?php

namespace VersionPress\Utils;
/**
 * VersionPress\Utils\Markdown helper class
 */
class Markdown {

    /**
     * Transforms VersionPress\Utils\Markdown to HTML using {@link https://michelf.ca/projects/php-markdown/ PHP VersionPress\Utils\Markdown}.
     * One special feature is single line detection - if the passed $text is single line, this function
     * removes the wrapping `<p>` element which PHPMarkdown automatically adds.
     *
     * @param $text
     * @return string HTML
     */
    public static function transform($text) {
        $html = \Michelf\Markdown::defaultTransform($text);
        if (strstr($text, "\n")) {
            return $html;
        } else {
            // single line, unwrap the <p> tag
            return substr($html, 3, -3);
        }
    }
}