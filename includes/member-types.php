<?php
/**
 * Add and remove a user from BuddyPress member types
 * when their level changes.
 * @param  int $level_id     ID of the level the user is being given.
 * @param  int $user_id      ID of the user being given the level.
 * @param  int $cancel_level ID of the level being cancelled if specified
 * @return void
 *
 * @since 1.2.1 - BUG FIX: Fatal error when BuddyPress is inactive/not installed
 */
function pmpro_bp_set_member_types( $level_id, $user_id, $cancel_level ) {
	
	// Exit if BuddyPress isn't active
	if ( ! function_exists( 'bp_set_member_type' ) ) {
		return;
	}
	$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );
	
	if( !empty( $cancel_level ) ) {
		$pmpro_bp_old_level_options = pmpro_bp_get_level_options( $cancel_level );
	} else {
		$pmpro_bp_old_level_options = pmpro_bp_get_user_old_level_options( $user_id );
	}
	
	$old_member_types = $pmpro_bp_old_level_options['pmpro_bp_member_types'];
	$new_member_types = $pmpro_bp_options['pmpro_bp_member_types'];
	
	if( !empty( $old_member_types ) ) {
		foreach( $old_member_types as $member_type ) {
			bp_remove_member_type( $user_id, $member_type );
		}
	}

	if( !empty( $new_member_types ) ) {
		foreach( $new_member_types as $member_type ) {
			//make sure we can sign up for more than one member type
			bp_set_member_type( $user_id, $member_type, true );
		}
	}
}
add_action( 'pmpro_after_change_membership_level', 'pmpro_bp_set_member_types', 10, 3 );
