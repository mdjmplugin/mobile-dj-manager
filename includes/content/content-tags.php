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

		$this->event_id		= $event_id;
		$this->client_id	= $client_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->event_id		= null;
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
		// Get tag
		$tag = $m[1];

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
 * @param	int		$event_id		Required: The event_id.
 * @param	int		$client_id		Optional: The event_id. id
 *
 * @since	1.3
 *
 * @return	str		Content with content tags filtered out.
 */
function mdjm_do_content_tags( $content, $event_id, $client_id='' ) {
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
			'tag'         => 'admin_url',
			'description' => __( 'The admin URL to WordPress', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_admin_url'
		),
		array(
			'tag'         => 'application_url',
			'description' => __( 'The Client Zone application URL', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_application_url'
		),
		array(
			'tag'         => 'application_name',
			'description' => __( 'The name of this MDJM application', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_application_name'
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
			'tag'         => 'ddmmyyyy',
			'description' => __( 'Todays date in shortdate format', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_ddmmyyyy'
		),
		array(
			'tag'         => 'website_url',
			'description' => __( 'The URL to your website', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_website_url'
		),
		array(
			'tag'         => 'client_firstname',
			'description' => __( 'The event clients first name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_firstname'
		),
		array(
			'tag'         => 'client_lastname',
			'description' => __( 'The event clients last name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_lastname'
		),
		array(
			'tag'         => 'client_fullname',
			'description' => __( 'The event clients full name', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_fullname'
		),
		array(
			'tag'         => 'client_full_address',
			'description' => __( 'The event clients full address', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_full_address'
		),
		array(
			'tag'         => 'client_email',
			'description' => __( 'The event clients email address', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_email'
		),
		array(
			'tag'         => 'client_primary_phone',
			'description' => __( 'The event clients primary phone number', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_client_primary_phone'
		),
		array(
			'tag'         => 'client_username',
			'description' => sprintf( __( 'The event clients username for logging into %s', 'mobile-dj-manager' ), MDJM_APP ),
			'function'    => 'mdjm_content_tag_client_username'
		),
		array(
			'tag'         => 'client_password',
			'description' => sprintf( __( 'The event clients password for logging into %s', 'mobile-dj-manager' ), MDJM_APP ),
			'function'    => 'mdjm_content_tag_client_password'
		),
		array(
			'tag'         => 'admin_notes',
			'description' => __( 'The admin notes associated with the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_admin_notes'
		),
		array(
			'tag'         => 'balance',
			'description' => __( 'The remaining balance owed for the event', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_balance'
		),
		array(
			'tag'         => 'contract_date',
			'description' => __( 'The date the event contract was signed, or today\'s date', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_date'
		),
		array(
			'tag'         => 'contract_id',
			'description' => __( 'The contract / event ID', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_id'
		),
		array(
			'tag'         => 'contract_url',
			'description' => __( 'The URL for the client to access their event contract', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_contract_url'
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
			'tag'         => 'end_time',
			'description' => __( 'The time the event completes', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_end_time'
		),
		array(
			'tag'         => 'end_date',
			'description' => __( 'The date the event completes', 'mobile-dj-manager' ),
			'function'    => 'mdjm_content_tag_end_date'
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
	return MDJM_APP;
} // mdjm_content_tag_application_name

/**
 * Content tag: company_name.
 * The name of the company running this MDJM instance.
 *
 * @param	
 *
 * @return	str		The name of the company running this MDJM instance.
 */
function mdjm_content_tag_company_name()	{
	return COMPANY_APP;
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
	return mdjm_get_formatted_url( MDJM_CONTACT_PAGE, false );
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
	return date( MDJM_SHORTDATE_FORMAT );
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
	
	if( !empty( $paid ) && $paid != '0.00' && !empty( $cost ) )	{
		return display_price( ( $cost - $paid ) );	
	}
	
	return display_price( $cost );
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
		$return	= $return = date( MDJM_SHORTDATE_FORMAT, strtotime( $contract_date ) );
	}
	else	{
		$return = date( MDJM_SHORTDATE_FORMAT );
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
	
	return mdjm_get_formatted_url( MDJM_CONTRACT_PAGE ) . 'event_id=' . $event_id;
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
		$return = display_price( $deposit );
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
	
	$return = get_post_meta( $post_id, '_mdjm_event_deposit_status', true );
	
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
		$return = date( MDJM_TIME_FORMAT, strtotime( $time ) );
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
		$return = date( MDJM_TIME_FORMAT, strtotime( $time ) );
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
		$return = date( MDJM_SHORTDATE_FORMAT, strtotime( $date ) );
	}
	
	return $return;
} // mdjm_content_tag_end_date
?>