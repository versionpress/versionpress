<?php

namespace VersionPress\Database;

use Symfony\Component\Yaml\Yaml;

/**
 * Describes shortcodes and their references to DB entities.
 *
 * The information is loaded from a shortcodes.yml files.
 */
class ShortcodesInfo
{

    private $shortcodeSchema = [];

    public function __construct($shortcodeFiles)
    {
        foreach ($shortcodeFiles as $shortcodeFile) {
            $this->shortcodeSchema = array_merge_recursive($this->shortcodeSchema, Yaml::parse(file_get_contents($shortcodeFile)));
        }
    }

    /**
     * Returns all supported shortcode names - keys from the `shortcodes` array in the YAML file.
     *
     * @return string[]
     */
    public function getAllShortcodeNames()
    {
        return array_keys($this->shortcodeSchema['shortcodes']);
    }

    /**
     * Returns description of a single shortcode as a map between shortcode attributes and entity names, e.g.:
     *
     * ```
     * [
     *   'id' => 'post',
     *   'include' => 'post'
     * ]
     * ```
     *
     * @param string $shortcodeName
     * @return array
     */
    public function getShortcodeInfo($shortcodeName)
    {
        return $this->shortcodeSchema['shortcodes'][$shortcodeName];
    }

    /**
     * Returns a list of entities and their fields where shortcodes are supported.
     * For example, vanilla WordPress only supports shortcodes in posts / their `post_content` column
     * so the array would look like this:
     *
     * ```
     * [
     *   'post' => [
     *     'post_content'
     *   ]
     * ]
     * ```
     *
     * @return array
     */
    public function getShortcodeLocations()
    {
        return $this->shortcodeSchema['shortcode-locations'];
    }
}
