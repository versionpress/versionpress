<?php

namespace VersionPress\ChangeInfos;

class ChangeInfoUtils
{
    /**
     * Extract tags new / old entity. Tags in `actions.yml` describes from which fields should be their values extracted.
     * The field is at first searched in the $newEntity.
     *
     * @param $tags
     * @param $oldEntity
     * @param $newEntity
     * @return array
     */
    public static function extractTags($tags, $oldEntity, $newEntity)
    {
        $extractedTags = [];

        foreach ($tags as $tagName => $field) {
            if (isset($newEntity[$field])) {
                $extractedTags[$tagName] = $newEntity[$field];
            } elseif (isset($oldEntity[$field])) {
                $extractedTags[$tagName] = $oldEntity[$field];
            }
        }

        return $extractedTags;
    }

    public static function changeInfoRepresentsEntity($changeInfo, $entityName)
    {
        return $changeInfo instanceof EntityChangeInfo && $changeInfo->getScope() === $entityName;
    }
}
