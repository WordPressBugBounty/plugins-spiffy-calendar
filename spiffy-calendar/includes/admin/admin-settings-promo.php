<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<h4><?php _e('Spiffy Calendar', 'spiffy-calendar'); ?>&nbsp;<a href="https://spiffycalendar.spiffyplugins.ca/documentation/">documentation</a></h4>
<?php if ( !$this->bonus_addons_active() ) { ?>
<div id="message" class="updated inline" style="margin-top: 35px; margin-left: 0;">
<p><a href="https://spiffycalendar.spiffyplugins.ca"><?php _e('Make a donation', 'spiffy-calendar'); ?></a> <?php _e('to this plugin and you will receive bonus add-ons and priority technical support', 'spiffy-calendar'); ?>.</p>
<ul class="ul-disc">
	<li><?php _e('Premium themes', 'spiffy-calendar'); ?></li>
	<li><?php _e('Theme Customizer', 'spiffy-calendar'); ?></li>
	<li><?php _e('ICS export', 'spiffy-calendar'); ?></li>
	<li><?php _e('Front end event submit form', 'spiffy-calendar'); ?></li>
	<li><?php _e('Import/Export events in a CSV file', 'spiffy-calendar'); ?></li>
	<li><?php _e('Custom fields', 'spiffy-calendar'); ?></li>
</ul>
</div>
<?php } ?>