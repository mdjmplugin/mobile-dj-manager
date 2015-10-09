<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	f_mdjm_has_updated();
		
	if( isset( $_POST['submit'] ) )	{ /* Form Submitted */
		if( $_POST['submit'] == 'Add Category' )	{ /* Add new category */
			$cats = get_option( 'mdjm_cats' );
			$cat_id = sanitize_title_with_dashes( $_POST['category_name'] );
			$cats[$cat_id] = sanitize_text_field( $_POST['category_name'] );
			update_option( 'mdjm_cats', $cats );
			mdjm_update_notice( 'updated', 'Category Added Successfully' );
		}
		if( $_POST['submit'] == 'Delete Selected' )	{ /* Delete category */
			$cats = get_option( 'mdjm_cats' );
			foreach( $_POST['categories'] as $categories )	{
				unset( $cats[$categories] );
			}
			update_option( 'mdjm_cats', $cats );
			mdjm_update_notice( 'updated', 'Category Deleted Successfully' );
		}
		if( $_POST['submit'] == 'Add Item' )	{
			$items = get_option( 'mdjm_equipment' );
			$item_id = sanitize_title_with_dashes( $_POST['equip_name'] );
			
			if( isset( $items[$item_id] ) )
				$item_id = sanitize_title_with_dashes( $_POST['equip_name'] ) . '_';
				
			$djs_have = '';
			$i = 1;
			foreach( $_POST['djs'] as $this_dj ) {
				$djs_have .= $this_dj;
				if( $i != count( $_POST['djs'] ) ) $djs_have .= ',';
				$i++;
			}
			if( isset( $_POST['addon_avail'] ) && $_POST['addon_avail'] != 'Y' ) $_POST['addon_avail'] = 'N';
			$items[$item_id] = array( 
									sanitize_text_field( $_POST['equip_name'] ),
									$item_id,
									$_POST['equip_qty'],
									$_POST['equip_options'],
									sanitize_text_field( $_POST['equip_desc'] ),
									$_POST['equip_cat'],
									$_POST['addon_avail'],
									number_format( $_POST['addon_cost'], 2 ),
									$djs_have
								);
			update_option( 'mdjm_equipment', $items );
			mdjm_update_notice( 'updated', 'Item Added Successfully' );
		}
		/* Edit Item within Inventory */
		if( $_POST['submit'] == 'Update' )	{
			$equipment = get_option( 'mdjm_equipment' );
			$djs_have = '';
			$i = 1;
			if( !empty( $_POST['djs'] ) )	{
				foreach( $_POST['djs'] as $this_dj ) {
					$djs_have .= $this_dj;
					if( $i != count( $_POST['djs'] ) ) $djs_have .= ',';
					$i++;
				}
			}
			if( $_POST['addon_avail'] != 'Y' ) $_POST['addon_avail'] = 'N';
			unset( $equipment[$_POST['slug']] );
			$item_id = sanitize_title_with_dashes( $_POST['equip_name'] );
			if( !empty( $equipment[$item_id] ) )
				$item_id = sanitize_title_with_dashes( $_POST['equip_name'] ) . '_';
				
			$equipment[$item_id] = array(
									sanitize_text_field( $_POST['equip_name'] ),
									$item_id,
									$_POST['equip_qty'],
									$_POST['equip_options'],
									sanitize_text_field( $_POST['equip_desc'] ),
									$_POST['equip_cat'],
									$_POST['addon_avail'],
									number_format( $_POST['addon_cost'], 2 ),
									$djs_have
									);
			update_option( 'mdjm_equipment', $equipment );
			mdjm_update_notice( 'updated', 'Item Updated Successfully' );
		}
		/* Remove Item from Inventory */
		if( $_POST['submit'] == 'Remove' )	{
			$equipment = get_option( 'mdjm_equipment' );
			unset( $equipment[$_POST['slug']] );
			update_option( 'mdjm_equipment', $equipment );
			mdjm_update_notice( 'updated', 'Item Removed Successully' );
		}
	}
	
	function f_mdjm_equipment_settings_display()	{

		$cats = get_option( 'mdjm_cats' );
		asort( $cats );
		?>
        <div class="wrap">
	    <div id="icon-themes" class="icon32"></div>
        <h3>Equipment Categories</h3>
        <table class="widefat">
        <form name="form-categories" id="form-categories" method="post">
        <?php
        if( !$cats )	{
			echo '<tr>';
			echo '<td>You do not have any categories defined yet. Add your first one below.</td>';	
			echo '</tr>';
		}
		else	{
			echo '<tr>';
			echo '<td style="vertical-align:middle">';
			echo '<select name="categories[]" multiple="multiple" id="categories" width="250" style="width: 250px">';
			foreach( $cats as $cat_key => $cat_value )	{
				echo '<option value="' . $cat_key . '">' . $cat_value . '</option>';
			}
			echo '</select>';
			echo '&nbsp;&nbsp;&nbsp;';
			submit_button( 'Delete Selected', 'delete', 'submit', false );
			echo '<br>';
			echo '<font size="-2">Hold CTRL & click to select multiple entries</font>';
			echo '</td>';
			echo '</tr>';
		}
		?>
        </form>
        <form name="form-new-category" id="form-new-category" method="post">
        <tr>
        <td><input type="text" name="category_name" id="category_name" class="all-options" />&nbsp;&nbsp;&nbsp;<?php submit_button( 'Add Category', 'primary', 'submit', false ); ?></td>
        </tr>
        </form>
        </table>
        <hr>
        <h3>Equipment Inventory</h3>
        <?php
        $equipment = get_option( 'mdjm_equipment' );
		?>
        <table class="widefat">
        <thead>
		<tr>
        <th class="row-title">Item</th>
        <th>Cat</th>
        <th>Qty</th>
        <th>Options</th>
        <th>Description</th>
        <th>Add-on?</th>
        <th>Provided By</th>
        <th>Actions</th>
		</tr>
        </thead>
        <tbody>
        <?
		$djs = mdjm_get_djs();
		if( empty( $equipment ) )	{
			echo '<tr>';
			echo '<td colspan="6">You have no equipment in your inventory yet. Begin adding below.</td>';
			echo '</tr>';	
		}
		else	{
			asort( $equipment );
			/* START TABLE DISPLAY OF EQUIPMENT INVENTORY */
			$rowclass = ' class="alternate"';
			foreach( $equipment as $equip_list )	{
				?>
				<form name="form-<?php echo $equip_list[1]; ?>" id="form-<?php echo $equip_list[1]; ?>" method="post">
				<input type="hidden" name="slug" value="<?php echo esc_attr( $equip_list[1] ); ?>" />
				<tr<?php echo $rowclass; ?>>
				<td class="row-title"><input type="text" name="equip_name" id="equip_name" value="<?php echo stripslashes( esc_attr( $equip_list[0] ) ); ?>" /></td>
                <td><select name="equip_cat" id="equip_cat" />
                <?php
				foreach( $cats as $cat_key => $cat_value )	{
					?>
					<option value="<?php echo $cat_key; ?>"<?php selected( $equip_list[5], $cat_key ); ?>><?php echo esc_attr( $cat_value ); ?></option>
                    <?php
				}
				?>
				</select></td></td>
                <td><input type="text" name="equip_qty" id="equip_qty" class="small-text" value="<?php echo esc_attr( $equip_list[2] ); ?>" /></td>
                <td><textarea name="equip_options" id="equip_options" class="all-options"><?php echo esc_textarea( $equip_list[3] ); ?></textarea></td>
				<td><textarea name="equip_desc" id="equip_desc" class="all-options"><?php echo stripslashes( esc_attr( $equip_list[4] ) ); ?></textarea></td>
				<td>
                <input type="checkbox" name="addon_avail" id="addon_avail" value="Y" <?php checked( $equip_list[6], 'Y' ); ?> />&nbsp;&nbsp;<input type="text" name="addon_cost" id="addon_cost" class="small-text" value="<?php echo esc_attr( $equip_list[7] ); ?>" /></td>
                <?php
				if ( MDJM_MULTI == true )	{
					?>
					<td><select name="djs[]" multiple="multiple" id="djs">
                    <?php
					$djs_have = explode( ',', $equip_list[8] );
					foreach( $djs as $dj )	{
						echo '<option value="' . $dj->ID . '"';
						foreach( $djs_have as $dj_in_list )	{
							if( $dj->ID == $dj_in_list )
								echo ' selected="selected"';
						}
						echo '>';
						echo $dj->display_name . '</option>';	
					}
					echo '</select>';
				}
				else	{
					$current_user = wp_get_current_user();
					echo '<td>' . $current_user->display_name . '</td>';	
				}
				echo '</td>';
				echo '<td>';
				submit_button( 'Update', 'primary', 'submit', false );
				echo '<br /><br />';
				submit_button( 'Remove', 'delete', 'submit', false );
				echo '</td>';
				echo '</tr>';
				echo '</form>';
				
				if( $rowclass == ' class="alternate"' ) $rowclass = ''; else $rowclass = ' class="alternate"';
			}
			/* END EQUIPMENT INVENTORY TABLE */
		}
		?>
        </tbody>
        <tfoot>
		<tr>
        <th class="row-title">Item</th>
        <th>Cat</th>
        <th>Qty</th>
        <th>Options</th>
        <th>Description</th>
        <th>Add-on?</th>
        <th>Provided By</th>
        <th>Actions</th>
		</tr>
        </tfoot>
        </table>
        <hr />
        <h3>Add Equipment Item</h3>
        <form name="add-equipment" id="add-equipment" method="post">
        <table class="form-table">
        <tr>
        <th><label for="equip_name">Item Name</label></th>
        <td><input type="text" name="equip_name" id="equip_name" class="all-options" /></td>
        </tr>
        <th><label for="equip_name">Quantity Available</label></th>
        <td><input type="text" name="equip_qty" id="equip_qty" class="small-text" /> <span class="description">If applicable</span></td>
        </tr>
         </tr>
        <th><label for="equip_options">Options</label></th>
        <td><textarea name="equip_options" id="equip_options" cols="80" rows="4" class="all-options" placeholder="Red&#10;Green&#10;Blue"></textarea> <span class="description">If there are specific options available for this item, list them here. One per line</span></td>
        </tr>
        <tr>
        <th><label for="equip_desc">Brief Description</label></th>
        <td><textarea name="equip_desc" id="equip_desc" class="all-options" cols="80" rows="4" placeholder="Excellent lighting effect"></textarea></td></td>
        </tr>
        <tr>
        <th><label for="equip_cat">Category</label></th>
        <td>
        <?php
		if( !$cats )	{
			echo 'Define a category above. You cannot add any items until you do!';
		}
		else	{
		?>
            <select name="equip_cat" id="equip_cat" width="250" style="width: 250px" />
            <?php
                foreach( $cats as $cat_key => $cat_value )	{
                    echo '<option value="' . $cat_key . '">' . esc_attr( $cat_value ) . '</option>';
                }
		}
		?>
        </select>
        </td>
        </tr>
        <tr>
        <th><label for="addon_avail">Available as Add-on?</label></th>
        <td><input type="checkbox" name="addon_avail" id="addon_avail" value="Y" checked /> <span class="description">If selected, will be an available optional add-on if not included in event package already</span></td>
        </tr>
        <tr>
        <th><label for="addon_cost">Cost as Add-on</label></th>
        <td><input type="text" name="addon_cost" id="addon_cost" class="small-text" placeholder="10.00" /> <span class="description"><?php _e( 'Cost of individual add-on', 'mobile-dj-manager' ); ?>. <?php echo sprintf( __( 'No %s symbol needed', 'mobile-dj-manager' ), MDJM_CURRENCY ); ?></span></td>
        </tr>
        <?php
		if ( MDJM_MULTI == true )	{
		?>
            <tr>
            <th><label for="djs">Which DJs Provide?</label></th>
            <td><select name="djs[]" multiple="multiple" id="djs" width="250" style="width: 250px">
                    <?php
                    foreach( $djs as $dj )	{
                        echo '<option value="' . $dj->ID . '">' . esc_attr( $dj->display_name ) . '</option>';	
                    }
                    ?>
            </select> <span class="description">Which DJ's have this item? Hold CTRL + click to select multiple</span></td>
            </tr>
        <?php
		}
		else	{
			echo '<input type="hidden" name="djs[]" value="' . get_current_user_id() . '"';	
		}
		?>
        <tr>
        <td>&nbsp;</td>
        <td><?php if( $cats ) submit_button( 'Add Item', 'primary', 'submit', true ); ?></td>
        </tr>
        </table>
        </form>
        </div>
		<?php
	}
	
	f_mdjm_equipment_settings_display();
?>
