<?php
/*
 * Update procedures for version 1.2.7.3
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_7_3	{
		function __construct()	{
			MDJM()->debug->log_it( 'No updates required for version 1.2.7.3' );
		}
	}
	
	new MDJM_Upgrade_to_1_2_7_3();