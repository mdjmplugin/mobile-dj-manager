<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Manage the transaction posts
 *
 *
 *
 */
		
/**
 * Define the columns to be displayed for transaction posts
 *
 * @since	0.5
 * @param	arr		$columns	Array of column names
 * @return	arr		$columns	Filtered array of column names
 */
function mdjm_transaction_post_columns( $columns ) {
		
	$columns = array(
		'cb'			   => '<input type="checkbox" />',
		'title' 	 		=> __( 'ID', 'mobile-dj-manager' ),
		'txn_date'		 => __( 'Date', 'mobile-dj-manager' ),
		'direction'		=> __( 'In/Out', 'mobile-dj-manager' ),
		'payee'			=> __( 'To/From', 'mobile-dj-manager' ),
		'txn_status'	   => __( 'Status', 'mobile-dj-manager' ),
		'detail'		   => __( 'Details', 'mobile-dj-manager' ),
		'event'			=> __( 'Event', 'mobile-dj-manager' ),
		'txn_value'		=> __( 'Value', 'mobile-dj-manager' )
	);
	
	if( ! mdjm_employee_can( 'edit_txns' ) && isset( $columns['cb'] ) )	{
		unset( $columns['cb'] );
	}
				
	return $columns;
} // mdjm_event_post_columns
add_filter( 'manage_mdjm-transaction_posts_columns' , 'mdjm_transaction_post_columns' );

/**
 * Define which columns are sortable for transaction posts
 *
 * @since	0.7
 * @param	arr		$sortable_columns	Array of transaction post sortable columns
 * @return	arr		$sortable_columns	Filtered Array of transaction post sortable columns
 */
function mdjm_transaction_post_sortable_columns( $sortable_columns )	{
	
	$sortable_columns['txn_date']	  = 'txn_date';
	$sortable_columns['txn_status']	= 'txn_status';
	$sortable_columns['txn_value']	 = 'txn_value';
	
	return $sortable_columns;
	
} // mdjm_transaction_post_sortable_columns
add_filter( 'manage_edit-mdjm-transaction_sortable_columns', 'mdjm_transaction_post_sortable_columns' );

/**
 * Define the data to be displayed in each of the custom columns for the Transaction post types
 *
 * @since	0.9
 * @param	str		$column_name	The name of the column to display
 * @param	int		$post_id		The current post ID
 * @return
 */
function mdjm_transaction_posts_custom_column( $column_name, $post_id )	{
	
	if( mdjm_employee_can( 'edit_txns' ) && ( $column_name == 'value' || $column_name == 'balance' ) )	{
		$value = get_post_meta( $post_id, '_mdjm_event_cost', true );
	}
		
	switch( $column_name ) {	
		// Details
		case 'detail':
			$trans_types = get_the_terms( $post_id, 'transaction-types' );
			
			if( is_array( $trans_types ) )	{
				foreach( $trans_types as $key => $trans_type ) {
					$trans_types[$key] = $trans_type->name;
				}
				echo implode( "<br/>", $trans_types );
			}
			break;
			
		// Date
		case 'txn_date':
			echo get_post_time( 'd M Y' );					
			break;
			
		// Direction
		case 'direction':
			if( 'mdjm-income' == get_post_status( $post_id ) )	{
				
				echo '<span style="color:green">' . __( 'In', 'mobile-dj-manager' ) . '</span>';
				
			} else	{
				
				echo '<span style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;' . __( 'Out', 'mobile-dj-manager' ) . '</span>';
				
			}
			break;
			
		// Source
		case 'payee':
			echo get_post_status( $post_id ) == 'mdjm-income' ? get_post_meta( $post_id, '_mdjm_payment_from', true ) : get_post_meta( $post_id, '_mdjm_payment_to', true );
			
			break;
				
		// Event
		case 'event':
			$parent = wp_get_post_parent_id( $post_id );
			
			if( ! empty( $parent ) )	{
				
				printf( '<a href="%s">%s</a>', 
					admin_url( "/post.php?post={$parent}&action=edit" ),
					mdjm_get_option( '' ) . $parent
				);
				
			} else	{
				
				echo __( 'N/A', 'mobile-dj-manager' );
				
			}				
			break;
			
		// Value
		case 'txn_value':
			echo mdjm_currency_filter( mdjm_sanitize_amount( get_post_meta( $post_id, '_mdjm_txn_total', true ) ) );
			break;
			
		// Status
		case 'txn_status':
			echo get_post_meta( $post_id, '_mdjm_txn_status', true );
			break;
	} // switch
	
} // mdjm_transaction_posts_custom_column
add_action( 'manage_mdjm-transaction_posts_custom_column' , 'mdjm_transaction_posts_custom_column', 10, 2 );

/**
 * Add the dropdown filters for the transaction post categories.
 *
 * @since	1.0
 * @param
 * @return	void
 */
function mdjm_transaction_type_filter_dropdown()	{
	
	if( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != 'mdjm-transaction' )	{
		return;
	}
	
	$transaction_types = get_categories( 
		array(
			'type'		  => 'mdjm-transaction',
			'taxonomy'	  => 'transaction-types',
			'pad_counts'	=> false,
			'hide_empty'	=> true,
			'orderby'	   => 'name'
		)
	);
									
	foreach( $transaction_types as $transaction_type )	{
		$values[ $transaction_type->term_id ] = $transaction_type->name;
	}
	
	?>
	<select name="mdjm_filter_type">
	<option value="0"><?php echo __( 'All Transaction Types', 'mobile-dj-manager' ); ?></option>
	
	<?php
		$current_value = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
		
		if( !empty( $values ) )	{
			
			foreach( $values as $value => $label )	{
				
				printf( '<option value="%s"%s>%3$s (%3$s)</option>', $value, $value == $current_value ? ' selected="selected"' : '', $label );

			}
			
		}
	?>
    
	</select>
	<?php
	
} // mdjm_transaction_type_filter_dropdown
add_action( 'restrict_manage_posts', 'mdjm_transaction_type_filter_dropdown' );

/**
 * Customise the messages associated with managing transaction posts
 *
 * @since	1.3
 * @param	arr		$messages	The current messages
 * @return	arr		$messages	Filtered messages
 *
 */
function mdjm_transaction_post_messages( $messages )	{
	
	global $post;
	
	if( 'mdjm-transaction' != get_post_type( $post->ID ) )	{
		return;
	}
	
	$messages = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __( 'Transaction updated.', 'mobile-dj-manager' ),
		2 => __( 'Custom field updated.' ),
		3 => __( 'Custom field deleted.' ),
		4 => __( 'Transaction updated.', 'mobile-dj-manager' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Transaction restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => __( 'Transaction updated.', 'mobile-dj-manager' ),
		7 => __( 'Transaction saved.', 'mobile-dj-manager' ),
		8 => __( 'Transaction submitted.', 'mobile-dj-manager' ),
		9 => __( 'Transaction scheduled.' ),
		10 => __( 'Transaction draft updated.', 'mobile-dj-manager' )
	);
	
	return apply_filters( 'mdjm_transaction_post_messages', $messages );
	
} // mdjm_transaction_post_messages
add_filter( 'post_updated_messages','mdjm_transaction_post_messages' );