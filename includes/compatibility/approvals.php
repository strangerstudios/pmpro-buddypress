<?php
/**
 * Set non-approved members to non-member settings/configuration.
 * @since TBD
 */
function pmpro_bp_handle_pending_approvals( $pmpro_bp_all_options, $user_id ) {
    
    // Bail if the user is logged-out.
    if ( empty( $user_id ) ) {
        return $pmpro_bp_all_options;
    }

    // Check if approvals is installed.
    if ( ! class_exists( 'PMPro_Approvals' ) ) {
        return $pmpro_bp_all_options;
    }

    $levels = pmpro_getMembershipLevelsForUser( $user_id );

    foreach( $levels as $level ) {
        if ( ! PMPro_Approvals::isApproved( $user_id, $level->id ) ) { //Change this check for each level.
            $is_approved = false;
            break;
        }
    }

    // If the user is not approved, then give them non-member BuddyPress settings.
    if ( ! $is_approved ) {
        $pmpro_bp_all_options = pmpro_bp_get_level_options( 0 );
    }

    return $pmpro_bp_all_options;

}
add_filter( 'pmpro_bp_get_user_options', 'pmpro_bp_handle_pending_approvals', 10, 2 );