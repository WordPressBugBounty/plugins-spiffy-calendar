<?php
/*
 ** Spiffy Calendar Gutenberg Block
 **
 ** This code is included during the "init" action.
 **
 ** Copyright Spiffy Plugins
 **
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!class_exists("SPIFFYCALBlock")) {
class SPIFFYCALBlock {

	/*
	** Construct the block
	*/
	function __construct () {
		global $wpdb, $spiffy_calendar;

		// Needed to run from WP-CLI
		if (!isset($spiffy_calendar)) {
			$spiffy_calendar = new stdClass();
			$spiffy_calendar->current_options = ['alphabetic_categories' => 'true'];
		}		

		// Get calendar category list
		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE;
		if ($spiffy_calendar->current_options['alphabetic_categories'] == 'true') $sql .= " ORDER BY category_name";
		$cats = $wpdb->get_results($sql);
		$cats_array = array();
		foreach($cats as $cat) {
			$cats_array[] = array( 'value' => $cat->category_id,
									'label' => esc_html(stripslashes($cat->category_name))
								);
			}
			
		// Register our script and associated data
		wp_register_script(
			'spiffy-calendar-block',
			plugins_url( '/js/block.js', __DIR__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n' ),
			filemtime( plugin_dir_path(__DIR__) . 'js/block.js'));
		wp_add_inline_script( 
			'spiffy-calendar-block', 
'/* <![CDATA[ */
' . 'var spiffycal_bonus="'.is_plugin_active( 'spiffy-calendar-addons/spiffy-calendar-addons.php').'";
'. 'var spiffycal_cats='. json_encode($cats_array, JSON_PRETTY_PRINT) . ';
' . '/* ]]> */',
			'before' );
		
		// Register our styles so the calendar displays in backend blocks
		wp_register_style(
			'spiffycal-styles',
			plugins_url( 'styles/default.css', __DIR__ ),
			array(),
			filemtime( plugin_dir_path( __DIR__ ) . 'styles/default.css' )
		);

		register_block_type( 'spiffy-calendar/main-block', array(
			'attributes' => array(
								'expand' => array(
										'type' => 'boolean',
										'default' => true,
									),
								'display' => array(
										'type' => 'string',
										'default' => 'spiffy-calendar',
									),
								'title' => array(
										'type' => 'string',
									),
								'cat_list' => array (
										'type' => 'array'
									),
								'limit' => array (
										'type' => 'number',
										'default' => 0
									),
								'style' => array (
										'type' => 'string'
									),
								'num_columns' => array (
										'type' => 'number',
										'default' => 3
									),									
								'none_found' => array(
										'type' => 'string',
									),
								'show_date' => array(
										'type' => 'string',
										'default' > 'false',
									),
								'manage' => array(
										'type' => 'string',
										'default' > 'false',
									),
								'manage_title' => array(
										'type' => 'string',
										'default' => __('Your events', 'spiffy-calendar'),
									),
								),
			'editor_script' => 'spiffy-calendar-block',
			'style' => 'spiffycal-styles',
			'render_callback' => array($this, 'block_render'),
		) );	
	}

	/**
	 * Render the block.
	 *
	 * @param array $attributes The attributes that were set on the block.
	 */
	public function block_render( $attributes ) {
		// return ("hello");
		// return print_r ($attributes, true);

		// Pull out the values that are not shortcode attributes
		$display = $attributes['display'];
		$expand =  $attributes['expand'];
		unset ($attributes['display']);
		unset ($attributes['expand']);
		
		
		// Encode the shortcode attributes
		$shortcode_atts = '';
		foreach ($attributes as $key => $value) {
			if ( !in_array($key, array(	
								'title',
								'cat_list',
								'limit',
								'style',
								'num_columns',									
								'none_found',
								'show_date',
								'manage',
								'manage_title' ))) continue;
			if ($value != '') {
				$shortcode_atts .= ' ' . $key . '="' . ((is_array($value) && $this->is_all_strings($value))? implode(',', $value) : $value) . '"';
			}
		}
		
		// Render the output appropriately
		if ($expand) return do_shortcode('[' . $display . $shortcode_atts . ']');
		return '[' . $display . $shortcode_atts . ']';
	}

	/*
	** Check for array of strings
	*/
	private function is_all_strings ( $check_this ) {
		if (is_array($check_this)) {
			foreach ($check_this as $value) {
					if (!is_string($value))	{
						 return false;
					}
				}
				return true;					
		}
		return is_string ($check_this);
	}

} // end of class
}

if (class_exists("SPIFFYCALBlock")) {
	$spiffy_calendar_block = new SPIFFYCALBlock();
}

?>