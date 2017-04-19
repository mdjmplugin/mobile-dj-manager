<?php
/**
 * Batch Transactions Export Class
 *
 * This class handles transaction exports
 *
 * @package     MDJM
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Batch_Export_Txns Class
 *
 * @since	1.4
 */
class MDJM_Batch_Export_Txns extends MDJM_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var		str
	 * @since	1.4
	 */
	public $export_type = 'transactions';

	/**
	 * Set the CSV columns
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$cols	All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        => __( 'ID', 'mobile-dj-manager' ),
			'date'      => __( 'Date', 'mobile-dj-manager' ),
			'status'    => __( 'Status', 'mobile-dj-manager' ),
			'income'    => __( 'Income', 'mobile-dj-manager' ),
			'expense'   => __( 'Expense', 'mobile-dj-manager' ),
			'to_from'   => __( 'To / From', 'mobile-dj-manager' ),
			'type'      => __( 'Type', 'mobile-dj-manager' ),
			'event'     => mdjm_get_label_singular()
		);

		return $cols;
	} // csv_cols

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$data	The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		// Export all transactions
		$offset = 30 * ( $this->step - 1 );

		$txn_args = array(
			'post_type'      => 'mdjm-transaction',
			'posts_per_page' => 30,
			'offset'         => $offset,
			'paged'          => $this->step,
			'post_status'    => array( 'mdjm-income', 'mdjm-expenditure' ),
			'order'          => 'ASC',
			'orderby'        => 'date'
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) )	{

			$txn_args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);

		}

		if ( ! empty( $this->status ) && is_array( $this->status ) )	{
			$meta_query = array();
		
			foreach( $this->status as $txn_status )	{
				$meta_query[] = array(
					'key'   => '_mdjm_txn_status',
					'value' => $txn_status
				);
			}
			$txn_args['meta_query'] = array(
				'relation' => 'OR',
				$meta_query
			);
		}

		$all_txns = get_posts( $txn_args );

		if ( $all_txns )	{
	
			$i       = 0;
			$income  = 0;
			$expense = 0;

			foreach ( $all_txns as $txn ) {
	
				$mdjm_txn = new MDJM_Txn( $txn->ID );
	
				$data[ $i ]['id']      = $mdjm_txn->ID;
				$data[ $i ]['date']    = date( 'd-M-Y', strtotime( $mdjm_txn->post_date ) );
				$data[ $i ]['status']  = $mdjm_txn->payment_status;
				$data[ $i ]['income']  = 'mdjm-income' == $mdjm_txn->post_status ? mdjm_format_amount( $mdjm_txn->price ) : '';
				$data[ $i ]['expense'] = 'mdjm-expenditure' == $mdjm_txn->post_status ? mdjm_format_amount( $mdjm_txn->price ) : '';
				$data[ $i ]['to_from'] = mdjm_get_txn_recipient_name( $mdjm_txn->ID );
				$data[ $i ]['type']    = $mdjm_txn->get_type();
				$data[ $i ]['source']  = $mdjm_txn->get_method();
				$data[ $i ]['gateway'] = $mdjm_txn->get_gateway();
				$data[ $i ]['event']   = ! empty( $mdjm_txn->post_parent ) ? mdjm_get_event_contract_id( $mdjm_txn->post_parent ) : '';

				if ( 'mdjm-income' == $mdjm_txn->post_status )	{
					$income  += $mdjm_txn->price;
				} else	{
					$expense += $mdjm_txn->price;
				}

				$i++;

			}
	
			$data = apply_filters( 'mdjm_export_get_data', $data );
			$data = apply_filters( 'mdjm_export_get_data_' . $this->export_type, $data );
	
			return $data;

		}

		return false;
	} // get_data

	/**
	 * Set the count args.
	 *
	 * @since	1.4
	 * @param	arr		$args	The args for the count query
	 * @return	arr		$args	The args for the count query
	 */
	 function filter_count_args( $args )	{

		 if ( ! empty( $this->start ) || ! empty( $this->end ) )	{

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);

		}

		if ( ! empty( $this->status ) && is_array( $this->status ) )	{
			$meta_query = array();
		
			foreach( $this->status as $txn_status )	{
				$meta_query[] = array(
					'key'   => '_mdjm_txn_status',
					'value' => $txn_status
				);
			}
			$args['meta_query'] = array(
				'relation' => 'OR',
				$meta_query
			);
		}

		return $args;

	 } // filter_count_args

	/**
	 * Return the calculated completion percentage
	 *
	 * @since	1.4
	 * @return	int
	 */
	public function get_percentage_complete() {

		add_filter( 'mdjm_txn_count_args', array( $this, 'filter_count_args' ) );
		$total = mdjm_txn_count();
		remove_filter( 'mdjm_txn_count_args', array( $this, 'filter_count_args' ) );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	} // get_percentage_complete

	/**
	 * Set the properties specific to the Clients export
	 *
	 * @since	1.4
	 * @param	arr		$request	The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start  = isset( $request['txn_start'] )    ? sanitize_text_field( $request['txn_start'] )  : '';
		$this->end    = isset( $request['txn_end'] )      ? sanitize_text_field( $request['txn_end'] )    : '';
		$this->status = ! empty( $request['txn_status'] ) ? array( sanitize_text_field( $request['txn_status'] ) ) : false;
	} // set_properties

} // MDJM_Batch_Export_Txns
