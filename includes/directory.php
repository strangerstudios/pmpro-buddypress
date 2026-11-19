<?php
/*
	Code to edit the BuddyPress members directory and search.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pmpro_bp_directory_init() {
	// Don't do this if PMPro is deactivated
	if( !defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	// Only filter the directory and its count when the directory is locked.
	if ( ! pmpro_bp_is_member_directory_locked() ) {
		return;
	}

	add_action( 'bp_pre_user_query_construct', 'pmpro_bp_bp_pre_user_query_construct', 1, 1 );
	add_filter( 'bp_get_total_member_count', 'pmpro_bp_bp_get_total_member_count' );
}
add_action('init', 'pmpro_bp_directory_init', 20);

function pmpro_bp_bp_pre_user_query_construct( $query_array ) {
	// Only apply this to the directory.
	if ( 'members' != bp_current_component() ) {
		// The AJAX request that loads the directory's list gets its component from the page it says it was sent from, so filter it anywhere.
		if ( ! doing_action( 'wp_ajax_members_filter' ) && ! doing_action( 'wp_ajax_nopriv_members_filter' ) ) {
			return;
		}

		// The same request loads lists of specific users, like a user's friends or friend requests and a group's members or admins.
		if ( $query_array instanceof BP_Group_Member_Query || ! empty( $query_array->query_vars['user_id'] ) || ! empty( $query_array->query_vars['include'] ) || ! empty( $query_array->query_vars['user_ids'] ) ) {
			return;
		}
	}

	$include = isset( $query_array->query_vars['include'] ) ? $query_array->query_vars['include'] : '';
	$query_array->query_vars['include'] = pmpro_bp_limit_user_ids_to_directory( $include );
}

/**
 * Apply the member directory settings to the list of members in the BuddyPress REST API.
 *
 * The REST API doesn't load the Members page, so pmpro_bp_bp_pre_user_query_construct() doesn't run for it.
 *
 * @since TBD
 *
 * @param array           $args    The arguments for the members query.
 * @param WP_REST_Request $request The REST API request.
 * @return array
 */
function pmpro_bp_rest_members_get_items_query_args( $args, $request ) {
	// Only filter the list when the directory is locked. Administrators need every user, like when adding members to a block.
	if ( ! defined( 'PMPRO_VERSION' ) || ! pmpro_bp_is_member_directory_locked() || current_user_can( 'manage_options' ) ) {
		return $args;
	}

	// BuddyPress skips the rest of the query when specific users are asked for.
	if ( ! empty( $args['user_ids'] ) ) {
		$args['user_ids'] = pmpro_bp_limit_user_ids_to_directory( $args['user_ids'] );
	}

	// A user's friends aren't the directory. Neither are the users they follow or their followers in BuddyBoss.
	if ( ! empty( $args['user_id'] ) && bp_is_active( 'friends' ) ) {
		return $args;
	}
	if ( 'buddyboss' === bp_rest_namespace() && in_array( $request->get_param( 'scope' ), array( 'following', 'followers' ), true ) ) {
		return $args;
	}

	$args['include'] = pmpro_bp_limit_user_ids_to_directory( isset( $args['include'] ) ? $args['include'] : '' );

	return $args;
}
add_filter( 'bp_rest_members_get_items_query_args', 'pmpro_bp_rest_members_get_items_query_args', 10, 2 );

/**
 * Limit a list of user IDs to the users in the member directory.
 *
 * @since TBD
 *
 * @param array|string|bool $include User IDs as an array or comma-separated string, or an empty value for no list.
 * @return array The user IDs that are in the directory. array( 0 ) if there are none.
 */
function pmpro_bp_limit_user_ids_to_directory( $include ) {
	$pmpro_bp_members_in_directory = pmpro_bp_get_members_in_directory();
	if ( ! empty( $pmpro_bp_members_in_directory ) ) {
		// Normalize a non-empty string include value into an array. We can't use !empty() here
		// because '0' is a valid sentinel (BP Profile Search sets include to '0' when a search
		// returns no matches). is_string() guards against BP's default include value of false.
		if ( is_string( $include ) && $include !== '' ) {
			$include = explode( ',', $include );
		}

		if ( is_array( $include ) ) {
			// Compute the intersect of members and include value.
			$include = array_intersect( $include, $pmpro_bp_members_in_directory );
			// If the intersect is empty, force "no users". BP treats an empty include as "no filter"
			// and would otherwise return all site users.
			if ( empty( $include ) ) {
				$include = array( 0 );
			}
			return $include;
		} else {
			// No include filter set, lock the directory to PMPro members.
			return $pmpro_bp_members_in_directory;
		}
	} else {
		// No members, block the directory.
		return array( 0 );
	}
}

function pmpro_bp_bp_get_total_member_count($count) {
	$count = count( pmpro_bp_get_members_in_directory() );
	return $count;
}

function pmpro_bp_get_members_in_directory() {
	global $wpdb, $pmpro_levels, $pmpro_bp_members_in_directory;

	static $include_users = null;
	if ( ! is_null( $include_users ) ) {
		return $include_users;
	}

	if( !function_exists( 'pmpro_getAllLevels' ) ) {
		$include_users = array();
		$pmpro_bp_members_in_directory = $include_users; // For backwards compatibility.
		return $include_users;
	}

	$pmpro_levels = pmpro_getAllLevels(true, true);
	
	if ( empty( $pmpro_levels ) ) {
		$include_users = array();
		$pmpro_bp_members_in_directory = $include_users; // For backwards compatibility.
		return $include_users;
	}

	//see if we should include them in the member directory.
	$include_levels = array();

	foreach($pmpro_levels as $level) {
		$pmpro_bp_options = pmpro_bp_get_level_options( $level->id );

		if( $pmpro_bp_options['pmpro_bp_member_directory'] == 1 || $pmpro_bp_options['pmpro_bp_restrictions'] == PMPROBP_GIVE_ALL_ACCESS) {
			$include_levels[] = $level->id;
		}
	}
	
	if ( empty( $include_levels ) ) {
		$include_users = array();
		$pmpro_bp_members_in_directory = $include_users; // For backwards compatibility.
		return $include_users;
	}

	$sql_parts = array();

	/**
	 * Adding in empty keys for supported array args
	 */

	$sql_parts['SELECT'] = "SELECT DISTINCT m.user_id FROM $wpdb->pmpro_memberships_users as m ";

	$sql_parts['JOIN'] = "";

	$sql_parts['WHERE'] = "WHERE m.membership_id IN (" . implode( ",", array_map( "intval", $include_levels ) ) . ") AND m.status = 'active' ";

	$sql_parts['GROUP'] = "";
	$sql_parts['ORDER'] = "";
	$sql_parts['LIMIT'] = "";

	/**
	 * Filter each SQL part to allow for extended queries in the directory
	 *
	 * @since 1.3
	 *
	 * @param array $sql_parts Contains each sql part
	 * @param array $include_levels Levels that should be included in the query
	 */
	$sql_parts = apply_filters( 'pmpro_bp_directory_sql_parts', $sql_parts, $include_levels );

	$sqlQuery = $sql_parts['SELECT'] . $sql_parts['JOIN'] . $sql_parts['WHERE'] . $sql_parts['GROUP'] . $sql_parts['ORDER'] . $sql_parts['LIMIT'];	

	$wpdb->flush();
	
	$include_users = $wpdb->get_col( $sqlQuery ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Static SQL on a PMPro custom table; level IDs are intval'd, and pmpro_bp_directory_sql_parts is a developer filter.

	$pmpro_bp_members_in_directory = $include_users; // For backwards compatibility.

	return $include_users;
}

function pmpro_bp_is_member_directory_locked() {
	$non_user_options = pmpro_bp_get_level_options( 0 );

	if( $non_user_options['pmpro_bp_restrictions'] != PMPROBP_GIVE_ALL_ACCESS
	 	&& ! $non_user_options['pmpro_bp_member_directory'] ) {
		return true;
	} else {
		return false;
	}
}
