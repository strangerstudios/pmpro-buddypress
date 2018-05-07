<?php
/**
 * Plugin Name: Paid Memberships Pro - BuddyPress Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/buddypress-integration
 * Description: Manage access to your BuddyPress Community using Paid Memberships Pro.
 * Version: 1.1
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Text Domain: pmpro-buddypress
 */

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

add_action( 'plugins_loaded', 'check_for_buddypress_before_init' );
/**
 * Check for PMPro and BuddyPress first before engaging the Add On.
 *
 * @return [type] [description]
 */
function check_for_buddypress_before_init() {
	if ( ! defined( 'BP_REQUIRED_PHP_VERSION' ) ) {
		$notice = 'You need to activate BuddyPress for the PMPro BuddyPress Add On to run properly.';
		pmpro_bp_admin_notice__error( $notice );
		return;
	}
	if ( ! defined( 'PMPRO_BASE_FILE' ) ) {
		$notice = 'You need to activate Paid Memberships Pro for the PMPro BuddyPress Add On to run properly.';
		pmpro_bp_admin_notice__error( $notice );
		return;
	}
	define( 'PMPROBP_DIR', dirname( __FILE__ ) );
	define( 'PMPROBP_BASENAME', plugin_basename( __FILE__ ) );

	require_once( PMPROBP_DIR . '/includes/common.php' );

	require_once( PMPROBP_DIR . '/includes/admin.php' );
	require_once( PMPROBP_DIR . '/includes/pmpro-buddypress-settings.php' );
	require_once( PMPROBP_DIR . '/includes/membership-level-settings.php' );

	require_once( PMPROBP_DIR . '/includes/directory.php' );
	require_once( PMPROBP_DIR . '/includes/groups.php' );
	require_once( PMPROBP_DIR . '/includes/member-types.php' );
	require_once( PMPROBP_DIR . '/includes/profiles.php' );
	require_once( PMPROBP_DIR . '/includes/registration.php' );
	require_once( PMPROBP_DIR . '/includes/restrictions.php' );
}

function pmpro_bp_admin_notice__error( $notice ) {
	$class = 'notice notice-error';
	$message = __( $notice, 'pmpro-buddypress' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
