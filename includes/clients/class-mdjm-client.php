<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * MDJM Client Class
 *
 * @package MDJM
 * @subpackage Clients
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MDJM_Client Class
 *
 * @since 1.5
 */
class MDJM_Client {

	/**
	 * The client WP user ID
	 *
	 * @since 1.5
	 * @var str $ID WP User ID. Always 0.
	 */
	public $ID = 0;

	/**
	 * The client login
	 *
	 * @since 1.5
	 * @var arr $user_login client login
	 */
	public $user_login;

	/**
	 * The client first name
	 *
	 * @since 1.5
	 * @var arr $first_name client first name
	 */
	public $first_name;

	/**
	 * The client last name
	 *
	 * @since 1.5
	 * @var arr $last_name client surname
	 */
	public $last_name;

	/**
	 * The client display name
	 *
	 * @since 1.5
	 * @var arr $display_name client display name
	 */
	public $display_name;

	/**
	 * The client email address
	 *
	 * @since 1.5
	 * @var arr $user_email client email address
	 */
	public $user_email;

	/**
	 * The client URL
	 *
	 * @since 1.5
	 * @var arr $user_url client URL
	 */
	public $user_url;

	/**
	 * The date the client registered
	 *
	 * @since 1.5
	 * @var arr $user_registered date client registered
	 */
	public $user_registered;

	/**
	 * The client address 1
	 *
	 * @since 1.5
	 * @var arr $address1 address line 1
	 */
	public $address1;

	/**
	 * The client address 2
	 *
	 * @since 1.5
	 * @var arr $address2 address line 2
	 */
	public $address2;

	/**
	 * The client town
	 *
	 * @since 1.5
	 * @var arr $town town
	 */
	public $town;

	/**
	 * The client county
	 *
	 * @since 1.5
	 * @var arr $county county
	 */
	public $county;

	/**
	 * The client postcode
	 *
	 * @since 1.5
	 * @var arr $postcode postcode
	 */
	public $postcode;

	/**
	 * The client address
	 *
	 * @since 1.5
	 * @var arr $address1 full address
	 */
	public $address;

	/**
	 * The client primary phone
	 *
	 * @since 1.5
	 * @var str $phone1 phone number1
	 */
	public $phone1;

	/**
	 * The client alternate phone
	 *
	 * @since 1.5
	 * @var str $phone2 phone number
	 */
	public $phone2;

	/**
	 * Client birthday
	 *
	 * @since 1.5
	 * @var arr $birthday client birthday
	 */
	public $birthday;

	/**
	 * Send marketing materials?
	 *
	 * @since 1.5
	 * @var arr $marketing send marketing emails
	 */
	public $marketing;

	/**
	 * Whether the client is active
	 *
	 * @since 1.5
	 * @var arr $active active client or not.
	 */
	public $active;

	/**
	 * The client profile fields
	 *
	 * @since 1.5
	 * @var arr $profile_fields client profile fields.
	 */
	public $profile_fields = null;

	/**
	 * Get things going
	 *
	 * @since 1.5
	 * @param int|string $_id The WP user ID or their login name or email address.
	 */
	public function __construct( $_id = 0 ) {
		$client = get_userdata( $_id );

		if ( ! $client ) {
			return false;
		}

		return $this->setup_client( $client );
	} // __construct

	/**
	 * Given the client data, let's set the variables
	 *
	 * @since 1.5
	 * @param obj $client The user object.
	 * @return bool If the setup was successful or not
	 */
	private function setup_client( $client ) {

		if ( ! is_object( $client ) ) {
			return false;
		}

		foreach ( $client->data as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}

		$this->first_name = sanitize_text_field( $client->first_name );
		$this->last_name  = sanitize_text_field( $client->last_name );
		$this->address1   = sanitize_text_field( $client->address1 );
		$this->address2   = sanitize_text_field( $client->address2 );
		$this->town       = sanitize_text_field( $client->town );
		$this->county     = sanitize_text_field( $client->county );
		$this->postcode   = sanitize_text_field( $client->postcode );
		$this->address[]  = ! empty( sanitize_textarea_field( $client->address1 ) ) ? esc_attr( $client->address1 ) : '';
		$this->address[]  = ! empty( sanitize_textarea_field( $client->address2 ) ) ? esc_attr( $client->address2 ) : '';
		$this->address[]  = ! empty( sanitize_text_field( $client->town ) ) ? esc_attr( $client->town ) : '';
		$this->address[]  = ! empty( sanitize_text_field( $client->county ) ) ? esc_attr( $client->county ) : '';
		$this->address[]  = ! empty( sanitize_text_field( $client->postcode ) ) ? esc_attr( $client->postcode ) : '';
		$this->phone1     = ! empty( $client->phone1 ) ? esc_attr( $client->phone1 ) : '';
		$this->phone2     = ! empty( $client->phone2 ) ? esc_attr( $client->phone2 ) : '';
		$this->birthday   = ! empty( $client->birthday ) ? esc_attr( $client->birthday ) : '';
		$this->marketing  = ! empty( $client->marketing ) ? $client->marketing : 'N';

		// $this->get_fields();
		// $this->mapped_fields();

		return true;

	} // setup_client

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.5
	 * @param arr $key Some key.
	 */
	public function __get( $key ) {

		if ( isset( $this->data->$key ) ) {
			$value = $this->data->$key;
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			$value = get_user_meta( $this->ID, $key, true );
		}

	} // __get

	/**
	 * Retrieve the ID
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID

	/**
	 * Retrieve the profile fields
	 *
	 * @since 1.5
	 * @return array
	 */
	public function get_profile_fields() {
		if ( ! isset( $this->profile_fields ) ) {
			$this->profile_fields = mdjm_get_client_fields();
			$field                = array();

			foreach ( $this->profile_fields as $key => $row ) {
				$field[ $key ] = $row['position'];
			}

			array_multisort( $field, SORT_ASC, $this->profile_fields );
		}

		return $this->profile_fields;
	} // get_profile_fields

} // MDJM_Client
