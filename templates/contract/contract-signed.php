<?php
/**
 * This template is used to display the contract page content.
 *
 * @version         1.0
 * @author          Mike Howard
 * @since           1.3
 * @content_tag     client
 * @content_tag     event
 * @shortcodes      Not Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/contract/contract.php
 */
global $mdjm_event;
?>

<div id="mdjm-contract-wrapper">
	<?php do_action( 'mdjm_pre_contract', $mdjm_event->ID ); ?>

	<div id="mdjm-contract-header">

        <?php do_action( 'mdjm_print_notices' ); ?>

        <p class="head-nav"><a href="{event_url}"><?php printf( esc_html__( 'Back to %s', 'mobile-dj-manager' ), esc_html( mdjm_get_label_singular() ) ); ?></a></p>

    	<?php do_action( 'mdjm_pre_contract_header', $mdjm_event->ID ); ?>

        <p>
        <?php 
        printf( esc_html__( 'The contract for your %1$s taking place on %2$s is displayed below.', 'mobile-dj-manager' ),
        esc_html( mdjm_get_label_singular( true ) ), '{event_date}' ); 
		?>
                </p>

        <p class="mdjm-alert mdjm-alert-success"><span style="font-weight: bold;"><?php esc_html_e( 'Your contract is signed', 'mobile-dj-manager' ); ?></span><br />
            <?php 
            printf( esc_html__( 'Signed on %1$s by %2$s with password verification', 'mobile-dj-manager' ),
                '{contract_date}',
            '{contract_signatory}' ); 
			?>
                <br />
            <?php printf( esc_html__( 'IP address recorded as: %s', 'mobile-dj-manager' ), '{contract_signatory_ip}' ); ?></p>

    	<?php do_action( 'mdjm_pre_contract_content', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-contract-header -->

    <hr />
    <div id="mdjm-contract-content">

    	<?php do_action( 'mdjm_pre_contract_content', $mdjm_event->ID ); ?>

        <?php echo mdjm_show_contract( $mdjm_event->get_contract(), $mdjm_event ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <?php do_action( 'mdjm_post_contract_footer', $mdjm_event->ID ); ?>

    </div><!-- end mdjm-contract-content -->
    <hr />

    <div id="mdjm-contract-footer">
    	<?php do_action( 'mdjm_pre_contract_footer', $mdjm_event->ID ); ?>

        <p class="mdjm-alert mdjm-alert-success"><span style="font-weight: bold;"><?php esc_html_e( 'Your contract is signed', 'mobile-dj-manager' ); ?></span><br />
            <?php 
            printf( esc_html__( 'Signed on %1$s by %2$s with password verification', 'mobile-dj-manager' ),
                '{contract_date}',
            '{contract_signatory}' ); 
			?>
                <br />
            <?php printf( esc_html__( 'IP address recorded as: %s', 'mobile-dj-manager' ), '{contract_signatory_ip}' ); ?></p>

        <?php do_action( 'mdjm_post_contract_footer', $mdjm_event->ID ); ?>
    </div><!-- end mdjm-contract-footer -->

</div><!-- end mdjm-contract-wrapper -->
