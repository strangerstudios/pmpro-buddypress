<?php
/**
 * Add and remove a user from BuddyPress groups
 * when their level changes.
 * @param  int $level_id     ID of the level the user is being given.
 * @param  int $user_id      ID of the user being given the level.
 * @param  int $cancel_level ID of the level being cancelled if specified
 * @return void
 */
function pmpro_bp_set_member_groups( $level_id, $user_id, $cancel_level = NULL ) {

	// Make sure Groups are activated.
	if ( ! function_exists( 'groups_create_group' ) ) {
		return;
	}

	$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );

	if ( ! empty( $cancel_level ) ) {
		$pmpro_bp_old_level_options = pmpro_bp_get_level_options( $cancel_level );
	} else {
		$pmpro_bp_old_level_options = pmpro_bp_get_user_old_level_options( $user_id );
	}

	// Add to groups
	$old_groups = $pmpro_bp_old_level_options['pmpro_bp_group_automatic_add'];
	$new_groups = $pmpro_bp_options['pmpro_bp_group_automatic_add'];


	if ( ! empty( $old_groups ) ) {
		foreach ( $old_groups as $group_id ) {
			groups_leave_group( intval( $group_id ), $user_id );
		}
	}

	if ( ! empty( $new_groups ) ) {
		foreach ( $new_groups as $group_id ) {
			groups_join_group( intval( $group_id ), $user_id );
		}
	}

	// Invite to groups
	$old_groups_invite = $pmpro_bp_old_level_options['pmpro_bp_group_can_request_invite'];
	$new_groups_invite = $pmpro_bp_options['pmpro_bp_group_can_request_invite'];

	if ( ! empty( $old_groups_invite ) ) {
		foreach ( $old_groups_invite as $group_id ) {
			// Don't remove them, if we're about to add them back in.
			if ( in_array( $group_id, $new_groups_invite ) ) {
				continue;
			}
			
			// Uninvite and remove from group.
			groups_uninvite_user( $user_id, $group_id );			
			groups_leave_group( intval( $group_id ), $user_id );
		}
	}

	if ( ! empty( $new_groups_invite ) ) {
		foreach ( $new_groups_invite as $group_id ) {
			$group = groups_get_group( array( 'group_id' => $group_id ) );
			groups_invite_user(
				array(
					'user_id'       => $user_id,
					'group_id'      => intval( $group_id ),
					'inviter_id'    => $group->creator_id,
					'date_modified' => bp_core_current_time(),
					'send_invite'   => 1,
				)
			);
		}
	}

}
add_action( 'pmpro_after_change_membership_level', 'pmpro_bp_set_member_groups', 10, 3 );
