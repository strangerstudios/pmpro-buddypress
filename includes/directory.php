<?php
/*
	Code to edit the BuddyPress members directory and search.
*/

function pmpro_bp_bp_before_directory_members() {
	global $pmpro_bp_members_in_directory;
	$pmpro_bp_members_in_directory = pmpro_bp_get_members_in_directory();
	
	add_action( 'bp_pre_user_query_construct', 'pmpro_bp_bp_pre_user_query_construct', 1, 1 );
	add_filter( 'bp_get_total_member_count', 'pmpro_bp_bp_get_total_member_count' );
}
add_action( 'bp_before_directory_members', 'pmpro_bp_bp_before_directory_members' );

function pmpro_bp_bp_pre_user_query_construct( $query_array ) {
	global $pmpro_bp_members_in_directory;		
	$query_array->query_vars['include'] = $pmpro_bp_members_in_directory;
}

function pmpro_bp_bp_get_total_member_count($count) {
	global $pmpro_bp_members_in_directory;
	$count = count($pmpro_bp_members_in_directory);
	return $count;
}

function pmpro_bp_get_members_in_directory() {
	global $wpdb, $pmpro_levels;
		
	$pmpro_levels = pmpro_getAllLevels(false, true);

	//see if we should include them in the member directory.
	$include_levels = array();

	foreach($pmpro_levels as $level) {
		$pmpro_bp_options = pmpro_bp_get_level_options( $level->id );

		if( $pmpro_bp_options['pmpro_bp_member_directory'] == 1 ) {
			$include_levels[] = $level->id;
		}
	}

	$sql = "SELECT DISTINCT user_id FROM $wpdb->pmpro_memberships_users WHERE membership_id IN (" . implode(",", array_map("intval", $include_levels)) . ") AND status = 'active'";

	$wpdb->flush();
	$include_users = $wpdb->get_col($sql);
	
	return $include_users;
}