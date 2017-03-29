<?php
/*
	Code to lock down BuddyPress features based on level settings.
*/

/**
 * Check if a user can create groups.
 * @param int $user_id ID of the user to check. Defaults to current user.
 * @TODO: Consider adding a Settings page to account for behavior for Level 0. i.e. those who are logged in but don't have a membership level. Should they be allowed to create groups?
 */
function pmpro_bp_user_can_create_groups( $user_id = NULL )
{
	//default to current user
	global $current_user;			
	if(empty($user_id))
		$user_id = $current_user->ID;

	//must have a user
	if(empty($user_id))
		return false;
	
	//get the user's current level
	$level = pmpro_getMembershipLevelForUser($user_id);
	
	//disable group creation for those with no membership level.
	if(empty($level))
		return false;

	//see if that level is allowed to create groups
	$can_create = get_option('pmpro_bp_group_creation_'.$level->ID, false);	
	
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
	
	//see if that level is allowed to view individual groups
	$can_view = get_option('pmpro_bp_group_single_viewing_'.$level->ID);
		
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

	//see if that level is allowed to create groups
	$can_view = get_option('pmpro_bp_groups_page_viewing_'.$level->ID);
		
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

	//see if that level is allowed to create groups
	$can_join = get_option('pmpro_bp_groups_join_'.$level->ID);
		
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
	if(bp_is_current_component($bp->pages->groups->slug) && !bp_is_group() && !pmpro_bp_user_can_view_groups_page()) //&& can_view_groups_page
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

add_filter( 'bp_get_group_join_button', 'pmpro_bp_bp_get_groups_join_button');
function pmpro_bp_bp_get_groups_join_button($button_args)
{
	global $pmpro_pages;
	
	$level = pmpro_getMembershipLevelForUser();
	
	if(empty($level))
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);

	else
	{
		$pmpro_bp_group_can_request_invite = get_option('pmpro_bp_group_can_request_invite_'.$level->id);
	
		$group_id = bp_get_group_id();
		if($button_args['id'] == 'request_membership')
		{
			if(!in_array($group_id, $pmpro_bp_group_can_request_invite))
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

function pmpro_bp_restrict_private_messaging() {
	
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	if($user_level)
		$pmpro_bp_private_messaging = get_option('pmpro_bp_private_messaging_'.$user_level->ID);
	
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
		$pmpro_bp_private_messaging = get_option('pmpro_bp_private_messaging_'.$user_level->ID);
	
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
		$pmpro_bp_public_messaging = get_option('pmpro_bp_public_messaging_'.$user_level->ID);
	
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
		$pmpro_bp_send_friend_request = get_option('pmpro_bp_send_friend_request_'.$user_level->ID);
	
		if(empty($pmpro_bp_send_friend_request))
		{
			$button['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
		}
	}
	return $button;
}

add_filter('bp_get_add_friend_button', 'pmpro_bp_bp_get_add_friend_button');


//http://hookr.io/functions/bp_get_send_message_button/
//http://buddypress.wp-a2z.org/oik_api/bp_get_add_friend_button/
//http://buddypress.wp-a2z.org/oik_api/bp_get_send_public_message_button/

function pmpro_bp_lockdown_all_bp()
{
	global $pmpro_pages, $post;
	
	$bp_pages = get_option('bp-pages');
	if($post->ID == $bp_pages['register'] || $post->ID == 0)
		return;

	$level = pmpro_getMembershipLevelForUser();
	
	if(!empty($level))
	{
		$pmpro_bp_restrictions = get_option('pmpro_bp_restrictions_'.$level->ID);
	}
	else
	{
		$pmpro_bp_restrictions = null;	
	}
	
	//lock it all down for all non-members as well
	if(($pmpro_bp_restrictions == 1 && is_buddypress()) || ($pmpro_bp_restrictions == null 
		   && $post->ID != $pmpro_pages['pmprobp_restricted'] && is_buddypress()))
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
		wp_redirect(get_permalink($bp_pages['register']));
		exit;
	}
	elseif(!empty($pmpro_bp_register) && $pmpro_bp_register == 'pmpro' && bp_is_register_page())//($post->ID == $bp_pages['register']))
	{
		//use PMPro
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

function pmpro_bp_remove_request_membership_nave_link() {  

	if ( ! bp_is_group() ) {
		return;
	}

	$slug = bp_get_current_group_slug();
	
	//remove the "Request Membership from the Nav menu.
	bp_core_remove_subnav_item( $slug, 'request-membership' );
}
add_action( 'bp_actions', 'pmpro_bp_remove_request_membership_nave_link' );

function pmpro_bp_bp_friends_get_invite_list($friends, $user_id, $group_id)
{
	return $friends;
}

add_filter('bp_friends_get_invite_list', 'pmpro_bp_bp_friends_get_invite_list', 10, 3);

//function pmpro_bp_bp_after_group_send_invites_list()
//{
//	echo "HELLO!";
//}

//add_action('bp_after_group_send_invites_list', 'pmpro_bp_bp_after_group_send_invites_list');


//filter the button for Accept Invite if they don't have the membership level.
function pmpro_bp_bp_get_button($contents, $args, $button)
{
	var_dump($contents);
	echo "<br>";
	var_dump($args);
	echo "<br>";
	var_dump($button);
	
//	return $contents;
}

add_filter('pmpro_bp_bp_get_button', 'pmpro_bp_pmpro_bp_bp_get_button', 3, 10);