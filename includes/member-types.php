<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add and remove a user from BuddyPress member types
 * when their level changes.
 * @param  int $level_id     ID of the level the user is being given.
 * @param  int $user_id      ID of the user being given the level.
 * @param  int $cancel_level ID of the level being cancelled if specified
 * @return void
 *
 * @since 1.2.1 - BUG FIX: Fatal error when BuddyPress is inactive/not installed
 *
 * @deprecated 1.4 - Use pmpro_bp_member_types_pmpro_after_all_membership_level_changes() instead.
 */
function pmpro_bp_set_member_types( $level_id, $user_id, $cancel_level ) {
	// Show deprecation notice.
	_deprecated_function( __FUNCTION__, '1.4', 'pmpro_bp_member_types_pmpro_after_all_membership_level_changes()' );
	
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

/**
 * Handle member type changes when users' membership level changes.
 *
 * @param array $pmpro_old_user_levels Array of old user levels before change.
 */
function pmpro_bp_member_types_pmpro_after_all_membership_level_changes( $pmpro_old_user_levels ) {
	foreach ( $pmpro_old_user_levels as $user_id => $old_levels ) {
		pmpro_bp_update_user_member_types( $user_id, wp_list_pluck( $old_levels, 'id' ), pmpro_bp_get_approved_level_ids_for_user( $user_id ) );
	}
}
add_action( 'pmpro_after_all_membership_level_changes', 'pmpro_bp_member_types_pmpro_after_all_membership_level_changes' );

/**
 * Swap a user's member types for their old levels for the member types for their new levels.
 *
 * @since TBD
 *
 * @param int   $user_id       ID of the user.
 * @param array $old_level_ids IDs of the levels the user had before the change.
 * @param array $new_level_ids IDs of the levels the user has after the change.
 */
function pmpro_bp_update_user_member_types( $user_id, $old_level_ids, $new_level_ids ) {
	// Exit if BuddyPress isn't active
	if ( ! function_exists( 'bp_set_member_type' ) ) {
		return;
	}

	$old_member_types = array();
	$new_member_types = array();

	// Get the member types for the old levels.
	foreach ( $old_level_ids as $old_level_id ) {
		$old_level_options = pmpro_bp_get_level_options( $old_level_id );
		$old_member_types  = array_merge( $old_member_types, empty( $old_level_options['pmpro_bp_member_types'] ) ? array() : $old_level_options['pmpro_bp_member_types'] );
	}

	// Get the member types for the new levels.
	foreach ( $new_level_ids as $new_level_id ) {
		$new_level_options = pmpro_bp_get_level_options( $new_level_id );
		$new_member_types  = array_merge( $new_member_types, empty( $new_level_options['pmpro_bp_member_types'] ) ? array() : $new_level_options['pmpro_bp_member_types'] );
	}

	// Remove duplicates.
	$old_member_types = array_unique( $old_member_types );
	$new_member_types = array_unique( $new_member_types );

	// Add new member types.
	foreach ( $new_member_types as $member_type ) {
		bp_set_member_type( $user_id, $member_type, true );
	}

	// Remove old member types.
	foreach ( $old_member_types as $member_type ) {
		if ( ! in_array( $member_type, $new_member_types ) ) {
			bp_remove_member_type( $user_id, $member_type );
		}
	}
}