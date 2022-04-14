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
	global $pmprorh_registration_fields;	
	if( empty( $pmprorh_registration_fields ) ) {
		return;
	}
	
	if ( ! function_exists( 'xprofile_get_field_id_from_name' ) ) {
		return;
	}

	foreach( $pmprorh_registration_fields as $field_location )
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
	global $pmprorh_registration_fields;
	if( empty( $pmprorh_registration_fields ) ) {
		return;
	}
	
	if ( empty( $errors ) ) {
		foreach( $posted_field_ids as $xprofile_field_id ) {
			$xprofile_field = new BP_XProfile_Field( $xprofile_field_id );
			
			foreach( $pmprorh_registration_fields as $field_location ) {
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
