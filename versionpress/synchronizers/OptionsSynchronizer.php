<?php

class OptionsSynchronizer {

    /**
     * @var OptionsStorage
     */
    private $optionsStorage;

    /**
     * @var wpdb
     */
    private $database;

    private $tableName;

    function __construct(EntityStorage $optionsStorage, wpdb $database, $tableName) {
        $this->optionsStorage = $optionsStorage;
        $this->database = $database;
        $this->tableName = $tableName;
    }

    function synchronize() {
        $options = $this->optionsStorage->loadAll();

        foreach($options as $optionName => $values) {
            $sql = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES (\"$optionName\", \"$values[option_value]\", \"$values[autoload]\")
                    ON DUPLICATE KEY UPDATE option_value = \"$values[option_value]\", autoload = \"$values[autoload]\";";
            $result = $this->database->query($sql);
        }
    }

}