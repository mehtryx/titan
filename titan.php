<?php
/*
Plugin Name: WP Post Importer
Plugin URI: http://github.com/postmedia/
Description: Pull content via Wordpress public API 
Version: 1.1.3
Author: Postmedia Network Inc.
License: MIT
*/

$pd_author_actions = array( 'discard' => 'Discard', 'create' => 'Create', 'random' => 'Random' );
$pd_wp_post_importer_options_temp = array();


if ( is_admin() ) {
	add_action( 'admin_menu', 'pd_wp_post_importer_create_menu' );
	add_action( 'admin_init', 'pd_wp_post_importer_register_settings' );
}

/**
* Retrieve options, merge in defaults, avoids need to use isset in all option page functions
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_get_option(){
	global $pd_author_actions; 
	$author_action = strtolower( $pd_author_actions['discard'] );
	$defaults = array( 'enabled' => false, 'author_action' => $author_action, 'create_cat' => false, 'filter_cat' => '', 'since' => '', 'frequency' => 'fifteen', 'number' => '10', 'blog' => '', 'blogs' => '[]' ) ; 
	$options = get_option( 'pd_wp_post_importer_config' );
	if ( is_array( $options ) ){
		return array_merge( $defaults, $options );
	}
	else {
		return $defaults; //options was blank.
	}
}

/**
 * Adds the WP Post Importer menu item to the settings dashboard
 * 
 * @uses add_options_page
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_create_menu(){
	$plugin_page = add_options_page( 'WP Post Importer', 'WP Post Importer', 'manage_options', 'pd_wp_post_importer', 'pd_wp_post_importer_options_page' );
	wp_enqueue_script( 'pd-wp-post-importer-admin', plugins_url( 'wp-post-importer-admin.js', __FILE__ ) );
}

/**
 * Register settings and create settings section
 *
 * @uses register_setting
 * @uses add_settings_section
 * @uses add_settings_field
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_register_settings(){
	register_setting( 'pd_wp_post_importer_config', 'pd_wp_post_importer_config', 'pd_wp_post_importer_validate_options' );
	add_settings_section( 'pd_wp_post_importer_main', 'WP Post Importer Plugin Options', 'pd_wp_post_importer_main_help', 'pd_wp_post_importer' );
	// Universal settings
	add_settings_field( 'pd_wp_post_importer_enabled', 'Enable Jobs', 'pd_wp_post_importer_enabled_check', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_frequency', 'Frequency', 'pd_wp_post_importer_frequency_select', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );

	// Blog Specific
	add_settings_field( 'pd_wp_post_importer_blog', 'Wordpress Blog', 'pd_wp_post_importer_blog_text', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_author_action', 'Author Action', 'pd_wp_post_importer_author_action_select', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_create_cat', 'Create Missing Categories', 'pd_wp_post_importer_create_cat_check', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_filter_cat', 'Filter by Categories', 'pd_wp_post_importer_filter_cat_text', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_since', 'Since Publish Date/Time', 'pd_wp_post_importer_since_text', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
	add_settings_field( 'pd_wp_post_importer_number', 'Number to Import', 'pd_wp_post_importer_number_text', 'pd_wp_post_importer', 'pd_wp_post_importer_main' );
}

/**
 * Render options page
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_options_page(){
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>WP Post Importer Settings</h2>
		<form action="options.php" method="post">
			<?php
			global $pd_author_actions;
			$options = pd_wp_post_importer_get_option();
			settings_fields( 'pd_wp_post_importer_config' );
			do_settings_sections( 'pd_wp_post_importer' );
			?>
			<input id="pd_wp_post_import_delete_blog" type="hidden" name="pd_wp_post_importer_config[delete_blogs]" value=""/>
			<span style="width:100px; padding:10px;"><input class="button-secondary" name="submit" type="submit" value="Add Blog / Save Settings" /></span>
			<hr />
			<table id="pd_wp_post_importer_blogs" class="form-table">
				<thead>
					<tr><th scope="row"><label><b>Blog Import List</b></label></th></tr>
					<tr>
						<th>Blog</th>
						<th>Author Action</th>
						<th>Create Categories</th>
						<th>Filter Categories</th>
						<th>Since</th>
						<th>Number</th>
						<th>Remove</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$blogs = json_decode( $options['blogs'], true );
						if ( is_array( $blogs ) ) {
							foreach( $blogs as $blog ) {
								echo '<tr id="' . preg_replace('/\./', '', $blog['blog']) . '">';
								echo '<td>' . $blog['blog'] . '</td>';
								echo '<td>' . $blog['author_action'] . '</td>';
								echo '<td>' . $blog['create_cat'] . '</td>';
								echo '<td>' . urldecode( $blog['filter_cat'] ) . '</td>';
								echo '<td>' . $blog['since'] . '</td>';
								echo '<td>' . $blog['number'] . '</td>';
								echo '<td>(<a href="javascript:void(pd_wp_post_importer_remove_blog(\'' . $blog['blog'] . '\'));">X</a>)</td></tr>';
							}
						}
					?>
				</tbody>
			</table>
		</form>
	</div>
	<hr />
	<div class="wrap">
		<?php $pd_wp_post_importer_history = pd_wp_post_importer_read_history(); ?>
		<h3>Job Status</h3>
		<table class="form-table">
			<tr>
				<td><label>Last Execution: </label></td>
				<td><?php echo ( $pd_wp_post_importer_history['last_ran'] ) ? date( 'd M Y - H:i e', $pd_wp_post_importer_history['last_ran'] ) : 'Never run' ?></td>
			</tr>
			<tr>
				<td><label>Last Error: </label></td>
				<td><?php echo ( $pd_wp_post_importer_history['last_error'] ) ? esc_attr( $pd_wp_post_importer_history['last_error'] ) : 'None' ?></td>
			</tr>
			<tr>
				<td><label>Execution Count: </label></td>
				<td><?php echo ( $pd_wp_post_importer_history['run_count'] ) ? esc_attr( $pd_wp_post_importer_history['run_count'] ) : '0' ?></td>
			</tr>
		</table>
	</div>
	<?php
}

/**
* Render enabled check for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_enabled_check(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_importer_enabled" type="checkbox" name="pd_wp_post_importer_config[enabled]" <?php checked ( $options['enabled'] ); ?>/>
	<?php
}

/**
* Render Author Select for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_author_action_select(){
	global $pd_author_actions; 
	$options = pd_wp_post_importer_get_option();
	?>
	<select id="pd_wp_post_importer_author_action" name="pd_wp_post_importer_config[author_action]">
		<?php
			foreach ( $pd_author_actions as $key => $value ) {
				echo '<option value="' . $key . '" ' . selected( $options['author_action'], $key ) . '>' . $value . '</option>';
			}
		?>
	</select>
	<?php
}

/**
* Render create categories check for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_create_cat_check(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_importer_create_cat" type="checkbox" name="pd_wp_post_importer_config[create_cat]" <?php checked ( $options['create_cat'] ); ?>/>
	<?php
}

/**
* Render since date/time for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_filter_cat_text(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_importer_filter_cat" type="text" name="pd_wp_post_importer_config[filter_cat]" class="regular-text code" value="<?php echo ( esc_attr( urldecode( $options['filter_cat'] ) ) ); ?>"/>
	<?php
}

/**
* Render since date/time for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_since_text(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_importer_since" type="text" name="pd_wp_post_importer_config[since]" class="regular-text code" value="<?php echo ( esc_attr( $options['since'] ) ); ?>"/>
	<?php
}

/**
* Render frequency for wp post importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_frequency_select(){
	$options = pd_wp_post_importer_get_option();
	?>
	<select id="pd_wp_post_importer_frequency" name="pd_wp_post_importer_config[frequency]">
		<option value="one" <?php selected( $options['frequency'], 'one' ); ?>>1 Minute</option>
		<option value="five" <?php selected( $options['frequency'], 'five' ); ?>>5 Minutes</option>
		<option value="ten" <?php selected( $options['frequency'], 'ten' ); ?>>10 Minutes</option>
		<option value="fifteen" <?php selected( $options['frequency'], 'fifteen' ); ?>>15 Minutes</option>
		<option value="hourly" <?php selected( $options['frequency'], 'hourly' ); ?>>Hourly</option>
		<option value="daily" <?php selected( $options['frequency'], 'daily' ); ?>>Daily</option>
	</select>
	<span style="width:100px; padding:10px;"><input class="button-secondary" name="submit" type="submit" value="Save Settings" /></span>
	<br /><br />
	<?php
}

/**
* Render number to import text for auto importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_number_text(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_importer_number" type="text" name="pd_wp_post_importer_config[number]" class="regular-text code" value="<?php echo ( esc_attr( $options['number'] ) ); ?>">
	<?php
}

/**
* Render es server for auto importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_blog_text(){
	$options = pd_wp_post_importer_get_option();
	?>
	<input id="pd_wp_post_import_blog" type="text" name="pd_wp_post_importer_config[blog]" class="regular-text code" value="<?php echo ( esc_attr ( $options['blog'] ) );  ?>"/>
	<?php
}

/**
* Render help text for auto importer options page
*
* @since 1.1.0
* @author Keith Benedict
*/
function pd_wp_post_importer_main_help() {
	?>
	<p>WP Post Importer uses wp_cron to execute imports by pulling content from the specified WP Blog using WordPress's public API.<br/><b>Edit/Delete</b> To edit an entry, simply re-enter the parameters for the blog, all entries are keyed to the blog address.  To delete, simply click the x next to the blog entry in the listings below.</p>
	The settings for "Enable Jobs" and "Frequency" apply to all of the jobs scheduled.<br/>
	<b>Enable Jobs</b> - Enables all of the listed jobs in the blog import list.<br />
	<b>Frequency</b> - Plugin includes additional cron intervals down to 1 minute. Recommended 15 min for normal development use.<br/>

	<hr/>
	<p><b>Blog</b> - The WordPress blog domain name i.e. blogs.windsorstar.com<br />
	<b>Author Action</b> - Specify the action to take when the stories author does not exist, discard the story, add the author, assign to random author.<br/>
	<b>Create Missing Categories</b> - when enabled, categories not present will be created.<br/>
	<b>Filter by Categories</b> - A comma separated list of categories by name or slug. Leave blank to retrieve all categories.<br/>
	<b>Since Publish Date/time</b> - indicates the Date/time to pull posts after (i.e. 2013-01-18 09:00:00), this auto updates as imports complete.<br/>
	<b>Number to Import</b> - The maximum number of stories to import per run. Default is 10, stories under 100 characters are ignored.<br/>
	</p>
	<?php
}

/**
 * Validate the wp post importer settings being saved
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_validate_options ( $input ) {
	global $pd_author_actions, $pd_wp_post_importer_options_temp;
	$options = pd_wp_post_importer_get_option();

	if ( !isset( $input['author_action'] ) ) {
		return $pd_wp_post_importer_options_temp; // handling a weird issue where the very first time used on the plugin, callback is fired twice and data in input is all escaped including index names.
	}
	$valid = array();
	$sanitized_options = array();
	$required_fields_missing = false;  // flag to true in validators if required data is missing to determine if the enabled check should be allowed.
	
	// Validate author_action
	if ( isset( $pd_author_actions[$input['author_action']] ) ) {
		$valid['author_action'] = $input['author_action'];
	}
	else {
		$valid['author_action'] = $pd_author_actions['discard'];
	}
	// Validate create category checkbox option
	if ( isset ( $input['create_cat'] ) ) {
		$valid['create_cat'] = ( true == $input['create_cat'] );
	}
	else {
		$valid['create_cat'] = false;
	}

	// Validate the since option
	if ( isset ( $input['since'] ) && !empty( $input['since'] ) ) {
		$since = sanitize_text_field( $input['since'] );

		if ( ! preg_match( "/^([\\+-]?\\d{4}(?!\\d{2}\\b))((-?)((0[1-9]|1[0-2])(\\3([12]\\d|0[1-9]|3[01]))?|W([0-4]\\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\\d|[12]\\d{2}|3([0-5]\\d|6[1-6])))([T\\s]((([01]\\d|2[0-3])((:?)[0-5]\\d)?|24\\:?00)([\\.,]\\d+(?!:))?)?(\\17[0-5]\\d([\\.,]\\d+)?)?([zZ]|([\\+-])([01]\\d|2[0-3]):?([0-5]\\d)?)?)?)?$/u", $since ) ) {
			add_settings_error( 'pd_wp_post_importer_since', 'pd_wp_post_importer_since_error', 'This must be a valid ISO 8601 date.', 'error' );
			$required_fields_missing = true;
		}
		else {
			$valid['since'] = $since;
		}

	}
	else {
		$valid['since'] = ''; // default to nothing...means we pull the oldest content possible from public api.
	}

	// Validate the number of stories to import option
	if ( isset( $input['number'] ) ) {
		$number = intval( sanitize_text_field( $input['number'] ) );

		if ( $number < 1 ) {
			$number = 10; // default to 10
		}
		elseif ( $number > 50 ) {
			$number = 50; // setting max to 50, otherwise job may bog down server
		}

		$valid['number'] = $number;
	}
	else {
		$valid['number'] = 10; // default to 10
	}
	
	// Validate filter
	if ( isset ( $input['filter_cat'] ) && !empty( $input['filter_cat'] ) ) {
		$valid['filter_cat'] = urlencode( strtolower(sanitize_text_field( $input['filter_cat'] ) ) );
	}
	else {
		$valid['filter_cat'] = ''; //default to blank.
	}
	
	// Validate blog option
	if ( isset ( $input['blog'] ) && !empty( $input['blog'] ) ) {
		// remove any protocol or slashes
		$blog = preg_replace( '/http:|https:|\/\//u', '', strtolower( sanitize_text_field( $input['blog'] ) ) );
		$valid['blog'] = $blog;
	}
	else {
		$required_fields_missing = true; // we need the blog to query content
	}
			
	// Validate the frequency option
	if ( isset ( $input['frequency'] ) ){
		$frequency = sanitize_text_field( $input['frequency'] );

		// check here is to see if the cron schedule exists, since this value is populated by a select box, the only way it would be invalid is if someone is trying to hack the form or a typo occurs
		$schedules = wp_get_schedules();

		if ( isset ( $schedules[$frequency] ) ) {
			$sanitized_options['frequency'] = $frequency;
		}
		else {
			$sanitized_options['frequency'] = 'fifteen';
		}

	}
	else {
		$sanitized_options['frequency'] = 'fifteen'; // default to 15 minutes schedule
	}
	
	$deleted_blogs = array();
	$blogs = json_decode( $options['blogs'], true );
	if ( isset( $input['delete_blogs'] ) && !empty( $input['delete_blogs'] ) ) {
		$deleted_blogs = explode( ',' , $input['delete_blogs'] );
	}
	if ( false == $required_fields_missing ) {
		// Assuming everything is good up to here from our validation, lets see if this is a new entry or existing
		if ( strpos( $options['blogs'], '"blog":"' . $valid['blog'] . '"' ) ) {
			// then this blog existed....overwrite
			$new_blogs = array();
			foreach( $blogs as $blog ) {
				if ( $blog['blog'] == $valid['blog'] ) {
					array_push( $new_blogs, $valid ); // replaces old entry with new
				}
				else {
					array_push( $new_blogs, $blog );
				}
			}
			$blogs = $new_blogs; // effectively removed the elements from the duplicated array, putting in the new entry instead
		}
		else {
			array_push( $blogs, $valid ); // adding the new valid blog entry to the blogs list
		}
	}
	
	$keep_blogs = array(); // now pruning 
	foreach( $blogs as $blog ) {
		if ( ! in_array( $blog['blog'], $deleted_blogs ) ) {
			array_push( $keep_blogs, $blog ); // not on the deleted list
		}
	}
	$blogs = $keep_blogs; // we have now removed any marked for deletion
	
	$sanitized_options['blogs'] = json_encode( $blogs );
	
	// enabled check
	if ( isset ( $input['enabled'] ) && $input['enabled'] ){
		if ( count( $blogs ) > 0 ){ // at least 1 blog in the blogs array.
			$sanitized_options['enabled'] = ( true == $input['enabled'] );
		}
		else {
			$sanitized_options['enabled'] = false; // default to false
			add_settings_error( 'pd_wp_post_importer_enabled', 'pd_wp_post_importer_enabled_error', 'Cannot enable until at least one valid blog is added.', 'error' );
		}
	}
	else {
		$sanitized_options['enabled'] = false; // default to false
	}

	// Schedule job if enabled
	if ( $sanitized_options['enabled'] ) {
		// clear first so we don't stack up events
		wp_clear_scheduled_hook( 'pd_wp_post_importer_event' );
		$start_time = time() + ( 60 * pd_wp_post_importer_frequency_to_number( $sanitized_options['frequency'] ) );
		wp_schedule_event ( $start_time, $sanitized_options['frequency'], 'pd_wp_post_importer_event' );
	}
	else {
		// clear event, there is no error if event does not exist.
		wp_clear_scheduled_hook( 'pd_wp_post_importer_event' );
	}
	$pd_wp_post_importer_options_temp = $sanitized_options; // part of the workaround on first submission with double callback
	return $sanitized_options;
}

/**
 * Adds custom cron schedules for one, five, ten and fifteen minutes
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_cron_add_custom( $schedules ) {
	$schedules['one'] = array(
		'interval' => 60,
		'display' => __( 'One Minute' )
	);

	$schedules['five'] = array(
		'interval' => 300,
		'display' => __( 'Five Minutes' )
	);

	$schedules['ten'] = array(
		'interval' => 600,
		'display' => __( 'Ten Minutes' )
	);

	$schedules['fifteen'] = array(
		'interval' => 900,
		'display' => __( 'Fifteen Minutes' )
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'pd_wp_post_importer_cron_add_custom' );

/**
 * This is the actual job that imports content, run based on configured cron schedule when plugin is set to enabled.
 *
 * @uses sanitize_title
 * @uses is_wp_error
 * @uses wp_insert_post
 * @uses wp_set_post_terms
 * @uses wp_update_post
 * @uses update_option
 *
 * @since 1.1.2
 * @author Keith Benedict
 */
function pd_wp_post_importer_job() {
	$options = pd_wp_post_importer_get_option();
	$error = NULL;
	$import_blogs = json_decode( $options['blogs'], true );
	$processed_blogs = array(); // to store blogs
	$last_fifty_titles = array();

	// for dedup, pull last 50 titles
	$last_fifty_titles = pd_wp_post_importer_last_fifty();

	foreach($import_blogs as $import_blog){
		$blog = 'http://public-api.wordpress.com/rest/v1/sites/' . $import_blog['blog'] . '/posts/?order=ASC';
		$params = '';
		$last_since = $import_blog['since'];
		$number_of_posts = $import_blog['number'];
		$filter_cat = $import_blog['filter_cat'];
		// check options for last_since and number
		if ( ! empty( $number_of_posts ) ) {
			$params = '&number=' . $import_blog['number'];
		}

		if ( ! empty( $last_since ) ) {
			$params = $params . '&after=' . $last_since;
		}

		// check options for category filters
		if ( ! empty( $filter_cat ) ) {
			$params = $params . '&category=' . $filter_cat;
		}
		// now add parameters if set
		$blog = $blog . $params;

		$wp_post_data =  json_decode( pd_wp_post_importer_file_get_contents( $blog ), true );
		if ( is_array( $wp_post_data ) && isset ( $wp_post_data['posts'] ) ){
			// we have content, now to process.
			$external_posts = $wp_post_data['posts'];
			foreach( $external_posts as $external_post ) {
				// need to add 1 second so as to ensure we don't get repeats...the after parameted for 
				// wordpress api includes posts on and after this date.
				// We are manipulating the string as local server will change timezone when converted to date.
				$last_since = $external_post['date'];
				$seconds_increased = intval( substr( $last_since, 17, 2 ) ) + 1;
				if ( $seconds_increased > 59 ) {
					$seconds_increased = 0;
					$minutes_increased = intval( substr( $last_since, 14, 2 ) ) + 1;
					if ( $minutes_increased > 59 ) {
						$minutes_increased = 0;
						$hours_increased = intval( substr( $last_since, 11, 2 ) ) + 1;
						if ( $hours_increased > 23 ) {
							$hours_increased = 0;
							$day_increased = new DateTime( date( 'Y-m-d', strtotime( date('Y-m-d', strtotime( substr( $last_since, 0, 10 ) ) ) . ' +1 day' ) ) );
							$last_since = substr_replace( $last_since, $day_increased->format('Y-m-d'), 0,10 );
						}
						$last_since = substr_replace( $last_since, ( ( $hours_increased < 10 ) ? '0' . strval( $hours_increased ) : strval( $hours_increased ) ), 11, 2 );
						
					}
					$last_since = substr_replace( $last_since, ( ( $minutes_increased < 10 ) ? '0' . strval( $minutes_increased ) : strval( $minutes_increased ) ), 14, 2 );
				}
				$last_since = substr_replace( $last_since, ( ($seconds_increased < 10 ) ? '0' . strval( $seconds_increased ) : strval( $seconds_increased ) ), 17, 2 );
				
				$import_blog['since'] = $last_since;

				// check if the post is already present, if so continue
				$check_post_title = sanitize_title( $external_post['title'] );
				if ( in_array( $check_post_title, $last_fifty_titles ) ) {
					error_log( ' Post already exists: ' . $external_post['title'] );
					continue;
				}
				else {
					array_push( $last_fifty_titles, $check_post_title );
				}

				$author = strtolower( $external_post['author']['name'] );
				$author_id = pd_wp_post_importer_check_author( $author, $import_blog['author_action'] );
				if ( is_wp_error( $author_id ) ) {
					// Move on to next story, an error means the conditions of the author name and settings
					// are incompatible, this story can not be imported.
					continue;  
				}

				// Create post
				$post = array();
				$post['post_title'] = $external_post['title'];
				$post['post_status'] = 'publish';
				$post['post_author'] = $author_id;
				// originally I was using strip_shortcodes, however this funciton only works with the sites registered shortcodes, in order to
				// ensure we remove shortcodes from the foreign site we use preg_replace
				$post['post_content'] = html_entity_decode( preg_replace( '/\[(.*)?\]/u', '', $external_post['content'] ) );
				$post['post_date'] = $external_post['date'];

				$post_id = wp_insert_post ( $post );

				if ( is_wp_error( $post_id ) ){
					// error posting story, we continue to next, however we  save to $error
					// it is possible multiple errors happen, we are only using this to log last error so someone
					// knows to look into the issue
					$error = $post_id->get_error_message();
					error_log( ' Error adding post: ' . $error );
					continue;
				}

				// need categories
				if ( isset( $external_post['categories'] ) && is_array( $external_post['categories'] ) ) {
					// generate category id list, returns an array
					$categories = pd_wp_post_importer_categorize_post( $external_post['categories'] );
					if ( ! empty( $categories ) ) {
						wp_set_post_terms( $post_id, $categories, 'category' );
					}
				}

				// need tags
				if ( isset( $external_post['tags'] ) && is_array( $external_post['tags'] ) ) {
					// generate category id list, returns an array
					$tags = pd_wp_post_importer_tag_post( $external_post['tags'] );
					if ( ! empty( $tags ) ) {
						wp_set_post_terms( $post_id, $tags, 'post_tag' );
					}
				}

				// need attachments
				$detached_images = pd_wp_post_importer_find_detached( $post['post_content'] );
				if ( is_array( $detached_images ) ) {
					// append or set external_posts['attachments'] 
					if ( isset( $external_post['attachments'] ) && is_array( $external_post['attachments'] ) ) {
							foreach( $detached_images as $key => $value ) {
								$external_post['attachments'][$key] = $value;
							}
					}
					else {
						$external_post['attachments'] = $detached_images;
					}
				}
				$internal_images = array(); // used below to reprocess image information inside post content
				if ( isset( $external_post['attachments'] ) && is_array( $external_post['attachments'] ) ) {
					// generate category id list, returns an array
					$featured = isset( $external_post['featured_image'] ) ? $external_post['featured_image'] : '';
					$internal_images = pd_wp_post_importer_upload_images( $post_id, $external_post['attachments'], $featured );
					if ( is_wp_error( $internal_images ) ) {
						$error = $internal_images->get_error_message();
					}
					else {
						// Now we alter post contents to match the new image information
						$fixed_content = $post['post_content'];
						$images_found = preg_match('/id=\"attachment_/u', $fixed_content ); 
						if ( ! $images_found > 0 ) {
							continue; // as there are no images to modify, there may be a featured image, it just isnt actually in the story body.
						}

						foreach( $internal_images as $key => $value ) {
							$master_start_pos = strpos( $fixed_content, 'id="attachment_' . $key ); // position of the id="attachment_xxxxxx"
							if ( ! $master_start_pos > 0 )
								continue; // not found, move to next id

							// we need to find this: id="attachment_242873"
							$search = array( 'prefix' => 'id="attachment_', 'term' => $key );
							$fixed_content = pd_wp_post_importer_str_replace( $fixed_content, $search, $value, $master_start_pos );
							if ( is_wp_error ($fixed_content ) )
								continue; // All fields update or none at all for selected image

							// we need to find this: wp-image-242873
							$search = array( 'prefix' => 'wp-image-', 'term' => $key );
							$fixed_content = pd_wp_post_importer_str_replace( $fixed_content, $search, $value, $master_start_pos );
							if ( is_wp_error ($fixed_content ) )
								continue; // All fields update or none at all for selected image

							// we need to find this: src="http://nationalpostnews.files.wordpress.com/2012/12/12-12-12-concert.jpg?w=450"
							$old_url = $external_post['attachments'][$key]['URL'];
							$image_src = wp_get_attachment_image_src( $value );
							$new_url = $image_src[0];
							$new_url = preg_replace( '/\/$|/u', '',  preg_replace( '/-150x150\./u', '.', $new_url ) ); //strip sizing and trailing slash from the image_src
							$search = array( 'prefix' => 'src="', 'term' => $old_url );
							$fixed_content = pd_wp_post_importer_str_replace( $fixed_content, $search, $new_url, $master_start_pos );
							if ( is_wp_error ($fixed_content ) )
								continue; // All fields update or none at all for selected image
						}

						// Now we update our post content
						if( ! is_wp_error ( $fixed_content ) ){
							$modified_post = array( 'ID' => $post_id, 'post_content' => $fixed_content );
							$result = wp_update_post( $modified_post );
							if ( $result == 0 )
								$error = 'Unable to update post image urls';
						}

					}
				}
			}
		}
		else
		{
			if ( isset( $wp_post_data['error'] ) ) {
				$error = 'Error talking to WordPress API: ' . $wp_post_data['error'] . ' - ' . $posts['message']; 
			}
			else {
				$error = 'WordPress Public API did not respond, no error message provided.';
			}
		}
		array_push( $processed_blogs, $import_blog ); // to save the changes/settings
		
	}
	$options = pd_wp_post_importer_get_option();
	$options['blogs'] = json_encode( $processed_blogs );
	update_option( 'pd_wp_post_importer_config', $options ); 
	pd_wp_post_importer_add_history( $error );
}
add_action( 'pd_wp_post_importer_event', 'pd_wp_post_importer_job' );

/**
 * str_replace
 *
 * This function originated out of a specific replacement function to clean up image urls in the body content that point to imported images,
 * and us now a generic function to perform string replacement
 *
 * @returns string | WP_Error
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_str_replace( $haystack, $needle, $replacement = '', $start_offset = 0 ) {
	try {
		if ( is_array( $needle ) ){
			$search_prefix = $needle['prefix'];
			$search_term = $needle['term'];
		}
		else {
			$search_prefex = '';
			$search_term = $needle;
		}
		if ( empty( $haystack ) || empty( $search_term ) ) // required parameters
			return new WP_Error( 'image_corrector', __( 'Required parameters missing, must supply (string Haystack, string Needle | array Needle( prefix => string, term => string ) )' ) );

		$start_pos = strpos( $haystack, $search_prefix . $search_term, $start_offset ) + strlen( $search_prefix );
		$replace_length = strlen( strval( $search_term ) );
		$end_pos = $start_pos + $replace_length;

		$new_haystack = substr_replace( $haystack, $replacement, $start_pos, $replace_length );
		return $new_haystack;
	}
	catch ( Exception $ex ) {
		return new WP_Error( 'image corrector', __( 'Exception: ' . $ex->getMessage() ) );
	}
}

/**
 * Read wp post importer history
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_read_history(){
	$history = json_decode( get_option( 'pd_wp_post_importer_history' ), true );
	$history = is_array( $history ) ? $history : array( 'last_ran' => false, 'last_error' => false, 'run_count' => 0 ); // create default array if necessary
	return $history;
}

/**
 * Write wp post importer history
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_db_write_history( $history ){
	update_option( 'pd_wp_post_importer_history', json_encode( $history ) );
}

/**
 * Modify wp post importer history
 *
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_add_history( $error = NULL ){
	// truncate error message to 200 characters, in case we get a long string we dont want to fill the options table
	if ( isset( $error ) ) {
		$error = ( strlen( $error ) > 200 ) ? substring( $error, 0, 200 ) : $error;
		$error = sanitize_text_field( $error );
	}
	else {
		$error = 'none';
	}

	$old_history = json_decode( get_option( 'pd_wp_post_importer_history' ), true );
	$run_count = intval( $old_history['run_count'] ) + 1;

	$history = array( 'last_ran' => time(), 'last_error' => $error, 'run_count' => $run_count );
	pd_wp_post_importer_db_write_history( $history );
}


/**
 * Fetches remote posts from a WP Blog
 *
 * Uses the VIP version of file_get_contents if available, otherwise defaults to standard version
 *
 * @uses wpcom_file_get_contents()
 * @uses file_get_contents()
 * @uses esc_url_raw()
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_get_remote_posts( $blog ) {
	// could make this full url the option however if the wordpress public api address changes, we probably have to review and update the field mappings anyway so leaving hard coded.
	$url = 'http://public-api.wordpress.com/rest/v1/sites/' . $blog . '/posts/';
	if ( function_exists( 'wpcom_vip_file_get_contents' ) ) {
		return wpcom_vip_file_get_contents( esc_url_raw( $url, array ( 'http', 'https' ) ) , 1, 60 );
	} else {
		return file_get_contents( esc_url_raw( $url, array ( 'http', 'https' ) ) );
	}
}
function pd_wp_post_importer_file_get_contents( $url ) {
	if ( function_exists( 'wpcom_vip_file_get_contents' ) ) {
		return wpcom_vip_file_get_contents( esc_url_raw( $url, array ( 'http', 'https' ) ) , 1, 60 );
	} else {
		return file_get_contents( esc_url_raw( $url, array ( 'http', 'https' ) ) );
	}
}

/**
 * Checks author of posts 
 *
 * Queries the author list, if the author is not present then it can do one of three things based on author action:
 * 1: Return wp_error if discard is the author_action
 * 2: Return author_ID if creaqte is set, or wp_error is create fails
 * 3: Return author_ID of a random author
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_check_author( $author, $action ) {
	// Initialize the author list
	$pd_author_display_names = array();
	$pd_author_ids = array();
	$all_users = get_users();
	foreach ( $all_users as $user ) {
		if ( intval( $user->ID ) > 1 ) { // avoids admin user and only accounts with displayname set.
			$pd_author_display_names[strtolower( $user->display_name )] = $user->ID;
			array_push( $pd_author_ids, $user->ID );
		}
	}
	global $pd_author_actions;
	$author = strtolower( $author );
	if ( isset ( $pd_author_display_names[$author] ) ) {
		// Author exists already, regardless of options we return the id
		return $pd_author_display_names[$author];
	}
	else {
		$list = '';
		foreach( $pd_author_display_names as $key => $value ) {
			$list = $list . '[ '. $key . ' ]   ';
		}
		// author does not exist, now we check plugin options
		switch ( $action ) {
			case strtolower( $pd_author_actions['discard'] ):
				// do nothing, return wp_error 
				return new WP_Error( 'discard', __( 'Author did not exist' ) );
				break;
		    case strtolower( $pd_author_actions['create'] ):
				// attempt to create
				return pd_wp_post_importer_create_author( $author );
				break;
			case strtolower( $pd_author_actions['random'] ):
				if ( count ( $pd_author_ids ) ) {
					return $pd_author_ids[rand( 0, count( $pd_author_ids ) - 1 )];
				}
				else {
					return new WP_Error( 'random', __( 'No Author to choose from.' ) );
				}
				break;
			default:
				return new WP_Error( 'author action', __( 'No author action detected.' ) );
				break;
		}	
	}
}

function pd_wp_post_importer_create_author( $author ) {
	// uppercase first letter of each word, remove non ascii characters
	$author = sanitize_user( ucwords( $author ), true );
	// Generate safe email address, using custom domain plus random number 'display_namexxxx@post.import'
	$email = sanitize_title( $author ) . strval( rand( 1000, 9999 ) ) . '@post.importer';

	// array to pass arguments into wp_insert_user
	$user_data = array( 'ID' => '', 'user_pass' => wp_generate_password(), 'user_login' => $author, 'display_name' => $author, 'user_email' => $email, 'role' => get_option('default_role') );

	$author_id = wp_insert_user( $user_data );
	return $author_id; // if there was an error the WP_error object is passed back as is.
}

/**
 * Uploads images into local wordpress, returns array of old/new id's to correct links in content
 *
 * @uses wp_upload_dir
 * @uses wp_check_filetype
 * @uses wp_mkdir_p
 * @uses wp_insert_attachment
 * @uses wp_generate_attachment_meta
 * @uses wp_update_attachment_meta
 * @uses set_post_thumbnail
 * @uses WP_Error
 *
 * @since 1.1.2
 * @author Keith Benedict
 */
function pd_wp_post_importer_upload_images( $post_id, $attachments, $featured ) {
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$error = NULL;
	$images = array(); // passed back with information on image filename, new ID to reprocess shortcoded images in the body content

	foreach( $attachments as $attachment ) {
		try {
			$upload_dir = wp_upload_dir();
			$image_data = pd_wp_post_importer_file_get_contents ( $attachment['URL'] );
			$filename = basename ( $attachment['URL'] );
			$filetype = wp_check_filetype( $filename, null );

			if( wp_mkdir_p ( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			}
			else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
			file_put_contents($file, $image_data);

			$attached_file = array(
				'post_mime_type' => $filetype['type'],
				'post_title' => sanitize_file_name( $filename ),
				'post_content' => '',
				'post_status' => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attached_file, $file, $post_id );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			if ( ! empty( $featured ) && $featured === $attachment['URL'] ) {
				// sets the image as the post thumbnail 
				set_post_thumbnail( $post_id, $attach_id );
			}
			$images[$attachment['ID']] = ( $attach_id ); // use the old id as the key, store the new id which we can look up 
		}
		catch ( Exception $ex ) {
			$error = $ex->getMessage();
		}
	}
	// either return the WP_Error object or null if no errors
	return ( ! empty( $error ) ) ? new WP_Error( 'upload', __( 'Failed to upload image, error message: ' . $error ) ) : $images;
}

function pd_wp_post_importer_tag_post( $post_tags ) {
	// process tag array, return tag names for post
	$tags = array();
	foreach( $post_tags as $tag ) {
		array_push( $tags, $tag['name'] );
	}
	return $tags;
}

function pd_wp_post_importer_categorize_post( $post_categories ) {
	// process categories, return category IDs
	$categories = array();

	// Build an array of category id's
	foreach ( $post_categories as $category ) {
		$slug = pd_wp_post_importer_slugify( $category['name'] );
		$id = pd_wp_post_importer_get_category_id( $slug, $category['name'] );

		if ( $id ) { array_unshift( $categories, $id ); }
	}

	// Determine if the category has child categories as well, omit parents when children present.
	$exclude_categories = array();
	foreach ( $categories as $category_id ) {
		$children = NULL;
		$args = array();
		$args['child_of'] = $category_id;
		$args['hide_empty'] = 0; // show emtpy categories

		$children = get_categories( $args );

		// now check if any children are in the categories list and add to exclude_categories array
		foreach ( $children as $child ) {
			if ( in_array ( $child -> cat_ID, $categories ) ) { 
				array_unshift( $exclude_categories, $category_id );
			}
		}
	}
	$categories = array_diff( $categories, $exclude_categories );

	return $categories;
}

/**
 * retrieves or creates the category based on options and returns category id
 * 
 * @uses get_category_by_slug
 * @uses get_term_by
 * @uses wp_insert_category
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_get_category_id( $slug, $category ) {
	require_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );
	$options = pd_wp_post_importer_get_option();

	$cat = get_category_by_slug( $slug );
	$cat_id = NULL;

	// becaus taxonomies share one table for slugs....check if slug is in use by tags
	$term = get_term_by( 'slug', $slug, 'post_tag' );

	// $cat == false if category doesnt exist
	if ( $cat ) {
		$cat_id = $cat -> cat_ID;
	}
	elseif ( $term ) {
		//check if slug-2 exists
		$slug = $slug . '-2';
		$cat = get_category_by_slug( $slug );
		if ($cat ) {
			$cat_id = $cat -> cat_ID;
		}
	}

	if ( $options['create_cat'] && ( NULL == $cat_id ) ) {
		$new_category = array( 'cat_name' => $category, 'category_nicename' => $slug );
		$cat_id = wp_insert_category( $new_category );
	}

	return $cat_id;
}

/**
 * Converts input string to the slug (nice name)
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_slugify( $string )
{
	$result = strtolower( $string );

	$result = preg_replace( "/[^a-z0-9\s-]/", "", $result );
	$result = trim( preg_replace( "/[\s-]+/", " ", $result ) );
	$result = trim( $result );
	$result = preg_replace( "/\s/", "-", $result );

	return $result;
}

/**
 * Converts frequency string to numeric value
 *
 * @since 1.1.0
 * @author Keith Benedict
 */
function pd_wp_post_importer_frequency_to_number( $frequency ) {
	switch ( $frequency ) {
		case 'one':
			$value = 1;
			break;
		case 'five':
			$value = 5;
			break;
		case 'ten':
			$value = 10;
			break;
		case 'fifteen':
			$value = 15;
			break;
		case 'hourly':
			$value = 60;
			break;
		case 'daily':
			$value = 60 * 24;
			break;
		default:
			// should never happen but if options are corrupt....
			$value = 15; // use plugin default frequency value
	}
	return $value;
}

/**
 * Retrieve the last 50 stories, put their titles into an array and return
 *
 * @uses WP_Query
 * @uses sanitize_title
 * @uses the_title
 *
 * @since 1.1.1
 * @author Keith Benedict
 */
function pd_wp_post_importer_last_fifty() {
	$last_fifty_titles = array();
	$posts = new WP_Query( 'showposts=50' );
	while ( $posts->have_posts() ) {
		$posts->the_post();
		$title = sanitize_title( the_title( '', '', false ) );
		array_push( $last_fifty_titles, $title );
	}
	return $last_fifty_titles;
}

/**
 * find images in the content as some posts may not list all images in the attachements
 *
 * @since 1.1.2
 * @author Keith Benedict
 */
function pd_wp_post_importer_find_detached( $post_content ) {
	// lets find all attachments, create them int he format they should be then return it as an array
	$master_start = 0;
	$images = array();
	do {
		$search_id = 'id="attachment_';
		$start_pos = strpos( $post_content, $search_id , $master_start );
		if ( $start_pos ) {
			// attachment found, lets build an array
			$image = array();

			// image ID
			$start_pos = $start_pos + strlen( $search_id );
			$end_pos = strpos( $post_content, '"', $start_pos ); //first quote after original search is end of id attribute.
			$length_of_field = $end_pos - $start_pos;
			$image['ID'] = substr( $post_content, $start_pos, $length_of_field );

			// Get the URL and guid which are the same
			$search_url = 'src="';
			$start_pos = strpos( $post_content, $search_url, $start_pos ) + strlen( $search_url ); // searching for image src attribute within div
			$end_pos = strpos( $post_content, '?', $start_pos ); // check for end if querystring variables are present as end of src attribute
			if ( ! $end_pos )
				$end_pos = strpos( $post_content, '"', $start_pos ); // first quote after original search is end of src attribute
			$length_of_field = $end_pos - $start_pos;
			$image['URL'] = substr( $post_content, $start_pos, $length_of_field );
			$image['guid'] = $image['URL'];

			// for structure of data we will just set these values
			$image['mime_type'] = 'unknown';
			$image['width'] = 150;
			$image['height'] = 150;

			// now add to images array by ID as key
			$images[$image['ID']] = $image;

			// set master_start to end_pos to cycle for next image
			$master_start = $end_pos;
		}

	} while ( $start_pos );
	
	// check if images has any content, return array or return null
	if ( count( $images ) ) {
		return $images;
	}
	else {
		return null;
	}

}

