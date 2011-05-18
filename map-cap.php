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

	// Don't edit capabilties for any custom post type with "post" as its capability type
	$post_types = array_diff( $post_types, $dont_touch );

	echo '<div class="wrap map-cap-settings">';
	screen_icon();
	echo '<h2>' . __( 'Map Capabilities', 'map-cap' ) . '</h2>';

	if ( !empty( $message ) )
		echo '<div id="message" class="updated fade"><p>' . $message . '</p></div>';

	if ( empty( $post_types ) ) :
		echo '<p>' . __( 'No custom post types registered.', 'map-cap' ) . '</p>';
	else:
		echo '<form id="map-cap-form" method="post" action="">';

		foreach( $post_types as $post_type ) {
			
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
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-publish" name="<?php echo $post_type . '-' . $role->name; ?>-publish"<?php checked( @$role->capabilities[ $post_type_caps->publish_posts ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing own posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Own %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit">
				  	<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit" name="<?php echo $post_type . '-' . $role->name; ?>-edit"<?php checked( @$role->capabilities[ 'edit_published_' . $post_type_cap . 's' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing others posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Others' %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit-others">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit-others" name="<?php echo $post_type . '-' . $role->name; ?>-edit-others"<?php checked( @$role->capabilities[ $post_type_caps->edit_others_posts ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow reading private posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "View Private %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-private">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-private" name="<?php echo $post_type . '-' . $role->name; ?>-private"<?php checked( @$role->capabilities[ $post_type_caps->read_private_posts], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
		<?php } ?>
		<p class="submit">
			<input type="submit" name="submit" class="button button-primary" value="<?php _e( 'Save', 'map-cap' ); ?>" />
		</p>
		</form>
		<?php
	endif;
	echo '</div>';
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

			// Shared capability required to see post's menu & publish posts
			if ( @$_POST[ $post_type . '-' . $key . '-publish' ] == 'on' || @$_POST[ $post_type . '-' . $key . '-edit' ] == 'on' || @$_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) {
				$role->add_cap( $post_type_caps->edit_posts );
				$role->add_cap( $post_type_caps->publish_posts );
				$role->add_cap( $post_type_caps->delete_posts );
			} else {
				$role->remove_cap( $post_type_caps->edit_posts );
				$role->remove_cap( $post_type_caps->publish_posts );
				$role->remove_cap( $post_type_caps->delete_posts );
			}

			// Allow editing own posts
			if ( @$_POST[ $post_type . '-' . $key . '-edit' ] == 'on' || @$_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) {
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
			if ( @$_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ){
				$role->add_cap( $post_type_caps->edit_others_posts );
				$role->add_cap( $post_type_caps->delete_others_posts );
			} else {
				$role->remove_cap( $post_type_caps->edit_others_posts );
				$role->remove_cap( $post_type_caps->delete_others_posts );
			}

			// Allow reading private
			if ( @$_POST[ $post_type . '-' . $key . '-private' ] == 'on' )
				$role->add_cap( $post_type_caps->read_private_posts);
			else
				$role->remove_cap( $post_type_caps->read_private_posts );
		}
	}
    return 'Settings saved';
}

