<?php

/*
  Plugin Name: Sw post views count
  Plugin URI: http://codetrycatch.com/myplugins/sw-post-views-count
  description: A plugin that shows post views count.
  Version: 1.0.0
  Author: Sagar Walzade
  Author URI: http://codetrycatch.com
  Text Domain:  swpvc
 */


/**
 * Define Constants 
 */

define('SW_PVC_FILE', __FILE__);
define('SW_PVC_PATH', plugin_dir_path(__FILE__));
define('SW_PVC_URI', plugin_dir_url(__FILE__));
define('SW_PVC_PLUGIN_NAME', plugin_basename(__FILE__));

/**
 * Include Files 
 */

include 'admin/admin-functions.php';

/**
 * do some default settings when plugin activate
 * create 1 custom sql table to store all post views on the daily basis 
 */

if (!function_exists("swpcv_default_settings")) {

    register_activation_hook(SW_PVC_FILE, 'swpcv_default_settings');

    function swpcv_default_settings() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'swpcv_daily_views';

        $table_sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		post_id bigint(20) NOT NULL,
		post_type text DEFAULT '' NOT NULL,
		view_date date DEFAULT 0 NOT NULL,
		view_count bigint(20) NOT NULL,
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY id (id)
	) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($table_sql);
    }

}

/*
 * enqueue styles and scripts to admin area
 */

function load_custom_wp_admin_style($hook) {
    wp_enqueue_style('swpvc_admin_css', plugins_url('css/admin.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'load_custom_wp_admin_style');