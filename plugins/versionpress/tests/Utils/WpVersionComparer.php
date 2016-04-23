<?php

namespace VersionPress\Tests\Utils;

use Icecave\SemVer\Comparator;
use Icecave\SemVer\Version;
use Nette\Utils\Strings;

class WpVersionComparer
{
    /**
     * Compares two WP versions.
     * Returns:
     *  negative number for $v1 < $v2,
     *  positive number for $v1 > $v2 and
     *  zero for $v1 == $v2.
     *
     * @param string $v1
     * @param string $v2
     * @return int
     */
    public static function compare($v1, $v2)
    {
        $semver1 = Version::parse(self::toSemVer($v1));
        $semver2 = Version::parse(self::toSemVer($v2));
        $versionComparator = new Comparator();

        return $versionComparator->compare($semver1, $semver2);
    }

    private static function toSemVer($v1)
    {
        $shortVersionMatcher = "/^(\\d\\.\\d)(-.*)?$/";
        $matches = Strings::match($v1, $shortVersionMatcher);

        if ($matches) {
            return $matches[1] . ".0" . (isset($matches[2]) ? $matches[2] : "");
        }

        return $v1;
    }
}
