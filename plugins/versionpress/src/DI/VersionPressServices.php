<?php

namespace VersionPress\DI;

final class VersionPressServices
{
    const STORAGE_FACTORY = 'storage-factory';
    const MIRROR = 'mirror';
    const DB_SCHEMA = 'db-schema';
    const WPDB_MIRROR_BRIDGE = 'mirror-bridge';
    const COMMITTER = 'committer';
    const INITIALIZER = 'initializer';
    const SYNCHRONIZATION_PROCESS = 'synchronization-process';
    const SYNCHRONIZER_FACTORY = 'synchronizer-factory';
    const REVERTER = 'reverter';
    const GIT_REPOSITORY = 'git-repository';
    const WPDB = 'wpdb';
    const DATABASE = 'database';
    const VPID_REPOSITORY = 'vpid-repository';
    const URL_REPLACER = 'url-replacer';
    const SHORTCODES_REPLACER = 'shortcode-replacer';
    const SHORTCODES_INFO = 'shortcodes-info';
    const SQL_QUERY_PARSER = 'sql-query-parser';
    const ACTIONSINFO_PROVIDER_ACTIVE_PLUGINS = 'actionsinfo-provider-active-plugins';
    const CHANGEINFO_FACTORY = 'changeinfo-factory';
    const ACTIONSINFO_PROVIDER_ALL_PLUGINS = 'actionsinfo-provider-all-plugins';
    const ACTIONS_DEFINITION_REPOSITORY = 'actions-definition-repository';
    const COMMIT_MESSAGE_PARSER = 'commit-message-parser';
    const TABLE_SCHEMA_STORAGE = 'table-schema-storage';

    private function __construct()
    {
    }
}
