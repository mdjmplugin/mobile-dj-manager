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
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Generate the availability action links.
 *
 * @since   1.5.6
 * @return  arr     Array of action links
 */
function mdjm_get_availability_page_action_links()    {

    $actions = array();

	$actions['add_absence'] = '<a id="mdjm-add-absence-action" href="#" class="toggle-add-absence-section">' . __( 'Show absence form', 'mobile-dj-manager' ) . '</a>';

    $actions['availabiility_check'] = '<a id="mdjm-check-availabilty-action" href="#" class="toggle-availability-checker-section">' . __( 'Show availability checker', 'mobile-dj-manager' ) . '</a>';

    $actions = apply_filters( 'mdjm_availability_page_actions', $actions );

    return $actions;
} // mdjm_get_availability_page_action_links

/**
 * Generate the availability page
 *
 * @since	1.5.6
 * @return	string
 */
function mdjm_availability_page()	{
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
								<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
							</span>
						</div>

						<div class="mdjm-repeatable-row-standard-fields">
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
 * @since	1.5.6
 * @return	string
 */
function mdjm_render_availability_page_checker()	{
	$employee_id = get_current_user_id();
	$artist      = esc_attr( mdjm_get_option( 'artist' ) );

	mdjm_insert_datepicker(
		array(
			'id'       => 'display_date',
			'altfield' => 'check_date',
			'mindate'  => 'today'
		)
	); ?>

	<div class="mdjm-availability-checker-fields">
		<div id="mdjm-pending-notice" class="notice"></div>
		<div class="mdjm-availability-check-date">
			<span class="mdjm-repeatable-row-setting-label">
				<?php _e( 'Date', 'mobile-dj-manager' );?>
			</span>

			<?php echo MDJM()->html->text( array(
				'name'     => 'display_date',
				'id'       => 'display_date',
				'class'    => 'mdjm_date'
			) ); ?>
			<?php echo MDJM()->html->hidden( array(
				'name'  => 'check_date',
				'id'    => 'check_date'
			) ); ?>
		</div>

		<?php if ( mdjm_employee_can( 'manage_employees' ) ) : ?>
			<div class="mdjm-event-primary-employee">
				<span class="mdjm-repeatable-row-setting-label">
					<?php printf( '%s', $artist ); ?>
				</span>

				<?php echo MDJM()->html->employee_dropdown( array(
					'name'        => 'check_employee',
					'selected'    => $employee_id,
					'group'       => mdjm_is_employer() ? true : false,
					'chosen'      => true,
					'placeholder' => __( 'Checking all employees', 'mobile-dj-manager'  ),
					'multiple'    => true,
					'selected'    => array()
				) ); ?>
			</div>

			<div class="mdjm-event-primary-employee">
				<span class="mdjm-repeatable-row-setting-label">
					<?php _e( 'Roles', 'mobile-dj-manager' ); ?>
				</span>

				<?php echo MDJM()->html->roles_dropdown( array(
					'name'             => 'check_roles',
					'chosen'           => true,
					'placeholder'      => __( 'Checking all roles', 'mobile-dj-manager' ),
					'multiple'         => true,
					'selected'         => array()
				) ); ?>
			</div>
		<?php else : ?>
			<?php echo MDJM()->html->hidden( array(
				'name'  => 'check_employee',
				'value' => $employee_id
			) ); ?>
		<?php endif; ?>
		<br>
		<p><span class="mdjm-event-worker-add">
			<a id="check-availability" class="button button-secondary">
				<?php _e( 'Check Availability', 'mobile-dj-manager' ); ?>
			</a>
		</span></p>
	</div>
	<?php
} // mdjm_render_availability_page_checker
add_action( 'mdjm_availability_page_standard_sections', 'mdjm_render_availability_page_checker', 5 );

/**
 * Displays the availability calendar
 *
 * @since	1.5.6
 * @return	string
 */
function mdjm_render_availability_page_calendar()	{
	?>
	<div id="mdjm-calendar"></div>
	<?php
} // mdjm_render_availability_page_calendar
add_action( 'mdjm_availability_page_standard_sections', 'mdjm_render_availability_page_calendar' );
