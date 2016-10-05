<?php

namespace VersionPress\Actions;

/**
 * Info about an action. Basically represents a section of the `actions.yml` file.
 */
class ActionsInfo
{
    /**
     * For DB entities it is a name of the entity. Otherwise, it's for example `plugin`, `theme`, `versionpress` etc.
     *
     * @var string
     */
    private $scope;

    /**
     * Actions in given scope. For example `create`, `edit`, `activate` etc.
     * It is a map where key is a name of the action (e.g. `create`) and value is another map with two keys - `priority`
     * and `message`.
     *
     * Example:
     *
     * [
     *  create => [ priority => 10, message => Created %VP-Post-Type% '%VP-Post-Title%' ],
     *  edit => [ priority => 12, message => Edited %VP-Post-Type% '%VP-Post-Title%' ],
     * ]
     *
     * @var array
     */
    private $actions;

    /**
     * Tags in given scope. For example `VP-Post-Title`, `VP-Post-Type` etc.
     * It is a map where key is a name of the tag and value is column from which will be the value automatically saved.
     * The value can by also `/` for values that are added in a filter.
     *
     * Example:
     * [
     *  VP-Post-Title => post_title,
     *  VP-Comment-PostTitle => /
     * ]
     *
     * @var array
     */
    private $tags;

    /**
     * Useful for meta-entities. Name of tag where the VPID of parent entity is saved.
     *
     * @var string|null
     */
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
