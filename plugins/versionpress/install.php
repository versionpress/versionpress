<?php

/** Load WordPress Bootstrap */
require_once(dirname(__FILE__) . '/../../../wp-load.php');

/** Load WordPress Administration Upgrade API */
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/** Load wpdb */
require_once(ABSPATH . 'wp-includes/wp-db.php');

/** Create WordPress tables */
dbDelta('all');

global $table_prefix, $wpdb;

$process = array();
$process[] = "DROP VIEW IF EXISTS `{$table_prefix}vp_reference_details`";
$process[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_references`";
$process[] = "DROP TABLE IF EXISTS `{$table_prefix}vp_id`";
$process[] = "CREATE TABLE `{$table_prefix}vp_id` (
          `vp_id` BINARY(16) NOT NULL,
          `table` VARCHAR(64) NOT NULL,
          `id` BIGINT(20) NOT NULL,
          PRIMARY KEY (`vp_id`),
          UNIQUE KEY `table_id` (`table`,`id`),
          KEY `id` (`id`)
        ) ENGINE=InnoDB;";

$process[] = "CREATE TABLE `{$table_prefix}vp_references` (
          `table` VARCHAR(64) NOT NULL,
          `reference` VARCHAR(64) NOT NULL,
          `vp_id` BINARY(16) NOT NULL,
          `reference_vp_id` BINARY(16) NOT NULL,
          PRIMARY KEY (`table`,`reference`,`vp_id`),
          KEY `reference_vp_id` (`reference_vp_id`),
          KEY `vp_id` (`vp_id`),
          CONSTRAINT `ref_vp_id` FOREIGN KEY (`vp_id`) REFERENCES `{$table_prefix}vp_id` (`vp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `ref_reference_vp_id` FOREIGN KEY (`reference_vp_id`) REFERENCES `{$table_prefix}vp_id` (`vp_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB;";

$process[] = "CREATE VIEW `{$table_prefix}vp_reference_details` AS
          SELECT `vp_id`.*, `vp_ref`.`reference`, `vp_ref`.`reference_vp_id`, `vp_id_ref`.`id` `reference_id`
          FROM `{$table_prefix}vp_id` `vp_id`
          JOIN `{$table_prefix}vp_references` `vp_ref` ON `vp_id`.`vp_id` = `vp_ref`.`vp_id`
          JOIN `{$table_prefix}vp_id` `vp_id_ref` ON `vp_ref`.`reference_vp_id` = `vp_id_ref`.`vp_id`;";

foreach ($process as $query) {
    $wpdb->query($query);
}

createEnvironmentSpecificOptions($wpdb, $table_prefix);
require_once(dirname(__FILE__) . '/sync.php');

function createEnvironmentSpecificOptions(wpdb $wpdb) {
    $siteUrl = getSiteUrl();
    $sql = "INSERT INTO {$wpdb->prefix}options (option_name, option_value, autoload) VALUES ";
    $sql .= "('siteurl', '{$siteUrl}', 'yes'), ";
    $sql .= "('home', '{$siteUrl}', 'yes');";
    $wpdb->query($sql);
}

function getSiteUrl() {
    $currentUrl = getCurrentUrl();
    return substr($currentUrl, 0, strpos($currentUrl, '/wp-content'));
}

function getCurrentUrl() {

    $pageUrl = 'http';
    if (@$_SERVER["HTTPS"] == "on") {
        $pageUrl .= "s";
    }
    $pageUrl .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageUrl .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageUrl .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageUrl;
}