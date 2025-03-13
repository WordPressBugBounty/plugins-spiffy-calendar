<?php
/**
 * Custom Posts and Taxonomy
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("SPIFFYCAL_customposts")) {
class SPIFFYCAL_customposts {

	private $allowed_capabilities = array(
					"edit_spiffycal",
					"read_spiffycal",
					"delete_spiffycal",
					"edit_spiffycals",
					"delete_spiffycals",
					"publish_spiffycals",
					"read_private_spiffycals",
					"delete_private_spiffycals",
					"delete_published_spiffycals",
					"edit_private_spiffycals",
					"edit_published_spiffycals",
					"assign_spiffy_terms",
				);
	private $others_capabilities = array(
					"edit_others_spiffycals",
					"delete_others_spiffycals",					
				);
	private $admin_capabilities = array(
					"manage_spiffy_terms",
					"edit_spiffy_terms",
					"delete_spiffy_terms",
				);

	public function __construct(){
		global $spiffy_calendar;
		
		// Needed to run from WP-CLI
		if (!isset($spiffy_calendar)) {
			$spiffy_calendar = new stdClass();
		}		

		add_action('init', array($this, 'init_action'));

		register_activation_hook( SPIFFY_FILE_URL, array( $this, 'activate' ) );	
		register_deactivation_hook( SPIFFY_FILE_URL, array( $this, 'deactivate' ) );

		add_action( 'spiffy_categories_edit_form_fields', array( $this, 'edit_custom_fields' ), 10, 2 );
		add_action( 'spiffy_categories_add_form_fields', array( $this, 'edit_custom_fields' ), 10, 2 );
		add_action( 'edited_spiffy_categories', array( $this, 'save_custom_fields' ), 10, 2);
		add_action( 'created_spiffy_categories', array( $this, 'save_custom_fields' ), 10, 2);

		add_action( 'admin_init', array( $this, 'admin_init_action' ) );

		add_filter( 'views_edit-spiffy_event', array( $this, 'events_admin_buttons' ) );

		add_action( 'restrict_manage_posts', array( $this, 'my_category_filter' ), 10, 2 );
		add_filter( 'months_dropdown_results', array( $this, 'my_remove_date_filter' ), 10, 2);
		add_filter( 'post_row_actions', array( $this, 'copy_post_link'), 25, 2 );
		add_filter( 'post_column_taxonomy_links', array( $this, 'fix_taxonomy_links' ), 10, 3 );
		add_filter( 'default_hidden_columns', array ( $this, 'set_default_hidden_columns'), 10, 2 );

		add_action( 'quick_edit_custom_box', array( $this,'quick_edit_category_field' ), 10, 2 );
		add_action( 'admin_footer', array( $this,'quickedit_category_javascript' ) );
		
		add_action( 'admin_action_spiffy_copy_event', array( $this, 'copy_event' ) );
		add_action( 'pre_get_posts', array ( $this, 'remove_post_type_from_search_results') );
	}
	
	/*
	** Action to take when plugin first activated
	*/
	function activate() {
		// Give calendar capabilities to administrator
		$role = get_role( 'administrator' );
		$this->add_caps ($role, false);

		foreach ( $this->admin_capabilities as $cap ) {
			$role->add_cap( $cap );
		}

		// Do other capabilites when wp is loaded
		add_action( 'wp_loaded', array ( $this, 'adjust_roles' ), 10, 0 );
	}
	
	/*
	** Init actions
	*/
	function init_action () {
		global $spiffy_calendar;
		
		// Register our custom post and taxonomy
		register_post_type('spiffy_event',
			array(
				'labels'      => array(
					'name'          => __( 'Events', 'spiffy-calendar' ),
					'singular_name' => __( 'Event', 'spiffy-calendar' ),
					'menu_name'             => __( 'Events', 'Admin menu name', 'spiffy-calendar' ),
					'add_new'               => __( 'Add Event', 'spiffy-calendar' ),
					'add_new_item'          => __( 'Add New Event', 'spiffy-calendar' ),
					'edit'                  => __( 'Edit', 'spiffy-calendar' ),
					'edit_item'             => __( 'Edit Event', 'spiffy-calendar' ),
					'new_item'              => __( 'New Event', 'spiffy-calendar' ),
					'view'                  => __( 'View Event', 'spiffy-calendar' ),
					'view_item'             => __( 'View Event', 'spiffy-calendar' ),
					'search_items'          => __( 'Search Events', 'spiffy-calendar' ),
					'not_found'             => __( 'No Events found', 'spiffy-calendar' ),
					'not_found_in_trash'    => __( 'No Events found in trash', 'spiffy-calendar' ),
					'parent'                => __( 'Parent Event', 'spiffy-calendar' ),
					'featured_image'        => __( 'Event Image', 'spiffy-calendar' ),
					'set_featured_image'    => __( 'Set event image', 'spiffy-calendar' ),
					'remove_featured_image' => __( 'Remove event image', 'spiffy-calendar' ),
					'use_featured_image'    => __( 'Use as event image', 'spiffy-calendar' ),
					'insert_into_item'      => __( 'Insert into event', 'spiffy-calendar' ),
					'uploaded_to_this_item' => __( 'Uploaded to this event', 'spiffy-calendar' ),
					'filter_items_list'     => __( 'Filter Events', 'spiffy-calendar' ),
					'items_list_navigation' => __( 'Events navigation', 'spiffy-calendar' ),
					'items_list'            => __( 'Events list', 'spiffy-calendar' ),
				),
				'public'      => true,
				'has_archive' => false,
				'supports' => ['author', 'title', 'editor', 'thumbnail' ],
				'taxonomies'  => array ( 'spiffy_categories' ),
				'rewrite'     => array( 'slug' => 'events' ),
				'capability_type' => 'spiffycal',
				'menu_icon' =>	$spiffy_calendar->spiffy_icon,
				'map_meta_cap' => true
			)
		);


		$single_cat = esc_html($spiffy_calendar->current_options['category_singular']);
		$plural_cat = esc_html($spiffy_calendar->current_options['category_plural']);
		$single_cat = ( $single_cat == '' )? __( 'Category', 'spiffy-calendar' ) : $single_cat;
		$plural_cat = ( $plural_cat == '' )? __( 'Categories', 'spiffy-calendar' ) : $plural_cat;
		register_taxonomy( 'spiffy_categories', 
				array('spiffy_event'),
				array(
					'hierarchical' => true, 
					'labels' => array(
							'name' 				=> $plural_cat,
							'singular_name' 	=> $single_cat,
							'menu_name'			=> $plural_cat,
							'search_items' 		=> __( 'Search', 'spiffy-calendar').' '.$plural_cat,
							'all_items' 		=> __( 'All', 'spiffy-calendar').' '.$plural_cat,
							'parent_item' 		=> __( 'Parent', 'spiffy-calendar').' '.$single_cat,
							'parent_item_colon' => __( 'Parent:', 'spiffy-calendar').' '.$single_cat,
							'edit_item' 		=> __( 'Edit', 'spiffy-calendar').' '.$single_cat,
							'update_item' 		=> __( 'Update', 'spiffy-calendar').' '.$single_cat,
							'add_new_item' 		=> __( 'Add New', 'spiffy-calendar').' '.$single_cat,
							'new_item_name' 	=> __( 'New', 'spiffy-calendar').' '.$single_cat.' '.__( 'Name', 'spiffy-calendar')
						),
					'show_ui' => true,
					'show_admin_column' => true,
					'show_in_nav_menus' => false,
					'public' => false,
					// 'show_in_rest' => true,
					'query_var' => true,
					'capabilities'=> array(
							'manage_terms' 		=> 'manage_spiffy_terms',
							'edit_terms' 		=> 'edit_spiffy_terms',
							'delete_terms' 		=> 'delete_spiffy_terms',
							'assign_terms' 		=> 'assign_spiffy_terms',
						),
					'rewrite' => array( 'slug' => 'event-category' ) 
				) 
			);
	}
	
	/*
	** Admin area setup
	*/
	function admin_init_action () {
		global $pagenow;
		

		// manage colunms
		add_filter( 'manage_spiffy_event_posts_columns', array( $this, 'set_custom_columns') );

		// make columns sortable
		add_filter( 'manage_edit-spiffy_event_sortable_columns', array( $this, 'my_set_sortable_columns') );

		// populate column cells
		add_action( 'manage_spiffy_event_posts_custom_column' , array( $this, 'custom_column_values'), 10, 2 );	

		// set query to sort
		add_action( 'pre_get_posts', array( $this, 'my_sort_custom_column_query') );

		$taxonomy = 'spiffy_categories';
		add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'taxonomy_rows'), 15, 3 );
		add_filter( 'manage_edit-' . $taxonomy . '_columns',  array( $this, 'taxonomy_columns') );
		
	}
	
	/*
	** Add spiffy capabilities to the given role
	*/
	function add_caps ($role, $limit) {
		if ($role == null) return;
		foreach ( $this->allowed_capabilities as $cap ) {
			$role->add_cap( $cap );
		}

		foreach ( $this->others_capabilities as $cap ) {
			if ($limit == 'true') {
				$role->remove_cap( $cap );
			} else {
				$role->add_cap( $cap );
			}
		}
	}
		
	/*
	** Action to take when plugin is deactivated
	*/
 	function deactivate () {
		// Remove calendar capabilities from all roles
		$role = get_role( 'administrator' );
		$this->remove_caps ($role);
		foreach ( $this->admin_capabilities as $cap ) {
			$role->remove_cap( $cap );
		}

		$this->remove_caps (get_role( 'subscriber' ));
		$this->remove_caps (get_role( 'contributor' ));
		$this->remove_caps (get_role( 'author' ));
		$this->remove_caps (get_role( 'editor' ));
	}
	
	/*
	** Remove spiffy capabilities from the given role
	*/
	function remove_caps ($role) {
		if ($role == null) return;
		foreach ( $this->allowed_capabilities as $cap ) {
			$role->remove_cap( $cap );
		}
		foreach ( $this->others_capabilities as $cap ) {
			$role->remove_cap( $cap );
		}
	}

	/*
	** Adjust user roles to match option settings
	**
	** $cap corresponds to lowest allowed level
	** $limit true for limited / false if allowed to edit others 
	*/
	function adjust_roles ($cap = 'manage_options', $limit = 'false') {
		// Give all calendar capabilities to administrator
		$role = get_role( 'administrator' );
		$this->add_caps ($role, false);

		foreach ( $this->admin_capabilities as $admin_cap ) {
			$role->add_cap( $admin_cap );
		}
		
		// Other roles depend on the given lowest allowed level
		if ($cap == 'read') { 
			// Subscriber and above
			$this->add_caps (get_role( 'subscriber' ), $limit);
			$this->add_caps (get_role( 'contributor' ), $limit);
			$this->add_caps (get_role( 'author' ), $limit);
			$this->add_caps (get_role( 'editor' ), $limit);
		} else if ($cap == 'edit_posts') { 
			// Contributor and above
			$this->remove_caps (get_role( 'subscriber' ));
			$this->add_caps (get_role( 'contributor' ), $limit);
			$this->add_caps (get_role( 'author' ), $limit);
			$this->add_caps (get_role( 'editor' ), $limit);
		} else if ($cap == 'publish_posts') {
			// Author and above
			$this->remove_caps (get_role( 'subscriber' ));
			$this->remove_caps (get_role( 'contributor' ));
			$this->add_caps (get_role( 'author' ), $limit);
			$this->add_caps (get_role( 'editor' ), $limit);
		} else if ($cap == 'moderate_comments') { 
			// Editor
			$this->remove_caps (get_role( 'subscriber' ));
			$this->remove_caps (get_role( 'contributor' ));
			$this->remove_caps (get_role( 'author' ));
			$this->add_caps (get_role( 'editor' ), $limit);
		} else if ($cap == 'manage_options') { 
			// Administrator only
			$this->remove_caps (get_role( 'subscriber' ));
			$this->remove_caps (get_role( 'contributor' ));
			$this->remove_caps (get_role( 'author' ));
			$this->remove_caps (get_role( 'editor' ));
		}		

		// echo "ADMIN<br><br>";
		// print_r ( get_role( 'administrator' )->capabilities );
		// echo "<br><br>EDITOR<br><br>";
		// print_r ( get_role( 'editor' )->capabilities );
	}

	// Extend the Events admin buttons
	function events_admin_buttons($views){
		global $post_type_object, $spiffy_calendar;

		if ( !$spiffy_calendar->bonus_addons_active() ) {
			$disabled = 'disabled="disabled"';
		} else {
			$disabled = '';
		}
		
		ob_start();
		?>
		<a href='<?php echo esc_url($_SERVER['REQUEST_URI']); ?>&spiffy_csv_export=true&nonce=<?php echo wp_create_nonce( 'spiffy_export_nonce' ); ?>' <?php echo $disabled; ?> >
			<?php _e( 'Export CSV','spiffy-calendar' ); ?>
		</a> | <a href="#" onclick='jQuery("#div_import").css("display", "inline"); return false;' <?php echo $disabled; ?> >
			<?php _e('Import CSV','spiffy-calendar'); ?>
		</a>
		<form id='div_import' class="spiffy-form" name='div_import' style="display: none; margin-left: 5px;" action="" method="POST" enctype='multipart/form-data'>
			<input type="file" name="spiffy_csv" multiple="false" <?php echo $disabled; ?> />
			<input type="submit" <?php echo $disabled; ?> value="<?php _e ( 'Import','spiffy-calendar'); ?>" name="import_events" id="import_events" class="button-primary spiffy-submit action" />
			<input type="hidden" value="true" name="save_spiffycal">
			<?php _e('Import events from CSV', 'spiffy-calendar'); ?>
			<?php 	if ( function_exists('wp_nonce_field') ) wp_nonce_field('update_spiffycal_options', 'update_spiffycal_options_nonce'); ?>
		</form>

		<?php	  
		if ( !$spiffy_calendar->bonus_addons_active() ) {
			echo " <small><em>* ";
			_e ('CSV import/export is a bonus feature', 'spiffy-calendar');
			echo "</em></small>";
		}
		?>

		<?php
		$views['csv'] = ob_get_clean( );

		return $views;
	}	

	/*
	** Add category filter to event list
	**
	**  $which (the position of the filters form) is either 'top' or 'bottom'
	*/
	function my_category_filter ( $post_type, $which ) {
		if ( 'top' === $which && 'spiffy_event' === $post_type ) {
			$taxonomy = 'spiffy_categories';
			$tax = get_taxonomy( $taxonomy );            // get the taxonomy object/data
			$cat = filter_input( INPUT_GET, $taxonomy ); // get the selected category slug

			echo '<label class="screen-reader-text" for="spiffy_categories">Filter by ' .
				esc_html( $tax->labels->singular_name ) . '</label>';

			wp_dropdown_categories( [
				'show_option_all' => $tax->labels->all_items,
				'hide_empty'      => 0, // include categories that have no posts
				'hierarchical'    => $tax->hierarchical,
				'show_count'      => 0, // don't show the category's posts count
				'orderby'         => 'name',
				'selected'        => $cat,
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'value_field'     => 'slug',
			] );
		}
	}

	/*
	** Remove date filter from event list since it is publish date rather than event date
	*/
	function my_remove_date_filter( $months ) {
		global $typenow; // use this to restrict it to a particular post type
		if ( $typenow == 'spiffy_event' ) {
			return array(); // return an empty array
		}
		return $months; // otherwise return the original for other post types
	}

	/*
	** Support event duplication
	*/
	function copy_post_link ( $actions, $post ) {
		
		if ( $post->post_type == 'spiffy_event' && current_user_can( 'edit_spiffycals' ) ) {
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'spiffy_copy_event',
						'post' => $post->ID,
					),
					'admin.php'
				),
				basename(__FILE__),
			);

			$actions[ 'copy' ] = '<a href="' . esc_url( $url ) . '" title="' . __('Copy','spiffy-calendar') . '">' . __('Copy','spiffy-calendar') . '</a>';
		}

		return $actions;		
	}
	
	/*
	** Copy an event
	*/
	function copy_event () {
		// check if post ID has been provided and action
		if ( empty( $_GET[ 'post' ] ) ) {
			wp_die( __('Bad copy request', 'spiffy-calendar') );
		}

		// Nonce verification
		check_admin_referer( basename( __FILE__ ) );

		// Get the original post ID and data
		$post_id = absint( $_GET[ 'post' ] );
		$post = get_post( $post_id );

		// Adjust author to current user
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;

		if( $post ) {

			// new post data array
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'Draft',
				'post_title'     => '(copy) ' . $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);

			// insert the post by wp_insert_post() function
			$new_post_id = wp_insert_post( $args );

			/*
			 * get all current post terms and set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies( $post->post_type );
			if( $taxonomies ) {
				foreach( $taxonomies as $taxonomy ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
					wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
				}
			}

			// duplicate all post meta
			$post_meta = get_post_meta( $post_id );
			if( $post_meta ) {

				foreach ( $post_meta as $meta_key => $meta_values ) {
					// we need to exclude some system meta keys
					if( in_array( $meta_key, array( '_edit_lock', '_wp_old_slug' ) ) ) {
						continue;
					}
					// do not forget that each meta key can have multiple values
					foreach ( $meta_values as $meta_value ) {
						add_post_meta( $new_post_id, $meta_key, $meta_value );
					}
				}
			}

			// finally, redirect to the edit post screen for the new draft
			wp_safe_redirect(
				add_query_arg(
					array(
						'action' => 'edit',
						'post' => $new_post_id
					),
					admin_url( 'post.php' )
				)
			);
			exit;

		} else {
			wp_die( __('Copy failed', 'spiffy-calendar') );
		}		
	}
	
	/*
	** Add class to taxonomy link so they can be color coded
	*/
	function fix_taxonomy_links ($term_links, $taxonomy, $terms ) {
		if ( ($taxonomy == 'spiffy_categories') && ( $term_links != null) ){
			$pos = strpos ($term_links[0], 'href');
			foreach ($term_links as $key => $value) {
				$term_links[$key] = substr_replace($value, 'class="spiffy_categories_' . $terms[$key]->slug . '" ', $pos, 0);
			}
		}
		return ($term_links);
	}

	function set_default_hidden_columns( $hidden, $screen ) {
		if( isset( $screen->id ) && 'edit-spiffy_event' === $screen->id ){      
			$hidden[] = 'date';     
		}   
		return $hidden;
	}

	/*
	** Remove events from search results if configured
	*/
	function remove_post_type_from_search_results( $query ) {
		global $spiffy_calendar;
		
		if ( !$spiffy_calendar->current_options['exclude_from_search'] ) return;
		
		/* check is front end main loop content */
		if(is_admin() || !$query->is_main_query()) return;

		/* check is search result query */
		if($query->is_search()){

			$post_type_to_remove = 'spiffy_event';
			/* get all searchable post types */
			$searchable_post_types = get_post_types(array('exclude_from_search' => false));

			/* make sure you got the proper results, and that your post type is in the results */
			if(is_array($searchable_post_types) && in_array($post_type_to_remove, $searchable_post_types)){
				/* remove the post type from the array */
				unset( $searchable_post_types[ $post_type_to_remove ] );
				/* set the query to the remaining searchable post types */
				$query->set('post_type', $searchable_post_types);
			}
		}
	}

	/*
	** Event admin arrange custom columns
	*/
	function set_custom_columns ($columns) {
		$new_columns['cb'] = $columns['cb'];
		$new_columns['title'] = $columns['title'];
		$new_columns['_spiffy_event_begin'] = __( 'Start Date', 'spiffy-calendar' );
		$new_columns['_spiffy_event_end'] = __( 'End Date', 'spiffy-calendar' );
		$new_columns['_spiffy_event_time'] = __( 'Start Time', 'spiffy-calendar' );
		$new_columns['_spiffy_event_end_time'] = __( 'End Time', 'spiffy-calendar' );
		$new_columns['_spiffy_event_recur'] = __( 'Recurs', 'spiffy-calendar' );
		$new_columns['_spiffy_event_repeats'] = __( 'Repeats', 'spiffy-calendar' );
		$new_columns['_spiffy_event_hide_events'] = __( 'Hide Events', 'spiffy-calendar' );
		$new_columns['_spiffy_event_show_title'] = __( 'Show Title', 'spiffy-calendar' );
		$new_columns['_thumbnail_id'] = __( 'Image', 'spiffy-calendar' );
		$new_columns['taxonomy-spiffy_categories'] = $columns['taxonomy-spiffy_categories'];
		$new_columns['author'] = $columns['author'];
		$new_columns['date'] = $columns['date'];
		// $new_columns['testing'] = print_r ($columns, true);
		return $new_columns;
	}
	
	/*
	** Event admin display custom columns
	*/
	function custom_column_values( $column, $post_id ) {

		switch ( $column ) {

			case '_spiffy_event_begin' :
			case '_spiffy_event_end' :
				echo esc_html (get_post_meta( $post_id , $column , true ));
				break;
				
			case '_spiffy_event_time' :
			case '_spiffy_event_end_time' :
				$item = get_post_meta( $post_id , $column , true );
				if ($item == '') {
					echo '-';
				} else {
					echo date (get_option('time_format'), strtotime( $item ));
				}
				break;
			
			case '_spiffy_event_recur' :
				// Interpret the DB values into something human readable
				$item = get_post_meta( $post_id , $column , true );
				if ($item == 'S') { echo '-'; } 
				else if ($item == 'W') { echo __('Weekly','spiffy-calendar'); }
				else if ($item == 'M') { echo __('Monthly (date)','spiffy-calendar'); }
				else if ($item == 'U') { echo __('Monthly (day)','spiffy-calendar'); }
				else if ($item == 'Y') { echo __('Yearly','spiffy-calendar'); }
				else if ($item == 'D') { 
					$mult = get_post_meta( $post_id , '_spiffy_event_recur_multiplier' , true );
					echo __('Every','spiffy-calendar') . ' ' . intval($mult) . ' ' . __('days','spiffy-calendar'); 
				}
				break;

			case '_spiffy_event_repeats':
				// Interpret the DB values into something human readable
				$item = get_post_meta( $post_id , $column , true );
				if (get_post_meta( $post_id , '_spiffy_event_recur' , true ) == 'S') { echo '-'; }
				else if ($item == 0) { echo __('Forever','spiffy-calendar'); }
				else if ($item > 0) { echo $item .' '.__('Times','spiffy-calendar'); }
				break;

			case '_spiffy_event_hide_events':
				// interpret the hide_events value
				if (get_post_meta( $post_id , $column , true ) == 'T') { echo __('True', 'spiffy-calendar'); }
				else { echo __('False', 'spiffy-calendar'); }
				break;
				
			case '_spiffy_event_show_title':
				if (get_post_meta( $post_id , '_spiffy_event_hide_events', true ) == 'F') { echo '-'; }
				else {      // hide_event event
					if (get_post_meta( $post_id , $column , true ) == 'T') { echo __('True', 'spiffy-calendar'); }
					else  { echo __('False', 'spiffy-calendar'); }
				}
				break;
			
			case '_thumbnail_id':
				$item = get_post_meta( $post_id , $column , true );
				if ($item > 0) {
					$image = wp_get_attachment_image_src( $item, 'thumbnail');
					echo '<img src="' . $image[0] . '" width="76px" />';
				}
				break;

		}
	}

	/*
	** Allow event list to sort by event begin time meta field
	*/
	function my_set_sortable_columns( $columns ) {
		$columns['_spiffy_event_begin'] = '_spiffy_event_begin';
		return $columns;
	}

	/*
	** Set the default sort order for events to sort by event begin time descending
	*/
	function my_sort_custom_column_query( $query ) {
		if( !is_admin() || ( 'spiffy_event' != $query->get( 'post_type' ) ))
			return;
			 
		$orderby = $query->get( 'orderby' );

		if ( ('_spiffy_event_begin' == $orderby) || (!isset( $_GET['orderby'])) ) {

			$meta_query = array(
				'relation' => 'OR',
				array(
					'key' => '_spiffy_event_begin',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => '_spiffy_event_begin',
				),
			);

			$query->set( 'meta_query', $meta_query );
			$query->set( 'orderby', 'meta_value' );
			if ( !isset( $_GET['orderby']) ) {
				$query->set( 'order', 'DESC' );
				$_GET['orderby'] = '_spiffy_event_begin'; 	// set the sorting indicator
				$_GET['order'] = 'desc';					// to reflect the modified default sort order
			}
		}
	}

	/*
	** Category colour support in category editor
	*/
	function edit_custom_fields($tag) {
		if (isset ($tag->term_id)) {
			$value = get_term_meta($tag->term_id, 'color', true);
		} else {
			$value = '';
		}
		?>

			<tr class="form-field">
			<th scope="row" valign="top">
				<label for="spiffy_tax_color"><?php _e('Color', 'spiffy-calendar'); ?></label>
			</th>
			<td>
				<input type="text" id="spiffy_tax_color" name="term_meta[color]" class="color-picker spiffy-color-field"
					   value="<?php echo esc_html($value) ?>">

				<script>
					jQuery(function ($) {
						$('.color-picker').wpColorPicker();
					})
				</script>
			</td>
		</tr>


		<?php
	}	
	
	function save_custom_fields( $term_id ) {

		if ( !isset( $_POST['term_meta'] ) ) return;


		foreach ( $_POST['term_meta'] as $slug => $value){

			switch($slug){
				default:
					$value = sanitize_text_field( $value );
					break;
			}

			update_term_meta( $term_id, $slug, $value );
		}

	}

	function taxonomy_columns( $columns ) {
		unset ($columns['description']);
		$columns['color'] = __( 'Color', 'spiffy-calendar' );
		$columns['cat_id'] = __( 'ID', 'spiffy-calendar' );
		return $columns;
	}

	function taxonomy_rows( $row, $column_name, $term_id ) {
		if ( 'color' === $column_name ) {
			$meta = esc_html (get_term_meta($term_id, 'color', true));
			return ( '<div style="background-color:'.$meta.';width:1em;">&nbsp;</div>' ); 
		}
		if ( 'cat_id' === $column_name ) {
			return ( $term_id ); 
		}
	}

	/**
	 * Category quick edit display in form
	 */
	function quick_edit_category_field( $column_name, $screen ) {
		// If we're not iterating over our custom column, then skip
		if ( $screen != 'spiffy_categories' && $column_name != 'color' ) {
			return false;
		}
		?>
		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php _e( 'Color', 'spiffy-calendar' ); ?></span>
					<span class="input-text-wrap"><input type="color" class="ptitle" name="term_meta[color]" value=""></span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	/*
	**  Category quick edit load value
	*/
	function quickedit_category_javascript() {
		$current_screen = get_current_screen();

		if ( $current_screen->id != 'edit-spiffy_categories' || $current_screen->taxonomy != 'spiffy_categories' ) {
			return;
		}

		// Ensure jQuery library is loaded
		wp_enqueue_script( 'jquery' );
		?>
		<script type="text/javascript">
			/*global jQuery*/
			jQuery(function($) {
				// $('#the-list').on( 'click', '.editinline', function( e ) {
					// e.preventDefault();
					// var $tr = $(this).closest('tr');
					// var val = $tr.find('td.color').find('div').css( "background-color" );
					// Update field
					// $('tr.inline-edit-row :input[name="term_meta[color]"]').wpColorPicker('color', val);
				// });

				// $('.inline-edit-row').on( 'click', '', function( e ) {
					// alert('click');
					// tr.inline-edit-row :input[name="term_meta[color]"]').wpColorPicker('color', val);
				// });
				
			const wp_inline_edit_function = inlineEditTax.edit;

			// we overwrite the it with our own
			inlineEditTax.edit = function( post_id ) {

				// let's merge arguments of the original function
				wp_inline_edit_function.apply( this, arguments );

				// get the post ID from the argument
				if ( typeof( post_id ) == 'object' ) { // if it is object, get the ID number
					post_id = parseInt( this.getId( post_id ) );
				}

				// add rows to variables
				const edit_row = $( '#edit-' + post_id )
				const post_row = $( '#tag-' + post_id )

				const eventColor = $( '.color div', post_row ).css( "background-color" );
				const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`
				// alert(rgb2hex(eventColor));
				
				// populate the inputs with column data
				$( ':input[name="term_meta[color]"]', edit_row ).val( rgb2hex(eventColor) );
			}
			});
			</script>

		<?php
	}

}
}

if (class_exists("SPIFFYCAL_customposts")) {
	global $spiffycal_custom_posts;
	$spiffycal_custom_posts = new SPIFFYCAL_customposts();
}