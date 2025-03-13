<?php
/*
Plugin Name: Spiffy Calendar
Plugin URI:  http://www.spiffyplugins.ca/spiffycalendar
Description: A full featured, simple to use Spiffy Calendar plugin for WordPress that allows you to manage and display your events and appointments.
Version:     5.0.5
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


define ('SPIFFY_FILE_URL', __FILE__);

// Cleanup after an update
require_once (plugin_dir_path(__FILE__) . 'includes/admin/update-cleanup.php');

// Needed for version checks
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


// Widgets
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-featured-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-minical-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-today-widget.php');
require_once (plugin_dir_path(__FILE__) . 'includes/spiffy-upcoming-widget.php');

// Custom post types support
require_once (plugin_dir_path(__FILE__) . 'includes/admin/custom-posts.php');
require_once (plugin_dir_path(__FILE__) . 'includes/admin/meta-boxes.php');

// Calendar display
require_once (plugin_dir_path(__FILE__) . 'includes/views.php');

// Use to catch php warnings stack trace
// set_error_handler(function($severity, $message, $file, $line) {
    // if (error_reporting() & $severity) {
        // throw new ErrorException($message, 0, $severity, $file, $line);
    // }
// });


if (!class_exists("Spiffy_Calendar")) {
Class Spiffy_Calendar
{
	public $spiffy_options = 'spiffy_calendar_options';
	private $spiffy_bonus_minimum_version = "4.00";
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

		// add_action('spiffycal_settings_tab_tools', array($this, 'settings_tab_tools'));
		add_action('spiffycal_settings_tab_theme', array($this, 'settings_tab_bonus'));
		add_action('spiffycal_settings_tab_frontend_submit', array($this, 'settings_tab_bonus'));
		add_action('spiffycal_settings_tab_custom_fields', array($this, 'settings_tab_bonus'));

		add_action('spiffycal_settings_tab_settings', array($this, 'settings_tab_settings'));
		add_action('spiffycal_settings_update_settings', array($this, 'settings_update_settings'));

		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

		// Frontend stuff
		add_action('wp_enqueue_scripts', array($this, 'calendar_styles'));
		add_shortcode('spiffy-calendar', array($this, 'calendar_insert'));	
		add_shortcode('spiffy-minical', array($this, 'minical_insert'));	
		add_shortcode('spiffy-upcoming-list', array($this, 'upcoming_insert'));
		add_shortcode('spiffy-todays-list', array($this, 'todays_insert'));
		add_shortcode('spiffy-week', array($this, 'weekly_insert'));
		
		// Mailpoet shortcode support
		add_filter('wysija_shortcodes', array($this, 'mailpoet_shortcodes_custom_filter'), 10, 2);	// Version 2
		add_filter('mailpoet_newsletter_shortcode', array($this, 'mailpoet_v3_shortcodes_custom_filter'), 10, 5);	// Version 3


		// Admin screen option handling
		add_filter('set-screen-option', array($this, 'admin_menu_set_option'), 10, 3);
		
		// Get a local copy of our options
		$this->current_options = $this->get_options();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );	
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}
	
	/*
	** Actions to take when plugin first activated and deactivated
	*/
	function activate() {
	}
	
	function deactivate () {
		
	}
	
	/*
	** Check if bonus addons are running
	*/
	function bonus_addons_active() {
		return is_plugin_active( 'spiffy-calendar-addons/spiffy-calendar-addons.php' );
	}
	
	function calendar_init_action() {
		global $spiffycal_update_cleanup;
		
		// Localization
		load_plugin_textdomain('spiffy-calendar', false, basename( dirname( __FILE__ ) ) . '/languages' );
			
		$spiffycal_update_cleanup->check_cleanup();

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

			if (version_compare($bonus_plugin_version, $this->spiffy_bonus_minimum_version, '<')) {
				add_action('admin_notices', array($this, 'admin_spiffycal_version_error') );
				deactivate_plugins (WP_PLUGIN_DIR . '/' . 'spiffy-calendar-addons/spiffy-calendar-addons.php');
				return;
			}
		}	
	}

	/* Admin error messages */
	function admin_spiffycal_version_error() {
		echo '<div id="message" class="notice notice-error is-dismissible">';
		echo '<p><strong>'. __('Spiffy Calendar Bonus Add-Ons plugin version ', 'spiffy-calendar') . $this->spiffy_bonus_minimum_version . __(' (or above) required. Please update to the latest version and reactivate the bonus addons.', 'spiffy-calendar') . '</strong></p>';
		echo '</div>';
	}
	
	function get_options() {
		global $spiffycal_custom_posts;
		
		// Merge default options with the saved values
		$use_options = array(
						'calendar_version' => '1.0.0',	// default to old version to force proper DB updates when needed
						'calendar_style' => '',
						'can_manage_events' => 'edit_posts',
						'category_singular' => '',
						'category_plural' => '',	
						'more_details' => '',
						'display_author' => 'false',
						'limit_author' => 'false',
						'display_detailed' => 'false',
						'display_jump' => 'false',
						'all_day_last' => 'false',
						'display_weeks' => 'false',
						'display_upcoming_days' => 7,
						'upcoming_includes_today' => 'false',
						'enable_categories' => 'false',
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
						'exclude_from_search' => false,
						'max_event_query' => 500,
						'title_label' => ''
					);
		$saved_options = get_option($this->spiffy_options);
		if (!empty($saved_options)) {
			foreach ($saved_options as $key => $option)
				$use_options[$key] = $option;
		}
		
		return $use_options;
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
		wp_register_style ('spiffycal_editor_styles', plugins_url('styles/editor.css', __FILE__), array(), 
							filemtime( plugin_dir_path(__FILE__) . 'styles/editor.css'));
		wp_enqueue_style ('spiffycal_editor_styles');	// needed for front end edit
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

		// Add the admin panel pages for Calendar. Use permissions pulled from above
		 if (function_exists('add_menu_page')) {
			// Add shortcuts to the tabs
			add_submenu_page( 'edit.php?post_type=spiffy_event', __('Spiffy Calendar', 'spiffy-calendar'), __('Settings', 'spiffy-calendar'), 
							'manage_options', 'settings', array($this, 'admin_menu_output') );		
			// add_submenu_page( 'edit.php?post_type=spiffy_event', __('Spiffy Calendar', 'spiffy-calendar'), __('Tools', 'spiffy-calendar'), 
							// 'manage_options', 'tools', array($this, 'admin_menu_output') );		
			add_submenu_page( 'edit.php?post_type=spiffy_event', __('Spiffy Calendar', 'spiffy-calendar'), __('Themes', 'spiffy-calendar'), 
							'manage_options', 'theme', array($this, 'admin_menu_output') );		
			add_submenu_page( 'edit.php?post_type=spiffy_event', __('Spiffy Calendar', 'spiffy-calendar'), __('Front End Submit', 'spiffy-calendar'), 
							'manage_options', 'frontend_submit', array($this, 'admin_menu_output') );
			add_submenu_page( 'edit.php?post_type=spiffy_event', __('Spiffy Calendar', 'spiffy-calendar'), __('Custom Fields', 'spiffy-calendar'), 
							'manage_options', 'custom_fields', array($this, 'admin_menu_output') );
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

			$current_tab = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : 'settings';

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
		
		$cat_name = ($this->current_options['category_plural'] == '') ? __('Categories', 'spiffy-calendar') : esc_html($this->current_options['category_plural']);
		
		// Our own Spiffy node
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_main_node',
			'title' => __('Spiffy Calendar', 'spiffy-calendar'),
			'href'  => admin_url('edit.php?post_type=spiffy_event') 
			) );
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_edit_events_node',
			'title' => __('Manage Events', 'spiffy-calendar'),
			'parent' => 'spiffy_main_node',
			'href'  => admin_url('edit.php?post_type=spiffy_event')
			) );
		$wp_admin_bar->add_node( array(
			'id'    => 'spiffy_add_event_node',
			'title' => __('Add Event', 'spiffy-calendar'),
			'parent' => 'spiffy_main_node',
			'href'  => admin_url('post-new.php?post_type=spiffy_event') 
			) );
		if (current_user_can('manage_options')) {
			$wp_admin_bar->add_node( array(
				'id'    => 'spiffy_categories_node',
				'title' => $cat_name,
				'parent' => 'spiffy_main_node',
				'href'  => admin_url('edit-tags.php?taxonomy=spiffy_categories&post_type=spiffy_event')
				) );
		}
			$wp_admin_bar->add_node( array(
				'id'    => 'spiffy_settings_node',
				'title' => __('Settings', 'spiffy-calendar'),
				'parent' => 'spiffy_main_node',
				'href'  => admin_url('edit.php?post_type=spiffy_event&page=settings')
				) );
	}

	/*
	** Add the default tabs to the settings tab array
	*/
	function settings_tabs_array_default ($settings_tabs ) {

		if (current_user_can('manage_options')) {
			// admins have access to all tabs
			$default_tabs = array (
							'settings' => __( 'Settings', 'spiffy-calendar' ),
							// 'tools' => __( 'Tools', 'spiffy-calendar' ),
							// Bonus tabs will be overwritten when bonus addons installed
							'theme' => __( 'Themes', 'spiffy-calendar' ),
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
	** Output the admin settings page for the "Settings" tab
	*/
	function settings_tab_settings() {
		include 'includes/admin/admin-settings-tab-settings.php';
	}

	/*
	** Output the admin settings page for the "Tools" tab
	*/
	// function settings_tab_tools() {
		// include 'includes/admin/admin-settings-tab-tools.php';
	// }

	/*
	** Output the admin settings page for the bonus tabs
	*/
	function settings_tab_bonus() {
		include 'includes/admin/admin-settings-tab-bonus.php';
	}

	/*
	** Add or Update an event
	**
	** Called by front end submit, import, db conversion
	**
	** $event_id = '' for insert, otherwise must be an existing event
	** $event_data = fields from an edit form or similar. Data must be sanitized before calling this. See sanitize_event_post()
	**
	** Returns an array:
	**	- 'errors' count
	**  - 'messages' string to display to user
	*/
	function add_or_update_event ($event_id, $event_data) {
		global $spiffycal_meta_boxes;
		
		$orig_id = $event_id;
		// print_r($event_data);
		
		// Determine post publish status
		if ( isset($event_data->event_status)) {
			$post_status = $event_data->event_status;
		} else {
			$post_status = 'Draft';
		}
		// Fix old post status
		if ($post_status == 'P') {
			$post_status = 'Publish';
		} else if ($post_status == 'D') {
			$post_status = 'Draft';
		} else if ($post_status == 'R') {
			$post_status = 'Pending';
		} 

		$post = array(
					'post_title'	=> wp_strip_all_tags($event_data->event_title),
					'post_content'	=> $event_data->event_desc,
					'post_status'	=> $post_status,
					'post_name'		=> sanitize_title (wp_strip_all_tags($event_data->event_title)),
					'post_type'		=> "spiffy_event",
					'post_author'	=> $event_data->event_author,
					);
		
		// Create post if necessary
		if ($event_id == '') {
			// Add new event
			$event_id = wp_insert_post( $post );
			if ($event_data->event_category != '') {
				$status = wp_set_object_terms($event_id, $event_data->event_category, 'spiffy_categories');
			}
			if ($event_data->event_image != 0) {
				set_post_thumbnail( $event_id, $event_data->event_image );
			}
		} else {
			// update existing Event
			if ( 'spiffy_event' != get_post_type( $event_id ) ) {
				$result = array();
				$result['messages'] .= '<p>' . __('Invalid post type.','spiffy-calendar') . '</p>';
				$result['errors']++;
				return $result;
			}
			if ( !current_user_can( 'edit_spiffycal', $event_id ) ) {
				$result = array();
				$result['messages'] .= '<p>' . __('You do not have sufficient permissions to edit this event','spiffy-calendar') . '</p>';
				$result['errors']++;
				return $result;
			}
			$post['ID'] = $event_id;
			$event_id = wp_update_post( $post );
			if ($event_data->event_category != '') {
				$status = wp_set_object_terms($event_id, $event_data->event_category, 'spiffy_categories');
			}
			if ($event_data->event_image != 0) {
				set_post_thumbnail( $event_id, $event_data->event_image );
			} else {
				delete_post_thumbnail( $event_id );
			}
		}
	
		// Update the post meta
		$result = $spiffycal_meta_boxes->meta_boxes_update ($event_id, $event_data);

		// remember last accessed event ID
		$_REQUEST['event'] = $event_id;

		// Set appropriate message
		if ( $orig_id == '' ) {
			// insert ok
			$result['messages'] .= '<p>' . __('Your event has been added.','spiffy-calendar') . '</p>';
		} else {
			// update ok
			$result['messages'] .= '<p>' . __('Your event has been updated successfully.','spiffy-calendar') . '</p>';
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
		global $current_user, $spiffy_user_input, $spiffy_edit_errors;

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

		// Check for spoofed author input
		if ($spiffy_user_input->event_author != $current_user->ID) {
			wp_die( __('Invalid author in form input.','spiffy-calendar') );	
		}

		if ( ($action == 'submit_edit_event') && (empty($event_id)) ) {
			// Missing event id for update?
			?>
				<div class="error error-message"><p><strong><?php _e('Failure','spiffy-calendar'); ?>:</strong> <?php _e("Invalid event id",'spiffy-calendar'); ?></p></div>
			<?php	
			return 1;
		} else {

			// Make sure user has permission for edit
			if ( ($this->current_options['limit_author'] == 'true') && !current_user_can('manage_options') && isset($_POST['submit_edit_event'])) {
				$data = get_post (esc_sql($event_id));
				// wp_die ("add or update breakpoint");
				if ( empty($data) ) {
					echo "<div class=\"error\"><p>".__("An event with that ID couldn't be found",'spiffy-calendar')."</p></div>";
					wp_die ( __('Invalid request','spiffy-calendar') );
				} else {
					// Check this user is allowed to edit this event
					if ($data->post_author != $current_user->ID) {
						wp_die( __('You do not have sufficient permissions to access this page.','spiffy-calendar') );	
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
	**
	** Called by front end submit, db conversion
	*/
	function sanitize_event_post () {
		global $spiffycal_meta_boxes;
		
		return $spiffycal_meta_boxes->meta_boxes_sanitize ();
	}
	/*
	** Save the "Settings" tab updates
	*/
	function settings_update_settings() {
		global $spiffycal_custom_posts;
		
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
		
		if (isset($_POST['exclude_from_search'])) {
			$this->current_options['exclude_from_search'] = true;
		} else {
			$this->current_options['exclude_from_search'] = false;
		}
			
		$this->current_options['max_event_query'] = (int) (sanitize_text_field($_POST['max_event_query']) );

		// Check to see if we are removing custom styles
		if (isset($_POST['reset_styles'])) {
			if ($_POST['reset_styles'] == 'on') {
				$this->current_options['calendar_style'] = '';
			}
		}
		update_option($this->spiffy_options, $this->current_options);

		// Update role capabilities of those who can or cannot manage events
		if ($spiffycal_custom_posts != null) {
			$spiffycal_custom_posts->adjust_roles ($this->current_options['can_manage_events'], $this->current_options['limit_author']);
		}

		echo "<div class=\"updated\"><p><strong>".__('Settings saved','spiffy-calendar').".</strong></p></div>";		
	}
	
	// Function to add the javascript to the admin pages
	function admin_scripts($hook) { 
		$screen = get_current_screen();
		if ( $screen->post_type === 'spiffy_event') {				
		// if ( ( ($hook == 'post.php') || ($hook == 'post-new.php') ) && ($screen->post_type === 'spiffy_event') ) {	

			// Date picker script
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );  
			wp_add_inline_style( 'jquery-ui', '.dp-highlight a { background: #ffa500a6 !important; }' );
	
			// Media api
			wp_enqueue_media();
			
			// Add the color picker css file       
			wp_enqueue_style( 'wp-color-picker' ); 
			
			// Spiffy Calendar utility scripts and styles
			wp_enqueue_script( 'spiffy_calendar_utilites', plugins_url('js/spiffy_utility.js', __FILE__), array('wp-color-picker', 'jquery'), filemtime( plugin_dir_path(__FILE__) . 'js/spiffy_utility.js'), true );
			// Localize the admin script messages
			$translation_array = array(
				'areyousure' => __( 'Are you sure you want to leave this page? The changes you made will be lost.', 'spiffy-calendar' )
			);
			wp_localize_script( 'spiffy_calendar_utilites', 'object_name', $translation_array );
			wp_register_style ('spiffycal_editor_styles', plugins_url('styles/editor.css', __FILE__), array(), 
								filemtime( plugin_dir_path(__FILE__) . 'styles/editor.css'));
			wp_enqueue_style ('spiffycal_editor_styles');

			// Add category color CSS rules
			$terms = get_terms( array(
				'taxonomy'   => 'spiffy_categories',
				'hide_empty' => false,
			) );
			$rules = '';
			foreach ($terms as $term) {
				$value = get_term_meta($term->term_id, 'color', true);
				$rules .= '
.spiffy_categories_' . esc_html($term->slug) . '  { 
	color: '. esc_html($value) . '; 
} ';
			}
			wp_add_inline_style( 'spiffycal_editor_styles', $rules );
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
	global $spiffy_calendar;
	$spiffy_calendar = new Spiffy_Calendar();
}
?>