<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the Email Template posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for Email Template posts
 *
 * @params	arr		$columns	Array of column names
 *
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_email_template_post_columns( $columns ) {
	$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'title'		=> __( 'Email Subject', 'mobile-dj-manager' ),
			'author'	=> __( 'Created By', 'mobile-dj-manager' ),
			'date'		=> __( 'Date', 'mobile-dj-manager' ) );
	
	return $columns;
} // mdjm_email_template_post_columns
add_filter( 'manage_email_template_posts_columns' , 'mdjm_email_template_post_columns' );
?>