<?php
/*
	Code to add settings to the edit membership level page and save those settings.
*/

/**
 * Add settings to the edit level page in the dashboard.
 * Fires on the 'pmpro_membership_level_after_other_settings' hook.
 */
function pmpro_bp_level_settings( ) {
	if( isset( $_REQUEST['edit'] ) ) {
		$level_id = intval( $_REQUEST['edit'] );
		$pmpro_bp_options = pmpro_bp_get_level_options( $level_id );
		$pmpro_bp_group_automatic_add		= $pmpro_bp_options['pmpro_bp_group_automatic_add'];
		$pmpro_bp_group_can_request_invite = $pmpro_bp_options['pmpro_bp_group_can_request_invite'];
		$pmpro_bp_member_types = $pmpro_bp_options['pmpro_bp_member_types'];
	} else {
		$level_id = -1;
		$pmpro_bp_group_automatic_add = 0;
		$pmpro_bp_group_can_request_invite = 0;
		$pmpro_bp_member_types = 0;
	}

	// Restriction Settings
	pmpro_bp_restriction_settings_form();	

	// Group Settings
	if ( class_exists( 'BP_Groups_Group' ) ): ?>
		<hr />
		<h3><?php _e('BuddyPress Group Membership', 'pmpro-buddypress');?></h3>
		<?php if ( defined( 'BP_PLATFORM_VERSION' ) ) { ?>
			<p class="description"><?php esc_html_e( 'Note: These settings apply to sites running BuddyPress or BuddyBoss.', 'pmpro-buddypress' ); ?></p>
		<?php } ?>
		<?php 
		//get groups by status
		$group_type_ids = BP_Groups_Group::get_group_type_ids();	
		$group_ids = $group_type_ids['all'];	
		$groups_args = array('include' => $group_ids, 'per_page' => 0);
		
		if(empty($pmpro_bp_group_automatic_add))
			$pmpro_bp_group_automatic_add = array();
		if(empty($pmpro_bp_group_can_request_invite))
			$pmpro_bp_group_can_request_invite = array();
			
		?>
		
		<table id="group_adding" class="form-table">
		<tbody>
	
			<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_automatic_add"><?php _e('Add to These Groups', 'pmpro-buddypress');?>:</label></th>
				<td>
					<?php if ( bp_has_groups( $groups_args ) ) { ?>
					<div class="checkbox_box" <?php if(count($group_ids) > 30) { ?>style="height: 300px; overflow: auto;"<?php } ?>>
						<?php
							global $groups_template;
									
							while ( bp_groups() ) {
								bp_the_group();?>
								<div class="clickable"><input type="checkbox" id="pmpro_bp_group_automatic_add_<?php echo esc_attr( $groups_template->group->id); ?>" name="pmpro_bp_group_automatic_add[]" value="<?php echo esc_attr( $groups_template->group->id); ?>" <?php if(in_array($groups_template->group->id, $pmpro_bp_group_automatic_add)) { ?>checked="checked"<?php } ?>> <?php echo $groups_template->group->name. " (".$groups_template->group->status.")"?></div> <?php
							}
						?>	
					</div>
					<?php } else { ?>
						<p><?php _e( 'There are no groups defined.', 'pmpro-buddypress' ); ?></p>
					<?php } ?>
				</td>
			</tr>
			
			<?php
				$group_ids = $group_type_ids['private'];
				$groups_args = array('include' => $group_ids, 'per_page' => 0);
			?>
			
			<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_can_request_invite"><?php _e('Invite to These Groups', 'pmpro-buddypress');?>:</label></th>
				<td>
					<?php if ( bp_has_groups( $groups_args ) ) { ?>
					<div class="checkbox_box" <?php if(count($group_ids) > 30) { ?>style="height: 300px; overflow: auto;"<?php } ?>>
						<?php
							global $groups_template;
									
							while ( bp_groups() ) {
								bp_the_group();?>
								<div class="clickable"><input type="checkbox" id="pmpro_bp_group_can_request_invite_<?php echo $groups_template->group->id?>" name="pmpro_bp_group_can_request_invite[]" value="<?php echo $groups_template->group->id?>" <?php if(in_array($groups_template->group->id, $pmpro_bp_group_can_request_invite)) { ?>checked="checked"<?php } ?>> <?php echo $groups_template->group->name. " (".$groups_template->group->status.")"?></div> <?php
							}
						?>							
					</div>
					<?php } else { ?>
						<p><?php _e( 'There are no groups defined.', 'pmpro-buddypress' ); ?></p>
					<?php } ?>
				</td>
			</tr>			
			
			</tbody>
		</table>
		<?php endif; ?>
		<hr />
		<h3><?php _e('BuddyPress Member Types', 'pmpro-buddypress');?></h3>
		<?php if ( defined( 'BP_PLATFORM_VERSION' ) ) { ?>
			<p class="description"><?php esc_html_e( 'Note: These settings apply to sites running BuddyPress or BuddyBoss.', 'pmpro-buddypress' ); ?></p>
		<?php } ?>		
		<?php
			if( function_exists( 'bp_get_member_types' ) ) {
				$registered_member_type_objects = bp_get_member_types( array(), 'objects' );
			} else {
				$registered_member_type_objects = array();
			}
			if(empty($registered_member_type_objects)) {
			?>
				<div><?php _e('There are no member types defined.', 'pmpro-buddypress');?></div>
			<?php
			} else {
			?>
				<table id="member-types" class="form-table">
				<tbody>
			
					<tr>
						<th scope="row" valign="top"><label for="pmpro_bp_member_types"><?php _e('Member Types', 'pmpro-buddypress');?>:</label></th>
						<td>
							<div class="checkbox_box" <?php if(count($registered_member_type_objects) > 30) { ?>style="height: 300px; overflow: auto;"<?php } ?>>							
							<?php
								foreach($registered_member_type_objects as $member_type => $member_type_data)
								{
								?>
								<div class="clickable">
									<input type="checkbox" id="pmpro_bp_member_type_<?php echo $member_type_data->name;?>" name="pmpro_bp_member_types[]" value="<?php echo esc_attr($member_type_data->name);?>" <?php if(is_array($pmpro_bp_member_types) && in_array($member_type_data->name, $pmpro_bp_member_types)) echo " checked='checked'";?>"> <?php echo $member_type_data->labels['name'];?>
								</div>
								<?php
								}
							?>	
							</div>						
						</td>
					</tr>
					</tbody>
				</table>
			<?php
			}
		?>	
		
		<script>
			jQuery('.checkbox_box input').click(function(event) {
				event.stopPropagation()
			});
			jQuery('.checkbox_box div.clickable').click(function() {
				var checkbox = jQuery(this).find(':checkbox');
				checkbox.attr('checked', !checkbox.attr('checked'));
			});			
		</script>
		
		
	<?php 
}
add_action('pmpro_membership_level_after_other_settings','pmpro_bp_level_settings');

/**
 * Save the settings on the edit membership page of the dashboard.
 * Fires on the 'pmpro_save_membership_level' hook.
 */
function pmpro_bp_pmpro_save_membership_level($level_id)
{		
	if( $level_id <= 0 ) {
		return;
	}
		
	$can_create_groups = intval( $_REQUEST['pmpro_bp_group_creation'] );
	$can_view_single_group = intval( $_REQUEST['pmpro_bp_group_single_viewing'] );
	$can_view_groups_page = intval( $_REQUEST['pmpro_bp_groups_page_viewing'] );
	$can_join_groups = intval( $_REQUEST['pmpro_bp_groups_join'] );
	$pmpro_bp_restrictions = intval( $_REQUEST['pmpro_bp_restrictions'] );
	$pmpro_bp_public_messaging = intval( $_REQUEST['pmpro_bp_public_messaging'] );
	$pmpro_bp_private_messaging = intval( $_REQUEST['pmpro_bp_private_messaging'] );
	$pmpro_bp_send_friend_request = intval( $_REQUEST['pmpro_bp_send_friend_request'] );
	$pmpro_bp_member_directory = intval( $_REQUEST['pmpro_bp_member_directory'] );
	
	if( isset( $_REQUEST['pmpro_bp_group_automatic_add'] ) ) {
		$pmpro_bp_group_automatic_add = array_map( 'sanitize_text_field', $_REQUEST['pmpro_bp_group_automatic_add'] );
	} else {
		$pmpro_bp_group_automatic_add = false;
	}
	
	if( isset( $_REQUEST['pmpro_bp_group_can_request_invite'] ) ) {
		$pmpro_bp_group_can_request_invite = array_map( 'sanitize_text_field', $_REQUEST['pmpro_bp_group_can_request_invite'] );
	} else {
		$pmpro_bp_group_can_request_invite = false;
	}
	
	if( isset( $_REQUEST['pmpro_bp_member_types'] ) ) {
		$pmpro_bp_member_types = array_map( 'sanitize_text_field', $_REQUEST['pmpro_bp_member_types'] );
	} else {
		$pmpro_bp_member_types = false;
	}
		
	$pmpro_bp_options = array(
		'pmpro_bp_restrictions'				=> $pmpro_bp_restrictions,
		'pmpro_bp_group_creation'			=> $can_create_groups,
		'pmpro_bp_group_single_viewing'		=> $can_view_single_group,
		'pmpro_bp_groups_page_viewing'		=> $can_view_groups_page,
		'pmpro_bp_groups_join'				=> $can_join_groups,		
		'pmpro_bp_private_messaging'		=> $pmpro_bp_private_messaging,
		'pmpro_bp_public_messaging'			=> $pmpro_bp_public_messaging,
		'pmpro_bp_send_friend_request'		=> $pmpro_bp_send_friend_request,
		'pmpro_bp_member_directory'			=> $pmpro_bp_member_directory,
		'pmpro_bp_group_automatic_add'		=> $pmpro_bp_group_automatic_add,
		'pmpro_bp_group_can_request_invite'	=> $pmpro_bp_group_can_request_invite,
		'pmpro_bp_member_types'				=> $pmpro_bp_member_types);
		
	update_option('pmpro_bp_options_'.$level_id, $pmpro_bp_options, 'no');
}
add_action('pmpro_save_membership_level','pmpro_bp_pmpro_save_membership_level', 10, 1);

/**
 * Output the BuddyPress restriction settings form.
 */
function pmpro_bp_restriction_settings_form( $level_id = NULL) {
	if( !isset( $level_id ) && isset( $_REQUEST['edit'] ) ) {
		$level_id = intval( $_REQUEST['edit'] );
	} elseif( !isset( $level_id ) ) {
		$level_id = -1;
	}
	
	$pmpro_bp_options = pmpro_bp_get_level_options( $level_id );
	
	$can_create_groups				= $pmpro_bp_options['pmpro_bp_group_creation'];
	$can_view_single_group			= $pmpro_bp_options['pmpro_bp_group_single_viewing'];
	$can_view_groups_page			= $pmpro_bp_options['pmpro_bp_groups_page_viewing'];
	$can_join_groups				= $pmpro_bp_options['pmpro_bp_groups_join'];
	$pmpro_bp_restrictions			= $pmpro_bp_options['pmpro_bp_restrictions'];
	$pmpro_bp_private_messaging		= $pmpro_bp_options['pmpro_bp_private_messaging'];
	$pmpro_bp_public_messaging		= $pmpro_bp_options['pmpro_bp_public_messaging'];
	$pmpro_bp_send_friend_request		= $pmpro_bp_options['pmpro_bp_send_friend_request'];		
	$pmpro_bp_member_directory		= $pmpro_bp_options['pmpro_bp_member_directory'];
	?>
	<?php if( $level_id <> 0 ) { ?>
		<hr />
		<h3> <?php _e('BuddyPress Restrictions', 'pmpro-buddypress');?></h3>
	<?php } ?>
	<?php if ( defined( 'BP_PLATFORM_VERSION' ) ) { ?>
		<p class="description"><?php esc_html_e( 'Note: These settings apply to sites running BuddyPress or BuddyBoss.', 'pmpro-buddypress' ); ?></p>
	<?php } ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_restrictions"><?php _e('Unlock BuddyPress?', 'pmpro-buddypress');?>:</label>
				</th>
			<td>
				<select id="pmpro_bp_restrictions" name="pmpro_bp_restrictions" onchange="pmpro_updateBuddyPressTRs();">
						<option value="-1" <?php if($pmpro_bp_restrictions == -1) { ?>selected="selected"<?php } ?>><?php _e('No - Lock access to all of BuddyPress.', 'pmpro-buddypress');?></option>
						<?php if( $level_id <> 0 ) { ?>
							<option value="0" <?php if(!$pmpro_bp_restrictions) { ?>selected="selected"<?php } ?>><?php _e('No - Use non-member user settings.', 'pmpro-buddypress');?></option>
						<?php }	?>
						<option value="1" <?php if($pmpro_bp_restrictions == 1) { ?>selected="selected"<?php } ?>>
							<?php 
								if( $level_id <> 0 ) {
									_e('Yes - Give members access to all of BuddyPress.', 'pmpro-buddypress');
								} else {
									_e('Yes - Give non-member users access to all of BuddyPress.', 'pmpro-buddypress');
								}
							?>							
						</option>
						<option value="2" <?php if($pmpro_bp_restrictions == 2) { ?>selected="selected"<?php } ?>>
							<?php 
								if( $level_id <> 0 ) {
									_e('Yes - Give members access to specific features.', 'pmpro-buddypress');
								} else {
									_e('Yes - Give non-member users access to specific features.', 'pmpro-buddypress');
								}
							?>
						</option>
				</select><br />
				</td>
			</tr>
			</tbody>
	</table>	
	
	<table id="specific_features" class="form-table">
		<tbody>
				
			<?php //viewing the groups page?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_groups_page_viewing"><?php _e('Groups Page Viewing', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_groups_page_viewing" id="pmpro_bp_groups_page_viewing">
						<option value= '0' <?php if($can_view_groups_page == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($can_view_groups_page == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
					</select>
		
					<p class="description">
					<?php
						if( $level_id <> 0 ) {
							_e( 'Can members of this level view the Groups page?', 'pmpro-buddypress' );
						} else {
							_e( 'Can non-member users view the Groups page?', 'pmpro-buddypress' );
						}
					?>
					</p>
				</td>
			</tr>

			<?php //viewing an individual group ?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_group_single_viewing"><?php _e('Single Group Viewing', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_group_single_viewing" id="pmpro_bp_group_single_viewing">
						<option value= '0' <?php if($can_view_single_group == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($can_view_single_group == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
					</select>
		
					<p class="description">
					<?php
						if( $level_id <> 0 ) {
							_e( 'Can members of this level view individual Groups?', 'pmpro-buddypress' );
						} else {
							_e( 'Can non-member users view individual Groups?', 'pmpro-buddypress' );
						}
					?>
					</p>
				</td>
			</tr>

			<?php //joining groups??>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_groups_join"><?php _e('Joining Groups', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_groups_join" id="pmpro_bp_groups_join">
						<option value= '0' <?php if($can_join_groups == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($can_join_groups == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
				</select>
		
				<p class="description">
				<?php
					if( $level_id <> 0 ) {
						_e( 'Can members of this level join Groups?', 'pmpro-buddypress' );
					} else {
						_e( 'Can non-member users join Groups?', 'pmpro-buddypress' );
					}
				?>
				</p>
			</td>
			</tr>

			<?php //creating groups ?>
			<tr>
				<th scope="row" valign="top"><label for="pmpro_bp_group_creation"><?php _e('Group Creation', 'pmpro-buddypress');?>:</label></th>
				<td>
					<select name="pmpro_bp_group_creation" id="pmpro_bp_group_creation">
							<option value= '0' <?php if($can_create_groups == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
							<option value= '1' <?php if($can_create_groups == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
					</select>
			
					<p class="description">
					<?php
						if( $level_id <> 0 ) {
							_e( 'Can members of this level create Groups?', 'pmpro-buddypress' );
						} else {
							_e( 'Can non-member users create Groups?', 'pmpro-buddypress' );
						}
					?>
					</p>
				</td>
			</tr>

			<?php //sending public messages ?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_public_messaging"><?php _e('Public Messaging', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_public_messaging" id="pmpro_bp_public_messaging">
						<option value= '0' <?php if($pmpro_bp_public_messaging == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($pmpro_bp_public_messaging == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
				</select>
				<p class="description">
				<?php
					if( $level_id <> 0 ) {
						_e( 'Can members of this level send public messages to other members?', 'pmpro-buddypress' );
					} else {
						_e( 'Can non-member users send public messages to other members?', 'pmpro-buddypress' );
					}
				?>
				</p>
				</td>
			</tr>
			
			<?php //private messages ?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_private_messaging"><?php _e('Private Messaging', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_private_messaging" id="pmpro_bp_private_messaging">
						<option value= '0' <?php if($pmpro_bp_private_messaging == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($pmpro_bp_private_messaging == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
				</select>
				<p class="description">
				<?php
					if( $level_id <> 0 ) {
						_e( 'Can members of this level send private messages to other members?', 'pmpro-buddypress' );
					} else {
						_e( 'Can non-member users send private messages to other members?', 'pmpro-buddypress' );
					}
				?>
				</p>
				</td>
			</tr>
			
			<?php //friend requests ?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_send_friend_request"><?php _e('Send Friend Requests', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_send_friend_request" id="pmpro_bp_send_friend_request">
						<option value= '0' <?php if($pmpro_bp_send_friend_request == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($pmpro_bp_send_friend_request == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
				</select>
				<p class="description">
				<?php
					if( $level_id <> 0 ) {
						_e( 'Can members of this level send friend requests to other members?', 'pmpro-buddypress' );
					} else {
						_e( 'Can non-member users send friend requests to other members?', 'pmpro-buddypress' );
					}
				?>
				</p>
				</td>
			</tr>
			
			<?php //member directory ?>
			<tr>
			<th scope="row" valign="top"><label for="pmpro_bp_member_directory"><?php _e('Include in Member Directory', 'pmpro-buddypress');?>:</label></th>
			<td>
				<select name="pmpro_bp_member_directory" id="pmpro_bp_member_directory">
						<option value= '0' <?php if($pmpro_bp_member_directory == 0) echo "selected"; ?> ><?php _e('No', 'pmpro-buddypress');?></option>
						<option value= '1' <?php if($pmpro_bp_member_directory == 1) echo "selected"; ?>><?php _e('Yes', 'pmpro-buddypress');?></option>
				</select>
				<p class="description">
					<?php
						if( $level_id <> 0 ) {
							_e( 'Should members of this level be included in the Members page?', 'pmpro-buddypress');
						} else {
							_e( 'Should non-member users be included in the Members page?', 'pmpro-buddypress');
						}
					?>
				</p>
				</td>
			</tr>
	</tbody>
	</table>
	<script>
		function pmpro_updateBuddyPressTRs() {
			var specific_features = jQuery( '#pmpro_bp_restrictions' ).val();

			if(specific_features == 2) {
				jQuery( '#specific_features' ).show();
			} else {
				jQuery( '#specific_features' ).hide();
			}
		}
		pmpro_updateBuddyPressTRs();
	</script>
	<?php
}
