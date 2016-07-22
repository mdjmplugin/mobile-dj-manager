<?php

/**
 * Contains all metaboxe functions for the mdjm-package post type
 *
 * @package		MDJM
 * @subpackage	Equipment
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Remove unwanted metaboxes to for the mdjm-package post type.
 *
 * @since	1.3
 * @param
 * @return
 */
function mdjm_remove_package_meta_boxes()	{
	remove_meta_box( 'authordiv','mdjm-package','normal' );
	remove_meta_box( 'commentstatusdiv','mdjm-package','normal' );
	remove_meta_box( 'commentsdiv','mdjm-package','normal' );
	remove_meta_box( 'postcustom','mdjm-package','normal' );
	remove_meta_box( 'postexcerpt','mdjm-package','normal' );
	remove_meta_box( 'revisionsdiv','mdjm-package','normal' );
	remove_meta_box( 'slugdiv','mdjm-package','normal' );
	remove_meta_box( 'trackbacksdiv','mdjm-package','normal' );
} // mdjm_remove_package_meta_boxes
add_action( 'admin_head', 'mdjm_remove_package_meta_boxes' );

/**
 * Add the metaboxes for the mdjm-package post type.
 *
 * @since	1.3
 * @param	int		$post		Required: The post object (WP_Post).
 * @return
 */
function mdjm_add_package_meta_boxes( $post )	{
	
} // mdjm_add_package_meta_boxes
add_action( 'add_meta_boxes_mdjm-package', 'mdjm_add_package_meta_boxes' );

/**
 * Define and add the metaboxes for the mdjm-addon post type.
 * Apply the `mdjm_addon_add_metaboxes` filter to allow for filtering of metaboxes and settings.
 * Uses function_exists to verify the callback function exists.
 *
 * @since	1.4
 * @global	$post	WP_Post object.
 * @return	void
 */
function mdjm_register_addon_meta_boxes( $post )	{
	
	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$mdjm_addon_update = true;
	}

	$metaboxes = apply_filters( 'mdjm_addon_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-addon-availability-mb',
				'title'      => __( 'Availability', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_addon_metabox_availability_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			),
			array(
				'id'         => 'mdjm-addon-pricing-mb',
				'title'      => __( 'Pricing Options', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_addon_metabox_pricing_callback',
				'context'    => 'normal',
				'priority'   => 'high',
				'args'       => array(),
				'dependancy' => '',
				'permission' => ''
			)
		)
	);
	// Runs before metabox output
	do_action( 'mdjm_addon_before_metaboxes' );
	
	// Begin metaboxes
	foreach( $metaboxes as $metabox )	{
		// Dependancy check
		if ( ! empty( $metabox['dependancy'] ) && $metabox['dependancy'] === false )	{
			continue;
		}
		
		// Permission check
		if ( ! empty( $metabox['permission'] ) && ! mdjm_employee_can( $metabox['permission'] ) )	{
			continue;
		}
		
		// Callback check
		if ( ! is_callable( $metabox['callback'] ) )	{
			continue;
		}
				
		add_meta_box(
			$metabox['id'],
			$metabox['title'],
			$metabox['callback'],
			'mdjm-addon',
			$metabox['context'],
			$metabox['priority'],
			$metabox['args']
		);
	}
	
	// Runs after metabox output
	do_action( 'mdjm_addon_after_metaboxes' );
} // mdjm_register_addon_meta_boxes
add_action( 'add_meta_boxes_mdjm-addon', 'mdjm_register_addon_meta_boxes' );

/**
 * Returns default MDJM Addon meta fields.
 *
 * @since	1.4
 * @return	arr		$fields		Array of fields.
 */
function mdjm_addons_metabox_fields() {

	$fields = array(
			'_addon_employees',
			'_addon_restrict_date',
			'_addon_months',
			'_addon_variable_pricing',
			'_addon_price',
			'_addon_variable_prices'
		);

	return apply_filters( 'mdjm_addons_metabox_fields_save', $fields );
} // mdjm_addons_metabox_fields

/**
 * Output for the Addon Options meta box.
 *
 * @since	1.4
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_addon_metabox_availability_callback( $post )	{

	/*
	 * Output the items for the addon options metabox
	 * @since	1.4
	 * @param	int	$post_id	The addon post ID
	 */
	do_action( 'mdjm_addon_availability_fields', $post );

} // mdjm_addon_metabox_availability_callback

/**
 * Output for the Addon Pricing meta box.
 *
 * @since	1.4
 * @param	obj		$post	The post object (WP_Post).
 * @return
 */
function mdjm_addon_metabox_pricing_callback( $post )	{

	/*
	 * Output the items for the addon pricing metabox
	 * @since	1.4
	 * @param	int	$post_id	The addon post ID
	 */
	do_action( 'mdjm_addon_price_fields', $post );

	wp_nonce_field( 'mdjm-addon', 'mdjm_addon_meta_box_nonce' );

} // mdjm_addon_metabox_pricing_callback

/**
 * Output the addon availability employee_row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_availability_employee_row( $post )	{

	$employees_with = mdjm_get_employees_with_addon( $post->ID );

	if ( mdjm_is_employer() ) : ?>
        <div class="mdjm_field_wrap mdjm_form_fields">
            <div id="addon-employee-select">
                <p><label for="_addon_employees"><?php _e( 'Employees with this addon', '' ); ?></label><br />
                <?php echo MDJM()->html->employee_dropdown( array(
                    'name'             => '_addon_employees',
                    'selected'         => $employees_with ? $employees_with : array( 'all' ),
                    'show_option_none' => false,
                    'show_option_all'  => __( 'All Employees', 'mobile-dj-manager' ),
                    'group'            => true,
                    'multiple'         => true,
					'placeholder'      => __( 'Click to select employees', 'mobile-dj-manager' )
                ) ); ?></p>
            </div>
        </div>
    <?php else : ?>

	<input type="hidden" name="_addon_employees" value="<?php echo get_current_user_id(); ?>" />

	<?php endif;

} // mdjm_addon_metabox_availability_employee_row
add_action( 'mdjm_addon_availability_fields', 'mdjm_addon_metabox_availability_employee_row', 10 );

/**
 * Output the addon availability availability period row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_availability_period_row( $post )	{

	$restricted = mdjm_addon_is_restricted_by_date( $post->ID );
	$class      = $restricted ? '' : ' class="mdjm-hidden"';

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
        <div id="addon-date-restrict">
        	 <p><?php echo MDJM()->html->checkbox( array(
                'name'     => '_addon_restrict_date',
				'current'  => $restricted
            ) ); ?>
            <label for="_addon_restrict_date"><?php _e( 'Select if this add-on is only available during certain months of the year', '' ); ?></label></p>
        </div>
        
        <div id="mdjm-addon-month-selection"<?php echo $class; ?>>
        	 <p><label for="_addon_months"><?php _e( 'Select the months this add-on is available', '' ); ?></label><br />
                <?php echo MDJM()->html->month_dropdown( array(
					'name'     => '_addon_months',
					'selected' => mdjm_addon_get_months_available( $post->ID ),
					'fullname' => true,
					'multiple' => true
				) ); ?></p>
        </div>
    </div>

    <?php

} // mdjm_addon_metabox_availability_period_row
add_action( 'mdjm_addon_availability_fields', 'mdjm_addon_metabox_availability_period_row', 20 );

/**
 * Output the addon availability pricing options row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_pricing_options_row( $post )	{

	$month             = 1;
	$price             = mdjm_addon_get_price( $post->ID );
	$variable          = mdjm_addon_has_variable_prices( $post->ID );
	$prices            = mdjm_addon_get_variable_prices( $post->ID );
	$price_display     = $variable ? ' style="display:none;"' : '';
	$variable_display  = $variable ? ''                       : ' style="display:none;"';
	$currency_position = mdjm_get_option( 'currency_format', 'before' );

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
        <div id="addon-variable-price">
        	 <p><?php echo MDJM()->html->checkbox( array(
                'name'     => '_addon_variable_pricing',
				'current'  => $variable
            ) ); ?>
            <label for="_addon_variable_pricing"><?php _e( 'Enable variable pricing', '' ); ?></label></p>
        </div>

		<div id="mdjm-addon-regular-price-field" class="mdjm_pricing_fields"<?php echo $price_display; ?>>
		<?php
				$price_args = array(
					'name'        => '_addon_price',
					'value'       => isset( $price ) ? esc_attr( $price ) : '',
					'class'       => 'mdjm-currency',
					'placeholder' => mdjm_format_amount( '10.00' )
				);
			?>
			<p><label for="<?php echo $price_args['name']; ?>"><?php _e( 'Standard Price', '' ); ?></label><br />
			<?php if ( $currency_position == 'before' ) : ?>
				<?php echo mdjm_currency_filter( '' ); ?>
				<?php echo MDJM()->html->text( $price_args ); ?>
			<?php else : ?>
				<?php echo MDJM()->html->text( $price_args ); ?>
				<?php echo mdjm_currency_filter( '' ); ?>
			<?php endif; ?></p>
	
			<?php do_action( 'mdjm_addon_price_field', $post->ID ); ?>
		</div>
		<?php do_action( 'mdjm_after_addon_price_field', $post->ID ); ?>
    </div>

	<div id="mdjm-addon-variable-price-fields" class="mdjm_pricing_fields" <?php echo $variable_display; ?>>
		<input type="hidden" id="mdjm_variable_prices" class="mdjm_variable_prices_name_field" value=""/>
        <div id="mdjm_price_fields" class="mdjm_meta_table_wrap">
			<table class="widefat mdjm_repeatable_table">
            	<thead>
					<tr>
						<th style="width: 50px;"><?php _e( 'Month', 'mobile-dj-manager' ); ?></th>
						<th style="width: 100px;"><?php _e( 'Price', 'mobile-dj-manager' ); ?></th>
						<?php do_action( 'mdjm_addon_price_table_head', $post->ID ); ?>
                        <th style="width: 2%"></th>
					</tr>
				</thead>
                <tbody>
                	<?php
						if ( ! empty( $prices ) ) :
							foreach ( $prices as $key => $value ) :
								$name   = isset( $value['name'] )   ? $value['name']   : '';
								$amount = isset( $value['amount'] ) ? $value['amount'] : '';
								$index  = isset( $value['index'] )  ? $value['index']  : $key;
								$args = apply_filters( 'mdjm_price_row_args', compact( 'name', 'amount' ), $value );
								?>
								<tr class="mdjm_variable_prices_wrapper mdjm_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
									<?php do_action( 'mdjm_render_addon_price_row', $key, $args, $post->ID, $index ); ?>
								</tr>
							<?php
							endforeach;
						else :
					?>
						<tr class="mdjm_variable_prices_wrapper mdjm_repeatable_row" data-key="1">
							<?php do_action( 'mdjm_render_addon_price_row', 1, array(), $post->ID, 1 ); ?>
						</tr>
					<?php endif; ?>

                    <tr>
						<td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
							<a class="button-secondary mdjm_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add New Price', 'mobile-dj-manager' ); ?></a>
						</td>
					</tr>

                </tbody>
            </table>
        </div>
    </div>

    <?php

} // mdjm_addon_metabox_pricing_options_row
add_action( 'mdjm_addon_price_fields', 'mdjm_addon_metabox_pricing_options_row', 10 );

/**
 * Individual Price Row
 *
 * Used to output a table row for each price associated with an add-on.
 * Can be called directly, or attached to an action.
 *
 * @since 1.3.9
 *
 * @param	int	$key
 * @param	arr	$args
 * @param	int $post_id
 * @param	int	$index
 */
function mdjm_addon_metabox_price_row( $key, $args, $post_id, $index ) {

	$defaults = array(
		'name'   => null,
		'amount' => null
	);

	$args = wp_parse_args( $args, $defaults );

	$currency_position = mdjm_get_option( 'currency_format', 'before' );

?>
	<td>
		<?php echo MDJM()->html->month_dropdown( array(
			'name'        => '_addon_variable_prices[' . $key . '][months]',
			'selected'    => '',
			'fullname'    => true,
			'multiple'    => true,
			'chosen'      => true,
			'placeholder' => __( 'Select Months', 'mobile-dj-manager' )
		) ); ?>
	</td>

	<td>
		<?php
			$price_args = array(
				'name'        => '_addon_variable_prices[' . $key . '][amount]',
				'value'       => $args['amount'],
				'placeholder' => mdjm_format_amount( 10.00 ),
				'class'       => 'mdjm-price-field'
			);
		?>

		<?php if( $currency_position == 'before' ) : ?>
			<span><?php echo mdjm_currency_filter( '' ); ?></span>
			<?php echo MDJM()->html->text( $price_args ); ?>
		<?php else : ?>
			<?php echo MDJM()->html->text( $price_args ); ?>
			<?php echo mdjm_currency_filter( '' ); ?>
		<?php endif; ?>
	</td>

	<?php do_action( 'mdjm_addon_price_table_row', $post_id, $key, $args ); ?>

	<td>
		<a href="#" class="mdjm_remove_repeatable" data-type="price" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
	</td>
	<?php
}
add_action( 'mdjm_render_addon_price_row', 'mdjm_addon_metabox_price_row', 10, 4 );
