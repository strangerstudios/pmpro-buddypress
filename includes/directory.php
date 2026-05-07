<?php
/*
	Code to edit the BuddyPress members directory and search.
*/

function pmpro_bp_directory_init() {
	// Don't do this if PMPro is deactivated
	if( !defined( 'PMPRO_VERSION' ) ) {
		return;
	}

	global $pmpro_bp_members_in_directory;
	$pmpro_bp_members_in_directory = pmpro_bp_get_members_in_directory();
	add_action( 'bp_pre_user_query_construct', 'pmpro_bp_bp_pre_user_query_construct', 1, 1 );
	add_filter( 'bp_get_total_member_count', 'pmpro_bp_bp_get_total_member_count' );
}
add_action('init', 'pmpro_bp_directory_init', 20);

function pmpro_bp_bp_pre_user_query_construct( $query_array ) {
	// If no setting is locking the member directory, let's bail.
	if( !pmpro_bp_is_member_directory_locked() ) {
		return;
	}
	
	// Only apply this to the directory.
	if ( 'members' != bp_current_component() ) {
		return;
	}

	global $pmpro_bp_members_in_directory;
	if ( ! empty( $pmpro_bp_members_in_directory ) ) {
		$include = isset( $query_array->query_vars['include'] ) ? $query_array->query_vars['include'] : '';

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
			$query_array->query_vars['include'] = $include;
		} else {
			// No include filter set, lock the directory to PMPro members.
			$query_array->query_vars['include'] = $pmpro_bp_members_in_directory;
		}
	} else {
		// No members, block the directory.
		$query_array->query_vars['include'] = array( 0 );
	}
}

function pmpro_bp_bp_get_total_member_count($count) {
	global $pmpro_bp_members_in_directory;

	$count = count($pmpro_bp_members_in_directory);
	return $count;
}

function pmpro_bp_get_members_in_directory() {
	global $wpdb, $pmpro_levels;

	if( !function_exists( 'pmpro_getAllLevels' ) ) {
		return array();
	}

	$pmpro_levels = pmpro_getAllLevels(true, true);
	
	if ( empty( $pmpro_levels ) ) {
		return array();
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
		return array();
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
	
	$include_users = $wpdb->get_col( $sqlQuery );

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
