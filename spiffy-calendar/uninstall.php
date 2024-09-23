<?php

if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
} else {
global $wpdb;

// drop tables
define('WP_SPIFFYCAL_TABLE', 'spiffy_calendar');
define('WP_SPIFFYCAL_CATEGORIES_TABLE', 'spiffy_calendar_categories');
define('WP_SPIFFYCAL_META_TABLE', 'spiffy_calendar_meta');
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE);
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE);
$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_META_TABLE);

// delete options
delete_option('spiffy_calendar_options');
}
?>