<?php

namespace VersionPress\Tests\Utils;

use Nette\Utils\Strings;

/**
 * Converts option names between property, env var and CLI argument conventions
 */
class OptionsConventionConverter {

    /**
     * Converts 'optionName' to 'VP_OPTION_NAME', i.e. from property convention to env var convention
     *
     * @param string $propertyName
     * @return string
     */
    public static function getEnvVarOptionName($propertyName) {
        $words = Strings::split($propertyName, '/(?=[A-Z])/');
        array_unshift($words, "VP");
        return strtoupper(join("_", $words));
    }

    /**
     * Converts 'optionName' to 'option-name', i.e. from property convention to CLI convention
     *
     * @param string $propertyName
     * @return string
     */
    public static function getCliOptionName($propertyName) {
        $words = Strings::split($propertyName, '/(?=[A-Z])/');
        return strtolower(join("-", $words));
    }

}
