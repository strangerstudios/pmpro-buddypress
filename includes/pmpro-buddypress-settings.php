<?php
/*
	Code to create a Memberships -> BuddyPress page with settings.
*/

function pmpro_bp_extra_page_settings($pages)
{
	$pages['pmprobp_restricted'] = array('title'=>'Access Restricted', 'content'=>'[pmpro_buddypress_restricted]', 'hint'=>'Include the shortcode [pmpro_buddypress_restricted].');
	return $pages;
}
add_action('pmpro_extra_page_settings', 'pmpro_bp_extra_page_settings', 10, 1);