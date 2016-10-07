<?php

namespace VersionPress\Utils;

use Nette\Utils\Strings;
use VersionPress\Actions\ActionsInfoProvider;

class AutocompleteUtils
{

    /**
     * @param $actionsInfoProvider ActionsInfoProvider
     *
     * @return array
     */
    public static function createAutocompleteConfig($actionsInfoProvider)
    {
        $config = [];

        $config['action:'] = self::getActionsConfig($actionsInfoProvider);
        $config['scope:'] = self::getScopesConfig($actionsInfoProvider);
        $config['author:'] = self::getAuthorsConfig();
        $config['before:'] = self::getDateConfig();
        $config['after:'] = self::getDateConfig();
        $config['date:'] = self::getDateConfig();

        return $config;
    }

    /**
     * @param $actionsInfoProvider ActionsInfoProvider
     *
     * @return array
     */
    private static function getActionsConfig($actionsInfoProvider)
    {
        $actions = [];
        foreach ($actionsInfoProvider->getAllActionsInfo() as $scope => $actionsInfo) {
            foreach ($actionsInfo->getActions() as $action => $actionInfo) {
                $actions[] = [
                    'label' => Strings::capitalize($action) . ' ' . $scope,
                    'value' => $scope . '/' . $action,
                ];
            }
        }
        return [
            'type' => 'list',
            'defaultHint' => 'e.g. post/edit',
            'sectionTitle' => 'Actions',
            'content' => $actions
        ];
    }

    /**
     * @param $actionsInfoProvider ActionsInfoProvider
     *
     * @return array
     */
    private static function getScopesConfig($actionsInfoProvider)
    {
        $scopes = [];
        foreach ($actionsInfoProvider->getAllActionsInfo() as $scope => $actionsInfo) {
            $scopes[] = [
                'label' => Strings::capitalize($scope),
                'value' => $scope,
            ];
        }
        return [
            'type' => 'list',
            'defaultHint' => 'e.g. post',
            'sectionTitle' => 'Scopes',
            'content' => $scopes
        ];
    }

    /**
     * @return array
     */
    private static function getAuthorsConfig()
    {
        $users = [];
        foreach (get_users() as $user) {
            $users[] = [
                'label' => $user->display_name . ' <' . $user->user_email . '>',
                'value' => $user->user_login
            ];
        }
        return [
            'type' => 'list',
            'defaultHint' => 'username or email',
            'sectionTitle' => 'Authors',
            'content' => $users
        ];
    }

    /**
     * @return array
     */
    private static function getDateConfig()
    {
        return [
            'type' => 'date'
        ];
    }
}
