<?php

if (!function_exists('apply_filters')) {
    function apply_filters()
    {
        return call_user_func_array('\VersionPress\Tests\Utils\HookMock::applyFilters', func_get_args());
    }
}

if (!function_exists('add_filter')) {
    function add_filter()
    {
        return call_user_func_array('\VersionPress\Tests\Utils\HookMock::addFilter', func_get_args());
    }
}
