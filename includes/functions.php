<?php
/**
 * THIS FILE CAN BE DEPRECATED AFTER 1 JAN 2016
 * Functions that are used mainly within the frontend
 * may also be called from the backend
 *
 *
 * @since 1.0
 *
 */
/****************************************************************************************************
--	AVAILABILITY
****************************************************************************************************/
/**
* f_mdjm_availability_form
* 27/12/2014
* @since 0.9.9
* Displays the availability checker form
*/
	function f_mdjm_availability_form( $args )	{
		MDJM_Availability_Checker::availability_form( $args );
	}
