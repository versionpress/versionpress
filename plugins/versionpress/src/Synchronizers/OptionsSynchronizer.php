<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Storages\OptionStorage;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\ArrayUtils;
use wpdb;

/**
 * Options synchronizer. Skips transient options and a couple of hardcoded values like
 * `cron` or `siteurl`, see the `synchronize()` method.
 */
class OptionsSynchronizer implements Synchronizer {

    /** @var OptionStorage */
    private $optionStorage;

    /** @var wpdb */
    private $database;
    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;

    private $tableName;
    private $options;

    function __construct(Storage $optionStorage, $wpdb, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer) {
        $this->optionStorage = $optionStorage;
        $this->database = $wpdb;
        $this->urlReplacer = $urlReplacer;
        $this->tableName = $dbSchema->getPrefixedTableName('option');
    }

    function synchronize($task, $entitiesToSynchronize = null) {
        $this->maybeInit($entitiesToSynchronize);
        $options = $this->options;

        if (count($options) > 0) {
            $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
            foreach ($options as $optionName => $values) {
                $values = $this->urlReplacer->restore($values);
                if (!isset($values['autoload'])) $values['autoload'] = 'yes'; // default value
                $syncQuery .= "(\"$optionName\", \"" . $this->database->_real_escape($values['option_value']) . "\", \"$values[autoload]\"),";
            }

            $syncQuery[mb_strlen($syncQuery) - 1] = " "; // strip last comma
            $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

            $this->database->query($syncQuery);
        }

        $ignoredOptionNames = ArrayUtils::column($options, 'option_name');
        $ignoredOptionNames = array_merge($ignoredOptionNames, OptionStorage::$optionsBlacklist);

        $deleteSql = "DELETE FROM {$this->tableName} WHERE option_name NOT IN (\"" . join('", "', $ignoredOptionNames) . "\") OR option_name NOT LIKE '_%'";
        if ($entitiesToSynchronize) {
            $synchronizedOptions = ArrayUtils::column($entitiesToSynchronize, 'vp_id');
            $deleteSql = "DELETE FROM {$this->tableName} WHERE option_name NOT IN (\"" . join('", "', $ignoredOptionNames) . "\") AND option_name IN (\"" . join('", "', $synchronizedOptions) . "\")";
        }

        $this->database->query($deleteSql);
        return array();
    }

    private function maybeInit($optionsToSynchronize) {
        if ($this->options === null) {
            $this->options = $this->loadOptionsFromStorage($optionsToSynchronize);
        }
    }

    private function loadOptionsFromStorage($optionsToSynchronize) {
        if ($optionsToSynchronize === null) {
            return $this->optionStorage->loadAll();
        }

        $options = array();
        foreach ($optionsToSynchronize as $optionToSynchronize) {
            $optionName = $optionToSynchronize['vp_id'];
            if ($this->optionStorage->exists($optionName)) {
                $option = $this->optionStorage->loadEntity($optionName);
                $options[$option['option_name']] = $option;
            }
        }

        return $options;
    }
}
