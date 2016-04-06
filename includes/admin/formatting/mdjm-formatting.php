<?php
/*
 * Remove the Add New button from the post display for specified post types
 * 
 * @params
 * 
 * @return
 */
function mdjm_remove_add_new() {
	if( !isset( $_GET['post_type'] ) )
		return;
	
	/**
	 * Remove the Add New button from the post lists display for all posts within the 
	 * $no_add_new array
	 */
	$no_add_new = array( MDJM_COMM_POSTS, MDJM_QUOTE_POSTS );
	
	if( in_array( $_GET['post_type'], $no_add_new ) )	{
		?>
		<style type="text/css">
			.page-title-action	{
				display: none;	
			}
		</style>
		<?php
	}
} // mdjm_remove_add_new
add_action( 'admin_head', 'mdjm_remove_add_new' );

/**
 * Removes filter from the post lists display for specified post types
 *
 * @params
 *
 * @return
 */
function mdjm_remove_post_filters()	{
	if( !isset( $_GET['post_type'] ) )
		return;
	
	/**
	 * Remove all filters from the venue post lists display for all posts within the 
	 * $no_filter array
	 */
	$no_filter = array( MDJM_VENUE_POSTS );
	
	if( in_array( $_GET['post_type'], $no_filter ) )	{
		?>
		<style type="text/css">
			#posts-filter .tablenav select[name=m],
			#posts-filter .tablenav select[name=cat],
			#posts-filter .tablenav #post-query-submit{
				display:none;
			}
		</style>
		<?php	
	}
} // mdjm_remove_post_filters
add_action( 'admin_head', 'mdjm_remove_post_filters' );

/**
 * Set the post title placeholder for custom post types
 * 
 *
 * @param    str	$title		The post title
 *
 * @return   str	$title		The filtered post title
 */
function mdjm_post_title_placeholder( $title )	{
	global $post;
	
	if( !isset( $post ) )
		return;
	
	switch( get_post_type() )	{
		case MDJM_CONTRACT_POSTS:
			return __( 'Enter Contract name here...', 'mobile-dj-manager' );
		break;
		case MDJM_EMAIL_POSTS:
			return __( 'Enter Template name here. Used as email subject, shortcodes allowed', 'mobile-dj-manager' );
		break;
		case MDJM_VENUE_POSTS:
			return __( 'Enter Venue name here...', 'mobile-dj-manager' );
		break;
		default:
			return $title;
		break;
	}	
} // mdjm_post_title_placeholder
add_filter( 'enter_title_here', 'mdjm_post_title_placeholder' );

/**
 * Sets the name for the publish button for each custom post type
 * 
 * @called 	gettext
 *
 * @params	str		$translation	The current button text translation
 * 			str		$text			The text for the button
 * 
 * @return	str		$translation	The filtererd button text translation
 */
function mdjm_rename_publish_button( $translation, $text )	{
	global $post;
	
	if( ! isset( $post ) )
		return $translation;
	
	switch( get_post_type() )	{
		case MDJM_CONTRACT_POSTS:
			if( $text == 'Publish' )
				return __( 'Save Contract', 'mobile-dj-manager' );
			elseif( $text == 'Update' )
				return __( 'Update Contract', 'mobile-dj-manager' );
			else
				return $translation;
		break;
		
		case MDJM_EMAIL_POSTS:
			if( $text == 'Publish' )
				return __( 'Save Template', 'mobile-dj-manager' );
			elseif( $text == 'Update' )
				return __( 'Update Template', 'mobile-dj-manager' );
			else
				return $translation;
		break;
						
		case MDJM_VENUE_POSTS:
			if( $text == 'Publish' )
				return __( 'Save Venue', 'mobile-dj-manager' );
			elseif( $text == 'Update' )
				return __( 'Update Venue', 'mobile-dj-manager' );
			else
				return $translation;
		break;
		
		default:
			return $translation;
		break;
	}
	
	return $translation;
} // mdjm_rename_publish_button
add_filter( 'gettext', 'mdjm_rename_publish_button', 10, 2 );