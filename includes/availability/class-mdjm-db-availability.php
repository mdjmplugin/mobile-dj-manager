<?php
/**
 * Availability DB class
 *
 * This class is for interacting with the availability database table
 *
 * Largely taken from Easy Digital Downloads.
 *
 * @package     MDJM
 * @subpackage  Classes/DB Availability
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since	1.5.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_DB_Availability Class
 *
 * @since	1.5.5
 */
class MDJM_DB_Availability extends MDJM_DB  {

	/**
	 * Get things started
	 *
	 * @access	public
	 * @since	1.5.5
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'mdjm_availability';
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
	 * @since	1.5.5
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'employee_id'  => '%d',
			'group_id'     => '%s',
			'from_date'    => '%s',
			'to_date'      => '%s',
			'notes'        => '%s',
			'date_added'   => '%s',
			'added_by'     => '%d'
		);
	} // get_columns

	/**
	 * Get default column values
	 *
	 * @access	public
	 * @since	1.5.5
	 */
	public function get_column_defaults() {
		return array(
			'id'            => 0,
			'employee_id'   => get_current_user_id(),
			'group_id'      => '',
			'from_date'     => '',
			'to_date'       => '',
			'notes'         => '',
			'date_added'    => date( 'Y-m-d H:i:s' ),
			'added_by'      => get_current_user_id()
		);
	} // get_column_defaults

	/**
	 * Get required columns
	 *
	 * @access	public
	 * @since	1.5.5
	 */
	public function get_required_fields()	{
		return apply_filters( 'mdjm_db_availability_required_fields', array(
			'employee_id',
			'group_id',
			'from_date',
			'to_date'
		) );
	} // get_required_fields

	/**
	 * Add a playlist entry
	 *
	 * @access	public
	 * @since	1.5.5
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

		$return = $this->insert( $args, 'availability' );

		if ( $return )	{
			foreach( $meta as $key => $value )	{
				MDJM()->availability_meta->update_meta( $return, $key, $value );
			}
		}

		return $return;

	} // add

	/**
	 * Delete an availability entry
	 *
	 * @access	public
	 * @since	1.5.5
	 * @param	int|string		$id_or_group	The ID or group ID
	 */
	public function delete( $id_or_group = 0 ) {

		if ( empty( $id_or_group ) ) {
			return false;
		}

		$column = ( 32 == strlen( $id_or_group ) && ctype_xdigit( $id_or_group ) ) ? 'group_id' : 'id';
		$entry  = $this->get_entry_by( $column, $id_or_group );
        $format = 'group_id' == $column ? array( '%s' ) : array( '%d' );
        $return = false;

		if ( $entry->id > 0 ) {
			global $wpdb;

			$return = $wpdb->delete( $this->table_name, array( $column => $id_or_group ), $format );
		}

        return $return;
	} // delete

	/**
	 * Retrieves a single entry from the database
	 *
	 * @access 	public
	 * @since	1.5.5
	 * @param	string	$field		The field to get the entry by
	 * @param	mixed	$value		The value to search
	 * @return	mixed	Upon success, an object of the playlist entry. Upon failure, NULL
	 */
	public function get_entry_by( $field = 'id', $value = 0 ) {
		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
		}

		if ( 'id' == $field || 'employee_id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'group_id' === $field ) {

			if ( 32 != strlen( $id_or_group ) && ctype_xdigit( $id_or_group ) ) {
				return false;
			}

			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'employee_id':
				$db_field = 'employee_id';
				break;
			case 'group_id':
				$value    = sanitize_text_field( $value );
				$db_field = 'group_id';
				break;
			default:
				return false;
		}

		$query = $wpdb->prepare(
			"
				SELECT * FROM
				$this->table_name
				WHERE $db_field = %s
				LIMIT 1
			", $value
		);
		if ( ! $entry = $wpdb->get_row( $query ) ) {
			return false;
		}

		return $entry;
	} // get_entry_by

	/**
	 * Retrieve entries from the database
	 *
	 * @access	public
	 * @since	1.5.5
	 * @param	array	$args	Array of arguments to pass the query
	 */
	public function get_entries( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'id'           => 0,
			'group_id'     => 0,
			'employee_id'  => 0,
			'from_date'    => false,
			'to_date'      => false,
			'orderby'      => 'id',
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

		// Entries for a specific group
		if ( ! empty( $args['group_id'] ) ) {

			if ( is_array( $args['group_id'] ) ) {
				$group_ids = implode( ',', array_map( 'sanitize_text_field', $args['group_id'] ) );
			} else {
				$group_ids = intval( $args['group_id'] );
			}

			$where .= " AND `group_id` IN( {$group_ids} ) ";

		}

		// Entries for specific employees
		if ( ! empty( $args['employee_id'] ) ) {

			if ( is_array( $args['employee_id'] ) ) {
				$employee_ids = implode( ',', array_map( 'intval', $args['employee_id'] ) );
			} else {
				$employee_ids = intval( $args['employee_id'] );
			}

			$where .= " AND `employee_id` IN( {$employee_ids} ) ";

		}

		// Specific entries by from date
		if ( ! empty( $args['from_date'] ) ) {
			// TODO
		}

		// Specific entries by artist
		if ( ! empty( $args['to_date'] ) ) {
			// TODO
		}

		// Entries starting on a specific date or in a date range
		if ( ! empty( $args['from_date'] ) ) {

			if ( ! empty( $args['to_date'] ) ) {

				$from   = date( 'Y-m-d', strtotime( $args['from_date'] ) );
				$to     = date( 'Y-m-d', strtotime( $args['to_date'] ) );
				$where .= " AND `from_date` >= '{$from}'";
				$where .= " AND `to_date` >= '{$to}'";

			} else {

				$year  = date( 'Y', strtotime( $args['from_date'] ) );
				$month = date( 'm', strtotime( $args['from_date'] ) );
				$day   = date( 'd', strtotime( $args['from_date'] ) );

				$where .= " AND $year = YEAR ( from_date ) AND $month = MONTH ( from_date ) AND $day = DAY ( from_date )";
			}

		}

		// Entries ending on a specific date
		if ( ! empty( $args['to_date'] ) ) {

			$year  = date( 'Y', strtotime( $args['to_date'] ) );
			$month = date( 'm', strtotime( $args['to_date'] ) );
			$day   = date( 'd', strtotime( $args['to_date'] ) );

			$where .= " AND $year = YEAR ( to_date ) AND $month = MONTH ( to_date ) AND $day = DAY ( to_date )";
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		$cache_key = md5( 'mdjm_availability_' . serialize( $args ) );

		$entries = wp_cache_get( $cache_key, 'availability' );

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
			wp_cache_set( $cache_key, $entries, 'availability', 3600 );
		}

		return $entries;

	} // get_entries

	/**
	 * Create the table
	 *
	 * @access	public
	 * @since	1.5.5
	 */
	public function create_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		employee_id bigint(20) NOT NULL,
		group_id varchar(50) NOT NULL,
		to_date date NOT NULL default '0000-00-00',
		from_date date NOT NULL default '0000-00-00',
		notes longtext NULL,
		date_added datetime NOT NULL default '0000-00-00 00:00:00',
		added_by bigint(20) NOT NULL,
		PRIMARY KEY  (id),
		KEY employee_id (employee_id),
		KEY group_id (group_id),
		KEY to_date (to_date),
		KEY from_date (from_date)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		if ( $this->table_exists( $this->table_name ) ) {
			update_option( $this->table_name . '_db_version', $this->version );
		}
	} // create_table

} // MDJM_DB_Availability
