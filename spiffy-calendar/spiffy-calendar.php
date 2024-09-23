<?php
/*
Plugin Name: Spiffy Calendar
Plugin URI:  http://www.spiffycalendar.spiffyplugins.ca
Description: A full featured, simple to use Spiffy Calendar plugin for WordPress that allows you to manage and display your events and appointments.
Version:     4.9.15
Author:      Spiffy Plugins
Author URI:  http://spiffyplugins.ca
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: spiffy-calendar

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.		See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA		02110-1301		USA
*/

// Define the tables used by Spiffy Calendar
global $wpdb;
define('WP_SPIFFYCAL_TABLE', 'spiffy_calendar');
define('WP_SPIFFYCAL_CATEGORIES_TABLE', 'spiffy_calendar_categories');
define('WP_SPIFFYCAL_META_TABLE', 'spiffy_calendar_meta');

// Version checks
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define ('SPIFFYCAL_BONUS_MINIMUM_VERSION', '3.25');

// Widget definitions
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-featured-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-minical-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-today-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-upcoming-widget.php');

// Define the admin list table for event management
require_once (plugin_dir_path(__FILE__) . 'includes/admin/event-list-table.php');

// Calendar modules
require_once (plugin_dir_path(__FILE__) . 'includes/views.php');

if (!class_exists("Spiffy_Calendar")) {
Class Spiffy_Calendar
{
	// private $gmt_offset = null; no longer used
	private $spiffy_options = 'spiffy_calendar_options';
	private $spiffy_version = "4.8.0";	// database format version number
	public $spiffycal_menu_page;
	public $spiffy_events_admin_list;
	public $current_options = array();

	public $spiffy_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIyNnB4IiBoZWlnaHQ9IjI2cHgiIHZpZXdCb3g9IjAgMCAyNiAyNiIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjYgMjYiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxyZWN0IHg9Ii0xIiB5PSIxOSIgZmlsbD0iI0NDREJFOCIgd2lkdGg9IjgiIGhlaWdodD0iOCIvPjxyZWN0IHg9IjkiIHk9IjE5IiBmaWxsPSIjQ0NEQkU4IiB3aWR0aD0iNyIgaGVpZ2h0PSI4Ii8+PHJlY3QgeD0iMTgiIHk9IjEiIGZpbGw9IiNDQ0RCRTgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciLz48cmVjdCB4PSItMSIgeT0iMTAiIGZpbGw9IiNDQ0RCRTgiIHdpZHRoPSI4IiBoZWlnaHQ9IjciLz48cmVjdCB4PSIxOCIgeT0iMTAiIGZpbGw9IiNDQ0RCRTgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciLz48cmVjdCB4PSI5IiB5PSIxIiBmaWxsPSIjQ0NEQkU4IiB3aWR0aD0iNyIgaGVpZ2h0PSI3Ii8+PHJlY3QgeD0iOSIgeT0iMTAiIGZpbGw9IiNDQ0RCRTgiIHdpZHRoPSI3IiBoZWlnaHQ9IjciLz48L2c+PC9zdmc+';
	
	function __construct()
	{
		// Admin stuff
		add_action('init', array($this, 'calendar_init_action'));
		add_action('admin_menu', array($this, 'admin_menu'), 10);
		add_action('admin_bar_menu', array($this, 'admin_toolbar'), 999 );
		add_filter('spiffycal_settings_tabs_array', array($this, 'settings_tabs_array_default'), 9);
		add_action('spiffycal_settings_tab_events', array($this, 'settings_tab_events'));
		add_action('spiffycal_settings_update_events', array($this, 'settings_update_events'));
		add_action('spiffycal_settings_tab_event_edit', array($this, 'settings_tab_event_edit'));
		add_action('spiffycal_settings_tab_theme', array($this, 'settings_tab_bonus'));
		//add_action('spiffycal_settings_tab_tickets', array($this, 'settings_tab_bonus'));
		add_action('spiffycal_settings_tab_frontend_submit', array($this, 'settings_tab_bonus'));
		add_action('spiffycal_settings_tab_custom_fields', array($this, 'settings_tab_bonus'));
		add_action('spiffycal_settings_update_event_edit', array($this, 'settings_update_event_edit'));
		add_action('spiffycal_settings_tab_categories', array($this, 'settings_tab_categories'));
		add_action('spiffycal_settings_update_categories', array($this, 'settings_update_categories'));
		add_action('spiffycal_settings_tab_options', array($this, 'settings_tab_options'));
		add_action('spiffycal_settings_update_options', array($this, 'settings_update_options'));

		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

		// Enable the ability for the calendar to be loaded from pages
		add_shortcode('spiffy-calendar', array($this, 'calendar_insert'));	
		add_shortcode('spiffy-minical', array($this, 'minical_insert'));	
		add_shortcode('spiffy-upcoming-list', array($this, 'upcoming_insert'));
		add_shortcode('spiffy-todays-list', array($this, 'todays_insert'));
		add_shortcode('spiffy-week', array($this, 'weekly_insert'));
		
		// Mailpoet shortcode support
		add_filter('wysija_shortcodes', array($this, 'mailpoet_shortcodes_custom_filter'), 10, 2);	// Version 2
		add_filter('mailpoet_newsletter_shortcode', array($this, 'mailpoet_v3_shortcodes_custom_filter'), 10, 5);	// Version 3

		// Add the functions that put style information in the header
		add_action('wp_enqueue_scripts', array($this, 'calendar_styles'));

		// Add the function that deals with deleted users
		add_action('delete_user', array($this, 'deal_with_delete_user'));
		
		// Admin screen option handling
		add_filter('set-screen-option', array($this, 'admin_menu_set_option'), 10, 3);
		

		// Get a local copy of our options
		$this->current_options = $this->get_options();
		// $this->current_options['display_upcoming_days'] = 7;
	}

	function bonus_addons_active() {
		return is_plugin_active( 'spiffy-calendar-addons/spiffy-calendar-addons.php' );
	}
	
	/*
	** Make sure Spiffy Calendar database tables are installed and up to date, if not fix them
	*/
	function check_db()	{
		global $wpdb;

		// Compare saved option to the current version
		if ($this->current_options['calendar_version'] == $this->spiffy_version)
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
			// Fresh install - create tables
			$sql = "CREATE TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " (
				event_id INT(11) NOT NULL AUTO_INCREMENT,
				event_status CHAR(1) DEFAULT 'P' COLLATE utf8_general_ci,
				event_begin DATE NOT NULL,
				event_end DATE NOT NULL,
				event_title VARCHAR(60) NOT NULL COLLATE utf8_general_ci,
				event_desc TEXT NOT NULL COLLATE utf8_general_ci,
				event_location TEXT NOT NULL COLLATE utf8_general_ci,
				event_link_location CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci,
				event_all_day CHAR(1) DEFAULT 'T' COLLATE utf8_general_ci,
				event_time TIME,
				event_end_time TIME,
				event_recur CHAR(1) COLLATE utf8_general_ci,
				event_recur_multiplier INT(2) DEFAULT 1,
				event_repeats INT(3),
				event_hide_events CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci,
				event_show_title CHAR(1) DEFAULT 'F' COLLATE utf8_general_ci,
				event_author BIGINT(20) UNSIGNED,
				event_category BIGINT(20) UNSIGNED NOT NULL DEFAULT 1,
				event_link TEXT COLLATE utf8_general_ci,
				event_image BIGINT(20) UNSIGNED,
				PRIMARY KEY (event_id)
			)";
			$wpdb->get_results($sql);

			$sql = "CREATE TABLE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " ( 
				category_id INT(11) NOT NULL AUTO_INCREMENT, 
				category_name VARCHAR(30) NOT NULL COLLATE utf8_general_ci, 
				category_colour VARCHAR(30) NOT NULL COLLATE utf8_general_ci, 
				PRIMARY KEY (category_id) 
			 )";
			$wpdb->get_results($sql);

			$sql = "INSERT INTO " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE .
				" SET category_id=1, category_name='General', category_colour='#000000'";
			$wpdb->get_results($sql);
			
			$this->db_create_meta();

		} else if ($this->current_options['calendar_version'] == '3.8.0') {
			$this->db_create_meta();
		} else if ($this->current_options['calendar_version'] == '3.7.0') {
			$this->db_update_status();
			$this->db_create_meta();
		} else if ($this->current_options['calendar_version'] == '3.5.6') {
			$this->db_update_status();
			$this->db_update_location();
			$this->db_create_meta();
		} else if ($this->current_options['calendar_version'] == '3.5.0') {
			$this->db_update_status();
			$this->db_update_collation();
			$this->db_update_location();
			$this->db_create_meta();
		} else if ($this->current_options['calendar_version'] == '3.4.0') {
			$this->db_update_status();
			$this->db_update_titles();
			$this->db_update_collation();
			$this->db_update_location();
			$this->db_create_meta();
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
		}
		
		// Update the store version
		$this->current_options['calendar_version'] = $this->spiffy_version;
		update_option($this->spiffy_options, $this->current_options);		
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

	function calendar_init_action() {		
		// Localization
		load_plugin_textdomain('spiffy-calendar', false, basename( dirname( __FILE__ ) ) . '/languages' );
		
		$this->check_db();

		// Gutenberg block
		if ( function_exists( 'register_block_type' ) ) {
			// Gutenberg is active, set up our spiffy block
			require_once (plugin_dir_path(__FILE__) . 'includes/block.php');
		}

		// Dashboard stuff follows, quit if not in admin area
		if (!is_admin()) return;
	
		// Shortcode generator
		require_once (plugin_dir_path(__FILE__) . 'includes/shortcode-buttons.php');

		// Check bonus add-ons version
		if (is_plugin_active('spiffy-calendar-addons/spiffy-calendar-addons.php')) {

			/* Make sure the bonus plugin is installed at the minimum version */
			$bonus_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . 'spiffy-calendar-addons/spiffy-calendar-addons.php' );
			$bonus_plugin_version = $bonus_plugin_data['Version'];

			if (version_compare($bonus_plugin_version, SPIFFYCAL_BONUS_MINIMUM_VERSION, '<')) {
				add_action('admin_notices', array($this, 'admin_spiffycal_version_error') );  
				return;
			}
		}	
	}

	/* Admin error messages */
	function admin_spiffycal_version_error() {
		echo '<div id="message" class="error">';
		echo '<p><strong>'. __('Spiffy Calendar Bonus Add-Ons plugin version ', 'spiffy-calendar') . SPIFFYCAL_BONUS_MINIMUM_VERSION . __(' (or above) must be activated for this version of the Spiffy Calendar.', 'spiffy-calendar') . '</strong></p>';
		echo '</div>';
	}
	
	function get_options() {
		
		// Merge default options with the saved values
		$use_options = array(
						'calendar_version' => '1.0.0',	// default to old version to force proper DB updates when needed
						'calendar_style' => '',
						'can_manage_events' => 'edit_posts',
						'category_singular' => __('Category', 'spiffy-calendar'),
						'category_plural' => __('Categories', 'spiffy-calendar'),
						'more_details' => __('More details', 'spiffy-calendar' ). ' &raquo;',
						'display_author' => 'false',
						'limit_author' => 'false',
						'display_detailed' => 'false',
						'display_jump' => 'false',
						'all_day_last' => 'false',
						'display_weeks' => 'false',
						'display_upcoming_days' => 7,
						'upcoming_includes_today' => 'false',
						'enable_categories' => 'false',
						'alphabetic_categories' => 'false',
						'enable_new_window' => 'false',
						'map_new_window' => 'false',
						'link_google_cal' => 'false',
						'enable_expanded_mini_popup' => 'false',
						'responsive_width' => 0,
						'category_bg_color' => false,
						'category_name_display' => false,
						'category_text_color' => '#FFFFFF',
						'grid_list_toggle' => false,
						'category_filter' => false,
						'category_key_above' => false,
						'mini_popup' => 'right',
						'title_label' => __('Event Title', 'spiffy-calendar')
					);
		$saved_options = get_option($this->spiffy_options);
		if (!empty($saved_options)) {
			foreach ($saved_options as $key => $option)
				$use_options[$key] = $option;
		}

		return $use_options;
	}
	
	/*
	** Deal with events posted by a user when that user is about to be deleted
	*/
	function deal_with_delete_user($id)
	{
		global $wpdb;

		// Reassign author appropriately based on the delete request
		switch ( $_REQUEST['delete_option'] ) {
			case 'delete':
				$sql = $wpdb->prepare("UPDATE ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=%d",$id);
				break;
			case 'reassign':
				$sql = $wpdb->prepare("UPDATE ".$wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE." SET event_author=%d WHERE event_author=%d", sanitize_text_field($_REQUEST['reassign_user']), $id);
				break;
		}
		
		// Do the query
		$wpdb->get_results($sql);
	}

	/*
	** Add the calendar front-end styles and scripts
	*/
	function calendar_styles() {
		wp_register_script( 'spiffycal-scripts', plugins_url('js/spiffy_frontend_utility.js', __FILE__), array('jquery'), 
							filemtime( plugin_dir_path(__FILE__) . 'js/spiffy_frontend_utility.js'), false );
		wp_register_style ('spiffycal-styles', plugins_url('styles/default.css', __FILE__), array(), 
							filemtime( plugin_dir_path(__FILE__) . 'styles/default.css'));
		wp_enqueue_style ('spiffycal-styles');
		$this->current_options = $this->get_options();	// update options to account for customizer
	
		if ($this->current_options['calendar_style'] != '') {
			wp_add_inline_style( 'spiffycal-styles', stripslashes($this->current_options['calendar_style']) );
		}
		if ($this->current_options['responsive_width'] > 0) {
			$responsive = '@media screen and ( max-width: ' . absint($this->current_options['responsive_width']) . 'px ) {
.spiffy.calendar-table.bigcal {
	border-collapse:collapse !important;
	border-spacing:0px !important;
}
.spiffy.calendar-table.bigcal tr {
	border:none;
}
.spiffy.calendar-table.bigcal td.day-with-date, 
.spiffy.calendar-table.bigcal td.calendar-date-switcher,
.spiffy.calendar-table.bigcal td.calendar-toggle,
.spiffy.calendar-table.bigcal td.category-key
 {
	width:100%;
	display:block;
	height: auto;
	text-align: left !important;
	padding: 3px !important;
	border-top: solid 1px rgba(255, 255, 255, .2) !important;
	box-sizing: border-box;
}
.spiffy.calendar-table.bigcal td.spiffy-day-1 {
    border-top: none !important;
}
.spiffy.calendar-table.bigcal .weekday-titles, .spiffy.calendar-table.bigcal .day-without-date {
	display: none !important;
}
.calnk-link span.spiffy-popup {
	width: 80%;
}
.spiffy.calendar-table.bigcal .event {
	padding:0 !important;
}
}';
			wp_add_inline_style( 'spiffycal-styles', $responsive );
		}
	}

	/*
	** Add the admin menu
	*/
	function admin_menu() {
		global $wpdb;

		// Set admin as the only one who can use Calendar for security
		$allowed_group = 'manage_options';

		// Use the database to *potentially* override the above if allowed
		$allowed_group = $this->current_options['can_manage_events'];

		// Add the admin panel pages for Calendar. Use permissions pulled from above
		 if (function_exists('add_menu_page')) {
			$this->spiffycal_menu_page = add_menu_page(__('Spiffy Calendar','spiffy-calendar'), __('Spiffy Calendar','spiffy-calendar'),
						$allowed_group, 'spiffy-calendar', array($this, 'admin_menu_output'), $this->spiffy_icon);
			add_action( "load-{$this->spiffycal_menu_page}", array($this, 'admin_menu_options') );
						
			// Add shortcuts to the tabs, first must be duplicate of main
			add_submenu_page( 'spiffy-calendar', __('Spiffy Calendar', 'spiffy-calendar'), __('Manage Events', 'spiffy-calendar'), 
							$allowed_group, 'spiffy-calendar');
			add_submenu_page( 'spiffy-calendar', __('Spiffy Calendar', 'spiffy-calendar'), __('Add Event', 'spiffy-calendar'), 
							$allowed_group, 'admin.php?page=spiffy-calendar&tab=event_edit&action=add' );		
			add_submenu_page( 'spiffy-calendar', __('Spiffy Calendar', 'spiffy-calendar'), esc_html($this->current_options['category_plural']), 
							'manage_options', 'admin.php?page=spiffy-calendar&tab=categories' );		
			add_submenu_page( 'spiffy-calendar', __('Spiffy Calendar', 'spiffy-calendar'), __('Options', 'spiffy-calendar'), 
							'manage_options', 'admin.php?page=spiffy-calendar&tab=options' );		
		 }
	}

	/*
	** Define the options used on admin settings page
	*/
	function admin_menu_options() {	
		$option = 'per_page';
 
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'events';
		if ($current_tab == 'events') {

			$args = array(
				'label' => __('Number of events per page','spiffy-calendar').':',
				'default' => 10,
				'option' => 'spiffy_events_per_page'
			);
			 
			add_screen_option( $option, $args );

			// Declare the list now so the columns are included in screen options
			$this->spiffy_events_admin_list = new Spiffy_Events_List_Table();
		}
	}

	/*
	** Construct the admin settings page
	*/
	function admin_menu_output() {
		//global $options_page;

		// verify user has permission
		$allowed_group = 'manage_options';

		// Use the database to potentially override the above if allowed
		$allowed_group = $this->current_options['can_manage_events'];

		if (!current_user_can($allowed_group))
			wp_die(__('Sorry, but you have no permission to change settings.','spiffy-calendar'));	

		// update the settings for the current tab
		if ( isset($_POST['save_spiffycal']) && ($_POST['save_spiffycal'] == 'true') && 
					check_admin_referer('update_spiffycal_options', 'update_spiffycal_options_nonce')) {

			$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'events';

			if (current_user_can('manage_options')) {
				// admins have access to all tabs
			} else {
				// edit event permission is configurable (default is edit_events)
				if ( ($current_tab != 'events') && ($current_tab != 'event_edit')) {
					wp_die(__('You have no permission to change settings.','spiffy-calendar'));	
				}
			}
			do_action ( 'spiffycal_settings_update_' . $current_tab);
		}

		// Get tabs for the settings page
		$tabs = apply_filters( 'spiffycal_settings_tabs_array', array() );
		
		// proceed with the settings form
		include 'includes/admin/admin-settings.php';
		include 'includes/admin/admin-settings-promo.php';
	}
	
	/*
	** Filter to set our custom admin menu options
	*/
	function admin_menu_set_option($status, $option, $value) {
		return $value;
	}
	
	/*
	** Add the menu shortcuts to the admin toolbar
	*/
	function admin_toolbar ($wp_admin_bar) {

		// Check user permissions
		$allowed_group = $this->current_options['can_manage_events'];
		
		if (!current_user_can($allowed_group)) return;
		
		// WP +New node
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_new_event_node',
			'title' => __('Spiffy Event', 'spiffy-calendar'),
			'parent' => 'new-content',
			'href'  => admin_url('admin.php?page=spiffy-calendar&tab=event_edit&action=add')
			) );
		
		// Our own Spiffy node
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_main_node',
			'title' => __('Spiffy Calendar', 'spiffy-calendar'),
			'href'  => admin_url('admin.php?page=spiffy-calendar&tab=events')
			) );
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_edit_events_node',
			'title' => __('Manage Events', 'spiffy-calendar'),
			'parent' => 'spiffy_main_node',
			'href'  => admin_url('admin.php?page=spiffy-calendar&tab=events')
			) );
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_add_event_node',
			'title' => __('Add Event', 'spiffy-calendar'),
			'parent' => 'spiffy_main_node',
			'href'  => admin_url('admin.php?page=spiffy-calendar&tab=event_edit&action=add')
			) );
		if (current_user_can('manage_options')) {
			$wp_admin_bar->add_node( array(
				'id'    => 'spiffy_categories_node',
				'title' => esc_html($this->current_options['category_plural']),
				'parent' => 'spiffy_main_node',
				'href'  => admin_url('admin.php?page=spiffy-calendar&tab=categories')
				) );
			$wp_admin_bar->add_node( array(
				'id'    => 'spiffy_options_node',
				'title' => __('Options', 'spiffy-calendar'),
				'parent' => 'spiffy_main_node',
				'href'  => admin_url('admin.php?page=spiffy-calendar&tab=options')
				) );
		}
	}
	
	/*
	** Add the default tabs to the settings tab array
	*/
	function settings_tabs_array_default ($settings_tabs ) {

		if (current_user_can('manage_options')) {
			// admins have access to all tabs
			$default_tabs = array (
							'events' =>  __( 'Events', 'spiffy-calendar' ),
							'event_edit' =>  __( 'Add/Edit Event', 'spiffy-calendar' ),
							'categories' => esc_html($this->current_options['category_plural']),
							'options' => __( 'Options', 'spiffy-calendar' ),
							// Bonus tabs will be overwritten when bonus addons installed
							'theme' => __( 'Themes', 'spiffy-calendar' ),
							//'tickets' => __( 'Tickets', 'spiffy-calendar' ),
							'frontend_submit' => __( 'Front End Submit', 'spiffy-calendar' ),
							'custom_fields' => __( 'Custom Fields', 'spiffy-calendar'));							
		} else {
			// edit event permission is configurable (default is edit_events)
			$allowed_group = $this->current_options['can_manage_events'];
			
			if (current_user_can($allowed_group)) {
				$default_tabs = array (
							'events' =>  __( 'Events', 'spiffy-calendar' ),
							'event_edit' =>  __( 'Add/Edit Event', 'spiffy-calendar' ),
							);
			}
		}
		
		return $default_tabs + $settings_tabs;
	}
	
	/*
	** Output the admin settings page for the "Categories" tab
	*/
	function settings_tab_categories() {
		include 'includes/admin/admin-settings-tab-categories.php';
	}
	
	/*
	** Output the admin settings page for the "Events" tab
	*/
	function settings_tab_events() {
		include 'includes/admin/admin-settings-tab-event-list.php';
	}

	/*
	** Output the admin settings page for the "Add Event" tab
	*/
	function settings_tab_event_edit() {
		include 'includes/admin/admin-settings-tab-event-edit.php';
	}

	/*
	** Output the admin settings page for the "Options" tab
	*/
	function settings_tab_options() {
		include 'includes/admin/admin-settings-tab-options.php';
	}

	/*
	** Output the admin settings page for the bonus tabs
	*/
	function settings_tab_bonus() {
		include 'includes/admin/admin-settings-tab-bonus.php';
	}

	/*
	** Save the "Categories" tab updates
	*/
	function settings_update_categories() {
		global $wpdb;
		
		// Look for category delete requests
		foreach($_POST as $key => $value) {
			$k_array = explode("_", $key, 2); 
			if(isset($k_array[0]) && $k_array[0] == "delete") {
				$category_id = sanitize_text_field($k_array[1]);
				$sql = $wpdb->prepare("DELETE FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " WHERE category_id=%d", $category_id);
				$wpdb->get_results($sql);
				$sql = $wpdb->prepare("UPDATE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_category=1 WHERE event_category=%d", $category_id);
				$wpdb->get_results($sql);
				echo "<div class=\"updated\"><p><strong>".esc_html($this->current_options['category_singular']).' '.__('deleted successfully','spiffy-calendar')."</strong></p></div>";
				
				return; // no more work to do
			}
		}
	
		if (isset($_POST['add_category'])) {
			// Adding new category. Check name and color
			if (isset($_POST['category_name']) && ($_POST['category_name'] != '') && isset($_POST['category_colour']) && ($_POST['category_colour'] != '')) {
				$category_name = sanitize_text_field( $_POST['category_name'] );
				
				// Proceed with the save		
				$sql = $wpdb->prepare("INSERT INTO " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " SET category_name='%s', category_colour='%s'",
							$category_name, sanitize_text_field($_POST['category_colour']));
				$wpdb->get_results($sql);
				echo "<div id=\"message\" class=\"updated\"><p>".esc_html($this->current_options['category_singular']).' '.__('added successfully','spiffy-calendar')."</p></div>";
				
				// Clear post parameters to avoid repeat
				$_POST['category_name'] = $_POST['category_colour'] = '';
			} else {
				echo "<div id=\"message\" class=\"error\"><p>".__('Missing name or color, not saved.','spiffy-calendar')."</p></div>";	
			}
		} else if (isset($_POST['update_category'])) {
			if (isset($_POST['category_id']) 
						&& isset($_POST['category_name_edit']) && ($_POST['category_name_edit'] != '')
						&& isset($_POST['category_colour_edit']) && ($_POST['category_colour_edit']) 
					) {
				// Proceed with the save
				$sql = $wpdb->prepare("UPDATE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " SET category_name='%s', category_colour='%s' WHERE category_id=%d", 
								sanitize_text_field($_POST['category_name_edit']), sanitize_text_field($_POST['category_colour_edit']), sanitize_text_field($_POST['category_id']));
				$wpdb->get_results($sql);
				echo "<div class=\"updated\"><p><strong>".esc_html($this->current_options['category_singular']).' '.__('edited successfully','spiffy-calendar')."</strong></p></div>";
			} else {
				echo "<div id=\"message\" class=\"error\"><p>".__('Missing name or color, not updated.','spiffy-calendar')."</p></div>";
				// Restore the edit form
				$_POST['edit_'.sanitize_text_field($_POST['category_id'])] = 'submit';
			}
		}
	}

	/*
	** Add or Update an event
	**
	** Returns an array:
	**	- 'errors' count
	**  - 'messages' string to display to user
	*/
	function add_or_update_event ($event_id, $event_data) {
		global $wpdb, $spiffy_edit_errors, $spiffycal_custom_fields, $current_user;
		
		$result = array();
		$result['errors'] = 0;
		$result['messages'] = '';

		// Perform some validation on the submitted dates - this checks for valid years and months
		$event_data->event_begin = date( 'Y-m-d',strtotime($event_data->event_begin) );
		$event_data->event_end = ($event_data->event_end == '')? $event_data->event_begin : date( 'Y-m-d',strtotime($event_data->event_end) );
		$date_format_one = '/^([0-9]{4})-([0][1-9])-([0-3][0-9])$/';
		$date_format_two = '/^([0-9]{4})-([1][0-2])-([0-3][0-9])$/';
		$start_ok = true;
		$end_ok = true;
		if ( !preg_match($date_format_one,$event_data->event_begin) && !preg_match($date_format_two,$event_data->event_begin) ) {
			$start_ok = false;
			$result['errors']++;
			$spiffy_edit_errors['event_begin'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Start Date must be entered and be in the format YYYY-MM-DD','spiffy-calendar') . '</p>';			
		} 
		if ( !preg_match($date_format_one,$event_data->event_end) && !preg_match($date_format_two,$event_data->event_end) ) {
			$end_ok = false;
			$result['errors']++;
			$spiffy_edit_errors['event_end'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('End Date must be entered and be in the format YYYY-MM-DD','spiffy-calendar') . '</p>';			
		} 
		if ($start_ok && $end_ok) {
			// We know we have a valid year and month and valid integers for days so now we do a final check on the date
			$begin_split = explode('-',$event_data->event_begin);
			$begin_y = $begin_split[0]; 
			$begin_m = $begin_split[1];
			$begin_d = $begin_split[2];
			$end_split = explode('-',$event_data->event_end);
			$end_y = $end_split[0];
			$end_m = $end_split[1];
			$end_d = $end_split[2];
			if (!checkdate($begin_m,$begin_d,$begin_y)) {
				$start_ok = false;
			    $result['errors']++;
				$spiffy_edit_errors['event_begin'] = '<p><strong>' . __('Error','spiffy-calendar'). ':</strong> ' . __('Start Date is invalid. Check for number of days in month and leap year related errors.','spiffy-calendar') . '</p>';
			} 
			if (!checkdate($end_m,$end_d,$end_y)) {
				$end_ok = false;
			    $result['errors']++;
				$spiffy_edit_errors['event_end'] = '<p><strong>' . __('Error','spiffy-calendar'). ':</strong> ' . __('End Date is invalid. Check for number of days in month and leap year related errors.','spiffy-calendar') . '</p>';
			}
			if ($start_ok && $end_ok) {
				// We know we have valid dates, we want to make sure that they are either equal or that 
				// the end date is later than the start date
				if (strtotime($event_data->event_end) < strtotime($event_data->event_begin)) {
					$result['errors']++;
					$spiffy_edit_errors['event_end'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Your event end date must be either after or the same as your event begin date','spiffy-calendar').'</p>';
				}
			 } 
		} 

		// Check for a valid time, or an empty one
		if ($event_data->event_time == '') {
			$event_data->event_all_day = 'T';
		} else {
			$event_data->event_all_day = 'F';
		}
		$event_data->event_time = ($event_data->event_time == '')?'00:00:00':date( 'H:i:00',strtotime($event_data->event_time) );

		// Check for a valid end time, or an empty one
		$event_data->event_end_time = ($event_data->event_end_time == '')?'00:00:00':date( 'H:i:00',strtotime($event_data->event_end_time) );

		// Check to make sure the URL is all right
		if (preg_match('/^(http)(s?)(:)\/\//',$event_data->event_link) || $event_data->event_link == '') {
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_link'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid link URL','spiffy-calendar') . '</p>';
		}
		// The title must be non-blank and sanitized
		if ($event_data->event_title != '') {
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_title'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('The title must not be blank','spiffy-calendar') . '</p>';
		}
		// Run some checks on recurrance
		if (  $event_data->event_recur == 'S' || 
			  $event_data->event_recur == 'W' || 
			  $event_data->event_recur == 'D' || 
			  $event_data->event_recur == 'M' || 
			  $event_data->event_recur == 'Y' || 
			  $event_data->event_recur == 'U'
			) {
			 // Recur code is good. Now check repeat value.
			$event_data->event_repeats = (int)$event_data->event_repeats;
			if ( ($event_data->event_repeats == 0 && $event_data->event_recur == 'S') || 
				 (($event_data->event_repeats >= 0) && ($event_data->event_recur == 'W' || $event_data->event_recur == 'D' || $event_data->event_recur == 'M' || $event_data->event_recur == 'Y' || $event_data->event_recur == 'U'))
				) {
				 // all good
			} else {
				$result['errors']++;
				$spiffy_edit_errors['event_repeats'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('The repetition value must be 0 for single events. It must be greater than or equal to 0 for recurring events. ','spiffy-calendar') . '</p>';
			}
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_recur'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid event recurrence code. ','spiffy-calendar') . '</p>';
		}
		
		$event_data->event_recur_multiplier = (int)$event_data->event_recur_multiplier;
		if ( ($event_data->event_recur_multiplier > 1) && ($event_data->event_recur_multiplier <= 199) && ($event_data->event_recur == 'D') ) {
		} elseif ( ($event_data->event_recur != 'D') && ($event_data->event_recur_multiplier == 1) ) {
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_recur_multiplier'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('The number of custom days in the interval must be greater than 1 and less than 200 for a custom day recur event, or 1 for other recur types.','spiffy-calendar') . '</p>';
		}
		
		// Ensure status is valid
		if (($event_data->event_status == 'P') || ($event_data->event_status == 'D') || ($event_data->event_status == 'R')) {
			// all good
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_status'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid event status. ','spiffy-calendar') . '</p>';
		}
		
		// Ensure show/hide is valid
		if (($event_data->event_hide_events == 'T') || ($event_data->event_hide_events == 'F')) {
			// good
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_hide_events'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid hide event flag. ','spiffy-calendar') . '</p>';			
		}
		if (($event_data->event_show_title == 'T') || ($event_data->event_show_title == 'F')) {
			// good
		} else {
			$result['errors']++;
			$spiffy_edit_errors['event_show_title'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid show title flag. ','spiffy-calendar') . '</p>';			
		}
		
		// Ensure author is valid on new events
		if (isset($_POST['submit_add_event']) && ($event_data->event_author != $current_user->ID)) {
			$result['errors']++;
			$result['messages'] .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid author.','spiffy-calendar') . '</p>';
			$spiffy_edit_errors['event_author'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid author. ','spiffy-calendar') . '</p>';			
		}
						
		// Done checks - attempt to insert or update
		if ( $result['errors'] == 0 ) {

			// Inspection passed, now add/insert
			
			// unlink image if requested
			if ($event_data->event_remove_image === 'true') $event_data->event_image = 0;

			if ( $event_id == '' ) {
				$sql = $wpdb->prepare("INSERT " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_title='%s', event_desc='%s', event_location='%s', event_link_location='%s', event_begin='%s', event_end='%s', event_all_day='%s', event_time='%s', event_end_time='%s', event_recur='%s', event_recur_multiplier='%s', event_repeats='%s', event_hide_events='%s', event_show_title='%s', event_image='%s', event_author=%d, event_category=%d, event_link='%s', event_status='%s'", $event_data->event_title, $event_data->event_desc, $event_data->event_location, $event_data->event_link_location, $event_data->event_begin, $event_data->event_end, $event_data->event_all_day, $event_data->event_time, $event_data->event_end_time, $event_data->event_recur, $event_data->event_recur_multiplier, $event_data->event_repeats, $event_data->event_hide_events, $event_data->event_show_title, $event_data->event_image, $event_data->event_author, $event_data->event_category, $event_data->event_link, $event_data->event_status);
				$db_result = $wpdb->get_results($sql);
				$last_id = $wpdb->insert_id;
			} else {
				$sql = $wpdb->prepare("UPDATE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_title='%s', event_desc='%s', event_location='%s', event_link_location='%s', event_begin='%s', event_end='%s', event_all_day='%s', event_time='%s', event_end_time='%s', event_recur='%s', event_recur_multiplier='%s', event_repeats='%s', event_hide_events='%s', event_show_title='%s', event_image='%s', event_author=%d, event_category=%d, event_link='%s', event_status='%s' WHERE event_id='%s'", $event_data->event_title, $event_data->event_desc, $event_data->event_location, $event_data->event_link_location, $event_data->event_begin, $event_data->event_end, $event_data->event_all_day, $event_data->event_time, $event_data->event_end_time, $event_data->event_recur, $event_data->event_recur_multiplier, $event_data->event_repeats, $event_data->event_hide_events, $event_data->event_show_title, $event_data->event_image, $event_data->event_author, $event_data->event_category, $event_data->event_link, $event_data->event_status, $event_id);
				$db_result = $wpdb->get_results($sql);
				$last_id = $event_id;
			}
			
			if ($wpdb->last_error) {
				$result['errors']++;
				if ( $event_id != '' ) {
					$result['messages'] .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('The event could not be added to the database. This may indicate a problem with your database or the way in which it is configured.','spiffy-calendar') . '</p>';
				} else {
					$result['messages'] .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('The event could not be updated. This may indicate a problem with your database or the way in which it is configured.','spiffy-calendar') . '</p>';
				}
				if (is_admin()) $result['messages'] .= '<p>' . $wpdb->last_error .'</p>';
			} else {
				/* Update custom fields */
				if ( $this->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
					$spiffycal_custom_fields->update($last_id, $event_data);
					if ($wpdb->last_error) {
						$result['errors']++;
						$result['messages'] .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Problems saving custom fields to the database.','spiffy-calendar') . '</p>';
						if (is_admin()) $result['messages'] .= '<p>' . $wpdb->last_error .'</p>';
					}
				}
				
				// remember last accessed event ID
				$_REQUEST['event'] = $last_id;

				// Finally set appropriate message
				if ( $event_id == '' ) {
					// insert ok
					$result['messages'] .= '<p>' . __('Your event has been added.','spiffy-calendar') . '</p>';
				} else {
					// update ok
					$result['messages'] .= '<p>' . __('Your event has been updated successfully.','spiffy-calendar') . '</p>';
				}
			} 

		} else {
			// The form is going to be rejected due to field validation issues
		}		
		
		return $result;
	}

	/*
	** Delete event
	*/
	function delete_event ($event_id) {
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields, $current_user;
		
		$sql = $wpdb->prepare("DELETE FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", $event_id);
		$wpdb->get_results($sql);

		$sql = $wpdb->prepare("SELECT event_id FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", $event_id);
		$result = $wpdb->get_results($sql);	

		if ( empty($result) || empty($result[0]->event_id) ) {
			// the event was deleted, now remove custom fields, if any
			if ( $spiffy_calendar->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
				$spiffycal_custom_fields->delete_custom_fields($event_id);
			}
		} 
		
		return $result;
	}
	
	
	/*
	** Save the "Events" tab updates
	*/
	function settings_update_events() {
		// no submit action possible on this tab, but handle submit from other sources for example front end edit.
		return $this->settings_update_event_edit();
	}

	/*
	** Save the "Add Event" tab updates
	**
	** $spiffy_user_input is used to preserve input in case of an error
	*/
	function settings_update_event_edit() {
		global $current_user, $spiffy_user_input, $spiffy_edit_errors, $wpdb;

		// Note: Delete requests are handled in the event-list-table.php
		if ( !isset($_POST['submit_add_event']) && !isset($_POST['submit_edit_event']) ) {
			return;
		}
	
		$action = !empty($_POST['action']) ? sanitize_text_field ( $_POST['action'] ) : '';
		$event_id = !empty($_POST['event_id']) ? intval(sanitize_text_field ( $_POST['event_id'] )) : '';

		// nonce check for edits
        if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce']) && isset($_POST['submit_edit_event']) ) {

            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'spiffy-edit-security' . $event_id ) )
                wp_die( __('Nonce check failed!','spiffy-calendar') );

        } else if ( isset($_POST['submit_edit_event']) ) {
			wp_die ( __('Nonce missing','spiffy-calendar') );
		}
		
		// First some quick cleaning up 
		$edit = $create = $save = false;

		
		// Collect and clean up user input
		$spiffy_user_input = $this->sanitize_event_post();

		if ( ($action == 'submit_edit_event') && (empty($event_id)) ) {
			// Missing event id for update?
			?>
				<div class="error error-message"><p><strong><?php _e('Failure','spiffy-calendar'); ?>:</strong> <?php _e("You can't update an event if you haven't submitted an event id",'spiffy-calendar'); ?></p></div>
			<?php	
			return 1;
		} else {

			// Make sure user has permission for edit
			if ( ($this->current_options['limit_author'] == 'true') && !current_user_can('manage_options') && isset($_POST['submit_edit_event'])) {
				$data = $wpdb->get_results("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id='" . 
								esc_sql($event_id) . "' LIMIT 1");
				if ( empty($data) ) {
					echo "<div class=\"error\"><p>".__("An event with that ID couldn't be found",'spiffy-calendar')."</p></div>";
					wp_die ( __('Invalid request','spiffy-calendar') );
				} else {
					// Check this user is allowed to edit this event
					if ($data[0]->event_author != $current_user->ID) {
						wp_die( __('You do not have sufficient permissions to access this page.','spiffy-calendar') );	
					}
					// Check for spoofed author input
					if ($spiffy_user_input->event_author != $current_user->ID) {
						wp_die( __('Invalid author in form input.','spiffy-calendar') );	
					}
				}
			}

			// Deal with adding/updating an event 
			$result = $this->add_or_update_event ($event_id, $spiffy_user_input);
				
			// Display results
			if ( $result['errors'] == 0 ) {
				echo '<div class="updated spiffy-updated">' . $result['messages'] . '</div>';
				unset($GLOBALS['spiffy_user_input']); // clear user input ready for next event
				unset($spiffy_user_input);
				unset($spiffy_edit_errors);
				return 0;
			} else {
				echo '<div class="error spiffy-error error-message">' . 
						'<p>' . __('Event not saved due to errors', 'spiffy-calendar') . '</p>' .
						$result['messages'] . 
					 '</div>';
				// If there are any errors, keep the user input for a re-try
				return 1;
			}
		}		
	}

	/*
	** Sanitize posted user input for an event
	*/
	function sanitize_event_post () {
		global $current_user, $spiffycal_custom_fields;
		
		$user_input = new stdClass();

		$user_input->event_title = !empty($_POST['event_title']) ? sanitize_text_field ( $_POST['event_title'] ) : '';
		$user_input->event_desc = !empty($_POST['event_desc']) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['event_desc'] ))) : ''; // preserve new lines
		$user_input->event_location = !empty($_POST['event_location']) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['event_location'] ))) : ''; // preserve new lines
		$user_input->event_link_location = !empty($_POST['link_location'])? 'T' : 'F';
		$user_input->event_begin = !empty($_POST['event_begin']) ? sanitize_text_field ( $_POST['event_begin'] ) : '';
		$user_input->event_end = !empty($_POST['event_end']) ? sanitize_text_field ( $_POST['event_end'] ) : '';
		$user_input->event_time = !empty($_POST['event_time']) ? sanitize_text_field ( $_POST['event_time'] ) : '';
		$user_input->event_end_time = !empty($_POST['event_end_time']) ? sanitize_text_field ( $_POST['event_end_time'] ) : '';
		$user_input->event_recur = !empty($_POST['event_recur']) ? sanitize_text_field ( $_POST['event_recur'] ) : 'S';
		$user_input->event_recur_multiplier = !empty($_POST['event_recur_multiplier']) ? sanitize_text_field ( $_POST['event_recur_multiplier'] ) : 1;
		$user_input->event_repeats = !empty($_POST['event_repeats']) ? sanitize_text_field ( $_POST['event_repeats'] ) : 0;
		$user_input->event_hide_events = !empty($_POST['event_hide_events']) ? sanitize_text_field ( $_POST['event_hide_events'] ) : '';
		$user_input->event_show_title = !empty($_POST['event_show_title']) ? sanitize_text_field ( $_POST['event_show_title'] ) : '';
		$user_input->event_image = !empty($_POST['event_image']) ? sanitize_text_field ( $_POST['event_image'] ) : 0;
		$user_input->event_remove_image = !empty($_POST['event_remove_image']) ? sanitize_text_field ( $_POST['event_remove_image'] ) : 'false';
		$user_input->event_category = !empty($_POST['event_category']) ? sanitize_text_field ( $_POST['event_category'] ) : '';
		$user_input->event_author = !empty($_POST['event_author']) ? sanitize_text_field ( $_POST['event_author'] ) : $current_user->ID;
		$user_input->event_link = !empty($_POST['event_link']) ? wp_filter_nohtml_kses ( $_POST['event_link'] ) : '';
		$user_input->event_status = !empty($_POST['event_status']) ? sanitize_text_field ( $_POST['event_status'] ) : 'P';	

		// Sanitize custom fields, if any
		if ( $this->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
			$spiffycal_custom_fields->sanitize($user_input);
		}
		
		return $user_input;
	}
	/*
	** Save the "Options" tab updates
	*/
	function settings_update_options() {

		if ($_POST['permissions'] == 'subscriber') { $this->current_options['can_manage_events'] = 'read'; }
		else if ($_POST['permissions'] == 'contributor') { $this->current_options['can_manage_events'] = 'edit_posts'; }
		else if ($_POST['permissions'] == 'author') { $this->current_options['can_manage_events'] = 'publish_posts'; }
		else if ($_POST['permissions'] == 'editor') { $this->current_options['can_manage_events'] = 'moderate_comments'; }
		else if ($_POST['permissions'] == 'admin') { $this->current_options['can_manage_events'] = 'manage_options'; }
		else { $this->current_options['can_manage_events'] = 'manage_options'; }

		$this->current_options['calendar_style'] = sanitize_textarea_field($_POST['style']);
		$this->current_options['display_upcoming_days'] = absint($_POST['display_upcoming_days']);

		if (isset($_POST['upcoming_includes_today'])) {
			$this->current_options['upcoming_includes_today'] = 'true';
		} else {
			$this->current_options['upcoming_includes_today'] = 'false';			
		}
		if (isset($_POST['display_author'])) {
			$this->current_options['display_author'] = 'true';
		} else {
			$this->current_options['display_author'] = 'false';
		}

		if (isset($_POST['limit_author'])) {
			$this->current_options['limit_author'] = 'true';
		} else {
			$this->current_options['limit_author'] = 'false';
		}

		if (isset($_POST['display_detailed'])) {
			$this->current_options['display_detailed'] = 'true';
		} else {
			$this->current_options['display_detailed'] = 'false';
		}

		if (isset($_POST['display_jump'])) {
			$this->current_options['display_jump'] = 'true';
		} else {
			$this->current_options['display_jump'] = 'false';
		}

		if (isset($_POST['grid_list_toggle'])) {
			$this->current_options['grid_list_toggle'] = true;
		} else {
			$this->current_options['grid_list_toggle'] = false;
		}
		
		if (isset($_POST['category_filter'])) {
			$this->current_options['category_filter'] = true;
		} else {
			$this->current_options['category_filter'] = false;
		}

		if (isset($_POST['category_key_above'])) {
			$this->current_options['category_key_above'] = true;
		} else {
			$this->current_options['category_key_above'] = false;
		}
		
		if (isset($_POST['all_day_last'])) {
			$this->current_options['all_day_last'] = 'true';
		} else {
			$this->current_options['all_day_last'] = 'false';
		}

		if (isset($_POST['display_weeks'])) {
			$this->current_options['display_weeks'] = 'true';
		} else {
			$this->current_options['display_weeks'] = 'false';
		}

		if (isset($_POST['enable_categories'])) {
			$this->current_options['enable_categories'] = 'true';
		} else {
			$this->current_options['enable_categories'] = 'false';
		}

		$this->current_options['category_singular'] = sanitize_text_field($_POST['category_singular']);
		$this->current_options['category_plural'] = sanitize_text_field($_POST['category_plural']);
		
		if (isset($_POST['alphabetic_categories'])) {
			$this->current_options['alphabetic_categories'] = 'true';
		} else {
			$this->current_options['alphabetic_categories'] = 'false';
		}

		if (isset($_POST['enable_new_window'])) {
			$this->current_options['enable_new_window'] = 'true';
		} else {
			$this->current_options['enable_new_window'] = 'false';
		}

		if (isset($_POST['map_new_window'])) {
			$this->current_options['map_new_window'] = 'true';
		} else {
			$this->current_options['map_new_window'] = 'false';
		}

		if (isset($_POST['link_google_cal'])) {
			$this->current_options['link_google_cal'] = 'true';
		} else {
			$this->current_options['link_google_cal'] = 'false';
		}

		$this->current_options['more_details'] = sanitize_text_field($_POST['more_details']);

		$this->current_options['title_label'] = sanitize_text_field($_POST['title_label']);

		if (isset($_POST['enable_expanded_mini_popup'])) {
			$this->current_options['enable_expanded_mini_popup'] = 'true';
		} else {
			$this->current_options['enable_expanded_mini_popup'] = 'false';
		}
		
		$this->current_options['mini_popup'] = sanitize_text_field($_POST['mini_popup']);
		
		$this->current_options['responsive_width'] = abs((int)$_POST['responsive_width']);

		if (isset($_POST['category_bg_color'])) {
			$this->current_options['category_bg_color'] = true;
		} else {
			$this->current_options['category_bg_color'] = false;
		}

		if (isset($_POST['category_name_display'])) {
			$this->current_options['category_name_display'] = true;
		} else {
			$this->current_options['category_name_display'] = false;
		}

		$this->current_options['category_text_color'] = sanitize_text_field($_POST['category_text_color']);
		
		// Check to see if we are removing custom styles
		if (isset($_POST['reset_styles'])) {
			if ($_POST['reset_styles'] == 'on') {
				$this->current_options['calendar_style'] = '';
			}
		}
		update_option($this->spiffy_options, $this->current_options);

		echo "<div class=\"updated\"><p><strong>".__('Settings saved','spiffy-calendar').".</strong></p></div>";		
	}
	
	// Function to add the javascript to the admin pages
	function admin_scripts($hook)
	{ 
		if ( $hook == $this->spiffycal_menu_page ) {			
			// Date picker script
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );  
			wp_add_inline_style( 'jquery-ui', '.dp-highlight a { background: orange !important; }' );
	
			// Media api
			wp_enqueue_media();
			
			// Add the color picker css file       
			wp_enqueue_style( 'wp-color-picker' ); 
			
			// Spiffy Calendar utility scripts
			wp_enqueue_script( 'spiffy_calendar_utilites', plugins_url('js/spiffy_utility.js', __FILE__), array('wp-color-picker', 'jquery'), filemtime( plugin_dir_path(__FILE__) . 'js/spiffy_utility.js'), true );
			// Localize the admin script messages
			$translation_array = array(
				'areyousure' => __( 'Are you sure you want to leave this page? The changes you made will be lost.', 'spiffy-calendar' )
			);
			wp_localize_script( 'spiffy_calendar_utilites', 'object_name', $translation_array );
		} 
	}

	// Front end scripts and styles are needed
	function enqueue_frontend_scripts_and_styles() {
		wp_enqueue_script('spiffycal-scripts');
		wp_enqueue_style( 'dashicons' );
		//wp_enqueue_style ('spiffycal-styles');
		
		// Make sure options are up to date to account for customizer use with block themes
		$this->current_options = $this->get_options();	// update options to account for customizer
		
	}
	
	// Calendar shortcode
	function calendar_insert($attr)
	{
		global $spiffy_calendar_views;
		
		$this->enqueue_frontend_scripts_and_styles();
		
		/*
		** Standard shortcode defaults that we support here	
		*/
		extract(shortcode_atts(array(
				'title' => '',
				'cat_list'	=> '',
		  ), $attr));

		$cal_output = apply_filters ('spiffycal_calendar', $spiffy_calendar_views->calendar($cat_list, $title), $attr);
		return $cal_output;
	}

	// Weekly calendar shortcode
	function weekly_insert($attr) {
		global $spiffy_calendar_views;
		
		$this->enqueue_frontend_scripts_and_styles();

		/*
		** Standard shortcode defaults that we support here	
		*/
		extract(shortcode_atts(array(
				'title' => '',
				'cat_list'	=> '',
		  ), $attr));

		$cal_output = $spiffy_calendar_views->weekly($cat_list, $title);
		return $cal_output;
	}

	// Mini calendar shortcode
	function minical_insert($attr) {
		global $spiffy_calendar_views;
		
		$this->enqueue_frontend_scripts_and_styles();

		/*
		** Standard shortcode defaults that we support here	
		*/
		extract(shortcode_atts(array(
				'title' => '',
				'cat_list'	=> '',
		  ), $attr));

		$cal_output = $spiffy_calendar_views->minical($cat_list, $title);
		return $cal_output;
	}

	// Upcoming events shortcode
	function upcoming_insert($attr) {
		global $spiffy_calendar_views;
		
		$this->enqueue_frontend_scripts_and_styles();

		/*
		** Standard shortcode defaults that we support here	
		*/
		extract(shortcode_atts(array(
				'title' 	=> '',
				'cat_list'	=> '',
				'limit'		=> '',
				'style'		=> '',
				'none_found' => '',
				'num_columns'	=> '',
		  ), $attr));

		$cal_output = '';
		if ($title != '') {
			$cal_output .= '<h2>' . esc_html($title) . '</h2>';
		}		
		$cal_output .= '<div class="spiffy page-upcoming-events spiffy-list-' . esc_html($style) . '">'
						. $spiffy_calendar_views->upcoming_events($cat_list, $limit, esc_html($style), esc_html($none_found), $title, esc_html($num_columns))
						. '</div>';
		return $cal_output;
	}

	// Today's events shortcode
	function todays_insert($attr) {
		global $spiffy_calendar_views;
		
		$this->enqueue_frontend_scripts_and_styles();

		/*
		** Standard shortcode defaults that we support here	
		*/
		extract(shortcode_atts(array(
				'title' 	=> '',
				'cat_list'	=> '',
				'limit'		=> '',
				'style'		=> '',
				'show_date' => 'false',
				'none_found' => '',
				'num_columns'	=> '',
		  ), $attr));

		$cal_output = '';
		if ($title != '') {
			$cal_output .= '<h2>' . esc_html($title) . '</h2>';
		}		
		$cal_output .= '<div class="spiffy page-todays-events spiffy-list-' . esc_html($style) . '">'
							. $spiffy_calendar_views->todays_events($cat_list, $limit, esc_html($style), $show_date, esc_html($none_found), $title, esc_html($num_columns))
							. '</div>';
		return $cal_output;
	}

	/*
	** Mail Poet newsletter support

	Inline styles: 
		.spiffy ul {
			list-style-type: none;
			padding: 0;
		}

		span replaced as p
		
		.spiffy-upcoming-date {
			font-size: 1.5em;
			margin-bottom: 1.5em;
			display: block;
			font-weight: bold;
		}

		li.spiffy-event-details.spiffy-Expanded {
			margin-left: 0;
			margin-right: 0;
			margin-bottom: 1.5em;
		}

		.spiffy-title {
			font-size: 1.5em;
			margin-bottom: .3em;
		}

		.spiffy-link {
			font-size: 1.3em;
		}
	*/
	function mailpoet_shortcodes_custom_filter( $tag_value , $user_id) {
		
		if (substr($tag_value, 0, 20) == 'spiffy-upcoming-list') {
			$code = do_shortcode('['.$tag_value.' style="Expanded"]'); 
			
			// insert inline styles
			$code = str_replace('<ul', 
								'<ul style="list-style-type:none; padding:0;"', 
								$code);
			$code = str_replace('<span', 
								'<p', 
								$code);
			$code = str_replace('</span', 
								'</p', 
								$code);			
			$code = str_replace('class="spiffy-upcoming-date"', 
								'style="font-size: 1.5em; margin-bottom: 1.5em; display: block; font-weight: bold;"', 
								$code);
			$code = str_replace('class="spiffy-event-details spiffy-Expanded"',
								'style="margin-left: 0; margin-right: 0; margin-bottom: 1.5em;"',
								$code);
			$code = str_replace('class="spiffy-title"',
								'style="font-size: 1.5em; margin-bottom: .3em;"',
								$code);
			$code = str_replace('class="spiffy-link"',
								'style="font-size: 1.3em"',
								$code);
			return '<span class="spiffy-newsletter">' . $code . '</span>';

		}
	}

	function mailpoet_v3_shortcodes_custom_filter($shortcode, $newsletter, $subscriber, $queue, $newsletter_body) {
		// always return the shortcode if it doesn't match your own!
		if (substr($shortcode, 0, 28) != '[custom:spiffy-upcoming-list') return $shortcode; 

		$tag_value = str_replace ( 'custom:', '', $shortcode);
		$tag_value = str_replace ( ']', ' style="Expanded"]', $tag_value);
		$code = do_shortcode($tag_value); 
		
		// insert inline styles
		$code = str_replace('<ul', 
							'<ul style="list-style-type:none; padding:0;"', 
							$code);
		$code = str_replace('<span', 
							'<p', 
							$code);
		$code = str_replace('</span', 
							'</p', 
							$code);			
		$code = str_replace('class="spiffy-upcoming-date"', 
							'style="font-size: 1.5em; margin-bottom: 1.5em; display: block; font-weight: bold;"', 
							$code);
		$code = str_replace('class="spiffy-event-details spiffy-Expanded"',
							'style="margin-left: 0; margin-right: 0; margin-bottom: 1.5em;"',
							$code);
		$code = str_replace('class="spiffy-title"',
							'style="font-size: 1.5em; margin-bottom: .3em;"',
							$code);
		$code = str_replace('class="spiffy-link"',
							'style="font-size: 1.3em"',
							$code);
		return '<span class="spiffy-newsletter">' . $code . '</span>';
	}

	/*
	** Functions that have moved to views.php, define here for backward compatibility with bonus addons
	*/
	function grab_events($y1,$m1,$d1,$y2,$m2,$d2,$cat_list = '') {
		global $spiffy_calendar_views;
		return $spiffy_calendar_views->grab_events($y1,$m1,$d1,$y2,$m2,$d2,$cat_list);
	}
	
	function filter_events(array &$events,$y,$m,$d)	{
		global $spiffy_calendar_views;
		return $spiffy_calendar_views->filter_events($events,$y,$m,$d);
	}

} // end of class definition

} // end of "if !class exists"

if (class_exists("Spiffy_Calendar")) {
	$spiffy_calendar = new Spiffy_Calendar();
}
?>