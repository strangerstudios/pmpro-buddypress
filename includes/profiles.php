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
	if ( empty( $pmprorh_registration_fields ) ) {
		return;
	}

	// foreach ( $pmprorh_registration_fields as $field_location ) {
	// foreach ( $field_location as $rh_field ) {
	// if ( $rh_field->meta_key == $meta_key && ! empty( $rh_field->buddypress ) ) {
	// // switch for type
	// $x_field = xprofile_get_field_id_from_name( $rh_field->buddypress );
	// if ( ! empty( $x_field ) ) {
	// xprofile_set_field_data( $x_field, $user_id, $meta_value );
	// }
	// }
	// }
	// }
	foreach ( $pmprorh_registration_fields as $field_location ) {
		foreach ( $field_location as $rh_field ) {
			if ( $rh_field->meta_key == $meta_key && ! empty( $rh_field->buddypress ) ) {
				$type = $rh_field->type;
				switch ( $type ) {
					case 'select':
						$select = get_xprofile_checkbox_or_select_field_data( $meta_key, $user_id );
						if ( ! empty( $select ) ) {
							xprofile_set_field_data( $select, $user_id, $meta_value );
						}
						break;
					case 'radio':
						$radio = get_xprofile_checkbox_or_select_field_data( $meta_key );
						if ( ! empty( $radio ) ) {
							xprofile_set_field_data( $radio, $user_id, $meta_value );
						}
						break;
					case 'select2':
						$select2 = xprofile_get_field_id_from_name( $rh_field->buddypress );
						if ( ! empty( $select2 ) ) {
							xprofile_set_field_data( maybe_unserialize( $select2 ), $user_id, $meta_value );
						}
						break;
					default:
						$x_field = xprofile_get_field_id_from_name( $rh_field->buddypress );
						if ( ! empty( $x_field ) ) {
							xprofile_set_field_data( $x_field, $user_id, $meta_value );
						}
				}
			}
		}
	}
}
add_action( 'update_user_meta', 'pmpro_bp_update_user_meta', 10, 4 );

/**
 * Use our filter above when user meta is added as well.
 */
function pmpro_bp_add_user_meta( $user_id, $meta_key, $meta_value ) {
	pmpro_bp_update_user_meta( null, $user_id, $meta_key, $meta_value );
}
add_action( 'add_user_meta', 'pmpro_bp_add_user_meta', 10, 3 );

/**
 * When xprofile is updated, see if we need to update user meta.
 */
function pmpro_bp_xprofile_updated_profile( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {
	global $pmprorh_registration_fields;
	if ( empty( $pmprorh_registration_fields ) ) {
		return;
	}

	if ( empty( $errors ) ) {
		foreach ( $posted_field_ids as $xprofile_field_id ) {
			$xprofile_field = new BP_XProfile_Field( $xprofile_field_id );
			echo $xprofile_field->name;

			foreach ( $pmprorh_registration_fields as $field_location ) {
				foreach ( $field_location as $rh_field ) {
					if ( ! empty( $rh_field->buddypress ) && $rh_field->buddypress == $xprofile_field->name ) {
						// switch for type?
						update_user_meta( $user_id, $rh_field->meta_key, $new_values[ $xprofile_field_id ]['value'] );
					}
				}
			}
		}
	}
}
add_action( 'xprofile_updated_profile', 'pmpro_bp_xprofile_updated_profile', 1, 5 );
function get_xprofile_checkbox_or_select_field_data( $field, $user_id ) {
	$data = bp_get_member_profile_data(
		array(
			'field' => $field,
			'user_id' => $user_id,
		)
	);
	return $data;
}

function get_xprofile_multiselect_field_data( $field, $user_id ) {
	$format = 'array'; // 'comma'
	$data = xprofile_get_field_data( $field, $user_id, 'array' );
	return $data;
}
