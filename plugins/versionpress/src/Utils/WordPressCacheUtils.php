<?php

namespace VersionPress\Utils;

class WordPressCacheUtils {
    public static function clearPostCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_post_cache')) {
            return;
        }

        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));

        $postIds = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}vp_id WHERE vp_id IN ($vpidsForRestriction)");

        foreach ($postIds as $id) {
            clean_post_cache($id);
        }
    }

    public static function clearTermCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_term_cache')) {
            return;
        }

        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));

        $termIds = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}vp_id WHERE vp_id IN ($vpidsForRestriction)");
        clean_term_cache($termIds);
    }

    public static function clearUserCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_user_cache')) {
            return;
        }

        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));

        $userIds = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}vp_id WHERE vp_id IN ($vpidsForRestriction)");

        foreach ($userIds as $id) {
            clean_user_cache($id);
        }
    }

    public static function clearCommentCache($vpids, $wpdb) {
        if (count($vpids) === 0 || !function_exists('clean_comment_cache')) {
            return;
        }

        $vpidsForRestriction = join(', ', array_map(function ($vpid) {
            return "UNHEX('$vpid')";
        }, $vpids));

        $commentsIds = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}vp_id WHERE vp_id IN ($vpidsForRestriction)");
        clean_comment_cache($commentsIds);
    }
}