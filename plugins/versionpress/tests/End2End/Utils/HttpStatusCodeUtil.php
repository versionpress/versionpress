<?php

namespace VersionPress\Tests\End2End\Utils;

class HttpStatusCodeUtil
{

    /**
     * Returns HTTP status code of a request to the given URL
     *
     * @param string $url
     * @return int
     */
    public static function getStatusCode($url)
    {
        $headers = get_headers($url, 1);
        return intval(substr($headers[0], 9, 3));
    }
}
