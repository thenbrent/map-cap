<?php
/*
Plugin Name: Map Cap
Plugin URI: http://prospress.org
Description: Recreate a WordPress role, like contributor, for your custom posts. Silly name, useful code.
Author: Brent Shepherd
Version: 0.1
Author URI: http://brentshepherd.com/
*/

function mc_add_admin_menu() {
	if ( function_exists('add_options_page') ) { // additional pages under E-Commerce
		$page = add_options_page( 'Map Caps', 'Map Cap', 'manage_options', 'mapcap', 'mc_capabilities_settings_page' );
	}
}
add_action( 'admin_menu', 'mc_add_admin_menu' );

/** 
 * Admin's may want to allow or disallow users to create, edit and delete custom post type.
 * To do this without relying on the post capability type, Prospress creates it's own type. 
 * This function provides an admin menu for selecting which roles can do what to posts. 
 * 
 * Allow site admin to choose which roles can do what to marketplace posts.
 */
function mc_capabilities_settings_page() { 
	global $wp_roles;

	$message = do_action( 'mc_capabilities_settings_page' );

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key => $value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' ); 
	error_log('$post_types = ' . print_r( $post_types, true ));
	
	?>
	<div class="wrap map-cap-settings">
	  <h2><?php _e( 'Map Capabilities', 'map-cap' ); ?></h2>
	  <?php if ( $message ) : ?>
	    <div id="message" class="updated fade"><p><?php echo $$message; ?></p></div>
	  <?php endif; ?>
	  <form id="map-cap-form" method="post" action="">
	<?php
	foreach( $post_types as $post_type => $post_type_details ) {
		wp_nonce_field( 'mc_capabilities_settings' );
		?>
		<h3><?php printf( __( '%s Capabilities', 'map-cap' ), $post_type_details->labels->singular_name ); ?></h3>
		<div class="map-cap">
			<h4><?php printf( __( "Publish %s", 'map-cap' ), $post_type_details->labels->name ); ?></h4>
			<?php foreach ( $roles as $role ): ?>
			<label for="<?php echo $post_type . '-' . $role->name; ?>-publish">
				<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-publish" name="<?php echo $post_type . '-' . $role->name; ?>-publish"<?php checked( $role->capabilities[ 'publish_' . $post_type . '_posts' ], 1 ); ?> />
				<?php echo $role->display_name; ?>
			</label>
			<?php endforeach; ?>
		</div>
		<div class="map-cap">
			<h4><?php printf( __( "Edit own %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
			<?php foreach ( $roles as $role ): ?>
			<label for="<?php echo $post_type . '-' . $role->name; ?>-edit">
			  	<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit" name="<?php echo $post_type . '-' . $role->name; ?>-edit"<?php checked( $role->capabilities[ 'edit_published_' . $post_type . '_posts' ], 1 ); ?> />
				<?php echo $role->display_name; ?>
			</label>
			<?php endforeach; ?>
		</div>
		<div class="map-cap">
			<h4><?php printf( __( "Edit others' %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
			<?php foreach ( $roles as $role ): ?>
			<label for="<?php echo $post_type . '-' . $role->name; ?>-edit-others">
				<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-edit-others" name="<?php echo $post_type . '-' . $role->name; ?>-edit-others"<?php checked( $role->capabilities[ 'edit_others_' . $post_type . '_posts' ], 1 ); ?> />
				<?php echo $role->display_name; ?>
			</label>
			<?php endforeach; ?>
		</div>
		<div class="map-cap">
			<h4><?php printf( __( "View private %s", 'map-cap' ), $post_type_details->labels->name  ); ?></h4>
			<?php foreach ( $roles as $role ): ?>
			<label for="<?php echo $post_type . '-' . $role->name; ?>-private">
				<input type="checkbox" id="<?php echo $post_type . '-' . $role->name; ?>-private" name="<?php echo $post_type . '-' . $role->name; ?>-private"<?php checked( $role->capabilities[ 'read_private_' . $post_type . '_posts' ], 1 ); ?> />
				<?php echo $role->display_name; ?>
			</label>
			<?php endforeach; ?>
		</div>
	<?php } ?>
    <div class="save-settings-form" style="margin-top: 1em;">
      <input class="button button-highlighted" type="submit" value="<?php _e( 'Save', 'map-cap' ); ?>" />
    </div>
	</form>
	</div>
	<?php
}


/** 
 * Save capabilities settings when the admin page is submitted page. As the settings don't need to be stored in 
 * the options table of the database, they're not added to the whitelist as is expected by this filter, instead 
 * they're added to the appropriate roles.
 */
function mc_save_capabilities() {
	global $wp_roles;

    if ( $_POST['_wpnonce' ] && check_admin_referer( 'mc_capabilities_settings' ) && current_user_can( 'manage_options' ) ){

		error_log('POST = ' . print_r( $_POST, true ) );

		$role_names = $wp_roles->get_names();
		$roles = array();

		foreach ( $role_names as $key=>$value ) {
			$roles[ $key ] = get_role( $key );
			$roles[ $key ]->display_name = $value;
		}

		$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' ); 

		foreach ( $roles as $key => $role ) {

			foreach( $post_types as $post_type => $post_type_details ) {

				// Shared capability
				if ( ( isset( $_POST[ $post_type . '-' . $key . '-publish' ] )  && $_POST[ $post_type . '-' . $key . '-publish' ] == 'on' ) || ( isset( $_POST[ $post_type . '-' . $key . '-edit' ] )  && $_POST[ $post_type . '-' . $key . '-edit' ] == 'on' ) || ( isset( $_POST[ $post_type . '-' . $key . '-edit-others' ] )  && $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) ) {
					$role->add_cap( 'edit_' . $post_type . '_posts' );
				} else {
					$role->remove_cap( 'edit_' . $post_type . '_posts' );
				}

				if ( isset( $_POST[ $post_type . '-' . $key . '-publish' ] )  && $_POST[ $post_type . '-' . $key . '-publish' ] == 'on' ) {
					$role->add_cap( 'publish_' . $post_type . '_posts' );
					$role->add_cap( 'delete_' . $post_type . '_posts' );
				} else {
					$role->remove_cap( 'publish_' . $post_type . '_posts' );
					$role->remove_cap( 'delete_' . $post_type . '_posts' );
				}

				if ( ( isset( $_POST[ $post_type . '-' . $key . '-edit' ] )  && $_POST[ $post_type . '-' . $key . '-edit' ] == 'on' ) || ( isset( $_POST[ $post_type . '-' . $key . '-edit-others' ] )  && $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) ) {
					$role->add_cap( 'edit_published_' . $post_type . '_posts' );
					$role->add_cap( 'delete_published_' . $post_type . '_posts' );
					$role->add_cap( 'edit_private_' . $post_type . '_posts' );
				} else {
					$role->remove_cap( 'edit_published_' . $post_type . '_posts' );
					$role->remove_cap( 'delete_published_' . $post_type . '_posts' );
					$role->remove_cap( 'edit_private_' . $post_type . '_posts' );
				}

				if ( isset( $_POST[ $post_type . '-' . $key . '-edit-others' ] )  && $_POST[ $post_type . '-' . $key . '-edit-others' ] == 'on' ) {
					$role->add_cap( 'edit_others_' . $post_type . '_posts' );
				} else {
					$role->remove_cap( 'edit_others_' . $post_type . '_posts' );
		        }

				if ( isset( $_POST[ $post_type . '-' . $key . '-private' ] )  && $_POST[ $post_type . '-' . $key . '-private' ] == 'on' ) {
					$role->add_cap( 'edit_' . $post_type . '_posts' );
					$role->add_cap( 'read_private_' . $post_type . '_posts' );
				} else {
					$role->remove_cap( 'read_private_' . $post_type . '_posts' );
					$role->remove_cap( 'edit_' . $post_type . '_posts' );
				}
				
				error_log( ' role = ' . print_r( $role, true ) );
			}
		}
    }
    return 'Settings saved';
}
add_action('mc_capabilities_settings_page', 'mc_save_capabilities', 1);


function mc_map_meta_cap( $caps, $cap, $user_id, $args ){

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'objects' ); 

	foreach( $post_types as $post_type => $post_type_details ) {
		//error_log( "in mc_map_meta_cap, cap = $cap, post_type = $post_type" );
		if( $cap == 'edit_' . $post_type . '_post' ) {

			$author_data = get_userdata( $user_id );

			$post = get_post( $args[0] );

			$post_type = get_post_type_object( $post->post_type );

			$post_author_data = get_userdata( $post->post_author );

			if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID ) {

				if ( 'publish' == $post->post_status ) {
					$caps[0] = 'edit_published_' . $post_type . '_posts';
				} elseif ( 'private' == $post->post_status ) {
					$caps[0] = 'edit_private_' . $post_type . '_posts';
				} elseif ( 'trash' == $post->post_status ) {
					if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
						$caps[0] = 'edit_published_' . $post_type . '_posts';
				} else {
					$caps[0] = 'edit_' . $post_type . '_posts';
					$caps[] = 'publish_' . $post_type . '_posts';
				}
			} else {
				$caps[0] = 'edit_others_' . $post_type . '_posts';

				if ( 'publish' == $post->post_status )
					$caps[] = 'edit_published_' . $post_type . '_posts';
				elseif ( 'private' == $post->post_status )
					$caps[] = 'edit_private_' . $post_type . '_posts';
			}
		} elseif( $cap == 'delete_' . $post_type . '_post' ) {
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
					$caps[0] = 'delete_published_' . $post_type . '_posts';
				} elseif ( 'trash' == $post->post_status ) {
					if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
						$caps[0] = 'delete_published_' . $post_type . '_posts';
				} else {
					$caps[0] = 'delete_' . $post_type . '_posts';
				}
			} else {
				$caps[0] = 'edit_others_' . $post_type . '_posts';

				if ( 'publish' == $post->post_status || 'private' == $post->post_status )
					$caps[] = 'delete_published_' . $post_type . '_posts';
			}
		} elseif( $cap == 'read_' . $post_type . '_post' ) {
			$post = get_post( $args[0] );

			if ( 'private' != $post->post_status ) {
				$caps[0] = 'read';
			} else {
				$author_data = get_userdata( $user_id );
				$post_author_data = get_userdata( $post->post_author );
				if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID )
					$caps[0] = 'read';
				else
					$caps[0] = 'read_private_' . $post_type . '_posts';
			}
		}
	}
	return $caps;
}
add_filter( 'map_meta_cap', 'mc_map_meta_cap', 10, 4 );