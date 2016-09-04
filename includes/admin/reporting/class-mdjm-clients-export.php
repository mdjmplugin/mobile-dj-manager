<?php
/**
 * Clients Export Class
 *
 * This class handles customer export
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
 * MDJM_Clients_Export Class
 *
 * @since	1.4
 */
class MDJM_Clients_Export extends MDJM_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var		str
	 * @since	1.4
	 */
	public $export_type = 'clients';

	/**
	 * Set the export headers
	 *
	 * @access	public
	 * @since	1.4
	 * @return	void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! mdjm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )	{
			set_time_limit( 0 );
		}

		$extra = '';

		if ( ! empty( $_POST['mdjm_export_event'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['mdjm_export_event'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'mdjm_clients_export_filename', 'mdjm-export-' . $extra . $this->export_type . '-' . date( 'd-m-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	} // headers

	/**
	 * Set the CSV columns
	 *
	 * @access	public
	 * @since	1.4
	 * @return	arr		$cols	All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['mdjm_export_event'] ) ) {
			$cols = array(
				'first_name' => __( 'First Name',   'mobile-dj-manager' ),
				'last_name'  => __( 'Last Name',   'mobile-dj-manager' ),
				'email'      => __( 'Email', 'mobile-dj-manager' ),
				'date'       => sprintf( __( '%s Date', 'mobile-dj-manager' ), mdjm_get_label_singular() )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['mdjm_export_option'] ) {
				$cols['name'] = __( 'Name',   'mobile-dj-manager' );
			}

			$cols['email'] = __( 'Email',   'mobile-dj-manager' );

			if( 'full' == $_POST['mdjm_export_option'] ) {
				$cols['events'] = sprintf( __( 'Total %s',   'mobile-dj-manager' ), mdjm_get_label_plural() );
				$cols['amount']    = __( 'Total Value', 'mobile-dj-manager' ) . ' (' . html_entity_decode( mdjm_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	} // csv_cols

	/**
	 * Get the Export Data
	 *
	 * @access	public
	 * @since	1.4
	 * @global	obj		$wpdb	Used to query the database using the WordPress Database API
	 * @return	arr		$data	The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		// Export all clients
		$clients = mdjm_get_clients();

		$i = 0;

		foreach ( $clients as $client ) {

			if( 'emails' != $_POST['mdjm_export_option'] ) {
				$data[$i]['name'] = $client->name;
			}

			$data[$i]['email'] = $client->email;

			if( 'full' == $_POST['mdjm_export_option'] )	{
				$amount = 0;
				$events = mdjm_get_client_events( $client->ID );
				$data[$i]['events'] = $events ? count( $events ) : 0;

				if ( $events )	{
					foreach ( $events as $event )	{
						$amount += mdjm_get_event_price( $event->ID );
					}
				}

				$data[$i]['amount'] = mdjm_format_amount( $amount );

			}
			$i++;
		}

		$data = apply_filters( 'mdjm_export_get_data', $data );
		$data = apply_filters( 'mdjm_export_get_data_' . $this->export_type, $data );

		return $data;
	} // get_data

} // MDJM_Clients_Export
