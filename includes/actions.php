<?php
/**
 * Front-end Actions
 *
 * @package     MDJM
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

function test_mdjm_av_upgrade()	{
	global $wpdb;

	$table   = $wpdb->prefix . 'mdjm_avail';
	$count   = 0;
	$entries = $wpdb->get_results( 
		"
		SELECT user_id, entry_id 
		FROM $table
		GROUP BY entry_id
		"
	);

	$group_ids = get_transient( 'mdjm_availability_db_migrate' );

	if ( false === $group_ids )	{
		$group_ids = array();
	}

	foreach( $entries as $entry )	{
		$group_ids[ $entry->entry_id ] = md5( $entry->user_id . '_' . mdjm_generate_random_string() );
	}

	set_transient( 'mdjm_availability_db_migrate', $group_ids, WEEK_IN_SECONDS );

	$entries = get_transient( 'mdjm_availability_db_migrate' );
	if ( false !== $entries )	{
		$count = count( $entries );
	}

	if ( $count > 0 )	{
		foreach( $entries as $old_key => $new_key )	{
			$data    = array();
			$results = $wpdb->get_results( $wpdb->prepare(
				"
				SELECT *
				FROM $table
				WHERE entry_id = '%s'
				",
				$old_key
			) );

			foreach( $results as $result )	{
				$data['employee_id'] = $result->user_id;
				$data['group_id']    = $new_key;
				$data['from_date']   = $result->date_from;
				$data['to_date']     = $result->date_to;
				$data['notes']       = $result->notes;

				MDJM()->availability_db->add( $data );
			}

			$migrated = MDJM()->availability_db->get_entries( array( 'group_id' => $new_key ) );

			if ( count( $migrated ) == count( $results ) )	{
				unset( $entries[ $old_key ] );
				set_transient( 'mdjm_availability_db_migrate', $entries, WEEK_IN_SECONDS );
				$wpdb->delete( $table, array( 'entry_id' => $old_key ), array( '%s' ) );
				error_log( "Migrated successfully" );
			}

		}

	}

}
//add_action( 'admin_init', 'test_mdjm_av_upgrade' );

/**
 * Hooks MDJM actions, when present in the $_GET superglobal. Every mdjm_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	1.3
 * @return	void
*/
function mdjm_get_actions() {
	if ( isset( $_GET['mdjm_action'] ) ) {
		do_action( 'mdjm_' . $_GET['mdjm_action'], $_GET );
	}
} // mdjm_get_actions
add_action( 'init', 'mdjm_get_actions' );

/**
 * Hooks MDJM actions, when present in the $_POST superglobal. Every mdjm_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since	1.3
 * @return	void
*/
function mdjm_post_actions() {
	if ( isset( $_POST['mdjm_action'] ) ) {
		do_action( 'mdjm_' . $_POST['mdjm_action'], $_POST );
	}
} // mdjm_post_actions
add_action( 'init', 'mdjm_post_actions' );

/**
 * Action field.
 *
 * Prints the output for a hidden form field which is required for post forms.
 *
 * @since	1.3
 * @param	str		$action		The action identifier
 * @param	bool	$echo		True echo's the input field, false to return as a string
 * @return	str		$input		Hidden form field string
 */
function mdjm_action_field( $action, $echo = true )	{
	$name = apply_filters( 'mdjm_action_field_name', 'mdjm_action' );
	
	$input = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $action . '" />';
	
	if( ! empty( $echo ) )	{
		echo apply_filters( 'mdjm_action_field', $input, $action );
	}
	else	{
		return apply_filters( 'mdjm_action_field', $input, $action );
	}
	
} // mdjm_action_field
