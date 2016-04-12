<?php

namespace VersionPress\Utils;

use VersionPress\Database\Database;

class WordPressCacheUtils {
    public static function clearPostCache($vpids, $database) {
        if (count($vpids) === 0 || !function_exists('clean_post_cache')) {
            return;
        }

        $postIds = self::getIdsForVpids($vpids, $database);

        foreach ($postIds as $id) {
            clean_post_cache($id);
        }
    }

    public static function clearTermCache($vpids, $database) {
        if (count($vpids) === 0 || !function_exists('clean_term_cache')) {
            return;
        }

        $termIds = self::getIdsForVpids($vpids, $database);
        clean_term_cache($termIds);
    }

    public static function clearUserCache($vpids, $database) {
        if (count($vpids) === 0 || !function_exists('clean_user_cache')) {
            return;
        }

        $userIds = self::getIdsForVpids($vpids, $database);

        foreach ($userIds as $id) {
            clean_user_cache($id);
        }
    }

    public static function clearCommentCache($vpids, $database) {
        if (count($vpids) === 0 || !function_exists('clean_comment_cache')) {
            return;
        }

        $commentsIds = self::getIdsForVpids($vpids, $database);
        clean_comment_cache($commentsIds);
    }

    /**
     * @param $vpids
     * @param Database $database
     * @return mixed
     */
    private static function getIdsForVpids($vpids, $database) {
        $vpidsForRestriction = self::joinVpidsForRestriction($vpids);
        return $database->get_col("SELECT id FROM {$database->vp_id} WHERE vp_id IN ($vpidsForRestriction)");
    }

    private static function joinVpidsForRestriction($vpids) {
        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));
        return $vpidsForRestriction;
    }
}
