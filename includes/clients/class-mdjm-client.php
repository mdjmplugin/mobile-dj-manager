<?php
/**
 * MDJM Client Class
 *
 * @package		MDJM
 * @subpackage	Clients
 * @since		1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * MDJM_Client Class
 *
 * @since	1.5
 */
class MDJM_Client {

	/**
	 * The client WP user ID
	 *
	 * @since	1.5
	 */
	public $ID = 0;

    /**
	 * The client login
	 *
	 * @since	1.5
	 */
	public $user_login;

    /**
	 * The client first name
	 *
	 * @since	1.5
	 */
	public $first_name;

    /**
	 * The client last name
	 *
	 * @since	1.5
	 */
	public $last_name;

    /**
	 * The client display name
	 *
	 * @since	1.5
	 */
	public $display_name;

    /**
	 * The client email address
	 *
	 * @since	1.5
	 */
	public $user_email;

    /**
	 * The client URL
	 *
	 * @since	1.5
	 */
	public $user_url;

    /**
	 * The date the client registered
	 *
	 * @since	1.5
	 */
	public $user_registered;

    /**
	 * The client address
	 *
	 * @since	1.5
	 */
	public $address;

    /**
	 * The client primary phone
	 *
	 * @since	1.5
	 */
	public $primary_phone;

    /**
	 * The client alternate phone
	 *
	 * @since	1.5
	 */
	public $alt_phone;

    /**
	 * Whether the client is active
	 *
	 * @since	1.5
	 */
	public $active;

	/**
	 * The client profile fields
	 *
	 * @since	1.5
	 */
	public $profile_fields = NULL;

	/**
	 * Get things going
	 *
	 * @since	1.5
     * @param   int|string  $_id    The WP user ID or their login name or email address
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
	 * @since	1.5
	 * @param 	obj		$client	The user object
	 * @return	bool    If the setup was successful or not
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

        $this->first_name    = $client->first_name;
        $this->last_name     = $client->last_name;
        $this->address[]     = ! empty( $client->address1 ) ? esc_attr( $client->address1 ) : '';
        $this->address[]     = ! empty( $client->address2 ) ? esc_attr( $client->address2 ) : '';
        $this->address[]     = ! empty( $client->town )     ? esc_attr( $client->town )     : '';
        $this->address[]     = ! empty( $client->county )   ? esc_attr( $client->county )   : '';
        $this->address[]     = ! empty( $client->postcode ) ? esc_attr( $client->postcode ) : '';
        $this->primary_phone = ! empty( $client->phone1 )   ? esc_attr( $client->phone1 ) : '';
        $this->alt_phone     = ! empty( $client->phone2 )   ? esc_attr( $client->phone2 ) : '';

		//$this->get_fields();
		//$this->mapped_fields();

		return true;

	} // setup_client
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	1.5
	 */
	public function __get( $key ) {

        if ( isset( $this->data->$key ) ) {
            $value = $this->data->$key;
        } elseif ( method_exists( $this, 'get_' . $key ) )  {
            return call_user_func( array( $this, 'get_' . $key ) );
        } else {
            $value = get_user_meta( $this->ID, $key, true );
        }

	} // __get

	/**
	 * Retrieve the ID
	 *
	 * @since	1.5
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID

    /**
     * Retrieve the profile fields
     *
     * @since   1.5
     * @return  array
     */
    public function get_profile_fields() {
		if ( ! isset( $this->profile_fields ) )   {
            $this->profile_fields = mdjm_get_client_fields();
            $field = array();

            foreach( $this->profile_fields as $key => $row )	{
                $field[ $key ] = $row['position'];	
            }

            array_multisort( $field, SORT_ASC, $this->profile_fields );
        }

        return $this->profile_fields;
	} // get_profile_fields

} // MDJM_Client
