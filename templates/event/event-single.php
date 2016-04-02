<?php
/**
 * This template is used to display the details of a single event to the client.
 *
 * @version 		1.0
 * @author			Mike Howard
 * @since			1.3
 * @content_tag		{client_*}
 * @content_tag		{event_*}
 * @shortcodes		Supported
 *
 * Do not customise this file!
 * If you wish to make changes, copy this file to your theme directory /theme/mdjm-templates/event/event-single.php
 */
global $mdjm_event;
?>
<?php do_action( 'mdjm_pre_event_detail', $mdjm_event->ID, $mdjm_event ); ?>
<div id="post-<?php echo $mdjm_event->ID; ?>" class="mdjm-<?php echo $mdjm_event->post_status; ?>">
	
	<?php do_action( 'mdjm_print_notices' ); ?>
    
	<p><?php printf( __( 'Details of your %s taking place on %s are shown below.', 'mobile-dj-manager' ),
			mdjm_get_label_singular( true ), '{event_date}' ); ?></p>
            
    <p><?php printf( __( 'Please confirm the details displayed are correct or <a href="%s">contact us</a> with any adjustments.', 'mobile-dj-manager' ),
			'{contact_page}' ); ?></p>
                
    <?php
	/**
	 * Display event action buttons
	 */
	?>
	<div id="mdjm-singlevent-action-buttons">
		<?php $buttons = mdjm_get_event_action_buttons( $mdjm_event->ID, false ); ?>
        <?php $cells   = 3; // Number of cells ?>
        <?php $i       = 1; // Counter for the current cell ?>
        
        <?php do_action( 'mdjm_pre_event_action_buttons', $mdjm_event->ID, $mdjm_event ); ?>
        
        <table>
        	<?php foreach( $buttons as $button ) : ?>
        	
				<?php if( $i == 1 ) : ?>
                    <tr>
                <?php endif; ?><!-- endif( $i == 1 ) -->
                
                        <td><?php printf( '<a class="mdjm-action-button mdjm-action-button-%s" href="%s">' . $button['label'] . '</a>', mdjm_get_option( 'action_button_colour', 'blue' ), $button['url'] ); ?></td>
                        
                <?php if( $i == $cells ) : ?>
                    </tr>
                    <?php $i = 0; ?>
                <?php endif; ?><!-- endif( $i == $cells ) -->
                
                <?php $i++; ?>
                
            <?php endforeach; ?><!-- endforeach( $buttons as $button ) -->
        
			<?php // Write out empty cells to complete the table row ?>
			<?php if( $i != 1 ) : ?>
                
                <?php while( $i <= $cells ) : ?>
                    <td>&nbsp;</td>
                    <?php $i++; ?>
                    <?php if( $i == $cells ) : ?> </tr> <?php endif; ?>
                <?php endwhile; ?><!-- endwhile( $i <= $cells ) -->
            </tr>    
            <?php endif; ?><!-- endif( $i < $cells ) -->
        </table>
        
        <?php do_action( 'mdjm_post_event_action_buttons', $mdjm_event->ID, $mdjm_event ); ?>
        
    </div>

	<?php
	/**
	 * Display event details
	 */
	?>
    <?php do_action( 'mdjm_pre_event_details', $mdjm_event->ID, $mdjm_event ); ?>
    <div id="mdjm-singleevent-details">
        <table class="mdjm-singleevent-overview">
            <tr>
                <th colspan="4" class="mdjm-event-heading">{event_name} - {event_date}</th>
            </tr>
            
            <tr>
            	<th colspan="4"><?php _e( 'Status:', 'mobile-dj-manager' ); ?> {event_status}</th>
            </tr>
            
            <tr>
            	<th><?php printf( __( '%s Type:', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></th>
                <td>{event_type}</td>
                <th><?php printf(  __( 'Your %s:', 'mobile-dj-manager' ), mdjm_get_option( 'artist', __( 'DJ', 'mogile-dj-manager' ) ) ); ?></th>
                <td>{dj_fullname}</td>
            </tr>
            
            <tr>
            	<th><?php _e( 'Starts:', 'mobile-dj-manager' ); ?></th>
                <td>{start_time}</td>
                <th><?php _e( 'Completes:', 'mobile-dj-manager' ); ?></th>
                <td>{end_time} ({end_date})</td>
            </tr>
            
            <tr>
            	<th colspan="4"><?php _e( 'Package Details:', 'mobile-dj-manager' ); ?></th>
            </tr>
            
            <tr>
            	<th><?php _e( 'Package:', 'mobile-dj-manager' ); ?></th>
                <td>{event_package}</td>
                <th><?php _e( 'Add-ons:', 'mobile-dj-manager' ); ?></th>
                <td>{event_addons}</td>
            </tr>
            
            <tr>
            	<th colspan="4"><?php _e( 'Pricing', 'mobile-dj-manager' ); ?></th>
            </tr>
            
            <tr>
                <th colspan="4"><?php _e( 'Total Cost:', 'mobile-dj-manager' ); ?> {total_cost}<br />
					<?php echo mdjm_get_option( 'deposit_label', __( 'Deposit', 'mobile-dj-manager' ) ); ?>: {deposit} ({deposit_status})<br />
					<?php echo mdjm_get_option( 'balance_label', __( 'Balance Due', 'mobile-dj-manager' ) ) ?> <?php _e( 'Remaining', 'mobile-dj-manager' ); ?>: {balance}
                </th>
            </tr>
            
            <tr>
            	<th colspan="4"><?php _e( 'Your Details', 'mobile-dj-manager' ); ?></th>
            </tr>
            
            <tr>
            	<th><?php _e( 'Name:', 'mobile-dj-manager' ); ?></th>
                <td>{client_fullname}</td>
                <th><?php _e( 'Phone:', 'mobile-dj-manager' ); ?></th>
                <td>{client_primary_phone}</td>
            </tr>
            
            <tr>
            	<th><?php _e( 'Email:', 'mobile-dj-manager' ); ?></th>
                <td>{client_email}</td>
                <th><?php _e( 'Address:', 'mobile-dj-manager' ); ?></th>
                <td>{client_full_address}</td>
            </tr>
            
            <tr>
            	<th colspan="4"><?php _e( 'Venue Details', 'mobile-dj-manager' ); ?></th>
            </tr>
            
             <tr>
            	<th><?php _e( 'Venue:', 'mobile-dj-manager' ); ?></th>
                <td>{venue}</td>
                <th><?php _e( 'Address:', 'mobile-dj-manager' ); ?></th>
                <td>{venue_full_address}</td>
            </tr>
            
        </table>
    </div>
    
    <?php do_action( 'mdjm_post_event_details', $mdjm_event->ID, $mdjm_event ); ?>

</div>
<?php do_action( 'mdjm_post_event_detail', $mdjm_event->ID, $mdjm_event ); ?>
