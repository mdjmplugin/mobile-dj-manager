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

	$metaboxes = apply_filters( 'mdjm_event_add_metaboxes',
		array(
			array(
				'id'         => 'mdjm-addon-availability-mb',
				'title'      => __( 'Availability', 'mobile-dj-manager' ),
				'callback'   => 'mdjm_addon_metabox_availability_callback',
				'context'    => 'normal',
				'priority'   => '',
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
 * Output the addon availability employee_row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_availability_employee_row( $post )	{

	if ( mdjm_is_employer() ) : ?>
        <div class="mdjm_field_wrap mdjm_form_fields">
            <div class="mdjm_col col2">
                <label for="employees"><?php _e( 'Employees with this addon:', 'mobile-dj-manager' ); ?></label><br />
                <?php echo MDJM()->html->employee_dropdown( array(
                    'name'             => '_addon_employees',
                    'id'               => 'addon-employees',
                    'class'            => '',
                    'selected'         => mdjm_get_employees_with_addon( $post->ID ),
                    'show_option_none' => false,
                    'show_option_all'  => false,
                    'chosen'           => true,
                    'group'            => true,
                    'multiple'         => true,
					'placeholder'      => __( 'Click to select employees', 'mobile-dj-manager' )
                ) ); ?>
            </div>
        </div>
    <?php else : ?>

	<input type="hidden" name="_addon_employees" value="<?php echo get_current_user_id(); ?>" />

	<?php endif;

} // mdjm_addon_metabox_availability_employee_row
add_action( 'mdjm_addon_availability_fields', 'mdjm_addon_metabox_availability_employee_row', 10 );

/**
 * Output the addon availability employee_row
 *
 * @since	1.4
 * @param	int		$post		The WP_Post object.
 * @return	str
 */
function mdjm_addon_metabox_availability_period_row( $post )	{
	?>
    <div class="mdjm_field_wrap mdjm_form_fields">
        <div class="mdjm_col">
        	 <?php echo MDJM()->html->checkbox( array(
                'name'     => '_addon_restrict_date',
				'current'  => mdjm_addon_is_restricted_by_date( $post->ID )
            ) ); ?>
            <label for="employees"><?php _e( 'Restrict availability by month?', 'mobile-dj-manager' ); ?></label><br />
        </div>
    </div>
    <?php

} // mdjm_addon_metabox_availability_period_row
add_action( 'mdjm_addon_availability_fields', 'mdjm_addon_metabox_availability_period_row', 20 );
