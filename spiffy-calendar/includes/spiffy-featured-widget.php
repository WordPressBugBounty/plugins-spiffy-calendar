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
		global $spiffy_calendar, $spiffy_calendar_views, $spiffycal_meta_boxes, $spiffycal_custom_fields;
		
		$spiffy_calendar->enqueue_frontend_scripts_and_styles();
		
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 

		/* Our variables from the widget settings. */
		$title = empty( $instance['title'] )? '' : apply_filters('widget_title', $instance['title'] );
		$event_id = empty( $instance['event_id'] )? '' : $instance['event_id'];

		$post = get_post ($event_id);
		if ( $post && ($post->post_type == 'spiffy_event') ) {
			$post->meta = $spiffycal_meta_boxes->get_all_meta($event_id);
			$post->terms = get_the_terms( $event_id, 'spiffy_categories' );
			if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
				$post->custom_field = $spiffycal_custom_fields->get_custom_fields($event_id);
			}
			echo $before_widget;
			echo '<div class="spiffy-list-Expanded">';
			echo $before_title . $title . $after_title;
			echo '<ul><li class="spiffy-event-details spiffy-Expanded">';
			echo '<span class="spiffy-upcoming-date">';
			echo $spiffy_calendar_views->format_date ( $post->meta['_spiffy_event_begin'][0], $post->meta['_spiffy_event_end'][0] );
			echo '</span>';
			echo $spiffy_calendar_views->draw_event_expanded ($post);
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
				$events = get_posts( array (
							'numberposts' => -1,
							'post_type' => 'spiffy_event',
							'orderby' => 'title',
							'order' => 'ASC'
				));
				foreach ( $events as $event ) {
					echo '<option ';
					if ($instance['event_id'] == $event->ID) echo 'selected="selected" ';
					echo 'value="'.$event->ID.'">' . esc_html(stripslashes($event->post_title)) . '</option>';
				} 
				?>
			</select>		
		</p>

	<?php
	}
}
?>