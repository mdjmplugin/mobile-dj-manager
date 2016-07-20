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

} // mdjm_addon_metabox_pricing_callback

/**
 * Output the addon availability employee_row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_availability_employee_row( $post )	{

	if ( mdjm_is_employer() ) : ?>
        <div class="mdjm_field_wrap mdjm_form_fields">
            <div id="addon-employee-select">
                <p><label for="_addon_employees"><?php _e( 'Employees with this addon:', 'mobile-dj-manager' ); ?></label><br />
                <?php echo MDJM()->html->employee_dropdown( array(
                    'name'             => '_addon_employees',
                    'selected'         => mdjm_get_employees_with_addon( $post->ID ),
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'chosen'           => true,
                    'group'            => true,
                    'multiple'         => true,
					'placeholder'      => __( 'Click to select employees', 'mobile-dj-manager' )
                ) ); ?> <span class="description"><?php _e( 'Leave empty for all', 'mobile-dj-manager' ); ?></span></p>
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
            <label for="_addon_restrict_date"><?php _e( 'Restrict availability by month?', 'mobile-dj-manager' ); ?></label></p>
        </div>
        
        <div id="mdjm-addon-month-selection"<?php echo $class; ?>>
        	 <p><label for="_addon_months"><?php _e( 'Months this add-on is available:', 'mobile-dj-manager' ); ?></label><br />
                <?php echo MDJM()->html->month_dropdown( '_addon_months', mdjm_addon_get_months_available( $post->ID ), true, true ); ?></p>
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

	$price              = mdjm_addon_get_price( $post->ID );
	$variable           = mdjm_addon_has_variable_pricing( $post->ID );
	$price_display      = $variable ? ' style="display:none;"' : '';
	$currency_position  = mdjm_get_option( 'currency_format', 'before' );

	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
        <div id="addon-monthly-price">
        	 <p><?php echo MDJM()->html->checkbox( array(
                'name'     => '_addon_monthly_pricing',
				'current'  => $variable
            ) ); ?>
            <label for="_addon_variable_pricing"><?php _e( 'Enable monthly pricing', 'mobile-dj-manager' ); ?></label></p>
        </div>

		<div id="mdjm-addon-regular-price-field" class="edd_pricing_fields"<?php echo $price_display; ?>>
		<?php
				$price_args = array(
					'name'  => '_addon_price',
					'value' => isset( $price ) ? esc_attr( $price ) : '',
					'class' => 'mdjm-currency'
				);
			?>
	
			<?php if ( $currency_position == 'before' ) : ?>
				<?php echo mdjm_currency_filter( '' ); ?>
				<?php echo MDJM()->html->text( $price_args ); ?>
			<?php else : ?>
				<?php echo MDJM()->html->text( $price_args ); ?>
				<?php echo mdjm_currency_filter( '' ); ?>
			<?php endif; ?>
	
			<?php do_action( 'mdjm_addon_price_field', $post->ID ); ?>
		</div>

		<?php do_action( 'mdjm_after_addon_price_field', $post->ID ); ?>

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
 * @param       $key
 * @param array $args
 * @param       $post_id
 */
function mdjm_addon_metabox_price_row( $key, $args = array(), $post_id, $index ) {

	$defaults = array(
		'name'   => null,
		'amount' => null
	);

	$args = wp_parse_args( $args, $defaults );

	$default_price_id  = mdjm_get_default_variable_price( $post_id );
	$currency_position = mdjm_get_option( 'currency_format', 'before' );

?>
	<td>
		<span class="edd_draghandle"></span>
		<input type="hidden" name="mdjm_monthly_prices[<?php echo $key; ?>][index]" class="mdjm_repeatable_index" value="<?php echo $index; ?>"/>
	</td>
	<td>
		<?php echo MDJM()->html->text( array(
			'name'  => '_addon_monthly_prices[' . $key . '][name]',
			'value' => esc_attr( $args['name'] ),
			'placeholder' => __( 'Option Name', 'mobile-dj-manager' ),
			'class' => 'mdjm_monthly_prices_name large-text'
		) ); ?>
	</td>

	<td>
		<?php
			$price_args = array(
				'name'  => '_addon_monthly_prices[' . $key . '][amount]',
				'value' => $args['amount'],
				'placeholder' => mdjm_format_amount( 9.99 ),
				'class' => 'mdjm-currency'
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
	<td class="mdjm_repeatable_default_wrapper">
		<label class="mdjm-default-price">
			<input type="radio" <?php checked( $default_price_id, $key, true ); ?> class="mdjm_repeatable_default_input" name="_mdjm_addon_default_price_id" value="<?php echo $key; ?>" />
			<span class="screen-reader-text"><?php printf( __( 'Set ID %s as default price', 'mobile-dj-manager' ), $key ); ?></span>
		</label>
	</td>

	<td>
		<span class="mdjm_price_id"><?php echo $key; ?></span>
	</td>

	<?php do_action( 'mdjm_addon_price_table_row', $post_id, $key, $args ); ?>

	<td>
		<a href="#" class="mdjm_remove_repeatable" data-type="price" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
	</td>
<?php
}
add_action( 'mdjm_addon_render_price_row', 'mdjm_addon_metabox_price_row', 10, 4 );
