<?php
/*
* mdjm-api-email-rcpt.php
* 09/03/2015
* @since 1.1.1
* A Listener for Client Emails
*/
	if( !class_exists( 'MDJM_Communication' ) )
		require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-communications.php' );
		
	$mdjm_comms = new MDJM_Communication();

	$action = !empty( $_GET['action'] ) ? $_GET['action'] : '';
	$p = !empty( $_GET['post'] ) ? $_GET['post'] : '';
	
	if( empty( $action ) )
		return;
		
	if( $action == 'open_email' )	{
		if( empty( $p ) )
			return;
			
		$mdjm_comms->track_email_open( $p );
	}
		
	exit;
?>