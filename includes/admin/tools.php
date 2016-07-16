<?php
/**
 * Tools
 *
 * Functions used for displaying MDJM tools menu page.
 *
 * @package     MDJM
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Tools
 *
 * Display the tools page.
 *
 * @since       1.4
 * @return      void
 */
function mdjm_tools_page()	{

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'api_keys';

	?>
    <div class="wrap">
		<h1 class="nav-tab-wrapper">
			<?php
			foreach( mdjm_get_tools_page_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'mdjm-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			?>
		</h1>
        <div class="tab-container">
        	<?php do_action( 'mdjm_tools_tab_' . $active_tab ); ?>
        </div>
    </div>
    <?php

} // mdjm_tools_page

/**
 * Define the tabs for the tools page.
 *
 * @since	1.4
 * @return	array
 */
function mdjm_get_tools_page_tabs()	{

	$tabs = array(
		'api_keys' => __( 'API Keys', 'mobile-dj-manager' )
	);

	return apply_filters( 'mdjm_tools_page_tabs', $tabs );

} // mdjm_get_tools_page_tabs

/**
 * Display the users API Keys
 *
 * @since	1.4
 * @return	void
 */
function mdjm_tools_api_keys_display()	{

	if( ! mdjm_employee_can( 'manage_mdjm' ) ) {
		return;
	}

	do_action( 'mdjm_tools_api_keys_before' );

	require_once( MDJM_PLUGIN_DIR . '/includes/admin/class-mdjm-api-keys-table.php' );

	$api_keys_table = new MDJM_API_Keys_Table();
	$api_keys_table->prepare_items();
	$api_keys_table->display();

	do_action( 'mdjm_tools_api_keys_after' );

} // mdjm_tools_api_keys_display
add_action( 'mdjm_tools_tab_api_keys', 'mdjm_tools_api_keys_display' );
