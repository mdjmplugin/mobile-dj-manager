<?php

/**
 * Add an option field to set the default event type when adding a new type.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_event_type_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="event_type_default"><?php printf( __( 'Set as Default %s type?', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label>
        <input type="checkbox" name="event_type_default" id="event_type_default" value="1" />
    </div>
    <?php
	
} // mdjm_add_event_category_fields
add_action( 'event-types_add_form_fields', 'mdjm_add_event_type_fields' );

/**
 * Add an option field to set the default event type when editing a type.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_event_type_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="event_type_default"><?php printf( __( 'Set as Default %s type?', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label></th>
        <td><input type="checkbox" id="event_type_default" name="event_type_default" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'event_type_default' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_edit_event_category_fields
add_action( 'event-types_edit_form_fields', 'mdjm_edit_event_type_fields' );

/**
 * Fires when an event type is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_event_type( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['event_type_default'] ) )	{
	
		mdjm_update_option( 'event_type_default', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'event_type_default' ) == $term_id )	{
			
			mdjm_delete_option( 'event_type_default' );
			
		}
		
	}
	
} // mdjm_save_playlist_category
add_action( 'create_event-types', 'mdjm_save_event_type', 10, 2 );
add_action( 'edited_event-types', 'mdjm_save_event_type', 10, 2 );

/**
 * Add an option field to set the default enquiry source when adding a new source.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_enquiry_source_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="enquiry_source_default"><?php _e( 'Set as Default Enquiry Source?', 'mobile-dj-manager' ); ?></label>
        <input type="checkbox" name="enquiry_source_default" id="enquiry_source_default" value="1" />
    </div>
    <?php
	
} // mdjm_add_enquiry_source_fields
add_action( 'enquiry-source_add_form_fields', 'mdjm_add_enquiry_source_fields' );

/**
 * Add an option field to set the default enquiry source when editing a new source.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_enquiry_source_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="enquiry_source_default"><?php  _e( 'Set as Default Enquiry Source?', 'mobile-dj-manager' ); ?></label></th>
        <td><input type="checkbox" id="enquiry_source_default" name="enquiry_source_default" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'enquiry_source_default' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_edit_enquiry_source_fields
add_action( 'enquiry-source_edit_form_fields', 'mdjm_edit_enquiry_source_fields' );

/**
 * Fires when an event type is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_enquiry_source( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['enquiry_source_default'] ) )	{
	
		mdjm_update_option( 'enquiry_source_default', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'enquiry_source_default' ) == $term_id )	{
			
			mdjm_delete_option( 'enquiry_source_default' );
			
		}
		
	}
	
} // mdjm_save_enquiry_source
add_action( 'create_enquiry-source', 'mdjm_save_enquiry_source', 10, 2 );
add_action( 'edited_enquiry-source', 'mdjm_save_enquiry_source', 10, 2 );

/**
 * Add an option field to set the default category when adding a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_playlist_category_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="playlist_default_cat"><?php _e( 'Set as default Category?', 'mobile-dj-manager' ); ?></label>
        <input type="checkbox" name="playlist_default_cat" id="playlist_default_cat" value="<?php echo $tag->term_id; ?>" />
    </div>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_add_form_fields', 'mdjm_add_playlist_category_fields' );

/**
 * Add an option field to set the default category when editing a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_playlist_category_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="playlist_default_cat"><?php _e( 'Set as Default Category?', 'mobile-dj-manager' ); ?></label></th>
        <td><input type="checkbox" id="playlist_default_cat" name="playlist_default_cat" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'playlist_default_cat' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_edit_form_fields', 'mdjm_edit_playlist_category_fields' );

/**
 * Fires when a playlist category is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_playlist_category( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['playlist_default_cat'] ) )	{
	
		mdjm_update_option( 'playlist_default_cat', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'playlist_default_cat' ) == $term_id )	{
			
			mdjm_delete_option( 'playlist_default_cat' );
			
		}
		
	}
	
} // mdjm_save_playlist_category
add_action( 'create_playlist-category', 'mdjm_save_playlist_category', 10, 2 );
add_action( 'edited_playlist-category', 'mdjm_save_playlist_category', 10, 2 );

/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * delete, edit and quick edit options from the hover menu on the edit screen.
 * 
 * @since	1.0
 * @param	arr		$actions		The array of actions in the hover menu
 * 			obj		$tag			The object array for the term
 * @return	arr		$actions		The filtered array of actions in the hover menu
 */
function mdjm_guest_playlist_term_remove_row_actions( $actions, $tag )	{
							
	if ( $tag->slug == 'mdjm-playlist-guest' ) 
		unset( $actions['delete'], $actions['edit'], $actions['inline hide-if-no-js'], $actions['view'] );
		
	return $actions;
	
} // mdjm_guest_playlist_term_remove_row_actions
add_filter( 'playlist-category_row_actions', 'mdjm_guest_playlist_term_remove_row_actions', 10, 2 );

/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * bulk action checkboxes
 * 
 * @param
 *
 * @return
 */
function mdjm_guest_playlist_term_remove_checkbox()	{
	
	if ( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'playlist-category' )	{
		return;
	}
	
	$protected_terms = array( 'mdjm-playlist-guest' );
	
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php
		foreach( $protected_terms as $term_slug )	{
			
			$obj_term = get_term_by( 'slug', $term_slug, 'playlist-category' );
			
			if( !empty( $obj_term ) )	{
				?>$('input#cb-select-<?php echo $obj_term->term_id; ?>').prop('disabled', true).hide();<?php
			}
			
		}
		?>
	});
	</script>
	<?php
} // mdjm_guest_playlist_term_remove_checkbox
add_action( 'admin_footer-edit-tags.php', 'mdjm_guest_playlist_term_remove_checkbox' );

/**
 * Make the Guest term slug read-only when editing.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_set_guest_playlist_term_readonly( $tag )	{
		
	if( $tag->slug == 'mdjm-playlist-guest' )	{
		?>
        <script type="text/javascript">
		jQuery().ready(function($)	{
			$("#slug").attr('readonly','true');
		});
		</script>
        <?php
	}
} // mdjm_set_guest_playlist_term_readonly
add_action( 'playlist-category_edit_form_fields', 'mdjm_set_guest_playlist_term_readonly' );
