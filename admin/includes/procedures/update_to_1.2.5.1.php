<?php
/*
 * Update procedures for version 1.2.5.1
 *
 *
 *
 */
	class MDJM_Upgrade_to_1_2_5_1	{
		function __construct()	{
			$GLOBALS['mdjm_debug']->log_it( 'No updates required for version 1.2.5.1' );
		}
	}
	
	new MDJM_Upgrade_to_1_2_5_1();