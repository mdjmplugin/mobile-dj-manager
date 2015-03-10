<?php
/**
 * playlist.php
 * 08/03/2015
 * @since 1.1.1
 * Renders the playlist page
 */

	/* -- No direct calls -- */
	defined('ABSPATH') or die("Direct access to this page is disabled!!!");
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_mdjm' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	/* -- Required Classes -- */
	if( !class_exists( 'WP_List_Table' ) ){
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	if( !class_exists( 'MDJM_PlayList_Table' ) ) {
		require_once( WPMDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-playlist.php' );
	}
	
	$playlist = new MDJM_PlayList_Table();
	
	/* -- Email the playlist -- */
	if( isset( $_POST['email_pl'] ) && $_POST['email_pl'] == 'Email Playlist' )	{
		$playlist->send_to_email( $_POST, $_GET );	
	}
	
	?>
    <div class="wrap">
    <h2>Event Playlist</h2>
    <hr />
    <form name="playlist_admin" method="post">
    <?php wp_nonce_field( 'playlist_admin-action' ); ?>
    
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
            
                <div id="post-body-content">
                    <?php $playlist->prepare_items(); ?>
                    <?php $playlist->display(); ?>
                    <a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back' ); ?>"><?php _e( 'Back' ); ?></a>
                </div> <!-- #post-body-content -->
    
                <div id="postbox-container-1" class="postbox-container">
                	<?php $playlist->playlist_header(); ?>
                    <br>
                    <table class="widefat">
                    <tr class="alternate">
                    <th colspan="2"><strong>Print this Playlist</strong></th>
                    </tr>
                    <tr>
                    <td>Repeat Headers:</td>
                    <td><input type="text" class="small-text" name="repeat" id="repeat" value="30" /> Rows</td>
                    </tr>
                    <tr>
                    <td>Order by:</td>
                    <td><select name="order_pl_by" id="order_pl_by">
                    <option value="date_added" selected="selected">Date Added</option>
                    <option value="artist">Artist Name</option>
                    <option value="song">Song Name</option>
                    <option value="play_when">When to Play</option>
                    </select></td>
                    </tr>
                    <tr>
                    <td colspan="2"><?php submit_button( 'Print Playlist', 'primary small', 'print_pl', false ); ?></td>
                    </tr>
                    </table>
                    <br>
                    <table class="widefat">
                    <tr class="alternate">
                    <th colspan="2"><strong>Email me this Playlist</strong></th>
                    </tr>
                    <tr>
                    <td>Repeat Headers:</td>
                    <td><input type="text" class="small-text" name="repeat" id="repeat" value="0" /> Rows</td>
                    </tr>
                    <tr>
                    <td>Order by:</td>
                    <td><select name="order_pl_by" id="order_pl_by">
                    <option value="date_added" selected="selected">Date Added</option>
                    <option value="artist">Artist Name</option>
                    <option value="song">Song Name</option>
                    <option value="play_when">When to Play</option>
                    </select></td>
                    </tr>
                    <tr>
                    <td colspan="2"><?php submit_button( 'Email Playlist', 'primary small', 'email_pl', false ); ?></td>
                    </tr>
                    </table>
                </div> <!-- #postbox-container-1 -->
    
                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes( '', 'normal' ,null ); ?>
                    <?php do_meta_boxes( '','advanced' ,null ); ?>
				</div> <!-- #postbox-container-2 -->
            </div> <!-- #post-body -->
        </div> <!-- #poststuff -->
        </form>
    </div> <!-- .wrap -->
    
    <?php
 
?>