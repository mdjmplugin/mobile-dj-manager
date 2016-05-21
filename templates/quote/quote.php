<?php
/**
 * This template is used to display the online quote page content.
 *
 * @version			1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		client
 * @content_tag		event
 * @shortcodes		Supported
 * @global			$mdjm_event		MDJM Event object
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/quote/quote.php
 */
global $mdjm_event;
?>

<div id="mdjm-quote-wrapper">
	<?php do_action( 'mdjm_pre_quote', $mdjm_event->ID ); ?>
	
	<div id="mdjm-quote-header">
    	
        <?php do_action( 'mdjm_print_notices' ); ?>
        
        <p class="head-nav"><a href="{event_url}"><?php  printf( __( 'Back to %s', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></a></p>
        
    	<?php do_action( 'mdjm_pre_quote_header', $mdjm_event->ID ); ?>

	</div><!-- end mdjm-quote-header -->
    
    <div id="mdjm-quote-content">
    
    	<?php do_action( 'mdjm_pre_quote_content', $mdjm_event->ID ); ?>
        
        <p class="head-nav"><?php echo mdjm_display_book_event_button( $mdjm_event->ID ); ?></p>
        
        <?php echo mdjm_display_quote( $mdjm_event->ID ); ?>
        
        <p class="head-nav"><?php echo mdjm_display_book_event_button( $mdjm_event->ID ); ?></p>
        
        <?php do_action( 'mdjm_post_quote_content', $mdjm_event->ID ); ?>
    
    </div><!-- end mdjm-quote-content -->
    <hr />
    
    <div id="mdjm-quote-footer">
    	<?php do_action( 'mdjm_pre_quote_footer', $mdjm_event->ID ); ?>
                        
        <?php do_action( 'mdjm_post_quote_footer', $mdjm_event->ID ); ?>
    </div><!-- end mdjm-quote-footer -->
	
</div><!-- end mdjm-quote-wrapper -->