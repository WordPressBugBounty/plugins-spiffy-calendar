<?php
/*
 ** Spiffy Calendar Views  Functions
 **
 ** Copyright Spiffy Plugins
 **
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!class_exists("SPIFFYCAL_Views")) {
class SPIFFYCAL_Views {

	private $categories = array();
	private $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');

	function __construct () {
	}
	
	// Main calendar output
	function calendar($cat_list = '', $title = '') {
		global $wpdb, $spiffy_calendar;

		$this->get_all_categories();

		// Build day of week names array
		$name_days = $this->get_day_names('full');

		// Determine month from arguments if provided
		$current_timestamp = current_time('timestamp');
		list($c_year, $c_month, $c_day) = $this->get_date_from_args();

		// Determine the index of the weekday for first of the month
		$first_weekday = $this->get_first_weekday($c_year, $c_month);

		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
		
		if ($spiffy_calendar->current_options['display_weeks'] == 'true')
			$num_columns = 8;
		else
			$num_columns = 7;
	
		// Start the table and add the header and navigation
		$calendar_body = '';
		
		if ($title != '') {
			$calendar_body .= '<h2>' . esc_html($title) . '</h2>';
		}
		
		// Determine category filter state from arguments if provided
		$category_filter_state = '';
		if ($spiffy_calendar->current_options['category_filter'] === true) {
			if (isset($_GET['category-filter-state'])) {
				$category_filter_state = sanitize_text_field ( $_REQUEST['category-filter-state'] );
			} else {
				$category_filter_state = '';
			}
		}

		// Determine grid/list selection from arguments if provided
		$toggle_state = 'grid';
		if ($spiffy_calendar->current_options['grid_list_toggle'] === true) {
			if (isset($_GET['grid-list-toggle'])) {
				$toggle_state = sanitize_text_field ( $_REQUEST['grid-list-toggle'] );
				if ( ($toggle_state != 'list') && ($toggle_state != 'grid') ) $toggle_state = 'grid';
			} else {
				$toggle_state = 'grid';
			}
		}

		$calendar_body .= '
<table class="spiffy calendar-table bigcal' . (($toggle_state === 'list')? ' spiffy-listed' : '') . '">';

		// Optional grid/list toggle button
		if ($spiffy_calendar->current_options['grid_list_toggle'] === true) {
			
			// Draw the toggle button in its initial state
			$calendar_body .= '
	<tr class="calendar-toggle-row">
	  <td colspan="'.$num_columns.'" class="calendar-toggle" >
		<a class="calendar-toggle-button" 
					href="#" 
					onclick="return false;" 
					data-list="' .  __('List View','spiffy-calendar') . '" 
					data-grid="' .  __('Grid View','spiffy-calendar') . '"
					data-month="'. date_i18n("F", mktime(null, null, null, $c_month, 1)) . '"
		>' 
			. (($toggle_state == 'grid') ? __('List View','spiffy-calendar') : __('Grid View','spiffy-calendar')) 
			. '</a>
	  </td>
	</tr>';
		}

		// Category key if going above the calendar
		if ( ($spiffy_calendar->current_options['enable_categories'] == 'true') && ($spiffy_calendar->current_options['category_key_above'] == 'true') ) {
			$calendar_body .= '<tr class="category-key-row"><td colspan="'.$num_columns.'" class="category-key" >' . $this->category_key ($cat_list) . '</td></tr>';
		}
		
		// The header of the calendar table and the links.
		$calendar_body .= '
	<tr class="calendar-heading"><td colspan="' . $num_columns . '" class="calendar-date-switcher">
		<table class="calendar-heading-tbl">
		<tr>
			<td class="calendar-prev">' . $this->prev_month($c_year, $c_month, false, $toggle_state) . '</td>
';

		// Optionally add date jumper
		if ($spiffy_calendar->current_options['display_jump'] == 'true') {
			$calendar_body .= '
			<td class="calendar-month">
				<form method="get" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'">';

			if (isset($_SERVER['QUERY_STRING'])) { 
				$qsa = array();
				parse_str($_SERVER['QUERY_STRING'], $qsa);
				foreach ($qsa as $name => $argument) {
					if ($name != 'month' && $name != 'yr' && $name != 'grid-list-toggle') {
						$calendar_body .= '<input type="hidden" name="'.strip_tags($name).'" value="'.strip_tags($argument).'" />';
					}
				}
			}

			// We build the months in the switcher
			$calendar_body .= '
					<select name="month">
						<option value="jan"'.$this->calendar_month_comparison($c_month, 1).'>'.date_i18n("F", mktime(null, null, null, 1, 1)).'</option>
						<option value="feb"'.$this->calendar_month_comparison($c_month, 2).'>'.date_i18n("F", mktime(null, null, null, 2, 1)).'</option>
						<option value="mar"'.$this->calendar_month_comparison($c_month, 3).'>'.date_i18n("F", mktime(null, null, null, 3, 1)).'</option>
						<option value="apr"'.$this->calendar_month_comparison($c_month, 4).'>'.date_i18n("F", mktime(null, null, null, 4, 1)).'</option>
						<option value="may"'.$this->calendar_month_comparison($c_month, 5).'>'.date_i18n("F", mktime(null, null, null, 5, 1)).'</option>
						<option value="jun"'.$this->calendar_month_comparison($c_month, 6).'>'.date_i18n("F", mktime(null, null, null, 6, 1)).'</option>
						<option value="jul"'.$this->calendar_month_comparison($c_month, 7).'>'.date_i18n("F", mktime(null, null, null, 7, 1)).'</option> 
						<option value="aug"'.$this->calendar_month_comparison($c_month, 8).'>'.date_i18n("F", mktime(null, null, null, 8, 1)).'</option> 
						<option value="sep"'.$this->calendar_month_comparison($c_month, 9).'>'.date_i18n("F", mktime(null, null, null, 9, 1)).'</option> 
						<option value="oct"'.$this->calendar_month_comparison($c_month, 10).'>'.date_i18n("F", mktime(null, null, null, 10, 1)).'</option> 
						<option value="nov"'.$this->calendar_month_comparison($c_month, 11).'>'.date_i18n("F", mktime(null, null, null, 11, 1)).'</option> 
						<option value="dec"'.$this->calendar_month_comparison($c_month, 12).'>'.date_i18n("F", mktime(null, null, null, 12, 1)).'</option> 
					</select>
					<select name="yr">';

			// The year switcher
			$current_year = date("Y",$current_timestamp);

			if ($c_year < $current_year-1) {
				$calendar_body .= sprintf('
						<option value="%1$s" selected>%1$s</option>', $c_year);
			}
			for ($year_offset = -1; $year_offset < 30; $year_offset++) {
				$option_year = $current_year + $year_offset;
				$option_select = ($option_year == $c_year)? ' selected' : '';
				$calendar_body .= sprintf('
						<option value="%1$s" %2$s>%1$s</option>', $option_year, $option_select);
			}
			if ($c_year >= $current_year+30) {
				$calendar_body .= sprintf('
						<option value="%1$s" selected>%1$s</option>', $c_year);
			}
			$calendar_body .= '
					</select>';
					
			if ($spiffy_calendar->current_options['grid_list_toggle'] === true) {
				$calendar_body .= '		
					<input name="grid-list-toggle" type="hidden" value="' . $toggle_state . '" />';
			}
			
			$calendar_body .= '		
					<input type="submit" onclick="sessionStorage.scrollTop = jQuery(window).scrollTop();" value="'.__('Go','spiffy-calendar').'" />
				</form>
			</td>';
		} else {
			$calendar_body .= '
			<td class="calendar-month">'.date_i18n("F", mktime(null, null, null, $c_month, 1)).' '.$c_year.'</td>';
		}
		$calendar_body .= '
			<td class="calendar-next">' . $this->next_month($c_year, $c_month, false, $toggle_state) . '</td>
		</tr>
		</table>
	</td></tr>';

		// Print the headings of the days of the week
		$calendar_body .= '<tr class="weekday-titles">';
		if ($spiffy_calendar->current_options['display_weeks'] == 'true') {
			$calendar_body .= '<td class="weekend-heading week-number-heading">'.__("#", 'spiffy-calendar').'</td>';		
		}
		for ($i=1; $i<=7; $i++) {
			// Colours need to be different if the starting day of the week is different
			if (get_option('start_of_week') == 0) {
				$calendar_body .= '<td class="'.($i<7&$i>1?'normal-day-heading':'weekend-heading').'">'.$name_days[$i-1].'</td>';
			} else {
				$calendar_body .= '<td class="'.($i<6?'normal-day-heading':'weekend-heading').'">'.$name_days[$i-1].'</td>';
			}
		}
		$calendar_body .= '</tr>';

		// Get all potential events for the month ready
		$potential_events = $this->grab_events($c_year,$c_month,1,$c_year,$c_month,$days_in_month,$cat_list);

		// Loop through the days, drawing each day box
		$go = FALSE;
		for ($i=1; $i<=$days_in_month;) {
			$calendar_body .= '<tr>';
			if ($spiffy_calendar->current_options['display_weeks'] == 'true') {
				$calendar_body .= '<td class="day-without-date week-number">'.date("W", mktime (0,0,0,$c_month,$i,$c_year)) .'</td>';		
			}
			for ($ii=1; $ii<=7; $ii++) {
				if ($ii==$first_weekday && $i==1) {
					$go = TRUE;
				} elseif ($i > $days_in_month ) {
					$go = FALSE;
				}
				
				// Determine "weekend" class applicability
				$weekend = '';
				if (get_option('start_of_week') == 0) {
					$weekend = ($ii<7&$ii>1?'':' weekend');
				} else {
					$weekend = ($ii<6?'':' weekend');
				}
				
				if ($go) {
					// This box has a date in it, get the events
					$grabbed_events = $this->filter_events($potential_events, $c_year,$c_month,$i,$cat_list);
					$no_events_class = '';
					if (!count($grabbed_events)) {
						$no_events_class = ' no-events';
					}
					$date_timestamp = mktime (0,0,0,$c_month,$i,$c_year);
					$calendar_body .= '	<td class="spiffy-day-' . $i . ' ' . (date("Ymd", $date_timestamp)==date("Ymd",$current_timestamp)?'current-day':'').$weekend.$no_events_class.' day-with-date">';
					if ($toggle_state == 'list') $calendar_body .= '<span class="spiffy-month-name">' . date_i18n("F", mktime(null, null, null, $c_month, 1)) . '</span>';
					$calendar_body .= '<span class="day-number'.$weekend.'">'.$i++.'</span><span class="spiffy-event-group">' . $this->draw_grid_events($grabbed_events) . '</span></td>';
				} else {
					// This box is empty
					$calendar_body .= '	<td class="day-without-date' . $weekend . '">&nbsp;</td>';
				}
			}
			$calendar_body .= '</tr>';
		}

		if ( ($spiffy_calendar->current_options['enable_categories'] == 'true') && ($spiffy_calendar->current_options['category_key_above'] != 'true') ) {
			$calendar_body .= '<tr class="category-key-row"><td colspan="'.$num_columns.'" class="category-key" >' . $this->category_key ($cat_list) . '</td></tr>';
		}

		$calendar_body .= '</table>';

		return $calendar_body;
	}

	/*
	** Produce the code for the category key
	*/
	function category_key ($cat_list = '') {
		global $spiffy_calendar;
		
		$row_classes = "";
		if ($spiffy_calendar->current_options['category_filter'] === true) {
			$row_classes .= " spiffy-category-filter-button";
		}
		
		$output = '<table class="spiffy cat-key">';
		$output .= '<colgroup>
	<col style="width:10px; height:10px;">
	<col>
</colgroup>';
		$output .= '
<tr><td colspan="2" class="cat-key-cell"><strong>'.esc_html($spiffy_calendar->current_options['category_plural']).'</strong></td></tr>';
		$filtered_cats = explode(',',$cat_list);
		foreach($this->categories as $cat_detail) {
			if ( ($cat_list == '') || (in_array($cat_detail->category_id, $filtered_cats))) {
				$output .= '<tr class="'.esc_html($row_classes).'" data-category="'.esc_html($cat_detail->category_id).'"><td style="background-color:' . esc_html($cat_detail->category_colour) . '; " class="cat-key-cell"></td>
<td class="cat-key-cell" data-category="'.esc_html($cat_detail->category_id).'">&nbsp;'.esc_html($cat_detail->category_name).'</td></tr>';
			}
		}
		$output .= '</table>';		
		
		return $output;
	}
	
	/*
	** Comparison functions for building the calendar select options
	*/
	function calendar_month_comparison($displayed_month, $month) {
		return ($displayed_month == $month)? ' selected="selected"' : '';
	}

	function calendar_year_comparison($c_year, $year) {
		return ($c_year == $year)? ' selected="selected"' : '';
	}

	/*
	** Draw an event in the specified style
	*/
	function draw_event ( $event, $style, $date_timestamp ) {
		if ($style == 'Expanded') {
			return $this->draw_event_expanded ($event);
		} else if ($style == 'Columns') {
			return $this->draw_event_column ($event, $date_timestamp);
		} else {
			return $this->draw_event_popup ($event, 'list');
		}
	}
	
	/*
	** Draw an event to the screen in column format
	** $event = full event
	** $date = start date timestamp
	*/
	function draw_event_column($event, $date_timestamp = '') {
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields;

		$this->get_all_categories();

		$cat_css = '';
		$cat_class = '';
		$cat_name_prefix = '';
		if ($spiffy_calendar->current_options['enable_categories'] == 'true') {
			foreach ($this->categories as $cat_details) {
				if ($cat_details->category_id == $event->event_category) {
					$cat_css = ' style="border-bottom: solid 4px ' . esc_html($cat_details->category_colour) . ';"';
					$cat_class = ' category_' . $cat_details->category_id;
					if ($spiffy_calendar->current_options['category_name_display'] == 'true') {
						$cat_name_prefix = esc_html(stripslashes($cat_details->category_name)) . ' - ';
					}
					break;
				}
			}
		}

		// Get time formatted
		if ($event->event_all_day != "T") {
			$time = date(get_option('time_format'), strtotime($event->event_time));
		} else {
			$time = "";
		}
		if ($event->event_end_time != "00:00:00") {
			$end_time = date(get_option('time_format'), strtotime($event->event_end_time));
		} else {
			$end_time = "";
		}

		$details = '<span class="spiffy-column-event' . $cat_class . ' spiffy-clearfix spiffy-eventid-' . $event->event_id . '">';

		// Image goes first in the column
		if ($event->event_image > 0) {
			$image = wp_get_attachment_image_src( $event->event_image, 'medium');
			$details .= '<span class="spiffy-img"><img src="' . $image[0] . '" alt="" /></span>';
		}


		$details .= '<span class="spiffy-title-meta spiffy-clearfix"' . $cat_css . '>';
		if ($date_timestamp != '') {
			$details .= '<span class="spiffy-column-date">';
			$details .= '<span class="spiffy-column-day-begin">' . 
							date_i18n('j', $date_timestamp) . 
							'</span><span class="spiffy-column-month-begin">' . 
							date_i18n('M', $date_timestamp) . 
							'</span>';
			$details .= '</span>';
		}
		$details .= '<span class="spiffy-title">';
		if ($event->event_link != '') { 
			$linkurl = esc_url(stripslashes($event->event_link)); 
			if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
				$target = ' target="_blank"';
			} else {
				$target = '';
			}
			$details .= '<a href="'.$linkurl.'" '.$target.'>';
		}

		$details .= $cat_name_prefix . esc_html(stripslashes($event->event_title));
		if ($event->event_link != '') { 
			$details .= '</a>';
		}

		$details .= '</span></span>';
		$details .= '<span class="spiffy-desc">' . $this->format_desc($event->event_desc) . '</span>';
		$details .= '<span class="spiffy-meta">';
		if ($spiffy_calendar->current_options['display_author'] == 'true') {
			if ($event->event_author > 0) {
				$e = get_userdata(stripslashes($event->event_author));
				$details .= '<span class="spiffy-author"><span>'.__('Posted by', 'spiffy-calendar').':</span> '.$e->display_name . '</span>';
			}
		}
		$details .= '<span class="spiffy-location">' . $this->format_location($event->event_location, $event->event_link_location) . '</span>';

		if (($date_timestamp != '') && ($event->event_begin != $event->event_end)) {
			$details .= '<span class="spiffy-column-date">';
			$details .= date_i18n('M j', $event->begin_timestamp) . ' - ';

			$end = $event->begin_timestamp + strtotime($event->event_end) - strtotime($event->event_begin);
			$details .= date_i18n('M j', $end);
			$details .= '</span>';
		}
		
		if ($event->event_all_day != "T") {
			$details .= '<span class="spiffy-time">'. $time;
			if ($event->event_end_time != "00:00:00") {
				$details .= ' - ' . $end_time;
			}
			$details .= '</span>';
		}

		if ($event->event_link != '') { 
			$linkurl = esc_url(stripslashes($event->event_link)); 
			if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
				$target = ' target="_blank"';
			} else {
				$target = '';
			}
			$details .= '<span class="spiffy-link"><a href="' . $linkurl . '" ' . $target . '>' . $spiffy_calendar->current_options['more_details'] . '</a></span>';
		}

		if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
			$details .= $spiffycal_custom_fields->view($event);
		}
		if ($spiffy_calendar->current_options['link_google_cal'] == 'true') $details .= $this->format_google_link($event);

		$details .= '</span>'; // end spiffy-meta
		
		$details .= '</span>';
		return $details;
	}

	/*
	** Draw an event to the screen in expanded format
	*/
	function draw_event_expanded($event) {
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields;

		$this->get_all_categories();

		$cat_css = '';
		$cat_class = '';
		$cat_name_prefix = '';
		if ($spiffy_calendar->current_options['enable_categories'] == 'true') {
			foreach ($this->categories as $cat_details) {
				if ($cat_details->category_id == $event->event_category) {
					$cat_css = ' style="color:' . esc_html($cat_details->category_colour) . ';"';
					$cat_class = ' category_' . $cat_details->category_id;
					if ($spiffy_calendar->current_options['category_name_display'] == 'true') {
						$cat_name_prefix = esc_html(stripslashes($cat_details->category_name)) . ' - ';
					}
					break;
				}
			}
		}

		// Get time formatted
		if ($event->event_all_day != "T") {
			$time = date(get_option('time_format'), strtotime($event->event_time));
		} else {
			$time = "";
		}
		if ($event->event_end_time != "00:00:00") {
			$end_time = date(get_option('time_format'), strtotime($event->event_end_time));
		} else {
			$end_time = "";
		}

		$details = '<span class="spiffy-expanded-event' . $cat_class . ' spiffy-clearfix spiffy-eventid-' . $event->event_id . '">';
		$details .= '<span class="spiffy-title"' . $cat_css . '>';
		if ($event->event_link != '') { 
			$linkurl = esc_url(stripslashes($event->event_link)); 
			if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
				$target = ' target="_blank"';
			} else {
				$target = '';
			}
			$details .= '<a href="'.$linkurl.'" '.$cat_css.$target.'>';
		}

		$details .= $cat_name_prefix . esc_html(stripslashes($event->event_title));
		if ($event->event_link != '') { 
			$details .= '</a>';
		}
		$details .= '</span><span class="spiffy-meta">';
		if ($event->event_all_day != "T") {
			$details .= '<span class="spiffy-time">'. $time;
			if ($event->event_end_time != "00:00:00") {
				$details .= ' - ' . $end_time;
			}
			$details .= '</span>';
		}
		if ($spiffy_calendar->current_options['display_author'] == 'true') {
			if ($event->event_author > 0) {
				$e = get_userdata(stripslashes($event->event_author));
				$details .= '<span class="spiffy-author"><span>'.__('Posted by', 'spiffy-calendar').':</span> '.$e->display_name . '</span>';
			}
		}
		$details .= '</span>'; // end spiffy-meta
		if ($event->event_image > 0) {
			$image = wp_get_attachment_image_src( $event->event_image, 'medium');
			$details .= '<span class="spiffy-img"><img src="' . $image[0] . '" alt="" /></span>';
		}
		$details .= '<span class="spiffy-desc">' . $this->format_desc($event->event_desc) . '</span>';
		$details .= '<span class="spiffy-location">' . $this->format_location($event->event_location, $event->event_link_location) . '</span>';
		if ($event->event_link != '') { 
			$linkurl = esc_url(stripslashes($event->event_link)); 
			if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
				$target = ' target="_blank"';
			} else {
				$target = '';
			}
			$details .= '<span class="spiffy-link"><a href="' . $linkurl . '" ' . $cat_css . $target . '>' . $spiffy_calendar->current_options['more_details'] . '</a></span>';
		}

		if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
			$details .= $spiffycal_custom_fields->view($event);
		}

		if ($spiffy_calendar->current_options['link_google_cal'] == 'true') $details .= $this->format_google_link($event);

		
		$details .= '</span>';
		return $details;
	}
	
	/*
	**	Draw an event to the screen in popup format
	**
	**  type = 'list' or 'grid' to determine if reverse category colouring should occur
	*/
	function draw_event_popup($event, $type)
	{
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields;

		$this->get_all_categories();

		$style_main = '';
		$style_popup = '';
		$cat_class = '';
		$cat_name_prefix = '';
		if ($spiffy_calendar->current_options['enable_categories'] == 'true') {
			foreach ($this->categories as $cat_details) {
				if ($cat_details->category_id == $event->event_category) {
					$style_popup = 'style="color:' . esc_html($cat_details->category_colour) . ';" ';
					$cat_class = 'category_' . $cat_details->category_id;
					if (($spiffy_calendar->current_options['category_bg_color'] === true) && ($type == 'grid')) {
						$style_main = 'style="color: '. $spiffy_calendar->current_options['category_text_color'] . '; background:' . esc_html($cat_details->category_colour) . ';" ';
						$cat_class .= " category-bg";
					} else {
						$style_main = $style_popup;
					}
					if ($spiffy_calendar->current_options['category_name_display'] == 'true') {
						$cat_name_prefix = esc_html(stripslashes($cat_details->category_name)) . ' - ';
					}
					break;
				}
			}
		}

		// Get time formatted
		if ($event->event_all_day != "T") {
			$time = date(get_option('time_format'), strtotime($event->event_time));
		} else {
			$time = "";
		}
		if ($event->event_end_time != "00:00:00") {
			$end_time = date(get_option('time_format'), strtotime($event->event_end_time));
		} else {
			$end_time = "";
		}

		if ($event->event_image > 0) {
			$image = wp_get_attachment_image_src( $event->event_image, 'thumbnail');
		}
		
		// Gather link settings (event & location map)
		$target = '';
		$linkurl = '';
		$linkmap = $event->event_link_location;
		if ($event->event_link != '') {
			// The event has a link
			$linkurl = esc_url(stripslashes($event->event_link)); 
			if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
				$target = ' target="_blank"';
			} 
		}

		// Construct the event header html
		$details = '<span class="calnk ' . $cat_class . ' spiffy-eventid-' . $event->event_id . '"><span onclick="" class="calnk-link" ' . $style_main . '><span class="calnk-box">'; // span 1
		if ($event->event_link != '') $details .= '<a href="' . $linkurl . '" ' . $style_main . $target . ' >';
		$details .= '<span class="spiffy-title">' . $cat_name_prefix . esc_html(stripslashes($event->event_title)) . '</span>';
		if ($event->event_link != '') $details .= '</a>';
		
		if ($spiffy_calendar->current_options['display_detailed'] == 'true') {
			if ($time != '') {
				$details .= '<span class="calnk-time"><br />' . $time; // span 2
				if ($event->event_end_time != "00:00:00") {
					$details .= ' - ' . $end_time;
				}
				$details .= '</span>'; // end span 2
			}
			if ($event->event_image > 0) {
				$details .= '<br /><img alt="" class="calnk-icon" src="' . $image[0] . '" />';
			}
		}

		// Add the popup html
		$details .= '<span class="spiffy-popup" '.$style_popup.'>';  // span 3
		if ($event->event_link != '') $details .= '<a href="' . $linkurl . '" ' . $target . ' >';
		$details .= '<span class="event-title" ' . $style_popup . '>' . $cat_name_prefix . esc_html(stripslashes($event->event_title)) . '</span>';
		$details .= '<span class="event-title-break"></span>';
		if ($event->event_all_day != "T") {
			$details .= '<span class="event-title-time"><strong>'.__('Time','spiffy-calendar').':</strong> ' . $time;  // span 4
			if ($event->event_end_time != "00:00:00") {
				$details .= ' - ' . $end_time;
			}
			$details .= '</span><br />'; // end span 4
		}
		if ($event->event_image > 0) {
			$details .= '<img src="' . $image[0] . '" alt="" />';
		}
		if ($spiffy_calendar->current_options['display_author'] == 'true') {
			if ($event->event_author > 0) {
				$e = get_userdata(stripslashes($event->event_author));
				$details .= '<strong>'.__('Posted by', 'spiffy-calendar').':</strong> '.$e->display_name;
			}
		}
		if ($spiffy_calendar->current_options['display_author'] == 'true' || $event->event_all_day != "T") {
			$details .= '<span class="event-content-break"></span>';
		}
		
		$details .= $this->format_desc($event->event_desc);
		if ($event->event_link != '') $details .= '</a>';
		$details .= $this->format_location($event->event_location, $linkmap);
		if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
			$details .= $spiffycal_custom_fields->view($event);
		}
		if ($spiffy_calendar->current_options['link_google_cal'] == 'true') $details .= $this->format_google_link($event);
		$details .= '</span></span></span>';
		// end span 1

		$details .= '</span>'; // end span 3

		return $details;
	}

	/*
	** Used to draw multiple events in a responsive grid layout
	*/
	function draw_grid_events($events)
	{
		// We need to sort arrays of objects by time
		usort($events, array($this, 'time_cmp'));
		$output = '';
		// Now process the events
		foreach($events as $event) {
			$output .= $this->draw_event_popup($event, 'grid').'
';
		}
		return $output;
	}

	/*
	** Function to provide date of the nth day passed (eg. 2nd Sunday)
	*/
	function dt_of_sun($date,$instance,$day)
	{
		$plan = array();
		$plan['Mon'] = 1;
		$plan['Tue'] = 2;
		$plan['Wed'] = 3;
		$plan['Thu'] = 4;
		$plan['Fri'] = 5;
		$plan['Sat'] = 6;
		$plan['Sun'] = 7;
		$proper_date = date('Y-m-d',strtotime($date));
		$begin_month = substr($proper_date,0,8).'01'; 
		$offset = $plan[date('D',strtotime($begin_month))]; 
		$result_day = 0;
		$recon = 0;
		if (($day-($offset)) < 0) { $recon = 7; }
		if ($instance == 1) { $result_day = $day-($offset-1)+$recon; }
		else if ($instance == 2) { $result_day = $day-($offset-1)+$recon+7; }
		else if ($instance == 3) { $result_day = $day-($offset-1)+$recon+14; }
		else if ($instance == 4) { $result_day = $day-($offset-1)+$recon+21; }
		else if ($instance == 5) { $result_day = $day-($offset-1)+$recon+28; }
		return substr($proper_date,0,8).$result_day;
	}

	/*
	** Draw the event edit form for front end or back end use
	**
	** On frontend forms it omits: author and event_status, and blanks hide event fields
	**
	** $data = data to pre-populate in the form, from user input or from an event read from the db
	** $frontend = true if the form is displayed on the front end
	** $args = array of options to enable/disable fields from the form
	*/
	function event_edit_form ($data, $frontend = false, $include_recurring = 'true', $include_images = 'true') {
		// for backwards compatibility with bonus add-ons prior to version 3.22
		return $this->event_edit_form_display ( $data, $frontend, array (
					'include_images' => $include_images,
					'include_recurring' => $include_recurring) );
	}
	
	function event_edit_form_display ($data, $frontend = false, $args = array()) {
		global $spiffy_calendar, $spiffy_edit_errors, $wpdb, $wp_version;

		$defaults = array (
				'include_category' => true,
				'include_description' => true,
				'include_images' => true,
				'include_link' => true,
				'include_location' => true,
				'include_recurring' => true,
				'include_times' => true,
			);
			 
		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );	 	
				
		$hidden = '';
		ob_start( );
?>		
<table class="spiffy-fe-submit" cellpadding="5" cellspacing="5">


<tr <?php if ( isset($spiffy_edit_errors['event_title']) ) echo 'class="error-message"';?>>
<td><legend><?php echo esc_html($spiffy_calendar->current_options['title_label']); ?></legend></td>
<td><input type="text" name="event_title" size="60" maxlength="60" required
	value="<?php if ( !empty($data) ) echo esc_html(stripslashes($data->event_title)); ?>" />
  <span class="description spiffy-form-desc"><?php _e('Maximum 60 characters.','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_title']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_title'].'</td></tr>'; ?>


<?php if ($args['include_description'] == 'true') { ?>

<tr class="spiffy-edit-description">
<td class="spiffy-edit-label" style="vertical-align:top;"><legend><?php _e('Event Description','spiffy-calendar'); ?></legend></td>
<td>
<textarea class="spiffy-edit-data" name="event_desc" rows="5" cols="50"><?php if ( !empty($data) ) echo esc_textarea(stripslashes($data->event_desc)); ?>
</textarea></td>
</tr>

<?php } else {
$hidden .= '
<input type="hidden" name="event_desc" value="" />
';	
} ?>

<?php if ($args['include_location'] == 'true') { ?>

<tr class="spiffy-edit-location">
<td class="spiffy-edit-label" style="vertical-align:top;"><legend><?php _e('Event Location','spiffy-calendar'); ?></legend></td>
<td>
<textarea class="spiffy-edit-data" name="event_location" rows="5" cols="50"><?php if ( !empty($data) ) echo esc_textarea(stripslashes($data->event_location)); ?>
</textarea><br />
<input class="spiffy-edit-data2" type="checkbox" name="link_location" <?php if ( !empty($data) && ($data->event_link_location == 'T')) echo 'checked'; ?> /> <span class="spiffy-edit-description2"><?php _e('Include link to Google map','spiffy-calendar'); ?></span>
</td>
</tr>

<?php } else {
$hidden .= '
<input type="hidden" name="event_location" value="" />
';	
} ?>

<?php if ($args['include_category'] == 'true') { ?>

<tr class="spiffy-edit-category" <?php if ( isset($spiffy_edit_errors['event_category']) ) echo 'class="error-message"';?>>
<td class="spiffy-edit-label" ><legend><?php echo esc_html($spiffy_calendar->current_options['category_singular']); ?></legend></td>
<td>	 <select class="spiffy-edit-data" name="event_category">
 <?php
		// Grab all the categories and list them
		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE;
		if ($spiffy_calendar->current_options['alphabetic_categories'] == 'true') $sql .= " ORDER BY category_name";
		$cats = $wpdb->get_results($sql);
		foreach($cats as $cat) {
			 echo '<option value="'.$cat->category_id.'"';
			 if (!empty($data)) {
				if ($data->event_category == $cat->category_id) {
					echo 'selected="selected"';
				}
			 }
			 echo '>' . esc_html(stripslashes($cat->category_name)) . '</option>';
		}
?>

	</select>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_category']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_category'].'</td></tr>'; ?>

<?php } else {
$hidden .= '
<input type="hidden" name="event_category" value="1" />
';	
} ?>

<?php
if (current_user_can( 'manage_options' ) && !$frontend) {
	?>
	<tr>
	<td><legend><?php _e( 'Author', 'spiffy-calendar' ); ?></legend></td>
	<td>
	<?php
		if (version_compare($wp_version, '4.5', '<')) {
			$show = 'display_name';
		} else {
			$show = 'display_name_with_login';
		}
		wp_dropdown_users( array(
			'name' => 'event_author',
			'selected' => (empty($data) || !isset($data->event_author) || ($data->event_author == 0)) ? get_current_user_id() : $data->event_author,
			'show' => $show,
		) );
	?>
	</td>
	</tr>
	<?php
} 


if (!$frontend) { ?>
<tr <?php if ( isset($spiffy_edit_errors['event_status']) ) echo 'class="error-message"';?>>
<td><legend><?php _e('Status', 'spiffy-calendar'); ?></legend></td>
<td>
 <?php
		$selected_publish = $selected_draft = $selected_review = '';
		if (isset($data)) {
			if ($data->event_status == "P") {
				$selected_publish = 'selected';
			} else if ($data->event_status == "D") {
				$selected_draft = 'selected';
			} else if ($data->event_status == "R") {
				$selected_review = 'selected';
			}
		}
	?>
	<select id="spiffy-event-status" name="event_status" class="input">
		<option <?php echo $selected_publish; ?> value="P"><?php _e('Publish', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_draft; ?> value="D"><?php _e('Draft', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_review; ?> value="R"><?php _e('Pending review', 'spiffy-calendar') ?></option>
	</select>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_status']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_status'].'</td></tr>'; ?>
<?php } ?>

<?php if ($args['include_link'] == 'true') { ?>

<tr class="spiffy-edit-link" <?php if ( isset($spiffy_edit_errors['event_link']) ) echo 'class="error-message"';?>>
<td class="spiffy-edit-label" ><legend><?php _e('Event Link','spiffy-calendar'); ?></legend></td>
<td class="spiffy-edit-data">
	<input type="text" name="event_link" size="40" value="<?php if ( !empty($data) ) echo esc_url(stripslashes($data->event_link)); ?>" />&nbsp;
	<span class="spiffy-form-desc description"><?php _e('Optional, set blank if not required.','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_link']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_link'].'</td></tr>'; ?>

<?php } else {
$hidden .= '
<input type="hidden" name="event_link" value="" />
';	
} ?>

<tr <?php if ( isset($spiffy_edit_errors['event_begin']) ) echo 'class="error-message"';?>>
<td><legend><?php _e('Start Date','spiffy-calendar'); ?></legend></td>
<td>
	<input type="text" id="event_begin" name="event_begin" class="spiffy-date-field" size="12"
	value="<?php 
		if ( !empty($data) ) {
			echo esc_html($data->event_begin);
		} else {
			echo date("Y-m-d",current_time('timestamp'));
		} 
	?>" />
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_begin']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_begin'].'</td></tr>'; ?>


<tr <?php if ( isset($spiffy_edit_errors['event_end']) ) echo 'class="error-message"';?>>
<td><legend><?php _e('End Date','spiffy-calendar'); ?></legend></td>
<td><input type="text" id="event_end" name="event_end" class="spiffy-date-field" size="12"
	value="<?php 
		if ( !empty($data) ) {
			echo esc_html($data->event_end);
		} else {
			echo date("Y-m-d",current_time('timestamp'));
		} 
	?>" />
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_end']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_end'].'</td></tr>'; ?>

<?php if ($args['include_times'] == 'true') { ?>

<tr <?php if ( isset($spiffy_edit_errors['event_time']) ) echo 'class="error-message"';?>>
<td><legend><?php _e('Start Time (hh:mm)','spiffy-calendar'); ?></legend></td>
<td>
	<input type="text" id="event_time" name="event_time" size=12
	value="<?php 
	if ( !empty($data) ) {
		if ( isset($data->event_all_day) && ($data->event_all_day == "T")) {
			echo '';
		} else {
			echo date(get_option('time_format'),strtotime($data->event_time));
		}
	} else {
		//echo date("a:i a",current_time('timestamp')); //defaulting to current time is not helpful
	}
	?>" />&nbsp;<span class="spiffy-form-desc description"><?php _e('Optional, set blank if not required. Ignored for "Hide Events".','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_time']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_time'].'</td></tr>'; ?>


<tr <?php if ( isset($spiffy_edit_errors['event_end_time']) ) echo 'class="error-message"';?>>
<td><legend><?php _e('End Time (hh:mm)','spiffy-calendar'); ?></legend></td>
<td>
	<input type="text" id="event_end_time" name="event_end_time" size=12
	value="<?php 
	if ( !empty($data) ) {
		if ($data->event_end_time == "00:00:00") {
			echo '';
		} else {
			echo date(get_option('time_format'),strtotime($data->event_end_time));
		}
	} 
	?>" />&nbsp;<span class="spiffy-form-desc description"><?php _e('Optional, set blank if not required. Ignored for "Hide Events".','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_end_time']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_end_time'].'</td></tr>'; ?>

<?php } else {
$hidden .= '
<input type="hidden" name="event_begin_time" value="" />
<input type="hidden" name="event_end_time" value="" />
';	
} ?>

<?php if ($args['include_recurring'] == 'true') { ?>
<tr>
<td style="vertical-align:top;"><legend><?php _e('Recurring Events','spiffy-calendar'); ?></legend></td>
<td>
	<?php
	if (isset($data)) {
		if ($data->event_repeats != NULL) {
			$repeats = $data->event_repeats;
		} else {
			$repeats = 0;
		}
	} else {
		$repeats = 0;
	}

	$selected_s = '';
	$selected_w = '';
	$selected_b = '';
	$selected_m = '';
	$selected_y = '';
	$selected_u = '';
	$selected_d = '';
	$recur_multiplier = 1;
	if (isset($data)) {
		if ($data->event_recur == "S") {
			$selected_s = 'selected';
		} else if ($data->event_recur == "W") {
			$selected_w = 'selected';
		} else if ($data->event_recur == "M") {
			$selected_m = 'selected';
		} else if ($data->event_recur == "Y") {
			$selected_y = 'selected';
		} else if ($data->event_recur == "U") {
			$selected_u = 'selected';
		} else if ($data->event_recur == "D") {
			$selected_d = 'selected';
		}
		$recur_multiplier = $data->event_recur_multiplier;
	}
	?>
	<span  <?php if ( isset($spiffy_edit_errors['event_recur']) ) echo 'class="error-message"';?>><?php _e('Interval', 'spiffy-calendar');?>:</span>&nbsp;
	<select id="spiffy-event-recur" name="event_recur" class="input">
		<option <?php echo $selected_s; ?> value="S"><?php _e('None', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_w; ?> value="W"><?php _e('Weekly', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_m; ?> value="M"><?php _e('Months (date)', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_u; ?> value="U"><?php _e('Months (day)', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_y; ?> value="Y"><?php _e('Years', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_d; ?> value="D"><?php _e('Custom Days', 'spiffy-calendar') ?></option>						
	</select>&nbsp;<span id="spiffy-custom-days" <?php if ( isset($spiffy_edit_errors['event_recur_multiplier']) ) echo 'class="error-message"';?>>
	<?php _e('Repeat every','spiffy-calendar'); ?>
	&nbsp;<input id="spiffy-custom-days-input" type="number" step="1" min="1" max="199" name="event_recur_multiplier" value="<?php echo esc_html($recur_multiplier); ?>" />
	&nbsp;<?php _e('days', 'spiffy-calendar'); ?></span><br />
	<span <?php if ( isset($spiffy_edit_errors['event_repeats']) ) echo 'class="error-message"';?> ><?php _e('Repeats','spiffy-calendar'); ?> 
	&nbsp;<input type="number" name="event_repeats" size="3" min="0" value="<?php echo esc_html($repeats); ?>" />&nbsp;<?php echo __('times','spiffy-calendar'); ?>.</span>
	<p class="spiffy-form-desc description"><?php _e('Entering 0 means forever. Where the recurrence interval is left at none, the event will not recur.','spiffy-calendar'); ?></p>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_recur']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_recur'].'</td></tr>'; ?>
<?php if ( isset($spiffy_edit_errors['event_recur_multiplier']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_recur_multiplier'].'</td></tr>'; ?>
<?php if ( isset($spiffy_edit_errors['event_repeats']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_repeats'].'</td></tr>'; ?>
<?php } else {
	$hidden .= '
<input type="hidden" name="event_recur" value="S" />
<input type="hidden" name="event_recur_multiplier" value="1" />		
<input type="hidden" name="event_repeats" value="0" />';
}
?>


<?php if (!$frontend) { ?>
<tr <?php if ( isset($spiffy_edit_errors['event_hide_events']) ) echo 'class="error-message"';?>>
<td style="vertical-align:top;"><legend><?php _e('Hide Events','spiffy-calendar'); ?></legend></td>
<td> 
<?php
	if (isset($data)) {
		if ($data->event_hide_events != NULL) {
			$hide_events = $data->event_hide_events;
		} else {
			$hide_events = 'F';
		}
	} else {
		$hide_events = 'F';
	}

	$selected_he_t = '';
	$selected_he_f = '';
	if (isset($data)) {
		if ($data->event_hide_events == 'T') {
			$selected_he_t = 'selected="selected"';
		} else if ($data->event_hide_events == 'F') {
			$selected_he_f = 'selected="selected"';
		}
	}
	if (isset($data)) {
		if ($data->event_show_title != NULL) {
			$show_title = $data->event_show_title;
		} else {
			$show_title = 'F';
		}
	} else {
		$show_title = 'F';
	}

	$selected_st_t = '';
	$selected_st_f = '';
	if (isset($data)) {
		if ($data->event_show_title == 'T') {
			$selected_st_t = 'selected="selected"';
		} else if ($data->event_show_title == 'F') {
			$selected_st_f = 'selected="selected"';
		}
	}
?>
	<select name="event_hide_events" class="input">
		<option <?php echo $selected_he_f; ?> value='F'><?php _e('False', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_he_t; ?> value='T'><?php _e('True', 'spiffy-calendar') ?></option>
	</select> 
	<span class="spiffy-form-desc description"><?php _e('Entering True means other events of this category will be hidden for the specifed day(s).','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_hide_events']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_hide_events'].'</td></tr>'; ?>


<tr <?php if ( isset($spiffy_edit_errors['event_show_title']) ) echo 'class="error-message"';?>>
<td style="vertical-align:top;"><legend>&nbsp;</legend></td>
<td><?php _e('Show Title','spiffy-calendar'); ?>&nbsp;
<select name="event_show_title" class="input">
		<option <?php echo $selected_st_f; ?> value='F'><?php _e('False', 'spiffy-calendar') ?></option>
		<option <?php echo $selected_st_t; ?> value='T'><?php _e('True', 'spiffy-calendar') ?></option>
	</select>
	<span class="spiffy-form-desc description"><?php _e('Entering True means the title of this event will be displayed. This is only used if Hide Events is True.','spiffy-calendar'); ?></span>
</td>
</tr>
<?php if ( isset($spiffy_edit_errors['event_show_title']) ) echo '<tr><td class="error-message" colspan="2">'.$spiffy_edit_errors['event_show_title'].'</td></tr>'; ?>
<?php } else { 
	$hidden .= '
<input type="hidden" name="event_hide_events" value="F" />
<input type="hidden" name="event_show_title" value="T" />
<input type="hidden" name="event_author" value="0" />';
} ?>


<?php if ($args['include_images'] == 'true') {

	// display current image, if any
	$image_url = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
	$image_id = "";
	
	if ( !empty($data) && isset($data->event_remove_image) && ($data->event_remove_image == 'true') ) {
		$data->event_image = ''; // unlink image was requested so go ahead and clear the selection		
		$data->event_remove_image = 'false';
	}

	if ( !empty($data) ) {
		if ($data->event_image > 0) {
			$image_id = $data->event_image;
			$image = wp_get_attachment_image_src( $data->event_image, 'thumbnail');
			$image_url = $image[0];
		}
	}
	$checked = '';
	$image_input = '';
	
	if (current_user_can ('upload_files')) {
		$image_input = '
<button class="spiffy-image-button">Select image</button>
<input type="hidden" class="spiffy-image-input" name="event_image" size="80" value="' . $image_id . '" />';
	} else {
		if  ( empty($data) || ($data->event_image == 0)) {
			$image_input = '
<input type="file" name="event_image" multiple="false" />';
		} else {
			$image_value = empty($data) ? '' : $data->event_image;
			$image_input = '
<input type="hidden" name="event_image" value="'.$image_value.'" />';
		}
	}
	?>
<tr>
<td><legend><?php _e('Image','spiffy-calendar'); ?></legend></td>
<td>
	<?php echo $image_input; ?>
	<img class="spiffy-image-view" style="max-width: 200px; height: auto;" src="<?php echo $image_url; ?>" />
	&nbsp;<input <?php echo $checked; ?> type="checkbox" name="event_remove_image" value="true"> <?php _e('Remove image selection','spiffy-calendar'); ?>
</td>
</tr>
<?php } else {
	$hidden .= '
<input type="hidden" name="event_image" value="" />
<input type="hidden" name="event_remove_image" value="false" />';
}
?>
<?php 
/* Add custom fields */
global $spiffycal_bonus_settings;
if ( $spiffy_calendar->bonus_addons_active() && method_exists ($spiffycal_bonus_settings, 'custom_fields_edit') ) {
	echo $spiffycal_bonus_settings->custom_fields_edit($data);
}
?>
</table>
<?php		
		$output = ob_get_clean( );
		return $output . $hidden;
	}
	
	/*
	** Return true if the given event will occur on the given target date timestamp
	*/
	function event_will_happen ($date_timestamp, $event, $num_days, $event_begin_timestamp, $event_end_timestamp) {

		
		// Quick simple test - does the target date fall within the first occurrence of the event?
		if (($event_begin_timestamp <= $date_timestamp) && ($event_end_timestamp >= $date_timestamp)) {
			return true;
		}
				
		// Check that the target is beyond the start date of the event and before the end of the last recurrence
		if ($event_begin_timestamp <= $date_timestamp) {
			if ( ($event->event_repeats == 0) ||			// event recurs infinitely OR
				 ($event_end_timestamp + $num_days*($event->event_repeats)*(24*60*60) >= $date_timestamp) // ensure the target day falls before the end of the last recurrence
				) {
					
				// Calculate number of recurrences from first occurrence to target date
				$num_recurs_from_begin = floor(floor(($date_timestamp - $event_begin_timestamp) / (60 * 60 * 24)) / $num_days);
				$num_recurs_from_begin_remainder = (($date_timestamp - $event_begin_timestamp) / (60 * 60 * 24)) % $num_days;
				$num_recurs_from_end = floor(floor(($date_timestamp - $event_end_timestamp) / (60 * 60 * 24)) / $num_days);
				$num_recurs_from_end_remainder = floor(floor(($date_timestamp - $event_end_timestamp) / (60 * 60 * 24)) % $num_days);
				
				// Determine if a recurrence of the event falls on the target date
				 if (($num_recurs_from_begin_remainder == 0) || ($num_recurs_from_end_remainder == 0)) {
					// target date is exactly "n" recurrences from the first occurrence begin or end date
					return true;													
				} else if ($num_recurs_from_begin > $num_recurs_from_end) {
					// target date falls between the event begin and end of a recurrence
					return true;													
				}
			}
		}
		return false;
	}	

	/*
	** Filter all events already queried from a date range for a specific date
	*/
	function filter_events(array &$events,$y,$m,$d)	{
	
		// Get the date format right
		$date = $y . '-' . $m . '-' . $d;
		$date_timestamp = strtotime($date);
		$arr_events = array();

		if (!empty($events)) {
			foreach($events as $event) {
				// get timestamp event times
				$event_begin_timestamp = strtotime($event->event_begin);
				$event_end_timestamp = strtotime($event->event_end);
				
				// Save event start timestamp to use in displays. This may be overwritten below if the event is recurring.
				$event->begin_timestamp = $event_begin_timestamp;

				if ($event->type == 'Normal') {
					if ( ($event_begin_timestamp <= $date_timestamp) && ($event_end_timestamp >= $date_timestamp)) {
						array_push($arr_events, $event);
					}
 				} else if ($event->type == 'Yearly') {
					// Note - we can't use the basic recurrence check here due to leap days
					
					// we know the year is good, check if the event recurrence ends before the target date
					if ($event->event_repeats != 0) {
						$final_recurrence_end_timestamp = strtotime('+'.strval($event->event_repeats).' years', $event_end_timestamp);
						if ($final_recurrence_end_timestamp < $date_timestamp) {
							continue; // the final recurrence ends before the target date
						}
					}

					// Store this event occurrence start date timestamp
					$year_target = date('Y', $date_timestamp);
					$event->begin_timestamp = strtotime ($year_target.'-'.date('m-d',$event_begin_timestamp));

					// now check the date ranges
					$year_begin = date('Y',$event_begin_timestamp);
					$year_end = date('Y',$event_end_timestamp);
					
					if ($year_begin == $year_end) {
						// if the event occurs within one year, perform the basic test
						if (date('m-d',$event_begin_timestamp) <= date('m-d',$date_timestamp) &&
							 date('m-d',$event_end_timestamp) >= date('m-d',$date_timestamp)) {
							array_push($arr_events, $event);
						}
					} else if ($year_begin < $year_end) {
						// if the event wraps around a year, the test is altered appropriately
						if (date('m-d',$event_begin_timestamp) <= date('m-d',$date_timestamp) ||
							 date('m-d',$event_end_timestamp) >= date('m-d',$date_timestamp)) {
							array_push($arr_events, $event);
						}
					}
 				} else if ($event->type == 'Monthly') {
					// Note - we can't use the basic recurrence check here due to month length variations
					
					// we know the year is good, check if the event recurrence ends before the target date
					if ($event->event_repeats != 0) {
						$final_recurrence_end_timestamp = strtotime('+'.strval($event->event_repeats).' months', $event_end_timestamp);
						if ($final_recurrence_end_timestamp < $date_timestamp) {
							continue; // the final recurrence ends before the target date
						}
					}
					
					// Store this event occurrence start date timestamp
					$year_target = date('Y', $date_timestamp);
					$month_target = date('m', $date_timestamp);
					$event->begin_timestamp = strtotime ($year_target.'-'.$month_target.'-'.date('d',$event_begin_timestamp));

					//now check the date ranges for this event's dates
					$month_begin = date('m',$event_begin_timestamp);
					$month_end = date('m',$event_end_timestamp);

					if (($month_begin == $month_end) && ($event_begin_timestamp <= $date_timestamp)) {
						if (date('d',$event_begin_timestamp) <= date('d',$date_timestamp) &&
							date('d',$event_end_timestamp) >= date('d',$date_timestamp)) {
							array_push($arr_events, $event);
	 					}
				 	} else if (($month_begin < $month_end) && ($event_begin_timestamp <= $date_timestamp)) {
						if ( ($event->event_begin <= date('Y-m-d',$date_timestamp)) 
							&& (date('d',$event_begin_timestamp) <= date('d',$date_timestamp) 
							|| date('d',$event_end_timestamp) >= date('d',$date_timestamp)) ) {
							array_push($arr_events, $event);
	 					}
				 	}
 				} else if ($event->type == 'MonthSun') {
					// we know the year is good, check if the event recurrence ends before the target date
					if ($event->event_repeats != 0) {
						$final_recurrence_end_timestamp = strtotime('+'.strval($event->event_repeats).' months', $event_end_timestamp);
						$final_recurrence_end_timestamp += 24*60*60*7;	// add one week since this match is by day of week rather than number
						if ($final_recurrence_end_timestamp < $date_timestamp) {
							continue; // the final recurrence ends before the target date
						}
					}
					
					//now check the date ranges for this event's days of week
					$month_begin = date('m',$event_begin_timestamp);
					$month_end = date('m',$event_end_timestamp);

					// Setup some variables and get some values
					$dow = date('w',$event_begin_timestamp);
					if ($dow == 0) { $dow = 7; }
					$start_ent_this = $this->dt_of_sun($date,$this->np_of_day($event->event_begin),$dow);
					$event->begin_timestamp = strtotime($start_ent_this); // Store this event occurrence start date timestamp
					$start_ent_prev = $this->dt_of_sun(date('Y-m-d',strtotime($date.'-1 month')),$this->np_of_day($event->event_begin),$dow);
					$len_ent = $event_end_timestamp-$event_begin_timestamp;

					// The grunt work
					if (($month_begin == $month_end) && ($event_begin_timestamp <= $date_timestamp)) {
						// The checks
						if ($event_begin_timestamp <= $date_timestamp 
							&& $event_end_timestamp >= $date_timestamp) {
							// Handle the first occurrence
							array_push($arr_events, $event);
	 					}
						else if (strtotime($start_ent_this) <= $date_timestamp 
							&& $date_timestamp <= strtotime($start_ent_this)+$len_ent) {
							// Now remaining items 
							array_push($arr_events, $event);
	 					}
				 	} else if (($month_begin < $month_end) && ($event_begin_timestamp <= $date_timestamp)) {
						// The checks
						if ($event_begin_timestamp <= $date_timestamp 
							&& $event_end_timestamp >= $date_timestamp) {
							// Handle the first occurrence
							array_push($arr_events, $event);
	 					} else if (strtotime($start_ent_prev) <= $date_timestamp 
							&& $date_timestamp <= strtotime($start_ent_prev)+$len_ent) {
							 // Remaining items from prev month
							array_push($arr_events, $event);
	 					} else if (strtotime($start_ent_this) <= $date_timestamp 
							&& $date_timestamp <= strtotime($start_ent_this)+$len_ent) {
							// Remaining items starting this month
							array_push($arr_events, $event);
	 					}
				 	}
 				} else if ($event->type == 'Weekly') {
					// Perform basic recurrence test for 7 days
					if ($this->event_will_happen($date_timestamp, $event, 7, $event_begin_timestamp, $event_end_timestamp)) {
						$num_recurs_from_begin = floor(floor(($date_timestamp - $event_begin_timestamp) / (60 * 60 * 24)) / 7);
						$event->begin_timestamp = $event_begin_timestamp + ($num_recurs_from_begin * 7 * 60 * 60 * 24);
						array_push ($arr_events, $event);
					}
 				} else if ($event->type == 'Daily') {
					// Perform basic recurrence test for "event_repeats" days
					if ($this->event_will_happen($date_timestamp, $event, $event->event_recur_multiplier, $event_begin_timestamp, $event_end_timestamp)) {
						$num_recurs_from_begin = floor(floor(($date_timestamp - $event_begin_timestamp) / (60 * 60 * 24)) / $event->event_recur_multiplier);
						$event->begin_timestamp = $event_begin_timestamp + ($num_recurs_from_begin * $event->event_recur_multiplier * 60 * 60 * 24);
						array_push ($arr_events, $event);
					}
				}
			}
		}
		// process the event list
		$hide_event_count = 0;
		foreach($arr_events as $arr_event) {
			// count the number of hide events
			if ($arr_event->event_hide_events == 'T') { $hide_event_count++; }		
		}
		if ($hide_event_count) { // hide_events event found for this date.
			// separate "hide events" from normal events
			$hide_events = array();
			$normal_events = array();
			foreach($arr_events as $arr_event) {
				if ($arr_event->event_hide_events == 'T') {
					array_push($hide_events, $arr_event);
				} else {
					array_push($normal_events, $arr_event);
				}
			}
			// use the show_title flag in the array (not the database) to
			// select which events to show after filtering on hide_events
			foreach($normal_events as $normal_event) {
				$normal_event->event_show_title = 'T';   // initialize
			}
			foreach($normal_events as $normal_event) {
				foreach($hide_events as $hide_event) {
					if ($normal_event->event_category == $hide_event->event_category) {
						// normal event has same category as hide_event: don't show it
						$normal_event->event_show_title = 'F';
						break;   // break out of inner loop
					}
				}
			}
			// create a new array of events to display
			$shown_events = array();
			// show hidden events first on calendar
			foreach($hide_events as $hide_event) {
				if ($hide_event->event_show_title == 'T') {array_push($shown_events, $hide_event);}
			}
			// then show normal events
			foreach($normal_events as $normal_event) {
				if ($normal_event->event_show_title == 'T') {array_push($shown_events, $normal_event);}
			}
			return $shown_events;			
		}
		else { return $arr_events; }
	}

	/*
	**  Sanitize and format the raw description ready for output
	*/
	function format_desc ($desc) {
		$data = apply_filters('spiffy_calendar_desc', wpautop(esc_textarea(stripslashes($desc))));
		return str_replace(array('<p>', '</p>'), array('<span class="ca-desc-p">', '</span>'), $data);
	}
	
	/*
	**  Sanitize and format the link to add to Google calendar
	*/
	function format_google_link ($event) {

		// Set up default timestamp if event was pulled directly from db instead of through usual grab_events/filter_events process
		if (!isset($event->begin_timestamp)) {
			$event->begin_timestamp = strtotime($event->event_begin);
		}
		
		$end_timestamp = $event->begin_timestamp + strtotime($event->event_end) - strtotime($event->event_begin);

		
		if ($event->event_all_day == 'T') {
			// Google end date must be incremented by one day
			$end_all_day = strtotime('+1 day', $end_timestamp);
			$dates = date_i18n('Ymd', $event->begin_timestamp) . '/' . date_i18n('Ymd', $end_all_day);
		} else {
			$begin_hour = date( 'His',strtotime($event->event_time) );
			$end_hour = date( 'His',strtotime($event->event_end_time) );
			$dates = date_i18n('Ymd', $event->begin_timestamp) . 'T' . $begin_hour . '/' . date_i18n('Ymd', $end_timestamp) . 'T' . $end_hour;
		}
		$data = sprintf('<a class="spiffy-google-calendar-link" href="https://www.google.com/calendar/render?
								action=TEMPLATE
								&text=%s
								&dates=%s
								&details=%s
								&location=%s
								&trp=false"
								target="_blank" rel="nofollow">+ Google calendar</a>',
								rawurlencode(sanitize_text_field(stripslashes($event->event_title))),
								$dates,
								rawurlencode(sanitize_text_field(stripslashes($event->event_desc))),
								rawurlencode(esc_textarea(stripslashes($event->event_location)))
						);
		return $data;		
	}
	
	/*
	**  Sanitize and format the raw location ready for output
	*/
	function format_location ($location, $include_link) {
		global $spiffy_calendar;
		
		$target = '';
		if ($spiffy_calendar->current_options['map_new_window'] == 'true') {
			$target = ' target="_blank"';
		} 
		$data = apply_filters('spiffy_calendar_location', wpautop(esc_textarea(stripslashes($location))));
		if ($include_link == 'T') {
			$data .= '<p><a href="https://maps.google.com/?q=' . rawurlencode(esc_textarea(stripslashes($location))) . '"' . $target . '>' . __('Map', 'spiffy-calendar') . '</a></p>';
		}
		return str_replace(array('<p>', '</p>'), array('<span class="ca-location-p">', '</span>'), $data);
	}
	
	/*
	** Read the categories into memory once when drawing events
	*/
	function get_all_categories() 
	{
		global $wpdb, $spiffy_calendar;
		
		if (count($this->categories) > 0) return; // only do this once

		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE;
		if ($spiffy_calendar->current_options['alphabetic_categories'] == 'true') $sql .= ' ORDER BY category_name ASC';
		$this->categories = $wpdb->get_results($sql);
	}

	/*
	** Determine the date requested for the current calendar display from the querystring, return as array (0=>year, 1=>month, 2=>day)
	*/
	function get_date_from_args () {
		global $wpdb;
		
		$current_timestamp = current_time('timestamp');
		if ( isset($_GET['yr']) && (isset($_GET['month'])) && ($_GET['yr'] >= 0) && ((int)$_GET['yr'] != 0) && ($_GET['yr'] <= 3000) ) {
			
			$year = $wpdb->prepare("%d",$_GET['yr']);
			$month = 1;
			if ($_GET['month'] == 'jan') { $month = 1; }
			else if ($_GET['month'] == 'feb') { $month = 2; }
			else if ($_GET['month'] == 'mar') { $month = 3; }
			else if ($_GET['month'] == 'apr') { $month = 4; }
			else if ($_GET['month'] == 'may') { $month = 5; }
			else if ($_GET['month'] == 'jun') { $month = 6; }
			else if ($_GET['month'] == 'jul') { $month = 7; }
			else if ($_GET['month'] == 'aug') { $month = 8; }
			else if ($_GET['month'] == 'sep') { $month = 9; }
			else if ($_GET['month'] == 'oct') { $month = 10; }
			else if ($_GET['month'] == 'nov') { $month = 11; }
			else if ($_GET['month'] == 'dec') { $month = 12; }
		 } else {
			// No valid month causes the calendar to default to today
			$year = date("Y", $current_timestamp);
			$month = date("m", $current_timestamp);
		}
		if (isset($_GET['daynum'])) {
			$day = $wpdb->prepare("%d",$_GET['daynum']);
		} else {
			$day = date("d", $current_timestamp);		
		}
		return array($year, $month, $day);
	}

	/*
	** Build day of week names array
	*/
	function get_day_names ($type) {
		if ($type == 'full') {
			$name_days = array(0=>date_i18n('l', strtotime('Sunday')),
								date_i18n('l', strtotime('Monday')),
								date_i18n('l', strtotime('Tuesday')),
								date_i18n('l', strtotime('Wednesday')),
								date_i18n('l', strtotime('Thursday')),
								date_i18n('l', strtotime('Friday')),
								date_i18n('l', strtotime('Saturday')));			
		} else {
			$name_days = array(0=>date_i18n('D', strtotime('Sunday')),
								date_i18n('D', strtotime('Monday')),
								date_i18n('D', strtotime('Tuesday')),
								date_i18n('D', strtotime('Wednesday')),
								date_i18n('D', strtotime('Thursday')),
								date_i18n('D', strtotime('Friday')),
								date_i18n('D', strtotime('Saturday')));
			
		}
		if (get_option('start_of_week') != 0) {
			// Use Monday for start of week if anything other than Sunday is set
			$sunday = array_shift ($name_days);
			$name_days[6] = $sunday;
		}
		return $name_days;
	}

	/*
	** Determine the index of the weekday for first of the month
	*/
	function get_first_weekday ($year, $month) {
		// Week starts Sunday
		if (get_option('start_of_week') == 0) {
			$first_weekday = date("w",mktime(0,0,0,$month,1,$year));
			$first_weekday = ($first_weekday==0?1:$first_weekday+1);
		} else {
			// Otherwise assume the week starts on a Monday. Anything other 
			// than Sunday or Monday is just plain odd
			$first_weekday = date("w",mktime(0,0,0,$month,1,$year));
			$first_weekday = ($first_weekday==0?7:$first_weekday);
		}
		return ($first_weekday);
	}

	/*
	**	Grab all events for the requested date range from the DB
	**
	**  The retrieved events consist of specific scheduled events within the range, and all recurring events that
	**  fall within the same year(s)
	*/
	function grab_events($y1,$m1,$d1,$y2,$m2,$d2,$cat_list = '') {
		global $wpdb, $spiffy_calendar, $spiffycal_custom_fields;

		// Get the date format right
		$date1 = $y1 . '-' . $m1 . '-' . $d1;
		$date2 = $y2 . '-' . $m2 . '-' . $d2;
		//echo 'Grabbing range '.$date1.' to '.$date2.'<br />';
		
		$date1_timestamp = strtotime($date1);
		$date2_timestamp = strtotime($date2);

		// Format the category list
		$pattern = '/^\d+(?:,\d+)*$/';
		if ($cat_list == '') { 
			$cat_sql = ''; 
		} else if ( preg_match($pattern, $cat_list) ) {
			$cat_sql = 'AND event_category in ('.$cat_list.')';
		} else {
			$cat_sql = '';
		}
				 
		// The collated SQL code
		$sql = "SELECT a.*,'Normal' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS a WHERE a.event_status = 'P' AND a.event_begin <= '$date2' AND a.event_end >= '$date1' AND a.event_recur = 'S' ".$cat_sql." 
UNION ALL 
SELECT b.*,'Yearly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS b WHERE b.event_status = 'P' AND b.event_recur = 'Y' AND b.event_begin <= '$date2' AND b.event_repeats = 0 ".$cat_sql." 
UNION ALL 
SELECT c.*,'Yearly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS c WHERE c.event_status = 'P' AND c.event_recur = 'Y' AND c.event_begin <= '$date2' AND c.event_repeats != 0 AND (EXTRACT(YEAR FROM '$date1')-EXTRACT(YEAR FROM c.event_end)) <= c.event_repeats ".$cat_sql." 
UNION ALL 
SELECT d.*,'Monthly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS d WHERE d.event_status = 'P' AND d.event_recur = 'M' AND d.event_begin <= '$date2' AND d.event_repeats = 0 ".$cat_sql." 
UNION ALL
SELECT e.*,'Monthly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS e WHERE e.event_status = 'P' AND e.event_recur = 'M' AND e.event_begin <= '$date2' AND e.event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date1'),EXTRACT(YEAR_MONTH FROM e.event_end))) <= e.event_repeats ".$cat_sql." 
UNION ALL
SELECT f.*,'MonthSun' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS f WHERE f.event_status = 'P' AND f.event_recur = 'U' AND f.event_begin <= '$date2'  AND f.event_repeats = 0 ".$cat_sql." 
UNION ALL
SELECT g.*,'MonthSun' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS g WHERE g.event_status = 'P' AND g.event_recur = 'U' AND g.event_begin <= '$date2' AND g.event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date1'),EXTRACT(YEAR_MONTH FROM g.event_end))) <= g.event_repeats ".$cat_sql." 
UNION ALL
SELECT h.*,'Weekly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS h WHERE h.event_status = 'P' AND h.event_recur = 'W' AND '$date2' >= h.event_begin AND h.event_repeats = 0 ".$cat_sql." 
UNION ALL
SELECT i.*,'Weekly' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS i WHERE i.event_status = 'P' AND i.event_recur = 'W' AND '$date2' >= i.event_begin AND i.event_repeats != 0 AND (i.event_repeats*7) >= (TO_DAYS('$date1') - TO_DAYS(i.event_end)) ".$cat_sql." 
UNION ALL
SELECT j.*,'Daily' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS j WHERE j.event_status = 'P' AND j.event_recur = 'D' AND '$date2' >= j.event_begin AND j.event_repeats = 0 ".$cat_sql."
UNION ALL
SELECT k.*,'Daily' AS type FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " AS k WHERE k.event_status = 'P' AND k.event_recur = 'D' AND '$date2' >= k.event_begin AND k.event_repeats != 0 AND (k.event_repeats*k.event_recur_multiplier) >= (TO_DAYS('$date1') - TO_DAYS(k.event_end)) ".$cat_sql."
ORDER BY event_id";

		// NOTE - we do not allow infinite custom days
		
		//print_r ($sql);
		
		// Run the query
		$events = $wpdb->get_results($sql);
		//print_r($events);
		
		// get the custom fields, if any
		if ( $spiffy_calendar->bonus_addons_active() && isset ($spiffycal_custom_fields) ) {
			foreach ( $events as $event) {
				$event->custom_field = $spiffycal_custom_fields->get_custom_fields($event->event_id);
			}
		}	
				
		return $events;
	}

	/*
	** Draw the mini calendar 
	*/
	function minical($cat_list = '', $title = '') {
		
		global $wpdb;

		// Build day of week names array
		$name_days = $this->get_day_names('mini');

		// Determine month from arguments if provided
		$current_timestamp = current_time('timestamp');
		list($c_year, $c_month, $c_day) = $this->get_date_from_args();

		// Determine the index of the weekday for first of the month
		$first_weekday = $this->get_first_weekday($c_year, $c_month);

		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));

		// Start the table and add the header and naviagtion					
		$calendar_body = '';
		if ($title != '') {
			$calendar_body .= '<h2>' . esc_html($title) . '</h2>';
		}
		$calendar_body .= '<div class="spiffy-minical-block"><table class="spiffy calendar-table minical">';

		// The header of the calendar table and the links
		$calendar_body .= '<tr class="calendar-heading"><td colspan="7" class="calendar-date-switcher">
	<table class="calendar-heading-tbl">
		<tr>
			<td class="calendar-prev">' . $this->prev_month($c_year,$c_month,true) . '</td>
			<td class="calendar-month">'. date_i18n("F", mktime(null, null, null, $c_month, 1)).' '.$c_year.'</td>
			<td class="calendar-next">' . $this->next_month($c_year,$c_month,true) . '</td>
		</tr>
	</table>
 </td></tr>';

		// Print the headings of the days of the week
		$calendar_body .= '<tr class="weekday-titles">';
		for ($i=1; $i<=7; $i++) {
			// Colours need to be different if the starting day of the week is different
			if (get_option('start_of_week') == 0) {
				$calendar_body .= '	<td class="'.($i<7&$i>1?'normal-day-heading':'weekend-heading').'" style="height:0;">'.$name_days[$i-1].'</td>';
			} else {
				$calendar_body .= '	<td class="'.($i<6?'normal-day-heading':'weekend-heading').'" style="height:0;">'.$name_days[$i-1].'</td>';
			}
		}
		$calendar_body .= '</tr>';

		// Get all potential events for the month ready
		$potential_events = $this->grab_events($c_year,$c_month,1,$c_year,$c_month,$days_in_month,$cat_list);

		$go = FALSE;
		for ($i=1; $i<=$days_in_month;) {
			$calendar_body .= '<tr>';
			for ($ii=1; $ii<=7; $ii++) {
				if ($ii==$first_weekday && $i==1) {
					$go = TRUE;
				} elseif ($i > $days_in_month ) {
					$go = FALSE;
				}
				
				// Determine "weekend" class applicability
				$weekend = '';
				if (get_option('start_of_week') == 0) {
					$weekend = ($ii<7&$ii>1?'':' weekend');
				} else {
					$weekend = ($ii<6?'':' weekend');
				}

				if ($go) {
					// This box has a date in it, get the events
					$grabbed_events = $this->filter_events($potential_events, $c_year,$c_month,$i,$cat_list);
					$no_events_class = '';
					if (!count($grabbed_events)) {
						$no_events_class = ' no-events';
					}
					$date_timestamp = mktime (0,0,0,$c_month,$i,$c_year);
					$calendar_body .= '	<td class="'.(date("Ymd", $date_timestamp)==date("Ymd",$current_timestamp)?'current-day':'').$weekend.$no_events_class.' day-with-date" style="height:0;"><span class="day-number'.$weekend.'">'.$this->minical_draw_grid_events($grabbed_events,$i++).'</span></td>';
				} else {
					// This box is empty
					$calendar_body .= '	<td class="day-without-date' . $weekend . '" style="height:0;">&nbsp;</td>';
				}
			}
			$calendar_body .= '</tr>';
		}
		$calendar_body .= '</table>';

		// Closing div
		$calendar_body .= '</div>';

		// The actual printing is done by the calling function .
		return $calendar_body;
	}

	/*
	** Create a hover with all a day's events for minical
	*/
	function minical_draw_grid_events($events, $day_of_week = '') {
		global $spiffy_calendar;
		
		$this->get_all_categories();

		// We need to sort arrays of objects by time
		usort($events, array($this, 'time_cmp'));
		
		// Only show anything if there are events
		$output = '';
		if (count($events)) {
			// Setup the wrapper
			$output = '<span class="calnk"><span class="mini-link calnk-link" style="background-color:#F8F9CD;" onClick="return true;">' .
						$day_of_week .
						'<span class="spiffy-popup spiffy-mp-' . $spiffy_calendar->current_options['mini_popup'] . '">';
			
			// Process the events
			foreach($events as $event) {
				if ($event->event_all_day == 'T') { 
					$the_time = __('all day', 'spiffy-calendar'); 
				} else if ($event->event_end_time == '00:00:00') { 
					$the_time = __('at ', 'spiffy-calendar') . date(get_option('time_format'), strtotime($event->event_time)); 
				} else {
					$the_time = __('from ', 'spiffy-calendar') . date(get_option('time_format'), strtotime($event->event_time)); 
					$the_time .= __(' to ', 'spiffy-calendar') . date(get_option('time_format'), strtotime($event->event_end_time));
				} 
				if ($event->event_link != '') { 
					$linkurl = esc_url(stripslashes($event->event_link)); 
					if ($spiffy_calendar->current_options['enable_new_window'] == 'true') {
						$target = ' target="_blank"';
					} else {
						$target = '';
					}
					$output .= '<a class="minical-popup-link" href="' . $linkurl . '" ' . $target . '>';
				}
				$cat_css = '';
				$cat_class = '';
				$cat_name_prefix = '';
				if ($spiffy_calendar->current_options['enable_categories'] == 'true') {
					foreach ($this->categories as $cat_details) {
						if ($cat_details->category_id == $event->event_category) {
							$cat_css = ' style="color:' . esc_html($cat_details->category_colour) . ';"';
							$cat_class = ' category_' . $cat_details->category_id;
							if ($spiffy_calendar->current_options['category_name_display'] == 'true') {
								$cat_name_prefix = esc_html(stripslashes($cat_details->category_name)) . ' - ';
							}
							break;
						}
					}
				}
				
				$output .= '<strong class="'.esc_html($cat_class).'" '.esc_html($cat_css).'>'.$cat_name_prefix.esc_html(stripslashes($event->event_title)).'</strong> '.esc_html($the_time).'<br />';
				if ($spiffy_calendar->current_options['enable_expanded_mini_popup'] == 'true') {
					if ($event->event_image > 0) {
						$image = wp_get_attachment_image_src( $event->event_image, 'thumbnail');
						$output .= '<img src="' . $image[0] . '" alt="" />';
					}
					$output .= $this->format_desc($event->event_desc);
				}
				if ($event->event_link != '') {
					$output .= '</a>';
				}
				if ($spiffy_calendar->current_options['enable_expanded_mini_popup'] == 'true') {
					$output .= $this->format_location($event->event_location, $event->event_link_location);
					if ($spiffy_calendar->current_options['link_google_cal'] == 'true') $output .= $this->format_google_link($event);
				}
			}
			// The tail
			$output .= '</span></span></span>';
		} else {
			$output .= esc_html($day_of_week);
		}
		return $output;
	}

	/*
	** Configure the "Next Day" link in the calendar
	*/
	function next_day($cur_year,$cur_month,$cur_day)
	{
		list($yy,$mm,$dd) = explode("-", date("Y-m-d", mktime(0,0,0,$cur_month,$cur_day+1,$cur_year)));

		$month = $this->mod_rewrite_months[intval($mm)];
		return '<a rel="nofollow" onclick="sessionStorage.scrollTop = jQuery(window).scrollTop();" href="' . esc_url(add_query_arg( array(
			'daynum' => $dd,
			'month' => $month,
			'yr' => $yy
				) )) . '">&gt;</a>';
	}

	/*
	**	Configure the "Next Month" link in the calendar
	*/
	function next_month($cur_year,$cur_month,$minical = false, $toggle='grid')
	{
		if ($cur_month == 12) {
			$the_month = 'jan';
			$the_year = $cur_year + 1;
		} else {
			$the_month = $this->mod_rewrite_months[$cur_month+1];
			$the_year = $cur_year;
		}
		return '<a rel="nofollow" title="' 
					.  __("Next month", "spiffy-calendar")
					. '" class="spiffy-calendar-arrow" onclick="sessionStorage.scrollTop = jQuery(window).scrollTop();" href="' 
					. esc_url(add_query_arg( array(
									'grid-list-toggle' => $toggle,
									'month' => $the_month,
									'yr' => $the_year
										) ) ) 
					. '">&gt;</a>';
	}

	/*
	** Function to indicate the number of the day passed, eg. 1st or 2nd Sunday
	*/
	function np_of_day($date)
	{
		$instance = 0;
		$dom = date('j',strtotime($date));
		if (($dom-7) <= 0) { $instance = 1; }
		else if (($dom-7) > 0 && ($dom-7) <= 7) { $instance = 2; }
		else if (($dom-7) > 7 && ($dom-7) <= 14) { $instance = 3; }
		else if (($dom-7) > 14 && ($dom-7) <= 21) { $instance = 4; }
		else if (($dom-7) > 21 && ($dom-7) < 28) { $instance = 5; }
		return $instance;
	}

	/*
	** Configure the "Previous Day" link in the calendar
	*/
	function prev_day($cur_year,$cur_month,$cur_day)
	{
		list($yy,$mm,$dd) = explode("-", date("Y-m-d", mktime(0,0,0,$cur_month,$cur_day-1,$cur_year)));

		$month = $this->mod_rewrite_months[intval($mm)];
		return '<a rel="nofollow" onclick="sessionStorage.scrollTop = jQuery(window).scrollTop();" href="' . esc_url(add_query_arg( array(
			'daynum' => $dd,
			'month' => $month,
			'yr' => $yy
				) )) . '">&lt;</a>';
	}	

	/*
	** Configure the "Previous Month" link in the calendar
	*/
	function prev_month($cur_year,$cur_month,$minical = false, $toggle = 'grid')
	{
		if ($cur_month == 1) {
			$the_month = 'dec';
			$the_year = $cur_year - 1;
		} else {
			$the_month = $this->mod_rewrite_months[$cur_month-1];
			$the_year = $cur_year;
		}
		return '<a rel="nofollow" title="' 
					.  __("Previous month", "spiffy-calendar")
					. '" class="spiffy-calendar-arrow" onclick="sessionStorage.scrollTop = jQuery(window).scrollTop();" href="' 
					. esc_url(add_query_arg( array(
									'grid-list-toggle' => $toggle,
									'month' => $the_month,
									'yr' => $the_year
										) ) )
					. '">&lt;</a>';

					$last_year = $cur_year - 1;
	}

	/*
	**	Function to compare time in event objects
	**
	**  $a < $b  -> -1
	**  $a == $b -> 0
	**  $a > $b  -> 1
	*/
	function time_cmp($a, $b) {
		global $spiffy_calendar;
		
		if ($a->event_time == $b->event_time) {
			if ($a->event_all_day == $b->event_all_day) {
				return 0;
			} else if (($a->event_all_day == 'T') && ($spiffy_calendar->current_options['all_day_last'] == 'false')) {
				return -1;
			} else {
				return 1;
			}
		}
		if ($a->event_all_day == 'T') {
			if ($spiffy_calendar->current_options['all_day_last'] == 'false') {
				return -1;
			} else {
				return 1;
			}
		}
		if ($b->event_all_day == 'T') {
			if ($spiffy_calendar->current_options['all_day_last'] == 'false') {
				return 1;
			} else {
				return -1;
			}
		}
		return ($a->event_time < $b->event_time) ? -1 : 1;
	}

	/*
	** Draw today's events
	*/
	function todays_events($cat_list = '', $event_limit = '', $style = '', $show_date = 'false', $none_found = '', $title = ' ', $num_columns = '')
	{
		global $wpdb;

		// Sanity check event limit
		if ($event_limit < 1) $event_limit = '';
		
		if ($show_date == 'true') {
			$date_str = '<p class="spiffy-current-date">' . date(get_option('date_format')) . '</p>';
		} else {
			$date_str = '';
		}
		
		$output = '';
		$output .= '<ul class="spiffy todays-events-list">';
		$current_timestamp = current_time('timestamp');
		$yr = date("Y",$current_timestamp);
		$mn = date("m",$current_timestamp);
		$dy = date("d",$current_timestamp);
		$events = $this->grab_events($yr,$mn,$dy,$yr,$mn,$dy,$cat_list);
		$events = $this->filter_events($events,$yr,$mn,$dy);
		usort($events, array($this, 'time_cmp'));
		$event_count = 0;
		foreach($events as $event) {
			$output .= '<li class="spiffy-event-details spiffy-'.esc_html($style).' spiffy-num'.esc_html($num_columns).'">'.$this->draw_event($event, $style, $current_timestamp).'</li>';
			$event_count ++;
			if (($event_limit != '') && ($event_count >= $event_limit)) break;
		}
		$output .= '</ul>';
		if (count($events) != 0) {
			return $date_str . $output;
		} else if ($none_found != '') {
			return $date_str . '<p class="spiffy-none-found">' . $none_found . '</p>';
		} else {
			return $date_str;
		}
	}

	/*
	** Draw upcoming events
	*/
	function upcoming_events($cat_list = '', $event_limit = '', $style = '', $none_found = '', $title = ' ', $num_columns = '')
	{
		global $wpdb, $spiffy_calendar;

		// Sanity check event limit
		if ($event_limit < 1) $event_limit = '';
		
		// Get number of days we should go into the future 
		$future_days = $spiffy_calendar->current_options['display_upcoming_days'];
		$day_count = 1;

		// Compute the date range to display
		if (date_default_timezone_get() != 'UTC') date_default_timezone_set ('UTC');
		$current_timestamp = current_time('timestamp');
		if ($spiffy_calendar->current_options['upcoming_includes_today'] == 'true') {
			$y1 = date("Y",$current_timestamp);
			$m1 = date("m",$current_timestamp);
			$d1 = date("d",$current_timestamp);
			$day_count = 0;
		} else {
			list($y1,$m1,$d1) = explode("-",date("Y-m-d",mktime(1*24,0,0,date("m",$current_timestamp),date("d",$current_timestamp),date("Y",$current_timestamp))));
		}
		list($y2,$m2,$d2) = explode("-",date("Y-m-d",mktime($future_days*24,0,0,date("m",$current_timestamp),date("d",$current_timestamp),date("Y",$current_timestamp))));
		$event_range = $this->grab_events($y1,$m1,$d1,$y2,$m2,$d2,$cat_list);
		
		$output = '';
		$event_count = 0;
		// $output .= '<div style="display:none;">Current timestamp ' . $current_timestamp . 
					// ' date_default_timezone_set: ' . date_default_timezone_get() . 
					// ' upcoming ymd: ' . $y1 . $m1 . $d1 . 
					// ' wp_date gives: ' . wp_date(get_option('date_format')) . '</div>';
				
		if ($style == 'Columns') $event_id_list = array();
		while ($day_count < $future_days+1)	{
			$this_timestamp = mktime($day_count*24,0,0,date("m",$current_timestamp),date("d",$current_timestamp),date("Y",$current_timestamp));
			list($y,$m,$d) = explode("-",date("Y-m-d", $this_timestamp));
			// $output .= '<div style="display:none;">Day ' . $day_count . 
						// ' timestamp for this day ' . $this_timestamp . ' and ymd: ' . $y . $m . $d . 
						// ' wp_date gives: ' . wp_date(get_option('date_format'), $this_timestamp) . 
						// ' date_i18n give: ' . date_i18n(get_option('date_format'), $this_timestamp) . '</div>';	
			$events = $this->filter_events($event_range, $y, $m, $d);
			usort($events, array($this, 'time_cmp'));
			if ( (count($events) != 0) && ($style != 'Columns') ) {
				$output .= '<li class="spiffy-upcoming-day ' . apply_filters ('spiffy_upcoming_day_classes', '') . '">';
				$output .= '<span class="spiffy-upcoming-date">';
				$upcoming_day = date_i18n(get_option('date_format'), $this_timestamp);
				$output .= apply_filters ('spiffy_upcoming_day_date', $upcoming_day, $m, $d, $y );
				$output .= '</span>';
				$output .= '<ul class="spiffy-upcoming-events">';
			} 
			foreach($events as $event) {
				if ($style == 'Columns') {
					// only display the first day for multi day span
					if (in_array ($event->event_id, $event_id_list) && ($event->begin_timestamp != $this_timestamp) ) {
						continue;
					}
					$event_id_list[] = $event->event_id;
				}
				$output .= '<li class="spiffy-event-details spiffy-'.esc_html($style).' spiffy-num'.esc_html($num_columns).'">'.$this->draw_event($event, $style, $this_timestamp).'</li>';
				$event_count ++;
				if (($event_limit != '') && ($event_count >= $event_limit)) break;
			}
			if ( (count($events) != 0) && ($style != 'Columns') ) {
				$output .= '</ul></li>';
			}
			$day_count = $day_count+1;
			if (($event_limit != '') && ($event_count >= $event_limit)) break;
		}

		if ($output != '') {
			return '<ul class="spiffy upcoming-events-list">' . $output . '</ul>';
		} else if ($none_found != '') {
			return '<p class="spiffy-none-found">' . $none_found . '</p>';
		} else {
			return '';
		}
	}

	/*
	** Draw a weekly calendar
	*/
	function weekly($cat_list = '', $title = '') {
		global $wpdb, $spiffy_calendar;

		$this->get_all_categories();

		// Build day of week names array
		$name_days = $this->get_day_names ('full');

		// Determine date from arguments if provided
		$current_timestamp = current_time('timestamp');
		list($c_year, $c_month, $c_day) = $this->get_date_from_args();

		$first = strtotime($c_year.'-'.$c_month.'-'.$c_day);
		$day_of_week = date("N", $first);
		
		// Determine the date range from the first to last day of week
		if ((get_option('start_of_week') == 0) && ($day_of_week != 7)) {
			$first = strtotime('last Sunday', $first);
		} else if ((get_option('start_of_week') != 0) && ($day_of_week != 1)) {
			$first = strtotime('last Monday', $first);
		}
		$last = strtotime('+6 day', $first);
		$y1 = date("Y", $first);
		$m1 = date("m", $first);
		$d1 = date("d", $first);
		$y2 = date("Y", $last);
		$m2 = date("m", $last);
		$d2 = date("d", $last);
		
		if ($spiffy_calendar->current_options['display_weeks'] == 'true')
			$num_columns = 8;
		else
			$num_columns = 7;
	
		// Start the table and add the header and navigation
		$calendar_body = '';
		if ($title != '') {
			$calendar_body .= '<h2>' . esc_html($title) . '</h2>';
		}
		$calendar_body .= '
<table class="spiffy calendar-table bigcal spiffy-weekly">';

		// The header of the calendar table and the links.
		$calendar_body .= '
	<tr class="calendar-heading"><td colspan="' . $num_columns . '" class="calendar-date-switcher">
		<table class="calendar-heading-tbl">
		<tr>
			<td class="calendar-prev">' . $this->prev_day($y1,$m1,$d1) . '</td>
';
		$calendar_body .= '
			<td class="calendar-month">'.date_i18n("F", mktime(null, null, null, $m1, 1)).' '.$y1.'</td>';
		$calendar_body .= '
			<td class="calendar-next">' . $this->next_day($y2,$m2,$d2) . '</td>
		</tr>
		</table>
	</td></tr>';

		// Print the headings of the days of the week
		$calendar_body .= '<tr class="weekday-titles">';
		if ($spiffy_calendar->current_options['display_weeks'] == 'true') {
			$calendar_body .= '<td class="weekend-heading week-number-heading">'.__("#", 'spiffy-calendar').'</td>';		
		}
		for ($i=1; $i<=7; $i++) {
			// Colours need to be different if the starting day of the week is different
			if (get_option('start_of_week') == 0) {
				$calendar_body .= '<td class="'.($i<7&$i>1?'normal-day-heading':'weekend-heading').'">'.$name_days[$i-1].'</td>';
			} else {
				$calendar_body .= '<td class="'.($i<6?'normal-day-heading':'weekend-heading').'">'.$name_days[$i-1].'</td>';
			}
		}
		$calendar_body .= '</tr>';

		// Get all potential events for the month ready
		$potential_events = $this->grab_events($y1,$m1,$d1,$y2,$m2,$d2,$cat_list);

		// Loop through the days, drawing each day box
		$calendar_body .= '<tr>';
		if ($spiffy_calendar->current_options['display_weeks'] == 'true') {
			$calendar_body .= '<td class="day-without-date week-number">'.date("W", mktime (0,0,0,$m1,$d1,$y1)) .'</td>';		
		}
		for ($ii=1; $ii<=7; $ii++) {
			
			// Determine "weekend" class applicability
			$weekend = '';
			if (get_option('start_of_week') == 0) {
				$weekend = ($ii<7&$ii>1?'':' weekend');
			} else {
				$weekend = ($ii<6?'':' weekend');
			}
			
			// Compute the date we are displaying
			$this_date = mktime (($ii-1)*24, 0, 0, $m1, $d1, $y1);
			list($yy,$mm,$dd) = explode("-", date("Y-m-d", $this_date));
			
			// Get the events
			$grabbed_events = $this->filter_events($potential_events, $yy,$mm,$dd,$cat_list);
			$no_events_class = '';
			if (!count($grabbed_events)) {
				$no_events_class = ' no-events';
			}
			$date_timestamp = mktime (0,0,0,$mm,$dd,$yy);
			$calendar_body .= '	<td class="spiffy-day-' . $dd . ' ' . 
								(date("Ymd", $date_timestamp)==date("Ymd",$current_timestamp)?'current-day':'').
								$weekend.$no_events_class.' day-with-date"><span class="day-number'.$weekend.'">' . $dd . '</span><span class="spiffy-event-group">' . $this->draw_grid_events($grabbed_events) . '</span></td>';
			 
		}
		$calendar_body .= '</tr>';

		$calendar_body .= '</table>';

		if ($spiffy_calendar->current_options['enable_categories'] == 'true') {
			$calendar_body .= '<table class="spiffy cat-key">';
			$calendar_body .= '<colgroup>
	<col style="width:10px; height:10px;">
	<col>
</colgroup>';
			$calendar_body .= '
<tr><td colspan="2" class="cat-key-cell"><strong>'.esc_html($spiffy_calendar->current_options['category_plural']).'</strong></td></tr>';
			$filtered_cats = explode(',',$cat_list);
			foreach($this->categories as $cat_detail) {
				if ( ($cat_list == '') || (in_array($cat_detail->category_id, $filtered_cats))) {
					$calendar_body .= '<tr><td style="background-color:' . esc_html($cat_detail->category_colour) . '; " class="cat-key-cell"></td>
<td class="cat-key-cell">&nbsp;'.$cat_detail->category_name.'</td></tr>';
				}
			}
			$calendar_body .= '</table>';
		}

		return $calendar_body;
	}
} // end of class
}

if (class_exists("SPIFFYCAL_Views")) {
	$spiffy_calendar_views = new SPIFFYCAL_Views();
}

?>