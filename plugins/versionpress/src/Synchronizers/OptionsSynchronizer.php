<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\OptionsStorage;
use VersionPress\Storages\Storage;
use wpdb;

/**
 * Options synchronizer. Skips transient options and a couple of hardcoded values like
 * `cron` or `siteurl`, see the `synchronize()` method.
 */
class OptionsSynchronizer implements Synchronizer {

    /** @var OptionsStorage */
    private $optionsStorage;

    /** @var wpdb */
    private $database;

    private $tableName;

    function __construct(Storage $optionsStorage, wpdb $database, DbSchemaInfo $dbSchema) {
        $this->optionsStorage = $optionsStorage;
        $this->database = $database;
        $this->tableName = $dbSchema->getPrefixedTableName('option');
    }

    function synchronize($task) {
        $options = $this->optionsStorage->loadAll();
        $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
        foreach ($options as $optionName => $values) {
            if (!isset($values['autoload'])) $values['autoload'] = 'yes'; // default value
            $syncQuery .= "(\"$optionName\", \"" . $this->database->_real_escape($values['option_value']) . "\", \"$values[autoload]\"),";
        }

        $syncQuery[mb_strlen($syncQuery) - 1] = " "; // strip last comma
        $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

        $this->database->query($syncQuery);

        if (count($options) == 0) return array();

        $ignoredOptionNames = array_map(function ($option) {
            return "\"" . $option['option_name'] . "\"";
        }, $options);

        $ignoredOptionNames[] = '"cron"';
        $ignoredOptionNames[] = '"siteurl"';
        $ignoredOptionNames[] = '"home"';
        $ignoredOptionNames[] = '"db_upgraded"';
        $ignoredOptionNames[] = '"auto_updater.lock"';

        $deleteSql = "DELETE FROM {$this->tableName} WHERE option_name NOT IN(" . join(", ", $ignoredOptionNames) . ") OR option_name NOT LIKE '_%'";
        $this->database->query($deleteSql);
        return array();
    }

}
