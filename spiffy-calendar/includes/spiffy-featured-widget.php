<?php
/*
 ** Spiffy Featured Event Widget
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'widgets_init', 'spiffy_register_featured_widget');
function spiffy_register_featured_widget() {
		register_widget( 'Spiffy_Featured_Widget' ); 
}	

/* Featured event widget */
class Spiffy_Featured_Widget extends WP_Widget {

	private $defaults = array();

	/**
	 * Initialize the widgets
	 */
	function __construct() {

		$this->defaults = array( 'title' => __('Featured Event', 'spiffy-calendar'), 'event_id' => '');

		parent::__construct(
			'spiffy_featured_widget', // Base ID
			__( 'Spiffy Featured Event', 'spiffy-calendar' ), // Name
			array( 'description' => __( 'Expanded view of a specific event', 'spiffy-calendar' ), ) // Args
		);
	}

	/**
	 * Register the widget
	 */
	function load_widget() { 
		register_widget( 'Spiffy_Featured_Widget' ); 
	}

	/**
	 * Display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		global $spiffy_calendar, $spiffy_calendar_views, $wpdb;
		
		$spiffy_calendar->enqueue_frontend_scripts_and_styles();
		
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 

		/* Our variables from the widget settings. */
		$title = empty( $instance['title'] )? '' : apply_filters('widget_title', $instance['title'] );
		$event_id = empty( $instance['event_id'] )? '' : $instance['event_id'];

		$sql = $wpdb->prepare("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id =%d", $event_id);
		$the_events = $wpdb->get_results($sql);
		if ( !empty($the_events) ) {
			echo $before_widget;
			echo '<div class="spiffy-list-Expanded">';
			echo $before_title . $title . $after_title;
			echo '<ul><li class="spiffy-event-details spiffy-Expanded">';
			if ($the_events[0]->event_recur == 'S') {
				echo '<span class="spiffy-upcoming-date">' . 
						date_i18n(get_option('date_format'), strtotime($the_events[0]->event_begin)) . '</span>';
			}
			echo $spiffy_calendar_views->draw_event_expanded ($the_events[0]);
			echo '</li></ul></div>';
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
		$instance['event_id'] = strip_tags( $new_instance['event_id'] );

		return $instance;
	}

	function form( $instance ) {
		global $wpdb;
		
		/* Set up some default widget settings. */
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 
		?>
		
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'spiffy-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_html($instance['title']); ?>" />
		</p>

		<!-- Event Id: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'event_id' ); ?>"><?php _e('Select the event','spiffy-calendar'); ?></label>
			<select id="<?php echo $this->get_field_id( 'event_id' ); ?>" name="<?php echo $this->get_field_name( 'event_id' ); ?>" class="widefat" style="width:100%;">
				<?php 
				if ($instance['event_id'] == '') {
					echo '<option value="">' . __( 'Select an event', 'spiffy-calendar') . '</option>';
				}
				$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " ORDER BY event_id ASC";
				$events = $wpdb->get_results($sql);
				foreach ( $events as $event ) {
					echo '<option ';
					if ($instance['event_id'] == $event->event_id) echo 'selected="selected" ';
					echo 'value="'.$event->event_id.'">' . esc_html(stripslashes($event->event_title)) . '</option>';
				} 
				?>
			</select>		
		</p>

	<?php
	}
}
?>