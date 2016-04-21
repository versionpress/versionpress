<?php

namespace VersionPress\Git;

class GitConfig
{
    public static $wpcliUserName = "WP-CLI";
    public static $wpcliUserEmail = "wpcli@localhost";

    public static function removeEmptySections($gitConfigPath)
    {
        $gitConfigContent = file_get_contents($gitConfigPath);

        // https://regex101.com/r/nD0zI5/2
        $re = "/\\[[^\\[]+\\]\\r?\\n(?=\\[)/";
        $gitConfigContent = preg_replace($re, '', $gitConfigContent);

        file_put_contents($gitConfigPath, $gitConfigContent);
    }
}
