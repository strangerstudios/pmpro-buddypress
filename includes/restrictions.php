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

//add_action( 'template_redirect', 'pmpro_bp_restrict_group_viewing' );

function pmpro_bp_restricted_message()
{
	//TODO: Either pull the content in from the Advanced Settings or
	//make it dynamic ie: "Viewing individual groups is not permitted for your level
	return "This content is restricted. ";
}

add_shortcode('pmpro_buddypress_restricted', 'pmpro_bp_restricted_message');


add_filter( 'bp_get_group_join_button', 'pmpro_bp_bp_get_groups_join_button');
function pmpro_bp_bp_get_groups_join_button($button_args)
{
	global $pmpro_pages;
	
	if(!pmpro_bp_user_can_join_groups())
	{
		$button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
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

/*
 * RESTRICT PUBLIC MESSAGING
function pmpro_bp_bp_get_send_public_message_button($args)
{
	global $pmpro_pages;
	
	$args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
	return $args;
}

add_filter('bp_get_send_public_message_button', 'pmpro_bp_bp_get_send_public_message_button');
*/

function pmpro_bp_bp_get_add_friend_button($button)
{
	global $current_user, $pmpro_pages;
	
	$user_level = pmpro_getMembershipLevelForUser($current_user->ID);
	
	$pmpro_bp_send_friend_request = get_option('pmpro_bp_send_friend_request_'.$user_level->ID);
	
	if(empty($pmpro_bp_send_friend_request))
	{
		$button['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
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
	
	$level = pmpro_getMembershipLevelForUser();
	
	if(!empty($level))
	{
		$pmpro_bp_restrictions = get_option('pmpro_bp_restrictions_'.$level->ID);
	}
	else
	{
		$pmpro_bp_restrictions = null;	
	}
	
	if(($pmpro_bp_restrictions == 1 && is_buddypress()) || ($pmpro_bp_restrictions == null 
		   && $post->ID != $pmpro_pages['pmprobp_restricted'] && is_buddypress()))
	{
		wp_redirect(get_permalink($pmpro_pages['pmprobp_restricted']));
		exit();
	}


}

//add_action( 'template_redirect', 'pmpro_bp_lockdown_all_bp', 50 );


function my_function_name()
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
add_filter( 'bp_profile_header_meta', 'my_function_name' );