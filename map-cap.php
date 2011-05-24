<?php
/*
Plugin Name: Map Cap
Plugin URI: https://github.com/thenbrent/map-cap
Description: Control who can publish, edit and delete custom post types.  Silly name, useful code.
Author: Brent Shepherd
Version: 1.1
Author URI: http://find.brentshepherd.com/
*/

function mc_add_admin_menu() {
	if ( function_exists('add_options_page') ) { // additional pages under E-Commerce
		$page = add_options_page( 'Map Caps', 'Map Cap', 'manage_options', 'mapcap', 'mc_capabilities_settings_page' );
	}
}
add_action( 'admin_menu', 'mc_add_admin_menu' );

/** 
 * Site admins may want to allow or disallow users to create, edit and delete custom post type.
 * This function provides an admin menu for selecting which roles can do what with custom posts.
 **/
function mc_capabilities_settings_page() { 
	global $wp_roles;

	if ( isset( $_POST['_wpnonce' ] ) )
		$message = mc_save_capabilities();
	else
		$message = '';

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key => $value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
	$dont_touch = get_post_types( array( 'capability_type' => 'post' ) );
	$not_mapped = get_post_types( array( 'map_meta_cap' => false ) );

	// Don't edit capabilties for any custom post type with "post" as its capability type
	$post_types = array_diff( $post_types, $dont_touch );
	// For mapping capabilities, post type needs to have map_meta_cap set to true
	$mapped_post_types = array_diff( $post_types, $not_mapped );

	echo '<div class="wrap map-cap-settings">';
	screen_icon();
	echo '<h2>' . __( 'Map Capabilities', 'map-cap' ) . '</h2>';

	if ( ! empty( $message ) )
		echo '<div id="message" class="updated fade"><p>' . $message . '</p></div>';

	// Start of the Map Meta Cap settings form
	if( ! empty( $post_types ) || ! empty( $not_mapped ) )
		echo '<form id="map-cap-form" method="post" action="">';

	if ( empty( $mapped_post_types ) ) : ?>
		<h3><?php _e( 'Map Caps', 'map-cap' ); ?></h3>
		<p><?php _e( 'No custom post types have been registered with a custom capability, public argument set to true and the map_meta_cap argument set to true.', 'map-cap' ) ?></p>
		<p><?php printf( __( 'Try creating custom post types with the %sCustom Post Type UI plugin%s.', 'map-cap' ), '<a href="http://wordpress.org/extend/plugins/custom-post-type-ui/">', '</a>' )?></p>
	<?php
	else:
		foreach( $mapped_post_types as $post_type ) {
			
			$post_type_details 	= get_post_type_object( $post_type );
			$post_type_cap 		= $post_type_details->capability_type;
			$post_type_caps		= $post_type_details->cap;

			wp_nonce_field( 'mc_capabilities_settings' );
			?>
			<h3><?php printf( __( '%s Capabilities', 'map-cap' ), $post_type_details->labels->name ); ?></h3>
			<?php // Allow publish ?>
			<div class="map-cap">
				<h4><?php printf( __( "Publish %s", 'map-cap' ), $post_type_details->labels->name ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-publish">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-publish" name="<?php echo $post_type . '-' . $role->name; ?>-publish"<?php checked( isset( $role->capabilities[ $post_type_caps->publish_posts ] ), 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing own posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Own %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit">
				  	<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit" name="<?php echo $post_type . '-' . $role->name; ?>-edit"<?php checked( isset( $role->capabilities[ $post_type_caps->edit_published_posts ] ), 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing others posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Others' %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit-others">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit-others" name="<?php echo $post_type . '-' . $role->name; ?>-edit-others"<?php checked( isset( $role->capabilities[ $post_type_caps->edit_others_posts ] ), 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow reading private posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "View Private %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-private">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-private" name="<?php echo $post_type . '-' . $role->name; ?>-private"<?php checked( isset( $role->capabilities[ $post_type_caps->read_private_posts] ), 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
		<?php } ?>
		<?php
	endif; 

	if( ! empty( $not_mapped ) ) :
	?>
	<h3><?php _e( 'Force Mapping', 'map-cap' ); ?></h3>
	<p><?php _e( 'The following is a list of all public custom post types registered on your site with a custom capability. Those unchecked are not using the WordPress meta capability system.', 'map-cap' ); ?></p>
	<p><?php _e( 'Check a post type to have Map Cap attempt to allow you to map capabilities for these post types. For this to work, the post type must be registered on the init hook with a priority less than 10,000.', 'map-cap' ); ?></p>
	<h4><?php _e( 'Custom Post Types', 'map-cap' ); ?></h4>
	<div class="map-cap">
	<?php foreach( $post_types as $post_type ) :
		$post_type_details 	= get_post_type_object( $post_type );
		?>
			<label for="<?php echo $post_type; ?>-map">
				<input type="checkbox" id="<?php echo $post_type; ?>-map" name="<?php echo $post_type; ?>-map"<?php checked( $post_type_details->map_meta_cap, 1 ); ?> />
				<?php echo $post_type_details->labels->name; ?>
			</label>
	<?php endforeach; ?>
	<?php endif;

	if( ! empty( $post_types ) || ! empty( $not_mapped ) ) :
	?>
	<p class="submit">
		<input type="submit" name="submit" class="button button-primary" value="<?php _e( 'Save', 'map-cap' ); ?>" />
	</p>
	</div>
	</form>
	</div>
	<?php
	endif;
}


/** 
 * Save capabilities settings when the admin page is submitted page by adding the capabilitiy
 * to the appropriate roles.
 **/
function mc_save_capabilities() {
	global $wp_roles;

    if ( ! check_admin_referer( 'mc_capabilities_settings' ) || ! current_user_can( 'manage_options' ) )
		return;

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key=>$value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
	$dont_touch = get_post_types( array( 'capability_type' => 'post' ) );

	// Don't edit capabilties for any custom post type with "post" as its capability type
	$post_types = array_diff( $post_types, $dont_touch );

	foreach ( $roles as $key => $role ) {

		foreach( $post_types as $post_type ) {

			$post_type_details = get_post_type_object( $post_type );
			$post_type_cap 	= $post_type_details->capability_type;
			$post_type_caps	= $post_type_details->cap;
			$post_role		= $post_type.'-'.$key;

			// Shared capability required to see post's menu & publish posts
			if ( ( isset( $_POST[ $post_role.'-publish' ] ) && $_POST[ $post_role.'-publish' ] == 'on' ) || ( isset( $_POST[ $post_role.'-edit' ] ) && $_POST[ $post_role.'-edit' ] == 'on' ) || ( isset( $_POST[ $post_role.'-edit-others' ] ) && $_POST[ $post_role.'-edit-others' ] == 'on' ) ) {
				$role->add_cap( $post_type_caps->edit_posts );
			} else {
				$role->remove_cap( $post_type_caps->edit_posts );
			}

			// Shared capability required to delete posts
			if ( ( isset( $_POST[ $post_role.'-publish' ] ) && $_POST[ $post_role.'-publish' ] == 'on' ) || ( isset( $_POST[ $post_role.'-edit' ] ) && $_POST[ $post_role.'-edit' ] == 'on' ) ) {
				$role->add_cap( $post_type_caps->delete_posts );
			} else {
				$role->remove_cap( $post_type_caps->delete_posts );
			}

			// Allow publish
			if ( isset( $_POST[ $post_role.'-publish' ] ) && $_POST[ $post_role.'-publish' ] == 'on' ) {
				$role->add_cap( $post_type_caps->publish_posts );
			} else {
				$role->remove_cap( $post_type_caps->publish_posts );
			}

			// Allow editing own posts
			if ( ( isset( $_POST[ $post_role.'-edit' ] ) && $_POST[ $post_role.'-edit' ] == 'on' ) || ( isset( $_POST[ $post_role.'-edit-others' ] ) && $_POST[ $post_role.'-edit-others' ] == 'on' ) ) {
				$role->add_cap( $post_type_caps->edit_published_posts );
				$role->add_cap( $post_type_caps->edit_private_posts );
				$role->add_cap( $post_type_caps->delete_published_posts );
				$role->add_cap( $post_type_caps->delete_private_posts );
			} else {
				$role->remove_cap( $post_type_caps->edit_published_posts );
				$role->remove_cap( $post_type_caps->edit_private_posts );
				$role->remove_cap( $post_type_caps->delete_published_posts );
				$role->remove_cap( $post_type_caps->delete_private_posts );
			}

			// Allow editing other's posts
			if ( isset( $_POST[ $post_role.'-edit-others' ] ) && $_POST[ $post_role.'-edit-others' ] == 'on' ) {
				$role->add_cap( $post_type_caps->edit_others_posts );
				$role->add_cap( $post_type_caps->delete_others_posts );
			} else {
				$role->remove_cap( $post_type_caps->edit_others_posts );
				$role->remove_cap( $post_type_caps->delete_others_posts );
			}

			// Allow reading private
			if ( isset( $_POST[ $post_role.'-private' ] ) && $_POST[ $post_role.'-private' ] == 'on' )
				$role->add_cap( $post_type_caps->read_private_posts);
			else
				$role->remove_cap( $post_type_caps->read_private_posts );
		}
	}
    return 'Settings saved';
}


/** 
 * If a post author doesn't have permission to edit their own custom post types, they are redirected
 * to the post.php page, but what if they don't have permission to edit vanilla posts? WP Breaks. 
 *
 * This is a bit cludgy, so this function redirects them to that post type's admin index page and adds 
 * a message to show post was published.
 */
function mc_post_access_denied_redirect() {
	global $pagenow;

	if( $pagenow == 'edit.php' ) { // @TODO find a way to determine this with better specificity
		wp_redirect( add_query_arg( array( 'updated' => 1 ), admin_url( 'index.php' ) ) );
		exit;
	}
}
add_action( 'admin_page_access_denied', 'mc_post_access_denied_redirect', 20 ); //run after other functions
