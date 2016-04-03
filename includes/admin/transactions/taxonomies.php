<?php
/**
 * Contains all transaction taxonomy functions
 *
 * @package		MDJM
 * @subpackage	Transactions
 * @since		1.3
 */

/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * delete, edit and quick edit options from the hover menu on the edit screen.
 * 
 * @since	1.0
 * @param	arr		$actions		The array of actions in the hover menu
 * 			obj		$tag			The object array for the term
 * @return	arr		$actions		The filtered array of actions in the hover menu
 */
function mdjm_txn_protected_terms_remove_row_actions( $actions, $tag )	{
	
	$protected_terms = mdjm_get_txn_protected_terms();
						
	if ( in_array( $tag->slug, $protected_terms ) ) 
		unset( $actions['delete'], $actions['edit'], $actions['inline hide-if-no-js'], $actions['view'] );
		
	return $actions;
	
} // mdjm_txn_protected_terms_remove_row_actions
add_filter( 'transaction-types_row_actions', 'mdjm_txn_protected_terms_remove_row_actions', 10, 2 );

/**
 * Ensure that built-in terms cannot be deleted by removing the 
 * bulk action checkboxes
 * 
 * @param
 *
 * @return
 */
function mdjm_txn_protected_terms_remove_checkbox()	{
	
	if ( !isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'transaction-types' )	{
		return;
	}
	
	$protected_terms = mdjm_get_txn_protected_terms();
	
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		<?php
		foreach( $protected_terms as $term_slug )	{
			
			$obj_term = get_term_by( 'slug', $term_slug, 'transaction-types' );
			
			if( !empty( $obj_term ) )	{
				?>$('input#cb-select-<?php echo $obj_term->term_id; ?>').prop('disabled', true).hide();<?php
			}
			
		}
		?>
	});
	</script>
	<?php
} // mdjm_txn_protected_terms_remove_checkbox
add_action( 'admin_footer-edit-tags.php', 'mdjm_txn_protected_terms_remove_checkbox' );

/**
 * Retrieve protected (built-in) txn terms.
 *
 * @since	1.3
 * @param
 * @return	arr		$protected_terms	Array of protected terms
 */
function mdjm_get_txn_protected_terms()	{
	
	$other_amount_term = get_term_by( 'name', mdjm_get_option( 'other_amount_label' ), 'transaction-types' );
	
	$protected_terms = array(
		'mdjm-balance-payment',
		'mdjm-deposit-payment',
		'mdjm-employee-wages',
		'mdjm-merchant-fees'
	);
	
	if ( ! empty( $other_amount_term ) )	{
		$protected_terms[] = $other_amount_term->slug;
	}
	
	return apply_filters( 'mdjm_txn_protected_terms', $protected_terms );
	
} // mdjm_get_txn_protected_terms

/**
 * Make the Deposit, Balance and Wages term slugs read-only when editing.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_set_protected_txn_terms_readonly( $tag )	{
	
	$protected_terms = mdjm_get_txn_protected_terms();
	
	if( in_array( $tag->slug, $protected_terms ) )	{
		?>
        <script type="text/javascript">
		jQuery().ready(function($)	{
			$("#slug").attr('readonly','true');
		});
		</script>
        <?php
	}
} // mdjm_set_protected_txn_terms_readonly
add_action( 'transaction-types_edit_form_fields', 'mdjm_set_protected_txn_terms_readonly' );