<?php
/**
 * Admin View: Settings bonus tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $spiffycal_custom_fields;

$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field($_REQUEST['tab']) : 'events';
if ($current_tab === 'tickets') {
	echo "<div class=\"error\"><p>".__('The CampTix plugin has been discontinued. Therefore the CampTix integration is deprecated.','spiffy-calendar')."</p></div>";
} elseif ( ($current_tab == 'custom_fields') && ( !isset($spiffycal_custom_fields)) ) {
	echo "<div class=\"notice notice-warning\"><p>".__('This feature requires bonus add-ons version 3.22 and above.','spiffy-calendar')."</p></div>";	
}

if ( !$this->bonus_addons_active() ) {
?>

<h3><strong><?php _e('This bonus feature requires the ', 'spiffy-calendar');?><a href="http://spiffycalendar.spiffyplugins.ca/bonus-add-ons/" ><?php _e('Spiffy Calendar Bonus Add-Ons', 'spiffy-calendar'); ?></a></strong></h3>
<?php } ?>