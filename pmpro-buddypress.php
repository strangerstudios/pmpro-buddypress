<?php
/*
 Plugin Name: Paid Memberships Pro - BuddyPress Add On
 Plugin URI: https://www.paidmembershipspro.com/add-ons/buddypress-integration
 Description: Manage access to your BuddyPress Community using Paid Memberships Pro.
 Version: 1.2.6
 Author: Paid Memberships Pro
 Author URI: https://www.paidmembershipspro.com
 Text Domain: pmpro-buddypress
 */

/*
    includes
*/
define( 'PMPROBP_DIR', dirname( __FILE__ ) );
define( 'PMPROBP_BASENAME', plugin_basename( __FILE__ ) );
define( 'PMPROBP_LOCK_ALL_ACCESS', -1);
define( 'PMPROBP_USE_NON_MEMBER_SETTINGS', 0);
define( 'PMPROBP_GIVE_ALL_ACCESS', 1);
define( 'PMPROBP_SPECIFIC_FEATURES', 2);


require_once( PMPROBP_DIR . '/includes/common.php' );

require_once( PMPROBP_DIR . '/includes/admin.php' );
require_once( PMPROBP_DIR . '/includes/pmpro-buddypress-settings.php' );
require_once( PMPROBP_DIR . '/includes/membership-level-settings.php' );

require_once( PMPROBP_DIR . '/includes/approvals.php' );
require_once( PMPROBP_DIR . '/includes/directory.php' );
require_once( PMPROBP_DIR . '/includes/groups.php' );
require_once( PMPROBP_DIR . '/includes/member-types.php' );
require_once( PMPROBP_DIR . '/includes/profiles.php' );
require_once( PMPROBP_DIR . '/includes/registration.php' );
require_once( PMPROBP_DIR . '/includes/restrictions.php' );
