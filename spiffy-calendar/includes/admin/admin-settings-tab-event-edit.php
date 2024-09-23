<?php
/**
 * Admin View: Settings tab "Events" - event edit form
 *
 * If $event_id is set, it will be used to edit or copy an existing event
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!current_user_can($this->current_options['can_manage_events']))
	wp_die(__('You do not have sufficient permissions to access this page.','spiffy-calendar'));	
	
	
if ( isset($_REQUEST['errors']) ) {
	// An add or update failed, redraw the event edit screen
	$action = (isset($_REQUEST['action']))? sanitize_text_field($_REQUEST['action']) : 'edit';
} else if ( isset($_REQUEST['action']) && ($_REQUEST['action'] == 'add') ) {
	// Add new event requested by post or get
	$action = 'add';
} else if ( isset($_REQUEST['action']) && (($_REQUEST['action'] == 'edit') || ($_REQUEST['action'] == 'copy')) ) {
	$orig_event_id = intval(sanitize_text_field ($_GET['event']));
	$event_id = intval(sanitize_text_field ($_REQUEST['event']));
	$action = sanitize_text_field ($_REQUEST['action']);
	// Edit or copy existing event
} else {
	// Add new event by default
	$action = 'add';
}

// security check!
if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {

	if ( !isset($event_id)) {
		wp_die ( __('Invalid request','spiffy-calendar') ); 
	}
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'spiffy-edit-security'.$orig_event_id ) )
		wp_die( __('Security check failed!','spiffy-calendar') );
} else {
	if ( ($action == 'edit') || ($action == 'copy') ) {
		wp_die ( __('Security check failure','spiffy-calendar') );
	}
}
	
global $wpdb, $spiffy_user_input, $wp_version, $spiffy_edit_errors, $spiffy_calendar_views, $spiffycal_custom_fields, $current_user;
$data = false;

// Check for existing event edit or copy
if ( isset($event_id) && ($event_id != '') ) {
	if ( intval($event_id) != $event_id ) {
		echo "<div class=\"error\"><p>".__('Bad event ID','spiffy-calendar')."</p></div>";
	} else {
		// Get the event data
		$data = $wpdb->get_results("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id='" . 
						esc_sql($event_id) . "' LIMIT 1");
		if ( empty($data) ) {
			echo "<div class=\"error\"><p>".__("An event with that ID couldn't be found",'spiffy-calendar')."</p></div>";
			wp_die ( __('Invalid request','spiffy-calendar') );
		} else {
			// Check this user is allowed to edit this event
			if (($this->current_options['limit_author'] == 'true') && !current_user_can('manage_options')) {
				if ($data[0]->event_author != $current_user->ID) {
					wp_die( __('You do not have sufficient permissions to access this page.','spiffy-calendar') );	
				}
			}
			
			if ( $this->bonus_addons_active() && isset($spiffycal_custom_fields) ) {
				// Add in custom fields
				$data[0]->custom_field = $spiffycal_custom_fields->get_custom_fields($event_id);
			}

			if ($action == 'copy') {
				// Set up variable to add a copy of the event
				unset ($data[0]->event_id);
				$event_id = '';
				$data[0]->event_title = '(copy) ' . $data[0]->event_title;
				$action = 'add';
			}
		}
		$data = $data[0];
	}
	// Recover users entries if they exist; in other words if editing an event went wrong
	if (!empty($spiffy_user_input)) {
		$data = $spiffy_user_input;
	}
} else {
	// Deal with possibility that form was submitted but not saved due to error - recover user's entries here
	$data = $spiffy_user_input;
	if ( isset($_POST['event_id']) ) {
		$event_id = intval(sanitize_text_field($_POST['event_id']));
	} else {
		$event_id = '';
	}
}

?>

<?php if ($action == 'add') { ?>
<h3><?php _e('Add Event','spiffy-calendar'); ?></h3>
<?php } else { ?>
<h3><?php _e('Edit Event','spiffy-calendar'); ?></h3>
<?php } 

// Output the bulk of the form
echo $spiffy_calendar_views->event_edit_form_display($data);
?>

<input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

<?php if ($action == 'add') { ?>
<input type="submit" name="submit_add_event" class="button button-primary spiffy-submit" value="<?php _e('Save','spiffy-calendar'); ?>" />
<?php } else { ?>
<input type="submit" name="submit_edit_event" class="button button-primary spiffy-submit" value="<?php _e('Update','spiffy-calendar'); ?>" />
<?php } ?>	