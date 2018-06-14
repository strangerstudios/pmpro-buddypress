<?php
/*
 Plugin Name: Paid Memberships Pro - BuddyPress Add On
 Plugin URI: https://www.paidmembershipspro.com/add-ons/buddypress-integration
 Description: Manage access to your BuddyPress Community using Paid Memberships Pro.
 Version: 1.0
 Author: Paid Memberships Pro
 Author URI: https://www.paidmembershipspro.com
 Text Domain: pmpro-buddypress
 */

 function pmpro_bp_init()
 {
      // Make sure PMPro and BuddyPress are activated.
     include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
     $plugins_loaded = is_plugin_active('paid-memberships-pro/paid-memberships-pro.php') && is_plugin_active( 'buddypress/bp-loader.php' );

     if(!empty($plugins_loaded))
     {
         /*
             includes
         */
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
}
add_action('init', 'pmpro_bp_init');
