<?php
/**
 * PMPro Approvals support.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reset groups and member types when a user is approved.
 */
function pmpro_bp_pmpro_approvals_after_approve_member( $user_id, $level_id ) {
	$approved_level_ids = pmpro_bp_get_approved_level_ids_for_user( $user_id );
	pmpro_bp_update_user_groups( $user_id, array(), $approved_level_ids );
	pmpro_bp_update_user_member_types( $user_id, array(), $approved_level_ids );
}
add_action( 'pmpro_approvals_after_approve_member', 'pmpro_bp_pmpro_approvals_after_approve_member', 10, 2 );

/**
 * Reset groups and member types when a user is denied or reset.
 */
function pmpro_bp_pmpro_approvals_after_deny_member( $user_id, $level_id ) {
	$approved_level_ids = pmpro_bp_get_approved_level_ids_for_user( $user_id );
	pmpro_bp_update_user_groups( $user_id, array( $level_id ), $approved_level_ids );
	pmpro_bp_update_user_member_types( $user_id, array( $level_id ), $approved_level_ids );
}
add_action( 'pmpro_approvals_after_deny_member', 'pmpro_bp_pmpro_approvals_after_deny_member', 10, 2 );
add_action( 'pmpro_approvals_after_reset_member', 'pmpro_bp_pmpro_approvals_after_deny_member', 10, 2 );