<?php

class OptionsSynchronizer implements Synchronizer {

    /**
     * @var OptionsStorage
     */
    private $optionsStorage;

    /**
     * @var wpdb
     */
    private $database;

    private $tableName;

    function __construct(Storage $optionsStorage, wpdb $database, DbSchemaInfo $dbSchema) {
        $this->optionsStorage = $optionsStorage;
        $this->database = $database;
        $this->tableName = $dbSchema->getPrefixedTableName('options');
    }

    function synchronize() {
        $options = $this->optionsStorage->loadAll();
        $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
        foreach ($options as $optionName => $values) {
            if(!isset($values['autoload'])) $values['autoload'] = 'yes'; // default value
            $syncQuery .= "(\"$optionName\", \"" . mysql_real_escape_string($values['option_value']) . "\", \"$values[autoload]\"),";
        }

        $syncQuery[mb_strlen($syncQuery) - 1] = " "; // strip last comma
        $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

        $this->database->query($syncQuery);

        if(count($options) == 0) return;

        $optionNames = array_map(function($option) {
            return "\"" . $option['option_name'] . "\"";
        }, $options);

        $optionNames[] = '"cron"';
        $optionNames[] = '"siteurl"';
        $optionNames[] = '"home"';
        $optionNames[] = '"db_upgraded"';
        $optionNames[] = '"auto_updater.lock"';

        $deleteSql = "DELETE FROM {$this->tableName} WHERE option_name NOT IN(" . join(", ", $optionNames) . ") OR option_name NOT LIKE '_%'";
        $this->database->query($deleteSql);
    }

}