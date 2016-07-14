<?php

namespace VersionPress\Git;

class ActionsInfo
{
    /** @var array */
    private $actionMap;

    public function __construct($actionFiles = [])
    {
        foreach ($actionFiles as $file) {

        }
    }

    public function getTags($entityName)
    {
        return $this->actionMap[$entityName]['tags'];
    }

    public function createCommitMessage($entityName, $action, $vpid, $tags)
    {
        $message = $this->actionMap[$entityName]['actions'][$action]['message'];

        foreach ($tags as $tag => $value) {
            $message = str_replace("%{$tag}%", $value, $message);
        }

        $message = str_replace('%VPID%', $vpid, $message);

        return apply_filters("vp_entity_change_description_{$entityName}", $message, $action, $vpid, $tags);
    }

    public function getActionPriority($entityName, $action)
    {
        return $this->actionMap[$entityName]['actions'][$action]['priority'];
    }
}
