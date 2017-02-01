<?php
/*
	Code to add settings to the edit membership level page and save those settings.
*/

/**
 * Add settings to the edit level page in the dashboard.
 * Fires on the 'pmpro_membership_level_after_other_settings' hook.
 */
function pmpro_bp_group_creation_level_settings()
{	
	if(isset($_REQUEST['edit']))
	{
		$edit = $_REQUEST['edit'];
		$can_create_groups = get_option('pmpro_bp_group_creation_'.$edit);
		$can_view_single_group = get_option('pmpro_bp_group_single_viewing_'.$edit);
		$can_view_groups_page = get_option('pmpro_bp_groups_page_viewing_'.$edit);
		$can_join_groups = get_option('pmpro_bp_groups_join_'.$edit);
		$pmpro_bp_restrictions = get_option('pmpro_bp_restrictions_'.$edit);
		$pmpro_bp_private_messaging = get_option('pmpro_bp_private_messaging_'.$edit);
		$pmpro_bp_send_friend_request = get_option('pmpro_bp_send_friend_request_'.$edit);
	}
	else
	{
		$can_create_groups = 0;
		$can_view_single_group = 0;
		$can_view_groups_page = 0;
		$can_join_groups = 0;
		$pmpro_bp_restrictions = 0;
		$pmpro_bp_private_messaging = 0;
		$pmpro_bp_send_friend_request = 0;
	}

	?>
	<h3 class="topborder"> <?php _e('BuddyPress Group Restrictions', 'pmpro');?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_restrictions"><?php _e('Lockdown BuddyPress?', 'pmpro');?>:</label>
				</th>
			<td>
				<select id="pmpro_bp_restrictions" name="pmpro_bp_restrictions" onchange="pmpro_updateBuddyPressTRs();">
						<option value="0" <?php if(!$pmpro_bp_restrictions) { ?>selected="selected"<?php } ?>><?php _e('No', 'pmpro');?></option>
						<option value="1" <?php if($pmpro_bp_restrictions == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes - Lock down all of BuddyPress', 'pmpro');?></option>
						<option value="2" <?php if($pmpro_bp_restrictions == 2) { ?>selected="selected"<?php } ?>><?php _e('Yes - Lock down specific features', 'pmpro');?></option>
				</select><br />
				</td>
			</tr>
			</tbody>
	</table>	
	
	<table id="specific_features" class="form-table">
		<tbody>
	
			<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_creation"><?php _e('Group Creation', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_group_creation" id="pmpro_bp_group_creation">
							<option value= '0' <?php if($can_create_groups == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($can_create_groups == 1) echo "selected"; ?>>Yes</option>
					</select>
			
					<p class="description">Can members of this level create BuddyPress Groups?</p>
				</td>
			</tr>
				
				<?php //viewing an individual group setting 
				?>
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
				
				<?php //viewing the groups page
				?>
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
				
				<?php //can members of this level join groups?
				?>
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
				
				<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_private_messaging"><?php _e('Private Messaging', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_private_messaging" id="pmpro_bp_private_messaging">
							<option value= '0' <?php if($pmpro_bp_private_messaging == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($pmpro_bp_private_messaging == 1) echo "selected"; ?>>Yes</option>
					</select>
					<p class="description">Can members of this level send private messages to other members?</p>
					</td>
				</tr>
				
				<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_send_friend_request"><?php _e('Send Friend Requests', 'pmpro');?>:</label></th>
				<td>
					<select name="pmpro_bp_send_friend_request" id="pmpro_bp_send_friend_request">
							<option value= '0' <?php if($pmpro_bp_send_friend_request == 0) echo "selected"; ?> >No</option>
							<option value= '1' <?php if($pmpro_bp_send_friend_request == 1) echo "selected"; ?>>Yes</option>
					</select>
					<p class="description">Can members of this level send friend requests to other members?</p>
					</td>
				</tr>
		</tbody>
		</table>
		
		<script>

			function pmpro_updateBuddyPressTRs()
			{
				var specific_features = jQuery('#pmpro_bp_restrictions').val();
				console.log(specific_features);
				if(specific_features == 2)
				{
					jQuery('#specific_features').show();
				}
				else
				{
					jQuery('#specific_features').hide();
				}
			}
			pmpro_updateBuddyPressTRs();
		</script>
		
		
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
	$pmpro_bp_restrictions = $_REQUEST['pmpro_bp_restrictions'];
	$pmpro_bp_private_messaging = $_REQUEST['pmpro_bp_private_messaging'];
	$pmpro_bp_send_friend_request = $_REQUEST['pmpro_bp_send_friend_request'];
	
	update_option('pmpro_bp_group_creation_'.$level_id, $can_create_groups);
	update_option('pmpro_bp_group_single_viewing_'.$level_id, $can_view_single_group);
	update_option('pmpro_bp_groups_page_viewing_'.$level_id, $can_view_groups_page);
	update_option('pmpro_bp_groups_join_'.$level_id, $can_join_groups);
	update_option('pmpro_bp_restrictions_'.$level_id, $pmpro_bp_restrictions);
	update_option('pmpro_bp_private_messaging_'.$level_id, $pmpro_bp_private_messaging);
	update_option('pmpro_bp_send_friend_request_'.$level_id, $pmpro_bp_send_friend_request);
}
add_action('pmpro_save_membership_level','pmpro_bp_pmpro_save_membership_level', 10, 1);