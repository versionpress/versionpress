<?php

namespace VersionPress\ChangeInfos;

class ChangeInfoUtils
{
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
}
