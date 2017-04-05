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
	if ( isset( $_GET['view'], $_GET['id'] ) && 'task' == sanitize_text_field( $_GET['view'] ) ) {
			mdjm_render_single_task_view( $_GET['id'] );
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
function mdjm_render_single_task_view( $id ) {

	$task_view_role = apply_filters( 'mdjm_view_tasks_role', 'manage_mdjm' );
	$url            = remove_query_arg( array( 'mdjm-message', 'render' ) );
	$task           = mdjm_get_task( $id );
	$return_url     = add_query_arg( array(
		'post_type' => 'mdjm-event',
		'page'      => 'mdjm-tasks'
	), admin_url( 'edit.php' ) );

	if ( empty( $task ) )	{
		wp_die( __( 'Invalid task', 'mobile-dj-manager' ) );
	}

	$delete_url = add_query_arg( array(
		'post_type'   => 'mdjm-event',
		'page'        => 'mdjm-tasks',
		'mdjm-action' => 'delete_task',
		'task_id' => $id
		), admin_url( 'edit.php?' )
	);

	?>

	<div class="wrap mdjm-wrap">
        <h2><?php printf( __( 'Task: %s', 'mobile-dj-manager' ), esc_html( $task['name'] ) ); ?></h2>
        <?php do_action( 'mdjm_view_task_details_before', $id ); ?>
        <form id="mdjm-edit-task-form" method="post">
		<?php do_action( 'mdjm_view_task_details_form_top', $id ); ?>
        <div id="poststuff">
			<div id="mdjm-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'mdjm_view_task_details_sidebar_before', $id ); ?>

							<div id="mdjm-task-update" class="postbox mdjm-task-data">

								<h3 class="hndle">
									<span><?php _e( 'Update Task', 'mobile-dj-manager' ); ?></span>
								</h3>

								<div class="mdjm-task-update-box mdjm-admin-box">
									<?php do_action( 'mdjm_view_task_details_update_before', $id ); ?>
									<div id="major-publishing-actions">
                                    	<?php if ( mdjm_can_delete_task( $task ) ) : ?>
                                            <div id="delete-action">
                                                <a href="<?php echo wp_nonce_url( $delete_url, 'mdjm_task_nonce' ) ?>" class="mdjm-delete-task mdjm-delete">
                                                    <?php _e( 'Delete Task', 'mobile-dj-manager' ); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Task', 'mobile-dj-manager' ); ?>"/>
										<div class="clear"></div>
									</div>
									<?php do_action( 'mdjm_view_task_details_update_after', $id ); ?>
								</div><!-- /.mdjm-order-update-box -->

							</div><!-- /#mdjm-task-data -->

							<?php do_action( 'mdjm_view_task_details_sidebar_after', $id ); ?>

						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'mdjm_view_task_details_main_before', $id ); ?>

							<div id="mdjm-task-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Task Details', 'mobile-dj-manager' ); ?></span>
								</h3>
								<div class="inside mdjm-clearfix">

									<div class="column-container task-info">
										<div class="column">
                                        	<strong><?php _e( 'Name:', 'mobile-dj-manager' ); ?></strong>
                                            <br />
											<?php echo MDJM()->html->text( array(
												'id'    => 'mdjm-task-name',
												'name'  => 'task_name',
												'value' => esc_html( $task['name'] ),
												'class' => 'regular-text',
											) ); ?>
										</div>
                                        <div class="column column-2">
                                        	<strong><?php _e( 'Frequency:', 'mobile-dj-manager' ); ?></strong>
                                            <br />
											<?php echo MDJM()->html->select( array(
												'options'          => mdjm_get_task_schedule_options(),
												'name'             => 'task_frequency',
												'id'               => 'mdjm-task-frequency',
												'selected'         => $task['frequency']
											) ); ?>
										</div>
									</div>

									<div class="column-container task-info">
                                        <p><strong><?php _e( 'Description:', 'mobile-dj-manager' ); ?></strong>
                                        <br />
                                        <?php echo MDJM()->html->textarea( array(
                                            'name'        => 'task_description',
											'value'       => esc_html( $task['desc'] ),
											'class'       => 'large-text description'
                                        ) ); ?></p>
									</div>

									<?php
									do_action( 'mdjm_task_view_details', $id );
									?>

								</div><!-- /.inside -->
							</div><!-- /#mdjm-task-details -->

							<?php do_action( 'mdjm_view_task_details_main_after', $id ); ?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #mdjm-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'mdjm_view_task_details_form_bottom', $id ); ?>
		<?php wp_nonce_field( 'mdjm_update_task_details_nonce' ); ?>
		<input type="hidden" name="mdjm_task_id" value="<?php echo esc_attr( $id ); ?>"/>
        <input type="hidden" name="mdjm-action" value="update_task_details"/>
	</form>
	<?php do_action( 'mdjm_view_task_details_after', $id ); ?>
</div><!-- /.wrap -->
	
	<?php

} // mdjm_render_single_task_view
