<?php
/**
 * The MDJM Content tags API.
 * Content tags are phrases wrapped in { } placed in HTML or email content
 * that are searched and replaced with MDJM content.
 *
 * Examples:
 * {event_name}
 * {client_fullname}
 *
 * @package     MDJM
 * @subpackage  Content
 * @since       1.3
 * 
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class MDJM_Content_Tags	{
	
	/**
	 * Container for storing all tags
	 *
	 * @since	1.3
	 */
	private $tags;
	
	/**
	 * Event ID
	 *
	 * @since	1.3
	 */
	private $event_id;
	
	/**
	 * Client ID
	 *
	 * @since	1.3
	 */
	private $client_id;
	
	/**
	 * Add a content tag.
	 *
	 * @since	1.3
	 *
	 * @param	str  		$tag 			Content tag to be replace in content.
	 * @param	str			$description	Short description of what content is provided from tag.
	 * @param	str			$func			Hook to run when content tag is found.
	 */
	public function add( $tag, $description, $func ) {
		if( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	} // add

	/**
	 * Remove a content tag.
	 *
	 * @since	1.3
	 *
	 * @param	str		$tag		Content tag to remove hook from.
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	} // remove
	
	/**
	 * Check if $tag is a registered content tag.
	 *
	 * @since	1.3
	 *
	 * @param	str		$tag		Content tag that will be searched.
	 *
	 * @return	bool
	 */
	public function content_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	} // content_tag_exists

	/**
	 * Returns a list of all content tags.
	 *
	 * @since 1.3
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	} // get_tags
	
	/**
	 * Search content for tags and filter content tags through their hooks.
	 *
	 * @param	str		$content		Content to search for tags.
	 * @param 	int		$event_id		The event id.
	 * @param 	int		$client_id		The event id.
	 *
	 * @since 1.3
	 *
	 * @return	str		Content with tags filtered out.
	 */
	public function do_tags( $content, $event_id, $client_id ) {
		// Check if there is at least one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->event_id     = $event_id;
		$this->client_id	= $client_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->event_id     = null;
		$this->client_id	= null;

		return $new_content;
	} // do_tags
	
	/**
	 * Do a specific tag, this function should not be used. Please use mdjm_do_content_tags instead.
	 *
	 * @since 1.3
	 *
	 * @param	str		$m		Content
	 *
	 * @return	mixed
	 */
	public function do_tag( $m ) {
		// Get tag. Force to lower case for backwards compatibility.
		$tag = strtolower( $m[1] );
		
		// Return tag if tag not set
		if ( ! $this->content_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->event_id, $this->client_id, $tag );
	} // do_tag
} // MDJM_Content_Tags

/**
 * Add a content tag.
 *
 * @since	1.3
 *
 * @param 	str		$tag			Content tag to be replace in content.
 * @param 	str		$description	Short description of what content is provided from tag.
 * @param 	str		$func			Callable hook to run when content tag is found.
 */
function mdjm_add_content_tag( $tag, $description, $func ) {
	MDJM()->content_tags->add( $tag, $description, $func );
} // mdjm_add_content_tag

/**
 * Remove a content tag.
 *
 * @since	1.3
 *
 * @param	str		$tag	Content tag to remove hook from.
 */
function mdjm_remove_content_tag( $tag ) {
	MDJM()->content_tags->remove( $tag );
} // mdjm_remove_content_tag

/**
 * Check if $tag is a registered content tag.
 *
 * @since	1.3
 *
 * @param	str		$tag	Content tag that will be searched.
 *
 * @return	bool
 */
function mdjm_content_tag_exists( $tag ) {
	return MDJM()->content_tags->content_tag_exists( $tag );
} // mdjm_content_tag_exists

/**
 * Get all content tags.
 *
 * @since	1.3
 *
 * @return	arr
 */
function mdjm_get_content_tags() {
	return MDJM()->content_tags->get_tags();
} // mdjm_get_content_tags

/**
 * Get a formatted HTML list of all available content tags.
 *
 * @since	1.3
 *
 * @return	str
 */
function mdjm_get_content_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$content_tags = mdjm_get_content_tags();

	// Check
	if( count( $content_tags ) > 0 )	{
		// Loop
		foreach( $content_tags as $content_tag )	{
			// Add email tag to list.
			$list .= '{' . $content_tag['tag'] . '} - ' . $content_tag['description'] . '<br/>';
		}
	}

	// Return the list of tags.
	return $list;
} // mdjm_get_content_tags_list

/**
 * Search content for tags and filter content tags through their hooks.
 *
 * @param	str		$content		Required: Content to search for tags.
 * @param	int		$event_id		Optional: The event_id.
 * @param	int		$client_id		Optional: The event_id. id
 *
 * @since	1.3
 *
 * @return	str		Content with content tags filtered out.
 */
function mdjm_do_content_tags( $content, $event_id='', $client_id='' ) {
	// Replace all tags
	$content = MDJM()->content_tags->do_tags( $content, $event_id, $client_id );

	// Return content
	return $content;
} // mdjm_do_content_tags

/**
 * Load content tags
 *
 * @since	1.3
 */
function mdjm_load_content_tags() {
	do_action( 'mdjm_add_content_tags' );
} // mdjm_load_content_tags
add_action( 'init', 'mdjm_load_content_tags', -999 );

/**
 * Add the default MDJM content tags.
 *
 * @since	1.3
 */
function mdjm_setup_content_tags() {
	// Setup default tags array
	$content_tags = array(
		array(
			'tag'         => 'admin_notes',
			'description' => __( 'The admin notes associated with the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_admin_notes'
		),
		array(
			'tag'         => 'admin_url',
			'description' => __( 'The admin URL to WordPress', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_admin_url'
		),
		array(
			'tag'         => 'application_name',
			'description' => __( 'The name of this MDJM application', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_application_name'
		),
		array(
			'tag'         => 'application_url',
			'description' => __( 'The Client Zone application URL', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_application_url'
		),
		array(
			'tag'         => 'available_packages',
			'description' => __( 'The list of packages available. No price', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_available_packages'
		),
		array(
			'tag'         => 'available_packages_cost',
			'description' => __( 'The list of packages available. With price', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_available_packages_cost'
		),
		array(
			'tag'         => 'balance',
			'description' => __( 'The remaining balance owed for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_balance'
		),
		array(
			'tag'         => 'client_email',
			'description' => __( 'The event clients email address', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_email'
		),
		array(
			'tag'         => 'client_firstname',
			'description' => __( 'The event clients first name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_firstname'
		),
		array(
			'tag'         => 'client_full_address',
			'description' => __( 'The event clients full address', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_full_address'
		),
		array(
			'tag'         => 'client_fullname',
			'description' => __( 'The event clients full name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_fullname'
		),
		array(
			'tag'         => 'client_lastname',
			'description' => __( 'The event clients last name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_lastname'
		),
		array(
			'tag'         => 'client_password',
			'description' => sprintf( __( 'The event clients password for logging into %s', 'mobile-dj-manager' ), 'Client Zone' ),
			'function'    => 'mdjm_content_tag_client_password'
		),
		array(
			'tag'         => 'client_primary_phone',
			'description' => __( 'The event clients primary phone number', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_primary_phone'
		),
		array(
			'tag'         => 'client_username',
			'description' => sprintf( __( 'The event clients username for logging into %s', 'mobile-dj-manager' ), 'Client Zone' ),
			'function'    => 'mdjm_content_tag_client_username'
		),
		array(
			'tag'         => 'company_name',
			'description' => __( 'The name of your company', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_company_name'
		),
		array(
			'tag'         => 'contact_page',
			'description' => __( 'The URL to your websites contact page', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contact_page'
		),
		array(
			'tag'         => 'contract_date',
			'description' => __( "The date the event contract was signed, or today's date", 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_date'
		),
		array(
			'tag'         => 'contract_id',
			'description' => __( 'The contract / event ID', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_id'
		),
		array(
			'tag'         => 'contract_signatory',
			'description' => __( 'The name of the person who signed the contract', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_signatory'
		),
		array(
			'tag'         => 'contract_signatory_ip',
			'description' => __( 'The IP address recorded during contract signing', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_signatory_ip'
		),
		array(
			'tag'         => 'contract_url',
			'description' => __( 'The URL for the client to access their event contract', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_url'
		),
		array(
			'tag'         => 'ddmmyyyy',
			'description' => __( 'Todays date in shortdate format', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_ddmmyyyy'
		),
		array(
			'tag'         => 'deposit',
			'description' => __( 'The deposit amount for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_deposit'
		),
		array(
			'tag'         => 'deposit_status',
			'description' => __( "The deposit payment status. Generally 'Paid' or 'Due'", 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_deposit_status'
		),
		array(
			'tag'         => 'dj_email',
			'description' => __( 'The email address of the events assigned primary employee', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_email'
		),
		array(
			'tag'         => 'dj_firstname',
			'description' => __( 'The first name of the events assigned primary employee', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_firstname'
		),
		array(
			'tag'         => 'dj_fullname',
			'description' => __( 'The full name of the events assigned primary employee', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_fullname'
		),
		array(
			'tag'         => 'dj_notes',
			'description' => __( 'The DJ notes that have been entered against the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_notes'
		),
		array(
			'tag'         => 'dj_primary_phone',
			'description' => __( 'The primary phone number of the events assigned primary employee', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_primary_phone'
		),
		array(
			'tag'         => 'dj_setup_date',
			'description' => __( 'The setup date for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_setup_date'
		),
		array(
			'tag'         => 'dj_setup_time',
			'description' => __( 'The setup time for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_dj_setup_time'
		),
		array(
			'tag'         => 'end_date',
			'description' => __( 'The date the event completes', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_end_date'
		),
		array(
			'tag'         => 'end_time',
			'description' => __( 'The time the event completes', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_end_time'
		),
		array(
			'tag'         => 'event_date',
			'description' => __( 'The date of the event in long format', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_date'
		),
		array(
			'tag'         => 'event_date_short',
			'description' => __( 'The date of the event in short format', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_date_short'
		),
		array(
			'tag'         => 'event_description',
			'description' => __( 'The contents of the event description field', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_description'
		),
		array(
			'tag'         => 'event_duration',
			'description' => __( 'The duration of the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_duration'
		),
		array(
			'tag'         => 'event_name',
			'description' => __( 'The assigned name of the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_name'
		),
		array(
			'tag'         => 'event_status',
			'description' => __( 'The current status of the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_status'
		),
		array(
			'tag'         => 'event_type',
			'description' => __( 'The type of event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_event_type'
		),
		array(
			'tag'         => 'guest_playlist_url',
			'description' => __( 'The URL to your event playlist page for guests', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_guest_playlist_url'
		),
		array(
			'tag'         => 'payment_history',
			'description' => __( 'An overview of payments made by the client for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_payment_history'
		),
		array(
			'tag'         => 'payment_url',
			'description' => __( 'The URL to your payments page', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_payment_url'
		),
		array(
			'tag'         => 'playlist_close',
			'description' => __( 'The number of days before the event that the playlist closes', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_payment_url'
		),
		array(
			'tag'         => 'playlist_url',
			'description' => __( 'The URL to your event playlist page for clients', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_payment_url'
		),
		array(
			'tag'         => 'quotes_url',
			'description' => __( 'The URL to your online quotes page', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_quotes_url'
		),
		array(
			'tag'         => 'start_time',
			'description' => __( 'The event start time', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_start_time'
		),
		array(
			'tag'         => 'total_cost',
			'description' => __( 'The total cost of the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_total_cost'
		),
		array(
			'tag'         => 'venue',
			'description' => __( 'The name of the event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue'
		),
		array(
			'tag'         => 'venue_contact',
			'description' => __( 'The name of the contact at event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_contact'
		),
		array(
			'tag'         => 'venue_details',
			'description' => __( 'Details stored for the venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_details'
		),
		array(
			'tag'         => 'venue_email',
			'description' => __( 'The email address of the event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_email'
		),
		array(
			'tag'         => 'venue_full_address',
			'description' => __( 'The full address of the event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_full_address'
		),
		array(
			'tag'         => 'venue_notes',
			'description' => __( 'Notes associated with the event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_notes'
		),
		array(
			'tag'         => 'venue_telephone',
			'description' => __( 'The phone number of the event venue', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_venue_telephone'
		),
		array(
			'tag'         => 'website_url',
			'description' => __( 'The URL to your website', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_website_url'
		)
	);

	// Apply mdjm_content_tags filter
	$email_tags = apply_filters( 'mdjm_content_tags', $content_tags );

	// Add content tags
	foreach ( $content_tags as $content_tag ) {
		mdjm_add_content_tag( $content_tag['tag'], $content_tag['description'], $content_tag['function'] );
	}
} // mdjm_setup_content_tags
add_action( 'mdjm_add_content_tags', 'mdjm_setup_content_tags' );

/**
 * Content tag: admin_url.
 * The admin url to this WordPress instance.
 *
 * @param	
 *
 * @return	str		The WP instance admin URL.
 */
function mdjm_content_tag_admin_url()	{
	return admin_url();
} // mdjm_content_tag_admin_url

/**
 * Content tag: application_home.
 * The url to the MDJM Client Zone home page for this instance.
 *
 * @param	
 *
 * @return	str		The URL to the Client Zone home page.
 */
function mdjm_content_tag_application_home()	{
	return mdjm_get_formatted_url( MDJM_HOME, false );
} // mdjm_content_tag_application_home

/**
 * Content tag: application_name.
 * The name given to this Client Zone instance.
 *
 * @param	
 *
 * @return	str		The customised name of the Client Zone.
 */
function mdjm_content_tag_application_name()	{
	return mdjm_get_option( 'app_name', __( 'Client Zone', 'mobile-dj-manager' ) );
} // mdjm_content_tag_application_name

/**
 * Content tag: available_packages.
 * The list of available packages.
 *
 * @param	
 *
 * @return	str		The list of available packages. No cost.
 */
function mdjm_content_tag_available_packages()	{
	return mdjm_list_available_packages();
} // mdjm_content_tag_available_packages

/**
 * Content tag: available_packages_cost.
 * The list of available packages with cost.
 *
 * @param	
 *
 * @return	str		The list of available packages. With cost.
 */
function mdjm_content_tag_available_packages_cost()	{
	return mdjm_list_available_packages( '', true );
} // mdjm_content_tag_available_packages_cost

/**
 * Content tag: company_name.
 * The name of the company running this MDJM instance.
 *
 * @param	
 *
 * @return	str		The name of the company running this MDJM instance.
 */
function mdjm_content_tag_company_name()	{
	return mdjm_get_option( 'company_name' );
} // mdjm_content_tag_company_name

/**
 * Content tag: contact_page.
 * The contact page.
 *
 * @param	
 *
 * @return	str		The URL of the contact page.
 */
function mdjm_content_tag_contact_page()	{
	return mdjm_get_formatted_url( mdjm_get_option( 'contact_page' ), false );
} // mdjm_content_tag_contact_page

/**
 * Content tag: ddmmyyyy.
 * The date in short format.
 *
 * @param	
 *
 * @return	str		The current date in short format.
 */
function mdjm_content_tag_ddmmyyyy()	{
	return date( mdjm_get_option( 'short_date_format', 'd/m/Y' ) );
} // mdjm_content_tag_ddmmyyyy

/**
 * Content tag: website_url.
 * The website URL.
 *
 * @param	
 *
 * @return	str		The URL of the website hosting MDJM.
 */
function mdjm_content_tag_website_url()	{
	return home_url();
} // mdjm_content_tag_website_url

/**
 * Content tag: client_firstname.
 * The first name of the client.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The first name of the client.
 */
function mdjm_content_tag_client_firstname( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'first_name', true );
	}
	
	return ucfirst( $return );
} // mdjm_content_tag_client_firstname

/**
 * Content tag: client_lastname.
 * The last name of the client.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The last name of the client.
 */
function mdjm_content_tag_client_lastname( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'last_name', true );
	}
	
	return ucfirst( $return );
} // mdjm_content_tag_client_lastname

/**
 * Content tag: client_fullname.
 * The full name of the client.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The full name (display name) of the client.
 */
function mdjm_content_tag_client_fullname( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
		
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'first_name', true );
		
		if( !empty( $return ) )	{
			$return .= ' ' . get_user_meta( $user_id, 'last_name', true );
		}
	}
	
	return ucwords( $return );
} // mdjm_content_tag_client_fullname

/**
 * Content tag: client_full_address.
 * The full address of the client.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The address of the client.
 */
function mdjm_content_tag_client_full_address( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$client = get_userdata( $user_id );	
	}
	
	if( !empty( $client ) )	{
		if( !empty( $client->address1 ) )	{
			$return[] = $client->address1;
			
			if( !empty( $client->address2 ) )	{
				$return[] = $client->address2;
			}
			
			if( !empty( $client->town ) )	{
				$return[] = $client->town;
			}
			
			if( !empty( $client->county ) )	{
				$return[] = $client->county;
			}
			
			if( !empty( $client->postcode ) )	{
				$return[] = $client->postcode;
			}
		}
	}
	
	return is_array( $return ) ? implode( '<br />', $return ) : $return;
} // mdjm_content_tag_client_full_address

/**
 * Content tag: client_email.
 * The email address of the client.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The email address of the client.
 */
function mdjm_content_tag_client_email( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'user_email', true );	
	}
	
	return strtolower( $return );
} // mdjm_content_tag_client_email

/**
 * Content tag: client_primary_phone.
 * The client phone number.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The primary phone number of the client.
 */
function mdjm_content_tag_client_primary_phone( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'phone1', true );
	}
	
	return $return;
} // mdjm_content_tag_client_primary_phone

/**
 * Content tag: client_username.
 * The client login.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The login name of the client.
 */
function mdjm_content_tag_client_username( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = __( 'Client data not set', 'mobile-dj-manager' );
	
	if( !empty( $user_id ) )	{
		$client = get_userdata( $user_id );	
	}
	
	if( !empty( $client ) && !empty( $client->user_login ) )	{
		$return = $client->user_login;
	}
	
	return $return;
} // mdjm_content_tag_client_username

/**
 * Content tag: client_password.
 * The client password. Reset the password and return the new password.
 *
 * @param	int		The event ID.
 * @param	int		The client ID.
 *
 * @return	str		The login password for the client.
 */
function mdjm_content_tag_client_password( $event_id='', $client_id='' )	{
	if( !empty( $client_id ) )	{
		$user_id = $client_id;
	}
	elseif( !empty( $event_id ) )	{
		$user_id = get_post_meta( $event_id, '_mdjm_event_client', true );
	}
	else	{
		$user_id = '';
	}
	
	$return = sprintf( 
		__( 'Please %sclick here%s to reset your password', 'mobile-dj-manager' ),
		'<a href="' . home_url( '/wp-login.php?action=lostpassword' ) . '">',
		'</a>'
	);
	
	$reset = get_user_meta( $user_id, 'mdjm_pass_action', true );
	
	if( !empty( $reset ) )	{
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( '	-- Password reset for user ' . $user_id );
		
		wp_set_password( $reset, $user_id );
		
		$return = $reset;
	}
	
	return $return;
} // mdjm_content_tag_client_password

/**
 * Content tag: admin_notes.
 * Admin notes associated with event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Event admin notes.
 */
function mdjm_content_tag_admin_notes( $event_id='' )	{	
	return !empty( $event_id ) ? get_post_meta( $event_id, '_mdjm_admin_notes', true ) : '';
} // mdjm_content_tag_admin_notes

/**
 * Content tag: balance.
 * The remaining balance for the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The event payment balance.
 */
function mdjm_content_tag_balance( $event_id='' )	{	
	if( empty( $event_id ) )	{
		return '';
	}
	
	$rcvd = MDJM()->txns->get_transactions( $event_id, 'mdjm-income' );
	$cost = get_post_meta( $event_id, '_mdjm_event_cost', true );
	
	if( !empty( $rcvd ) && $rcvd != '0.00' && !empty( $cost ) )	{
		return mdjm_currency_filter( mdjm_sanitize_amount( ( $cost - $rcvd ) ) );	
	}
	
	return mdjm_currency_filter( mdjm_sanitize_amount( $cost ) );
} // mdjm_content_tag_balance

/**
 * Content tag: contract_date.
 * The date of the contract. If the contract is signed, return the signing date.
 * Otherwise, return the current date.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The event contract date.
 */
function mdjm_content_tag_contract_date( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$signed = get_post_meta( $event_id, '_mdjm_event_contract_approved', true );
	
	if( !empty( $signed ) )	{
		$return	= $return = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $signed ) );
	}
	else	{
		$return = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ) );
	}
	
	return $return;
} // mdjm_content_tag_contract_date

/**
 * Content tag: contract_id.
 * The event ID.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The event contract ID.
 */
function mdjm_content_tag_contract_id( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return get_the_title( $event_id );
} // mdjm_content_tag_contract_id

/**
 * Content tag: contract_signatory.
 * The event ID.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The name of the person who signed the contract.
 */
function mdjm_content_tag_contract_signatory( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
		
	return mdjm_get_contract_signatory_name( $event_id );
} // mdjm_content_tag_contract_signatory

/**
 * Content tag: contract_signatory_ip.
 * The event ID.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The IP address of the person who signed the contract.
 */
function mdjm_content_tag_contract_signatory_ip( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
		
	return mdjm_get_contract_signatory_ip( $event_id );
} // mdjm_content_tag_contract_signatory_ip

/**
 * Content tag: contract_url.
 * The event contract URL for the client.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The URL to the client contract within Client Zone
 */
function mdjm_content_tag_contract_url( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_formatted_url( mdjm_get_option( 'contract_page' ) ) . 'event_id=' . $event_id;
} // mdjm_content_tag_contract_url

/**
 * Content tag: deposit.
 * The event contract URL for the client.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The formatted event deposit amount
 */
function mdjm_content_tag_deposit( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$deposit = get_post_meta( $event_id, '_mdjm_event_deposit', true );
	
	if( !empty( $deposit ) )	{
		$return = mdjm_currency_filter( mdjm_sanitize_amount( $deposit ) );
	}
	else	{
		$return = '';
	}
	
	return $return;
} // mdjm_content_tag_deposit

/**
 * Content tag: deposit_status.
 * Current status of the deposit.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The status of the deposit payment, or Due if no status is found.
 */
function mdjm_content_tag_deposit_status( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = get_post_meta( $event_id, '_mdjm_event_deposit_status', true );
	
	if( empty( $return ) )	{
		$return = 'Due';	
	}
	
	return $return;
} // mdjm_content_tag_deposit_status

/**
 * Content tag: dj_email.
 * Email address of primary employee assigned to event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The email address of the primary employee assigned to the event.
 */
function mdjm_content_tag_dj_email( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$user_id = get_post_meta( $event_id, '_mdjm_event_dj', true );
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'user_email', true );
	}
	
	return $return;
} // mdjm_content_tag_dj_email

/**
 * Content tag: dj_firstname.
 * First name of primary employee assigned to event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The first name of the primary employee assigned to the event.
 */
function mdjm_content_tag_dj_firstname( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$user_id = get_post_meta( $event_id, '_mdjm_event_dj', true );
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'first_name', true );
	}
	
	return $return;
} // mdjm_content_tag_dj_firstname

/**
 * Content tag: dj_fullname.
 * Full name of primary employee assigned to event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The full name (display name) of the primary employee assigned to the event.
 */
function mdjm_content_tag_dj_fullname( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$user_id = get_post_meta( $event_id, '_mdjm_event_dj', true );
	
	$return = '';
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'first_name', true );
		
		if( !empty( $return ) )	{
			$return .= ' ' . get_user_meta( $user_id, 'last_name', true );
		}
	}
	
	return ucwords( $return );
} // mdjm_content_tag_dj_fullname

/**
 * Content tag: dj_primary_phone.
 * DJ Notes associated with event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The notes tassociated with the event that are for the DJ.
 */
function mdjm_content_tag_dj_primary_phone( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$user_id = get_post_meta( $event_id, '_mdjm_event_dj', true );
	
	$return = '';
	
	if( !empty( $user_id ) )	{
		$return = get_user_meta( $user_id, 'phone1', true );
	}
		
	return $return;
} // mdjm_content_tag_dj_primary_phone

/**
 * Content tag: dj_notes.
 * DJ Notes associated with event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The notes tassociated with the event that are for the DJ.
 */
function mdjm_content_tag_dj_notes( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return get_post_meta( $event_id, '_mdjm_event_dj_notes', true );
} // mdjm_content_tag_dj_notes

/**
 * Content tag: dj_setup_date.
 * The date to setup for the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The date for which the event needs to be setup.
 */
function mdjm_content_tag_dj_setup_date( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = __( 'Not specified', 'mobile-dj-manager' );
	
	$date = get_post_meta( $event_id, '_mdjm_event_djsetup', true );
	
	if( !empty( $date ) )	{
		$return = date( 'l, jS F Y', strtotime( $date ) );
	}
	
	return $return;
} // mdjm_content_tag_dj_setup_date

/**
 * Content tag: dj_setup_time.
 * The time to setup for the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Formatted time for which the event needs to be setup.
 */
function mdjm_content_tag_dj_setup_time( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = __( 'Not specified', 'mobile-dj-manager' );
	
	$time = get_post_meta( $event_id, '_mdjm_event_djsetup_time', true );
	
	if( !empty( $time ) )	{
		$return = date( mdjm_get_option( 'time_format', 'H:i' ), strtotime( $time ) );
	}
	
	return $return;
} // mdjm_content_tag_dj_setup_time

/**
 * Content tag: end_time.
 * The time the event completes.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Formatted time for when the event finishes.
 */
function mdjm_content_tag_end_time( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = '';
	
	$time = get_post_meta( $event_id, '_mdjm_event_finish', true );
	
	if( !empty( $time ) )	{
		$return = date( mdjm_get_option( 'time_format', 'H:i' ), strtotime( $time ) );
	}
	
	return $return;
} // mdjm_content_tag_end_time

/**
 * Content tag: end_date.
 * The date the event completes.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Formatted date the event finishes.
 */
function mdjm_content_tag_end_date( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = '';
	
	$date = get_post_meta( $event_id, '_mdjm_event_end_date', true );
	
	if( !empty( $date ) )	{
		$return = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $date ) );
	}
	
	return $return;
} // mdjm_content_tag_end_date

/**
 * Content tag: event_date.
 * The date of the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Formatted long date of the event.
 */
function mdjm_content_tag_event_date( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = '';
	
	$date = get_post_meta( $event_id, '_mdjm_event_date', true );
	
	if( !empty( $date ) )	{
		$return = date( 'l, jS F Y', strtotime( $date ) );
	}
	
	return $return;
} // mdjm_content_tag_event_date

/**
 * Content tag: event_date_short.
 * The date of the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Formatted short date of the event.
 */
function mdjm_content_tag_event_date_short( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = '';
	
	$date = get_post_meta( $event_id, '_mdjm_event_date', true );
	
	if( !empty( $date ) )	{
		$return = date( mdjm_get_option( 'short_date_format', 'd/m/Y' ), strtotime( $date ) );
	}
	
	return $return;
} // mdjm_content_tag_event_date_short

/**
 * Content tag: event_description.
 * The event description as defined by the description field.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Contents of the event description field.
 */
function mdjm_content_tag_event_description( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
		
	$return = get_post_meta( $event_id, '_mdjm_event_description', true );
		
	return $return;
} // mdjm_content_tag_event_description

/**
 * Content tag: event_duration.
 * Duration of the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The duration of the event in hours, minutes.
 */
function mdjm_content_tag_event_duration( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
				
	return mdjm_event_duration( $event_id );
} // event_duration

/**
 * Content tag: event_name.
 * The assigned event name.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		Contents of the event name field.
 */
function mdjm_content_tag_event_name( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
		
	$return = get_post_meta( $event_id, '_mdjm_event_name', true );
		
	return $return;
} // mdjm_content_tag_event_name

/**
 * Content tag: event_status.
 * The current event status.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The current event status label.
 */
function mdjm_content_tag_event_status( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_status( $event_id );
} // mdjm_content_tag_event_status

/**
 * Content tag: event_type.
 * The current event type.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The current event type label.
 */
function mdjm_content_tag_event_type( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_type( $event_id );
} // mdjm_content_tag_event_type

/**
 * Content tag: guest_playlist_url.
 * The URL to the guest playlist page.
 *
 * @param	int		The event ID.
 * @return	str		The playlist page URL for guests.
 */
function mdjm_content_tag_guest_playlist_url( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$url = mdjm_guest_playlist_url( $event_id );
	
	if( empty( $url ) )	{
		$return = __( 'Guest playlist is disabled.', 'mobile-dj-manager' );
	}
	
	else	{
		$return = $url;
	}
		
	return $return;
} // mdjm_content_tag_guest_playlist_url

/**
 * Content tag: payment_history.
 * The payment history for the event.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	str		The events payment history.
 */
function mdjm_content_tag_payment_history( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return MDJM()->txns->list_event_transactions( $event_id );
} // mdjm_content_tag_payment_history

/**
 * Content tag: payment_url.
 * The URL to the payments page.
 *
 * @param
 * @param
 *
 * @return	str		The payments page URL.
 */
function mdjm_content_tag_payment_url()	{	
	return mdjm_get_formatted_url( mdjm_get_option( 'payments_page' ), false );
} // mdjm_content_tag_payment_url

/**
 * Content tag: playlist_close.
 * The number of days until the playlist closes.
 *
 * @param	int		The event ID.
 * @param
 *
 * @return	int|str	The number of days until the playlist for the event closes, or 'never' if it does not.
 */
function mdjm_content_tag_playlist_close( $event_id='' )	{
	$close = $_GLOBALS['mdjm_settings']['playlist']['close'];
	
	return !empty( $close ) ? $close : 'never';
} // mdjm_content_tag_playlist_close

/**
 * Content tag: playlist_url.
 * The URL to the playlist page.
 *
 * @param	int		The event ID.
 * @return	str		The playlist page URL for clients.
 */
function mdjm_content_tag_playlist_url( $event_id='' )	{
	$access = get_post_meta( $event_id, '_mdjm_event_playlist', true );
	
	$return = __( 'Event playlist disabled', 'mobile-dj-manager' );
	
	if( $access == 'Y' )	{
		$return = mdjm_get_formatted_url( mdjm_get_option( 'playlist_page' ), true );
		
		if( !empty( $event_id ) )	{
			$return .=  'event_id=' . $event_id;
		}
	}
	
	return $return;
} // mdjm_content_tag_playlist_url

/**
 * Content tag: quotes_url.
 * The URL to the online quotes page.
 *
 * @param	int		The event ID.
 *
 * @return	str		The online quote page URL for clients.
 */
function mdjm_content_tag_quotes_url( $event_id='' )	{
	$return = mdjm_get_formatted_url( mdjm_get_option( 'quotes_page' ), true );

	if( empty( $event_id ) )	{
		$return .= 'event_id=' . $event_id;
	}
	
	return $return;
} // mdjm_content_tag_quotes_url

/**
 * Content tag: start_time.
 * The event start time.
 *
 * @param	int		The event ID.
 *
 * @return	str		Formatted event start time.
 */
function mdjm_content_tag_start_time( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = '';
	
	$time = get_post_meta( $event_id, '_mdjm_event_start', true );
	
	if( !empty( $time ) )	{
		$return = date( mdjm_get_option( 'time_format', 'H:i' ), strtotime( $time ) );
	}
	
	return $return;
} // mdjm_content_tag_start_time

/**
 * Content tag: total_cost.
 * The event start time.
 *
 * @param	int		The event ID.
 *
 * @return	str		Formatted event total cost.
 */
function mdjm_content_tag_total_cost( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$return = __( 'Not specified', 'mobile-dj-manager' );
	
	$cost = get_post_meta( $event_id, '_mdjm_event_cost', true );
	
	if( !empty( $cost ) )	{
		$return = mdjm_currency_filter( mdjm_sanitize_amount( $cost ) );
	}
	
	return $return;
} // mdjm_content_tag_total_cost

/**
 * Content tag: venue.
 * The event venue name.
 *
 * @param	int		The event ID.
 *
 * @return	str		Name of the event venue.
 */
function mdjm_content_tag_venue( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_venue_meta( $event_id, 'name' );
} // mdjm_content_tag_total_venue

/**
 * Content tag: venue_contact.
 * The event venue contact name.
 *
 * @param	int		The event ID.
 *
 * @return	str		Name of the contact at the event venue.
 */
function mdjm_content_tag_venue_contact( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_venue_meta( $event_id, 'contact' );
} // mdjm_content_tag_venue_contact

/**
 * Content tag: venue_details.
 * The details associated with the venue.
 *
 * @param	int		The event ID.
 *
 * @return	str		The details (tags) associated with the venue.
 */
function mdjm_content_tag_venue_details( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$details = mdjm_get_event_venue_meta( $event_id, 'details' );
	$return = '';
	
	$i = 0;
	
	foreach( $details as $detail )	{
		if( $i > 0 )	{
			$return .= '<br />';
		}
		
		$return .= $detail;
		
		$i++;
	}
	
	return $return;
} // mdjm_content_tag_venue_details

/**
 * Content tag: venue_email.
 * The event venue email address.
 *
 * @param	int		$event_id	The 
 *
 * @return	str		Email address of the event venue.
 */
function mdjm_content_tag_venue_email( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_venue_meta( $event_id, 'email' );
} // mdjm_content_tag_venue_email

/**
 * Content tag: venue_full_address.
 * The address of the venue.
 *
 * @param	int		The event ID.
 *
 * @return	str		The details (tags) associated with the venue.
 */
function mdjm_content_tag_venue_full_address( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	$address = mdjm_get_event_venue_meta( $event_id, 'address' );
	$return = '';
	
	$i = 0;
	
	foreach( $address as $add )	{
		if( !empty( $add ) )	{
			if( $i > 0 )	{
				$return .= '<br />';
			}
			
			$return .= $add;
			
			$i++;
		}
	}
	
	return $return;
} // mdjm_content_tag_venue_full_address

/**
 * Content tag: venue_notes.
 * The notes for the venue.
 *
 * @param
 *
 * @return	str		Notes associated with venue.
 */
function mdjm_content_tag_venue_notes( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_venue_meta( $event_id, 'notes' );
} // mdjm_content_tag_venue_notes


/**
 * Content tag: venue_telephone.
 * The event venue phone number.
 *
 * @param
 *
 * @return	str		Phone number of the event venue.
 */
function mdjm_content_tag_venue_telephone( $event_id='' )	{
	if( empty( $event_id ) )	{
		return '';
	}
	
	return mdjm_get_event_venue_meta( $event_id, 'phone' );
} // mdjm_content_tag_venue_telephone
?>