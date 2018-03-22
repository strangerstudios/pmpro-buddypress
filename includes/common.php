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
	}

	return $options;
}