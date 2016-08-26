<?php

namespace VersionPress\Tests\Utils;

use VersionPress\Utils\ArrayUtils;

/**
 * Class used for mocking hooks in tests.
 *
 * It does two types of "mocking":
 *   a) The first type simulates behavior of WordPress (TRUE_HOOKS). With this type you can call `add_filter` and `apply_filters`
 *      and the registered filter will be actually executed.
 *   b) The second type uses WP_MOCK. You can use all features of WP_MOCK if you choose it. The problem is,
 *      WP_MOCK does not execute the registered hooks; therefore, we introduced this thin wrapper.
 *
 * Usage:
 *
 * Just call `HookMock::setUp` with desired type of mocking in the `setUp` method of your test case
 * and `HookMock::tearDown` in the `tearDown` method.
 *
 * Note:
 * For now, this class supports only filters.
 *
 */
class HookMock
{

    const TRUE_HOOKS = 'true-hooks';
    const WP_MOCK = 'wp_mock';

    private static $type = null;

    private static $hooks = [];

    public static function setUp($type = HookMock::TRUE_HOOKS)
    {
        require_once __DIR__ . '/hookmock-functions.php';

        self::$type = $type;
        self::$hooks = [];

        if (self::$type === HookMock::WP_MOCK) {
            \WP_Mock::setUp();
        }
    }

    public static function tearDown()
    {
        if (self::$type === HookMock::WP_MOCK) {
            \WP_Mock::tearDown();
        }

        self::$type = null;
    }

    public static function applyFilters($tag, $value)
    {
        $args = func_get_args();
        $args = array_slice($args, 1);
        $args[0] = $value;

        if (self::$type === HookMock::WP_MOCK) {
            return \WP_Mock::onFilter($tag)->apply($args);
        }

        if (self::$type === HookMock::TRUE_HOOKS) {
            $relatedFilters = self::getRelatedHooks($tag, 'filters');

            foreach ($relatedFilters as $filter) {
                $fn = $filter['fn'];
                $acceptedArgs = $filter['args'];
                $args[0] = $value;

                $value = call_user_func_array($fn, array_slice($args, 0, $acceptedArgs));
            }
        }

        return $value;
    }

    public static function addFilter($tag, $fn, $priority = 10, $acceptedArgs = 1)
    {
        if (self::$type === HookMock::WP_MOCK) {
            \WP_Mock::onFilterAdded($tag)->react($fn, (int)$priority, (int)$acceptedArgs);
        }

        if (self::$type === HookMock::TRUE_HOOKS) {
            self::$hooks['filters'][$tag][] = ['fn' => $fn, 'priority' => $priority, 'args' => $acceptedArgs];
        }
    }

    public static function doAction($tag, $arg = '')
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (self::$type === HookMock::WP_MOCK) {
            \WP_Mock::onAction($tag)->react($args);
        }

        if (self::$type === HookMock::TRUE_HOOKS) {
            $relatedHooks = self::getRelatedHooks($tag, 'actions');

            foreach ($relatedHooks as $hook) {
                $fn = $hook['fn'];
                $acceptedArgs = $hook['args'];

                call_user_func_array($fn, array_slice($args, 0, $acceptedArgs));
            }
        }
    }

    public static function addAction($tag, $fn, $priority = 10, $acceptedArgs = 1)
    {
        if (self::$type === HookMock::WP_MOCK) {
            \WP_Mock::onActionAdded($tag)->react($fn, (int)$priority, (int)$acceptedArgs);
        }

        if (self::$type === HookMock::TRUE_HOOKS) {
            self::$hooks['actions'][$tag][] = ['fn' => $fn, 'priority' => $priority, 'args' => $acceptedArgs];
        }
    }

    private static function getRelatedHooks($tag, $hookType)
    {
        $relatedHooks = isset(self::$hooks[$hookType][$tag]) ? self::$hooks[$hookType][$tag] : [];

        ArrayUtils::stablesort($relatedHooks, function ($hook1, $hook2) {
            return $hook1['priority'] - $hook2['priority'];
        });

        return $relatedHooks;
    }
}
