<?php

class DbSchemaInfo {

    public function getIdColumnName($tableName) {
        static $idColumnNames = array(
            'posts' => 'ID',
            'comments' => 'comment_ID',
            'term_taxonomy' => 'term_taxonomy_id',
            'options' => 'option_name',
            'users' => 'ID'
        );

        return $idColumnNames[$tableName];
    }

    public function isHierarchical($tableName) {
        return $tableName === 'posts' ||
        $tableName === 'comments' ||
        $tableName === 'term_taxonomy';
    }

    public function entityShouldHaveVersionPressId($entityName) {
        return $this->isHierarchical($entityName) || $entityName === 'terms' || $entityName === 'users';
    }

    public function getParentIdColumnName($tableName) {
        static $parentIdColumnNames = array(
            'posts' => 'post_parent',
            'comments' => 'comment_parent',
            'term_taxonomy' => 'parent'
        );

        return $parentIdColumnNames[$tableName];
    }
}