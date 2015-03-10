<?php
/*
* mdjm-api-email-rcpt.php
* 09/03/2015
* @since 1.1.1
* A Listener for Client Emails
*/	
	$e = !empty( $_GET['e'] ) ? $_GET['e'] : '';
	$c = !empty( $_GET['c'] ) ? $_GET['c'] : '';
	$s = !empty( $_GET['s'] ) ? urldecode( $_GET['s'] ) : '';
	
	if( empty( $e ) || empty( $c ) || empty( $s ) )
		return;
		
	$j_args = array(
				'client' => $c,
				'author' => 0,	
				'event' => $e,
				'type' => 'Email Read',
				'source' => 'API',
				'entry' => 'The Email "' . stripslashes( $s ) . '" has been opened',
	);
	if( WPDJM_JOURNAL == 'Y' ) f_mdjm_do_journal( $j_args );

?>