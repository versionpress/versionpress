<?php

namespace VersionPress\Database;

use SqlParser\Parser;
use SqlParser\Statement;
use SqlParser\Statements\DeleteStatement;
use SqlParser\Statements\InsertStatement;
use SqlParser\Statements\ReplaceStatement;
use SqlParser\Statements\UpdateStatement;

class SqlQueryParser
{

    /**
     * @var DbSchemaInfo
     */
    private $schema;

    /**
     * @var Database
     */
    private $database;

    /**
     * SqlQueryParser constructor.
     *
     * @param DbSchemaInfo $schema
     * @param Database $database
     */
    public function __construct($schema, $database)
    {
        $this->schema = $schema;
        $this->database = $database;
    }

    /**
     * Parses Sql query called. If not parseable, returns null
     * @param string $query
     * @return ParsedQueryData
     */
    public function parseQuery($query)
    {
        $parser = $this->getParser($query);
        $sqlStatement = $parser->statements[0];
        if ($sqlStatement instanceof UpdateStatement) {
            return $this->parseUpdateQuery($parser, $query, $this->schema, $this->database);
        } elseif ($sqlStatement instanceof InsertStatement) {
            return $this->parseInsertQuery($parser, $query, $this->schema);
        } elseif ($sqlStatement instanceof DeleteStatement) {
            return $this->parseDeleteQuery($parser, $query, $this->schema, $this->database);
        }
        return null;

    }

    /**
     * Parses UPDATE query
     *
     * @param Parser $parser
     * @param DbSchemaInfo $schema
     * @param string $query
     * @param Database $database
     * @return ParsedQueryData
     */
    private function parseUpdateQuery($parser, $query, $schema, $database)
    {
        /** @var UpdateStatement $sqlStatement */
        $sqlStatement = $parser->statements[0];
        $table = $sqlStatement->tables[0]->table;
        $idColumn = $this->resolveIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $parsedQueryData = new ParsedQueryData(ParsedQueryData::UPDATE_QUERY);
        $parsedQueryData->table = $table;
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = $this->resolveEntityName($schema, $table);
        $selectSql = $this->getSelectQuery($parser, $idColumn);
        $where = $this->getWhereFragments($parser, $query, $sqlStatement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $parsedQueryData->sqlQuery = $selectSql;
        $parsedQueryData->ids = $database->get_col($selectSql);
        $parsedQueryData->data = $this->getColumnDataToSet($sqlStatement);
        return $parsedQueryData;
    }

    /**
     * Parses INSERT query
     *
     * @param Parser $parser
     * @param string $query
     * @param DbSchemaInfo $schema
     * @return ParsedQueryData
     */
    private function parseInsertQuery($parser, $query, $schema)
    {
        /** @var InsertStatement $sqlStatement */
        $sqlStatement = $parser->statements[0];
        $queryType = ParsedQueryData::INSERT_QUERY;
        $table = $sqlStatement->into->dest->table;
        $idColumn = $this->resolveIdColumn($schema, $table);
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
        $parsedQueryData->data = $this->getColumnDataToSet($sqlStatement);
        $parsedQueryData->table = $table;
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = $this->resolveEntityName($schema, $table);
        $selectSql = $this->getSelectQuery($parser, $idColumn);
        $parsedQueryData->sqlQuery = $selectSql;
        return $parsedQueryData;
    }

    /**
     * Parses DELETE query
     *
     * @param Parser $parser
     * @param string $query
     * @param DbSchemaInfo $schema
     * @param $database Database
     * @return ParsedQueryData
     */
    private function parseDeleteQuery($parser, $query, $schema, $database)
    {
        /** @var DeleteStatement $sqlStatement */
        $sqlStatement = $parser->statements[0];
        $table = $sqlStatement->from[0]->table;
        $idColumn = $this->resolveIdColumn($schema, $table);
        if ($idColumn == null) {
            return null;
        }
        $parsedQueryData = new ParsedQueryData(ParsedQueryData::DELETE_QUERY);
        $parsedQueryData->idColumnName = $idColumn;
        $parsedQueryData->entityName = $this->resolveEntityName($schema, $table);
        $parsedQueryData->table = $table;
        $selectSql = $this->getSelectQuery($parser, $idColumn);
        $where = $this->getWhereFragments($parser, $query, $sqlStatement);
        if (isset($where)) {
            $selectSql .= " WHERE " . join(' ', $where);
        }
        $parsedQueryData->sqlQuery = $selectSql;
        $parsedQueryData->ids = $database->get_col($selectSql);

        return $parsedQueryData;
    }

    /**
     * Returns representation of WHERE SQL clauses found in whole query
     *
     * @param Parser $parser
     * @param string $sqlQuery
     * @param DeleteStatement|UpdateStatement $primarySqlStatement
     * @return array
     */
    private function getWhereFragments($parser, $sqlQuery, $primarySqlStatement)
    {
        if ($primarySqlStatement->where != null) {
            $whereFragments = $primarySqlStatement->where;
            return $whereFragments;
        } elseif ($primarySqlStatement->where == null && strpos($sqlQuery, 'WHERE') !== false) {
            if (isset($parser->statements[1])) {
                /** @var UpdateStatement|DeleteStatement|ReplaceStatement $secondarySqlStatement */
                $secondarySqlStatement = $parser->statements[1];
                if ($secondarySqlStatement->where != null) {
                    $whereFragments = $secondarySqlStatement->where;
                    return $whereFragments;
                }
            }
        } elseif ($parser->errors != null && $primarySqlStatement->where == null
            && strpos($sqlQuery, 'WHERE') === false) {
            $whereFragments = ['1=1'];
            return $whereFragments;
        }
    }

    /**
     * Gets data which needs to be set by UPDATE statement
     *
     * Example
     *  [
     *          [ column => post_modified, value => NOW() ],
     *          [ column => another_column, value => 123 ]
     *  ]
     * @param Statement $sqlStatement
     * @return array
     */
    private function getColumnDataToSet($sqlStatement)
    {

        if ($sqlStatement instanceof UpdateStatement) {
            $dataSet = [];
            foreach ($sqlStatement->set as $set) {
                $dataSet[str_replace('`', '', $set->column)] = stripslashes($set->value);
            };
            return $dataSet;
        } elseif ($sqlStatement instanceof InsertStatement) {
            $columns = $sqlStatement->into->columns;
            $dataToSet = [];
            foreach ($sqlStatement->values as $sets) {
                $data = [];
                foreach ($sets->values as $i => $value) {
                    if (is_string($value)) {
                        $data[$columns[$i]] = stripslashes($value);
                    } else {
                        $data[$columns[$i]] = $value;
                    }
                }
                array_push($dataToSet, $data);
            }
            return $dataToSet;
        }

    }

    /**
     * Creates Select SQL query from query in Parser
     *
     * @param Parser $parser
     * @param string $idColumn
     * @return string
     */
    private function getSelectQuery($parser, $idColumn)
    {
        $selectQuery = "SELECT $idColumn FROM ";
        $sqlStatement = $parser->statements[0];
        if ($sqlStatement instanceof InsertStatement) {
            $selectQuery = "SELECT * FROM " . $sqlStatement->into->dest . " WHERE ";
            $dataSet = $this->getColumnDataToSet($sqlStatement);
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
            $from = $sqlStatement->{'tables'}[0];
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
     * @return Parser
     */
    private function getParser($query)
    {
        $containsUsingPattern = "/(.*)(USING ?\\(([^\\)]+)\\))(.*)/"; //https://regex101.com/r/vF6dI5/1
        $parser = new Parser($query);
        if (preg_match_all($containsUsingPattern, $query, $matches)) {
            return $this->getParserFromQueryWithUsingClause($query, $parser, $matches, $containsUsingPattern);
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
    private function resolveIdColumn($schema, $table)
    {

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
    private function resolveEntityName($schema, $table)
    {

        $entity = $schema->getEntityInfoByPrefixedTableName($table);
        return $entity == null ? null : $entity->entityName;
    }

    /**
     * @param $query
     * @param $parser
     * @param $matches
     * @param $containsUsingPattern
     * @return Parser
     */
    private function getParserFromQueryWithUsingClause($query, $parser, $matches, $containsUsingPattern)
    {
        /** @var DeleteStatement|InsertStatement $sqlStatement */
        $sqlStatement = $parser->statements[0];
        $transformedPart = 'ON ';
        $usingColumn = str_replace('`', '', $matches[3][0]);
        $from = $sqlStatement->from[0];
        if ($from->alias != null) {
            $transformedPart .= $from->alias . '.' . $usingColumn;
        } else {
            $transformedPart .= $from->table . '.' . $usingColumn;
        }
        $transformedPart .= '=';
        $join = $sqlStatement->{'join'}[0];

        if ($join->expr->alias != null) {
            $transformedPart .= $join->expr->alias . '.' . $usingColumn;
        } else {
            $transformedPart .= $join->expr->table . '.' . $usingColumn;
        }
        $transformedQuery = preg_replace($containsUsingPattern, '$1' . $transformedPart . '$4', $query);
        return new Parser($transformedQuery);
    }
}
