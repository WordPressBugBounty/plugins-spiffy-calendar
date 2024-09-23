<?php
/**
 * Admin View: Settings tab "Categories"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!current_user_can('manage_options'))
	wp_die(__('You do not have sufficient permissions to access this page.','spiffy-calendar'));	

global $wpdb;

// Add warning if categories are disabled
if ($this->current_options['enable_categories'] != 'true') { ?>
<div id="message" class="error">
	<p><?php echo __('Event categories are currently disabled. To enable this feature visit the Options tab.','spiffy-calendar'); ?></p>
</div>
<?php } 

// Look for category edit request first
foreach($_POST as $key => $value) {
	$k_array = explode("_", $key, 2); 
	if(isset($k_array[0]) && $k_array[0] == "edit") {
		$category_id = intval(sanitize_text_field($k_array[1]));
		if ($category_id == 0) wp_die ( __('Invalid request','spiffy-calendar') ); // quit if invalid category specified
		
		$sql = $wpdb->prepare("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " WHERE category_id=%d", $category_id);
		$cur_cat = $wpdb->get_row($sql);
		if ($cur_cat == NULL) wp_die ( __('Invalid request','spiffy-calendar') ); // quit if invalid category specified
			?>
		<h3><?php echo __('Edit','spiffy-calendar') . ' ' . esc_html($this->current_options['category_singular']); ?></h3>
		<input type="hidden" name="category_id" value="<?php echo $category_id; ?>" />
		<table class="form-table">
		<tr>
		<th scope="row"><?php _e('Name','spiffy-calendar'); ?></th>
		<td><input type="text" name="category_name_edit" class="input" size="30" maxlength="30" value="<?php echo esc_html(stripslashes($cur_cat->category_name)); ?>" /></td>
		</tr>
		<tr>
		<th scope="row"><?php _e('Colour (Hex format)','spiffy-calendar'); ?></th>
		<td><input type="text" class="spiffy-color-field" name="category_colour_edit" class="input" size="10" maxlength="7" value="<?php echo esc_html($cur_cat->category_colour); ?>" /></td>
		</tr>
		</table>
		<input type="submit" name="update_category" class="button button-primary spiffy-submit" value="<?php _e('Save Changes','spiffy-calendar'); ?> &raquo;" />
	<?php
		break;
	}
}

if (!isset($category_id)) {
	$cat_name = isset($_POST['category_name'])? sanitize_text_field($_POST['category_name']) : '';
	$cat_colour = isset($_POST['category_colour'])? sanitize_text_field($_POST['category_colour']) : '';
?>
<h3><?php echo __('Add','spiffy-calendar') . ' ' . esc_html($this->current_options['category_singular']); ?></h3>
<table class="form-table">
<tr>
<th scope="row"><?php _e('Name','spiffy-calendar'); ?></th>
<td><input type="text" name="category_name" class="input" size="30" maxlength="30" value="<?php echo esc_html($cat_name) ?>" /></td>
</tr>
<tr>
<th scope="row"><?php _e('Colour','spiffy-calendar'); ?></th>
<td><input type="text" class="spiffy-color-field" name="category_colour" class="input" size="10" maxlength="7" value="<?php echo esc_html($cat_colour) ?>" />
<p class="description">Hex format</p></td>
</tr>
</table>
	<input type="submit" name="add_category" class="button button-primary spiffy-submit" value="<?php echo __('Add','spiffy-calendar') . ' ' . esc_html($this->current_options['category_singular']); ?> &raquo;" />

<h3><?php echo __('Manage','spiffy-calendar') . ' ' . esc_html($this->current_options['category_plural']); ?></h3>
	<?php
		
	// We pull the categories from the database	
	$categories = $wpdb->get_results("SELECT * FROM " . $wpdb->get_blog_prefix().WP_SPIFFYCAL_CATEGORIES_TABLE . " ORDER BY category_id ASC");

	if ( !empty($categories) ) {
		 ?>
<table class="form-table">
<thead> 
<tr>
	 <th class="manage-column" scope="col"><?php _e('ID','spiffy-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Name','spiffy-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Colour','spiffy-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Edit','spiffy-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Delete','spiffy-calendar') ?></th>
</tr>
</thead>
		<?php
		$class = '';
		foreach ( $categories as $category ) {
			 $class = ($class == 'alternate') ? '' : 'alternate';
			 ?>
 <tr class="<?php echo $class; ?>">
	 <th scope="row"><?php echo $category->category_id; ?></th>
	 <td><?php echo esc_html(stripslashes($category->category_name)); ?></td>
	 <td><span style="display:block; width: 60px; background-color:<?php echo esc_html($category->category_colour); ?>;">&nbsp;</span></td>
	 <td>
			<input type="submit" name="edit_<?php echo $category->category_id; ?>" class="button bold spiffy-submit" value="<?php _e('Edit','spiffy-calendar'); ?> &raquo;" />
	</td>
			 <?php
			if ($category->category_id == 1) {
				echo '<td>'.__('N/A','spiffy-calendar').'</td>';
			} else {
				?>
	 <td>
		<input type="submit" name="delete_<?php echo $category->category_id; ?>" class="button bold spiffy-submit" value="<?php _e('Delete','spiffy-calendar'); ?> &raquo;" onclick="return confirm('<?php echo __('Are you sure you want to delete the category named &quot;','spiffy-calendar').esc_js($category->category_name).'&quot;?'; ?>')" />

	</td>
				<?php
			}
			?>
</tr>
			<?php
		}
		?>
</table>
		<?php
	} else {
		 echo '<p>'.__('There are no categories in the database - something has gone wrong!','spiffy-calendar').'</p>';
	}
}
?>