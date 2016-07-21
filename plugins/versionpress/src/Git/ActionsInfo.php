<?php

namespace VersionPress\Git;

use Symfony\Component\Yaml\Yaml;

class ActionsInfo
{
    const DEFAULT_PRIORITY = 10;

    /** @var array */
    private $actionMap;

    public function __construct($actionFiles = [])
    {
        foreach ($actionFiles as $file) {
            $content = file_get_contents($file);
            $this->actionMap = Yaml::parse($content);
        }

        foreach ($this->actionMap as $entityName => &$tagsAndActions) {
            foreach ($tagsAndActions['actions'] as &$action) {
                if (is_string($action)) {
                    $action = ['message' => $action, 'priority' => self::DEFAULT_PRIORITY];
                }

                if (!isset($action['priority'])) {
                    $action['priority'] = self::DEFAULT_PRIORITY;
                }
            }
        }
    }

    public function getTags($entityName)
    {
        return @$this->actionMap[$entityName]['tags'] ?: [];
    }

    public function createCommitMessage($entityName, $action, $vpid, $tags)
    {
        $message = @$this->actionMap[$entityName]['actions'][$action]['message'] ?: '';

        foreach ($tags as $tag => $value) {
            $message = str_replace("%{$tag}%", $value, $message);
        }

        $message = str_replace('%VPID%', $vpid, $message);

        return apply_filters("vp_entity_change_description_{$entityName}", $message, $action, $vpid, $tags);
    }

    public function getActionPriority($entityName, $action)
    {
        return @$this->actionMap[$entityName]['actions'][$action]['priority'] ?: self::DEFAULT_PRIORITY;
    }

    public function getTagContainingParentId($entityName)
    {
        return @$this->actionMap[$entityName]['parent-id-tag'] ?: null;
    }
}
