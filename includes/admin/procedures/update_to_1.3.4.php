<?php

/**
 * Run the update procedures.
 *
 * @version	1.3.4
 * @param
 * @return.
 */
function mdjm_run_update_14()	{

	mdjm_update_option( 'employee_pay_status', array( 'completed' ) );

}
add_action( 'init', 'mdjm_run_update_14', 15 );

