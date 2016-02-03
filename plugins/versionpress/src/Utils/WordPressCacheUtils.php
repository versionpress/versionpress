<?php

namespace VersionPress\Utils;

class WordPressCacheUtils {
    public static function clearPostCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_post_cache')) {
            return;
        }

        $postIds = self::getIdsForVpids($vpids, $wpdb);

        foreach ($postIds as $id) {
            clean_post_cache($id);
        }
    }

    public static function clearTermCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_term_cache')) {
            return;
        }

        $termIds = self::getIdsForVpids($vpids, $wpdb);
        clean_term_cache($termIds);
    }

    public static function clearUserCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_user_cache')) {
            return;
        }

        $userIds = self::getIdsForVpids($vpids, $wpdb);

        foreach ($userIds as $id) {
            clean_user_cache($id);
        }
    }

    public static function clearCommentCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_comment_cache')) {
            return;
        }

        $commentsIds = self::getIdsForVpids($vpids, $wpdb);
        clean_comment_cache($commentsIds);
    }

    private static function getIdsForVpids($vpids, $wpdb) {
        $vpidsForRestriction = self::joinVpidsForRestriction($vpids);
        return $wpdb->get_col("SELECT id FROM {$wpdb->prefix}vp_id WHERE vp_id IN ($vpidsForRestriction)");
    }

    private static function joinVpidsForRestriction($vpids) {
        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));
        return $vpidsForRestriction;
    }
}