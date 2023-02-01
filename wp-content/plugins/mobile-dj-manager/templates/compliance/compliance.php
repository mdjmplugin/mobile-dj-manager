<?php
/**
 * This template is used to display the compliance page content.
 *
 * @version         1.0
 * @author          Jack Mawhinney, Dan Porter
 * @since           1.6
 * @content_tag     client
 * @content_tag     event
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/compliance/compliance.php
 */

global $mdjm_event;
?>

<div id="mdjm-compliance-wrapper">
	<?php do_action( 'mdjm_pre_compliance', $mdjm_event->ID ); ?>

	<div id="mdjm-compliance-header">

		<?php do_action( 'mdjm_print_notices' ); ?>

		<p class="head-nav"><a href="{event_url}"><?php printf( esc_html__( 'Back to %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></a></p>

		<?php do_action( 'mdjm_pre_compliance_header', $mdjm_event->ID ); ?>

		<p>
		<?php
		printf(
			esc_html__( 'The documents you may need for your venue, %1$s, are below. Please pass these on to the venue co-ordinator.', 'mobile-dj-manager' ),
			esc_html( mdjm_get_event_venue_meta( $mdjm_event->get_venue_id(), 'name' ) )
		); ?>
			<br />
			<p>The documents will download to your device.</p>

	</div><!-- end mdjm-compliance-header -->
	<div class="row">

		<?php if ( mdjm_get_option( 'enable_pli' ) === '1' ){
		printf( __( '<a href="' . esc_html( mdjm_get_option( 'pli_cert_link', '' ) ) . '" class="btn" style="background-color: %s; color: %s" download>Public Liability Insurance (PLI)</a>' ),
			mdjm_get_option( 'action_button_colour', 'blue' ), mdjm_get_option( 'action_button_font_colour', '' ) );
		 } ?><br />
		<?php if ( mdjm_get_option( 'enable_pat' ) === '1' ){
		printf( __( '<br /><a href="' . esc_html( mdjm_get_option( 'pat_cert_link', '' ) ) . '" class="btn" style="background-color: %s; color: %s" download>Portable Appliance Testing (PAT)</a>' ),
			mdjm_get_option( 'action_button_colour', 'blue' ), mdjm_get_option( 'action_button_font_colour', '' ) );
		 }
?>
	</div><!-- end mdjm-compliance-content -->
</div><!-- end mdjm-compliance-wrapper -->