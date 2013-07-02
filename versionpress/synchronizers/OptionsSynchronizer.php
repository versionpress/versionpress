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

    function __construct(EntityStorage $optionsStorage, wpdb $database, DbSchemaInfo $dbSchema) {
        $this->optionsStorage = $optionsStorage;
        $this->database = $database;
        $this->tableName = $dbSchema->getPrefixedTableName('options');
    }

    function synchronize() {
        $options = $this->optionsStorage->loadAll();
        $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
        foreach ($options as $optionName => $values) {
            $syncQuery .= "(\"$optionName\", \"" . mysql_real_escape_string($values['option_value']) . "\", \"$values[autoload]\"),";
        }

        $syncQuery[mb_strlen($syncQuery) - 1] = " "; // strip last comma
        $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

        $this->database->query($syncQuery);
    }

}