<?php
/*
	Code to add settings to the edit membership level page and save those settings.
*/

/**
 * Add settings to the edit level page in the dashboard.
 * Fires on the 'pmpro_membership_level_after_other_settings' hook.
 */
function pmpro_bp_group_creation_level_settings()
{?>
	<h3 class="topborder"><?php _e('BuddyPress Group Restrictions', 'pmpro');?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_creation"><?php _e('Group Creation', 'pmpro');?>:</label></th>
				<td>
					<?php
						if(isset($_REQUEST['edit'])){
								
							$edit = $_REQUEST['edit'];
								
							$can_create_groups = get_option('pmpro_bp_group_creation_'.$edit);
							$can_view_single_group = get_option('pmpro_bp_group_single_viewing_'.$edit);
							$can_view_groups_page = get_option('pmpro_bp_groups_page_viewing_'.$edit);
							$can_join_groups = get_option('pmpro_bp_groups_join_'.$edit);

						}
						else
						{
							$can_create_groups = 0;
							$can_view_single_group = 0;
							$can_view_groups_page = 0;
							$can_join_groups = 0;

						}?>
							
						<select name="pmpro_bp_group_creation" id="pmpro_bp_group_creation">
							<option value= '0' <?php if($can_create_groups == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($can_create_groups == 1) echo "selected"; ?>>Yes</option>
						</select>
			
						<p class="description">Can members of this level create BuddyPress Groups?</p>
					</td>
				</tr>
				
				<?php//viewing an individual group setting ?>
				<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_single_viewing"><?php _e('Single Group Viewing', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_group_single_viewing" id="pmpro_bp_group_single_viewing">
							<option value= '0' <?php if($can_view_single_group == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($can_view_single_group == 1) echo "selected"; ?>>Yes</option>
						</select>
			
						<p class="description">Can members of this level view individual BuddyPress Groups?</p>
					</td>
				</tr>
				
				<?php//viewing the groups page ?>
				<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_groups_page_viewing"><?php _e('Groups Page Viewing', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_groups_page_viewing" id="pmpro_bp_groups_page_viewing">
							<option value= '0' <?php if($can_view_groups_page == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($can_view_groups_page == 1) echo "selected"; ?>>Yes</option>
						</select>
			
						<p class="description">Can members of this level view the BuddyPress Groups page?</p>
					</td>
				</tr>
				
				<?php//can members of this level join groups? ?>
				<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_groups_join"><?php _e('Joining Groups', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_groups_join" id="pmpro_bp_groups_join">
							<option value= '0' <?php if($can_join_groups == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($can_join_groups == 1) echo "selected"; ?>>Yes</option>
						</select>
			
						<p class="description">Can members of this level join BuddyPress Groups?</p>
					</td>
				</tr>
		
		
		</tbody>
		</table>
	<?php 
}
add_action('pmpro_membership_level_after_other_settings','pmpro_bp_group_creation_level_settings');

/**
 * Save the settings on the edit membership page of the dashboard.
 * Fires on the 'pmpro_save_membership_level' hook.
 */
function pmpro_bp_pmpro_save_membership_level($level_id)
{
	if( $level_id <= 0 )
	{
		return;
	}

	$can_create_groups = $_REQUEST['pmpro_bp_group_creation'];
	$can_view_single_group = $_REQUEST['pmpro_bp_group_single_viewing'];
	$can_view_groups_page = $_REQUEST['pmpro_bp_groups_page_viewing'];
	$can_join_groups = $_REQUEST['pmpro_bp_groups_join'];
	
	update_option('pmpro_bp_group_creation_'.$level_id, $can_create_groups);
	update_option('pmpro_bp_group_single_viewing_'.$level_id, $can_view_single_group);
	update_option('pmpro_bp_groups_page_viewing_'.$level_id, $can_view_groups_page);
	update_option('pmpro_bp_groups_join_'.$level_id, $can_join_groups);
}
add_action('pmpro_save_membership_level','pmpro_bp_pmpro_save_membership_level', 10, 1);