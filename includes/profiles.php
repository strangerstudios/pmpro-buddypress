<?php
/*
	Code to sync profile fields to PMPro Register Helper or edit profiles in general.
*/

/**
 * See if the meta field is in the RH defined fields 
 * and has the BuddyPress property set. If so,
 * update the xprofile field.
 */
function pmpro_bp_update_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {	
	global $pmpro_user_fields;	
	if( empty( $pmpro_user_fields ) ) {
		return;
	}
	
	if ( ! function_exists( 'xprofile_get_field_id_from_name' ) ) {
		return;
	}

	foreach( $pmpro_user_fields as $field_location )
	{
		foreach( $field_location as $rh_field )
		{
			if( $rh_field->meta_key == $meta_key && !empty( $rh_field->buddypress ) )
			{
				//switch for type
				
				$x_field = xprofile_get_field_id_from_name( $rh_field->buddypress );

				if( !empty( $x_field ) )
					xprofile_set_field_data( $x_field, $user_id, $meta_value );
			}
				
		}
	}
}
add_action( 'update_user_meta', 'pmpro_bp_update_user_meta', 10, 4 );

/**
 * Use our filter above when user meta is added as well.
 */
function pmpro_bp_add_user_meta( $user_id, $meta_key, $meta_value ) {
	pmpro_bp_update_user_meta( NULL, $user_id, $meta_key, $meta_value );
}
add_action( 'add_user_meta', 'pmpro_bp_add_user_meta', 10, 3 );

/**
 * When xprofile is updated, see if we need to update user meta.
 */
function pmpro_bp_xprofile_updated_profile( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {
	global $pmpro_user_fields;
	if( empty( $pmpro_user_fields ) ) {
		return;
	}
	
	if ( empty( $errors ) ) {
		foreach( $posted_field_ids as $xprofile_field_id ) {
			$xprofile_field = new BP_XProfile_Field( $xprofile_field_id );
			
			foreach( $pmpro_user_fields as $field_location ) {
				foreach( $field_location as $rh_field ) {
					if( !empty( $rh_field->buddypress ) && $rh_field->buddypress == $xprofile_field->name ) {
						//switch for type?
						update_user_meta( $user_id, $rh_field->meta_key, $new_values[$xprofile_field_id]['value'] );
					}
				}
			}
		}
	}
}
add_action( 'xprofile_updated_profile', 'pmpro_bp_xprofile_updated_profile', 1, 5 );

/**
 * Filter edit profile link based on user's BuddyPress access.
 *
 * @since 1.2.4
 */
function pmpro_bp_init_edit_profile_url() {
	
	global $current_user;
	
	$user_options = pmpro_bp_get_user_options( $current_user->ID );
	
	if ( PMPROBP_LOCK_ALL_ACCESS == $user_options['pmpro_bp_restrictions'] ) {
		remove_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );
	}
}
add_action( 'init', 'pmpro_bp_init_edit_profile_url' );

/**
 * Remove "Extended Profile" tab based on user's BuddyPress access.
 * 
 * @since 1.2.4
 */
function pmpro_bp_profile_nav() {
	
	global $current_user, $bp;
	
	if ( empty( $bp ) ) {
		return;
	}
	
	$user_options = pmpro_bp_get_user_options( $current_user->ID );
	
	if ( PMPROBP_LOCK_ALL_ACCESS == $user_options['pmpro_bp_restrictions'] ) {
		remove_action( 'edit_user_profile', array( $bp->members->admin, 'profile_nav' ), 99, 1 );
		remove_action( 'show_user_profile', array( $bp->members->admin, 'profile_nav' ), 99, 1 );
	}
}
add_action( 'edit_user_profile', 'pmpro_bp_profile_nav' );
add_action( 'show_user_profile', 'pmpro_bp_profile_nav' );

/**
 * Show a message if non-member settings are restricted to all of BuddyPress.
 * 
 * @since 1.3
 * @return string $field_html The formatted HTML for radio buttons on the edit fields screen.
 */
function pmpro_bp_adjust_xprofile_view_radio_buttons( $field_html, $r, $args ) {
	// Get non-member users restriction settings.
	$non_user_options = pmpro_bp_get_level_options( 0 );
	if ( $non_user_options['pmpro_bp_restrictions'] === PMPROBP_GIVE_ALL_ACCESS ) {
			return $field_html;
	}

	// Show a message in the dashboard to admins when editing users extended profiles.
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		$message = esc_html__( "The user's profile is only visible to other active members and no fields are shown publicly. This is due to non-members having no access to BuddyPress components.", 'pmpro-buddypress' );
	} else {
		$message = esc_html__( 'Your profile is only visible to other active members.', 'pmpro-buddypress' );
	}

	$field_html = '<strong>' . esc_html__( 'Note:', 'pmpro-buddypress' ) . '</strong> ' . $message . '<br><br>' . $field_html;

	return $field_html;
}
add_filter( 'bp_profile_get_visibility_radio_buttons', 'pmpro_bp_adjust_xprofile_view_radio_buttons', 10, 3 );

/**
 * Create a custom menu item for profile page when Paid Memberships Pro is active. Callback runs the [pmpro_account] shortcode.
 *
 * @since 1.3
 */
function pmpro_bp_custom_user_nav_item() {
    $args = array(
            'name' => __( 'Membership', 'pmpro-buddypress' ),
            'slug' => 'membership_account',
            'default_subnav_slug' => 'membership',
            'position' => 50,
            'show_for_displayed_user' => false,
            'screen_function' => 'pmpro_bp_membership_profile_content',
            'item_css_id' => 'membership'
    );
 
    bp_core_new_nav_item( $args );
}
add_action( 'bp_setup_nav', 'pmpro_bp_custom_user_nav_item', 99 );

/**
 * Callback for the membership profile content and load the template.
 *
 * @since 1.3
 */
function pmpro_bp_membership_profile_content() {
    add_action( 'bp_template_content', 'pmpro_bp_membership_profile_screen' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * Callback to return the default shortcode for the account page on the BuddyPress profile page for current user only.
 *
 * @since 1.3
 * @return string [pmpro_account] Returns the default shortcode screen for Paid Memberships Pro.
 */
function pmpro_bp_membership_profile_screen() {
	/**
	 * Allow filtering the content added to the Membership tab of the BuddyPress profile page.
	 *
	 * @param string $content_escaped The content to add to the Membership tab of the BuddyPress profile page for current user only.
	 */
	$content_escaped = apply_filters( 'pmpro_buddypress_profile_account_shortcode', '[pmpro_account]' );

	// phpcs:ignore Content has been escaped within the pmpro_shortcode_account function
	echo $content_escaped;
}
