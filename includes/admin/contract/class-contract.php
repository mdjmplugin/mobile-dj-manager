<?php
/**
 * The Wizard pages helper.
 *
 * @package     MDJM
 * @subpackage  Admin\Contract
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author      MDJM <info@mdjm.co.uk>
 * @since       1.5.9
 */

defined( 'ABSPATH' ) || exit;

class Contract_View {

	protected $slug = 'mdjm-view-contract';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		\add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// If the page is not this page stop here.
		if ( ! $this->is_current_page() ) {
			return;
		}

		\add_action( 'admin_init', array( $this, 'admin_page' ), 30 );
	}

	/**
	 * Add the admin menu item.
	 */
	public function add_admin_menu() {
		if ( $this->get( 'page' ) !== $this->slug ) {
			return;
		}

		add_submenu_page(
			null,
			esc_html__( 'Contract View', 'mobile-dj-manager' ),
			esc_html__( 'Contract View', 'mobile-dj-manager' ),
			'mdjm_employee',
			$this->slug,
			[ $this, 'admin_page' ]
		);
	}

	/**
	 * Output the admin page.
	 */
	public function admin_page() {

		// Do not proceed if we're not on the right page.
		if ( $this->get( 'page' ) !== $this->slug ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		if ( ! mdjm_employee_can( 'manage_events' ) )	{
			return;
		}

		$event_id = isset($_GET['event_id']) ? absint( wp_unslash( $_GET['event_id'] ) ) : 0;

		if ( empty( $event_id ) ) {
			esc_html_e( 'The event cannot be found', 'mobile-dj-manager' );
			exit;
		}

		$mdjm_event = new MDJM_Event( $event_id );

		if ( ! mdjm_is_admin() )	{
			if ( ! array_key_exists( get_current_user_id(), $mdjm_event->get_all_employees() ) )	{
				return;
			}
		}

		if ( ! $mdjm_event->get_contract_status() )	{
			printf( esc_html__( 'The contract for this %s is not signed', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) );
			exit;
		}

		$contract_id = $mdjm_event->get_contract();

		if ( empty( $contract_id ) )	{
			return;
		}

		echo mdjm_show_contract( $contract_id, $mdjm_event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		exit;
	}

	/**
	 * Is the page is current page.
	 *
	 * @return boolean
	 */
	public function is_current_page() {
		$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? filter_input( INPUT_GET, 'page' ) : false;
		return $page === $this->slug;
	}

	/**
	 * Get field from query string.
	 *
	 * @return mixed
	 */
	public static function get( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return filter_has_var( INPUT_GET, $id ) ? filter_input( INPUT_GET, $id, $filter, $flag ) : $default;
	}

}
new Contract_View();
