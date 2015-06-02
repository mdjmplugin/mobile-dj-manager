<?php
/*
 * class-mdjm-widgets.php
 * The MDJM Widget class
 *
 * @since 1.1.3
 */
 
	class MDJM_Widgets	{
		/*
		 * The constructor
		 *
		 *
		 *
		 */
		public function __construct()	{
			global $mdjm_settings, $pagenow;
			/* -- Widgets for the WordPress main dashboard -- */
			if( $pagenow == 'index.php' && isset( $mdjm_settings['main']['show_dashboard'] ) )
				add_action( 'wp_dashboard_setup', array( &$this, 'wp_dashboard' ) );
		} // __construct
		
		/*
		 * Initialise the widgets for the main WP Dashboard
		 *
		 *
		 *
		 */
		public function wp_dashboard()	{
			wp_add_dashboard_widget( 'mdjm-overview', 'Mobile DJ Manager Overview', array( &$this, 'admin_overview' ) );
			wp_add_dashboard_widget( 'mdjm-availability', 'Mobile DJ Manager Availability', array( &$this, 'admin_availability' ) );
		} // wp_dashboard
		
		/*
		 * The Admin UI MDJM Overview widget
		 *
		 *
		 *
		 */
		public function admin_overview()	{
			echo "Test";
		} // admin_overview
		/*
		 * The Admin UI Availability widget
		 * Display next 7 days activity & availability checker form
		 *
		 *
		 */
		public function admin_availability()	{
			global $mdjm_settings;
			
			/* -- We want the datepicker -- */
			wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			
			?>
			<script type="text/javascript">
            <?php mdjm_jquery_datepicker_script( $args = array( '', 'check_date' ) ); ?>
            </script>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php /* Display Availability Overview */ ?>
            <?php get_availability_activity( 0, 0 ); ?>
            <?php /* Availability Check */ ?>
            <form name="availability-check" id="availability-check" method="post" action="<?php echo mdjm_get_admin_page( 'availability', 'str' ); ?>">
			<input type="hidden" name="check_employee" id="check_employee" value="<?php echo ( !current_user_can( 'administrator' ) ? 
					get_current_user_id() : 'all' ); ?>" />
            <tr>
            <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
            <td colspan="2"><input type="text" name="show_check_date" id="show_check_date" class="check_custom_date" required="required" style="font-size:12px" />&nbsp;&nbsp;&nbsp;
            <input type="hidden" name="check_date" id="check_date" />
            <?php submit_button( 'Check Date', 'primary small', 'submit', false, '' ); ?></td>
            </tr>
            </form>
            </table>
            <?php	
		} // admin_availability
		
	} // MDJM_Widgets
?>