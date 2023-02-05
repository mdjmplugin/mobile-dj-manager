<?php
/**
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Class: MDJM
 * Description: The main MDJM class
 */

	/* -- Build the MDJM class -- */
if ( ! class_exists( 'MDJM' ) ) {
	class MDJM {
		// Publicise the Events class so we can use it throughout
		public $mdjm_events;
		/**
		 * Class constructor
		 */
		public function __construct() {
			global $mdjm_post_types;

			$mdjm_post_types = array(
				'mdjm_communication',
				'contract',
				'mdjm-custom-fields',
				'mdjm-signed-contract',
				'email_template',
				'mdjm-event',
				'mdjm-quotes',
				'mdjm-transaction',
				'mdjm-venue',
			);

			/* -- Hooks -- */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) ); // Admin styles & scripts
		} // __construct

		/*
		* --
		* STYLES & SCRIPTS
		* --
		*/
		/*
		 * admin_enqueue
		 * Register & enqueue the scripts & styles we want to use
		 * Only register those scripts we want on all pages
		 * Or those we can control
		 * Others should be called from the pages themselves
		 */
		public function admin_enqueue() {
			global $mdjm_post_types;

			// jQuery Validation
			wp_register_script( 'jquery-validation-plugin', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', false );

			if ( in_array( get_post_type(), $mdjm_post_types ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'mdjm_custom_event_fields' ) ) {

				wp_register_style( 'mdjm-admin', MDJM_PLUGIN_URL . '/assets/css/mdjm-admin.min.css', '', MDJM_VERSION_NUM );
				wp_enqueue_style( 'mdjm-admin' );

				wp_enqueue_script( 'jquery-validation-plugin' );

			}
		} // admin_enqueue

	} // MDJM class
} // if( !class_exists )
