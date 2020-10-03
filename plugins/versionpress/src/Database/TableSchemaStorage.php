<?php

namespace VersionPress\Database;

use VersionPress\Storages\Storage;
use VersionPress\Utils\FileSystem;

/**
 * This class acts as a storage for DDL scripts of site's tables. It saves the DDL scripts as individual files (<table_name>.sql)
 * to a directory specified in the constructor. Also, it automatically removes and restores the prefix.
 */
class TableSchemaStorage
{
    /** @var Database */
    private $database;

    private $directory;

    public function __construct(Database $database, $directory)
    {
        $this->database = $database;
        $this->directory = $directory;
    }

    public function saveSchema($table)
    {
        $schemaFile = $this->getSchemaFile($table);

        if ($this->database->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return;
        }

        $tableDDL = $this->database->get_var("show create table `{$table}`", 1);

        if (!$tableDDL) {
            return;
        }

        $tableDDL = str_replace("CREATE TABLE `{$this->database->prefix}", 'CREATE TABLE IF NOT EXISTS `' . Storage::PREFIX_PLACEHOLDER, $tableDDL);
        $tableDDL = preg_replace('/( AUTO_INCREMENT=[0-9]+)/', '', $tableDDL);

        FileSystem::mkdir($this->directory);
        file_put_contents($schemaFile, $tableDDL);
    }

    public function getSchema($table)
    {
        $schemaFile = $this->getSchemaFile($table);

        if (file_exists($schemaFile)) {
            $tableDDL = file_get_contents($schemaFile);
            return str_replace(Storage::PREFIX_PLACEHOLDER, $this->database->prefix, $tableDDL);
        }
    }

    public function containsSchema($table)
    {
        return file_exists($this->getSchemaFile($table));
    }

    private function getSchemaFile($table)
    {
        $tableWithoutPrefix = strpos($table, $this->database->prefix) === 0 ? substr($table, strlen($this->database->prefix)) : $table;
        $schemaFile = sprintf('%s/%s.sql', $this->directory, $tableWithoutPrefix);
        return $schemaFile;
    }

    public function deleteAll()
    {
        FileSystem::removeContent($this->directory);
    }
}
