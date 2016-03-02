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
     * @param $schema DbSchemaInfo
     * @param \wpdb $wpdb
     */
    public function __construct($schema, $wpdb) {
        $this->schema = $schema;
        $this->wpdb = $wpdb;
    }

    /**
     * Parses Sql query called. If not parseable, returns null
     * @param $query string
     * @param $schema DbSchemaInfo
     * @param \wpdb $wpdb
     * @return ParsedQueryData
     */
    public function parseQuery($query) {
        $parser = self::getParser($query);
        $primaryStatement = $parser->statements[0];
        if ($primaryStatement instanceof UpdateStatement) {
            return self::parseUpdateQuery($parser, $query, $this->schema, $this->wpdb);
        } elseif ($primaryStatement instanceof InsertStatement) {
            return self::parseInsertQuery($parser, $query, $this->schema);
        } elseif ($primaryStatement instanceof DeleteStatement) {
            return self::parseDeleteQuery($parser, $query, $this->schema, $this->wpdb);
        }
        return null;

    }

    /**
     * Parses UPDATE query
     *
     * @param $parser Parser
     * @param $schema DbSchemaInfo
     * @param $query string
     * @param \wpdb $wpdb
     */
    private static function parseUpdateQuery($parser, $query, $schema, $wpdb) {
        $statement = $parser->statements[0];
        $table = $statement->tables[0]->table;
        $idColumn = self::getIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $result = new ParsedQueryData(ParsedQueryData::UPDATE_QUERY);
        $result->originalQuery = $query;
        $result->table = $table;
        $result->idColumn = $idColumn;
        $selectSql = self::getSelect($parser, $idColumn);
        $where = self::getWhereFragments($parser, $query, $statement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $result->query = $selectSql;
        $result->ids = $wpdb->get_col($selectSql);
        $result->data = self::getData($statement);
        $result->usesSqlFunctions = self::isUsingSqlFunctions($parser);
        $result->where = self::getWhere($where);
        return $result;
    }

    /**
     * Parses INSERT query
     *
     * @param $parser
     * @param $query
     * @return ParsedQueryData
     */
    private static function parseInsertQuery($parser, $query, $schema) {
        $statement = $parser->statements[0];
        $queryType = ParsedQueryData::INSERT_QUERY;
        $table = $statement->into->dest->table;
        $idColumn = self::getIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        if (count($statement->options->options) > 0) {
            foreach (array_keys($statement->options->options) as $key) {
                $queryType .= "_" . $statement->options->options[$key];
            }
        }
        if(strpos($query, 'ON DUPLICATE KEY UPDATE') !== false) {
            $queryType .= "_UPDATE";
        }
        $result = new ParsedQueryData($queryType);
        $result->data = self::getData($statement);
        $result->table = $table;
        $result->originalQuery = $query;
        $result->usesSqlFunctions = self::isUsingSqlFunctions($parser);
        $selectSql = self::getSelect($parser, $idColumn);
        $result->query = $selectSql;
        return $result;
    }


    /**
     * Parses DELETE query
     *
     * @param $parser
     * @param $query
     * @param $schema
     * @param $wpdb
     * @return ParsedQueryData
     */
    private static function parseDeleteQuery($parser, $query, $schema, $wpdb) {
        $statement = $parser->statements[0];
        $table = $statement->from[0]->table;
        $idColumn = self::getIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $result = new ParsedQueryData(ParsedQueryData::DELETE_QUERY);
        $result->originalQuery = $query;
        $result->idColumn = $idColumn;
        $result->table = $table;
        $selectSql = self::getSelect($parser, $idColumn);
        $where = self::getWhereFragments($parser, $query, $statement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $result->query = $selectSql;
        $result->ids = $wpdb->get_col($selectSql);
        $result->usesSqlFunctions = self::isUsingSqlFunctions($parser);
        $result->where = self::getWhere($where);
        return $result;
    }

    /**
     * Returns representation of WHERE SQL clauses found in whole query
     *
     * @param $parser
     * @param $sql
     * @param $primaryStatement
     * @return array
     */
    private static function getWhereFragments($parser, $sql, $primaryStatement) {
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
     * @param $statement
     * @return array
     */
    private static function getData($statement) {

        if ($statement instanceof UpdateStatement) {
            $dataSet = [];
            foreach ($statement->set as $set) {
                $dataSet[str_replace('`', '', $set->column)] = $set->value;
            };
            return $dataSet;
        } elseif ($statement instanceof InsertStatement) {
            $columns = $statement->into->columns;
            $result = [];
            for ($i = 0; $i < count($statement->values); $i++) {
                $sets = $statement->values[$i];
                $data = [];
                for ($j = 0; $j < count($sets->values); $j++) {
                    $data[$columns[$j]] = $sets->values[$j];
                }
                array_push($result, $data);
            }
            return $result;
        }

    }

    /**
     * If query contains some markers (unknown functions) or more than one statement.
     * @param $parser
     * @return bool
     */
    private static function isUsingSqlFunctions($parser) {
        if (count($parser->statements) > 1) {
            return true;
        } else {
            $statement = $parser->statements[0];
            if ($statement instanceof UpdateStatement) {
                return self::containsDirtyPatterns($parser->statements[0]->set);
            } elseif ($statement instanceof InsertStatement) {
                if (isset($statement->tables)) {
                    return true;
                }
                $values = [];
                foreach ($statement->values as $dataSet) {
                    $values = array_merge($values, $dataSet->values);
                }
                return self::containsDirtyPatterns($values);
            }
        }
    }

    /**
     * Returns Sql query WHERE fragments as array
     *
     * Example
     *
     * [where] => Array
     *     (
     *          [0] => option_name LIKE '%_'
     *          [1] => option_value LIKE '%s'
     *     )
     * Filters operators  where fragments
     * @param $whereConditions
     * @return mixed
     */
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
            "/DATE_ADD[ ]?\\(.*/", //https://regex101.com/r/tS2iC6/1
            "/NOW\\(\\)/",
            "/NOW/"
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

    /**
     * Creates Select SQL query from query in Parser
     *
     *
     *
     * @param $parser
     * @param $idColumn
     * @return string
     */
    private static function getSelect($parser, $idColumn) {
        $query = "SELECT $idColumn FROM ";
        $statement = $parser->statements[0];
        if ($statement instanceof InsertStatement) {
            $query = "SELECT * FROM " . $statement->into->dest . " WHERE ";
            $dataSet = self::getData($statement);
            $whereConditions = [];
            foreach ($dataSet[0] as $key => $value) {
                array_push($whereConditions, $key . '=\'' . $value . '\'');
            }
            $query .= join(" AND ", $whereConditions);
            return $query;
        }
        if (isset($statement->from)) {
            $from = $statement->from[0];
        } else {
            $from = $statement->tables[0];
        }

        $query .= $from->expr;
        if ($from->alias != null) {
            $query .= ' AS ' . $from->alias;
        }
        if (isset($statement->join)) {
            $join = $statement->join[0];
            $query .= ' JOIN ' . $join->expr->expr;
            if ($join->expr->alias != null) {
                $query .= ' AS ' . $join->expr->alias;
            }
            if ($join->on != null) {
                $query .= ' ON ' . $join->on[0]->expr;
            }
        }
        return $query;
    }

    /**
     * If query contains some suspicious patten, we need to transform it and than create Parser for further use.
     *
     * @param $query
     * @return Parser
     */
    private
    static function getParser($query) {
        $containsUsingPattern = "/(.*)(USING ?\\(([^\\)]+)\\))(.*)/"; //https://regex101.com/r/vF6dI5/1
        $isTransformed = false;
        $parser = new Parser($query);
        $transformedQuery = '';
        $primaryStatement = $parser->statements[0];
        if ($primaryStatement instanceof DeleteStatement) {
            $transformedPart = 'ON ';
            if (preg_match_all($containsUsingPattern, $query, $matches)) {
                $usingColumn = str_replace('`', '', $matches[3][0]);
                $isTransformed = true;
                $from = $primaryStatement->from[0];
                if ($from->alias != null) {
                    $transformedPart .= $from->alias . '.' . $usingColumn;
                } else {
                    $transformedPart .= $from->table . '.' . $usingColumn;
                }
                $transformedPart .= '=';
                $join = $primaryStatement->join[0];

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
     * @param $schema DbSchemaInfo
     * @param $table
     * @return mixed
     */
    private
    static function getIdColumn($schema, $table) {

        $entity = $schema->getEntityInfoByPrefixedTableName($table);
        return $entity == null ? null : $entity->idColumnName;
    }


}
