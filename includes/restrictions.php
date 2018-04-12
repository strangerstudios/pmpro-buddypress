<?php
/*
	Code to lock down BuddyPress features based on level settings.
*/

/**
 * Make sure administrators can do everything
 */
function pmpro_bp_admins_can_do_everything( $can, $check, $user_id ) {
	if( user_can( $user_id, 'manage_options') ) {
		$can = true;
	}

	return $can;
}
add_filter( 'pmpro_bp_user_can', 'pmpro_bp_admins_can_do_everything', 10, 3 );

/**
 * Restrict viewing of the groups page or individual
 * groups pages if the user doesn't have access.
 */
function pmpro_bp_restrict_group_viewing() {
	global $bp, $pmpro_pages;

	//Group (Single) Viewing Restrictions - which levels can view individual groups?
	if ( bp_is_group() 
		&& !pmpro_bp_user_can( 'group_single_viewing' ) 
		&& !pmpro_bp_user_can_view_group( $bp->groups->current_group->id ) ) {		
		pmpro_bp_redirect_to_access_required_page();
	}

	//Group Viewing restrictions - which levels can view the groups page?
	if( !empty( $bp->pages->groups ) 
		&& bp_is_current_component( $bp->pages->groups->slug )
		&& !bp_is_group()
		&& !pmpro_bp_user_can( 'groups_page_viewing' ) ) {
		pmpro_bp_redirect_to_access_required_page();
	}
}
add_action( 'template_redirect', 'pmpro_bp_restrict_group_viewing' );

/**
 * Hide the Create Group button if group creation is restricted
 */
function pmpro_bp_bp_get_group_create_button( $button_args ) { 
	global $pmpro_pages;
	
	if(!pmpro_bp_user_can( 'pmpro_bp_group_creation' ) ) {
		$button_args['link_href'] =	get_permalink($pmpro_pages['pmprobp_restricted']);
	}
	
    return $button_args;
}         
add_filter( 'bp_get_group_create_button', 'pmpro_bp_bp_get_group_create_button', 10, 1 );

/**
 * Hide the Join Group button if joining groups is restricted
 */
function pmpro_bp_bp_get_groups_join_button( $button_args, $group ) {			
	if( ( $button_args['id'] === 'join_group' || $button_args['id'] === 'request_membership' ) && !pmpro_bp_user_can_join_group( $group->id ) ) {
		global $pmpro_pages;
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
	}

	return $button_args;
}
add_filter( 'bp_get_group_join_button', 'pmpro_bp_bp_get_groups_join_button', 10, 2);

/**
 * Remove Nav Link to request an invite
 * if user doesn't have access to.
 */
function pmpro_bp_remove_request_membership_nav_link() {
	if ( ! bp_is_group() ) {
		return;
	}
	
	if( !pmpro_bp_user_can_join_group( bp_get_current_group_id() ) ) {
		global $pmpro_pages;
		$slug = bp_get_current_group_slug();
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);	
		bp_core_remove_subnav_item( $slug, 'request-membership' );
	}
}
add_action( 'bp_actions', 'pmpro_bp_remove_request_membership_nav_link' );

/**
 * Redirect away from private messaging page if the user
 * doesn't have access to it.
 */
function pmpro_bp_restrict_private_messaging() {
	if( bp_is_current_component('messages') && !pmpro_bp_user_can( 'private_messaging' ) ) {
		pmpro_bp_redirect_to_access_required_page();
	}
}
add_action( 'wp','pmpro_bp_restrict_private_messaging' );

/**
 * Remove the send private message button if the user
 * doesn't have access to it.
 */
function pmpro_bp_bp_get_send_message_button_args($args) {
	if( !pmpro_bp_user_can( 'private_messaging' ) ) {	
		global $pmpro_pages;
		$args['link_href'] = get_permalink( $pmpro_pages['pmprobp_restricted'] );
	}

	return $args;
}
add_filter( 'bp_get_send_message_button_args', 'pmpro_bp_bp_get_send_message_button_args' );

/**
 * Remove the send public message button if the user
 * doesn't have access to it
 */
function pmpro_bp_bp_get_send_public_message_button($args) {
	if( !pmpro_bp_user_can( 'public_messaging' ) ) {	
		global $pmpro_pages;
		$args['link_href'] = get_permalink( $pmpro_pages['pmprobp_restricted'] );
	}
	
	return $args;
}
add_filter( 'bp_get_send_public_message_button', 'pmpro_bp_bp_get_send_public_message_button' );

/**
 * Remove the add friend button if the user
 * doesn't have access to it.
 */
function pmpro_bp_bp_get_add_friend_button($args) {
	if( !pmpro_bp_user_can( 'send_friend_request' ) ) {	
		global $pmpro_pages;
		$args['link_href'] = get_permalink( $pmpro_pages['pmprobp_restricted'] );
	}

	return $args;
}
add_filter( 'bp_get_add_friend_button', 'pmpro_bp_bp_get_add_friend_button' );

/**
 * Redirect away from any BuddyPress page if set to.
 */
function pmpro_bp_lockdown_all_bp() {
	
	if ( !function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		return;
	}
	
	if( !is_buddypress() ) {
		return;
	}
		
	global $current_user;
	$user_id = $current_user->ID;
	
	if( !empty( $user_id ) ) {
		$level = pmpro_getMembershipLevelForUser( $user_id );
	}

	if( !empty( $level ) ) {
		$level_id = $level->id;
	} else {
		$level_id = 0;	//non-member user
	}
		
	$pmpro_bp_options = pmpro_bp_get_level_options( $level_id );
	
	if( $pmpro_bp_options['pmpro_bp_restrictions'] == -1 ) {
		pmpro_bp_redirect_to_access_required_page();
	}
}
add_action( 'template_redirect', 'pmpro_bp_lockdown_all_bp', 50 );

/**
 * Redirect BuddyPress registration to PMPro
 * unless setting says not to.
 */
function pmpro_bp_buddypress_or_pmpro_registration() {
	global $post, $pmpro_pages;
	
	$bp_pages = get_option( 'bp-pages' );
	
	$pmpro_bp_register = get_option( 'pmpro_bp_registration_page' );
		
	if( !empty( $pmpro_bp_register ) && $pmpro_bp_register == 'buddypress' && ( $post->ID == $pmpro_pages['levels'] ) && !is_user_logged_in() ) {
		//Use BuddyPress Register page
		wp_redirect( get_permalink( $bp_pages['register'] ) );
		exit;
	}
	elseif( !empty( $pmpro_bp_register ) && $pmpro_bp_register == 'pmpro' && bp_is_register_page() )
	{
		//use PMPro Levels page
		$url = pmpro_url("levels");
		wp_redirect($url);
		exit;
	}
}
add_action( 'template_redirect', 'pmpro_bp_buddypress_or_pmpro_registration', 70 );

/**
 * Show level on BuddyPress profile
 * unless setting says not to
 */
function pmpro_bp_show_level_on_bp_profile() {
	
	if ( !function_exists('pmpro_getMembershipLevelForUser') ) {
		return;
	}
	
	$level = pmpro_getMembershipLevelForUser(bp_displayed_user_id());
	
	$show_level = get_option('pmpro_bp_show_level_on_bp_profile');
	
	if( $show_level == 'yes' && !empty( $level ) ) {
	?>
	<div class="pmpro_bp_show_level_on_bp_profile">
		<strong><?php _e( 'Membership Level', 'pmpro-buddypress' );?>: <?php echo $level->name; ?> </strong>
	</div>
	<?php
	}
}
add_filter( 'bp_profile_header_meta', 'pmpro_bp_show_level_on_bp_profile' );

/**
 * Restricted message shortcode.
 */
function pmpro_bp_restricted_message() {
	return __('This content is restricted.', 'pmpro-buddypress' ) . ' ';
}
add_shortcode( 'pmpro_buddypress_restricted', 'pmpro_bp_restricted_message' );
