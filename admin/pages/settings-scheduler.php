<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
		
/*
* settings-scheduler.php 
* 13/11/2014
* since 0.9.3
* Manage schedule tasks
*/
	require_once( sprintf( "%s/admin/includes/class/class-mdjm-cron.php", MDJM_PLUGIN_DIR ) );
	$mdjm_cron = new MDJM_Cron();
		
	global $mdjm_settings;
		
	
/* Check for form submission */
	if( isset( $_POST['submit'] ) )	{
		if( $_POST['submit'] == 'Save Changes' )	{
			$mdjm_schedules = get_option( 'mdjm_schedules' );
			if( $_POST['task_id'] )	{
				/* Reset all tasks to inactive */
				foreach( $mdjm_schedules as $task )	{
					if( $mdjm_schedules[$task['slug']]['slug'] != 'upload-playlists' )	{
						$mdjm_schedules[$task['slug']]['active'] = 'N';
					}
					else	{
						$mdjm_schedules[$task['slug']]['active'] = ( !empty( $mdjm_settings['playlist']['upload_playlists'] ) ? 'Y' : 'N' );
					}
				}
				/* Now activate the selected tasks */
				foreach( $_POST['task_id'] as $task_id )	{
					if( $mdjm_schedules[$task_id]['slug'] != 'upload-playlists' )	{
						$mdjm_schedules[$task_id]['active'] = 'Y';
						
						/* Update the Next Run time */
						if( $mdjm_schedules[$task_id]['nextrun'] == 'N/A' )	{
							$mdjm_schedules[$task_id]['nextrun'] = time();
						}
					}
				}
				/* For non-active tasks, set nextrun to N/A */
				foreach( $mdjm_schedules as $task )	{
					if( $mdjm_schedules[$task['slug']]['active'] != 'Y' )	{
						$mdjm_schedules[$task['slug']]['nextrun'] = 'N/A';
					}
				}
				update_option( 'mdjm_schedules', $mdjm_schedules );
				mdjm_update_notice( 'updated', 'Settings Saved' );
			}
		} // if( $_POST['submit'] == 'Save Changes' )
		elseif( $_POST['submit'] == 'Update Task' )	{
			$mdjm_schedules = get_option( 'mdjm_schedules' );
			
			/* Correctly set checkbox values if unchecked */
			if( !isset( $_POST['notify_admin'] ) || $_POST['notify_admin'] == '' )
				$_POST['notify_admin'] = 'N';
			if( !isset( $_POST['notify_dj'] ) || $_POST['notify_dj'] == '' )
				$_POST['notify_dj'] = 'N';
			if( !isset( $_POST['email_client'] ) || $_POST['email_client'] == '' )
				$_POST['email_client'] = 'N';
			
			/* Update array for task from input */
			if( isset( $_POST['name'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['name'] != $_POST['name'] )	{
					$mdjm_schedules[$_POST['slug']]['name'] = sanitize_text_field( $_POST['name'] );
				}
			}
			if( isset( $_POST['frequency'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['frequency'] != $_POST['frequency'] )	{
					$mdjm_schedules[$_POST['slug']]['frequency'] = $_POST['frequency'];
					/* Reset the next run time */
					if( $mdjm_schedules[$_POST['slug']]['active'] == 'Y' )	{
						$mdjm_schedules[$_POST['slug']]['nextrun'] = time();
					}
				}
			}
			if( isset( $_POST['desc'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['desc'] != $_POST['desc'] )	{
					$mdjm_schedules[$_POST['slug']]['desc'] = sanitize_text_field( $_POST['desc'] );
				}
			}
			if( isset( $_POST['notify_admin'] ) )	{
				if( !isset( $mdjm_schedules[$_POST['slug']]['options']['notify_admin'] )
					|| $mdjm_schedules[$_POST['slug']]['options']['notify_admin'] != $_POST['notify_admin'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['notify_admin'] = $_POST['notify_admin'];
				}
			}
			if( isset( $_POST['notify_dj'] ) )	{
				if( !isset( $mdjm_schedules[$_POST['slug']]['options']['notify_dj'] )
					|| $mdjm_schedules[$_POST['slug']]['options']['notify_dj'] != $_POST['notify_dj'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['notify_dj'] = $_POST['notify_dj'];
				}
			}
			
			if( isset( $_POST['execute_when'] ) && $_POST['execute_when'] != 'N/A' )	{
				$when = explode( ' ', $mdjm_schedules[$_POST['slug']]['options']['age'] );
				if( $mdjm_schedules[$_POST['slug']]['options']['run_when'] != $_POST['execute_when'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['run_when'] = $_POST['execute_when'];
				}
				if( $when[0] != $_POST['event_execute_time'] || $when[1] != $_POST['event_execute_period'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['age'] = $_POST['event_execute_time'] . ' ' . $_POST['event_execute_period'];	
				}
			}
			
			/* Email Options */
			if( isset( $_POST['email_client'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['options']['email_client'] != $_POST['email_client'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['email_client'] = $_POST['email_client'];
				}
			}
			if( isset( $_POST['email_template'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['options']['email_template'] != $_POST['email_template'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['email_template'] = $_POST['email_template'];
				}
			}
			if( isset( $_POST['email_subject'] ) )	{
				if( !isset( $mdjm_schedules[$_POST['slug']]['options']['email_subject'] ) 
				|| $mdjm_schedules[$_POST['slug']]['options']['email_subject'] != $_POST['email_subject'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['email_subject'] = sanitize_text_field( $_POST['email_subject'] );
				}
			}
			if( isset( $_POST['email_from'] ) )	{
				if( $mdjm_schedules[$_POST['slug']]['options']['email_from'] != $_POST['email_from'] )	{
					$mdjm_schedules[$_POST['slug']]['options']['email_from'] = $_POST['email_from'];
				}
			}
			
			/* Update the options table */
			update_option( 'mdjm_schedules', $mdjm_schedules );
			
			$class = 'updated';
			$message = 'The <strong>' . $mdjm_schedules[$_POST['slug']]['name'] . '</strong> task has been updated successfully';
			f_mdjm_update_notice( $class, $message );
		}
	}

/*
* f_mdjm_render_scheduler
* 13/11/2014
* since 0.9.3
* Displays the scheduled tasks
*/
	function f_mdjm_render_scheduler()	{
		global $mdjm_settings;
		$mdjm_schedules = get_option( MDJM_SCHEDULES_KEY );
		asort( $mdjm_schedules );
		?>
        <div class="wrap">
        <div id="icon-themes" class="icon32"></div>
        <h2>Automated Tasks <a href="<?php f_mdjm_admin_page( 'tasks' ); ?>&action=add_task" class="add-new-h2">Add New</a></h2></h2>
        <p><strong>Important Note:</strong> because of the way that WordPress handles scheduled tasks, the timing at which your tasks below run may differ from day to day dependant on activity to your website. If your website receives zero visits in a day after the time at which your tasks are scheduled to next run, those tasks will not run that day.</p>
		<p>In this instance, the tasks will be ran the the next time someone visits your website.</p>
        <hr />
        <h4>Tasks will next be checked for execution shortly after: 
			<?php echo get_date_from_gmt( date( 'Y-m-d H:i:s', wp_next_scheduled( 'mdjm_hourly_schedule' ) ), MDJM_TIME_FORMAT . ' \o\n ' . MDJM_SHORTDATE_FORMAT ); ?></h4>
        <form name="form-scheduler" id="form-scheduler" method="post" action="">
        <table class="widefat">
        <thead>
        <tr>
        <th class="row-title" width="5%">Active?</th>
        <th class="row-title" width="15%">Task</th>
        <th class="row-title" width="5%">Frequency</th>
        <th class="row-title" width="50%">Description</th>
        <th class="row-title" width="10%">Last Run</th>
        <th class="row-title" width="10%">Next Run</th>
        <th class="row-title" width="5%">Edit</th>
        </tr>
        </thead>
        <?php
		if( !$mdjm_schedules )	{
			?>
            <tr>
            <td colspan="5" class="form-invalid">You do not have any schedules defined yet</td>
            </tr>
            <?php	
		}
		else	{
			$i = 0;
			foreach( $mdjm_schedules as $schedule )	{
				$rowclass = '';
				if( $i == 1 )
					$rowclass = ' class="alt"';
				if( $schedule['active'] != 'Y' || $schedule['slug'] == 'upload-playlists' && empty( $mdjm_settings['playlist']['upload_playlists'] ) )
					$rowclass = ' class="form-invalid"';
				?>
                <tr<?php if( $rowclass != '' ) echo $rowclass; ?>>
                <?php
				if( $schedule['slug'] != 'upload-playlists' )	{
				?>
                    <td><input type="checkbox" name="task_id[]" id="task_id" value="<?php echo $schedule['slug']; ?>"<?php checked( $schedule['active'], 'Y' ); ?> /></td>
                <?php
				}
				else	{
					?>
                    <td><input type="checkbox" name="task_id[]" id="task_id" value="<?php echo $schedule['slug']; ?>"<?php checked( $schedule['active'], 'Y' ); ?> disabled="disabled" title="This setting is set on the General tab of the settings pages. it cannot be adjusted here" /></td>
                    <?php
				}
				?>
                <td><?php echo esc_attr( $schedule['name'] ); ?></td>
                <td><?php echo esc_attr( $schedule['frequency'] ); ?></td>
                <td><?php echo $schedule['desc']; ?></td>
                <td>
				<?php
                if( $schedule['lastran'] != 'Never' )	{
					echo get_date_from_gmt( date( 'Y-m-d H:i:s', $schedule['lastran'] ), 'd M Y' ) . '<br />';
					echo get_date_from_gmt( date( 'Y-m-d H:i:s', $schedule['lastran'] ), MDJM_TIME_FORMAT );
				}
				else	{
					echo esc_attr( $schedule['lastran'] );
				}
				?>
                </td>
                <td>
				<?php 
				if( $schedule['nextrun'] != 'N/A'
					&& $schedule['nextrun'] != 'Today' 
					&& $schedule['nextrun'] != 'Next Week'
					&& $schedule['nextrun'] != 'Next Month' )	{
						
					echo get_date_from_gmt( date( 'Y-m-d H:i:s', $schedule['nextrun'] ), 'd M Y' ) . '<br />';
					echo get_date_from_gmt( date( 'Y-m-d H:i:s', $schedule['nextrun'] ), MDJM_TIME_FORMAT );
				}
				elseif( $schedule['nextrun'] == 'N/A' )	{
						echo 'N/A';	
				}
				else	{
					esc_attr( $schedule['nextrun'] );
				}
				?>
                </td>
                <td>
                <a href="<?php echo f_mdjm_admin_page( 'tasks') . '&task_action=edit&slug=' . $schedule['slug']; ?>" title="Edit <?php echo esc_attr( $schedule['name'] ); ?> Scheduled Task">Edit</a>
                </td>
                </tr>
                <?php
				
				if( $i == 1 ) $i = 0;
				else $i++;
			}
		}
		?>
        <tfoot>
        <tr>
        <th class="row-title">Active?</th>
        <th class="row-title">Task</th>
        <th class="row-title">Frequency</th>
        <th class="row-title">Description</th>
        <th class="row-title">Last Run</th>
        <th class="row-title">Next Run</th>
        <th class="row-title">Edit</th>
        </tr>
        </tfoot>
        </table>
        <?php submit_button(); ?>
        </form>
        </div>
        <?php
	}
	
/*
* f_mdjm_edit_task
* 15/11/2014
* @since 0.9.3
* Edit given task
*/
	function f_mdjm_edit_task( $task )	{
		global $mdjm_settings;
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		if( isset( $mdjm_schedules[$task]['default'] ) && $mdjm_schedules[$task]['default'] == 'Y' )
			$ro = ' readonly';
		else
			$ro = '';
		?>
        <h2>Edit Task</h2>
        <form name="form_task_edit" method="post" action="<?php echo f_mdjm_admin_page( 'tasks' ) . '&task_updated=1'; ?>">
        <input type="hidden" name="slug" value="<?php echo $mdjm_schedules[$task]['slug']; ?>" />
        <table class="widefat">
        <tr>
        <td width="75%">
        <table class="widefat">
        <tr>
        <th colspan="4" class="alternate"><strong><?php echo $mdjm_schedules[$task]['name']; ?></strong></th>
        </tr>
        <tr>
        <th class="row-title" width="15%"><label for="name">Task Name:</label></th>
        <td width="35%"><input type="text" name="name" id="name" value="<?php echo $mdjm_schedules[$task]['name']; ?>"<?php echo $ro; ?> /></td>
        <th scope="row-title" width="15%"><label for="frequency">Frequency:</label></th>
        <td width="35%"><select name="frequency" id="frequency">
        <option value="Hourly" <?php selected( $mdjm_schedules[$task]['frequency'], 'Hourly' ); ?>>Hourly</option>
        <option value="Daily" <?php selected( $mdjm_schedules[$task]['frequency'], 'Daily' ); ?>>Daily</option>
        <option value="Weekly" <?php selected( $mdjm_schedules[$task]['frequency'], 'Weekly' ); ?>>Weekly</option>
        <option value="Monthly" <?php selected( $mdjm_schedules[$task]['frequency'], 'Monthly' ); ?>>Monthly</option>
        <option value="Yearly" <?php selected( $mdjm_schedules[$task]['frequency'], 'Yearly' ); ?>>Yearly</option>
        </select>
        </td>
        </tr>
        <tr>
        <th class="row-title"><label for="desc">Description:</label></th>
        <td colspan="3"><textarea name="desc" id="desc" cols="60" rows="2"><?php echo esc_attr( stripslashes( $mdjm_schedules[$task]['desc'] ) ); ?></textarea></td>
        </tr>
        <?php
		if( $mdjm_schedules[$task]['slug'] != 'upload-playlists' )	{
			?>
            <tr>
            <th scope="row-title">Notifications:</th>
            <td colspan="3"><label for="notify_admin">Admin</label> <input type="checkbox" name="notify_admin" id="notify_admin" value="Y"
				<?php checked( $mdjm_schedules[$task]['options']['notify_admin'], 'Y' ); ?> />&nbsp;&nbsp;&nbsp;
                <?php if( dj_can( 'see_deposit' ) )	{
					?>
                    <label for="notify_dj">DJ</label> <input type="checkbox" name="notify_dj" id="notify_dj" value="Y"
                    <?php checked( $mdjm_schedules[$task]['options']['notify_dj'], 'Y' ); ?> />&nbsp;
                    <?php
				}
				else	{
					echo '<input type="hidden" name="notify_dj" id="notify_dj" value="N"	/>' . "\r\n";
				}
				?>
                <span class="description">Who to notify when the task completes</span>
            </td>
            </tr>
			<?php
		}
		if( $mdjm_schedules[$task]['slug'] != 'upload-playlists' 
			&& $mdjm_schedules[$task]['slug'] != 'purge-clients' )	{
			?>
			<th scope="row-title"><label for="event_execute_time">Run:</label></th>
            <td colspan="3"><select name="event_execute_time" id="event_execute_time">
            <?php
			if( $mdjm_schedules[$task]['options']['run_when'] != '0' )	{
				$when = explode( ' ', $mdjm_schedules[$task]['options']['age'] );
			}
            if( $mdjm_schedules[$task]['slug'] != 'fail-enquiry' )	{
				?>
				<option value="N/A">N/A</option>
                <?php
			}
			?>
            <option value="1"<?php selected( $when[0], '1' ); ?>>1</option>
            <option value="2"<?php selected( $when[0], '2' ); ?>>2</option>
            <option value="3"<?php selected( $when[0], '3' ); ?>>3</option>
            <option value="4"<?php selected( $when[0], '4' ); ?>>4</option>
            <option value="5"<?php selected( $when[0], '5' ); ?>>5</option>
            <option value="6"<?php selected( $when[0], '6' ); ?>>6</option>
            <option value="7"<?php selected( $when[0], '7' ); ?>>7</option>
            <option value="8"<?php selected( $when[0], '8' ); ?>>8</option>
            <option value="9"<?php selected( $when[0], '9' ); ?>>9</option>
            <option value="10"<?php selected( $when[0], '10' ); ?>>10</option>
            <option value="11"<?php selected( $when[0], '11' ); ?>>11</option>
            <option value="12"<?php selected( $when[0], '12' ); ?>>12</option>
            </select>
            &nbsp;
            <select name="event_execute_period" id="event_execute_period">
            <?php
            if( $mdjm_schedules[$task]['slug'] != 'fail-enquiry' )	{
				?>
				<option value="N/A">N/A</option>
                <?php
			}
			?>
            <option value="HOUR"<?php selected( $when[1], 'HOUR' ); ?>>Hour(s)</option>
            <option value="DAY"<?php selected( $when[1], 'DAY' ); ?>>Day(s)</option>
            <option value="WEEK"<?php selected( $when[1], 'WEEK' ); ?>>Week(s)</option>
            <option value="MONTH"<?php selected( $when[1], 'MONTH' ); ?>>Month(s)</option>
            </select>
            &nbsp;
            <select name="execute_when" id="execute_when">
            <?php
            if( $mdjm_schedules[$task]['slug'] != 'fail-enquiry' && $mdjm_schedules[$task]['slug'] != 'request-deposit' )	{
				?>
				<option value="N/A">N/A</option>
                <?php
			}
			?>
            <option value="after_approval"<?php selected($mdjm_schedules[$task]['options']['run_when'], 'after_approval' ); ?>>After Contract Accepted</option>
            <?php
			if( $mdjm_schedules[$task]['slug'] != 'request-deposit' )	{
				?>
				<option value="event_created"<?php selected( $mdjm_schedules[$task]['options']['run_when'], 'event_created' ); ?>>After Enquiry Created</option>
				 <?php
			}
			if( $mdjm_schedules[$task]['slug'] != 'fail-enquiry' && $mdjm_schedules[$task]['slug'] != 'request-deposit' )	{
					?>
					<option value="before_event"<?php selected($mdjm_schedules[$task]['options']['run_when'], 'before_event' ); ?>>Before Event Starts</option>
					<option value="after_event"<?php selected($mdjm_schedules[$task]['options']['run_when'], 'after_event' ); ?>>After Event Finishes</option>
					<?php
			}
			?>
            </select>
            </td>
            </tr>
        	<?php
			if( $mdjm_schedules[$task]['slug'] == 'client-feedback' )	{
				echo '<tr>';
				echo '<td colspan="4">';
				echo '<span class="description">Note: This task will ignore all events completed over 1 month ago to avoid emailing clients whose events ended prior to that</span>';
				echo '</td>';
				echo '</tr>';
			}
		}
		if( $mdjm_schedules[$task]['slug'] != 'upload-playlists'
			&& $mdjm_schedules[$task]['slug'] != 'complete-events'
			&& $mdjm_schedules[$task]['slug'] != 'fail-enquiry'
			&& $mdjm_schedules[$task]['slug'] != 'purge-clients' )	{
		?>
			<tr>
			<th class="alternate" colspan="4"><strong>Email Options</strong></th>
			</tr>
			<tr>
            <th scope="row-title"><label for="email_client">Emails Client:</label></th>
            <td><input type="checkbox" name="email_client" id="email_client" value="Y"<?php checked( $mdjm_schedules[$task]['options']['email_client'], 'Y' ); ?> /></td>
			<th scope="row-title"><label for="email_template">Template:</label></th>
			<td>
			<select name="email_template" id="email_template">
			<option value="0"<?php selected( $mdjm_schedules[$task]['options']['email_template'], '0' ); ?>>None</option>
			<?php
			$email_template_args = array(
									'post_type' => 'email_template',
									'orderby' => 'name',
									'order' => 'ASC',
									'posts_per_page' => -1,
									);
			$email_template_query = new WP_Query( $email_template_args );
				if ( $email_template_query->have_posts() ) {
					while ( $email_template_query->have_posts() ) {
						$email_template_query->the_post();
						echo '<option value="' . get_the_id() . '"';
						if( $mdjm_schedules[$task]['options']['email_template'] == get_the_id() )	{
							echo ' selected="selected"';	
						}
						echo '>' . get_the_title() . '</option>' . "\n";	
					}
				}
				wp_reset_postdata();
			?>
			</select>
			</td>
            </tr>
			<tr>
			<th scope="row-title"><label for="email_subject">Subject</label></th>
			<td><input type="text" name="email_subject" id="email_subject" class="regular-text" value="<?php echo esc_attr( stripslashes( $mdjm_schedules[$task]['options']['email_subject'] ) ); ?>" /></td>
			<th scope="row-title"><label for="email_from">From:</label></th>
			<td>
			<select name="email_from" id="email_from">
			<option value="0" <?php selected( $mdjm_schedules[$task]['options']['email_from'], '0' ); ?>>N/A</option>
			<option value="admin" <?php selected( $mdjm_schedules[$task]['options']['email_from'], 'admin' ); ?>>Admin</option>
			<option value="dj" <?php selected( $mdjm_schedules[$task]['options']['email_from'], 'dj' ); ?>>DJ</option>
			</select>
			</td>
			</tr>
		<?php
		}
		?>
        <tr>
        <td><?php submit_button( 'Update Task', 'primary', 'submit', false ); ?></td>
        <td colspan="3"><a class="button-secondary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" title="<?php _e( 'Back' ); ?>"><?php _e( 'Back' ); ?></a></td>
        </tr>
        </table>
        </td>
        <td width="25%">
<?php /***** This is where we display the task overview */ ?>
        <table class="widefat">
        <tr>
        <th colspan="2" class="alternate"><strong>Task Overview</strong></th>
        </tr>
        <tr>
        <td width="40%">Status:</td>
        <td><?php if( $mdjm_schedules[$task]['active'] == 'Y' ) echo '<font style="color:#090">Active'; else echo '<font style="color:#F00">Inactive'; ?></font></td>
        </tr>
        <tr>
        <td>Frequency:</td>
        <td><?php echo $mdjm_schedules[$task]['frequency']; ?></td>
        </tr>
        <tr>
        <td>Last Run:</td>
        <td><?php if( $mdjm_schedules[$task]['lastran'] != 'Never' ) 
			echo get_date_from_gmt( date( 'Y-m-d H:i:s', $mdjm_schedules[$task]['lastran'] ), MDJM_TIME_FORMAT . ' d M Y' ); 
			else echo $mdjm_schedules[$task]['lastran']; ?>
        </td>
        </tr>
        <tr>
        <td>Next Run:</td>
        <td><?php if( $mdjm_schedules[$task]['nextrun'] != 'N/A' ) 
			echo get_date_from_gmt( date( 'Y-m-d H:i:s', $mdjm_schedules[$task]['nextrun'] ), MDJM_TIME_FORMAT . ' d M Y' ); 
			else echo $mdjm_schedules[$task]['nextrun']; ?>
        </td>
        </tr>
        <tr>
        <td>Total Runs:</td>
        <td><?php echo $mdjm_schedules[$task]['totalruns']; ?></td>
        </tr>
        <?php
		if( $mdjm_schedules[$task]['slug'] == 'upload-playlists' && $mdjm_schedules[$task]['active'] == 'Y' )	{
			?>
            <tr>
            <td>Entries Uploaded:</td>
            <td><?php f_mdjm_count_playlist_records_uploaded(); ?></td>
            </tr>
            <?php	
		}
		?>
        </table>
     	</td>
        </tr>
        </table>
        <?php
	} // f_mdjm_edit_task

/* Determine which function to execute */
	if( !isset( $_GET['task_action'] ) )	{
		f_mdjm_render_scheduler();
	}
	else	{
		if( $_GET['task_action'] == 'edit' && isset( $_GET['slug'] ) )	{
			f_mdjm_edit_task( $_GET['slug'] );	
		}
	}
	
?>