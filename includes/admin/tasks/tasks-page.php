<?php
/**
 * Tasks Page
 *
 * @package     MDJM
 * @subpackage  Tasks/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Tasks Page
 *
 * Renders the task page contents.
 *
 * @since	1.0
 * @return	void
*/
function mdjm_tasks_page() {
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'tasks';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		if ( 'view' == $requested_view )	{
			mdjm_render_single_task_view();
		} else	{
			wp_die( __( 'Page not found', 'mobile-dj-manager' ) );
		}
	} else {
		mdjm_tasks_list();
	}
} // mdjm_tasks_page

/**
 * List table of customers
 *
 * @since	1.0
 * @return	void
 */
function mdjm_tasks_list() {
	include( dirname( __FILE__ ) . '/class-mdjm-tasks-table.php' );

	$tasks_table = new MDJM_Tasks_Table();
	$tasks_table->prepare_items();
	?>
	<div class="wrap">
		<h1>
			<?php _e( 'Tasks', 'mobile-dj-manager' ); ?>
        </h1>
		<?php do_action( 'mdjm_tasks_table_top' ); ?>
		<form id="mdjm-tasks-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-tasks' ); ?>">
			<?php
			$tasks_table->display();
			?>
			<input type="hidden" name="post_type" value="mdjm-event" />
			<input type="hidden" name="page" value="mdjm-tasks" />
			<input type="hidden" name="view" value="tasks" />
		</form>
		<?php do_action( 'mdjm_tasks_table_bottom' ); ?>
	</div>
	<?php
} // kbs_customers_list

/**
 * Renders the task view wrapper
 *
 * @since	1.0
 * @param	str		$view		The View being requested
 * @param	arr		$callbacks	The Registered views and their callback functions
 * @return	void
 */
function mdjm_render_single_task_view( $view, $callbacks ) {

	$id             = isset( $_GET['id'] ) ? $_GET['id'] : 0;
	$task_view_role = apply_filters( 'mdjm_view_tasks_role', 'manage_mdjm' );
	$url            = remove_query_arg( array( 'mdjm-message', 'render' ) );

	?>

	<div class='wrap'>

		<?php if ( $customer && $render ) : ?>
            <div id="kbs-item-wrapper" class="kbs-customer-wrapper" style="float: left">
                <?php $callbacks[ $view ]( $customer ) ?>
            </div>
        <?php endif; ?>

	</div><!-- .wrap -->
	
	<?php

} // mdjm_render_single_task_view
