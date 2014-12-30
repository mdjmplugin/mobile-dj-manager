<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
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
			f_mdjm_add_holiday( $_POST );	
		}
	}

    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	
	/* Calendar Code */
	$month = date( 'n' );
	$year = date( 'Y' );
	$first_day = mktime( 0, 1, 0, $month, 1, $year );
	$days_in_month = date( 't', $first_day );
	$first_day = date(' w', $first_day );

	?>
	<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.custom_date').datepicker({
        dateFormat : 'yy-mm-dd'
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
    <tr>
    <th colspan="7" class="alternate"><strong><?php echo date( 'F Y' ); ?></strong></th>
    </tr>
    <tr align="center">
    <th class="alternate"><center><strong>Sun</strong></center></th>
    <th class="alternate"><center><strong>Mon</strong></center></th>
    <th class="alternate"><center><strong>Tue</strong></center></th>
    <th class="alternate"><center><strong>Wed</strong></center></th>
    <th class="alternate"><center><strong>Thu</strong></center></th>
    <th class="alternate"><center><strong>Fri</strong></center></th>
    <th class="alternate"><center><strong>Sat</strong></center></th>
    </tr>
    <?php
	/* How many rows do we need? */
	$total_cells = $first_day + $days_in_month;
	if( $total_cells < 36 )	{
		$row_number = 5;
	}
	else {
		$row_number = 6;
	}
	$day_number = 1;
	
	/* Create the rows */
	for( $current_row = 1; $current_row <= $row_number; $current_row++ )	{
		if( $current_row == 1 )	{
			/* First row */
            ?><tr><?php
			for( $current_cell  = 0; $current_cell < 7; $current_cell++ )	{
				if( $current_cell == $first_day )	{
                /* First Day of the Month */
					?><td height="60"<?php if( $day_number == date( 'j' ) ) echo ' class="alternate"'; ?>><?php echo $day_number; ?></td><?php
					$day_number++;
				}
				else	{
					if( $day_number > 1 )	{
						/* First Day Passed so output Date */
						?><td height="60"<?php if( $day_number == date( 'j' ) ) echo ' class="alternate"'; ?>><?php echo $day_number; ?></td><?php
						$day_number++;
					}
					else	{
						/* First Day Not Reached so display blank cell */
						?><td height="60">&nbsp;</td><?php
					}
				}
			}
			?></tr><?php
		} // if( $current_row == 1 )
		else	{
			/* All other rows */
			?><tr><?php
			for( $current_cell = 0; $current_cell < 7; $current_cell++ )	{
				if( $day_number > $days_in_month )	{
					/* Days in month exceeded so display blank cell */
					?><td height="60"<?php if( $day_number == date( 'j' ) ) echo ' class="alternate"'; ?>>&nbsp;</td><?php
				}
				else	{
					?><td height="60"<?php if( $day_number == date( 'j' ) ) echo ' class="alternate"'; ?>><?php echo $day_number; ?></td><?php
					$day_number++;                            	
				}
			}
			?></tr><?php
		}
	}
	
	
	?>
    
    </table>
    </td>
    <td>
    <form name="holiday-quick-entry" method="post" action="">
    <table class="widefat">
    <tr>
    <th colspan="2" class="alternate"><strong>Quick Entry</strong></th>
    </tr>
    <tr>
    <th scope="row" width="25%">Employee:</th>
    <td><select name="employee" id="employee">
    <?php
	if( !current_user_can( 'administrator' ) )	{
		?>
		<option value="<?php get_current_user_id(); ?>"><?php echo $current_user->display_name; ?></option>
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
    <th scope="row">From:</th>
    <td><input type="text" name="from_date" id="from_date" class="custom_date" required="required" /></td>
    </tr>
    <th scope="row">To:</th>
    <td><input type="text" name="to_date" id="to_date" class="custom_date" required="required" /></td>
    </tr>
    <th scope="row" valign="top">Notes:</th>
    <td><textarea name="notes" id="notes" class="all-options" placeholder="i.e. On holiday"></textarea></td>
    </tr>
    <tr>
    <th>&nbsp;</th>
    <td><?php submit_button( 'Add Entry', 'primary small', 'submit', false, '' ); ?></td>
    </tr>
    </table>
    </form>
    </td>
    </tr>
    </table>
    </div>
    
    <?php
	
?>