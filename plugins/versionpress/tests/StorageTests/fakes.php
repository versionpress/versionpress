<?php

/**
 * Used by AbsoluteUrlFilter.
 *
 * @hack
 * @private
 * @return string
 */
function get_site_url() {
    $config = \VersionPress\Tests\Utils\TestConfig::createDefaultConfig();
    return $config->testSite->url;
}
