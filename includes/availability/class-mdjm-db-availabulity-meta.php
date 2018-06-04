<?php
/**
 * Availability Meta DB class
 *
 * This class is for interacting with the availability meta database table
 *
 * @package		MDJM
 * @subpackage	Classes/MDJM_DB_Availability_Meta
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class MDJM_DB_Availability_Meta extends MDJM_DB {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.5.6
	*/
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'mdjm_availabilitymeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

	} // __construct

	/**
	 * Get table columns and data types
	 *
	 * @access	public
	 * @since	1.5.6
	*/
	public function get_columns() {
		return array(
			'meta_id'    => '%d',
			'entry_id'   => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	} // get_columns

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access	public
	 * @since	1.5.6
	*/
	public function register_table() {
		global $wpdb;
		$wpdb->mdjm_availabilitymeta = $this->table_name;
	} // register_table

	/**
	 * Retrieve availability meta field for an entry.
	 *
	 * For internal use only. Use MDJM_Availabiility->get_meta() for public usage.
	 *
	 * @param	int		$id				Entry ID.
	 * @param	str		$meta_key		The meta key to retrieve.
	 * @param	bool	$single			Whether to return a single value.
	 * @return	mixed	Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access	public
	 * @since	1.5.6
	 */
	public function get_meta( $id = 0, $meta_key = '', $single = false ) {
		$id = $this->sanitize_entry_id( $id );
		if ( false === $id ) {
			return false;
		}

		return get_metadata( 'mdjm_availability', $id, $meta_key, $single );
	} // get_meta

	/**
	 * Add meta data field to an entry.
	 *
	 * For internal use only. Use MDJM_DB_Availability->add_meta() for public usage.
	 *
	 * @param	int		$id				Entry ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	bool	$unique			Optional, default is false. Whether the same key should not be added.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.5.6
	 */
	public function add_meta( $id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$id = $this->sanitize_entry_id( $id );
		if ( false === $id ) {
			return false;
		}

		return add_metadata( 'mdjm_availability', $id, $meta_key, $meta_value, $unique );
	} // add_meta

	/**
	 * Update availability meta field based on Entry ID.
	 *
	 * For internal use only. Use MDJM_DB_Availability->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Entry ID.
	 *
	 * If the meta field for the entry does not exist, it will be added.
	 *
	 * @param	int		$id				Entry ID.
	 * @param	str		$meta_key		Metadata key.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	mixed	$prev_value		Optional. Previous value to check before removing.
	 * @return	bool	False on failure, true if success.
	 *
	 * @access	private
	 * @since	1.5.6
	 */
	public function update_meta( $id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$id = $this->sanitize_entry_id( $id );
		if ( false === $id ) {
			return false;
		}

		return update_metadata( 'mdjm_availability', $id, $meta_key, $meta_value, $prev_value );
	} // update_meta

	/**
	 * Remove metadata matching criteria from an entry.
	 *
	 * For internal use only. Use MDJM_DB_Availability->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param	int		$id				Entry ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Optional. Metadata value.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.5.6
	 */
	public function delete_meta( $id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'mdjm_availability', $id, $meta_key, $meta_value );
	} // delete_meta

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.5.6
	*/
	public function create_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			entry_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY entry_id (entry_id),
			KEY meta_key (meta_key)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	} // create_table

	/**
	 * Given an entry ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since	1.5.6
	 * @param	int|string	$entry_id	A passed entry ID.
	 * @return	int|bool	The normalized entry ID or false if it's found to not be valid.
	 */
	private function sanitize_entry_id( $entry_id ) {
		if ( ! is_numeric( $entry_id ) ) {
			return false;
		}

		$entry_id = (int) $entry_id;

		// We were given a non positive number
		if ( absint( $entry_id ) !== $entry_id ) {
			return false;
		}

		if ( empty( $entry_id ) ) {
			return false;
		}

		return absint( $entry_id );

	} // sanitize_entry_id

} // MDJM_DB_Availability_Meta
