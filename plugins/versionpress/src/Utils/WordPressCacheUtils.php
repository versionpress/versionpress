<?php

namespace VersionPress\Utils;

use VersionPress\Database\Database;

class WordPressCacheUtils
{
    public static function cleanCache($cacheType, $vpids, $database)
    {
        $cleanFunction = "clean_{$cacheType}_cache";

        if (count($vpids) === 0 || !function_exists($cleanFunction)) {
            return;
        }

        $ids = self::getIdsForVpids($vpids, $database);

        foreach ($ids as $id) {
            $cleanFunction($id);
        }

    }

    public static function clearTermCache($vpids, $database)
    {
        if (count($vpids) === 0 || !function_exists('clean_term_cache')) {
            return;
        }

        $termIds = self::getIdsForVpids($vpids, $database);
        clean_term_cache($termIds);
    }

    /**
     * @param $vpids
     * @param Database $database
     * @return mixed
     */
    private static function getIdsForVpids($vpids, $database)
    {
        $vpidsForRestriction = self::joinVpidsForRestriction($vpids);
        return $database->get_col("SELECT id FROM {$database->vp_id} WHERE vp_id IN ($vpidsForRestriction)");
    }

    private static function joinVpidsForRestriction($vpids)
    {
        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));
        return $vpidsForRestriction;
    }
}
