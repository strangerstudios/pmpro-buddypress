<?php
/**
 * PMPro Approvals support.
 */

/**
 * Reset groups and member types when a user is approved.
 */
function pmpro_bp_pmpro_approvals_after_approve_member( $user_id, $level_id ) {
	pmpro_bp_set_member_groups( $level_id, $user_id, NULL );
	pmpro_bp_set_member_types( $level_id, $user_id, NULL );
}
add_action( 'pmpro_approvals_after_approve_member', 'pmpro_bp_pmpro_approvals_after_approve_member', 10, 2 );

/**
 * Reset groups and member types when a user is denied or reset.
 */
function pmpro_bp_pmpro_approvals_after_deny_member( $user_id, $level_id ) {
	pmpro_bp_set_member_groups( $level_id, $user_id, $level_id );
	pmpro_bp_set_member_types( $level_id, $user_id, $level_id );
}
add_action( 'pmpro_approvals_after_deny_member', 'pmpro_bp_pmpro_approvals_after_deny_member', 10, 2 );
add_action( 'pmpro_approvals_after_reset_member', 'pmpro_bp_pmpro_approvals_after_deny_member', 10, 2 );