<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\OptionDirectoryStorage;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use wpdb;

/**
 * Options synchronizer. Skips transient options and a couple of hardcoded values like
 * `cron` or `siteurl`, see the `synchronize()` method.
 */
class OptionsSynchronizer implements Synchronizer {

    /** @var Storage */
    private $optionsStorage;

    /** @var wpdb */
    private $database;
    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    private $tableName;

    function __construct(Storage $optionsStorage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        $this->optionsStorage = $optionsStorage;
        $this->database = $wpdb;
        $this->urlReplacer = $urlReplacer;
        $this->tableName = $dbSchema->getPrefixedTableName('option');
    }

    function synchronize($task, $entitiesToSynchronize = null) {
        $options = $this->optionsStorage->loadAll();
        if (count($options) == 0) return array();

        $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
        foreach ($options as $optionName => $values) {
            $values = $this->urlReplacer->restore($values);
            if (!isset($values['autoload'])) $values['autoload'] = 'yes'; // default value
            $syncQuery .= "(\"$optionName\", \"" . $this->database->_real_escape($values['option_value']) . "\", \"$values[autoload]\"),";
        }

        $syncQuery[mb_strlen($syncQuery) - 1] = " "; // strip last comma
        $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

        $this->database->query($syncQuery);

        $ignoredOptionNames = array_map(function ($option) {
            return "\"" . $option['option_name'] . "\"";
        }, $options);

        $ignoredOptionNames = array_merge($ignoredOptionNames, OptionDirectoryStorage::$optionsBlacklist);

        $deleteSql = "DELETE FROM {$this->tableName} WHERE option_name NOT IN(" . join(", ", $ignoredOptionNames) . ") OR option_name NOT LIKE '_%'";
        $this->database->query($deleteSql);
        return array();
    }

}
