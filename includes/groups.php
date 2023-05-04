<?php
/**
 * Add and remove a user from BuddyPress groups
 * when their level changes.
 * @param  int $level_id     ID of the level the user is being given.
 * @param  int $user_id      ID of the user being given the level.
 * @param  int $cancel_level ID of the level being cancelled if specified
 * @return void
 *
 * @deprecated 1.4 - Use pmpro_bp_groups_pmpro_after_all_membership_level_changes() instead.
 */
function pmpro_bp_set_member_groups( $level_id, $user_id, $cancel_level = NULL ) {
	// Show deprecation notice.
	_deprecated_function( __FUNCTION__, '1.4', 'pmpro_bp_groups_pmpro_after_all_membership_level_changes()' );

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

/**
 * Handle group changes when users' membership level changes
 *
 * @param array $pmpro_old_user_levels Array of old user levels before change.
 */
function pmpro_bp_groups_pmpro_after_all_membership_level_changes( $pmpro_old_user_levels ) {
	// Make sure Groups are activated.
	if ( ! function_exists( 'groups_create_group' ) ) {
		return;
	}

	foreach ( $pmpro_old_user_levels as $user_id => $old_levels ) {
		// Get the user's current active membership levels.
		$new_levels = pmpro_getMembershipLevelsForUser( $user_id );

		$new_subscribes = array();
		$old_subscribes = array();
		$new_invites    = array();
		$old_invites    = array();

		// Build an array of all groups assigned to the user's old membership levels.
		foreach ( $old_levels as $old_level ) {
			$pmpro_bp_old_level_options = pmpro_bp_get_level_options( $old_level->id );
			$old_subscribes             = array_merge( $old_subscribes, empty( $pmpro_bp_old_level_options['pmpro_bp_group_automatic_add'] ) ? array() : $pmpro_bp_old_level_options['pmpro_bp_group_automatic_add'] );
			$old_invites                = array_merge( $old_invites, empty( $pmpro_bp_old_level_options['pmpro_bp_group_can_request_invite'] ) ? array() : $pmpro_bp_old_level_options['pmpro_bp_group_can_request_invite'] );
		}

		// Build an array of all groups assigned to the user's new membership levels.
		foreach ( $new_levels as $new_level ) {
			$pmpro_bp_new_level_options = pmpro_bp_get_level_options( $new_level->id );
			$new_subscribes             = array_merge( $new_subscribes, empty( $pmpro_bp_new_level_options['pmpro_bp_group_automatic_add'] ) ? array() : $pmpro_bp_new_level_options['pmpro_bp_group_automatic_add'] );
			$new_invites                = array_merge( $new_invites, empty( $pmpro_bp_new_level_options['pmpro_bp_group_can_request_invite'] ) ? array() : $pmpro_bp_new_level_options['pmpro_bp_group_can_request_invite'] );
		}

		// Remove duplicates in the array of old and new groups.
		$new_subscribes = array_unique( $new_subscribes );		
		$old_subscribes = array_unique( $old_subscribes );
		$new_invites    = array_unique( $new_invites );
		$old_invites    = array_unique( $old_invites );	

		// Add to all groups assigned to the user's new membership levels.
		// Function will ignore gropus the user is already a member of.
		foreach ( $new_subscribes as $group_id ) {
			groups_join_group( intval( $group_id ), $user_id );
		}

		// Invite to all groups assigned to the user's new membership levels
		// that the user was not added to above.
		foreach ( $new_invites as $group_id ) {
			// Don't invite them, we added them to the group above.
			if ( in_array( $group_id, $new_subscribes ) ) {
				continue;
			}

			// Check if the user is already a member of the group.
			if ( groups_is_user_member( $user_id, $group_id ) ) {
				continue;
			}

			// Send the invite. Will not send if the user has already has a pending invite.
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

		// Remove from all groups assigned to the user's old membership levels that are not assigned to the user's new membership levels.
		$delete_groups = array_diff( array_merge( $old_subscribes, $old_invites ), array_merge( $new_subscribes, $new_invites ) );
		foreach ( $delete_groups as $group_id ) {
			// Uninvite and remove from group.
			groups_uninvite_user( $user_id, $group_id );			
			groups_leave_group( intval( $group_id ), $user_id );
		}
	}
}
add_action( 'pmpro_after_all_membership_level_changes', 'pmpro_bp_groups_pmpro_after_all_membership_level_changes' );

/**
 * Remove the group status in meta if one is present (Disabled since 1.4.1)
 * 
 * @since 1.3
 * 
 * @param  array $group_meta Group meta data
 * @param  object $group Group data object
 * @param  bool $is_group If it is a group
 * @return array
 */
function pmpro_bp_nouveau_get_group_meta_custom( $group_meta, $group, $is_group ) {

	if ( isset( $group_meta['status'] ) ) {
		$group_meta['status'] = '';
	}
	
	return $group_meta;

}
// add_filter( 'bp_nouveau_get_group_meta', 'pmpro_bp_nouveau_get_group_meta_custom', 10, 3 );

/**
 * Remove the group type if one is present (Disabled since 1.4.1)
 * 
 * @since 1.3
 * 
 * @param  object $group Group data object
 * @return string
 */
function pmpro_bp_get_group_type_custom( $group ) {
	return '';
}
// add_filter( 'bp_get_group_type', 'pmpro_bp_get_group_type_custom' );