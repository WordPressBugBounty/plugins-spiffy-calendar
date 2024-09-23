<?php
/*
 ** Spiffy MiniCal Event List Widget
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'widgets_init', 'spiffy_register_minical_widget');
function spiffy_register_minical_widget() {
		register_widget( 'Spiffy_MiniCal_Widget' ); 
}	

/* MiniCal events widget */
class Spiffy_MiniCal_Widget extends WP_Widget {

	private $defaults = array();
	
	/**
	 * Initialize the widgets
	 */
	function __construct() {
		$this->defaults = array( 'title' => __('Calendar', 'spiffy-calendar'), 'catlist' => '' );
		parent::__construct(
			'spiffy_minical_widget', // Base ID
			__( 'Spiffy Mini Calendar', 'spiffy-calendar' ), // Name
			array( 'description' => __( 'Mini calendar grid', 'spiffy-calendar' ), ) // Args
		);
	}

	/**
	 * Register the widget
	 */
	function load_widget() { 
		register_widget( 'Spiffy_MiniCal_Widget' ); 
	}

	/**
	 * Display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		global $spiffy_calendar, $spiffy_calendar_views;
		
		$spiffy_calendar->enqueue_frontend_scripts_and_styles();

		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 

		/* Our variables from the widget settings. */
		$title = empty( $instance['title'] )? '' : apply_filters('widget_title', $instance['title'] );
		$catlist = empty( $instance['catlist'] )? '' : $instance['catlist'];

		$the_events = $spiffy_calendar_views->minical($catlist, '');
		if ($the_events != '') {
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo $the_events;
			echo $after_widget;
		}
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		$instance['catlist'] = strip_tags( $new_instance['catlist'] );

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 
		?>
		
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'spiffy-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_html($instance['title']); ?>" />
		</p>

		<!-- Category List: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'catlist' ); ?>"><?php _e('Comma separated category id list','spiffy-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'catlist' ); ?>" name="<?php echo $this->get_field_name( 'catlist' ); ?>" value="<?php echo esc_html($instance['catlist']); ?>" />
		</p>

	<?php
	}
}
?>