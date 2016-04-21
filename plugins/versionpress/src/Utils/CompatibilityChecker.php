<?php

namespace VersionPress\Utils;

class CompatibilityChecker
{
    public static function testCompatibilityBySlug($slug)
    {
        if (isset(RequirementsChecker::$compatiblePlugins[$slug])) {
            return CompatibilityResult::COMPATIBLE;
        } elseif (isset(RequirementsChecker::$incompatiblePlugins[$slug])) {
            return CompatibilityResult::INCOMPATIBLE;
        } else {
            return CompatibilityResult::UNTESTED;
        }
    }

    public static function testCompatibilityByPluginFile($pluginFile)
    {
        if ($pluginFile === 'versionpress/versionpress.php') {
            return CompatibilityResult::VERSIONPRESS;
        }

        if (in_array($pluginFile, RequirementsChecker::$compatiblePlugins)) {
            return CompatibilityResult::COMPATIBLE;
        } elseif (in_array($pluginFile, RequirementsChecker::$incompatiblePlugins)) {
            return CompatibilityResult::INCOMPATIBLE;
        } else {
            return CompatibilityResult::UNTESTED;
        }
    }
}
