<?php

namespace VersionPress\Database;

use SqlParser\Components\SetOperation;
use SqlParser\Parser;
use SqlParser\Statement;
use SqlParser\Statements\UpdateStatement;
use VersionPress\Database\DbSchemaInfo;

class SqlQueryParser {

    /**
     * @param $query string
     * @param $schema DbSchemaInfo
     * @param \wpdb $wpdb
     * @return \Exception
     */
    public static function parseQuery($query, $schema, $wpdb) {

        $parser = new Parser($query);
        $primaryStatement = $parser->statements[0];
        if ($primaryStatement instanceof UpdateStatement) {
            return self::parseUpdateQuery($parser, $query, $schema, $wpdb);
        }

    }

    private static function parseUpdateQuery($parser, $query, $schema, $wpdb) {
        $primaryStatement = $parser->statements[0];
        $tableExpression = $primaryStatement->tables[0]->expr;
        $table = $primaryStatement->tables[0]->table;
        $idColumn = $schema->getEntityInfoByPrefixedTableName($table)->idColumnName;
        $result = new ParsedQueryData('UPDATE');
        $result->originalQuery = $query;
        $result->table = $table;

        $selectSql = "SELECT $idColumn FROM $tableExpression";
        $where = self::getSelectWhereClause($parser, $query, $primaryStatement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $result->query = $selectSql;
        $result->ids = $wpdb->get_col($selectSql);
        $result->data = self::getData($primaryStatement);
        $result->dirty = self::maybeDataDirty($parser);
        $result->where = self::getWhere($where);
        return $result;
    }


    /**
     * @param $statement UpdateStatement
     * @param $idColumn string Name of the id column
     * @return string
     */
    public static function prepareUpdateQuery($statement, $idColumn) {
        if (!($statement instanceof UpdateStatement)) {
            return new \Exception("Wrong method used for processing statement");
        }

        $tableExpression = $statement->tables[0]->expr;
        $set = self::setToString($statement->set);
        $where = ' WHERE ' . $idColumn . '= %s';
        $sql = "UPDATE " . $tableExpression . $set . $where;
        return $sql;

    }

    /**
     * @param $setOperation SetOperation
     * @return string
     */
    private static function setToString($setOperations) {
        $sqlChunk = ' SET';
        foreach ($setOperations as $setOperation) {
            $sqlChunk .= " " . $setOperation->column . "=" . $setOperation->value . ",";
        }

        return rtrim($sqlChunk, ',');
    }

    /**
     * @param $parser
     * @param $sql
     * @param $primaryStatement
     * @return array
     */
    private static function getSelectWhereClause($parser, $sql, $primaryStatement) {
        if ($primaryStatement->where != null) {
            $where = $primaryStatement->where;
            return $where;
        } elseif ($primaryStatement->where == null && strpos($sql, 'WHERE') !== false) {
            if (isset($parser->statements[1])) {
                $secondarySatement = $parser->statements[1];
                if ($secondarySatement->where != null) {
                    $where = $secondarySatement->where;
                    return $where;
                }

            }
        } elseif ($parser->errors != null && $primaryStatement->where == null && strpos($sql, 'WHERE') === false) {
            $where = ['1=1'];
            return $where;
        }
    }


    private static function getData($statement) {
        if (!($statement instanceof UpdateStatement)) {
            return new \Exception("Wrong method used for processing statement");
        }
        return array_map(function ($set) {
            return array('column' => $set->column, 'value' => $set->value);
        }, $statement->set);
    }

    private static function maybeDataDirty($parser) {
        if (count($parser->statements) > 1) {
            return true;
        } else {
            return self::containsDirtyPatterns($parser->statements[0]->set);
        }
    }

    private static function getWhere($whereConditions) {
        $where = array_filter($whereConditions, function ($w) {
            if (is_object($w)) {
                return !$w->isOperator;
            } else {
                return true;
            }
        });

        $where = array_map(function ($w) {
            if (is_object($w)) {
                return $w->expr;
            } else {
                return $w;
            }
        }, $where);

        return array_values($where);
    }

    /**
     * @param array $sets
     * @return boolean
     */
    private static function containsDirtyPatterns($sets) {
        $dirtyPatterns = array(
            "/DATE_ADD[ ]?\\(.*/" //https://regex101.com/r/tS2iC6/1
        );
        foreach ($sets as $set) {
            foreach ($dirtyPatterns as $pattern) {
                if (preg_match($pattern, $set)) {
                    return true;
                }
            }
        }
        return false;
    }


}
