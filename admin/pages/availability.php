<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) && !current_user_can( 'dj' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	global $mdjm_options, $current_user;
	// If recently updated, display the release notes
	f_mdjm_has_updated();

/*
* availability.php 
* 08/12/2014
* since 1.0
* Manage Availability
*/
	/* Process form submissions */
	if( isset( $_POST['submit'] ) )	{
		if( $_POST['submit'] == 'Add Entry' )	{
			if( !isset( $_POST['from_date'] ) || empty( $_POST['from_date'] ) )	{
				f_mdjm_update_notice( 'error', 'ERROR: You did not enter a <strong>From Date</strong>' );
			}
			elseif( !isset( $_POST['to_date'] ) || empty( $_POST['to_date'] ) )	{
				f_mdjm_update_notice( 'error', 'ERROR: You did not enter a <strong>To Date</strong>' );
			}
			elseif( date( 'Y-m-d', strtotime( $_POST['from_date'] ) ) < date( 'Y-m-d' ) )	{
				f_mdjm_update_notice( 'error', 'ERROR: The from date you entered is in the past' );	
			}
			else	{
				f_mdjm_add_holiday( $_POST );
			}
		}
		if( $_POST['submit'] == 'Check Date' )	{
			if( !isset( $_POST['check_date'] ) || empty( $_POST['check_date'] ) )	{
				f_mdjm_update_notice( 'error', 'ERROR: You did not enter a date to check' );	
			}
			elseif( date( 'Y-m-d', strtotime( $_POST['check_date'] ) ) < date( 'Y-m-d' ) )	{
				f_mdjm_update_notice( 'error', 'ERROR: The from date you entered is in the past' );	
			}
			else	{
				/* Run the availability check */
				if( isset( $_POST['check_employee'] ) && $_POST['check_employee'] != 'all' )	{
					$dj_avail = f_mdjm_available( $_POST['check_date'], $_POST['check_employee'] );
				}
				else	{
					$dj_avail = f_mdjm_available( $_POST['check_date'] );
				}
				
				/* Print the availability result */
				if( isset( $dj_avail ) )	{
					/* Check all DJ's */
					if ( $dj_avail !== false && $_POST['check_employee'] == 'all' )	{
						if( count( $dj_avail ) != 1 )	{
							$avail_message = count( $dj_avail ) . ' DJ\'s available on ' . date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ) . '"> Create event</a><br />';
						}
						else	{
							$avail_message = count( $dj_avail ) . ' DJ available on ' . date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ) . '"> Create event</a><br />';
						}
						$class = 'updated';
						?><ui><?php
						foreach( $dj_avail as $dj_detail )	{
							$dj = get_userdata( $dj_detail );
							$avail_message .= '<li>' . $dj->display_name . '</li>';
						}
						?></ui><?php
					}
					/* Single DJ Check */
					elseif ( $dj_avail !== false && $_POST['check_employee'] != 'all' )	{
						$dj = get_userdata( $_POST['check_employee'] );
						$class = 'updated';
						$avail_message = $dj->display_name . ' is available on ' . date( 'l, jS F Y', strtotime( $_POST['check_date'] ) ) . '<a href="' . admin_url( 'admin.php?page=mdjm-events&action=add_event_form' ) . '"> Create event</a><br />';
					}
					else	{
						$class = 'error';
						if( $_POST['check_employee'] == 'all' )	{
							$avail_message = 'No DJ\'s available on ' . date( 'l, jS F Y', strtotime( $_POST['check_date'] ) );
						}
						else	{
							$dj = get_userdata( $_POST['check_employee'] );
							$avail_message = $dj->display_name . ' is not available on ' . date( 'l, jS F Y', strtotime( $_POST['check_date'] ) );
						}
					}
					f_mdjm_update_notice( $class, $avail_message );
				}
			}
		}
	}

    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

	?>
	<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.from_custom_date').datepicker({
        dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
		altField  : '#from_date',
		altFormat : 'yy-mm-dd',
		firstDay: <?php echo get_option( 'start_of_week' ); ?>
        });
    });
	jQuery(document).ready(function($) {
        $('.to_custom_date').datepicker({
        dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
		altField  : '#to_date',
		altFormat : 'yy-mm-dd',
		firstDay: <?php echo get_option( 'start_of_week' ); ?>
        });
    });
	jQuery(document).ready(function($) {
        $('.check_custom_date').datepicker({
        dateFormat : '<?php f_mdjm_short_date_jquery(); ?>',
		altField  : '#check_date',
		altFormat : 'yy-mm-dd',
		firstDay: <?php echo get_option( 'start_of_week' ); ?>
        });
    });
    </script>
    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Availability</h2>
    <table class="widefat">
    <tr>
    <td width="75%">
    <table class="widefat">
    <form name="date-select-form" method="post">
    <tr>
    <th colspan="2"><select name="show_month" id="show_month">
    <?php
	$year = date( 'Y' );
	for( $i = 1; $i <= 12; $i++ )	{
		?>
        <option value="<?php echo $i; ?>"<?php if( isset( $_POST['show_year'] ) ) { selected( $i, $_POST['show_month'] ); } else { selected( $i, date( 'n' ) ); } ?>><?php echo date( 'F', strtotime( $year . '-' . $i . '-01' ) ); ?></option>
        <?php	
	}
	?>
    </select>&nbsp;&nbsp;&nbsp;
    <select name="show_year" id="show_year">
    <option value="2014"<?php if( isset( $_POST['show_year'] ) ) { selected( '2014', $_POST['show_year'] ); } else { selected( '2014', $_POST['show_year'] ); } ?>>2014</option>
    <option value="2015"<?php if( isset( $_POST['show_year'] ) ) { selected( '2015', $_POST['show_year'] ); } else { selected( '2015', $_POST['show_year'] ); } ?>>2015</option>
    <option value="2016"<?php if( isset( $_POST['show_year'] ) ) { selected( '2016', $_POST['show_year'] ); } else { selected( '2016', $_POST['show_year'] ); } ?>>2016</option>
    </select>&nbsp;&nbsp;&nbsp;
    <?php submit_button( 'Go', 'secondary', 'submit', false, '' ); ?>
    </th>
    </tr>
    </form>
    <?php
	if( isset( $_POST['show_month'], $_POST['show_year'], $_POST['submit'] ) && $_POST['submit'] == 'Go' )	{
		get_availability_activity( $_POST['show_month'], $_POST['show_year'] );
	}
	else	{
		get_availability_activity( date( 'm' ), date( 'Y' ) );
	}
	?>
    </table>
    </td>
    <td>
    <form name="holiday-quick-entry" method="post" action="">
    <table class="widefat">
    <tr>
    <th colspan="2" class="alternate"><strong>Quick Absence Entry</strong></th>
    </tr>
    <tr>
    <th scope="row" width="25%"><label for="employee">Employee:</label></th>
    <td><select name="employee" id="employee">
    <?php
	if( !current_user_can( 'administrator' ) )	{
		?>
		<option value="<?php echo get_current_user_id(); ?>"><?php echo $current_user->display_name; ?></option>
      	<?php
	}
	else	{
		$djs = f_mdjm_get_djs();
		foreach( $djs as $dj )	{
			?>
			<option value="<?php echo $dj->ID; ?>" <?php selected( $current_user->ID, $dj->ID ); ?>><?php echo $dj->display_name; ?></option>
			<?php
		}	
	}
	?>
    </select>
    </td>
    </tr>
    <th scope="row"><label for="show_from_date">From:</label></th>
    <td><input type="text" name="show_from_date" id="show_from_date" class="from_custom_date" required="required" /></td>
    <input type="hidden" name="from_date" id="from_date" />
    </tr>
    <th scope="row"><label for="show_to_date">To:</label></th>
    <td><input type="text" name="show_to_date" id="show_to_date" class="to_custom_date" required="required" /></td>
    <input type="hidden" name="to_date" id="to_date" />
    </tr>
    <th scope="row" valign="top">Notes:</th>
    <td><textarea name="notes" id="notes" class="all-options" placeholder="i.e. On holiday"></textarea></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><?php submit_button( 'Add Entry', 'primary small', 'submit', false, '' ); ?></td>
    </tr>
    </form>
    <form name="check-date" method="post" action="">
    <tr>
    <th colspan="2" class="alternate"><strong>Check DJ Availability</strong></th>
    </tr>
    <tr>
    <th scope="row" width="25%"><label for="show_check_date">Date:</label></th>
    <td><input type="text" name="show_check_date" id="show_check_date" class="check_custom_date" required="required" /></td>
    <input type="hidden" name="check_date" id="check_date" />
    </tr>
    <tr>
    <th scope="row"><label for="check_employee">Employee</label></th>
    <td><select name="check_employee" id="check_employee">
    <?php
	if( !current_user_can( 'administrator' ) )	{
		?>
		<option value="<?php echo get_current_user_id(); ?>"><?php echo $current_user->display_name; ?></option>
      	<?php
	}
	else	{
		?>
        <option value="all">All</option>
        <?php
		$djs = f_mdjm_get_djs();
		foreach( $djs as $dj )	{
			?>
			<option value="<?php echo $dj->ID; ?>" <?php if( isset( $_POST['check_employee'] ) ) { selected( $_POST['check_employee'], $dj->ID ); } ?>><?php echo $dj->display_name; ?></option>
			<?php
		}	
	}
	?>
    </select></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><?php submit_button( 'Check Date', 'primary small', 'submit', false, '' ); ?></td>
    </tr>
    </table>
    </form>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </div>
    
    <?php
	
?>