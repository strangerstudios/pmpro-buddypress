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

	//If BuddyPress is not active, don't worry
	if( empty( $bp ) ) {
		return;
	}

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
		$button_args['button_element'] = 'a';
	}
	
    return $button_args;
}         
add_filter( 'bp_get_group_create_button', 'pmpro_bp_bp_get_group_create_button', 10, 1 );

/**
 * Prevent users from creating groups
 * if their level doesn't allow it.
 */
function pmpro_bp_bp_user_can_create_groups( $can_create, $restricted ) {
	if ( $can_create && ! pmpro_bp_user_can( 'pmpro_bp_group_creation' ) && ! current_user_can( 'manage_options' ) ) {
		$can_create = false;
	}
	
	return $can_create;
}
add_filter( 'bp_user_can_create_groups', 'pmpro_bp_bp_user_can_create_groups', 10, 2);

/**
 * Hide the Join Group button if joining groups is restricted
 */
function pmpro_bp_disable_group_buttons( $button_args, $group ) {			
	if( ! isset( $button_args['id'] ) || ( $button_args['id'] === 'join_group' || $button_args['id'] === 'request_membership' || $button_args['id'] === 'group_membership' ) && !pmpro_bp_user_can_join_group( $group->id ) ) {
		global $pmpro_pages;
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
		$button_args['link_class'] = str_replace( 'join-group', '', $button_args['link_class'] );
		$button_args['button_element'] = 'a';
	}

	return $button_args;
}
add_filter( 'bp_get_group_join_button', 'pmpro_bp_disable_group_buttons', 10, 2);

/**
 * With the Nouveau theme, some attributes get overwritten,
 * so we need to filter them again later.
 */
function pmpro_bp_disable_nouveau_group_buttons( $buttons, $group ) {
	foreach( $buttons as &$button ) {
		$button = pmpro_bp_disable_group_buttons( $button, $group );
	}
	
	return $buttons;
}
add_filter( 'bp_nouveau_get_groups_buttons', 'pmpro_bp_disable_nouveau_group_buttons', 10, 2 );

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
	if( function_exists( 'bp_is_current_component' ) && bp_is_current_component('messages') && !pmpro_bp_user_can( 'private_messaging' ) ) {
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
		$args = false;
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
		$args = false;
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
		$args = false;
	}

	return $args;
}
add_filter( 'bp_get_add_friend_button', 'pmpro_bp_bp_get_add_friend_button' );

/**
 * Redirect away from any BuddyPress page if set to.
 */
function pmpro_bp_lockdown_all_bp() {
	global $current_user, $pmpro_pages;

	// Make sure PMPro is active.
	if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		return;
	}
	
	// Make sure BuddyPress is active.
	if( ! function_exists( 'is_buddypress') || !is_buddypress() ) {
		return;
	}
	
	// Don't lockdown the BuddyPress Register or Activate pages. The pmpro_bp_buddypress_or_pmpro_registration() will redirect if needed.
	if ( in_array( bp_current_component(), array( 'register', 'activate' ) ) ) {
		return;
	}
	
	// Make sure we don't lock down the PMPro levels page. The pmpro_bp_buddypress_or_pmpro_registration() will redirect if needed.
	if ( isset( $pmpro_pages['levels'] ) && is_page( $pmpro_pages['levels'] ) ) {
		return;
	}
	
	// Don't redirect awawy from BuddyPress profile pages.
	if ( bp_is_my_profile() ) {
		return;
	}

	// Check PMPro BuddyPress options.
	$pmpro_bp_options = pmpro_bp_get_user_options();	
	if( $pmpro_bp_options['pmpro_bp_restrictions'] == -1 ) {
		pmpro_bp_redirect_to_access_required_page();
	}

}
add_action( 'template_redirect', 'pmpro_bp_lockdown_all_bp', 50 );

/**
 * Redirect BuddyPress registration to PMPro
 * or redirect PMPro to BuddyPress depending on the
 * "Registration Page" setting.
 */
function pmpro_bp_buddypress_or_pmpro_registration() {
	global $pmpro_pages;
	
	// Make sure BuddyPress and PMPro are active.
	if( ! function_exists( 'bp_is_register_page' ) || ! function_exists( 'pmpro_url' ) ) {
		return;
	}

	// What is our Registration Page setting?
	$page_setting = get_option( 'pmpro_bp_registration_page', 'pmpro' );
	
	// Are we on the BuddyPress register/activate page?
	$on_bp_register = bp_is_register_page();
	
	// Are we on the PMPro levels page?
	$on_pmpro_levels = ( ! empty( $pmpro_pages['levels'] ) && is_page( $pmpro_pages['levels'] ) );
	
	// Avoid the loop when the BP Register Page is set to the PMPro Levels page.
	if ( $on_bp_register && $on_pmpro_levels ) {
		return;
	}
	
	// Do we need to redirect to PMPro Levels?
	if ( $page_setting === 'pmpro' && $on_bp_register ) {
		// Use PMPro Levels page.
		$url = pmpro_url( 'levels' );
	}

	// Do we need to redirect to BuddyPress registration?
	if ( $page_setting === 'buddypress' && $on_pmpro_levels ) {
		// Use the BuddyPress Register page.
		$bp_pages = get_option( 'bp-pages' );
		if ( ! empty( $bp_pages['register'] ) && ! is_user_logged_in() ) {
			$url = get_permalink( $bp_pages['register'] );
		} else {
			$url = '';
		}
	}

	// Redirect only if the URL was set.
	if ( ! empty( $url ) ) {
		wp_redirect( $url );
		exit;
	}
}
add_action( 'template_redirect', 'pmpro_bp_buddypress_or_pmpro_registration', 70 );

/**
 * Show level on BuddyPress profile
 * unless setting says not to
 */
function pmpro_bp_show_level_on_bp_profile() {
	if ( ! function_exists('pmpro_getMembershipLevelsForUser') ) {
		return;
	}
	
	$levels = pmpro_getMembershipLevelsForUser( bp_displayed_user_id() );
	$show_level = get_option('pmpro_bp_show_level_on_bp_profile');
	
	if( $show_level == 'yes' && ! empty( $levels ) ) {
		$level_names_string = implode( ', ', wp_list_pluck( $levels, 'name' ) );
		?>
		<div class="pmpro_bp_show_level_on_bp_profile">
			<strong><?php echo esc_html( _n( 'Membership Level', 'Membership Levels', count( $levels ) , 'pmpro-buddypress' ) );?>: <?php echo esc_html( $level_names_string ); ?> </strong>
		</div>
		<?php
	}
}
add_filter( 'bp_before_member_header_meta', 'pmpro_bp_show_level_on_bp_profile' );

/**
 * Restricted message shortcode.
 */
function pmpro_bp_restricted_message() {
	return __('This content is restricted.', 'pmpro-buddypress' ) . ' ';
}
add_shortcode( 'pmpro_buddypress_restricted', 'pmpro_bp_restricted_message' );

/**
 * Restrict viewing of the noifications page or individual
 * notification pages if the user doesn't have access.
 */
function pmpro_bp_restrict_notification_viewing() {
	
	global $bp;

	//If BuddyPress is not active, don't worry
	if( empty( $bp ) ) {
		return;
	}

	/**
	 * If you're restricting group access and want to view the notifications, redirect away
	 */
	if ( !pmpro_bp_user_can( 'groups_page_viewing' ) && $bp->current_component == 'notifications' ) {	
		pmpro_bp_redirect_to_access_required_page();
	}

}
add_action( 'template_redirect', 'pmpro_bp_restrict_notification_viewing' );