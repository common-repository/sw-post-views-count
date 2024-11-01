<?php

/**
 * Sw Post View Counts Uninstall
 * Uninstalling tables, metadata created by plugin.
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;
$table_name = $wpdb->prefix . 'swpcv_daily_views';
$wpdb->query('DROP TABLE IF EXISTS ' . $table_name);
$wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE meta_key='_swpvc_views_count_status' ");
