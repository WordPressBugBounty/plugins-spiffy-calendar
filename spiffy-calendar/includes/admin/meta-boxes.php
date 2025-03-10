<?php
/**
 * Meta boxes
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("SPIFFYCAL_metaboxes")) {
class SPIFFYCAL_metaboxes {

	public $meta_defs;
	
	/*
	** Setup
	*/
	public function __construct(){
		add_action ( 'init' , array ($this, 'meta_boxes_define') );
		add_action( 'add_meta_boxes', array($this, 'meta_boxes_init')); 
		add_action( 'save_post', array($this, 'meta_boxes_save'), 10, 3 );		
	}
	
	/*
	** Define the meta boxes
	*/
	function meta_boxes_define () {
		$this->meta_defs = array(
			array(
					'slug' => '_spiffy_event_begin',
					'label' => __('Start Date', 'spiffy-calendar'),
					'type' => 'date',
					'name' => 'event_begin',
					'description' => '',
					'default' => '',
					'front_edit' => true			// allowed on front end submit form?
					),
			array(
					'slug' => '_spiffy_event_end',
					'label' => __('End Date', 'spiffy-calendar'),
					'type' => 'date',
					'name' => 'event_end',
					'description' => '',
					'default' => '',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_time',
					'label' => __('Start Time', 'spiffy-calendar'),
					'type' => 'time',
					'name' => 'event_time',
					'description' => '',
					'default' => '',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_end_time',
					'label' => __('End Time', 'spiffy-calendar'),
					'type' => 'time',
					'name' => 'event_end_time',
					'description' => '',
					'default' => '',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_location',
					'label' => __('Location', 'spiffy-calendar'),
					'type' => 'textarea',
					'name' => 'event_location',
					'description' => '',
					'default' => '',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_link_location',
					'label' => __('Link Checkbox', 'spiffy-calendar'),
					'type' => 'checkbox',
					'name' => 'event_link_location',
					'description' => '',
					'default' => 'F',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_link',
					'label' => __('Event Link', 'spiffy-calendar'),
					'type' => 'url',
					'name' => 'event_link',
					'description' => '',
					'default' => '',
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_recur',
					'label' => __('Recurring Interval', 'spiffy-calendar'),
					'type' => 'recur',
					'name' => 'event_recur',
					'description' => '',
					'default' => 'S',
					'front_edit' => true
					),
					
			array(
					'slug' => '_spiffy_event_recur_multiplier',
					'label' => __(' Repeat every', 'spiffy-calendar'),
					'type' => 'recur-multi',
					'name' => 'event_recur_multiplier',
					'description' => '',
					'default' => 0,
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_repeats',
					'label' => __('Repeats', 'spiffy-calendar'),
					'type' => 'recur-repeats',
					'name' => 'event_repeats',
					'description' => __('Entering 0 means forever. Where the recurrence interval is left at none, the event will not recur.','spiffy-calendar'),
					'default' => 0,
					'front_edit' => true
					),
			array(
					'slug' => '_spiffy_event_hide_events',
					'label' => __('Hide Events', 'spiffy-calendar'),
					'type' => 'boolean',
					'name' => 'event_hide_events',
					'description' => __('Entering True means other events of this category will be hidden for the specifed day(s)', 'spiffy-calendar'),
					'default' => 'F',
					'front_edit' => false
					),
			array(
					'slug' => '_spiffy_event_show_title',
					'label' => __('Show Title', 'spiffy-calendar'),
					'type' => 'boolean',
					'name' => 'event_show_title',
					'description' => __('Entering True means the title of this event will be displayed. This is only used if Hide Events is True', 'spiffy-calendar'),
					'default' => 'F',
					'front_edit' => false
					),
					
		);		
	}
	
	/*
	** Add the custom meta boxes to our custom post editor
	*/
	function meta_boxes_init () {
		add_meta_box(
			'spiffy_event_meta', 					// Unique ID
			__('Event Details','spiffy-calendar'),	// Box title
			array($this, 'meta_boxes_output'),		// Content callback, must be of type callable
			'spiffy_event',							// Post type
			'normal',
			'high'
		);		
	}

	/*
	** Return all post meta, including default values if meta is not defined
	*/
	function get_all_meta($post_id) {
		$meta = get_post_meta ($post_id);
		foreach ($this->meta_defs as $meta_def) {
			if (!isset($meta[$meta_def['slug']])) $meta[$meta_def['slug']] = array($meta_def['default']);
		}
		// echo "<br /><br />META for ".($post_id)."<br />";
		// print_r($meta);
		
		return ($meta);
	}
	
	/*
	** Custom meta boxes output
	**
	** $user_input and $args are needed to share with front end submit form
	** $args defines excluded fields
	*/	
	function meta_boxes_output ($post, $user_input = '', $args = array()) {
			
		// Add nonce for security and authentication.
		wp_nonce_field( 'spiffycal_meta', 'spiffycal_meta_nonce' );
				
		$defaults = array (
				'include_category' => 'true', // not applicable here
				'include_description' => 'true', // not applicable here
				'include_images' => 'true', // not applicable here
				'include_link' => 'true',
				'include_location' => 'true',
				'include_recurring' => 'true',
				'include_times' => 'true',
			);
			 
		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );	

		// Output our meta 
		foreach ($this->meta_defs as $meta_def) {
			// Skip if this field is never allowed on front
			if ( ($post == null) && !$meta_def['front_edit']) continue;
			
			if ( ($post == null) && ($user_input != '') ) {
				$value = $user_input->{$meta_def['name']};
			} else if ($post != null) {
				$value = get_post_meta( $post->ID, $meta_def['slug'], true );
			} else {
				$value = $meta_def['default'];
			}
			
			// watch out for excluded fields for front end edit form
			?>
			<div class="spiffy-meta-def">
			<?php
			switch ($meta_def['type']) {
				case 'date': 
					if ( empty($value) ) {
						$value = date("Y-m-d",current_time('timestamp'));
					} 				
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label'] ?></label>
					<input type="text" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" readonly class="spiffy-date-field" size="12"
						value="<?php echo esc_html($value) ?>" />
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'time':
					if ($args['include_times'] != 'true') break;
					if ( !empty($value) ) {
						$value = date(get_option('time_format'), strtotime($value));
					} 				
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<input type="text" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" value="<?php echo esc_html($value) ?>" />
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'textarea' :
					if (($meta_def['name'] == 'event_location') && ($args['include_location'] != 'true')) break;
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<textarea class="spiffy-edit-data" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" rows="5" cols="50"><?php echo esc_textarea(stripslashes($value)); ?>
					</textarea>
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;

				case 'checkbox' :
					if (($meta_def['name'] == 'event_link_location') && ($args['include_location'] != 'true')) break;
					?>
					<label for="<?php echo $meta_def['name'] ?>"></label>
					<input class="spiffy-edit-data2" type="checkbox" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" <?php if ( !empty($value) && ($value == 'T')) echo 'checked'; ?> />
					<?php
					if ($meta_def['name'] == 'event_link_location') {
						_e('Include link to Google map','spiffy-calendar');
					}
					?>
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'url' :
					if (($meta_def['name'] == 'event_link') && ($args['include_link'] != 'true')) break;
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<input type="url" class="spiffy-edit-data" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" value="<?php echo esc_url($value) ?>">
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'boolean' :
					$selected_t = '';
					$selected_f = '';
					if ($value == 'T') {
						$selected_t = 'selected="selected"';
					} else if ($value == 'F') {
						$selected_f = 'selected="selected"';
					}
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<select id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" class="input">
						<option <?php echo $selected_f ?> value='F'><?php _e('False', 'spiffy-calendar') ?></option>
						<option <?php echo $selected_t ?> value='T'><?php _e('True', 'spiffy-calendar') ?></option>
					</select> 
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'recur' :
					if ($args['include_recurring'] != 'true') break;
					$selected_s = '';
					$selected_w = '';
					$selected_b = '';
					$selected_m = '';
					$selected_y = '';
					$selected_u = '';
					$selected_d = '';
					if ($value == "S") {
						$selected_s = 'selected';
					} else if ($value == "W") {
						$selected_w = 'selected';
					} else if ($value == "M") {
						$selected_m = 'selected';
					} else if ($value == "Y") {
						$selected_y = 'selected';
					} else if ($value == "U") {
						$selected_u = 'selected';
					} else if ($value == "D") {
						$selected_d = 'selected';
					}
					?>
					<label for="spiffy-event-recur"><?php echo $meta_def['label']; ?></label>
					<select id="spiffy-event-recur" name="<?php echo $meta_def['name'] ?>" class="input">
							<option <?php echo $selected_s; ?> value="S"><?php _e('None', 'spiffy-calendar') ?></option>
							<option <?php echo $selected_w; ?> value="W"><?php _e('Weekly', 'spiffy-calendar') ?></option>
							<option <?php echo $selected_m; ?> value="M"><?php _e('Months (date)', 'spiffy-calendar') ?></option>
							<option <?php echo $selected_u; ?> value="U"><?php _e('Months (day)', 'spiffy-calendar') ?></option>
							<option <?php echo $selected_y; ?> value="Y"><?php _e('Years', 'spiffy-calendar') ?></option>
							<option <?php echo $selected_d; ?> value="D"><?php _e('Custom Days', 'spiffy-calendar') ?></option>						
					</select> 
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'recur-multi' :
					if ($args['include_recurring'] != 'true') break;
					?>
					<span id="spiffy-custom-days">
					<span for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></span>
					<input id="spiffy-custom-days-input" type="number" step="1" min="1" max="199" class="spiffy-edit-data" name="<?php echo $meta_def['name'] ?>" value="<?php echo esc_html($value) ?>">&nbsp;<?php _e('days', 'spiffy-calendar'); ?>
					</span>
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				case 'recur-repeats' :
					if ($args['include_recurring'] != 'true') break;
					if ($value == '') $value = 0;
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<input id="<?php echo $meta_def['name'] ?>" type="number" size="3" min="0" class="spiffy-edit-data" name="<?php echo $meta_def['name'] ?>" value="<?php echo esc_html($value) ?>">&nbsp;<?php echo __('times','spiffy-calendar'); ?>
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
					
				default:
					?>
					<label for="<?php echo $meta_def['name'] ?>"><?php echo $meta_def['label']; ?></label>
					<input type="text" id="<?php echo $meta_def['name'] ?>" name="<?php echo $meta_def['name'] ?>" value="<?php echo esc_html($value) ?>" />
					<span class="spiffy-description"><?php echo esc_html($meta_def['description']) ?></span>
					<?php
					break;
			}
			
			?>
			</div>
			<?php
		}
		
		/* Add custom fields */
		global $spiffy_calendar, $spiffycal_bonus_settings, $spiffycal_custom_fields;
		if ( $spiffy_calendar->bonus_addons_active() && method_exists ($spiffycal_bonus_settings, 'custom_fields_edit') ) {
			if ( ( $post == null) && ($user_input != '') ) {
				echo $spiffycal_bonus_settings->custom_fields_edit($user_input);
			} else if ( $post != null) {
				$custom_fields = new stdClass();
				$custom_fields->custom_field = $spiffycal_custom_fields->get_custom_fields($post->ID);
				echo $spiffycal_bonus_settings->custom_fields_edit($custom_fields);
			} else {
				$custom_fields = new stdClass();
				echo $spiffycal_bonus_settings->custom_fields_edit($custom_fields);
			}
		}

	}
	
	/*
	** Save the custom meta boxes input
	*/	
	function meta_boxes_save ($post_id, $post, $update) {
		global $spiffy_calendar;

		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['spiffycal_meta_nonce'] ) ? $_POST['spiffycal_meta_nonce'] : '';
		$nonce_action = 'spiffycal_meta';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_spiffycal', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['post_type'] ) && 'spiffy_event' === $_POST['post_type'] ) {
			// Collect and clean up user input
			$event_data = $this->meta_boxes_sanitize();

			// Update post meta
			$result = $this->meta_boxes_update ($post_id, $event_data);
		}
		
	}

	/*
	** Sanitize the custom meta boxes posted data
	*/	
	function meta_boxes_sanitize () {
		global $current_user, $spiffy_calendar, $spiffycal_custom_fields;
		
		$user_input = new stdClass();

		foreach ($this->meta_defs as $meta_def) {
			$user_input->{$meta_def['name']} = '';
			if (!empty($_POST[$meta_def['name']])) {
				switch ($meta_def['type']) {
					case "textarea":
						$user_input->{$meta_def['name']} = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST[$meta_def['name']] ))); // preserve new lines
						break;
						
					default:
						$user_input->{$meta_def['name']} = sanitize_text_field ( $_POST[$meta_def['name']] );
						break;
				}
			} else {
				$user_input->{$meta_def['name']} = $meta_def['default'];
			}
		}

		// Special cases not handled in the loop
		$user_input->event_link_location = !empty($_POST['event_link_location'])? 'T' : 'F';
		$user_input->event_link = !empty($_POST['event_link']) ? wp_filter_nohtml_kses ( $_POST['event_link'] ) : '';

		// Sanitize front end submit additional fields
		$user_input->event_title = !empty($_POST['event_title']) ? sanitize_text_field ( $_POST['event_title'] ) : '';
		$user_input->event_desc = !empty($_POST['event_desc']) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['event_desc'] ))) : ''; // preserve new lines
		$user_input->event_location = !empty($_POST['event_location']) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['event_location'] ))) : ''; // preserve new lines
		$user_input->event_status = !empty($_POST['event_status']) ? sanitize_text_field ( $_POST['event_status'] ) : 'Publish';	
		$user_input->event_category = !empty($_POST['event_category']) ? intval(sanitize_text_field ( $_POST['event_category'] )) : '';
		$user_input->event_author = !empty($_POST['event_author']) ? sanitize_text_field ( $_POST['event_author'] ) : $current_user->ID;
		$user_input->event_image = !empty($_POST['event_image']) ? sanitize_text_field ( $_POST['event_image'] ) : 0;
		$user_input->event_remove_image = !empty($_POST['event_remove_image']) ? sanitize_text_field ( $_POST['event_remove_image'] ) : 'false';

		// Sanitize custom fields, if any
		if ( $spiffy_calendar->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
			$spiffycal_custom_fields->sanitize($user_input);
		}
		
		return $user_input;
}
	
	/*
	** Perform the meta updates after the posted data has been cleaned up
	*/
	function meta_boxes_update ($event_id, $event_data) {
		global $wpdb, $wp_error, $spiffy_calendar, $spiffy_edit_errors, $spiffycal_custom_fields, $current_user;
		
		$result = array();
		$result['errors'] = 0;
		$result['messages'] = '';

		// Check dates are logical
		$start_datestamp = strtotime($event_data->event_begin);
		$end_datestamp = ($event_data->event_end == '')? $start_datestamp : strtotime($event_data->event_end);
		if ($end_datestamp < $start_datestamp) {
			$end_datestamp = $start_datestamp;
		}
		$event_data->event_begin = date( 'Y-m-d',$start_datestamp );
		$event_data->event_end = date( 'Y-m-d', $end_datestamp );

		update_post_meta( $event_id,'_spiffy_event_begin', $event_data->event_begin );
		update_post_meta( $event_id,'_spiffy_event_end', $event_data->event_end );

		// Check for a valid time, or an empty one
		$event_data->event_time = ($event_data->event_time == '')? '' : date( 'H:i:00',strtotime($event_data->event_time) );
		update_post_meta( $event_id,'_spiffy_event_time', $event_data->event_time );

		// Check for a valid end time, or an empty one
		$event_data->event_end_time = ($event_data->event_end_time == '')? '' : date( 'H:i:00',strtotime($event_data->event_end_time) );
		update_post_meta( $event_id,'_spiffy_event_end_time', $event_data->event_end_time );
		
		// location
		update_post_meta ( $event_id, '_spiffy_event_location', $event_data->event_location );

		// link to google maps
		if (($event_data->event_link_location == 'T') || ($event_data->event_link_location == 'F')) {
			// good
		} else {
			$event_data->event_link_location = 'F';
		}
		update_post_meta ( $event_id, '_spiffy_event_link_location', $event_data->event_link_location );
		
		// Check to make sure the URL is all right
		if (preg_match('/^(http)(s?)(:)\/\//',$event_data->event_link) || $event_data->event_link == '') {
		} else {
			$event_data->event_link = '';
		}
		update_post_meta( $event_id,'_spiffy_event_link', $event_data->event_link );

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
			if ($event_data->event_repeats != 0 && $event_data->event_recur == 'S') {
				$event_data->event_repeats = 0;
			}
			if ($event_data->event_repeats < 0) {
				$event_data->event_repeats = 0;
			}
		} else {
			// default to something logical
			$event_data->event_recur = 'S';
		}
		update_post_meta( $event_id,'_spiffy_event_recur', $event_data->event_recur );
		update_post_meta( $event_id,'_spiffy_event_repeats', $event_data->event_repeats );
		
		$event_data->event_recur_multiplier = (int)$event_data->event_recur_multiplier;
		if ($event_data->event_recur == 'D') {
			if ( ($event_data->event_recur_multiplier < 1) || ($event_data->event_recur_multiplier > 199) ) $event_data->event_recur_multiplier = 1;
		} else {
			$event_data->event_recur_multiplier = 1;
		}
		update_post_meta( $event_id,'_spiffy_event_recur_multiplier', $event_data->event_recur_multiplier );
		
		// Ensure show/hide is valid
		if (($event_data->event_hide_events == 'T') || ($event_data->event_hide_events == 'F')) {
			// good
		} else {
			$event_data->event_hide_events = 'F';
		}
		update_post_meta ( $event_id, '_spiffy_event_hide_events', $event_data->event_hide_events );

		if (($event_data->event_show_title == 'T') || ($event_data->event_show_title == 'F')) {
			// good
		} else {
			$event_data->event_show_title = 'F';
		}
		update_post_meta ( $event_id, '_spiffy_event_show_title', $event_data->event_show_title );
		
		// Ensure author is valid on new events
		if (isset($_POST['submit_add_event']) && ($event_data->event_author != $current_user->ID)) {
			$result['errors']++;
			$result['messages'] .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid author.','spiffy-calendar') . '</p>';
			$spiffy_edit_errors['event_author'] = '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Invalid author. ','spiffy-calendar') . '</p>';			
		}
						

		// unlink image if requested 
		if ( isset($event_data->event_remove_image) && ($event_data->event_remove_image === 'true')) {
			delete_post_thumbnail( $event_id );
			$event_data->event_image = 0;
		}
			
		/* Update custom fields */
		if ( $spiffy_calendar->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
			$spiffycal_custom_fields->update($event_id, $event_data);
		}

		return $result;		
	}
	
}
}

if (class_exists("SPIFFYCAL_metaboxes")) {
	global $spiffycal_meta_boxes;
	$spiffycal_meta_boxes = new SPIFFYCAL_metaboxes();
}