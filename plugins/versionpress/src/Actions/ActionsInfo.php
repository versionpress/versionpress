<?php

namespace VersionPress\Actions;

class ActionsInfo
{
    private $scope;
    private $actions;
    private $tags;
    private $parentIdTag;

    public function __construct($scope, $actions, $tags = [], $parentIdTag = null)
    {
        $this->scope = $scope;
        $this->actions = $actions;
        $this->tags = $tags;
        $this->parentIdTag = $parentIdTag;
    }


    public function getTags()
    {
        return $this->tags;
    }

    public function getDescription($action, $vpid, $tags)
    {
        $message = $this->actions[$action]['message'];

        foreach ($tags as $tag => $value) {
            $message = str_replace("%{$tag}%", $value, $message);
        }

        $message = str_replace('%VPID%', $vpid, $message);

        return apply_filters("vp_action_description_{$this->scope}", $message, $action, $vpid, $tags);
    }

    public function getActionPriority($action)
    {
        return $this->actions[$action]['priority'];
    }

    public function getTagContainingParentId()
    {
        return $this->parentIdTag;
    }
}
