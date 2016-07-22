<?php
	
/**
 * Package and Addon Posts
 *
 * @package     MDJM
 * @subpackage  Equipment
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
		
/**
 * Define the columns to be displayed for addon posts
 *
 * @since	1.4
 * @param	arr		$columns	Array of column names
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_addon_post_columns( $columns ) {

	$columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => __( 'Addon', 'mobile-dj-manager' ),
			'price'  => __( 'Price', 'mobile-dj-manager' )
		);

	if( ! mdjm_employee_can( 'manage_packages' ) && isset( $columns['cb'] ) )	{
		unset( $columns['cb'] );
	}
		
	return $columns;
} // mdjm_addon_post_columns
add_filter( 'manage_mdjm-addon_posts_columns' , 'mdjm_addon_post_columns' );
		
/**
 * Define which columns are sortable for addon posts
 *
 * @since	1.4
 * @param	arr		$sortable_columns	Array of addon post sortable columns
 * @return	arr		$sortable_columns	Filtered Array of addon post sortable columns
 */
function mdjm_addon_post_sortable_columns( $sortable_columns )	{
	$sortable_columns['price'] = 'price';
	
	return $sortable_columns;
} // mdjm_addon_post_sortable_columns
add_filter( 'manage_edit-mdjm-addon_sortable_columns', 'mdjm_addon_post_sortable_columns' );
		
/**
 * Define the data to be displayed in each of the custom columns for the Addon post types
 *
 * @since	1.4
 * @param	str		$column_name	The name of the column to display
 * @param	int		$post_id		The current post ID
 * @return
 */
function mdjm_addon_posts_custom_column( $column_name, $post_id )	{
	global $post;
	
	if( mdjm_employee_can( 'edit_txns' ) && ( $column_name == 'value' || $column_name == 'balance' ) )	{
		$value = mdjm_get_event_price( $post_id );
	}
		
	switch ( $column_name ) {
		// Addon Price
		case 'price':
			if( mdjm_employee_can( 'read_events' ) )	{
				echo '<strong><a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">' . date( 'd M Y', strtotime( get_post_meta( $post_id, '_mdjm_event_date', true ) ) ) . '</a>';
			} else	{
				echo '<strong>' . date( 'd M Y', strtotime( get_post_meta( $post_id, '_mdjm_event_date', true ) ) ) . '</strong>';
			}
		break;

	} // switch
	
} // mdjm_event_posts_custom_column
add_action( 'manage_mdjm-addon_posts_custom_column' , 'mdjm_addon_posts_custom_column', 10, 2 );

/**
 * Set the addon post placeholder title.
 *
 * @since	1.4
 * @param	str		$title	Current post placeholder title
 * @return	str		$title	Post placeholder title
 */
function mdjm_addon_set_post_title_placeholder( $title ) {
	
	$screen = get_current_screen();

	if ( 'mdjm-addon' == $screen->post_type )	{
		$title = __( 'Enter a name for this add-on', 'mobile-dj-manager' );
	}

	return $title;

} // mdjm_addon_set_post_title
add_action( 'enter_title_here', 'mdjm_addon_set_post_title_placeholder' );

/**
 * Order addon posts.
 *
 * @since	1.4
 * @param	obj		$query		The WP_Query object
 * @return	void
 */
function mdjm_addon_post_order( $query )	{
	
	if ( ! is_admin() || 'mdjm-addon' != $query->get( 'post_type' ) )	{
		return;
	}

	$orderby = $query->get( 'orderby' );
	$order   = $query->get( 'order' );

	switch( $orderby )	{
		case 'ID':
		default:
			$query->set( 'orderby',  'ID' );
			$query->set( 'order',  $order );
			break;

		case 'price':
			$query->set( 'meta_key', '_addon_price' );
			$query->set( 'orderby',  'meta_value' );
			$query->set( 'order',  $order );
            break;
	}
	
} // mdjm_event_post_order
add_action( 'pre_get_posts', 'mdjm_addon_post_order' );

/**
 * Hook into pre_get_posts and limit employees addons if their permissions are not full.
 *
 * @since	1.4
 * @param	arr		$query		The WP_Query
 * @return	void
 */
function mdjm_limit_results_to_employee_addons( $query )	{
	
	if ( ! is_admin() || 'mdjm-addon' != $query->get( 'post_type' ) || mdjm_employee_can( 'mdjm_package_edit' ) )	{
		return;
	}
			
	global $user_ID;
	
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_addon_employees',
				'value'   => sprintf( ':"%s";', $user_ID ),
				'compare' => 'LIKE'
			)
		)
	);

} // mdjm_limit_results_to_employee_addons
add_action( 'pre_get_posts', 'mdjm_limit_results_to_employee_addons' );

/**
 * Map the meta capabilities
 *
 * @since	1.3
 * @param	arr		$caps		The users actual capabilities
 * @param	str		$cap		The capability name
 * @param	int		$user_id	The user ID
 * @param	arr		$args		Adds the context to the cap. Typically the object ID.
 */
function mdjm_addon_map_meta_cap( $caps, $cap, $user_id, $args )	{
	
	// If editing, deleting, or reading a package or addon, get the post and post type object.
	if ( 'edit_mdjm_package' == $cap || 'delete_mdjm_package' == $cap || 'read_mdjm_package' == $cap || 'publish_mdjm_package' == $cap ||
		 'edit_mdjm_addon' == $cap || 'delete_mdjm_addon' == $cap || 'read_mdjm_addon' == $cap || 'publish_mdjm_addon' == $cap ) {
		
		$post = get_post( $args[0] );
		
		if ( empty( $post ) )	{
			return $caps;
		}
		
		$post_type = get_post_type_object( $post->post_type );

		// Set an empty array for the caps.
		$caps = array();
		
	}
			
	// If editing a package or an addon, assign the required capability. */
	if ( 'read_mdjm_package' == $cap || 'edit_mdjm_addon' == $cap )	{
		
		if ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) )	{
			$caps[] = $post_type->cap->edit_posts;
		} else	{
			$caps[] = $post_type->cap->edit_others_posts;
		}

	}
	
	// If deleting a package or an addon, assign the required capability.
	elseif ( 'delete_mdjm_package' == $cap || 'delete_mdjm_addon' == $cap ) {
		
		if ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) )	{
			$caps[] = $post_type->cap->delete_posts;
		} else	{
			$caps[] = $post_type->cap->delete_others_posts;
		}

	}
	
	// If reading a private package or addon, assign the required capability.
	elseif ( 'read_mdjm_package' == $cap || 'read_mdjm_addon' == $cap )	{

		if ( 'private' != $post->post_status )	{
			$caps[] = 'read';
		} elseif ( in_array( $user_id, mdjm_get_event_employees( $post->ID ) ) )	{
			$caps[] = 'read';
		} else	{
			$caps[] = $post_type->cap->read_private_posts;
		}

	}
	
	// Return the capabilities required by the user.
	return $caps;
	
} // mdjm_addon_map_meta_cap
add_filter( 'map_meta_cap', 'mdjm_addon_map_meta_cap', 10, 4 );

/**
 * Save the meta data for the addon
 *
 * @since	1.4
 * @param	int		$post_id		The current event post ID.
 * @param	obj		$post			The current event post object (WP_Post).
 * 
 * @return	void
 */
function mdjm_save_addon_post( $post_id, $post )	{

	if ( ! isset( $_POST['mdjm_addon_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['mdjm_addon_meta_box_nonce'], 'mdjm-addon' ) ) {
		return;
	}
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )	{
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	// The default fields that get saved
	$fields = mdjm_addons_metabox_fields();

	foreach ( $fields as $field )	{

		if ( ! empty( $_POST[ $field ] ) ) {
			$new_value = apply_filters( 'mdjm_addon_metabox_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, $new_value );
		} else {
			delete_post_meta( $post_id, $field );
		}

	}

	do_action( 'mdjm_save_addon', $post_id, $post );

} // mdjm_save_addon_post
add_action( 'save_post_mdjm-addon', 'mdjm_save_addon_post', 10, 2 );

/**
 * Customise the messages associated with managing addon posts
 *
 * @since	1.4
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 *
 */
function mdjm_addon_post_messages( $messages )	{
	
	global $post;
	
	if( 'mdjm-addon' != $post->post_type )	{
		return $messages;
	}
	
	$url1 = '<a href="' . admin_url( 'edit.php?post_type=mdjm-addon' ) . '">';
	$url2 = __( 'Add-on', 'mobile-dj-manager' );
	$url3 = __( 'Add-ons', 'mobile-dj-manager' );
	$url4 = '</a>';
		
	$messages['mdjm-event'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( '%2$s updated. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		4 => sprintf( __( '%2$s updated. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		6 => sprintf( __( '%2$s created. %1$s%3$s List%4$s.' ), $url1, $url2, $url3, $url4 ),
		7 => sprintf( __( '%2$s saved. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 ),
		8 => sprintf( __( '%2$s submitted. %1$s%3$s List%4$s.', 'mobile-dj-manager' ), $url1, $url2, $url3, $url4 )
	);
	
	return apply_filters( 'mdjm_addon_post_messages', $messages );
	
} // mdjm_event_post_messages
add_filter( 'post_updated_messages', 'mdjm_addon_post_messages' );