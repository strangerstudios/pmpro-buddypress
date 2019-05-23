<?php
/*
	Common functions used throughout the plugin.
*/

/**
 * Get the PMPro BuddyPress options for a specific level.
 * Level 0 contains options for non-member users.
 */
function pmpro_bp_get_level_options( $level_id ) {
	$default_options = array(
		'pmpro_bp_restrictions'             => 0,
		'pmpro_bp_group_creation'           => 0,
		'pmpro_bp_group_single_viewing'     => 0,
		'pmpro_bp_groups_page_viewing'      => 0,
		'pmpro_bp_groups_join'              => 0,
		'pmpro_bp_private_messaging'        => 0,
		'pmpro_bp_public_messaging'         => 0,
		'pmpro_bp_send_friend_request'      => 0,
		'pmpro_bp_member_directory'         => 0,
		'pmpro_bp_group_automatic_add'      => array(),
		'pmpro_bp_group_can_request_invite' => array(),
		'pmpro_bp_member_types'             => array(),
	);

	if ( $level_id == -1 ) {
		// defaults
		$options = $default_options;
	} elseif ( $level_id == 0 ) {
		// non-member users
		$options = get_option( 'pmpro_bp_options_users', $default_options );
	} else {
		// level options
		$options = get_option( 'pmpro_bp_options_' . $level_id, $default_options );

		// might be set to mirror non-member users
		if ( $options['pmpro_bp_restrictions'] == PMPROBP_USE_NON_MEMBER_SETTINGS ) {
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

	// Fill in defaults
	$options = array_merge( $default_options, $options );

	return $options;
}

/**
 * Get options for a user based on their level.
 */
function pmpro_bp_get_user_options( $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	// Grab levels if PMPro is active
	if ( function_exists( 'pmpro_getMembershipLevelsForUser' ) ) {
		if ( ! empty( $user_id ) ) {
			$levels = pmpro_getMembershipLevelsForUser( $user_id );
		}
		// check for level because Admins often don't think to give themselves a level
		if ( ! empty( $levels ) ) {
			// Add Ons like PMPro Approvals filter pmpro_hasMembershipLevel, so let's double check
			$new_levels = array();
			foreach ( $levels as $level ) {
				if ( pmpro_hasMembershipLevel( $level->id, $user_id ) ) {
					$new_levels[] = $level;
				}
			}
			unset( $level );
			$levels = $new_levels;
		}
	}

	// we need the ids in a separate array
	if ( ! empty( $levels ) ) {
		$level_ids = array();
		foreach ( $levels as $level ) {
			$level_ids[] = $level->id;
		}
	} else {
		$level_ids = null;  // non-member user
	}

	$pmpro_bp_all_options = pmpro_bp_get_level_options( 0 );

	if ( ! empty( $level_ids ) ) {
		foreach ( $level_ids as $level_id ) {
			$pmpro_bp_options = pmpro_bp_get_level_options( $level_id );

			// pmpro_bp_restrictions
			$pmpro_bp_all_options['pmpro_bp_restrictions'] = max( $pmpro_bp_all_options['pmpro_bp_restrictions'], $pmpro_bp_options['pmpro_bp_restrictions'] );
			if ( $pmpro_bp_all_options['pmpro_bp_restrictions'] == PMPROBP_GIVE_ALL_ACCESS || $pmpro_bp_options['pmpro_bp_restrictions'] == PMPROBP_GIVE_ALL_ACCESS ) {
				$pmpro_bp_all_options['pmpro_bp_restrictions'] = PMPROBP_GIVE_ALL_ACCESS;
			}

			// module restrictions
			$pmpro_bp_all_options['pmpro_bp_group_creation'] = max( 0, $pmpro_bp_all_options['pmpro_bp_group_creation'], $pmpro_bp_options['pmpro_bp_group_creation'] );
			$pmpro_bp_all_options['pmpro_bp_group_single_viewing'] = max( $pmpro_bp_all_options['pmpro_bp_group_single_viewing'], $pmpro_bp_options['pmpro_bp_group_single_viewing'] );
			$pmpro_bp_all_options['pmpro_bp_groups_page_viewing'] = max( $pmpro_bp_all_options['pmpro_bp_groups_page_viewing'], $pmpro_bp_options['pmpro_bp_groups_page_viewing'] );
			$pmpro_bp_all_options['pmpro_bp_groups_join'] = max( $pmpro_bp_all_options['pmpro_bp_groups_join'], $pmpro_bp_options['pmpro_bp_groups_join'] );
			$pmpro_bp_all_options['pmpro_bp_private_messaging'] = max( $pmpro_bp_all_options['pmpro_bp_private_messaging'], $pmpro_bp_options['pmpro_bp_private_messaging'] );
			$pmpro_bp_all_options['pmpro_bp_public_messaging'] = max( $pmpro_bp_all_options['pmpro_bp_public_messaging'], $pmpro_bp_options['pmpro_bp_public_messaging'] );
			$pmpro_bp_all_options['pmpro_bp_send_friend_request'] = max( $pmpro_bp_all_options['pmpro_bp_send_friend_request'], $pmpro_bp_options['pmpro_bp_send_friend_request'] );
			$pmpro_bp_all_options['pmpro_bp_member_directory'] = max( $pmpro_bp_all_options['pmpro_bp_member_directory'], $pmpro_bp_options['pmpro_bp_member_directory'] );

			// groups to add
			$pmpro_bp_all_options['pmpro_bp_group_automatic_add'] = array_unique( array_merge( (array) $pmpro_bp_all_options['pmpro_bp_group_automatic_add'], (array) $pmpro_bp_options['pmpro_bp_group_automatic_add'] ) );

			// groups to invite
			$pmpro_bp_all_options['pmpro_bp_group_can_request_invite'] = array_unique( array_merge( (array) $pmpro_bp_all_options['pmpro_bp_group_can_request_invite'], (array) $pmpro_bp_options['pmpro_bp_group_can_request_invite'] ) );

			// member types
			$pmpro_bp_all_options['pmpro_bp_member_types'] = array_unique( array_merge( (array) $pmpro_bp_all_options['pmpro_bp_member_types'], (array) $pmpro_bp_options['pmpro_bp_member_types'] ) );
		}
	}

	$pmpro_bp_all_options = apply_filters( 'pmpro_bp_get_user_options', $pmpro_bp_all_options, $user_id );

	return $pmpro_bp_all_options;
}

/**
 * Get options for a user's "last" old level.
 */
function pmpro_bp_get_user_old_level_options( $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if ( ! empty( $user_id ) ) {
		$level = pmpro_getMembershipLevelForUser( $user_id );
	}

	if ( ! empty( $level ) ) {
		$level_id = $level->id;
	} else {
		$level_id = 0;  // non-member user
	}

	global $wpdb;

	$sqlQuery = $wpdb->prepare(
		"
		SELECT DISTINCT(membership_id)
		FROM $wpdb->pmpro_memberships_users
		WHERE user_id = %d AND membership_id NOT
		IN(%s)
		AND status
		IN('admin_changed', 'admin_cancelled', 'cancelled', 'changed', 'expired', 'inactive')
		AND modified > NOW() - INTERVAL 15 MINUTE
		ORDER BY id
		DESC LIMIT 1
		",
		$user_id, $level_id
	);
	$old_level_id = $wpdb->get_var( $sqlQuery );

	if ( empty( $old_level_id ) ) {
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
	if ( ! empty( $pmpro_pages['pmprobp_restricted'] ) ) {
		$redirect_to = get_permalink( $pmpro_pages['pmprobp_restricted'] );
	} elseif ( ! empty( $pmpro_pages['levels'] ) ) {
		$redirect_to = get_permalink( $pmpro_pages['levels'] );
	} else {
		$redirect_to = home_url();
	}
	wp_redirect( $redirect_to );
	exit;
}

/**
 * Check if a user has access to a specific feature
 */
function pmpro_bp_user_can( $check, $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );
	if ( false === strpos( $check, 'pmpro_bp_' ) ) {
		$check = 'pmpro_bp_' . $check;
	}

	$can = ( PMPROBP_GIVE_ALL_ACCESS === $pmpro_bp_options['pmpro_bp_restrictions'] || 1 === $pmpro_bp_options[ $check ] );

	$can = apply_filters( 'pmpro_bp_user_can', $can, $check, $user_id );

	return $can;
}

/**
 * Check if a user can join a specific group.
 */
function pmpro_bp_user_can_join_group( $group_id, $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if ( pmpro_bp_user_can( 'groups_join', $user_id ) ) {
		// they can join any group
		$can_join = true;
	} else {
		// check if they can joint his specific group
		$can_join = false;
		$pmpro_bp_options = pmpro_bp_get_user_options( $user_id );
		if ( is_array( $pmpro_bp_options['pmpro_bp_group_automatic_add'] ) && in_array( $group_id, $pmpro_bp_options['pmpro_bp_group_automatic_add'] ) ) {
			$can_join = true;
		}
		if ( is_array( $pmpro_bp_options['pmpro_bp_group_can_request_invite'] ) && in_array( $group_id, $pmpro_bp_options['pmpro_bp_group_can_request_invite'] ) ) {
			$can_join = true;
		}
	}

	return $can_join;
}

/**
 * Check if a user can join a specific group.
 */
function pmpro_bp_user_can_view_group( $group_id, $user_id = null ) {
	if ( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if ( pmpro_bp_user_can( 'group_single_viewing', $user_id )
		|| pmpro_bp_user_can_join_group( $group_id, $user_id )
		|| groups_is_user_member( $user_id, $group_id ) ) {
		$can_view = true;
	} else {
		$can_view = false;
	}

	return $can_view;
}
