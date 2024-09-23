<?php
/**
 * Admin View: Settings tab "Events" - event list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (!current_user_can($this->current_options['can_manage_events']))
	wp_die(__('You do not have sufficient permissions to access this page.','spiffy-calendar'));	

// Define the admin list table for event management
require_once (plugin_dir_path(__FILE__) . 'event-list-table.php');
 
global $wpdb, $spiffy_calendar;	
$spiffyEvents = $spiffy_calendar->spiffy_events_admin_list;
?>
<br />
<a href="<?php echo admin_url('admin.php?page=spiffy-calendar&tab=event_edit&action=add'); ?>" class="button button-primary"><?php _e('Add New Event','spiffy-calendar'); ?></a>

<?php 

// Display the admin list table for event management
$spiffyEvents->prepare_items();

// Display search string if applicable
if (!empty($_REQUEST['s'])) { ?>
	<span class="subtitle"><?php _e('Search results for', 'spiffy-calendar'); ?> "<?php echo sanitize_text_field($_REQUEST['s']); ?>"</span>
<?php } ?>

<!--input type="hidden" name="page" value="spiffy-calendar" /-->

<?php $spiffyEvents->search_box(__('Search', 'spiffy-calendar'), 'search_id'); ?>
<p style="text-align: right; font-style: italic; margin-top: 3px;"><?php _e('Search title, description and location', 'spiffy-calendar'); ?></p>

<?php
if ( !$this->bonus_addons_active() ) {
	$disabled = 'disabled="disabled"';
} else {
	$disabled = '';
}
?>
<div>
	<a href="#" class="button" onclick='jQuery("#div_import").css("display", ""); return false;' <?php echo $disabled; ?> >
		<?php _e('Import CSV','spiffy-calendar'); ?>
	</a>
	<a href='<?php echo esc_url($_SERVER['REQUEST_URI']); ?>&spiffy_csv_export=true&nonce=<?php echo wp_create_nonce( 'spiffy_export_nonce' ); ?>' class='button' <?php echo $disabled; ?> >
		<?php _e( 'Export CSV','spiffy-calendar' ); ?>
	</a>

	<div id='div_import' name='div_import' style="display: none;">
		<input type="file" name="spiffy_csv" multiple="false" />
		<input type="submit" <?php echo $disabled; ?> value="<?php _e ( 'Import','spiffy-calendar'); ?>" name="import_events" id="import_events" class="button-primary spiffy-submit action" />
		<?php _e('Import events from CSV', 'spiffy-calendar'); ?>
		<?php // Nonce is defined in admin-settings.php ?>
	</div>

<?php	  
if ( !$this->bonus_addons_active() ) {
	echo " <small><em>* ";
	_e ('CSV import/export is a bonus feature', 'spiffy-calendar');
	echo "</em></small>";
}
?>
</div>

<?php
// Display the list
$spiffyEvents->views();
$spiffyEvents->display(); 
?>