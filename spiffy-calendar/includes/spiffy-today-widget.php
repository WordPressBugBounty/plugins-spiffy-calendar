<?php
/*
 ** Spiffy Today's Event List Widget
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'widgets_init', 'spiffy_register_todays_widget');
function spiffy_register_todays_widget() {
		register_widget( 'Spiffy_Today_Widget' ); 
}	

/* Today's events widget */
class Spiffy_Today_Widget extends WP_Widget {

	private $defaults = array();

	/**
	 * Initialize the widgets
	 */
	function __construct() {
		$this->defaults = array( 
					'title' => __('Today\'s Events', 'spiffy-calendar'), 
					'catlist' => '', 
					'limit' => '', 
					'style' => 'popup', 
					'show_date' => 'false',
					'none_found' => '',
					'num_columns' => '3' );
		parent::__construct(
			'spiffy_today_widget', // Base ID
			__( 'Spiffy Today\'s Events', 'spiffy-calendar' ), // Name
			array( 'description' => __( 'List today\'s events', 'spiffy-calendar' ), ) // Args
		);
	}

	/**
	 * Register the widget
	 */
	function load_widget() { 
		register_widget( 'Spiffy_Today_Widget' ); 
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
		$limit = empty( $instance['limit'] ) ? '' : absint( $instance['limit'] );
		$style = empty( $instance['style'] )? '' : $instance['style'];
		$show_date = empty( $instance['show_date'] )? 'false' : $instance['show_date'];
		$none_found = empty( $instance['none_found'] )? '' : $instance['none_found'];
		$num_columns = empty( $instance['num_columns'] ) ? '' : absint( $instance['num_columns'] );
		
		$the_events = $spiffy_calendar_views->todays_events($catlist, $limit, $style, $show_date, $none_found, '', $num_columns);
		if ($the_events != '') {
			echo $before_widget;
			echo '<div class="spiffy-list-'.$style.'">';
			echo $before_title . $title . $after_title;
			echo $the_events;
			echo '</div>';
			echo $after_widget;
		}
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Sanitize user input. */
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['catlist'] = sanitize_text_field ( $new_instance['catlist'] );
		$instance['limit'] =  (int) $new_instance['limit'];
		$instance['style'] = sanitize_text_field ( $new_instance['style'] );
		$instance['show_date'] = sanitize_text_field ( $new_instance['show_date'] );
		$instance['none_found'] = sanitize_text_field ( $new_instance['none_found'] );
		$instance['num_columns'] =  (int) $new_instance['num_columns'];

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 

		$limit = ( ! empty( $instance['limit'] ) ) ? absint( $instance['limit'] ) : '';
		$num_columns = ( ! empty( $instance['num_columns'] ) ) ? absint( $instance['num_columns'] ) : '';
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

		<!-- Limit: Numeric Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Number of events to display, blank for all','spiffy-calendar'); ?></label>
			<input type="number" class="tiny-text" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" step="1" min="1" size="2" value="<?php echo esc_html($limit); ?>" />
		</p>
		
		<!-- Style: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e('Choose the display style','spiffy-calendar'); ?></label>
			<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>" class="widefat" style="width:100%;">
				<?php
				echo "<option value='Popup' ";
				if ($instance['style'] == 'Popup') echo 'selected';
				echo ">" . __('Popup','spiffy-calendar') . "</option>";
				echo "<option value='Expanded' ";
				if ($instance['style'] == 'Expanded') echo 'selected';
				echo ">" . __('Expanded','spiffy-calendar') . "</option>";
				echo "<option ";
				if ($instance['style'] == 'Columns') echo 'selected="selected"';
				echo ">Columns</option>";				?>
			</select>		
		</p>

		<!-- Number of columns: Numeric Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_columns' ); ?>"><?php _e('Number of columns','spiffy-calendar'); ?></label>
			<input type="number" class="tiny-text" id="<?php echo $this->get_field_id( 'num_columns' ); ?>" name="<?php echo $this->get_field_name( 'num_columns' ); ?>" type="number" step="1" min="1" max="4" size="2" value="<?php echo esc_html($num_columns); ?>" />
		</p>

		<!-- Show current date?: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e('Display the current date','spiffy-calendar'); ?></label>
			<select id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" class="widefat" style="width:100%;">
				<?php
				echo "<option value='false' ";
				if ($instance['show_date'] == 'false') echo 'selected';
				echo ">" . __('False','spiffy-calendar') . "</option>";
				echo "<option value='true' ";
				if ($instance['show_date'] == 'true') echo 'selected';
				echo ">" . __('True','spiffy-calendar') . "</option>";
				?>
			</select>		
		</p>

		<!-- Default text when none found: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'none_found' ); ?>"><?php _e('Text to display if none found','spiffy-calendar'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'none_found' ); ?>" name="<?php echo $this->get_field_name( 'none_found' ); ?>" value="<?php echo esc_html($instance['none_found']); ?>" />
		</p>		
	<?php
	}
}
?>