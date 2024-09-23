<?php
/*
 ** Spiffy admin table for managing events
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*************************** LOAD THE BASE CLASS *******************************
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 */
class Spiffy_Events_List_Table extends WP_List_Table {
    
     /** ************************************************************************
     * Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page, $spiffy_calendar;
                
       //Set parent defaults
        parent::__construct( array(
            'singular'  => __('event','spiffy-calendar'),     //singular name of the listed records
            'plural'    => __('events','spiffy-calendar'),    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

	/*
	** Create filters menu with event counts
	*/
	protected function get_views() { 
		global $wpdb, $current_user, $spiffy_calendar;
		
		// Determine if query is limited to author's events
		if (($spiffy_calendar->current_options['limit_author'] == 'true') && !current_user_can('manage_options')) {
			$author = $wpdb->prepare(" AND (event_author = %d)", $current_user->ID);
		} else {
			$author = '';
		}
		
		// Construct query for each event type
		$sql = "SELECT event_id FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_status = 'P'" . $author;
		$data = $wpdb->get_results($sql, ARRAY_A);    
		$num_published = count ($data);
		$sql = "SELECT event_id FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_status = 'D'" . $author;
		$data = $wpdb->get_results($sql, ARRAY_A);    
		$num_draft = count ($data);
		$sql = "SELECT event_id FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_status = 'R'" . $author;
		$data = $wpdb->get_results($sql, ARRAY_A);    
		$num_pending = count ($data);
		$total = $num_published + $num_draft + $num_pending;
		
		// Determine current view
		$all_class = $p_class = $d_class = $r_class = '';
		if (isset($_REQUEST['filter'])) {
			switch($_REQUEST['filter']) {
				case 'P':
					$p_class = " class='current'";
					break;
				case 'D':
					$d_class = " class='current'";
					break;
				case 'R':
					$r_class = " class='current'";
					break;
				default:
					$all_class = " class='current'";
					break;
			}	
		} else {
			$all_class = " class='current'";
		}
		$status_links = array(
			"all"		=> "<a href='" . 
							esc_url(add_query_arg( array ('paged' => false, 'filter' => false) )) . 
							"' $all_class>" . __('All','spiffy-calendar') . " <span class='count'>(" . $total . ")</span></a>",
			"published"	=> "<a href='" . 
							esc_url(add_query_arg( array('paged' => false, 'filter' => 'P' ) )) . 
							"' $p_class>" . __('Published','spiffy-calendar') . " <span class='count'>(" . $num_published . ")</span></a>",
			"draft"		=> "<a href='" . 
							esc_url(add_query_arg( array('paged' => false, 'filter' => 'D' ) )) . 
							"' $d_class>" . __('Draft','spiffy-calendar') . " <span class='count'>(" . $num_draft . ")</span></a>",
			"pending"	=> "<a href='" . 
							esc_url(add_query_arg( array('paged' => false, 'filter' => 'R' ) )) . 
							"' $r_class>" . __('Pending','spiffy-calendar') . " <span class='count'>(" . $num_pending . ")</span></a>"
		);
		return $status_links;
	}

    /** ************************************************************************
     * Column output handler
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
		global $wpdb;
		
        switch($column_name){
			case 'event_time':
				if ($item['event_all_day'] == 'T') { 
					return __('N/A','spiffy-calendar'); 
				} else { 
					return date(get_option('time_format'),strtotime($item[$column_name]));
				}
				break;

			case 'event_end_time':
				if ($item[$column_name] == '00:00:00') { 
					return __('N/A','spiffy-calendar'); 
				} else { 
					return date(get_option('time_format'),strtotime($item[$column_name]));
				}
				break;
				
			case 'event_recur':
				// Interpret the DB values into something human readable
				if ($item[$column_name] == 'S') { return '-'; } 
				else if ($item[$column_name] == 'W') { return __('Weekly','spiffy-calendar'); }
				else if ($item[$column_name] == 'M') { return __('Monthly (date)','spiffy-calendar'); }
				else if ($item[$column_name] == 'U') { return __('Monthly (day)','spiffy-calendar'); }
				else if ($item[$column_name] == 'Y') { return __('Yearly','spiffy-calendar'); }
				else if ($item[$column_name] == 'D') { return __('Every','spiffy-calendar') . ' ' . $item['event_recur_multiplier'] . ' ' . __('days','spiffy-calendar'); }
				break;
				
			case 'event_repeats':
				// Interpret the DB values into something human readable
				if ($item['event_recur'] == 'S') { return '-'; }
				else if ($item[$column_name] == 0) { return __('Forever','spiffy-calendar'); }
				else if ($item[$column_name] > 0) { return $item[$column_name].' '.__('Times','spiffy-calendar'); }
				break;
				
			case 'event_hide_events':
				// interpret the hide_events value
				if ($item[$column_name] == 'F') { return __('False', 'spiffy-calendar'); }
				else if ($item[$column_name] == 'T') { return __('True', 'spiffy-calendar'); }
				break;
				
			case 'event_show_title':
				if ($item['event_hide_events'] == 'F') { return '-'; }
				else {      // hide_event event
					if ($item[$column_name] == 'F') { return __('False', 'spiffy-calendar'); }
					else if ($item[$column_name] == 'T') { return __('True', 'spiffy-calendar'); }
				}
				break;
			
			case 'event_image':
				if ($item[$column_name] > 0) {
					$image = wp_get_attachment_image_src( $item[$column_name], 'thumbnail');
					return '<img src="' . $image[0] . '" width="76px" />';
				}
				break;
				
			case 'event_author':
				if ($item[$column_name] != 0) {
					$e = get_userdata($item[$column_name]); 
					return $e->display_name;
				} else {
					return '';
				}
				break;
				
			case 'event_category':
				$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " WHERE category_id=".esc_sql($item[$column_name]);
				$this_cat = $wpdb->get_row($sql);
				return '<span style="color:'. esc_html($this_cat->category_colour).';">' . esc_html(stripslashes($this_cat->category_name)) . '</span>';
			
            default:
                return esc_html(stripslashes($item[$column_name]));
        }
    }


    /** ************************************************************************
     * Title column output handler
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_event_title($item){
        
		// Construct nonce URL strings
		$url = add_query_arg( array(
			'page' => sanitize_text_field($_REQUEST['page']),
			'tab' => 'event_edit',
			'action'  => 'edit',
			'event' => $item['event_id'],
		), admin_url( 'admin.php' ) );
		$edit_url = wp_nonce_url( $url, 'spiffy-edit-security'.$item['event_id'] );
		$url = add_query_arg( array(
			'page' => sanitize_text_field($_REQUEST['page']),
			'tab' => 'event_edit',
			'action'  => 'copy',
			'event' => $item['event_id'],
		), admin_url( 'admin.php' ) );
		$copy_url = wp_nonce_url( $url, 'spiffy-edit-security'.$item['event_id'] );
		$url = add_query_arg( array(
			'page' => sanitize_text_field($_REQUEST['page']),
			'tab' => 'events',
			'action'  => 'delete',
			'event' => $item['event_id'],
		), admin_url( 'admin.php' ) );
		$delete_url = wp_nonce_url( $url, 'spiffy-delete-security'.$item['event_id'], '_spfdelnonce' );

        // Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="%s">%s</a>',esc_url($edit_url),__('Edit', 'spiffy-calendar')),
            'copy'      => sprintf('<a href="%s">%s</a>',esc_url($copy_url),__('Copy', 'spiffy-calendar')),
            'delete'    => sprintf('<a href="%s" onclick="return confirm(\'%s: %s?\')">Delete</a>',	
								esc_url ($delete_url),
								__('Are you sure you want to delete the event titled','spiffy-calendar'),
								esc_html(stripslashes($item['event_title'])),
								__('Delete', 'spiffy-calendar')
								),
        );
        
		if ($item['event_status'] == 'D') {
			$title_text = esc_html(stripslashes($item['event_title'])) . ' - ' . __('Draft', 'spiffy-calendar');
		} else if ($item['event_status'] == 'R') {
			$title_text = esc_html(stripslashes($item['event_title'])) . ' - ' . __('Pending', 'spiffy-calendar');
		} else {
			$title_text = esc_html(stripslashes($item['event_title']));			
		}
				
       //Return the title contents
        return sprintf('%1$s%2$s',
            $title_text,
            $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * Handle the checkbox column
	 *
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['event_id']);
    }

    /** ************************************************************************
     * Define our columns to display
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
		global $spiffy_calendar;
		
        $columns = array(
            'cb'			=> '<input type="checkbox" />', //Render a checkbox instead of text
            'event_title'	=> __('Title','spiffy-calendar'),
            'event_begin'	=> __('Start Date','spiffy-calendar'),
            'event_end'		=> __('End Date','spiffy-calendar'),
            'event_time'	=> __('Start Time','spiffy-calendar'),
            'event_end_time'	=> __('End Time','spiffy-calendar'),
            'event_recur'	=> __('Recurs','spiffy-calendar'),
            'event_repeats'	=> __('Repeats','spiffy-calendar'),
            'event_hide_events'	=> __('Hide Events','spiffy-calendar'),
            'event_show_title'	=> __('Show Title','spiffy-calendar'),
            'event_image'	=> __('Image','spiffy-calendar'),
            'event_author'	=> __('Author','spiffy-calendar'),
            'event_category'	=> esc_html($spiffy_calendar->current_options['category_singular']),
        );
        return $columns;
    }


    /** ************************************************************************
     * Sortable columns array
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'event_begin'    => array('event_begin',true),
			'event_category' => array('event_category',false)
        );
        return $sortable_columns;
    }


   /** ************************************************************************
	* Display the bulk actions dropdown.
	*
	* @param string $which The location of the bulk actions: 'top' or 'bottom'.
	**************************************************************************/
	function bulk_actions( $which = '' ) {
		global $spiffy_calendar, $wpdb;
		
		// Output the standard bulk actions dropdown
		parent::bulk_actions( $which );
		
		// Add our custom bulk inputs
		
		// Category selection
		?>
<select name="event_category-<?php echo esc_attr($which); ?>" class="spiffy-category-selector" style="display:none;">
<option value="0"><?php echo __('Select', 'spiffy-calendar') . ' ' . esc_html($spiffy_calendar->current_options['category_singular']); ?></option>
 <?php
		// Grab all the categories and list them
		$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE;
		if ($spiffy_calendar->current_options['alphabetic_categories'] == 'true') $sql .= " ORDER BY category_name";
		$cats = $wpdb->get_results($sql);
		foreach($cats as $cat) {
			 echo '<option value="'.$cat->category_id.'">' . esc_html(stripslashes($cat->category_name)) . '</option>';
		}
?>
</select>		
<?php

		// Status selection
		?>
<select class="spiffy-status-selector" name="event_status-<?php echo esc_attr($which); ?>" style="display:none;">
	<option value="0"><?php _e('Select Status', 'spiffy-calendar') ?></option>
	<option value="P"><?php _e('Publish', 'spiffy-calendar') ?></option>
	<option value="D"><?php _e('Draft', 'spiffy-calendar') ?></option>
	<option value="R"><?php _e('Pending review', 'spiffy-calendar') ?></option>
</select>		
		<?php
	}

    /** ************************************************************************
     * Bulk actions array
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
		global $spiffy_calendar;
		
        $actions = array(
            'delete'    => __('Delete', 'spiffy-calendar'),
            'set-category' => __('Set', 'spiffy-calendar') . ' ' . esc_html($spiffy_calendar->current_options['category_singular']),
			'set-status' => __('Set Status', 'spiffy-calendar')
        );
        return $actions;
    }


    /** ************************************************************************
     * Bulk action handler
	 *
	 * Note: edit and copy actions are handled before this 
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
		global $wpdb, $spiffy_calendar, $current_user;

		if ( ! isset( $_REQUEST['event'] ) ) {
			return;
		}
	
		// security check for bulk actions
        if ( isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce']) ) {

            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
                wp_die( __('Security check failed!','spiffy-calendar') );
			}
        } else if ( isset($_REQUEST['_spfdelnonce']) && !empty($_REQUEST['_spfdelnonce']) && isset($_REQUEST['event']) && !empty($_REQUEST['event']) ) {
			$event_id = intval(sanitize_text_field($_REQUEST['event']));
			if ( ! wp_verify_nonce( $_REQUEST['_spfdelnonce'], 'spiffy-delete-security' . $event_id ) ) {
                wp_die( __('Delete security check failed!','spiffy-calendar') );		
			}
		} else {
			wp_die ( __('Security check failure','spiffy-calendar') );
		}
		
		// make sure the user has permission to alter these events
		if ( ($spiffy_calendar->current_options['limit_author'] == 'true') && !current_user_can('manage_options') ) {	
			foreach ( (array) $_REQUEST['event'] as $event_id ) {
				$event_id = intval(sanitize_text_field($event_id));
				$data = $wpdb->get_results("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id='" . 
								esc_sql($event_id) . "' LIMIT 1");
				if ( empty($data) ) {
					wp_die ( __('Invalid event in list','spiffy-calendar') );
				} else {
					// Check this user is allowed to edit this event
					if ($data[0]->event_author != $current_user->ID) {
						wp_die( __('You do not have sufficient permissions to access these events.','spiffy-calendar') );	
					}
				}
			}
		}
		
		$success_msg = '';
		$error_msg = '';

        //Detect when a bulk action is being triggered...
		switch ($this->current_action()) {
			case 'delete':
				// Delete selected events
				foreach ( (array) $_REQUEST['event'] as $event_id ) {
					$event_id = intval(sanitize_text_field($event_id));
					$sql = $wpdb->prepare("SELECT event_title FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", sanitize_text_field($event_id));
					$title = $wpdb->get_results($sql);
					if ( count ($title) > 0 ) {
						$title = $title[0]->event_title;;
						$result = $spiffy_calendar->delete_event($event_id);									
						if ( empty($result) || empty($result[0]->event_id) ) {
							$success_msg .= '<p>' . __('Event deleted successfully','spiffy-calendar') . ': ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						} else {
							$error_msg .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Despite issuing a request to delete, the event still remains in the database. Please investigate.','spiffy-calendar') . ' : ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						}
					} else {
						$error_msg .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Event not found','spiffy-calendar') . ' : ' . $event_id . '</p>';
					}


				}
				
				if ($success_msg != '') {
					echo '<div class="updated">' . $success_msg . '</div>';
				}
				if ($error_msg != '') {
					echo '<div class="error">' . $error_msg . '</div>';			
				}
				break;
				
			case 'set-category':
				// Update the events' category
				//print_r($_REQUEST);
				if ( isset($_REQUEST['event_category-top']) && ($_REQUEST['event_category-top'] != '0') ) {
					$category = sanitize_text_field($_REQUEST['event_category-top']);
				} else if ( isset($_REQUEST['event_category-bottom']) && ($_REQUEST['event_category-bottom'] != '0') ) {
					$category = sanitize_text_field($_REQUEST['event_category-bottom']);
				} else {
					$category = '0';
				}
				if ($category != '0') {
					foreach ( (array) $_REQUEST['event'] as $event_id ) {
						$sql = $wpdb->prepare("SELECT event_title FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", sanitize_text_field($event_id));
						$title = $wpdb->get_results($sql);
						$title = (count($title) > 0)? $title[0]->event_title : __('Unknown', 'spiffy-calendar');

						$sql = $wpdb->prepare("UPDATE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_category=%d WHERE event_id=%d", $category, $event_id);
						$result = $wpdb->get_results($sql);
						//print_r ($result);
					
						if ( empty($result) || empty($result[0]->event_id) ) {
							$success_msg .= '<p>' . __('Event updated successfully','spiffy-calendar') . ': ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						} else {
							$error_msg .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Update failed. Please investigate.','spiffy-calendar') . ' : ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						}
					}
					
					if ($success_msg != '') {
						echo '<div class="updated">' . $success_msg . '</div>';
					}
					if ($error_msg != '') {
						echo '<div class="error">' . $success_msg . '</div>';			
					}
					
				} else {
						echo '<div class="error"><p>' . __('Please select a','spiffy-calendar') . ' ' . esc_html($spiffy_calendar->current_options['category_singular']) . '</p></div>';
				}
				break;
				
			case 'set-status':
				// Update the events' status
				//print_r($_REQUEST);
				if ( isset($_REQUEST['event_status-top']) && ($_REQUEST['event_status-top'] != '0') ) {
					$status = sanitize_text_field($_REQUEST['event_status-top']);
				} else if ( isset($_REQUEST['event_status-bottom']) && ($_REQUEST['event_status-bottom'] != '0') ) {
					$status = sanitize_text_field($_REQUEST['event_status-bottom']);
				} else {
					$status = '0';
				}
				if ($status != '0') {
					foreach ( (array) $_REQUEST['event'] as $event_id ) {
						$sql = $wpdb->prepare("SELECT event_title FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE event_id=%d", sanitize_text_field($event_id));
						$title = $wpdb->get_results($sql);
						$title = (count($title) > 0)? $title[0]->event_title : __('Unknown', 'spiffy-calendar');

						$sql = $wpdb->prepare("UPDATE " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " SET event_status=%s WHERE event_id=%d", $status, $event_id);
						$result = $wpdb->get_results($sql);
						//print_r ($result);
					
						if ( empty($result) || empty($result[0]->event_id) ) {
							$success_msg .= '<p>' . __('Event updated successfully','spiffy-calendar') . ': ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						} else {
							$error_msg .= '<p><strong>' . __('Error','spiffy-calendar') . ':</strong> ' . __('Update failed. Please investigate.','spiffy-calendar') . ' : ' . $event_id . ' ' . esc_html(stripslashes($title)) . '</p>';
						}
					}
					
					if ($success_msg != '') {
						echo '<div class="updated">' . $success_msg . '</div>';
					}
					if ($error_msg != '') {
						echo '<div class="error">' . $success_msg . '</div>';			
					}
					
				} else {
						echo '<div class="error"><p>' . __('Please select a status value','spiffy-calendar') . '</p></div>';
				}
				break;
        } 
		
		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'action', 'event', '_wpnonce', '_spfdelnonce' ), $_SERVER['REQUEST_URI'] );

    }


    /** ************************************************************************
     * Prepare array of items to display in the table
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $current_user, $wpdb, $spiffy_calendar;

        /**
         * Determine how many records per page to show
         */
        $per_page = $this->get_items_per_page('spiffy_events_per_page', 10);
        
        /**
         * Define our column headers
         */
        // $columns = $this->get_columns();
        // $hidden = array();
        $sortable = $this->get_sortable_columns();

        // $this->_column_headers = array($columns, $hidden, $sortable);
        $this->_column_headers = $this->get_column_info();
        
        /**
         * Handle bulk actions
         */
        $this->process_bulk_action();
        
        
        /**
         * Parse options
         */
		$orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'event_begin'; 	//If no sort, default to start date
		if ( !array_key_exists ( $orderby, $sortable )) $orderby = 'event_begin'; 								// check value to prevent sql injection
		$order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc'; 				//If no order, default to desc
		if ( !in_array( $order, array ( 'asc', 'desc') )) $order = 'desc';										// check value to prevent sql injection
		// note that $orderby and $order are column names and must not be "prepared"
		$search = !empty($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
		$filter = !empty($_REQUEST['filter']) ? sanitize_text_field($_REQUEST['filter']) : '';
		
		$query_options = array(
			//'blog_id'     => $blog_id,
			's'           => $search,
			//'record_type' => $record_type,
			'orderby'     => $orderby,
			'order'       => $order,
			'filter'	  => $filter,
		);

		// Update the current URI with the new options.
		$_SERVER['REQUEST_URI'] = add_query_arg( $query_options, $_SERVER['REQUEST_URI'] );
		
		/*
		 * Get list data
		 */
		if (!empty($search)) {
			$search_string1 = $wpdb->prepare(" AND (event_title LIKE %s OR event_desc LIKE %s OR event_location LIKE %s )", 
										'%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
			$search_string2 = $wpdb->prepare(" WHERE (event_title LIKE %s OR event_desc LIKE %s OR event_location LIKE %s )", 
										'%'.$search.'%', '%'.$search.'%', '%'.$search.'%');
		} else {
			$search_string1 = "";
			$search_string2 = "";
		}
		if ($filter != '') {
			$search_string1 .= $wpdb->prepare(" AND (event_status = %s)", $filter);
			if ($search_string2 == '') {
				$search_string2 = $wpdb->prepare(" WHERE (event_status = %s)", $filter);							
			} else {
				$search_string2 .= $wpdb->prepare(" AND (event_status = %s)", $filter);				
			}
		}
		if (($spiffy_calendar->current_options['limit_author'] == 'true') && !current_user_can('manage_options')) {
			$sql = $wpdb->prepare("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . " WHERE (event_author=%d) " . $search_string1 . " ORDER BY $orderby $order", $current_user->ID);
		} else {
			$sql = "SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_TABLE . $search_string2 . " ORDER BY $orderby $order";
		}
		// print_r ($sql);
		$data = $wpdb->get_results($sql, ARRAY_A);    
		
        /**
         * Handle pagination
         */
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        /**
         * Add our *sorted* data to the items property, where it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}
?>