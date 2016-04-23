<?php

namespace VersionPress\Utils;

use VersionPress\Database\DbSchemaInfo;

class ReferenceUtils
{

    /**
     * Returns complex info about the M:N reference. The source is always the given entity, target is the second one.
     *
     * @param DbSchemaInfo $dbSchema
     * @param $entityName
     * @param $reference
     * @return array The details has keys 'junction-table', 'source-entity', 'source-column',
     *   'target-entity' and 'target-column'.
     */
    public static function getMnReferenceDetails(DbSchemaInfo $dbSchema, $entityName, $reference)
    {
        list($junctionTable, $targetColumn) = explode(".", $reference);
        $targetEntity = $dbSchema->getEntityInfo($entityName)->mnReferences[$reference];
        $sourceColumn = self::getSourceColumn($dbSchema, $entityName, $targetEntity, $junctionTable);
        return [
            'junction-table' => $junctionTable,
            'source-entity' => $entityName,
            'source-column' => $sourceColumn,
            'target-entity' => $targetEntity,
            'target-column' => $targetColumn,
        ];
    }

    /**
     * Returns parsed info about the value reference.
     *
     * @param string $reference
     * @return array The details has keys 'source-column', 'source-value', 'value-column'
     */
    public static function getValueReferenceDetails($reference)
    {
        list($keyCol, $valueColumn) = explode("@", $reference);
        list($sourceColumn, $sourceValue) = explode("=", $keyCol);
        if (strpos($sourceValue, '[') !== false) {
            list($sourceValue, $pathInStructure) = explode("[", $sourceValue, 2);
        } else {
            $pathInStructure = '';
        }

        return [
            'source-column' => $sourceColumn,
            'source-value' => $sourceValue,
            'value-column' => $valueColumn,
            'path-in-structure' => $pathInStructure,
        ];
    }

    /**
     * Returns name of column referencing synchronized entity in the junction table.
     * Example:
     * We are synchronizing posts with M:N reference to the taxonomies. The reference is defined
     * as term_relationships.term_taxonomy_id => term_taxonomy. Name of column referencing term_taxonomy is obvious,
     * it's term_taxonomy_id. However we need also name of column referencing the post. We can find this name
     * in the definition of M:N references of term_taxonomy, where it's defined as term_relationships.object_id => post.
     * So in the end we are looking for M:N reference to post with same junction table (term_relationships).
     *
     * @param DbSchemaInfo $dbSchema
     * @param $sourceEntity
     * @param $targetEntity
     * @param $junctionTable
     * @return string
     */
    private static function getSourceColumn(DbSchemaInfo $dbSchema, $sourceEntity, $targetEntity, $junctionTable)
    {
        $targetEntityMnReferences = $dbSchema->getEntityInfo($targetEntity)->mnReferences;
        foreach ($targetEntityMnReferences as $reference => $referencedEntity) {
            list($referencedTable, $referenceColumn) = explode(".", $reference);
            if ($referencedTable === $junctionTable && $referencedEntity === $sourceEntity) {
                return $referenceColumn;
            }
        }

        return null;
    }

    /**
     * Returns list of all paths in hierarchic structure (nested arrays, objects) matching given pattern.
     * The pattern comes from a schema file. For example line `some_option[/\d+/]["key"]` contains
     * pattern `[/\d+/]["key"]`. It can contain regular expressions to match multiple / dynamic keys.
     *
     * Examples:
     * For $value = [0 => [key => value], 1 => [key => value]]
     * And $pathInStructure = [0]["key"]
     * Returns [[0, key]]
     *
     * For the same $value nad $pathInStructure = [/\d+/]["key"]
     * Returns [[0, key], [1, key]]
     *
     *
     * @param array|object $value
     * @param string $pathInStructure
     * @return array
     */
    public static function getMatchingPaths($value, $pathInStructure)
    {
        // https://regex101.com/r/vR8yK3/2
        $re = "/(?:\\[(?<number>\\d+)|\"(?<string>(?:[^\"\\\\]|\\\\.)*)\"|\\/(?<regex>(?:[^\\/\\\\]|\\\\.)*)\\/)\\]+/";
        preg_match_all($re, $pathInStructure, $matches, PREG_SET_ORDER);
        $pathParts = array_map(function ($match) {
            if (strlen($match['number']) > 0) {
                return ['type' => 'exact-value', 'value' => intval($match['number'])];
            } else {
                if (strlen($match['string']) > 0) {
                    return ['type' => 'exact-value', 'value' => $match['string']];
                } else {
                    $regex = "/^$match[regex]$/";
                    return ['type' => 'regex', 'value' => $regex];
                }
            }
        }, $matches);

        $paths = self::getMatchingPathsFromSubtree($value, $pathParts);

        return $paths;
    }

    private static function getMatchingPathsFromSubtree($value, $pathParts)
    {
        if (!is_array($value) && !is_object($value)) {
            return [];
        }

        $currentLevelKey = array_shift($pathParts);
        $paths = [];

        foreach ($value as $key => $subTree) {
            if (($currentLevelKey['type'] === 'exact-value' && $currentLevelKey['value'] === $key) ||
                ($currentLevelKey['type'] === 'regex' && preg_match($currentLevelKey['value'], $key))
            ) {
                if (count($pathParts) > 0) {
                    $subPaths = self::getMatchingPathsFromSubtree($subTree, $pathParts);
                    foreach ($subPaths as $subPath) {
                        array_unshift($subPath, $key);
                        $paths[] = $subPath;
                    }
                } else {
                    $paths[] = [$key];
                }
            }
        }

        return $paths;
    }

    public static function valueMatchesWildcard($valueWithWildcards, $value)
    {
        // https://regex101.com/r/tC8zD4/3
        $re = "/(?<escaped>(?:(?:\\\\\\\\)|(?:\\\\\\*))+)|(?<asterisk>\\*)|(?<string>[^\\\\\\*]+)/";

        $regex = preg_replace_callback($re, function ($match) {
            if ($match['escaped'] !== '') {
                return preg_quote(stripslashes($match['escaped']), '/');
            }

            if ($match['asterisk'] === '*') {
                return '.*';
            }

            return preg_quote($match['string'], '/');

        }, $valueWithWildcards);

        return preg_match("/^{$regex}$/", $value);

    }
}
