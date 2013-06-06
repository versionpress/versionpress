<?php

class DbSchemaInfo {

    private $schema;

    function __construct($schemaFile) {
        $neonSchema = file_get_contents($schemaFile);
        $this->schema = Neon::decode($neonSchema);

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
}