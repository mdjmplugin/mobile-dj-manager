<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the email template posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for email template posts
 *
 * @since	0.5
 * @param	arr		$columns	Array of column names
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_email_template_post_columns( $columns ) {
		
	$columns = array(
		'cb'		=> '<input type="checkbox" />',
		'title'		=> __( 'Email Subject', 'mobile-dj-manager' ),
		'author'	=> __( 'Created By', 'mobile-dj-manager' ),
		'date'		=> __( 'Date', 'mobile-dj-manager' )
	);
			
	if( ! mdjm_employee_can( 'manage_templates' ) && isset( $columns['cb'] ) )	{
		unset( $columns['cb'] );
	}
				
	return $columns;
} // mdjm_email_template_post_columns
add_filter( 'manage_email_template_posts_columns' , 'mdjm_email_template_post_columns' );

/**
 * Customise the post row actions on the email template edit screen.
 *
 * @since	1.0
 * @param	arr		$actions	Current post row actions
 * @param	obj		$post		The WP_Post post object
 */
function mdjm_email_template_post_row_actions( $actions, $post )	{
	
	if( $post->post_type != 'email_template' )	{
		return $actions;
	}
	
	if( isset( $actions['inline hide-if-no-js'] ) )	{
		unset( $actions['inline hide-if-no-js'] );
	}
	
	return $actions = array();
	
} // mdjm_email_template_post_row_actions
add_filter( 'post_row_actions', 'mdjm_email_template_post_row_actions', 10, 2 );

/**
 * Set the post title placeholder for email templates
 * 
 * @since	1.3
 * @param	str		$title		The post title
 * @return  str		$title		The filtered post title
 */
function mdjm_email_template_title_placeholder( $title )	{
	global $post;
	
	if( !isset( $post ) || 'email_template' != $post->post_type )	{
		return $title;
	}
	
	return __( 'Enter Template name here. Used as email subject, shortcodes allowed', 'mobile-dj-manager' );

} // mdjm_email_template_title_placeholder
add_filter( 'enter_title_here', 'mdjm_email_template_title_placeholder' );

/**
 * Customise the messages associated with managing email template posts
 *
 * @since	1.3
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 */
function mdjm_email_template_post_messages( $messages )	{
	
	global $post;
	
	if( 'email_template' != get_post_type( $post->ID ) )	{
		return;
	}
	
	$messages = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( '%s updated.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		2 => __( 'Custom field updated.' ),
		3 => __( 'Custom field deleted.' ),
		4 => sprintf( __( '%s updated.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Transaction restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( '%s updated.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		7 => sprintf( __( '%s saved.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		8 => sprintf( __( '%s submitted.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		9 => sprintf( __( '%s scheduled.' ), get_post_type_object( $post->post_type )->labels->singular_name ),
		10 => sprintf( __( '%s draft updated.', 'mobile-dj-manager' ), get_post_type_object( $post->post_type )->labels->singular_name )
	);
	
	return apply_filters( 'mdjm_email_template_post_messages', $messages );
	
} // mdjm_email_template_post_messages
add_filter( 'post_updated_messages','mdjm_email_template_post_messages' );