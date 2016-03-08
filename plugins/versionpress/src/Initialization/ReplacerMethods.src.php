<?php

namespace VersionPress\Initialization;

use VersionPress\Database\ParsedQueryData;
use VersionPress\Database\SqlQueryParser;

/**
 * Methods in this class are used by WpdbReplacer. It pastes them into the wpdb class.
 * We have them in this class because of refactoring.
 *
 * @property null last_error
 * @property null last_query
 * @property null last_result
 * @property null rows_affected
 * @property null num_rows
 * @property null insert_id
 * @method __wp_insert($table, $data, $format)
 * @method __wp_update($table, $data, $where, $format, $where_format)
 * @method __wp_delete($table, $where, $where_format)
 * @method __wp_query($query)
 */
class ReplacerMethods {

    public function insert($table, $data, $format = null) {
        global $versionPressContainer;
        if ($versionPressContainer == null) {
            return $this->__wp_insert($table, $data, $format);
        }
        $this->vp_use_original_query_method = true;
        /** @var \VersionPress\Database\WpdbMirrorBridge $wpdbMirrorBridge */
        $wpdbMirrorBridge = $versionPressContainer->resolve(\VersionPress\DI\VersionPressServices::WPDB_MIRROR_BRIDGE);

        $r = $this->__wp_insert($table, $data, $format);

        if ($r === false) {
            return $r;
        }

        $this->vp_backup_fields();
        $wpdbMirrorBridge->insert($table, $data);
        $this->vp_restore_fields();
        $this->vp_use_original_query_method = false;
        return $r;

    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        global $versionPressContainer;
        if ($versionPressContainer == null) {
            return $this->__wp_update($table, $data, $where, $format, $where_format);
        }
        $this->vp_use_original_query_method = true;
        /** @var \VersionPress\Database\WpdbMirrorBridge $wpdbMirrorBridge */
        $wpdbMirrorBridge = $versionPressContainer->resolve(\VersionPress\DI\VersionPressServices::WPDB_MIRROR_BRIDGE);

        $r = $this->__wp_update($table, $data, $where, $format, $where_format);

        if ($r === false) {
            return $r;
        }

        $this->vp_backup_fields();
        $wpdbMirrorBridge->update($table, $data, $where);
        $this->vp_restore_fields();
        $this->vp_use_original_query_method = false;
        return $r;
    }

    public function delete($table, $where, $where_format = null) {
        global $versionPressContainer;
        if ($versionPressContainer == null) {
            return $this->__wp_delete($table, $where, $where_format);
        }
        $this->vp_use_original_query_method = true;
        /** @var \VersionPress\Database\WpdbMirrorBridge $wpdbMirrorBridge */
        $wpdbMirrorBridge = $versionPressContainer->resolve(\VersionPress\DI\VersionPressServices::WPDB_MIRROR_BRIDGE);

        $r = $this->__wp_delete($table, $where, $where_format);

        if ($r === false) {
            return $r;
        }

        $this->vp_backup_fields();
        $wpdbMirrorBridge->delete($table, $where);
        $this->vp_restore_fields();
        $this->vp_use_original_query_method = false;
        return $r;
    }

    public function query($query) {
        global $versionPressContainer;
        if ($versionPressContainer == null || $this->vp_use_original_query_method) {
            return $this->__wp_query($query);
        }

        $r = null;

        /** @var SqlQueryParser $sqlQueryParser */
        $sqlQueryParser = $versionPressContainer->resolve(\VersionPress\DI\VersionPressServices::SQL_QUERY_PARSER);

        $parsedQueryData = $sqlQueryParser->parseQuery($query);


        if ($parsedQueryData != null && $parsedQueryData->queryType !== \VersionPress\Database\ParsedQueryData::DELETE_QUERY) {
            $r = $this->__wp_query($query);
        }

        if ($parsedQueryData != null && $parsedQueryData->queryType == \VersionPress\Database\ParsedQueryData::INSERT_UPDATE_QUERY) {
            $parsedQueryData->ids = $this->insert_id;
        }

        if ($parsedQueryData == null) {
            $r = $this->__wp_query($query);
        }

        if ($r === false || $parsedQueryData == null) {
            return $r;
        }

        /** @var \VersionPress\Database\WpdbMirrorBridge $wpdbMirrorBridge */
        $wpdbMirrorBridge = $versionPressContainer->resolve(\VersionPress\DI\VersionPressServices::WPDB_MIRROR_BRIDGE);

        $this->vp_backup_fields();
        $wpdbMirrorBridge->query($parsedQueryData);
        $this->vp_restore_fields();

        if ($parsedQueryData != null && $parsedQueryData->queryType == \VersionPress\Database\ParsedQueryData::DELETE_QUERY) {
            $r = $this->__wp_query($query);
        }
        return $r;
    }

    /**
     * Used by VersionPress for restoring last_query, last_error etc. after its hook.
     *
     * @since VersionPress
     * @var array
     */
    private $vp_field_backup = array();

    /**
     * Used by VersionPress to avoid 'patched' query() method call when update,insert,delete methods are called
     *
     * @since VersionPress
     * @var boolean
     */
    private $vp_use_original_query_method;

    /**
     * @since VersionPress
     */
    private function vp_backup_fields() {
        $this->vp_field_backup = array(
            "last_error" => $this->last_error,
            "last_query" => $this->last_query,
            "last_result" => $this->last_result,
            "rows_affected" => $this->rows_affected,
            "num_rows" => $this->num_rows,
            "insert_id" => $this->insert_id,
        );
    }

    /**
     * @since VersionPress
     */
    private function vp_restore_fields() {
        $this->last_error = $this->vp_field_backup["last_error"];
        $this->last_query = $this->vp_field_backup["last_query"];
        $this->last_result = $this->vp_field_backup["last_result"];
        $this->rows_affected = $this->vp_field_backup["rows_affected"];
        $this->num_rows = $this->vp_field_backup["num_rows"];
        $this->insert_id = $this->vp_field_backup["insert_id"];
    }

    /**
     * @since VersionPress
     */
    public static function restoreOriginal() {
        $wpdbClassPath = ABSPATH . WPINC . '/wp-db.php';
        $wpdbOriginalPath = $wpdbClassPath . '.original';
        if (file_exists($wpdbOriginalPath)) {
            copy($wpdbOriginalPath, $wpdbClassPath);
            unlink($wpdbOriginalPath);
        }
    }


}
