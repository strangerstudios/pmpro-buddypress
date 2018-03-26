<?php
/*
	Common functions used throughout the plugin.
*/

/**
 * Get the PMPro BuddyPress options for a specific level.
 * Level 0 contains options for non-member users.
 */
function pmpro_bp_getLevelOptions($level_id) {
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
			$non_member_user_options = pmpro_bp_getLevelOptions( 0 );
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

	if( !empty( $user_id ) ) {		
		$level = pmpro_getMembershipLevelForUser( $user_id );		
	}
	
	if( !empty( $level_id ) ) {
		$level_id = $level->id;
	} else {
		$level_id = 0;	//non-member user
	}
	
	$pmpro_bp_options = pmpro_bp_getLevelOptions( $level_id );
	if( strpos( $check, 'pmpro_bp_' ) === false ) {
		$check = 'pmpro_bp_' . $check;
	}	
	$can = ( $pmpro_bp_options['pmpro_bp_restrictions'] == 1 || $pmpro_bp_options[ $check ] == 1 );

	$can = apply_filters( 'pmpro_bp_user_can', $can, $check, $user_id );
		
	return $can;
}
