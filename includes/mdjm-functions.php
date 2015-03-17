<?php
/*
 * mdjm-functions.php
 * 17/03/2015
 * Contains all main MDJM functions used in front & back end
 * 
 */
 
/*
 * -- START EVENT FUNCTIONS
 */
 	/*
	* mdjm_event_by_id
	* 17/03/2015
	* Get the event details from the given ID
	* 
	*	@since: 1.1.2
	*	@called: Only from within the MDJM_Events class
	*	@params: $event_id
	*	@returns: $event_details => object
	*/
	function mdjm_event_by_id( $event_id )	{
		if( empty( $event_id ) )
			return;
			
		/* -- Utilise the MDJM_Events class -- */
		$event_details = mdjm_event_by( 'ID', $event_id );
		
		return $event_details;
	} // mdjm_event_by_id
 
/*
 * -- END EVENT FUNCTIONS
 */
?>