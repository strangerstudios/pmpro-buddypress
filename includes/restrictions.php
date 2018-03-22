<?php
/*
	Code to lock down BuddyPress features based on level settings.
	
	- When the plugin is activated, BuddyPress is still "unlocked" for all users.
	- On the main settings page, you can "Lock" BuddyPress features (all or specific) for non-member users.
	- On the level settings page, the level gives access to BuddyPress by default.
	- OR you can "Lock" BuddyPress features (all or specific) for users of that level.
*/

/**
 * Check if a user can create groups.
 * @param int $user_id ID of the user to check. Defaults to current user.
 */
function pmpro_bp_user_can_create_groups( $user_id = NULL ) {
	//default to current user
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	//must have a user
	if( empty( $user_id ) ) {
		return false;
	}
	
	//must have a level
	if( !function_exists( 'pmpro_hasMembershipLevel' ) || !pmpro_hasMembershipLevel( NULL, $user_id ) ) {
		return false;
	}
		
	$level = pmpro_getMembershipLevelForUser($user_id);			
	$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);	
	
	//are they restricting BuddyPress at all?
	if($pmpro_bp_options['pmpro_bp_restrictions'] == 0)
		return true;
	
	//see if this level is allowed to create groups	
	$can_create = $pmpro_bp_options['pmpro_bp_group_creation'];	
	
	return $can_create;
}

function pmpro_bp_user_can_view_single_group()
{
	global $current_user;
		
	//get the user's current level
	$level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	//disable single group viewing for those with no membership level.
	if(empty($level))
		return false;
	
	$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);
	
		//are they restricting BuddyPress at all?
	if($pmpro_bp_options['pmpro_bp_restrictions'] == 0)
		return true;
	
	//see if that level is allowed to view individual groups
	$can_view = $pmpro_bp_options['pmpro_bp_group_single_viewing'];
		
	if ( $can_view == 1 && !empty($level))
	{
		return true;
	}
	
	return false;
}

function pmpro_bp_user_can_view_groups_page()
{
	global $current_user;
		
	//get the user's current level
	$level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	//disable groups page viewing for those with no membership level.
	if(empty($level))
		return false;

	$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);
	
	//are they restricting BuddyPress at all?
	if($pmpro_bp_options['pmpro_bp_restrictions'] == 0)
		return true;
	
	//see if that level is allowed to create groups
	$can_view = $pmpro_bp_options['pmpro_bp_groups_page_viewing'];
		
	if ( $can_view == 1 && !empty($level))
	{
		return true;
	}
	
	return false;
}

function pmpro_bp_user_can_join_groups()
{
	global $current_user;
		
	//get the user's current level
	$level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	//disable groups joining for those with no membership level.
	if(empty($level))
		return false;

	$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);
	
	//are they restricting BuddyPress at all?
	if($pmpro_bp_options['pmpro_bp_restrictions'] == 0)
		return true;
	
	//see if that level is allowed to create groups
	$can_join = $pmpro_bp_options['pmpro_bp_groups_join'];
		
	if ( $can_join == 1 && !empty($level))
	{
		return true;
	}
	
	return false;
}

function pmpro_bp_bp_get_group_create_button( $button_args )
{ 
	global $pmpro_pages;
	
	if(!pmpro_bp_user_can_create_groups())
		$button_args['link_href'] =	get_permalink($pmpro_pages['pmprobp_restricted']);
	
    return $button_args;
}
         
add_filter( 'bp_get_group_create_button', 'pmpro_bp_bp_get_group_create_button', 10, 1 );

function pmpro_bp_restrict_group_viewing()
{
	global $bp, $pmpro_pages;
	
	//Group (Single) Viewing Restrictions - which levels can view individual groups?
	if ( bp_is_group() && !pmpro_bp_user_can_view_single_group())
	{
		wp_redirect(get_permalink($pmpro_pages['pmprobp_restricted']));
		exit;
	}

	//Group Viewing restrictions - which levels can view the groups page?
	if(!empty($bp->pages->groups) && bp_is_current_component($bp->pages->groups->slug) && !bp_is_group() && !pmpro_bp_user_can_view_groups_page()) //&& can_view_groups_page
	{
		wp_redirect(get_permalink($pmpro_pages['pmprobp_restricted']));
		exit();
	}
}

add_action( 'template_redirect', 'pmpro_bp_restrict_group_viewing' );

function pmpro_bp_restricted_message()
{
	return "This content is restricted. ";
}

add_shortcode('pmpro_buddypress_restricted', 'pmpro_bp_restricted_message');

function pmpro_bp_bp_get_groups_join_button($button_args)
{
	global $pmpro_pages;
	
	$level = pmpro_getMembershipLevelForUser();
	
	if(empty($level))
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);

	else
	{

		$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);

		$pmpro_bp_group_can_request_invite = $pmpro_bp_options['pmpro_bp_group_can_request_invite'];
		
		$group_id = bp_get_group_id();
		if($button_args['id'] == 'request_membership')
		{
			if(empty($pmpro_bp_group_can_request_invite) || !in_array($group_id, $pmpro_bp_group_can_request_invite))
			{
				$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
			}
		}
	
		if(!pmpro_bp_user_can_join_groups())
		{
			$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
		}
	}
	
	return $button_args;
}
add_filter( 'bp_get_group_join_button', 'pmpro_bp_bp_get_groups_join_button');

function pmpro_bp_restrict_private_messaging() {
	
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	if($user_level)
	{
		$pmpro_bp_options = pmpro_bp_getLevelOptions($user_level->ID);
		$pmpro_bp_private_messaging = $pmpro_bp_options['pmpro_bp_private_messaging'];
	}
	
	if(empty($pmpro_bp_private_messaging) && bp_is_current_component('messages'))
	{
		wp_redirect(get_permalink($pmpro_pages['pmprobp_restricted']));
		exit();
	}
}
add_action('wp','pmpro_bp_restrict_private_messaging');

function pmpro_bp_bp_get_send_message_button_args($args)
{
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	if(!empty($user_level))
	{
		$pmpro_bp_options = pmpro_bp_getLevelOptions($user_level->ID);
		$pmpro_bp_private_messaging = $pmpro_bp_options['pmpro_bp_private_messaging'];
		
	}
	
	if(empty($pmpro_bp_private_messaging))
	{
		$args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
	}

	return $args;
}

add_filter('bp_get_send_message_button_args', 'pmpro_bp_bp_get_send_message_button_args');

function pmpro_bp_bp_get_send_public_message_button($args)
{
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	if(!empty($user_level))
	{	
		$pmpro_bp_options = pmpro_bp_getLevelOptions($user_level->ID);
		$pmpro_bp_public_messaging = $pmpro_bp_options['pmpro_bp_public_messaging'];
	}
	
	if(empty($pmpro_bp_public_messaging))
	{
		$args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
	}
	
	return $args;
}

add_filter('bp_get_send_public_message_button', 'pmpro_bp_bp_get_send_public_message_button');

function pmpro_bp_bp_get_add_friend_button($button)
{
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	if(empty($user_level))
	{
		$button['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
	}
	
	else
	{
		$pmpro_bp_options = pmpro_bp_getLevelOptions($user_level->ID);
		
		$pmpro_bp_send_friend_request = $pmpro_bp_options['pmpro_bp_send_friend_request'];
	
		if(empty($pmpro_bp_send_friend_request))
		{
			$button['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
		}
	}
	return $button;
}

add_filter('bp_get_add_friend_button', 'pmpro_bp_bp_get_add_friend_button');

function pmpro_bp_lockdown_all_bp()
{
	global $pmpro_pages, $post;
	
	$bp_pages = get_option('bp-pages');

	$level = pmpro_getMembershipLevelForUser();
	
	if(!empty($level))
	{
		$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);
		$pmpro_bp_restrictions = $pmpro_bp_options['pmpro_bp_restrictions'];
	}
	else
	{
		$pmpro_bp_restrictions = null;	
	}
	
	if(($pmpro_bp_restrictions == 1 && is_buddypress()))
	{
		wp_redirect(get_permalink($pmpro_pages['pmprobp_restricted']));
		exit();
	}
}

add_action( 'template_redirect', 'pmpro_bp_lockdown_all_bp', 50 );

function pmpro_bp_buddypress_or_pmpro_registration()
{
	global $post, $pmpro_pages;
	
	$bp_pages = get_option('bp-pages');
	
	$pmpro_bp_register = get_option('pmpro_bp_registration_page');
		
	if(!empty($pmpro_bp_register) && $pmpro_bp_register == 'buddypress' && ($post->ID == $pmpro_pages['levels']))
	{
		//Use BuddyPress Register page
		wp_redirect(get_permalink($bp_pages['register']));
		exit;
	}
	elseif(!empty($pmpro_bp_register) && $pmpro_bp_register == 'pmpro' && bp_is_register_page())//($post->ID == $bp_pages['register']))
	{
		//use PMPro Levels page
		$url = pmpro_url("levels");
		wp_redirect($url);
		exit;
	}
}

add_action( 'template_redirect', 'pmpro_bp_buddypress_or_pmpro_registration', 70 );

function pmpro_bp_show_level_on_bp_profile()
{
	$level = pmpro_getMembershipLevelForUser(bp_displayed_user_id());
	
	$show_level = get_option('pmpro_bp_show_level_on_bp_profile');
	
	if($show_level == 'yes' && $level)
	{?>
		<div class="pmpro_bp_show_level_on_bp_profile">
			<strong>Membership Level: <?php echo $level->name; ?> </strong>
		</div><?php
	}
}
add_filter( 'bp_profile_header_meta', 'pmpro_bp_show_level_on_bp_profile' );

function pmpro_bp_remove_request_membership_nav_link()
{  
	if ( ! bp_is_group() ) {
		return;
	}
	
	$slug = bp_get_current_group_slug();
	$level = pmpro_getMembershipLevelForUser();
	$pmpro_bp_options = pmpro_bp_getLevelOptions($level->ID);

	$pmpro_bp_group_can_request_invite = $pmpro_bp_options['pmpro_bp_group_can_request_invite'];
	
	if(empty($pmpro_bp_group_can_request_invite) || !in_array(bp_get_current_group_id(), $pmpro_bp_group_can_request_invite))
	{
		//remove the "Request Membership from the Nav menu.
		bp_core_remove_subnav_item( $slug, 'request-membership' );
	}
}
add_action( 'bp_actions', 'pmpro_bp_remove_request_membership_nav_link' );