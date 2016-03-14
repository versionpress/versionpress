<?php

namespace VersionPress\Database;

use SqlParser\Components\SetOperation;
use SqlParser\Parser;
use SqlParser\Statement;
use SqlParser\Statements\DeleteStatement;
use SqlParser\Statements\InsertStatement;
use SqlParser\Statements\UpdateStatement;
use VersionPress\Database\DbSchemaInfo;

class SqlQueryParser {

    private $schema;

    private $wpdb;

    /**
     * SqlQueryParser constructor.
     * @param DbSchemaInfo $schema
     * @param \wpdb $wpdb
     */
    public function __construct($schema, $wpdb) {
        $this->schema = $schema;
        $this->wpdb = $wpdb;
    }

    /**
     * Parses Sql query called. If not parseable, returns null
     * @param string $query
     * @return ParsedQueryData
     */
    public function parseQuery($query) {
        $parser = self::getParser($query);
        $sqlStatement = $parser->statements[0];
        if ($sqlStatement instanceof UpdateStatement) {
            return self::parseUpdateQuery($parser, $query, $this->schema, $this->wpdb);
        } elseif ($sqlStatement instanceof InsertStatement) {
            return self::parseInsertQuery($parser, $query, $this->schema);
        } elseif ($sqlStatement instanceof DeleteStatement) {
            return self::parseDeleteQuery($parser, $query, $this->schema, $this->wpdb);
        }
        return null;

    }

    /**
     * Parses UPDATE query
     *
     * @param SqlParser\Parser $parser
     * @param DbSchemaInfo $schema
     * @param string $query
     * @param \wpdb $wpdb
     * @return ParsedQueryData
     */
    private static function parseUpdateQuery($parser, $query, $schema, $wpdb) {
        $sqlStatement = $parser->statements[0];
        $table = $sqlStatement->tables[0]->table;
        $idColumn = self::resolveIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $parsedQueryData = new ParsedQueryData(ParsedQueryData::UPDATE_QUERY);
        $parsedQueryData->table = $table;
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = self::resolveEntityName($schema, $table);
        $selectSql = self::getSelectQuery($parser, $idColumn);
        $where = self::getWhereFragments($parser, $query, $sqlStatement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $parsedQueryData->sqlQuery = $selectSql;
        $parsedQueryData->ids = $wpdb->get_col($selectSql);
        $parsedQueryData->data = self::getColumnDataToSet($sqlStatement);
        return $parsedQueryData;
    }

    /**
     * Parses INSERT query
     *
     * @param SqlParser\Parser $parser
     * @param string $query
     * @return ParsedQueryData
     */
    private static function parseInsertQuery($parser, $query, $schema) {
        $sqlStatement = $parser->statements[0];
        $queryType = ParsedQueryData::INSERT_QUERY;
        $table = $sqlStatement->into->dest->table;
        $idColumn = self::resolveIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        if (count($sqlStatement->options->options) > 0) {
            foreach (array_keys($sqlStatement->options->options) as $key) {
                $queryType .= "_" . $sqlStatement->options->options[$key];
            }
            if ($queryType == ParsedQueryData::INSERT_IGNORE_QUERY) {
                return null;
            }
        }
        if (strpos($query, 'ON DUPLICATE KEY UPDATE') !== false) {
            $queryType .= "_UPDATE";
        }
        $parsedQueryData = new ParsedQueryData($queryType);
        $parsedQueryData->data = self::getColumnDataToSet($sqlStatement);
        $parsedQueryData->table = $table;
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = self::resolveEntityName($schema, $table);
        $selectSql = self::getSelectQuery($parser, $idColumn);
        $parsedQueryData->sqlQuery = $selectSql;
        return $parsedQueryData;
    }

    /**
     * Parses DELETE query
     *
     * @param SqlParser\Parser $parser
     * @param string $query
     * @param DbSchemaInfo $schema
     * @param $wpdb \wpdb
     * @return ParsedQueryData
     */
    private static function parseDeleteQuery($parser, $query, $schema, $wpdb) {
        $sqlStatement = $parser->statements[0];
        $table = $sqlStatement->from[0]->table;
        $idColumn = self::resolveIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $parsedQueryData = new ParsedQueryData(ParsedQueryData::DELETE_QUERY);
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = self::resolveEntityName($schema, $table);
        $parsedQueryData->table = $table;
        $selectSql = self::getSelectQuery($parser, $idColumn);
        $where = self::getWhereFragments($parser, $query, $sqlStatement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $parsedQueryData->sqlQuery = $selectSql;
        $parsedQueryData->ids = $wpdb->get_col($selectSql);

        return $parsedQueryData;
    }

    /**
     * Returns representation of WHERE SQL clauses found in whole query
     *
     * @param SqlParser\Parser $parser
     * @param string $sqlQuery
     * @param SqlParser\Statements\Statement $primarySqlStatement
     * @return array
     */
    private static function getWhereFragments($parser, $sqlQuery, $primarySqlStatement) {
        if ($primarySqlStatement->where != null) {
            $where = $primarySqlStatement->where;
            return $where;
        } elseif ($primarySqlStatement->where == null && strpos($sqlQuery, 'WHERE') !== false) {
            if (isset($parser->statements[1])) {
                $secondarySqlSatement = $parser->statements[1];
                if ($secondarySqlSatement->where != null) {
                    $where = $secondarySqlSatement->where;
                    return $where;
                }

            }
        } elseif ($parser->errors != null && $primarySqlStatement->where == null && strpos($sqlQuery, 'WHERE') === false) {
            $where = ['1=1'];
            return $where;
        }
    }

    /**
     * Gets data which needs to be set by UPDATE statement
     *
     * Example
     *
     * [data] => Array
     *     (
     *         [0] => Array
     *               (
     *                   [column] => post_modified
     *                   [value] => NOW()
     *               )
     *     )
     * @param SqlParser\Statements\Statement $sqlStatement
     * @return array
     */
    private static function getColumnDataToSet($sqlStatement) {

        if ($sqlStatement instanceof UpdateStatement) {
            $dataSet = [];
            foreach ($sqlStatement->set as $set) {
                $dataSet[str_replace('`', '', $set->column)] = $set->value;
            };
            return $dataSet;
        } elseif ($sqlStatement instanceof InsertStatement) {
            $columns = $sqlStatement->into->columns;
            $result = [];
            for ($i = 0; $i < count($sqlStatement->values); $i++) {
                $sets = $sqlStatement->values[$i];
                $data = [];
                for ($j = 0; $j < count($sets->values); $j++) {
                    $data[$columns[$j]] = stripslashes($sets->values[$j]);
                }
                array_push($result, $data);
            }
            return $result;
        }

    }

    /**
     * Creates Select SQL query from query in Parser
     *
     * @param SqlParser\Parser $parser
     * @param string $idColumn
     * @return string
     */
    private static function getSelectQuery($parser, $idColumn) {
        $selectQuery = "SELECT $idColumn FROM ";
        $sqlStatement = $parser->statements[0];
        if ($sqlStatement instanceof InsertStatement) {
            $selectQuery = "SELECT * FROM " . $sqlStatement->into->dest . " WHERE ";
            $dataSet = self::getColumnDataToSet($sqlStatement);
            $whereConditions = [];
            foreach ($dataSet[0] as $key => $value) {
                array_push($whereConditions, $key . '=\'' . $value . '\'');
            }
            $selectQuery .= join(" AND ", $whereConditions);
            return $selectQuery;
        }
        if (isset($sqlStatement->from)) {
            $from = $sqlStatement->from[0];
        } else {
            $from = $sqlStatement->tables[0];
        }

        $selectQuery .= $from->expr;
        if ($from->alias != null) {
            $selectQuery .= ' AS ' . $from->alias;
        }
        if (isset($sqlStatement->join)) {
            $join = $sqlStatement->join[0];
            $selectQuery .= ' JOIN ' . $join->expr->expr;
            if ($join->expr->alias != null) {
                $selectQuery .= ' AS ' . $join->expr->alias;
            }
            if ($join->on != null) {
                $selectQuery .= ' ON ' . $join->on[0]->expr;
            }
        }
        return $selectQuery;
    }

    /**
     * If query contains some suspicious patten, we need to transform it and than create Parser for further use.
     *
     * @param $query
     * @return SqlParser\Parser
     */
    private static function getParser($query) {
        $containsUsingPattern = "/(.*)(USING ?\\(([^\\)]+)\\))(.*)/"; //https://regex101.com/r/vF6dI5/1
        $isTransformed = false;
        $parser = new Parser($query);
        $transformedQuery = '';
        $sqlStatement = $parser->statements[0];
        if ($sqlStatement instanceof DeleteStatement) {
            $transformedPart = 'ON ';
            if (preg_match_all($containsUsingPattern, $query, $matches)) {
                $usingColumn = str_replace('`', '', $matches[3][0]);
                $isTransformed = true;
                $from = $sqlStatement->from[0];
                if ($from->alias != null) {
                    $transformedPart .= $from->alias . '.' . $usingColumn;
                } else {
                    $transformedPart .= $from->table . '.' . $usingColumn;
                }
                $transformedPart .= '=';
                $join = $sqlStatement->join[0];

                if ($join->expr->alias != null) {
                    $transformedPart .= $join->expr->alias . '.' . $usingColumn;
                } else {
                    $transformedPart .= $join->expr->table . '.' . $usingColumn;
                }
                $transformedQuery = preg_replace($containsUsingPattern, '$1' . $transformedPart . '$4', $query);

            }
        }
        if ($isTransformed) {
            return new Parser($transformedQuery);
        }
        return $parser;
    }

    /**
     * Returns ID column for a table
     *
     * @param DbSchemaInfo $schema
     * @param string $table
     * @return mixed
     */
    private static function resolveIdColumn($schema, $table) {

        $entity = $schema->getEntityInfoByPrefixedTableName($table);
        return $entity == null ? null : $entity->idColumnName;
    }

    /**
     * Returns entity name for a table
     *
     * @param DbSchemaInfo $schema
     * @param string $table
     * @return mixed
     */
    private static function resolveEntityName($schema, $table) {

        $entity = $schema->getEntityInfoByPrefixedTableName($table);
        return $entity == null ? null : $entity->entityName;
    }
    
}
