<?php
/**
 * Clean up after software update
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// The old tables
define('WP_SPIFFYCAL_TABLE', 'spiffy_calendar');
define('WP_SPIFFYCAL_CATEGORIES_TABLE', 'spiffy_calendar_categories');
define('WP_SPIFFYCAL_META_TABLE', 'spiffy_calendar_meta');

if (!class_exists("SPIFFYCAL_db_cleanup")) {
class SPIFFYCAL_update_cleanup {

	private $spiffy_version = "5.0.0";	// software format version number - tables are discontinued 

	/*
	** Setup
	*/
	public function __construct(){
	}

	/*
	** Look for action to take with new software release
	*/
	function check_cleanup()	{
		global $wpdb, $spiffy_calendar, $spiffycal_custom_posts;

		// Compare saved option to the current version
		if ($spiffy_calendar->current_options['calendar_version'] == $this->spiffy_version)
			return;
		
		// Assume this is a new install until we prove otherwise
		$new_install = true;
		$wp_spiffycal_exists = false;

		// Determine if the calendar exists
		$sql = "SHOW TABLES LIKE '" . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . "'";
		$ans =  $wpdb->get_results($sql);
		if (count($ans) > 0) {
			$new_install = false;  // Event table already exists. Assume other table does too.
		}

		if ( $new_install == true ) {
			// Fresh install - no need to create tables as they are discontinued in version 5.0.0
			
			// Older versions - update tables and then convert to posts 
		} else if ($spiffy_calendar->current_options['calendar_version'] == '4.8.0') {
			$this->db_convert_to_posts();
		} else if ($spiffy_calendar->current_options['calendar_version'] == '3.8.0') {
			$this->db_create_meta();
			$this->db_convert_to_posts();
		} else if ($spiffy_calendar->current_options['calendar_version'] == '3.7.0') {
			$this->db_update_status();
			$this->db_create_meta();
			$this->db_convert_to_posts();
		} else if ($spiffy_calendar->current_options['calendar_version'] == '3.5.6') {
			$this->db_update_status();
			$this->db_update_location();
			$this->db_create_meta();
			$this->db_convert_to_posts();
		} else if ($spiffy_calendar->current_options['calendar_version'] == '3.5.0') {
			$this->db_update_status();
			$this->db_update_collation();
			$this->db_update_location();
			$this->db_create_meta();
			$this->db_convert_to_posts();
		} else if ($spiffy_calendar->current_options['calendar_version'] == '3.4.0') {
			$this->db_update_status();
			$this->db_update_titles();
			$this->db_update_collation();
			$this->db_update_location();
			$this->db_create_meta();
			$this->db_convert_to_posts();
		} else {
			// Tables exist in some form before version numbers were implemented. 
			$this->db_update_status();
			$this->db_update_titles();
			//$this->db_update_collation(); Not here - add columns first, then update collation
			$this->db_update_location();
			$this->db_create_meta();

			// Check whether the newer columns are in the event table 
			$samples = $wpdb->get_results( 'SELECT * FROM '. $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . ' LIMIT 1', OBJECT);
            if (count($samples) == 0) {
				// no events found, insert a dummy event to get the structure
				$result = $wpdb->get_results("INSERT " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_title='temp'");				
				$samples = $wpdb->get_results( 'SELECT * FROM '. $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . ' LIMIT 1', OBJECT);
				if (count($samples) == 0) {
					// event insert failed, something is seriously wrong. Turn on message to enable logging.
					//error_log ("Spiffy Calendar table cannot be updated");
				} else {
					$sql = $wpdb->prepare("DELETE FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", $samples[0]->event_id);
					$wpdb->get_results($sql);
				}
			}
			
			// Check for newer columns
			$hide_ok = false;
			$mult_ok = false;
			foreach ($samples as $sample) {
				if (!isset($sample->event_hide_events)) {
					// Old version of the table found. Add two new columns.
					$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " ADD COLUMN event_hide_events CHAR(1) NOT NULL DEFAULT 'F' COLLATE utf8_general_ci";
					$wpdb->get_results($sql);
					$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " ADD COLUMN event_show_title CHAR(1) NOT NULL DEFAULT 'F' COLLATE utf8_general_ci";
					$wpdb->get_results($sql);
				}
				
				// Check for event_recur_multiplier column
				if (!isset($sample->event_recur_multiplier)) {
					// Old version of the table found. Add new column.
					$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " ADD COLUMN event_recur_multiplier INT(2) NOT NULL DEFAULT 1";
					$wpdb->get_results($sql);
				}
				
				// Check for event_all_day column
				if (!isset($sample->event_all_day)) {
					// Older version of the table found, add new column.
					$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " ADD COLUMN event_all_day CHAR(1) DEFAULT 'T' COLLATE utf8_general_ci";
					$wpdb->get_results($sql);
					
					// Set this column false on all events with non-zero event_time
					$sql = "UPDATE ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE." SET event_all_day='F' WHERE event_time != '00:00:00'";
					$wpdb->get_results($sql);
				}
			}

			// Set collation on all text fields
			$this->db_update_collation();
			$this->db_convert_to_posts();
		}
		
		// Update role capabilities of those who can or cannot manage events
		if ($spiffycal_custom_posts != null) {
			$spiffycal_custom_posts->adjust_roles ($spiffy_calendar->current_options['can_manage_events'], $spiffy_calendar->current_options['limit_author']);
		} 

		// Update the stored version
		$spiffy_calendar->current_options['calendar_version'] = $this->spiffy_version;
		update_option($spiffy_calendar->spiffy_options, $spiffy_calendar->current_options);		
	}

	/*
	** Create calendar meta table
	*/
	function db_create_meta () {
		global $wpdb;
		
		$sql = "CREATE TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_META_TABLE . " ( 
			event_id INT(11) UNSIGNED,
			meta_key INT(11) UNSIGNED, 
			meta_value VARCHAR(255) NOT NULL COLLATE utf8_general_ci, 
			KEY (event_id),
			KEY (meta_key)
		 )";
		$wpdb->get_results($sql);	
	}
	
	/*
	** Text fields in db needs update to utf8_general_ci
	*/
	function db_update_collation () {
		global $wpdb;
		
		$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " 
			MODIFY COLUMN event_title VARCHAR(60) NOT NULL COLLATE utf8_general_ci,
			MODIFY COLUMN event_desc TEXT NOT NULL COLLATE utf8_general_ci,
			MODIFY COLUMN event_all_day CHAR(1) DEFAULT 'T' COLLATE utf8_general_ci,
			MODIFY COLUMN event_recur CHAR(1) COLLATE utf8_general_ci,
			MODIFY COLUMN event_hide_events CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci,
			MODIFY COLUMN event_show_title CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci,
			MODIFY COLUMN event_link TEXT COLLATE utf8_general_ci
				";
		$wpdb->get_results($sql);
		
		$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . "
			MODIFY COLUMN category_name VARCHAR(30) NOT NULL COLLATE utf8_general_ci,
			MODIFY COLUMN category_colour VARCHAR(30) NOT NULL COLLATE utf8_general_ci
				";
		$wpdb->get_results($sql);
	}

	/*
	** New location field
	*/
	function db_update_location () {
		global $wpdb;
		
		$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . "
			ADD COLUMN event_location TEXT NOT NULL COLLATE utf8_general_ci,
			ADD COLUMN event_link_location CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci
				";
		$wpdb->get_results($sql);
	}
	
	/*
	** New status field
	*/
	function db_update_status () {
		global $wpdb;
		
		$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . "
			ADD COLUMN event_status CHAR(1) DEFAULT 'P' COLLATE utf8_general_ci
				";
		$wpdb->get_results($sql);
	}
	
	/*
	** Title field in db needs update from 30 chars to 60 chars
	*/
	function db_update_titles () {
		global $wpdb;
		
		$sql = "ALTER TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " MODIFY COLUMN event_title VARCHAR(60) NOT NULL COLLATE utf8_general_ci";
		$wpdb->get_results($sql);
	}

	/*
	** Database tables are discontinued. Convert to the new custom post and taxonomy
	*/
	function db_convert_to_posts () {
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields;

		// Quit if posts already exist
		$posts = get_posts ( array ( 'post_type' => 'spiffy_event' ) );
		if ( $posts) return;
		
		// Create categories
		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE;
		$cats = $wpdb->get_results($sql);
		foreach($cats as $cat) {
			$term = term_exists( esc_html(stripslashes($cat->category_name)), 'spiffy_categories' );
			if ( $term === 0 || $term === null ) {
				$term = wp_insert_term ( esc_html(stripslashes($cat->category_name)), 'spiffy_categories' );
			}
			update_term_meta ( $term['term_id'], 'color', esc_html(stripslashes($cat->category_colour)) );
			$cat->term_id = $term['term_id'];
		}
		
		// Read all old events
		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE;
		$events = $wpdb->get_results($sql);
		//print_r($events);
				
		// Process each event
		foreach ($events as $event) {
			// Fix post status
			if ($event->event_status == 'P') {
				$event->event_status = 'Publish';
			} else if ($event->event_status == 'D') {
				$event->event_status = 'Draft';
			} else if ($event->event_status == 'R') {
				$event->event_status = 'Pending';
			} else {
				$event->event_status = 'Publish';
			}
			
			// Map old category to new value. Note only single values were allowed.
			$new_term = '';
			foreach ($cats as $cat) {
				if ($cat->category_id == $event->event_category) {
					$new_term = intval($cat->term_id);
					break;
				}
			}
			$event->event_category = $new_term;

			// All day converted to blanks
			if ($event->event_all_day == 'T') {
				$event->event_time = '';
				$event->event_end_time = '';
			}
			
			// always get the custom fields, if any, and convert to new field names
			// if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
					
				/* Extract old custom fields from db */
				$sql = $wpdb->prepare("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_META_TABLE . " WHERE event_id='%s'", $event->event_id);
				$db_result = $wpdb->get_results($sql);
				foreach ($db_result as $meta) {
					$event->custom_field['_spiffy_cf_'.$meta->meta_key] = $meta->meta_value;
				}
			// }	
	
			// Note that wp_insert_post will sanitize
			$result = $spiffy_calendar->add_or_update_event ('', $event);
			$event_id = isset($_REQUEST['event'])? sanitize_text_field($_REQUEST['event']) : '';
			
			// Add custom fields if bonus addons not active. If bonus addons are active this is done in add_or_update_event.
			if ( isset($event->custom_field) && ($event_id != '') && ( (!$spiffy_calendar->bonus_addons_active() || !isset ($spiffycal_custom_fields) )) ) {
				foreach ( $event->custom_field as $key => $value ) {
					update_post_meta( $event_id, esc_html ($key), esc_html ($value) );
				}
			}
		}
	
	}
}
}

if (class_exists("SPIFFYCAL_update_cleanup")) {
	global $spiffycal_update_cleanup;
	$spiffycal_update_cleanup = new SPIFFYCAL_update_cleanup();
}