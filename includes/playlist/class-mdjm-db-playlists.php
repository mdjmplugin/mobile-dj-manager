<?php
/**
 * Playlists DB class
 *
 * This class is for interacting with the playlist database table
 *
 * Largely taken from Easy Digital Downloads.
 *
 * @package     MDJM
 * @subpackage  Classes/DB Playlists
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_DB_Playlists Class
 *
 * @since	1.5
 */
class MDJM_DB_Playlists extends MDJM_DB  {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'mdjm_playlists';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		$db_version = get_option( $this->table_name . '_db_version' );

		/*if ( ! $this->table_exists( $this->table_name ) || version_compare( $db_version, $this->version, '<' ) ) {
			$this->create_table();
		}*/

	} // __construct

	/**
	 * Get columns and formats
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'event_id'     => '%d',
			'artist'       => '%s',
			'song'         => '%s',
			'added_by'     => '%s',
			'category'     => '%s',
			'notes'        => '%s',
			'date_added'   => '%s'
		);
	} // get_columns

	/**
	 * Get default column values
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function get_column_defaults() {
		return array(
			'id'            => 0,
			'event_id'      => '',
			'artist'        => '',
			'song'          => 0,
			'added_by'      => get_current_user_id(),
			'category'      => '',
			'notes'         => '',
			'date_added'    => date( 'Y-m-d H:i:s' ),
			'_uploaded'     => 0,
			'date_uploaded' => ''
		);
	} // get_column_defaults

	/**
	 * Get required columns
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function get_required_fields()	{
		return apply_filters( 'mdjm_db_playlist_required_fields', array(
			'event_id',
			'artist',
			'song'
		) );
	} // get_required_fields

	/**
	 * Add a playlist entry
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function add( $data = array() ) {

		$defaults = $this->get_column_defaults();

		$args = wp_parse_args( $data, $defaults );
		$meta = array();

		foreach( $this->get_required_fields() as $required_field )	{
			if ( empty( $args[ $required_field ] ) ) {
				return false;
			}
		}

		// Check for data that needs to be stored as meta.
		foreach ( $args as $key => $value )	{
			if ( ! array_key_exists( $key, $this->get_columns() ) )	{
				$meta[ $key ] = $value;
				unset( $args[ $key ] );
			}
		}

		$return = $this->insert( $args, 'playlist' );

		if ( $return )	{
			foreach( $meta as $key => $value )	{
				MDJM()->playlist_meta->update_meta( $return, $key, $value );
			}
		}

		return $return;

	} // add

	/**
	 * Delete a playlist entry
	 *
	 * @access	public
	 * @since	1.5
	 * @param	int		$id		The entry ID
	 */
	public function delete( $id = 0 ) {

		if ( empty( $id ) ) {
			return false;
		}

		$entry = $this->get_entry_by( 'id', $id );

		if ( $entry->id > 0 ) {

			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $entry->id ), array( '%d' ) );

		} else {
			return false;
		}

	} // delete

	/**
	 * Checks if an entry exists
	 *
	 * @access	public
	 * @since	1.5
	 * @param	int		$event_id	The event ID to which the entry is associated
	 * @param	mixed	$value		The value to search for
	 * @param	string	$field		The field to search within
	 */
	public function exists( $event_id, $value = '', $field = 'email' ) {

		$columns = $this->get_columns();

		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	} // exists

	/**
	 * Retrieves a single entry from the database
	 *
	 * @access 	public
	 * @since	1.5
	 * @param	string	$id		The entry ID
	 * @return	mixed	Upon success, an object of the playlist entry. Upon failure, NULL
	 */
	public function get_entry_by( $id ) {
		global $wpdb;

		
		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$id = intval( $id );

		if ( $id < 1 ) {
			return false;
		}

		if ( ! $entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %d LIMIT 1", $id ) ) ) {
			return false;
		}

		return $entry;
	} // get_entry_by

	/**
	 * Retrieve entries from the database
	 *
	 * @access	public
	 * @since	1.5
	 * @param	array	$args	Array of arguments to pass the query
	 */
	public function get_entries( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'id'           => 0,
			'event_id'     => 0,
			'added_by'     => 0,
			'song'         => false,
			'category'     => 0,
			'date'         => false,
			'orderby'      => 'song',
			'order'        => 'DESC'
		);

		$args  = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific entries
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// Entries for specific events
		if ( ! empty( $args['event_id'] ) ) {

			if ( is_array( $args['event_id'] ) ) {
				$event_ids = implode( ',', array_map('intval', $args['event_id'] ) );
			} else {
				$event_ids = intval( $args['event_id'] );
			}

			$where .= " AND `event_id` IN( {$event_ids} ) ";

		}

		// Entries from specific users
		if ( ! empty( $args['added_by'] ) ) {

			if ( is_array( $args['added_by'] ) ) {
				$users = implode( ',', array_map('intval', $args['added_by'] ) );
			} else {
				$users = intval( $args['added_by'] );
			}

			$where .= " AND `added_by` IN( {$users} ) ";

		}

		// Specific entries by song name
		if ( ! empty( $args['song'] ) ) {
			$song = sanitize_text_field( $args['song'] );
			$song = trim( $song );
			$where .= $wpdb->prepare( " AND `song` LIKE '%%%%" . '%s' . "%%%%' ", $song ) ;
		}

		// Specific entries by artist
		if ( ! empty( $args['artist'] ) ) {
			$artist = sanitize_text_field( $args['artist'] );
			$artist = trim( $artist );
			$where .= $wpdb->prepare( " AND `artist` LIKE '%%%%" . '%s' . "%%%%' ", $artist ) ;
		}

		// Specific entries by category
		if ( ! empty( $args['category'] ) ) {
			if ( is_array( $args['category'] ) ) {
				$categories = implode( ',', array_map('intval', $args['category'] ) );
			} else {
				$categories = intval( $args['category'] );
			}

			$where .= " AND `category` IN( {$categories} ) ";
		}

		// Entries created on a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_added ) AND $month = MONTH ( date_added ) AND $day = DAY ( date_added )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'mdjm_playlist_' . serialize( $args ) );

		$entries = wp_cache_get( $cache_key, 'playlist' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( false === $entries ) {
			$query = $wpdb->prepare(
				"
					SELECT * FROM
					$this->table_name
					$join
					$where
					GROUP BY $this->primary_key
					ORDER BY {$args['orderby']}
					{$args['order']}
					LIMIT %d,%d;
				",
				absint( $args['offset'] ),
				absint( $args['number'] )
			);
			$entries = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $entries, 'playlist', 3600 );
		}

		return $entries;

	} // get_entries


	/**
	 * Count the total number of playlist entries in the database
	 *
	 * @access	public
	 * @since	1.5
	 * @param	array	$args	Array of arguments to pass the query
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific entries
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// Entries for specific events
		if ( ! empty( $args['event_id'] ) ) {

			if ( is_array( $args['event_id'] ) ) {
				$event_ids = implode( ',', array_map('intval', $args['event_id'] ) );
			} else {
				$event_ids = intval( $args['event_id'] );
			}

			$where .= " AND `event_id` IN( {$event_ids} ) ";

		}

		// Entries from specific users
		if ( ! empty( $args['added_by'] ) ) {

			if ( is_array( $args['added_by'] ) ) {
				$users = implode( ',', array_map('intval', $args['added_by'] ) );
			} else {
				$users = intval( $args['added_by'] );
			}

			$where .= " AND `added_by` IN( {$users} ) ";

		}

		// Specific entries by song name
		if ( ! empty( $args['song'] ) ) {
			$song = sanitize_text_field( $args['song'] );
			$song = trim( $song );
			$where .= $wpdb->prepare( " AND `song` LIKE '%%%%" . '%s' . "%%%%' ", $song ) ;
		}

		// Specific entries by artist
		if ( ! empty( $args['artist'] ) ) {
			$artist = sanitize_text_field( $args['artist'] );
			$artist = trim( $artist );
			$where .= $wpdb->prepare( " AND `artist` LIKE '%%%%" . '%s' . "%%%%' ", $artist ) ;
		}

		// Specific entries by category
		if ( ! empty( $args['category'] ) ) {
			if ( is_array( $args['category'] ) ) {
				$categories = implode( ',', array_map('intval', $args['category'] ) );
			} else {
				$categories = intval( $args['category'] );
			}

			$where .= " AND `category` IN( {$categories} ) ";
		}

		// Entries created on a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_added ) AND $month = MONTH ( date_added ) AND $day = DAY ( date_added )";
			}

		}

		$cache_key = md5( 'mdjm_playlist_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'playlist' );

		if ( 'false' === $count ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'playlist', 3600 );
		}

		return absint( $count );

	} // count

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function create_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		event_id bigint(20) NOT NULL,
		artist mediumtext NOT NULL,
		song mediumtext NOT NULL,
		added_by bigint(20) NOT NULL,
		category bigint(20) NOT NULL,
		notes longtext NOT NULL,
		date_added datetime NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

		if ( $this->table_exists( $this->table_name ) ) {
			update_option( $this->table_name . '_db_version', $this->version );
		}
	} // create_table

} // MDJM_DB_Playlists
