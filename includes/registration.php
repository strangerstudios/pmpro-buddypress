<?php
//redirect the Register button from wp-login.php
function pmpro_bp_registration_pmpro_to_bp_redirect( $url ) {
	$bp_pages = get_option( 'bp-pages' );
	
	$pmpro_bp_register = get_option( 'pmpro_bp_registration_page' );
	if( !empty( $pmpro_bp_register ) && $pmpro_bp_register == 'buddypress' ) {
		$url = get_permalink( $bp_pages['register'] );
	}
	
	return $url;
}
add_filter( 'pmpro_register_redirect', 'pmpro_bp_registration_pmpro_to_bp_redirect' );