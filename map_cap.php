<?php
/*
Plugin Name: Map Cap
Plugin URI: https://github.com/thenbrent/map-cap
Description: Control who can publish, edit and delete custom post types.  Silly name, useful code.
Author: Brent Shepherd
Version: 1.0
Author URI: http://brentshepherd.com/
*/

function mc_add_admin_menu() {
	if ( function_exists('add_options_page') ) { // additional pages under E-Commerce
		$page = add_options_page( 'Map Caps', 'Map Cap', 'manage_options', 'mapcap', 'mc_capabilities_settings_page' );
	}
}
add_action( 'admin_menu', 'mc_add_admin_menu' );

/** 
 * Site admin's may want to allow or disallow users to create, edit and delete custom post type.
 * This function provides an admin menu for selecting which roles can do what with custom posts.
 **/
function mc_capabilities_settings_page() { 
	global $wp_roles;

	$message = mc_save_capabilities();

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key => $value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' );


	echo '<div class="wrap map-cap-settings">';
	screen_icon();
	echo '<h2>' . __( 'Map Capabilities', 'map-cap' ) . '</h2>';

	if ( !empty( $message ) )
		echo '<div id="message" class="updated fade"><p>' . $message . '</p></div>';

	if ( empty( $post_types ) ) :
		echo '<p>' . __( 'No custom post types registered.', 'map-cap' ) . '</p>';
	else:
		echo '<form id="map-cap-form" method="post" action="">';

		foreach( $post_types as $post_type => $post_type_details ) {

			$post_type_cap	= $post_type_details->capability_type;
			$post_type_caps	= $post_type_details->cap;

			wp_nonce_field( 'mc_capabilities_settings' );
			?>
			<h3><?php printf( __( '%s Capabilities', 'map-cap' ), $post_type_details->labels->name ); ?></h3>

			<? // Allow publish ?>
			<div class="map-cap">
				<h4><?php printf( __( "Publish %s", 'map-cap' ), $post_type_details->labels->name ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-publish">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-publish" name="<?php echo $post_type . '-' . $role->name; ?>-publish"<?php checked( $role->capabilities[ $post_type_caps->publish_posts ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing own posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Own %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit">
				  	<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit" name="<?php echo $post_type . '-' . $role->name; ?>-edit"<?php checked( $role->capabilities[ 'edit_published_' . $post_type_cap . '_posts' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow editing others posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "Edit Others' %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-edit-others">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit-others" name="<?php echo $post_type . '-' . $role->name; ?>-edit-others"<?php checked( $role->capabilities[ $post_type_caps->edit_others_posts ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>

			<? // Allow reading private posts ?>
			<div class="map-cap">
				<h4><?php printf( __( "View Private %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
				<?php foreach ( $roles as $role ): ?>
				<label for="<?php echo $post_type . '-' . $role->name; ?>-private">
					<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-private" name="<?php echo $post_type . '-' . $role->name; ?>-private"<?php checked( $role->capabilities[ $post_type_caps->read_private_posts], 1 ); ?> />
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

    if ( !$_POST['_wpnonce' ] || !check_admin_referer( 'mc_capabilities_settings' ) || !current_user_can( 'manage_options' ) )
		return;

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key=>$value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' ); 

	foreach ( $roles as $key => $role ) {

		foreach( $post_types as $post_type => $post_type_details ) {

			$post_type_cap 	= $post_type_details->capability_type;
			$post_type_caps	= $post_type_details->cap;

			// Shared capability require to see post's menu
			if ( $_POST[ $post_type . '-' . $key . '-publish' ] == 'on' || $_POST[ $post_type . '-' . $key . '-edit' ] == 'on' || $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' )
				$role->add_cap( $post_type_caps->edit_posts );
			else
				$role->remove_cap( $post_type_caps->edit_posts );

			// Allow publish
			if ( $_POST[ $post_type . '-' . $key . '-publish' ] == 'on' ) {
				$role->add_cap( $post_type_caps->publish_posts );
				$role->add_cap( $post_type_caps->delete_post . 's');
			} else {
				$role->remove_cap( $post_type_caps->publish_posts );
				$role->remove_cap( $post_type_caps->delete_post . 's');
			}

			// Allow editing own posts
			if ( $_POST[ $post_type . '-' . $key . '-edit' ] == 'on' || $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) {
				$role->add_cap( 'edit_published_' . $post_type_cap . '_posts' );
				$role->add_cap( 'delete_published_' . $post_type_cap . '_posts' );
				$role->add_cap( 'edit_private_' . $post_type_cap . '_posts' );
			} else {
				$role->remove_cap( 'edit_published_' . $post_type_cap . '_posts' );
				$role->remove_cap( 'delete_published_' . $post_type_cap . '_posts' );
				$role->remove_cap( 'edit_private_' . $post_type_cap . '_posts' );
			}

			// Allow editing other's posts
			if ( $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' )
				$role->add_cap( $post_type_caps->edit_others_posts );
			else
				$role->remove_cap( $post_type_caps->edit_others_posts );

			// Allow reading private
			if ( $_POST[ $post_type . '-' . $key . '-private' ] == 'on' )
				$role->add_cap( $post_type_caps->read_private_posts);
			else
				$role->remove_cap( $post_type_caps->read_private_posts );
		}
	}
    return 'Settings saved';
}


/** 
 * Custom posts' meta capabilities are not mapped by WordPress, so need to manually map them.
 * This function is based on the map_meta_cap function in the capabilities.php file
 **/
function mc_map_meta_cap( $caps, $cap, $user_id, $args ){

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' ); 

	foreach( $post_types as $post_type_name => $post_type_details ) {
		
		$post_type_cap	= $post_type_details->capability_type;
		$post_type_caps	= $post_type_details->cap;

		if( $cap == $post_type_caps->edit_post ) {

			$author_data = get_userdata( $user_id );

			$post = get_post( $args[0] );

			$post_type = get_post_type_object( $post->post_type );

			$post_author_data = get_userdata( $post->post_author );

			if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID ) {

				if ( 'publish' == $post->post_status ) {
					$caps[0] = 'edit_published_' . $post_type_cap . '_posts';
				} elseif ( 'private' == $post->post_status ) {
					$caps[0] = 'edit_private_' . $post_type_cap . '_posts';
				} elseif ( 'trash' == $post->post_status ) {
					if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
						$caps[0] = 'edit_published_' . $post_type_cap . '_posts';
				} else {
					$caps[0] = $post_type_caps->edit_posts;
					$caps[] = $post_type_caps->publish_posts;
				}
			} else {
				$caps[0] = $post_type_caps->edit_others_posts;

				if ( 'publish' == $post->post_status )
					$caps[] = 'edit_published_' . $post_type_cap . '_posts';
				elseif ( 'private' == $post->post_status )
					$caps[] = 'edit_private_' . $post_type_cap . '_posts';
			}
		} elseif( $cap == $post_type_caps->delete_post ) {
			$author_data = get_userdata( $user_id );
			$post = get_post( $args[0] );

			if ( '' != $post->post_author ) {
				$post_author_data = get_userdata( $post->post_author );
			} else {
				//No author, default to current user
				$post_author_data = $author_data;
			}

			if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID ) {

				if ( 'publish' == $post->post_status ) {
					$caps[0] = 'delete_published_' . $post_type_cap . '_posts';
				} elseif ( 'trash' == $post->post_status ) {
					if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
						$caps[0] = 'delete_published_' . $post_type_cap . '_posts';
				} else {
					$caps[0] = 'delete_' . $post_type_cap . '_posts';
				}
			} else {
				$caps[0] = $post_type_caps->edit_others_posts;

				if ( 'publish' == $post->post_status || 'private' == $post->post_status )
					$caps[] = 'delete_published_' . $post_type_cap . '_posts';
			}
		} elseif( $cap ==  $post_type_caps->read_post ) {
			$post = get_post( $args[0] );

			if ( 'private' != $post->post_status ) {
				$caps[0] = 'read';
			} else {
				$author_data = get_userdata( $user_id );
				$post_author_data = get_userdata( $post->post_author );
				if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID )
					$caps[0] = 'read';
				else
					$caps[0] = $post_type_caps->read_private_posts;
			}
		}
	}
	return $caps;
}
add_filter( 'map_meta_cap', 'mc_map_meta_cap', 10, 4 );