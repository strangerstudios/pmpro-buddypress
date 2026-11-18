<?php
/*
	Code to lock down BuddyPress features based on level settings.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	//Bail if $button_args isn't an array. Something went wrong.
	if( ! is_array( $button_args ) ) {
		return $button_args;
	}
 
  // Check if this is a button for a group that should be restricted.
	if( !pmpro_bp_user_can_join_group( $group->id ) 
		&& (! isset( $button_args['id'] ) 
		|| ( $button_args['id'] === 'join_group' 
		|| $button_args['id'] === 'request_membership' 
		|| $button_args['id'] === 'group_membership' ) ) ) {
      // Restrict the button.
		  global $pmpro_pages;
		  $button_args['link_href'] = get_permalink($pmpro_pages['pmprobp_restricted']);
		  $button_args['link_class'] = str_replace( 'join-group', '', $button_args['link_class'] ?? '' );
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
 * Don't let users join or request to join groups if their level doesn't allow it.
 *
 * BuddyPress checks these capabilities in the REST API, the join group link and membership requests.
 *
 * @since 1.6.2
 *
 * @param bool   $retval     Whether the user has the capability.
 * @param int    $user_id    The user ID.
 * @param string $capability The capability being checked.
 * @param int    $site_id    The site ID.
 * @param array  $args       Extra arguments, including the group ID.
 * @return bool
 */
function pmpro_bp_bp_user_can_join_group( $retval, $user_id, $capability, $site_id, $args ) {
	if ( $retval && in_array( $capability, array( 'groups_join_group', 'groups_request_membership' ), true ) ) {
		$group_id = empty( $args['group_id'] ) ? bp_get_current_group_id() : (int) $args['group_id'];
		if ( ! pmpro_bp_user_can_join_group( $group_id, $user_id ) ) {
			$retval = false;
		}
	}

	return $retval;
}
add_filter( 'bp_user_can', 'pmpro_bp_bp_user_can_join_group', 20, 5 );

/**
 * Block the AJAX request to join a group if the user's level doesn't allow it.
 *
 * The join group AJAX handlers don't enforce the groups_join_group capability.
 *
 * @since 1.6.2
 */
function pmpro_bp_ajax_join_group_check() {
	// Make sure Groups are active.
	if ( ! function_exists( 'groups_is_user_member' ) ) {
		return;
	}

	// Nouveau sends item_id. Legacy sends gid, and uses the same action to leave a group.
	$group_id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : ( isset( $_POST['gid'] ) ? intval( $_POST['gid'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- BuddyPress checks the nonce.
	if ( empty( $group_id ) || groups_is_user_member( get_current_user_id(), $group_id ) || groups_check_user_has_invite( get_current_user_id(), $group_id ) ) {
		return;
	}

	if ( ! pmpro_bp_user_can_join_group( $group_id ) ) {
		wp_send_json_error( array( 'feedback' => __( 'You do not have permission to join this group.', 'pmpro-buddypress' ), 'type' => 'error' ), 403 );
	}
}
add_action( 'wp_ajax_groups_join_group', 'pmpro_bp_ajax_join_group_check', 1 );
add_action( 'wp_ajax_joinleave_group', 'pmpro_bp_ajax_join_group_check', 1 );

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
 * Don't send private messages from users who don't have access to private messaging.
 *
 * Messages can be sent through AJAX, the REST API or a form POST, so check when the message is saved.
 * BuddyPress won't send a message with no recipients.
 *
 * @since 1.6.2
 *
 * @param BP_Messages_Message $message The message being sent.
 */
function pmpro_bp_messages_message_before_save( $message ) {
	if ( ! pmpro_bp_user_can( 'private_messaging', $message->sender_id ) ) {
		$message->recipients = array();
	}
}
add_action( 'messages_message_before_save', 'pmpro_bp_messages_message_before_save' );

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
	if ( ! empty( $pmpro_pages['levels'] ) && is_page( $pmpro_pages['levels'] ) ) {
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
 * Check whether the current user is locked out of all of BuddyPress.
 *
 * @since 1.6.2
 *
 * @return bool True if the current user is locked out of all of BuddyPress.
 */
function pmpro_bp_is_locked_out() {
	// Make sure PMPro is active.
	if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		return false;
	}

	// Make sure administrators can do everything.
	if ( current_user_can( 'manage_options' ) ) {
		return false;
	}

	$pmpro_bp_options = pmpro_bp_get_user_options();
	return $pmpro_bp_options['pmpro_bp_restrictions'] == PMPROBP_LOCK_ALL_ACCESS;
}

/**
 * Block the BuddyPress REST API if the user is locked out of all of BuddyPress.
 *
 * The REST API doesn't load BuddyPress pages, so pmpro_bp_lockdown_all_bp() doesn't run for it.
 * The activity oEmbed route is blocked too, since it returns the activity content.
 *
 * @since 1.6.2
 *
 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send to the client.
 * @param array                                            $handler  Route handler used for the request.
 * @param WP_REST_Request                                  $request  Request used to generate the response.
 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
 */
function pmpro_bp_lockdown_rest_api( $response, $handler, $request ) {
	if ( is_wp_error( $response ) || ! function_exists( 'bp_rest_namespace' ) ) {
		return $response;
	}

	// WordPress matches REST routes case-insensitively, so compare them that way too.
	$route     = $request->get_route();
	$namespace = '/' . bp_rest_namespace() . '/';
	if ( 0 !== stripos( $route, $namespace ) && 0 !== strcasecmp( $route, '/oembed/1.0/embed/activity' ) ) {
		return $response;
	}

	// Like the Register and Activate pages, don't lock down sign ups. BuddyBoss also needs its settings route before log in.
	$open_routes = ( 'buddyboss' === bp_rest_namespace() ) ? 'signup|settings' : 'signup';
	if ( preg_match( '#^' . preg_quote( $namespace, '#' ) . '[^/]+/(' . $open_routes . ')(/|$)#i', $route ) ) {
		return $response;
	}

	if ( pmpro_bp_is_locked_out() ) {
		return new WP_Error( 'pmpro_bp_locked', __( 'This content is restricted.', 'pmpro-buddypress' ), array( 'status' => rest_authorization_required_code() ) );
	}

	return $response;
}
add_filter( 'rest_request_before_callbacks', 'pmpro_bp_lockdown_rest_api', 10, 3 );

/**
 * Disable the activity RSS feeds if the user is locked out of all of BuddyPress.
 *
 * BuddyPress sends its feeds before pmpro_bp_lockdown_all_bp() runs.
 *
 * @since 1.6.2
 *
 * @param bool $enabled Whether the feed is enabled.
 * @return bool
 */
function pmpro_bp_lockdown_activity_feeds( $enabled ) {
	if ( $enabled && pmpro_bp_is_locked_out() ) {
		$enabled = false;
	}

	return $enabled;
}
add_filter( 'bp_activity_enable_feeds', 'pmpro_bp_lockdown_activity_feeds' );

/**
 * Block BuddyPress AJAX requests if the user is locked out of all of BuddyPress.
 *
 * Users can still use their own profile, like in pmpro_bp_lockdown_all_bp(). The profile
 * comes from the page the request says it was sent from, so the exception doesn't apply
 * to actions that aren't limited to the displayed user.
 * Runs after BuddyPress sets up the page that the AJAX request came from.
 *
 * @since 1.6.2
 */
function pmpro_bp_lockdown_ajax() {
	if ( ! wp_doing_ajax() || empty( $_REQUEST['action'] ) || ! function_exists( 'bp_ajax_action_is_registered' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading the AJAX action name.
		return;
	}

	$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading the AJAX action name.

	// Member suggestions (@mentions) aren't registered with bp_ajax_register_action().
	if ( 'heartbeat' === $action || ( ! bp_ajax_action_is_registered( $action ) && 'bp_get_suggestions' !== $action ) ) {
		return;
	}

	if ( bp_is_my_profile() && ! in_array( $action, array( 'members_filter', 'groups_filter', 'blogs_filter', 'get_single_activity_content', 'activity_widget_filter', 'bp_get_suggestions' ), true ) ) {
		return;
	}

	if ( pmpro_bp_is_locked_out() ) {
		wp_send_json_error( array( 'feedback' => __( 'This content is restricted.', 'pmpro-buddypress' ) ), 403 );
	}
}
add_action( 'admin_init', 'pmpro_bp_lockdown_ajax', 20 );

/**
 * Remove new activity from the heartbeat response if the user is locked out of all of BuddyPress.
 *
 * BuddyPress adds new activity to every heartbeat, including logged-out ones.
 *
 * @since 1.6.2
 *
 * @param array $response The heartbeat response.
 * @return array
 */
function pmpro_bp_lockdown_heartbeat( $response ) {
	if ( isset( $response['bp_activity_newest_activities'] ) && pmpro_bp_is_locked_out() ) {
		unset( $response['bp_activity_newest_activities'] );
	}

	return $response;
}
add_filter( 'heartbeat_received', 'pmpro_bp_lockdown_heartbeat', 11 );
add_filter( 'heartbeat_nopriv_received', 'pmpro_bp_lockdown_heartbeat', 11 );

/**
 * Get the activity types that BuddyPress records for posts and their comments.
 *
 * @since 1.6.3
 *
 * @return array Activity types, with 'post' or 'comment' for each.
 */
function pmpro_bp_get_post_activity_types() {
	static $types = null;

	if ( null !== $types ) {
		return $types;
	}

	// Site Tracking's types are included even when it's off, since activity it recorded earlier is still shown.
	$types = array(
		'new_blog_post'    => 'post',
		'new_blog_comment' => 'comment',
	);

	if ( function_exists( 'bp_activity_get_post_types_tracking_args' ) ) {
		$tracking_args = bp_activity_get_post_types_tracking_args();

		// Comment tracking entries have an action ID too, so find the comment types first.
		foreach ( $tracking_args as $args ) {
			if ( ! empty( $args->comment_action_id ) ) {
				$types[ $args->comment_action_id ] = 'comment';
			}
		}
		foreach ( $tracking_args as $args ) {
			if ( ! empty( $args->action_id ) && ! isset( $types[ $args->action_id ] ) ) {
				$types[ $args->action_id ] = 'post';
			}
		}
	}

	return $types;
}

/**
 * Get the post that an activity item is about.
 *
 * BuddyPress records new posts and their comments in the activity stream when Site Tracking (or
 * activity tracking for another post type) is on. Comments made in the activity stream belong to
 * the post's activity item.
 *
 * @since 1.6.3
 *
 * @param object $activity The activity item.
 * @return array|false The post ID and whether the activity item shows the post ('post') or a comment ('comment'), or false if it isn't about a post on this site.
 */
function pmpro_bp_get_activity_post( $activity ) {
	static $root_posts = array();

	if ( empty( $activity->type ) ) {
		return false;
	}

	// Comments made in the activity stream are about the post of the activity item they belong to.
	if ( 'activity_comment' === $activity->type ) {
		$root_id = (int) $activity->item_id;
		if ( ! array_key_exists( $root_id, $root_posts ) ) {
			$root                   = new BP_Activity_Activity( $root_id );
			$root_posts[ $root_id ] = empty( $root->id ) ? false : pmpro_bp_get_activity_post( $root );
		}

		return empty( $root_posts[ $root_id ] ) ? false : array( 'post_id' => $root_posts[ $root_id ]['post_id'], 'type' => 'comment' );
	}

	$types = pmpro_bp_get_post_activity_types();
	if ( empty( $types[ $activity->type ] ) ) {
		return false;
	}

	// The item ID is the site the post is on. PMPro can only check posts on this site.
	if ( is_multisite() && (int) $activity->item_id !== get_current_blog_id() ) {
		return false;
	}

	if ( 'comment' === $types[ $activity->type ] ) {
		$comment = get_comment( $activity->secondary_item_id );
		$post_id = empty( $comment ) ? 0 : (int) $comment->comment_post_ID;
	} else {
		$post_id = (int) $activity->secondary_item_id;
	}

	return empty( $post_id ) ? false : array( 'post_id' => $post_id, 'type' => $types[ $activity->type ] );
}

/**
 * Get what the current user should see instead of the content of an activity item about a post.
 *
 * @since 1.6.3
 *
 * @param object $activity The activity item.
 * @return string|null The content to show instead, or null if the current user can see the activity item's content.
 */
function pmpro_bp_get_hidden_activity_content( $activity ) {
	static $post_access = array();

	if ( ! is_object( $activity ) || ! function_exists( 'pmpro_has_membership_access' ) || bp_current_user_can( 'bp_moderate' ) ) {
		return null;
	}

	$activity_post = pmpro_bp_get_activity_post( $activity );
	if ( empty( $activity_post ) ) {
		return null;
	}

	$key = get_current_user_id() . ':' . $activity_post['post_id'];
	if ( ! isset( $post_access[ $key ] ) ) {
		$post_access[ $key ] = pmpro_has_membership_access( $activity_post['post_id'] ) || current_user_can( 'edit_post', $activity_post['post_id'] );
	}

	if ( $post_access[ $key ] ) {
		return null;
	}

	// Comments are never shown. The summary BuddyPress saves for a post is the start of its content.
	if ( 'comment' === $activity_post['type'] || ! get_option( 'pmpro_showexcerpts' ) ) {
		return '';
	}

	// With "Show excerpts to non-members" on, show the post's excerpt like PMPro does.
	if ( has_excerpt( $activity_post['post_id'] ) ) {
		return wp_kses_post( get_post_field( 'post_excerpt', $activity_post['post_id'] ) );
	}

	// Without a manual excerpt, PMPro's excerpt is the start of the content, so keep the text of the summary. BuddyPress may add an image, embed URL, audio or video to it, so remove those.
	$summary = preg_split( '/\R\s*\R/', strip_shortcodes( $activity->content ) );
	return wp_kses( trim( $summary[0] ), array( 'a' => array( 'href' => true ), 'strong' => array(), 'span' => array() ) );
}

/**
 * Remove the content of an activity item, and its comments, when the current user can't see it.
 *
 * BuddyPress caches the activity objects it returns, so changed items are copies.
 *
 * @since 1.6.3
 *
 * @param object $activity The activity item.
 * @return object The activity item, or a copy with the hidden content removed.
 */
function pmpro_bp_maybe_hide_activity_content( $activity ) {
	if ( ! is_object( $activity ) ) {
		return $activity;
	}

	$children = null;
	if ( ! empty( $activity->children ) && is_array( $activity->children ) ) {
		foreach ( $activity->children as $key => $child ) {
			$new_child = pmpro_bp_maybe_hide_activity_content( $child );
			if ( $new_child !== $child ) {
				if ( null === $children ) {
					$children = $activity->children;
				}
				$children[ $key ] = $new_child;
			}
		}
	}

	$hidden_content = pmpro_bp_get_hidden_activity_content( $activity );
	if ( null === $hidden_content && null === $children ) {
		return $activity;
	}

	$activity = clone $activity;
	if ( null !== $hidden_content ) {
		$activity->content = $hidden_content;
	}
	if ( null !== $children ) {
		$activity->children = $children;
	}

	return $activity;
}

/**
 * Don't show the content of restricted posts and their comments in the activity stream.
 *
 * BuddyPress saves a summary of the post's content when it records the activity item and shows it
 * to everyone who can see the activity stream, including through the REST API and RSS feeds. This
 * removes the content when the activity is loaded, so it also covers activity items recorded
 * before the post was restricted. The activity item's action, with the post title and link, is still shown.
 *
 * @since 1.6.3
 *
 * @param array $activity The activity items and their total.
 * @return array
 */
function pmpro_bp_hide_restricted_post_activity_content( $activity ) {
	if ( empty( $activity['activities'] ) || ! is_array( $activity['activities'] ) ) {
		return $activity;
	}

	foreach ( $activity['activities'] as $key => $item ) {
		$activity['activities'][ $key ] = pmpro_bp_maybe_hide_activity_content( $item );
	}

	return $activity;
}
add_filter( 'bp_activity_get', 'pmpro_bp_hide_restricted_post_activity_content' );
add_filter( 'bp_activity_get_specific', 'pmpro_bp_hide_restricted_post_activity_content' );

/**
 * Don't show the content of restricted posts and their comments when activity is displayed.
 *
 * BuddyBoss rebuilds the content of post and comment activity items from the post or comment
 * when they are displayed, so the content removed when the activity was loaded comes back.
 * With "Show excerpts to non-members" on, BuddyBoss rebuilds post items from the excerpt, so those are left alone.
 *
 * @since 1.6.3
 *
 * @param string      $content  The activity item's content.
 * @param object|null $activity The activity item.
 * @return string
 */
function pmpro_bp_hide_restricted_activity_content_display( $content, $activity = null ) {
	// Comment and @mention emails pass other content with the activity item, and check access as the sender instead of the recipient.
	if ( doing_action( 'bp_activity_comment_posted' ) || doing_action( 'bp_activity_after_save' ) ) {
		return $content;
	}

	return '' === pmpro_bp_get_hidden_activity_content( $activity ) ? '' : $content;
}
add_filter( 'bp_get_activity_content_body', 'pmpro_bp_hide_restricted_activity_content_display', 10000, 2 );
add_filter( 'bp_get_activity_content', 'pmpro_bp_hide_restricted_activity_content_display', 10000, 2 );

/**
 * Don't show the content of restricted posts and their comments in activity embeds.
 *
 * The oEmbed response loads the activity item directly and includes its content as fallback HTML.
 *
 * @since 1.6.3
 *
 * @param string $html     The fallback HTML.
 * @param object $activity The activity item.
 * @return string
 */
function pmpro_bp_hide_restricted_activity_embed( $html, $activity ) {
	return null === pmpro_bp_get_hidden_activity_content( $activity ) ? $html : '';
}
add_filter( 'bp_activity_embed_fallback_html', 'pmpro_bp_hide_restricted_activity_embed', 10, 2 );

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
		wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Destination is pmpro_url() or a page permalink, both filterable and may legitimately point offsite (e.g. Network Subsite).
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