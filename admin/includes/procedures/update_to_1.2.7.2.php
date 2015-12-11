<?php
/*
 * Update procedures for version 1.2.7.2
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_7_2	{
		function __construct()	{
			MDJM()->debug->log_it( 'No updates required for version 1.2.7.2' );
		}
	}
	
	new MDJM_Upgrade_to_1_2_7_2();