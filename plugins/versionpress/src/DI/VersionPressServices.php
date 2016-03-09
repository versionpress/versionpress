<?php

namespace VersionPress\DI;

final class VersionPressServices {
    const STORAGE_FACTORY = 'storage-factory';
    const MIRROR = 'mirror';
    const DB_SCHEMA = 'db-schema';
    const WPDB_MIRROR_BRIDGE = 'database';
    const COMMITTER = 'committer';
    const INITIALIZER = 'initializer';
    const SYNCHRONIZATION_PROCESS = 'synchronization-process';
    const SYNCHRONIZER_FACTORY = 'synchronizer-factory';
    const REVERTER = 'reverter';
    const REPOSITORY = 'repository';
    const WPDB = 'wpdb';
    const VPID_REPOSITORY = 'vpid-repository';
    const URL_REPLACER = 'url-replacer';
    const SHORTCODES_REPLACER = 'shortcode-replacer';
    const SHORTCODES_INFO = 'shortcodes-info';
    const SQL_QUERY_PARSER = 'sql-query-parser';

    private function __construct() {
    }
}
