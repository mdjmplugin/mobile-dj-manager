<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_TXN_Posts
 * Manage the Transaction posts
 *
 *
 *
 */
if( !class_exists( 'MDJM_TXN_Posts' ) ) :
	class MDJM_TXN_Posts	{
		/**
		 * Initialise
		 */
		public static function init()	{
			add_action( 'manage_mdjm-transaction_posts_custom_column' , array( __CLASS__, 'transaction_posts_custom_column' ), 10, 2 );
			
			add_action( 'admin_footer-edit-tags.php', array( __CLASS__, 'transaction_term_checkboxes' ) );
			
			add_action( 'restrict_manage_posts', array( __CLASS__, 'transaction_post_filter_list' ) ); // Filter dropdown boxes
								
			add_filter( 'manage_mdjm-transaction_posts_columns' , array( __CLASS__, 'transaction_post_columns' ) );
			
			add_filter( 'manage_edit-mdjm-transaction_sortable_columns', array( __CLASS__, 'transaction_post_sortable_columns' ) );
						
			add_filter( 'transaction-types_row_actions', array( __CLASS__, 'transaction_term_row_actions' ), 10, 2 );
		} // init
		
		/**
		 * Define the columns to be displayed for transaction posts
		 *
		 * @params	arr		$columns	Array of column names
		 *
		 * @return	arr		$columns	Filtered array of column names
		 */
		function transaction_post_columns( $columns ) {
			$columns = array(
					'cb'			   => '<input type="checkbox" />',
					'title' 	 		=> __( 'ID', 'mobile-dj-manager' ),
					'txn_date'		 => __( 'Date', 'mobile-dj-manager' ),
					'direction'		=> __( 'In/Out', 'mobile-dj-manager' ),
					'payee'			=> __( 'To/From', 'mobile-dj-manager' ),
					'txn_status'	   => __( 'Status', 'mobile-dj-manager' ),
					'detail'		   => __( 'Details', 'mobile-dj-manager' ),
					'event'			=> __( 'Event', 'mobile-dj-manager' ),
					'txn_value'		=> __( 'Value', 'mobile-dj-manager' ) );
			
			return $columns;
		} // transaction_post_columns
		
		/**
		 * Define which columns are sortable for transaction posts
		 *
		 * @params	arr		$sortable_columns	Array of event post sortable columns
		 *
		 * @return	arr		$sortable_columns	Filtered Array of event post sortable columns
		 */
		function transaction_post_sortable_columns( $sortable_columns )	{
			$sortable_columns['txn_date'] = 'txn_date';
			$sortable_columns['txn_status'] = 'txn_status';
			$sortable_columns['txn_value'] = 'txn_value';
			
			return $sortable_columns;
		} // transaction_post_sortable_columns
		
		/**
		 * Define the data to be displayed in each of the custom columns for the transaction post types
		 *
		 * @param	str		$column_name	The name of the column to display
		 *			int		$post_id		The current post ID
		 * 
		 *
		 */
		function transaction_posts_custom_column( $column_name, $post_id )	{
			global $post;
							
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
					echo ( $post->post_status == 'mdjm-income' ? 
						'<span style="color:green">' . __( 'In', 'mobile-dj-manager' ) . '</span>' : 
						'<span style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;' . __( 'Out', 'mobile-dj-manager' ) . '</span>' );					
					break;
					
				// Source
				case 'payee':
					echo ( $post->post_status == 'mdjm-income' ? 
						get_post_meta( $post_id, '_mdjm_payment_from', true ) : 
						get_post_meta( $post_id, '_mdjm_payment_to', true ) );
					
					break;
						
				// Event
				case 'event':
					echo ( wp_get_post_parent_id( $post_id ) ? 
						'<a href="' . admin_url( '/post.php?post=' . wp_get_post_parent_id( $post_id ) . '&action=edit' ) . '">' . 
						MDJM_EVENT_PREFIX . wp_get_post_parent_id( $post_id ) . '</a>' : 
						'N/A' );					
					break;
				// Value
				case 'txn_value':
					echo display_price( get_post_meta( $post_id, '_mdjm_txn_total', true ) );
					break;
					
				// Status
				case 'txn_status':
					echo get_post_meta( $post_id, '_mdjm_txn_status', true );
				break;
			} // switch( $column_name )
		} // transaction_posts_custom_column
		
		/**
		 * Add the filter dropdowns to the transaction post list
		 *
		 * @params
		 *
		 * @return
		 */
		function transaction_post_filter_list()	{
			if( !isset( $_GET['post_type'] ) || $_GET['post_type'] != MDJM_TRANS_POSTS )
				return;
			
			self::transaction_type_filter_dropdown();
		} // transaction_post_filter_list
		
		/**
		 * Display the filter drop down list to enable user to select and filter transactions by type
		 * 
		 * @params
		 *
		 * @return
		 */
		public function transaction_type_filter_dropdown()	{			
			$transaction_types = get_categories( 
										array(
											'type'			  => MDJM_TRANS_POSTS,
											'taxonomy'		  => 'transaction-types',
											'pad_counts'		=> false,
											'hide_empty'		=> true,
											'orderby'		  => 'name' ) );
											
			foreach( $transaction_types as $transaction_type )	{
				$values[$transaction_type->term_id] = $transaction_type->name;
			}
			?>
			<select name="mdjm_filter_type">
			<option value=""><?php echo __( 'All Transaction Types', 'mobile-dj-manager' ); ?></option>
			<?php
				$current_v = isset( $_GET['mdjm_filter_type'] ) ? $_GET['mdjm_filter_type'] : '';
				
				if( !empty( $values ) )	{
					foreach( $values as $value => $label ) {
						printf(
							'<option value="%s"%s>%s (%s)</option>',
							$value,
							$value == $current_v ? ' selected="selected"' : '',
							$label,
							$label );
					}
				}
			?>
			</select>
			<?php
		} // transaction_type_filter_dropdown
		
		/**
		 * Ensure that built-in terms cannot be deleted by removing the 
		 * delete, edit and quick edit options from the hover menu
		 * 
		 * @param	arr		$actions		The array of actions in the hover menu
		 * 			obj		$tag			The object array for the term
		 * 
		 * @return	arr		$actions		The filtered array of actions in the hover menu
		 */
		function transaction_term_row_actions( $actions, $tag )	{
			$protected_terms = array(
								__( 'Merchant Fees', 'mobile-dj-manager' ),
								MDJM_DEPOSIT_LABEL,
								MDJM_BALANCE_LABEL,
								$GLOBALS['mdjm_settings']['payments']['other_amount_label'] );
								
			if ( in_array( $tag->name, $protected_terms ) ) 
				unset( $actions['delete'], $actions['edit'], $actions['inline hide-if-no-js'], $actions['view'] );
				
			return $actions;
		} // transaction_term_row_actions
		
		/**
		 * Ensure that built-in terms cannot be deleted by removing the 
		 * bulk action checkboxes
		 * 
		 * @param
		 *
		 * @return
		 */
		 function transaction_term_checkboxes()	{
			 if ( !isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'transaction-types' )
				return;
			
			// Create an array with all terms that we should protect from deletion	
			$protected_terms = array(
								__( 'Merchant Fees', 'mobile-dj-manager' ),
								MDJM_DEPOSIT_LABEL,
								MDJM_BALANCE_LABEL,
								$GLOBALS['mdjm_settings']['payments']['other_amount_label'] );
			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				<?php
				foreach( $protected_terms as $term_name )	{
					$obj_term = get_term_by( 'name', $term_name, 'transaction-types' );
					if( !empty( $obj_term ) )	{
						?>
						$('input#cb-select-<?php echo $obj_term->term_id; ?>').prop('disabled', true).hide();
						<?php
					}
				}
				?>
			});
			</script>
			<?php
		 } // transaction_term_checkboxes		
	} // class MDJM_TXN_Posts
endif;
	MDJM_TXN_Posts::init();