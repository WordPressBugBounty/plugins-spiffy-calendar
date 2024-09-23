<?php
/**
 * Admin View: Settings tab "Options"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!current_user_can('manage_options'))
	wp_die(__('You do not have sufficient permissions to access this page.','spiffy-calendar'));	

// Now we render the form
		?>
<h3><?php _e('Users','spiffy-calendar'); ?></h3>

<table class="form-table">
<?php 
	// Determine default/selected Event Manager Role
	$subscriber_selected = '';
	$contributor_selected = '';
	$author_selected = '';
	$editor_selected = '';
	$admin_selected = '';
	if ($this->current_options['can_manage_events'] == 'read') { $subscriber_selected='selected="selected"';}
	else if ($this->current_options['can_manage_events'] == 'edit_posts') { $contributor_selected='selected="selected"';}
	else if ($this->current_options['can_manage_events'] == 'publish_posts') { $author_selected='selected="selected"';}
	else if ($this->current_options['can_manage_events'] == 'moderate_comments') { $editor_selected='selected="selected"';}
	else if ($this->current_options['can_manage_events'] == 'manage_options') { $admin_selected='selected="selected"';}
?>
<tr>
	<th scope="row">
		<?php _e('Event manager role','spiffy-calendar'); ?>
	</th>
	<td>
		<select name="permissions">
			<option value="subscriber"<?php echo $subscriber_selected ?>><?php _e('Subscriber','spiffy-calendar')?></option>
			<option value="contributor" <?php echo $contributor_selected ?>><?php _e('Contributor','spiffy-calendar')?></option>
			<option value="author" <?php echo $author_selected ?>><?php _e('Author','spiffy-calendar')?></option>
			<option value="editor" <?php echo $editor_selected ?>><?php _e('Editor','spiffy-calendar')?></option>
			<option value="admin" <?php echo $admin_selected ?>><?php _e('Administrator','spiffy-calendar')?></option>
		</select>
		<span class="description"><?php _e('Choose the lowest user group that can manage events','spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Limit non-admins to editing their own events only','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="limit_author" <?php if ( $this->current_options['limit_author'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
</table>

<h3><?php _e('Display Preferences','spiffy-calendar'); ?></h3>

<table class="form-table">

<tr>
	<th scope="row">
		<?php _e('Display author name on events','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="display_author" <?php if ( $this->current_options['display_author'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Enable detailed event display','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="display_detailed" <?php if ( $this->current_options['display_detailed'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('When this option is enabled the time and image will be listed with the event title. Note that time and image are always displayed in the popup window.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display a jumpbox for changing month and year quickly','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="display_jump" <?php if ( $this->current_options['display_jump'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display a toggle between grid and list view with the full size calendar','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="grid_list_toggle" <?php if ( $this->current_options['grid_list_toggle'] === true) echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display all day events at the end of the list','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="all_day_last" <?php if ( $this->current_options['all_day_last'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('If not selected, all day events are listed first, followed by events sorted by start time.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display week numbers in the full size calendar','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="display_weeks" <?php if ( $this->current_options['display_weeks'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Open event links in new window','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="enable_new_window" <?php if ( $this->current_options['enable_new_window'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Open map links in new window','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="map_new_window" <?php if ( $this->current_options['map_new_window'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Enable expanded mini calendar popup','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="enable_expanded_mini_popup" <?php if ( $this->current_options['enable_expanded_mini_popup'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('When this option is disabled the time and title will be listed in the mini calendar popup. When this option is enabled, the description is also displayed.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<?php 
	// Determine default/selected mini calendar popup location
	$left_selected = '';
	$center_selected = '';
	$right_selected = '';
	if ($this->current_options['mini_popup'] == 'left') { $left_selected='selected="selected"';}
	else if ($this->current_options['mini_popup'] == 'center') { $center_selected='selected="selected"';}
	else if ($this->current_options['mini_popup'] == 'right') { $right_selected='selected="selected"';}
?>
<tr>
	<th scope="row">
		<?php _e('Mini calendar popup location','spiffy-calendar'); ?>
	</th>
	<td>
		<select name="mini_popup">
			<option value="left"<?php echo $left_selected ?>><?php _e('Left','spiffy-calendar')?></option>
			<option value="center" <?php echo $center_selected ?>><?php _e('Center','spiffy-calendar')?></option>
			<option value="right" <?php echo $right_selected ?>><?php _e('Right','spiffy-calendar')?></option>
		</select>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display link to add to Google calendar on each event','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="link_google_cal" <?php if ( $this->current_options['link_google_cal'] == 'true') echo 'checked'; ?>>
	</td>
</tr>

<tr>
	<th scope="row">
		<?php _e('More details link text','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="text" name="more_details" value="<?php echo esc_html($this->current_options['more_details']); ?>" />
	</td>
</tr>

<tr>
	<th scope="row">
		<?php _e('Event Title label','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="text" name="title_label" value="<?php echo esc_html($this->current_options['title_label']); ?>" />
	</td>
</tr>

</table>

<h3><?php _e('Upcoming Events','spiffy-calendar'); ?></h3>

<table class="form-table">

<tr>
	<th scope="row">
		<?php _e('Display upcoming events for','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="text" name="display_upcoming_days" value="<?php echo esc_html($this->current_options['display_upcoming_days']); ?>" size="3" maxlength="3" /> <?php _e('days into the future','spiffy-calendar'); ?>.
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Include today in the upcoming list','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="upcoming_includes_today" <?php if ( $this->current_options['upcoming_includes_today'] == 'true') echo 'checked'; ?>>
	</td>
</tr>
</table>

<h3><?php _e('Categories','spiffy-calendar'); ?></h3>

<table class="form-table">

<tr>
	<th scope="row">
		<?php _e('Enable event categories','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="enable_categories" <?php if ( $this->current_options['enable_categories'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('This will enable or disable the colouring of categories and the category key in front end displays.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Category Titles','spiffy-calendar'); ?>
	</th>
	<td>
		<?php _e('Singular','spiffy-calendar'); ?>: <input type="text" name="category_singular" value="<?php echo esc_html($this->current_options['category_singular']); ?>" />
		<?php _e('Plural','spiffy-calendar'); ?>: <input type="text" name="category_plural" value="<?php echo esc_html($this->current_options['category_plural']); ?>" />
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Sort categories alphabetically','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="alphabetic_categories" <?php if ( $this->current_options['alphabetic_categories'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('The default order is sorted by category ID.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Enable the category filter','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="category_filter" <?php if ( $this->current_options['category_filter'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('The category key will act as a filter on the full calendar.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display category key above calendar','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" name="category_key_above" <?php if ( $this->current_options['category_key_above'] == 'true') echo 'checked'; ?>>
		<span class="description"><?php _e('The default position is below the full calendar.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Use category color as background color','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" id="spiffy_category_bg_color" name="category_bg_color" <?php if ( $this->current_options['category_bg_color'] === true) echo 'checked'; ?>>
		<span class="description"><?php _e('When this option is disabled the category color is used for the text color in the main calendar. When this option is enabled, the category color is used for the background color instead.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Display category name with event title','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="checkbox" id="spiffy_category_name_display" name="category_name_display" <?php if ( $this->current_options['category_name_display'] === true) echo 'checked'; ?>>
		<span class="description"><?php _e('When this option is enabled the category name is inserted at the beginning of the event title followed by -.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr id="spiffy-category-text">
	<th scope="row">
		<?php _e('Category text color','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="text" class="spiffy-color-field" name="category_text_color" data-default-color="#FFFFFF" class="input" size="10" maxlength="7" value="<?php echo esc_html($this->current_options['category_text_color']); ?>" />
		<span class="description"><?php _e('Default is white', 'spiffy-calendar'); ?></span>
	</td>
</tr>
</table>

<h3><?php _e('Formatting','spiffy-calendar'); ?></h3>

<table class="form-table">
<tr>
	<th scope="row">
		<?php _e('Responsive maximum width','spiffy-calendar'); ?>
	</th>
	<td>
		<input type="text" name="responsive_width" value="<?php echo esc_html($this->current_options['responsive_width']); ?>" size="3" maxlength="3" />
		<span class="description"><?php _e('Enter 0 to disable the responsive full size calendar. Otherwise enter an integer number of pixels. Recommended value is 600.', 'spiffy-calendar'); ?></span>
	</td>
</tr>
<tr>
	<th scope="row">
		<?php _e('Custom CSS styles','spiffy-calendar'); ?>
	</th>
	<td>
		<textarea name="style" rows="10" cols="60" tabindex="2"><?php echo stripslashes(esc_textarea($this->current_options['calendar_style'])); ?></textarea><br />
		<input type="checkbox" name="reset_styles" /> <?php _e('Tick this box if you wish to reset the Calendar style to default','spiffy-calendar'); ?>
		<p class="description"><?php _e('Default styles are always loaded. If you would like to add additional custom CSS you may do so here.', 'spiffy-calendar'); ?></p>
	</td>
</tr>
</table>

<p class="submit"><input class="button button-primary spiffy-submit" name="submit" value="<?php echo __('Save Changes','spiffy-calendar'); ?>" type="submit" /></p>