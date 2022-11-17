<?php

/**
 * Renders text API field within setting options.
 *
 * @since   1.1
 * @param   arr $args   Arguments passed by the setting
 * @global  $mdjm_options   Array of all the MDJM Options
 * @return  void
 */
function mdjm_mcs_api_callback( $args ) {
	global $mdjm_options;

	if ( isset( $mdjm_options[ $args['id'] ] ) ) {
		$value = $mdjm_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="mdjm_settings[' . $args['id'] . ']"';
	}

	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$class    = '';
	$mdjm_mc  = new MDJM_MC_API();

	if ( $mdjm_mc->is_connected() ) {
		$class = ' mdjm-tick';
	}

	$html = '<input type="text" class="' . $size . '-text' . $class . '" id="mdjm_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';

	$html .= '<label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['hint'] . '</label>';
	$html .= '<p class="description"><label for="mdjm_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label></p>';

	echo $html;

} // mdjm_mcs_api_callback

/**
 * Subscribe a client to the newsletter when created with event.
 *
 * @since   1.0
 * @param   int|arr $client     Either a client ID or an array of client data.
 * @return  void
 */
function mdjm_mcs_subscribe_client( $client ) {

	if ( mdjm_get_option( 'mc_auto_subscribe', false ) ) {
		mdjm_mcs_subscribe( $client );
	}

} // mdjm_mcs_subscribe_client
add_action( 'mdjm_after_add_new_client', 'mdjm_mcs_subscribe_client' );

/**
 * Process a subscription.
 *
 * @since   1.0
 * @param   mixed $client     An email address, client ID or an array of client data.
 * @return  void
 */
function mdjm_mcs_subscribe( $client ) {

	$client_data = false;

	if ( is_email( $client ) ) {

		$client_data['user_email'] = $client;
		$client_obj                = get_user_by( 'email', $client );

		if ( $client_obj ) {
			$client_data['first_name'] = $client_obj->first_name;
			$client_data['last_name']  = $client_obj->last_name;
		}
	} elseif ( ! is_array( $client ) ) {

		$client_obj = get_userdata( $client );

		if ( $client_obj ) {
			$client_data = array(
				'first_name' => $client_obj->first_name,
				'last_name'  => $client_obj->last_name,
				'user_email' => $client_obj->user_email,
			);
		}
	} else {
		$client_data = array(
			'first_name' => isset( $client['first_name'] ) ? $client['first_name'] : '',
			'last_name'  => isset( $client['last_name'] ) ? $client['last_name'] : '',
			'user_email' => isset( $client['user_email'] ) ? $client['user_email'] : '',
		);
	}

	if ( ! $client_data ) {
		return;
	}

	$mdjm_mc = new MDJM_MC_API();

	$email      = $client_data['user_email'];
	$merge_vars = array(
		'FNAME' => $client_data['first_name'],
		'LNAME' => $client_data['last_name'],
	);

	if ( $mdjm_mc->subscribe( $email, $merge_vars, $replace_interests = true ) ) {
		mdjm_mcs_increment_signups();
	}

} // mdjm_mcs_subscribe

/**
 * Retrieve a list by ID.
 *
 * @since   1.0
 * @param   str $list_id    The ID of the list to fetch
 * @return  obj
 */
function mdjm_mcs_get_list( $list_id ) {
	$mdjm_mc = new MDJM_MC_API();

	return $mdjm_mc->get_lists( array( $list_id ) );

} // mdjm_mcs_get_list

/**
 * Retrieve a list name.
 *
 * @since   1.0
 * @param   str $list_id    The ID of the list to fetch
 * @return  str     The name of the list.
 */
function mdjm_mcs_get_list_name( $list_id = '' ) {

	if ( empty( $list_id ) ) {
		$list_id = mdjm_get_option( 'mc_list', false );
	}

	if ( ! $list_id ) {
		return false;
	}

	$list = mdjm_mcs_get_list( $list_id );

	if ( ! $list ) {
		return false;
	}

	return $list[0]->name;

} // mdjm_mcs_get_list_name

/**
 * Retrieve the number of users who have signed up via this plugin.
 *
 * @since   1.0
 * @return  int
 */
function mdjm_mcs_get_web_signup_count() {
	$list = mdjm_get_option( 'mc_list', false );

	if ( $list ) {
		return get_option( 'mdjm_mcs_signups_' . $list, '0' );
	}

	return false;
} // mdjm_mcs_get_web_signup_count

/**
 * Increase the signup count.
 *
 * @since   1.0
 * @return  int
 */
function mdjm_mcs_increment_signups() {
	$list = mdjm_get_option( 'mc_list', false );

	$count = mdjm_mcs_get_web_signup_count();

	if ( ! $count ) {
		$count = 0;
	}

	if ( $list ) {
		update_option( 'mdjm_mcs_signups_' . $list, $count++ );
	}
} // mdjm_mcs_increment_signups

/**
 * Register the DCF field type.
 *
 * @since   1.0
 * @param   arr $field_types
 * @return  arr     $field_types
 */
function mdjm_mcs_register_dcf_field_type( $field_types ) {
	$field_types['mcs_newsletter'] = __( 'MailChimp Subscription', 'mdjm-mailchimp-subscribe' );

	return $field_types;
} // mdjm_mcs_register_dcf_field_type
add_filter( 'mdjm_dcf_field_types', 'mdjm_mcs_register_dcf_field_type' );

/**
 * Register the newsletter field within DCF as single use.
 *
 * @since   1.0
 * @param   arr $single     Array of single fields.
 * @return  arr     $single     Array of single fields.
 */
function mdjm_mcs_register_dcf_single_field( $single ) {
	$single[] = 'mcs_newsletter';

	return $single;
} // mdjm_mcs_register_dcf_single_field
add_filter( 'mdjm_dcf_single_fields', 'mdjm_mcs_register_dcf_single_field' );

/**
 * Hide the field label for the signup option
 *
 * @since   1.1
 * @param   bool $hide_label     True to hide, false to display
 * @param   arr  $settings       Form field settings array
 * @param   obj  $field          Form field post object
 * @return  bool    True|False
 */
function mdjm_mcs_filter_dcf_newsletter_signup_field_label( $hide_label, $settings, $field ) {
	if ( 'mcs_newsletter' == $settings['type'] ) {
		$hide_label = true;
	}
	return $hide_label;
} // mdjm_mcs_filter_dcf_newsletter_signup_field_label
add_filter( 'mdjm_dcf_hide_field_label', 'mdjm_mcs_filter_dcf_newsletter_signup_field_label', 10, 3 );

/**
 * Callback for the DCF field type.
 *
 * @since   1.0
 * @param   obj $field      The field WP_Post object.
 * @param   arr $settings   The configuration for the field
 */
function mdjm_dcf_display_mcs_newsletter_field( $field, $settings ) {

	$checked = ! empty( $settings['selected'] ) ? ' checked="checked"' : '';
	$class   = ! empty( $settings['input_class'] ) ? $settings['input_class'] : '';

	if ( ! $checked ) {
		$checked = mdjm_get_option( 'mc_auto_subscribe', false ) ? ' checked="checked"' : '';
	}

	$output = sprintf(
		'<input type="checkbox" name="%1$s" id="%1$s"%2$s%3$s /> %4$s',
		esc_attr( $field->post_name ),
		$class,
		$checked,
		esc_attr( get_the_title( $field->ID ) )
	);

	$output .= sprintf( '<input type="hidden" name="mdjm_mcs_newsletter" value="%1$s" />', $field->post_name );

	echo apply_filters( 'mdjm_dcf_checkbox_field_callback', $output, $field, $settings );

} // mdjm_dcf_display_mcs_newsletter_field
add_action( 'mdjm_dcf_display_mcs_newsletter_field', 'mdjm_dcf_display_mcs_newsletter_field', 10, 2 );

/**
 * Subscribe a client to the newsletter after contact form submission.
 *
 * @since   1.0
 * @param   int $client_id      Client user ID.
 * @param   arr $data           Array of data submitted to form
 * @return  void
 */
function mdjm_mcs_dcf_subscribe_client( $client_id, $data ) {

	$auto_subscribe = mdjm_get_option( 'mc_auto_subscribe', false );

	if ( ! isset( $_POST['mdjm_mcs_newsletter'] ) && ! $auto_subscribe ) {
		return;
	}

	if ( isset( $_POST[ $_POST['mdjm_mcs_newsletter'] ] ) && ! empty( $data['client_info']['user_email'] ) ) {
		mdjm_mcs_subscribe( $data['client_info']['user_email'] );
	}

} // mdjm_mcs_dcf_subscribe_client
add_action( 'mdjm_dcf_after_create_client', 'mdjm_mcs_dcf_subscribe_client', 10, 2 );

/**
 * Displays the newsletter signup option on the Payment Form
 *
 * @since   1.3.8.2
 * @return  str
 */
function mdjm_mcs_payments_form_signup_field() {
	?>
	<p><input type="checkbox" name="mcs_newsletter" id="mdjm-newsletter" value="1" /> <?php _e( 'Signup to our newsletter?', 'mdjm-mailchimp-subscribe' ); ?></p>
	<?php
} // mdjm_mcs_payments_form_signup
add_action( 'mdjm_payment_form_before_submit', 'mdjm_mcs_payments_form_signup_field' );

/**
 * Subscribe a client to the newsletter after contact form submission.
 *
 * @since   1.0
 * @param   int|arr $client     Either a client ID or an array of client data.
 * @return  void
 */
function mdjm_mcs_subscribe_client_payment( $data, $payment_data ) {

	if ( empty( $data['mcs_newsletter'] ) && ! mdjm_get_option( 'mc_auto_subscribe', false ) ) {
		return;
	}

	mdjm_mcs_subscribe( $payment_data['client_id'] );

} // mdjm_mcs_subscribe_client_payment
add_action( 'mdjm_payment_before_gateway', 'mdjm_mcs_subscribe_client_payment', 10, 2 );

/**
 * Adds list to the events overview dashboard widget
 *
 * @since   1.0
 * @return  void
 */
function mdjm_mcs_overview() {
	$list_id   = mdjm_get_option( 'mc_list', false );
	$web_count = mdjm_mcs_get_web_signup_count();

	if ( ! $list_id ) {
		return;
	}

	$list = mdjm_mcs_get_list( $list_id );

	if ( $list ) :
		?>
		<p>
		<?php
		printf(
			__( 'MailChimp List <strong>%1$s</strong> has %2$s subscribers..', 'mdjm-mailchimp-subscribe' ),
			$list[0]->name,
			$list[0]->stats->member_count
		);
		?>
		</p> 
		<?php
	endif;

} // mdjm_mcs_overview
add_action( 'mdjm_after_events_overview', 'mdjm_mcs_overview' );
