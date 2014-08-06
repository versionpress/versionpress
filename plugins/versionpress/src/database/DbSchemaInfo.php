<?php
/**
 * Represents basic information about WordPress(+plugins) DB schema
 * Information is loaded from given .neon file
 */
class DbSchemaInfo {

    private $schema;
    private $prefix;

    function __construct($schemaFile, $prefix) {
        $neonSchema = file_get_contents($schemaFile);
        $this->schema = NNeon::decode($neonSchema);
        $this->prefix = $prefix;
    }

    public function hasId($entityName) {
        return isset($this->schema[$entityName]['id']);
    }

    public function hasReferences($entityName) {
        return isset($this->schema[$entityName]['references'])
        && count($this->schema[$entityName]['references']) > 0;
    }

    public function getReferences($entityName) {
        return $this->schema[$entityName]['references'];
    }

    public function getIdColumnName($entityName) {
        return $this->schema[$entityName]['id'];
    }

    public function getReference($entityName, $referenceName) {
        return $this->schema[$entityName]['references'][$referenceName];
    }

    public function getEntityNames() {
        return array_keys($this->schema);
    }

    public function getPrefixedTableName($entityName) {
        return $this->prefix . $entityName;
    }
}