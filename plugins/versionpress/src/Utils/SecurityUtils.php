<?php

namespace VersionPress\Utils;

class SecurityUtils
{

    /**
     * Installs .htaccess and web.config to the given path
     *
     * @param string $path
     */
    public static function protectDirectory($path)
    {
        $templatesLocation = __DIR__ . "/../Initialization";
        FileSystem::copy("$templatesLocation/.htaccess.tpl", "$path/.htaccess");
        FileSystem::copy("$templatesLocation/web.tpl.config", "$path/web.config");
    }
}
