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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tasks Page
 *
 * Renders the task page contents.
 *
 * @since   1.0
 * @return  void
*/
function mdjm_tasks_page() {
	if ( isset( $_GET['view'], $_GET['id'] ) && 'task' == sanitize_text_field( wp_unslash( $_GET['view'] ) ) ) {
			mdjm_render_single_task_view( sanitize_text_field( wp_unslash( $_GET['id'] ) ) );
	} else {
		mdjm_tasks_list();
	}
} // mdjm_tasks_page

/**
 * List table of customers
 *
 * @since   1.0
 * @return  void
 */
function mdjm_tasks_list() {
	include dirname( __FILE__ ) . '/class-mdjm-tasks-table.php';

	$tasks_table = new MDJM_Tasks_Table();
	$tasks_table->prepare_items();
	?>
	<div class="wrap">
		<h1>
			<?php esc_html_e( 'Tasks', 'mobile-dj-manager' ); ?>
        </h1>
		<?php do_action( 'mdjm_tasks_table_top' ); ?>
		<form id="mdjm-tasks-filter" method="get" action="<?php echo esc_url( admin_url( 'edit.php?post_type=mdjm-event&page=mdjm-tasks' ) ); ?>">
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
} // mdjm_tasks_list

/**
 * Renders the task view wrapper
 *
 * @since   1.0
 * @param   str     $view       The View being requested
 * @param   arr     $callbacks  The Registered views and their callback functions
 * @return  void
 */
function mdjm_render_single_task_view( $id ) {

	$task_view_role = apply_filters( 'mdjm_view_tasks_role', 'manage_mdjm' );
	$url            = remove_query_arg( array( 'mdjm-message', 'render' ) );
	$task           = mdjm_get_task( $id );
	$run_when       = explode( ' ', $task['options']['age'] );
    $run_times      = mdjm_get_task_run_times( $id );
    $hide_runtimes  = 'playlist-notification' == $id ? ' mdjm-hidden' : '';
	$return_url     = add_query_arg( array(
		'post_type' => 'mdjm-event',
		'page'      => 'mdjm-tasks',
	), admin_url( 'edit.php' ) );

	if ( empty( $task ) ) {
		wp_die( esc_html__( 'Invalid task', 'mobile-dj-manager' ) );
	}

	$run_task_url = add_query_arg( array(
		'post_type'   => 'mdjm-event',
		'page'        => 'mdjm-tasks',
		'id'          => $id,
		'mdjm-action' => 'run_task',
	), admin_url( 'edit.php' ) );

	$delete_url = add_query_arg( array(
		'post_type'   => 'mdjm-event',
		'page'        => 'mdjm-tasks',
		'mdjm-action' => 'delete_task',
		'task_id'     => $id,
	), admin_url( 'edit.php?' ) );

	?>

	<div class="wrap mdjm-wrap">
        <h1>
			<?php printf( esc_html__( 'Task: %s', 'mobile-dj-manager' ), esc_html( $task['name'] ) ); ?>
        	<a href="<?php echo esc_url( $return_url ); ?>" class="page-title-action">
				<?php esc_html_e( 'Back to Task List', 'mobile-dj-manager' ); ?>
            </a>
        </h1>
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
									<span><?php esc_html_e( 'Update Task', 'mobile-dj-manager' ); ?></span>
								</h3>
                                <div class="inside">
									<div class="mdjm-admin-box">

										<?php do_action( 'mdjm_task_details_stats_before', $id ); ?>

										<div class="mdjm-admin-box-inside mdjm-task-stats">
                                        	<p>
												<span class="label"><?php esc_html_e( 'Last Ran:', 'mobile-dj-manager' ); ?>&nbsp;</span>
                                                <?php if ( ! empty( $task['lastran'] ) && 'Never' != $task['lastran'] ) : ?>
                                                    <?php echo esc_html( date_i18n( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ), $task['lastran'] ) ); ?>
                                                <?php else : ?>
                                                    <?php echo esc_html__( 'Never', 'mobile-dj-manager' ); ?>
                                                <?php endif; ?>
                                            </p>

											<p>
												<span class="label"><?php esc_html_e( 'Next Due:', 'mobile-dj-manager' ); ?>&nbsp;</span>
                                                <?php if ( ! empty( $task['nextrun'] ) && 'N/A' != $task['nextrun'] ) : ?>
                                                     <?php echo esc_html( date_i18n( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ), $task['nextrun'] ) ); ?>
                                                <?php else : ?>
                                                    <?php echo esc_html__( 'N/A', 'mobile-dj-manager' ); ?>
                                                <?php endif; ?>
                                            </p>

											<p>
												<span class="label"><?php esc_html_e( 'Total Runs:', 'mobile-dj-manager' ); ?>&nbsp;</span>
                                                <?php echo esc_html( $task['totalruns'] ); ?>
                                            </p>

											<?php if ( 'upload-playlists' == $id ) : ?>
                                                <p>
                                                    <span class="label"><?php esc_html_e( 'Entries Uploaded:', 'mobile-dj-manager' ); ?>&nbsp;</span>
													<?php echo esc_html( mdjm_get_uploaded_playlist_entry_count() ); ?>
                                                </p>
                                            <?php else : ?>
                                            	<p>
													<?php
                                                    echo MDJM()->html->checkbox( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
														'name'    => 'task_active',
														'current' => ! empty( $task['active'] ) ? true : false, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													) );
                                                    ?>
                                                    &nbsp;
                                                    <span class="label"><?php esc_html_e( 'Task Active', 'mobile-dj-manager' ); ?></span>
                                                </p>
                                            <?php endif; ?>

											<?php if ( ! empty( $task['active'] ) ) : ?>
                                            	<p>
                                                    <a href="<?php echo esc_url( $run_task_url ); ?>" class="button button-secondary">
														<?php esc_html_e( 'Run Task', 'mobile-dj-manager' ); ?>
                                                    </a>
                                                </p>
                                            <?php endif; ?>

                                        </div><!-- /.mdjm-admin-box-inside -->

                                    </div><!-- /.mdjm-admin-box -->
                                </div><!-- /.inside -->

								<div class="mdjm-task-update-box mdjm-admin-box">
									<?php do_action( 'mdjm_view_task_details_update_before', $id ); ?>
									<div id="major-publishing-actions">
                                    	<?php if ( mdjm_can_delete_task( $task ) ) : ?>
                                            <div id="delete-action">
                                                <a href="<?php echo wp_nonce_url( $delete_url, 'mdjm_task_nonce' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" class="mdjm-delete-task mdjm-delete">
                                                    <?php esc_html_e( 'Delete Task', 'mobile-dj-manager' ); ?>
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
									<span><?php esc_html_e( 'Task Details', 'mobile-dj-manager' ); ?></span>
								</h3>
								<div class="inside mdjm-clearfix">

									<div class="column-container task-info">
										<div class="column">
                                        	<strong><?php esc_html_e( 'Name:', 'mobile-dj-manager' ); ?></strong>
                                            <br />
											<?php
                                            echo MDJM()->html->text( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'id'    => 'mdjm-task-name',
												'name'  => 'task_name',
												'value' => esc_html( $task['name'] ),
											) );
                                            ?>
										</div>
                                        <div class="column column-2">
                                        	<strong><?php esc_html_e( 'Frequency:', 'mobile-dj-manager' ); ?></strong>
                                            <br />
											<?php
                                            echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'options'  => mdjm_get_task_schedule_options(),
												'name'     => 'task_frequency',
												'id'       => 'mdjm-task-frequency',
												'selected' => esc_attr( $task['frequency'] ),
											) );
                                            ?>
										</div>
									</div>

									<?php do_action( 'mdjm_task_view_details_after_info', $id ); ?>

									<div class="column-container task-info">
                                        <p><strong><?php esc_html_e( 'Description:', 'mobile-dj-manager' ); ?></strong>
                                        <br />
                                        <?php
                                        echo MDJM()->html->textarea( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'name'  => 'task_description',
											'value' => esc_html( $task['desc'] ),
											'class' => 'large-text description',
										) );
                                        ?>
                                        </p>
									</div>

									<?php do_action( 'mdjm_task_view_details_after_description', $id ); ?>

                                    <div class="column-container task-info<?php echo esc_attr( $hide_runtimes ); ?>">
                                        <p><strong><?php esc_html_e( 'Run this task:', 'mobile-dj-manager' ); ?></strong>
                                        <br />
                                        <?php
                                            $run_intervals = array();
										for ( $i = 1; $i <= 12; $i++ ) {
											$run_intervals[ $i ] = $i;
										}

                                        ?>
                                        <?php
                                        echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'name'     => 'task_run_time',
                                            'id'       => 'task-run-time',
                                            'selected' => esc_html( $run_when[0] ),
                                            'options'  => $run_intervals, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										) );
                                        ?>
                                        &nbsp;&nbsp;
                                        <?php
                                        echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'name'     => 'task_run_period',
                                            'id'       => 'task-run-period',
                                            'selected' => esc_html( $run_when[1] ),
                                            'options'  => array(
                                                'HOUR'  => esc_html__( 'Hour(s)', 'mobile-dj-manager' ),
                                                'DAY'   => esc_html__( 'Day(s)', 'mobile-dj-manager' ),
                                                'WEEK'  => esc_html__( 'Week(s)', 'mobile-dj-manager' ),
                                                'MONTH' => esc_html__( 'Month(s)', 'mobile-dj-manager' ),
                                                'YEAR'  => esc_html__( 'Year(s)', 'mobile-dj-manager' ),
                                            ),
										) );
                                        ?>
                                        &nbsp;&nbsp;
                                        <?php
                                        echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'name'     => 'task_run_event_status',
                                            'id'       => 'task-run-event-status',
                                            'selected' => esc_attr( $task['options']['run_when'] ),
                                            'options'  => $run_times, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										) );
                                        ?>
                                        </p>
                                    </div>

									<?php do_action( 'mdjm_task_view_details', $id ); ?>

								</div><!-- /.inside -->
							</div><!-- /#mdjm-task-details -->

							<?php do_action( 'mdjm_view_task_details_main_after', $id ); ?>

							<?php if ( isset( $task['options']['email_template'] ) ) : ?>

								<?php do_action( 'mdjm_view_task_details_email_options_before', $id ); ?>

                                <div id="mdjm-task-email-options" class="postbox">
                                    <h3 class="hndle">
                                        <span><?php esc_html_e( 'Email Options', 'mobile-dj-manager' ); ?></span>
                                    </h3>
                                    <div class="inside mdjm-clearfix">
                                        <div class="column-container email-options">
                                            <div class="column">
                                                <strong><?php esc_html_e( 'Email Template:', 'mobile-dj-manager' ); ?></strong>
                                                <br />
                                                <?php
                                                echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                    'options' => mdjm_list_templates( 'email_template' ),
                                                    'name' => 'task_email_template',
                                                    'id'   => 'mdjm-task-email-template',
                                                    'selected' => esc_attr( $task['options']['email_template'] ),
												) );
                                                ?>
                                            </div>
                                            <div class="column column-2">
                                                <strong><?php esc_html_e( 'Subject:', 'mobile-dj-manager' ); ?></strong>
                                                <br />
                                                <?php
                                                echo MDJM()->html->text( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                    'id'   => 'mdjm-task-email-subject',
                                                    'name' => 'task_email_subject',
                                                    'value' => esc_html( $task['options']['email_subject'] ),
												) );
                                                ?>
                                            </div>
                                        </div>
                                        <div class="column-container email-options">
                                            <div class="column">
                                                <p><strong><?php esc_html_e( 'Email From:', 'mobile-dj-manager' ); ?></strong>
                                                <br />
                                                <?php
                                                echo MDJM()->html->select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                    'options' => array(
                                                        'admin'    => esc_html__( 'System Administrator', 'mobile-dj-manager' ),
                                                        'employee' => esc_html__( 'Primary Employee', 'mobile-dj-manager' ),
                                                    ),
														   'name' => 'task_email_from',
														   'id'   => 'mdjm-task-email-from',
														   'selected' => esc_attr( $task['options']['email_from'] ),
												) );
                                                ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div><!-- #mdjm-task-email-options -->

						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #mdjm-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'mdjm_view_task_details_form_bottom', $id ); ?>
		<?php wp_nonce_field( 'mdjm_update_task_details_nonce', 'mdjm_task_nonce' ); ?>
		<input type="hidden" name="mdjm_task_id" value="<?php echo esc_attr( $id ); ?>"/>
        <input type="hidden" name="mdjm-action" value="update_task_details"/>
	</form>
	<?php do_action( 'mdjm_view_task_details_after', $id ); ?>
</div><!-- /.wrap -->

	<?php

} // mdjm_render_single_task_view
