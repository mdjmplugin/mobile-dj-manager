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

	</div><!-- end mdjm-compliance-header -->
	<hr />
	<div id="mdjm-compliance-content">
		<p>
		<?php
		printf(
			esc_html__( 'The documents you may need for your venue, %1, for your event taking place on %2$s are displayed below.', 'mobile-dj-manager' ), mdjm_get_event_venue_meta( $mdjm_event->get_venue_id(), 'name' ), '{event_date}'
		);
		?>
				</p>

		<?php if ( mdjm_get_option( 'enable_pli' ) === '1' ){ ?>
		<span style="font-weight: bold"><?php printf( esc_html__( 'Public Liability Insurance (PLI): ' ) ); ?></span><?php printf( '<a href="' . esc_html( mdjm_get_option( 'pli_cert_link', '' ) ) . '" download> Click Here</a>' ); ?><br/>
<?php }
?>
		
		<?php if ( mdjm_get_option( 'enable_pat' ) === '1' ){ ?>
		<span style="font-weight: bold"><?php printf( esc_html__( 'Portable Appliance Test (PAT): ' ) ); ?></span><?php printf( '<a href="' . esc_html( mdjm_get_option( 'pat_cert_link', '' ) ) . '" download> Click Here</a>' ); ?><br/>
<?php }
?>
	</div><!-- end mdjm-compliance-content -->
</div><!-- end mdjm-compliance-wrapper -->