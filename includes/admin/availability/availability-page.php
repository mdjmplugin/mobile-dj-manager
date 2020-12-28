<?php
/**
 * Availability Page
 *
 * @package     MDJM
 * @subpackage  Availability/Functions
 * @copyright   Copyright (c) 2018, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the availability action links.
 *
 * @since   1.5.6
 * @return  arr     Array of action links
 */
function mdjm_get_availability_page_action_links() {

    $actions = array();

	$actions['add_absence'] = '<a id="mdjm-add-absence-action" href="#" class="toggle-add-absence-section">' . __( 'Show absence form', 'mobile-dj-manager' ) . '</a>';

    $actions = apply_filters( 'mdjm_availability_page_actions', $actions );

    return $actions;
} // mdjm_get_availability_page_action_links

/**
 * Generate the availability page
 *
 * @since   1.5.6
 * @return  string
 */
function mdjm_availability_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div id="mdjm_availability_fields" class="mdjm_meta_table_wrap">

			<div class="widefat mdjm_repeatable_table">

				<div class="mdjm-availability-fields mdjm-repeatables-wrap">

					<div class="mdjm_availability_wrapper">

						<div class="mdjm-availability-row-header">
							<?php
							$actions = mdjm_get_availability_page_action_links();
							?>

							<span class="mdjm-repeatable-row-actions">
								<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>

						<div class="mdjm-repeatable-row-standard-fields">
                            <div id="mdjm-pending-notice" class="notice"></div>
							<?php do_action( 'mdjm_availability_page_standard_sections' ); ?>
						</div>
						<?php do_action( 'mdjm_availability_page_custom_sections' ); ?>
					</div>

				</div>

			</div>

		</div>
	</div><!--wrap-->
	<?php
} // mdjm_availability_page

/**
 * Displays the availability checker
 *
 * @since   1.5.6
 * @return  string
 */
function mdjm_render_availability_page_checker() {
	$employee_id = get_current_user_id();
	$artist      = esc_attr( mdjm_get_option( 'artist' ) );

	mdjm_insert_datepicker(
		array(
			'id'       => 'display_date',
			'altfield' => 'check_date',
			'mindate'  => 'today',
		)
	);
    ?>

	<div class="mdjm-availability-checker-fields">
		<div class="mdjm-availability-check-date">
			<span class="mdjm-repeatable-row-setting-label">
				<?php esc_html_e( 'Date', 'mobile-dj-manager' ); ?>
			</span>

			<?php
            echo MDJM()->html->text( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'name'  => 'display_date',
				'id'    => 'display_date',
				'class' => 'mdjm_date',
			) );
            ?>
			<?php
            echo MDJM()->html->hidden( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'name' => 'check_date',
				'id'   => 'check_date',
			) );
            ?>
		</div>

		<?php if ( mdjm_employee_can( 'manage_employees' ) ) : ?>
			<div class="mdjm-event-primary-employee">
				<span class="mdjm-repeatable-row-setting-label">
					<?php printf( '%s', esc_html( $artist ) ); ?>
				</span>

				<?php
                echo MDJM()->html->employee_dropdown( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'name'        => 'check_employee',
					'group'       => mdjm_is_employer(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'chosen'      => true,
					'placeholder' => esc_html__( 'Checking all employees', 'mobile-dj-manager' ),
					'multiple'    => true,
					'selected'    => array(),
				) );
                ?>
			</div>

			<div class="mdjm-event-primary-employee">
				<span class="mdjm-repeatable-row-setting-label">
					<?php esc_html_e( 'Roles', 'mobile-dj-manager' ); ?>
				</span>

				<?php
                echo MDJM()->html->roles_dropdown( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'name'        => 'check_roles',
					'chosen'      => true,
					'placeholder' => esc_html__( 'Checking all roles', 'mobile-dj-manager' ),
					'multiple'    => true,
					'selected'    => array(),
				) );
                ?>
			</div>
		<?php else : ?>
			<?php
            echo MDJM()->html->hidden( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'name'  => 'check_employee',
				'value' => esc_attr( $employee_id ),
			) );
            ?>
		<?php endif; ?>
		<br>
		<p><span class="mdjm-event-worker-add">
			<a id="check-availability" class="button button-secondary">
				<?php esc_html_e( 'Check Availability', 'mobile-dj-manager' ); ?>
			</a>
		</span></p>
	</div>
	<?php
} // mdjm_render_availability_page_checker
add_action( 'mdjm_availability_page_standard_sections', 'mdjm_render_availability_page_checker', 5 );

/**
 * Displays the availability calendar
 *
 * @since   1.5.6
 * @return  string
 */
function mdjm_render_availability_page_calendar() {
	?>
	<div id="mdjm-calendar"></div>
	<?php
} // mdjm_render_availability_page_calendar
add_action( 'mdjm_availability_page_standard_sections', 'mdjm_render_availability_page_calendar' );

/**
 * Displays the absence entry form
 *
 * @since   1.5.6
 * @return  string
 */
function mdjm_render_availability_absence_form() {
    $employee_id = get_current_user_id();
	$ampm        = 'H:i' != mdjm_get_option( 'time_format', 'H:i' ) ? true : false;

    mdjm_insert_datepicker(
		array(
			'id'       => 'display_absence_start',
			'altfield' => 'absence_start',
			'mindate'  => 'today',
		)
	);
    mdjm_insert_datepicker(
		array(
			'id'       => 'display_absence_end',
			'altfield' => 'absence_end',
		)
	);
    ?>
    <div id="mdjm-add-absence-fields" class="mdjm-availability-add-absence-sections-wrap">
        <div class="mdjm-custom-event-sections">
            <div class="mdjm-custom-event-section">
                <span class="mdjm-custom-event-section-title"><?php esc_html_e( 'Add Employee Absence', 'mobile-dj-manager' ); ?></span>
                <?php if ( mdjm_employee_can( 'manage_employees' ) ) : ?>
                    <span class="mdjm-employee-option">
                        <label class="mdjm-employee-id">
                            <?php esc_html_e( 'Employee', 'mobile-dj-manager' ); ?>
                        </label>
                        <?php
                        echo MDJM()->html->employee_dropdown( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'name'     => 'absence_employee_id',
                            'selected' => esc_attr( $employee_id ),
                            'group'    => mdjm_is_employer(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'chosen'   => true,
						) );
                        ?>
                    </span>
                <?php else : ?>
                    <?php
                    echo MDJM()->html->hidden( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'name'  => 'absence_employee_id',
                        'value' => esc_attr( $employee_id ),
					) );
                    ?>
                <?php endif; ?>

                <span class="mdjm-absence-start-option">
                    <label class="mdjm-absence-start">
                        <?php esc_html_e( 'From', 'mobile-dj-manager' ); ?>
                    </label>
                    <?php
                    echo MDJM()->html->text( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'name'  => 'display_absence_start',
                        'id'    => 'display_absence_start',
                        'class' => 'mdjm_date',
					) );
                    ?>
                    <?php
                    echo MDJM()->html->hidden( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'name' => 'absence_start',
                        'id'   => 'absence_start',
					) );
                    ?>
                </span>

                <span class="mdjm-absence-end-option">
                    <label class="mdjm-absence-end">
                        <?php esc_html_e( 'To', 'mobile-dj-manager' ); ?>
                    </label>
                    <?php
                    echo MDJM()->html->text( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'name'  => 'display_absence_end',
                        'id'    => 'display_absence_end',
                        'class' => 'mdjm_date',
					) );
                    ?>
                    <?php
                    echo MDJM()->html->hidden( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'name' => 'absence_end',
                        'id'   => 'absence_end',
					) );
                    ?>
                </span>

                <div class="mdjm-repeatable-option">
                    <span class="mdjm-absence-allday-option">
                        <label class="mdjm-absence-all-day">
                            <?php esc_html_e( 'All day?', 'mobile-dj-manager' ); ?>
                        </label>
                        <?php
                        echo MDJM()->html->checkbox( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'name'    => 'absence_all_day',
							'current' => true,
						) );
                        ?>
                    </span>
                </div>

				<span class="mdjm-absence-start-time-option mdjm-hidden">
					<label class="mdjm-absence-start-hour">
						<?php esc_html_e( 'Start time', 'mobile-dj-manager' ); ?>
					</label>
					<?php
                    echo MDJM()->html->time_hour_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'name' => 'absence_start_time_hr',
					) );
                    ?>
					<?php
                    echo MDJM()->html->time_minute_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'name' => 'absence_start_time_min',
					) );
                    ?>
					<?php if ( $ampm ) : ?>
						<?php
                        echo MDJM()->html->time_period_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'name' => 'absence_start_time_period',
						) );
                        ?>
					<?php endif; ?>
				</span>

				<span class="mdjm-absence-end-time-option">
					<label class="mdjm-absence-end-hour">
						<?php esc_html_e( 'End time', 'mobile-dj-manager' ); ?>
					</label>
					<?php
                    echo MDJM()->html->time_hour_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'name' => 'absence_end_time_hr',
					) );
                    ?>
					<?php
                    echo MDJM()->html->time_minute_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'name' => 'absence_end_time_min',
					) );
                    ?>
					<?php if ( $ampm ) : ?>
						<?php
                        echo MDJM()->html->time_period_select( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'name' => 'absence_end_time_period',
						) );
                        ?>
					<?php endif; ?>
				</span>

				<br>
				<span class="mdjm-absence-notes-option">
					<label class="mdjm-absence-notes">
						<?php esc_html_e( 'Notes', 'mobile-dj-manager' ); ?>
					</label>
					<?php
                    echo MDJM()->html->textarea( array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'name'        => 'absence_notes',
						'placeholder' => esc_html__( 'i.e. On holiday', 'mobile-dj-manager' ),
						'class'       => 'mdjm_form_fields',
					) );
                    ?>
				</span>

				<br>
				<span class="mdjm-absence-submit-option">
					<button id="add-absence" class="button button-secondary">
						<?php esc_html_e( 'Add Absence', 'mobile-dj-manager' ); ?>
					</button>
				</span>

            </div>
        </div>
    </div>
    <?php
} // mdjm_render_availability_absence_form
add_action( 'mdjm_availability_page_custom_sections', 'mdjm_render_availability_absence_form' );
