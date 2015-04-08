<?php

namespace VersionPress\Utils;

use VersionPress\Database\DbSchemaInfo;

class ReferenceUtils {

    /**
     * Returns complex info about the M:N reference. The source is always the given entity, target is the second one.
     *
     * @param DbSchemaInfo $dbSchema
     * @param $entityName
     * @param $reference
     * @return array The details has keys 'junction-table', 'source-entity', 'source-column', 'target-entity' and 'target-column'.
     */
    public static function getMnReferenceDetails(DbSchemaInfo $dbSchema, $entityName, $reference) {
        list($junctionTable, $targetColumn) = explode(".", $reference);
        $targetEntity = $dbSchema->getEntityInfo($entityName)->mnReferences[$reference];
        $sourceColumn = self::getSourceColumn($dbSchema, $entityName, $targetEntity, $junctionTable);
        return array(
            'junction-table' => $junctionTable,
            'source-entity' => $entityName,
            'source-column' => $sourceColumn,
            'target-entity' => $targetEntity,
            'target-column' => $targetColumn,
        );
    }

    /**
     * Returns parsed info about the value reference.
     *
     * @param string $reference
     * @return array The details has keys 'source-column', 'source-value', 'value-column'
     */
    public static function getValueReferenceDetails($reference) {
        list($keyCol, $valueColumn) = explode("@", $reference);
        list($sourceColumn, $sourceValue) = explode("=", $keyCol);

        return array(
            'source-column' => $sourceColumn,
            'source-value'  => $sourceValue,
            'value-column' => $valueColumn,
        );
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
    private static function getSourceColumn(DbSchemaInfo $dbSchema, $sourceEntity, $targetEntity, $junctionTable) {
        $targetEntityMnReferences = $dbSchema->getEntityInfo($targetEntity)->mnReferences;
        foreach ($targetEntityMnReferences as $reference => $referencedEntity) {
            list($referencedTable, $referenceColumn) = explode(".", $reference);
            if ($referencedTable === $junctionTable && $referencedEntity === $sourceEntity) {
                return $referenceColumn;
            }
        }

        return null;
    }
}