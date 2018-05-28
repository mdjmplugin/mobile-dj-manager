<?php
/**
 * Playlist Meta DB class
 *
 * This class is for interacting with the playlist meta database table
 *
 * @package		MDJM
 * @subpackage	Classes/DB_Playlist_Meta
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class MDJM_DB_Playlist_Meta extends MDJM_DB {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.5
	*/
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'mdjm_playlistmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		/*if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}*/

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );

	} // __construct

	/**
	 * Get table columns and data types
	 *
	 * @access	public
	 * @since	1.5
	*/
	public function get_columns() {
		return array(
			'meta_id'           => '%d',
			'playlist_entry_id' => '%d',
			'meta_key'          => '%s',
			'meta_value'        => '%s'
		);
	} // get_columns

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access	public
	 * @since	1.5
	*/
	public function register_table() {
		global $wpdb;
		$wpdb->mdjm_playlistmeta = $this->table_name;
	} // register_table

	/**
	 * Retrieve playlist meta field for an entry.
	 *
	 * For internal use only. Use MDJM_Playlist->get_meta() for public usage.
	 *
	 * @param	int		$id				Playlist Entry ID.
	 * @param	str		$meta_key		The meta key to retrieve.
	 * @param	bool	$single			Whether to return a single value.
	 * @return	mixed	Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function get_meta( $id = 0, $meta_key = '', $single = false ) {
		$entry_id = $this->sanitize_entry_id( $id );
		if ( false === $entry_id ) {
			return false;
		}

		return get_metadata( 'mdjm_playlist', $entry_id, $meta_key, $single );
	} // get_meta

	/**
	 * Add meta data field to a playlist entry.
	 *
	 * For internal use only. Use MDJM_Playlist->add_meta() for public usage.
	 *
	 * @param	int		$id				Entry ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	bool	$unique			Optional, default is false. Whether the same key should not be added.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.5
	 */
	public function add_meta( $id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$id = $this->sanitize_entry_id( $id );
		if ( false === $id ) {
			return false;
		}

		return add_metadata( 'mdjm_playlist', $id, $meta_key, $meta_value, $unique );
	} // add_meta

	/**
	 * Update playist entry meta field based on entry ID.
	 *
	 * For internal use only. Use MDJM_Playlist->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Entry ID.
	 *
	 * If the meta field for the entry does not exist, it will be added.
	 *
	 * @param	int		$id				Playlist entry ID.
	 * @param	str		$meta_key		Metadata key.
	 * @param	mixed	$meta_value		Metadata value.
	 * @param	mixed	$prev_value		Optional. Previous value to check before removing.
	 * @return	bool	False on failure, true if success.
	 *
	 * @access	private
	 * @since	1.5
	 */
	public function update_meta( $id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$id = $this->sanitize_entry_id( $id );
		if ( false === $id ) {
			return false;
		}

		return update_metadata( 'mdjm_playlist', $id, $meta_key, $meta_value, $prev_value );
	} // update_meta

	/**
	 * Remove metadata matching criteria from a playlist entry.
	 *
	 * For internal use only. Use MDJM_Playlist->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param	int		$id				Playlist entry ID.
	 * @param	str		$meta_key		Metadata name.
	 * @param	mixed	$meta_value		Optional. Metadata value.
	 * @return	bool	False for failure. True for success.
	 *
	 * @access	private
	 * @since	1.5
	 */
	public function delete_meta( $id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'mdjm_playlist', $id, $meta_key, $meta_value );
	} // delete_meta

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.5
	*/
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			playlist_entry_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY playlist_entry_id (playlist_entry_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	} // create_table

	/**
	 * Given a playlist entry ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @since	1.5
	 * @param	int|str		$entry_id	A passed playlist entry ID.
	 * @return	int|bool	The normalized customer ID or false if it's found to not be valid.
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

} // MDJM_DB_Playlist_Meta
