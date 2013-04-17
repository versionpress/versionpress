<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');

global $table_prefix;

function createVersionPressIdentifiers($tableName) {
    global $wpdb;
    $rows = $wpdb->get_results("SELECT * FROM $tableName");
    $uniqueIds = array();

    foreach ($rows as $row) {
        $uniqid = base_convert(uniqid(), 16, 10);
        $row->vp_id = $uniqid;
        $uniqueIds[$row->ID] = $uniqid;
    }

    foreach ($rows as $row) {
        if ($row->post_parent != 0) {
            $parentId = $uniqueIds[$row->post_parent];
            $row->vp_parent_id = $parentId;
        }
    }

    $wpdb->query("FLUSH TABLES $tableName WITH READ LOCK;SET AUTOCOMMIT=0;START TRANSACTION;");
    $result = true;
    foreach ($rows as $row) {
        $result &= (bool)$wpdb->update($tableName, ["vp_id" => $row->vp_id, "vp_parent_id" => $row->vp_parent_id], ["ID" => $row->ID]);
    }
    if ($result == true) {
        $wpdb->query("COMMIT;UNLOCK TABLES;");
    } else {
        $wpdb->query("ROLLBACK;UNLOCK TABLES");
    }
}


createVersionPressIdentifiers($table_prefix . 'posts');