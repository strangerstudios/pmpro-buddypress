<?php
/**
 * Add and remove a user from BuddyPress groups
 * when their level changes.
 * @param  int $level_id     ID of the level the user is being given.
 * @param  int $user_id      ID of the user being given the level.
 * @param  int $cancel_level ID of the level being cancelled if specified
 * @return void
 */
function pmpro_bp_set_member_groups( $level_id, $user_id, $cancel_level ) {

	// Make sure Groups are activated.
	if(!function_exists('groups_accept_invite')) return;
	
	$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );

	if( !empty( $cancel_level ) ) {
		$pmpro_bp_old_level_options = pmpro_bp_get_level_options( $cancel_level );
	} else {
		$pmpro_bp_old_level_options = pmpro_bp_get_user_old_level_options( $user_id );
	}

	$old_groups = $pmpro_bp_old_level_options['pmpro_bp_group_automatic_add'];
	$new_groups = $pmpro_bp_options['pmpro_bp_group_automatic_add'];

	if( !empty( $old_groups ) ) {
		foreach($old_groups as $group_id) {
			groups_leave_group( $group_id, $user_id );
		}
	}

	if( !empty( $new_groups ) ) {
		foreach($new_groups as $group_id) {
			groups_accept_invite( $user_id, $group_id );
		}
	}
}
add_action( 'pmpro_after_change_membership_level', 'pmpro_bp_set_member_groups', 10, 3 );
