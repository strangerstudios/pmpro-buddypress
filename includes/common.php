<?php
/*
	Common functions used throughout the plugin.
*/

/**
 * Get the PMPro BuddyPress options for a specific level.
 * Level 0 contains options for non-member users.
 */
function pmpro_bp_get_level_options($level_id) {
	if( $level_id == -1 ) {
		// defaults
		$options = array(			
			'pmpro_bp_restrictions'				=> 0,
			'pmpro_bp_group_creation'			=> 0,
			'pmpro_bp_group_single_viewing'		=> 0,
			'pmpro_bp_groups_page_viewing'		=> 0,
			'pmpro_bp_groups_join'				=> 0,			
			'pmpro_bp_private_messaging'		=> 0,
			'pmpro_bp_public_messaging'			=> 0,
			'pmpro_bp_send_friend_request'		=> 0,
			'pmpro_bp_member_directory'			=> 0,
			'pmpro_bp_group_automatic_add'		=> array(),
			'pmpro_bp_group_can_request_invite'	=> array(),
			'pmpro_bp_member_types'				=> array());
	} elseif ( $level_id == 0 ) {
		// non-member users
		$options = get_option( 'pmpro_bp_options_users' );
	} else {
		// level options
		$options = get_option( 'pmpro_bp_options_' . $level_id );

		// might be set to mirror non-member users
		if( $options['pmpro_bp_restrictions'] == 0 ) {
			$non_member_user_options = pmpro_bp_get_level_options( 0 );
			$options['pmpro_bp_restrictions'] = $non_member_user_options['pmpro_bp_restrictions'];
			$options['pmpro_bp_group_creation'] = $non_member_user_options['pmpro_bp_group_creation'];
			$options['pmpro_bp_group_single_viewing'] = $non_member_user_options['pmpro_bp_group_single_viewing'];
			$options['pmpro_bp_groups_page_viewing'] = $non_member_user_options['pmpro_bp_groups_page_viewing'];
			$options['pmpro_bp_groups_join'] = $non_member_user_options['pmpro_bp_groups_join'];
			$options['pmpro_bp_private_messaging'] = $non_member_user_options['pmpro_bp_private_messaging'];
			$options['pmpro_bp_public_messaging'] = $non_member_user_options['pmpro_bp_public_messaging'];
			$options['pmpro_bp_send_friend_request'] = $non_member_user_options['pmpro_bp_send_friend_request'];
			$options['pmpro_bp_member_directory'] = $non_member_user_options['pmpro_bp_member_directory'];			
		}
	}

	return $options;
}

/**
 * Get options for a user based on their level.
 */
function pmpro_bp_get_user_options( $user_id = NULL ) {
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if( !empty( $user_id ) ) {		
		$level = pmpro_getMembershipLevelForUser( $user_id );		
	}

	if( !empty( $level ) ) {
		$level_id = $level->id;
	} else {
		$level_id = 0;	//non-member user
	}
	
	$pmpro_bp_options = pmpro_bp_get_level_options( $level_id );

	$pmpro_bp_options = apply_filters( 'pmpro_bp_get_user_options', $pmpro_bp_options, $user_id );

	return $pmpro_bp_options;
}

/**
 * Get options for a user's "last" old level.
 */
function pmpro_bp_get_user_old_level_options( $user_id = NULL ) {
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if( !empty( $user_id ) ) {		
		$level = pmpro_getMembershipLevelForUser( $user_id );		
	}

	if( !empty( $level ) ) {
		$level_id = $level->id;
	} else {
		$level_id = 0;	//non-member user
	}

	global $wpdb;

	$sqlQuery = $wpdb->prepare("SELECT DISTINCT(membership_id) FROM $wpdb->pmpro_memberships_users WHERE user_id = %d AND membership_id NOT IN(%s) AND status IN('admin_changed', 'admin_cancelled', 'cancelled', 'changed', 'expired', 'inactive') AND modified > NOW() - INTERVAL 15 MINUTE ORDER BY id DESC LIMIT 1", $user_id, $level_id);
	$old_level_id = $wpdb->get_var($sqlQuery);

	if( empty( $old_level_id ) ) {
		$old_level_id = 0;
	}

	$pmpro_bp_options = pmpro_bp_get_level_options( $old_level_id );

	return $pmpro_bp_options;
}

/**
 * Redirect to the Access Required page.
 */
function pmpro_bp_redirect_to_access_required_page() {
	do_action( 'pmpro_bp_redirect_to_access_required_page' );

	global $pmpro_pages;
	wp_redirect( get_permalink( $pmpro_pages['pmprobp_restricted'] ) );
	exit;
}

/**
 * Check if a user has access to a specific feature
 */
function pmpro_bp_user_can( $check, $user_id = NULL ) {
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}	
	
	$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );
	if( strpos( $check, 'pmpro_bp_' ) === false ) {
		$check = 'pmpro_bp_' . $check;
	}
	
	$can = ( $pmpro_bp_options['pmpro_bp_restrictions'] == 1 || $pmpro_bp_options[ $check ] == 1 );

	$can = apply_filters( 'pmpro_bp_user_can', $can, $check, $user_id );
		
	return $can;
}

/**
 * Check if a user can join a specific group.
 */
function pmpro_bp_user_can_join_group( $group_id, $user_id = NULL ) {
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}
	
	if( pmpro_bp_user_can( 'groups_join', $user_id ) ) {
		// they can join any group
		$can_join = true;
	} else {
		// check if they can joint his specific group
		$can_join = false;
		$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );		
		if( is_array( $pmpro_bp_options['pmpro_bp_group_automatic_add'] ) && in_array( $group_id, $pmpro_bp_options['pmpro_bp_group_automatic_add'] ) ) {
			$can_join = true;
		}
		if( is_array( $pmpro_bp_options['pmpro_bp_group_can_request_invite'] ) && in_array( $group_id, $pmpro_bp_options['pmpro_bp_group_can_request_invite'] ) ) {
			$can_join = true;
		}
	}

	return $can_join;
}
