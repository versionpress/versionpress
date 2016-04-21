<?php

namespace VersionPress\Database;

use VersionPress\Utils\WordPressMissingFunctions;

/**
 * Class for replacing IDs in shortcodes with VPIDs (and vice versa).
 *
 * !!! This class uses many functions of wp-includes/shortcodes.php, there is no easy
 * way around it.
 *
 */
class ShortcodesReplacer
{
    /** @var ShortcodesInfo */
    private $shortcodesInfo;
    /** @var VpidRepository */
    private $vpidRepository;

    /**
     * @param ShortcodesInfo $shortcodeInfo
     * @param VpidRepository $vpidRepository
     */
    public function __construct(ShortcodesInfo $shortcodeInfo, VpidRepository $vpidRepository)
    {
        $this->shortcodesInfo = $shortcodeInfo;
        $this->vpidRepository = $vpidRepository;
    }

    /**
     * Translates IDs to VPIDs in shortcodes.
     *
     * @param string $string
     * @return string
     */
    private function replaceShortcodes($string)
    {
        $pattern = get_shortcode_regex($this->shortcodesInfo->getAllShortcodeNames());
        return preg_replace_callback(
            "/$pattern/",
            $this->createReplaceCallback([$this, 'getVpidByEntityNameAndId']),
            $string
        );
    }

    /**
     * Translates VPIDs to IDs in shortcodes.
     *
     * @param string $string
     * @return string
     */
    private function restoreShortcodes($string)
    {
        $pattern = get_shortcode_regex($this->shortcodesInfo->getAllShortcodeNames());
        return preg_replace_callback("/$pattern/", $this->createReplaceCallback([$this, 'getIdByVpid']), $string);
    }

    /**
     * Translates IDs to VPIDs in shortcodes for an entity
     *
     * @param string $entityName
     * @param array $entity Entity data, see {@link WpdbMirrorBridge}
     * @return array Entity data
     */
    public function replaceShortcodesInEntity($entityName, $entity)
    {
        if (!$this->entityCanContainShortcodes($entityName)) {
            return $entity;
        }

        foreach ($entity as $field => $value) {
            if ($this->fieldCanContainShortcodes($entityName, $field)) {
                $entity[$field] = $this->replaceShortcodes($value);
            }
        }

        return $entity;
    }

    /**
     * Translates VPIDs to IDs in shortcodes for an entity
     *
     * @param string $entityName
     * @param array $entity Entity data, see {@link WpdbMirrorBridge}
     * @return array Entity data
     */
    public function restoreShortcodesInEntity($entityName, $entity)
    {
        if (!$this->entityCanContainShortcodes($entityName)) {
            return $entity;
        }

        foreach ($entity as $field => $value) {
            if ($this->fieldCanContainShortcodes($entityName, $field)) {
                $entity[$field] = $this->restoreShortcodes($value);
            }
        }

        return $entity;
    }

    /**
     * Return true if entity can contain shortcodes
     *
     * @param string $entityName
     * @return bool
     */
    public function entityCanContainShortcodes($entityName)
    {
        $shortcodeLocations = $this->shortcodesInfo->getShortcodeLocations();
        return isset($shortcodeLocations[$entityName]);
    }

    /**
     * Return true if a field on an entity can contain shortcodes
     *
     * @param string $entityName E.g., 'post'
     * @param string $field E.g., 'post_content'
     * @return bool
     */
    public function fieldCanContainShortcodes($entityName, $field)
    {
        if (!$this->entityCanContainShortcodes($entityName)) {
            return false;
        }

        $shortcodeLocations = $this->shortcodesInfo->getShortcodeLocations();
        $allowedFields = $shortcodeLocations[$entityName];

        return array_search($field, $allowedFields) !== false;
    }

    private function createReplaceCallback($idProvider)
    {
        $shortcodesInfo = $this->shortcodesInfo;

        return function ($m) use ($shortcodesInfo, $idProvider) {
            // allow [[foo]] syntax for escaping a tag - code adopted from WP function `do_shortcode_tag`
            if ($m[1] == '[' && $m[6] == ']') {
                return substr($m[0], 1, -1);
            }

            $shortcodeTag = $m[2];
            $shortcodeInfo = $shortcodesInfo->getShortcodeInfo($shortcodeTag);
            $attributes = shortcode_parse_atts($m[3]);

            foreach ($attributes as $attribute => $value) {
                if (isset($shortcodeInfo[$attribute])) {
                    $ids = explode(',', $value);
                    $entityName = $shortcodeInfo[$attribute];
                    $attributes[$attribute] = join(',', array_map(function ($id) use ($entityName, $idProvider) {
                        return $idProvider($entityName, $id);
                    }, $ids));
                }
            }

            return WordPressMissingFunctions::renderShortcode($shortcodeTag, $attributes);
        };
    }

    private function getVpidByEntityNameAndId($entityName, $id)
    {
        return $this->vpidRepository->getVpidForEntity($entityName, $id) ?: $id;
    }

    private function getIdByVpid($entityName, $vpid)
    {
        return $this->vpidRepository->getIdForVpid($vpid) ?: $vpid;
    }
}
