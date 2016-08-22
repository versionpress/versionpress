<?php

namespace VersionPress\Actions;

use Symfony\Component\Yaml\Yaml;

class ActionsInfoProvider
{
    const DEFAULT_PRIORITY = 10;

    /** @var ActionsInfo[] */
    private $actionsInfoMap = [];

    /**
     * @param array|\Traversable $actionFiles
     */
    public function __construct($actionFiles = [])
    {
        $actionMap = [];
        foreach ($actionFiles as $file) {
            $content = file_get_contents($file);
            $actionMap = array_merge_recursive($actionMap, Yaml::parse($content));
        }

        foreach ($actionMap as $scope => &$scopeDefinition) {
            foreach ($scopeDefinition['actions'] as &$action) {
                if (is_string($action)) {
                    $action = ['message' => $action, 'priority' => self::DEFAULT_PRIORITY];
                }

                if (!isset($action['priority'])) {
                    $action['priority'] = self::DEFAULT_PRIORITY;
                }
            }

            $actions = $scopeDefinition['actions'];
            $tags = @$scopeDefinition['tags'] ?: [];
            $parentIdTag = @$scopeDefinition['parent-id-tag'];

            $this->actionsInfoMap[$scope] = new ActionsInfo($scope, $actions, $tags, $parentIdTag);
        }
    }

    public function getActionsInfo($scope)
    {
        return $this->actionsInfoMap[$scope];
    }
}
