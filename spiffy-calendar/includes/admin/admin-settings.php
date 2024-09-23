<?php
/**
 * Admin View: Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="wrap">
	<h2><?php _e('Spiffy Calendar Settings', 'spiffy-calendar'); ?></h2>
	<?php // Use GET method on event list, POST on all others
	$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field($_REQUEST['tab']) : 'events';
	?>
	<form class="spiffy-form" action="" method="POST" enctype='multipart/form-data' >
		<?php
			echo '<h2 class="nav-tab-wrapper">';
				foreach ( $tabs as $tab_key => $tab_caption ) {
					$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
					echo '<a class="nav-tab ' . $active . '" href="?page=spiffy-calendar&tab=' . $tab_key . '">' . $tab_caption . '</a>';
				}
			echo '</h2>';
			
			do_action ( 'spiffycal_settings_tab_' . $current_tab);
		?>

	<input type="hidden" value="true" name="save_spiffycal">
	<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('update_spiffycal_options', 'update_spiffycal_options_nonce');
	?>
	</form>		

</div>
		