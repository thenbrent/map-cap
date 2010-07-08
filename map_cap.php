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
	global $wp_roles, $market_system;

	$role_names = $wp_roles->get_names();
	$roles = array();

	foreach ( $role_names as $key => $value ) {
		$roles[ $key ] = get_role( $key );
		$roles[ $key ]->display_name = $value;
	}

	$post_types = get_post_types( array( 'public'   => true, '_builtin' => false ), 'names', 'and'); 
	
	wp_nonce_field( 'pp_capabilities_settings' );  ?>
	<div class="wrap buggypress-settings">
	<div class="prospress-capabilities">
	<?php
	foreach( $post_types as $post_type ) {
	?>
			<h3><?php printf( __( '%s Capabilities', 'map-cap' ), ucfirst( $post_type ) ); ?></h3>
			<div class="map-cap">
				<h4><?php printf( __( "Publish %s", 'map-cap' ), $market_system->display_name() ); ?></h4>
				<?php foreach ( $roles as $role ): if( $role->name == 'administrator' ) continue; ?>
				<label for="<?php echo $role->name; ?>-publish">
					<input type="checkbox" id="<?php echo $role->name; ?>-publish" name="<?php echo $role->name; ?>-publish"<?php checked( $role->capabilities[ 'publish_prospress_posts' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
			<div class="map-cap">
				<h4><?php printf( __( "Edit own %s", 'map-cap' ), $market_system->display_name() ); ?></h4>
				<?php foreach ( $roles as $role ): if( $role->name == 'administrator' ) continue; ?>
				<label for="<?php echo $role->name; ?>-edit">
				  	<input type="checkbox" id="<?php echo $role->name; ?>-edit" name="<?php echo $role->name; ?>-edit"<?php checked( $role->capabilities[ 'edit_published_prospress_posts' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
			<div class="map-cap">
				<h4><?php printf( __( "Edit others' %s", 'map-cap' ), $market_system->display_name() ); ?></h4>
				<?php foreach ( $roles as $role ): if( $role->name == 'administrator' ) continue; ?>
				<label for="<?php echo $role->name; ?>-edit-others">
					<input type="checkbox" id="<?php echo $role->name; ?>-edit-others" name="<?php echo $role->name; ?>-edit-others"<?php checked( $role->capabilities[ 'edit_others_prospress_posts' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
			<div class="map-cap">
				<h4><?php printf( __( "View private %s", 'map-cap' ), $market_system->display_name() ); ?></h4>
				<?php foreach ( $roles as $role ): if( $role->name == 'administrator' ) continue; ?>
				<label for="<?php echo $role->name; ?>-private">
					<input type="checkbox" id="<?php echo $role->name; ?>-private" name="<?php echo $role->name; ?>-private"<?php checked( $role->capabilities[ 'read_private_prospress_posts' ], 1 ); ?> />
					<?php echo $role->display_name; ?>
				</label>
				<?php endforeach; ?>
			</div>
	<?php } ?>
	</div>
	</div>
	<?php
}


/** 
 * Save capabilities settings when the admin page is submitted page. As the settings don't need to be stored in 
 * the options table of the database, they're not added to the whitelist as is expected by this filter, instead 
 * they're added to the appropriate roles.
 */
function mc_capabilities_whitelist( $whitelist_options ) {
	global $wp_roles, $market_system;

    if ( $_POST['_wpnonce' ] && check_admin_referer( 'pp_capabilities_settings' ) && current_user_can( 'manage_options' ) ){

		$role_names = $wp_roles->get_names();
		$roles = array();

		foreach ( $role_names as $key=>$value ) {
			$roles[ $key ] = get_role( $key );
			$roles[ $key ]->display_name = $value;
		}

		foreach ( $roles as $key => $role ) {

			if( $role->name == 'administrator' )
				continue;

			// Shared capability
			if ( ( isset( $_POST[ $key . '-publish' ] )  && $_POST[ $key . '-publish' ] == 'on' ) || ( isset( $_POST[ $key . '-edit' ] )  && $_POST[ $key . '-edit' ] == 'on' ) || ( isset( $_POST[ $key . '-edit-others' ] )  && $_POST[ $key . '-edit-others' ] == 'on' ) ) {
				$role->add_cap( 'edit_prospress_posts' );
			} else {
				$role->remove_cap( 'edit_prospress_posts' );
			}

			if ( isset( $_POST[ $key . '-publish' ] )  && $_POST[ $key . '-publish' ] == 'on' ) {
				$role->add_cap( 'publish_prospress_posts' );
				$role->add_cap( 'delete_prospress_posts' );
			} else {
				$role->remove_cap( 'publish_prospress_posts' );
				$role->remove_cap( 'delete_prospress_posts' );
			}

			if ( ( isset( $_POST[ $key . '-edit' ] )  && $_POST[ $key . '-edit' ] == 'on' ) || ( isset( $_POST[ $key . '-edit-others' ] )  && $_POST[ $key . '-edit-others' ] == 'on' ) ) {
				$role->add_cap( 'edit_published_prospress_posts' );
				$role->add_cap( 'delete_published_prospress_posts' );
				$role->add_cap( 'edit_private_prospress_posts' );
			} else {
				$role->remove_cap( 'edit_published_prospress_posts' );
				$role->remove_cap( 'delete_published_prospress_posts' );
				$role->remove_cap( 'edit_private_prospress_posts' );
			}

			if ( isset( $_POST[ $key . '-edit-others' ] )  && $_POST[ $key . '-edit-others' ] == 'on' ) {
				$role->add_cap( 'edit_others_prospress_posts' );
			} else {
				$role->remove_cap( 'edit_others_prospress_posts' );
	        }

			if ( isset( $_POST[ $key . '-private' ] )  && $_POST[ $key . '-private' ] == 'on' ) {
				$role->add_cap( 'edit_prospress_posts' );
				$role->add_cap( 'read_private_prospress_posts' );
			} else {
				$role->remove_cap( 'read_private_prospress_posts' );
				$role->remove_cap( 'edit_prospress_posts' );
			}

		}
    }

	return $whitelist_options;
}
add_filter( 'pp_options_whitelist', 'pp_capabilities_whitelist' );


function mc_map_meta_cap( $caps, $cap, $user_id, $args ){

	if( $cap == 'edit_prospress_post' ) {

		$author_data = get_userdata( $user_id );

		$post = get_post( $args[0] );

		$post_type = get_post_type_object( $post->post_type );

		$post_author_data = get_userdata( $post->post_author );

		if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID ) {

			if ( 'publish' == $post->post_status ) {
				$caps[0] = 'edit_published_prospress_posts';
			} elseif ( 'private' == $post->post_status ) {
				$caps[0] = 'edit_private_prospress_posts';
			} elseif ( 'trash' == $post->post_status ) {
				if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
					$caps[0] = 'edit_published_prospress_posts';
			} else {
				$caps[0] = 'edit_prospress_posts';
				$caps[] = 'publish_prospress_posts';
			}
		} else {
			$caps[0] = 'edit_others_prospress_posts';

			if ( 'publish' == $post->post_status )
				$caps[] = 'edit_published_prospress_posts';
			elseif ( 'private' == $post->post_status )
				$caps[] = 'edit_private_prospress_posts';
		}
	} elseif( $cap == 'delete_prospress_post' ) {
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
				$caps[0] = 'delete_published_prospress_posts';
			} elseif ( 'trash' == $post->post_status ) {
				if ('publish' == get_post_meta($post->ID, '_wp_trash_meta_status', true) )
					$caps[0] = 'delete_published_prospress_posts';
			} else {
				$caps[0] = 'delete_prospress_posts';
			}
		} else {
			$caps[0] = 'edit_others_prospress_posts';

			if ( 'publish' == $post->post_status || 'private' == $post->post_status )
				$caps[] = 'delete_published_prospress_posts';
		}
	} elseif( $cap == 'read_prospress_post' ) {
		$post = get_post( $args[0] );

		if ( 'private' != $post->post_status ) {
			$caps[0] = 'read';
		} else {
			$author_data = get_userdata( $user_id );
			$post_author_data = get_userdata( $post->post_author );
			if ( is_object( $post_author_data ) && $user_id == $post_author_data->ID )
				$caps[0] = 'read';
			else
				$caps[0] = 'read_private_prospress_posts';
		}
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'pp_map_meta_cap', 10, 4 );