<?php
/**
 * This template is used to display the contract page content.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Not Supported
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
        
        <p class="head-nav"><a href="{event_url}>"><?php  printf( __( 'Back to %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></a></p>
        
    	<?php do_action( 'mdjm_pre_contract_header', $mdjm_event->ID ); ?>
                        
        <p><?php printf( __( 'The contract for your %s taking place on %s is displayed below.', 'mobile-dj-manager' ),
                mdjm_get_label_singular( true ), '{event_date}' ); ?></p>
                
        <?php if( $mdjm_event->post_status == 'mdjm-contract' ) : ?>
        	
            <p><?php printf( __( 'When ready, please <a href="%s">scroll to the bottom</a> of this page to confirm your acceptance of the contractual terms and digitally sign the contract.', 'mobile-dj-manager' ),
                '#signature_form' ); ?></p>
                
            <p><?php _e( 'Once the contract is signed, you will receive a confirmation email from us.', 'mobile-dj-manager' ); ?></p>
            
        <?php else : ?>
            <p class="mdjm-contract-notready"><?php printf( __( 'You cannot yet sign your contract as you have not indicated that you would like to proceed with your %s. Please return to the <a href="%s">event details</a> screen to confirm that you wish to proceed.', 'mobile-dj-manager' ),
				mdjm_get_label_singular( true ), '{event_url}' ); ?></p>
            
        <?php endif; // endif( $mdjm_event->post_status == 'mdjm-contract' ) ?>
    
    	<?php do_action( 'mdjm_pre_contract_content', $mdjm_event->ID ); ?>
	</div><!-- end mdjm-contract-header -->
    
    <hr />
    <div id="mdjm-contract-content">
    
    	<?php do_action( 'mdjm_pre_contract_content', $mdjm_event->ID ); ?>
        
        <?php echo mdjm_show_contract( $mdjm_event->get_contract(), $mdjm_event ); ?>
        
        <?php do_action( 'mdjm_post_contract_footer', $mdjm_event->ID ); ?>
    
    </div><!-- end mdjm-contract-content -->
    <hr />
    
    <div id="mdjm-contract-footer">
    	<?php do_action( 'mdjm_pre_contract_footer', $mdjm_event->ID ); ?>
        <?php $disabled = ''; ?>
        
        <a id="signature_form"></a>
        <?php if( $mdjm_event->post_status != 'mdjm-contract' ) : ?>
            
            <?php $disabled = ' disabled="disabled"'; ?>
            <p class="mdjm-contract-notready"><?php printf( __( 'You cannot yet sign your contract as you have not indicated that you would like to proceed with your %s. Please return to the <a href="%s">event details</a> screen to confirm that you wish to proceed.', 'mobile-dj-manager' ),
            mdjm_get_label_singular( true ), '{event_url}' ); ?></p>
            
        <?php endif; // endif( $mdjm_event->post_status != 'mdjm-contract' ) ?>
            
        <div id="mdjm-contract-signature-form">
            <form name="mdjm-signature-form" id="mdjm-signature-form" method="post" action="<?php echo mdjm_get_current_page_url(); ?>">
                <?php wp_nonce_field( 'sign_contract', 'mdjm_nonce', true, true ); ?>
                <?php mdjm_action_field( 'sign_event_contract' ); ?>
                <input type="hidden" id="event_id" name="event_id" value="<?php echo $mdjm_event->ID; ?>" />
                
                <div class="row mdjm-contract-signatory-name">
                    <div class="col first-name">
                        <p><label for="mdjm_first_name"><?php _e( 'First Name:', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="mdjm_first_name" id="mdjm_first_name" data-placeholder="<?php _e( 'First Name', 'mobile-dj-manager' ); ?>" size="20"<?php echo $disabled; ?> /></p>
                    </div>
                    
                    <div class="last last-name">
                        <p><label for="mdjm_last_name"><?php _e( 'Last Name:', 'mobile-dj-manager' ); ?></label><br />
                            <input type="text" name="mdjm_last_name" id="mdjm_last_name" data-placeholder="<?php _e( 'Last Name', 'mobile-dj-manager' ); ?>" size="20"<?php echo $disabled; ?> /></p>
                    </div>
                </div>
                
                <div class="row mdjm-contract-signatory-terms">
                    <p><input type="checkbox" name="mdjm_accept_terms" id="mdjm_accept_terms" value="accept"<?php echo $disabled; ?> /> <label for="mdjm_accept_terms"><?php _e( 'I hereby confirm that I have read and accept the contract and its terms', 'mobile-dj-manager' ); ?></label></p>
                </div>
                
                <div class="row mdjm-contract-signatory-client">
                    <p><input type="checkbox" name="mdjm_confirm_client" id="mdjm_confirm_client" value="yes"<?php echo $disabled; ?> /> <label for="mdjm_confirm_client"><?php _e( 'I hereby confirm that the person named within the above contract is me and that all associated details are correct', 'mobile-dj-manager' ); ?></label></p>
                </div>
                
                <div class="row mdjm-contract-signatory-password">
                    <p><label for="mdjm_verify_password"><?php _e( 'Enter Your Password:', 'mobile-dj-manager' ); ?></label><br />
                        <input type="password" name="mdjm_verify_password" id="mdjm_verify_password" size="20"<?php echo $disabled; ?> /></p>
                </div>
                
                <div class="row mdjm-contract-sign">
                    <p><input type="submit" name="mdjm_submit_sign_contract" id="mdjm_submit_sign_contract" value="<?php _e( 'Sign Contract', 'mobile-dj-manager' ); ?>"<?php echo $disabled; ?> /></p>
                </div>
            </form>
        </div><!-- end mdjm-signature-form -->
                
        <?php do_action( 'mdjm_post_contract_footer', $mdjm_event->ID ); ?>
    </div><!-- end mdjm-contract-footer -->
	
</div><!-- end mdjm-contract-wrapper -->