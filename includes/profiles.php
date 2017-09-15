<?php
/*
	Code to sync profile fields to PMPro Register Helper or edit profiles in general.
*/

add_action( 'update_user_meta', 'pmpro_bp_update_user_meta', 10, 4 );

function pmpro_bp_add_user_meta($user_id, $meta_key, $meta_value)
{
	pmpro_bp_update_user_meta(NULL, $user_id, $meta_key, $meta_value);
}
add_action('add_user_meta', 'pmpro_bp_add_user_meta', 10, 3);

function pmpro_bp_update_user_meta($meta_id, $user_id, $meta_key, $meta_value)
{
	//see if the meta field is in the RH defined fields, and has the BuddyPress property set
	global $pmprorh_registration_fields;
	
	if ($pmprorh_registration_field)
	{
		foreach($pmprorh_registration_fields as $field_location)
		{
			foreach($field_location as $rh_field)
			{
				if($rh_field->meta_key == $meta_key && !empty($rh_field->buddypress))
				{
					//switch for type
				
					$x_field = xprofile_get_field_id_from_name($rh_field->buddypress);

					if(!empty($x_field))
						xprofile_set_field_data($x_field, $user_id, $meta_value);
				}
				
			}
		}
	}
}

function pmpro_bp_xprofile_updated_profile($user_id, $posted_field_ids, $errors, $old_values, $new_values) 
{
	global $pmprorh_registration_fields;
	
	if ( empty( $errors ) ) 
	{
		foreach($posted_field_ids as $xprofile_field_id)
		{
			$xprofile_field = new BP_XProfile_Field($xprofile_field_id);
			echo $xprofile_field->name;
			
			foreach($pmprorh_registration_fields as $field_location)
			{
				foreach($field_location as $rh_field)
				{
					if(!empty($rh_field->buddypress) && $rh_field->buddypress == $xprofile_field->name)
					{
						//switch for type?
						update_user_meta($user_id, $rh_field->meta_key, $new_values[$xprofile_field_id]['value']);
					}
				}
			}
		}
	}
}
add_action('xprofile_updated_profile', 'pmpro_bp_xprofile_updated_profile', 1, 5);