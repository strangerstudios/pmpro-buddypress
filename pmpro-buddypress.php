<?php
/*
 Plugin Name: Paid Memberships Pro - BuddyPress Add On
 Plugin URI: https://wordpress.org/plugins/pmpro-buddypress
 Description: Allow individual BuddyPress functionality to be controlled by PMPro
 Version: 0.1
 Author: strangerstudios, ghmaster
 Author URI: http://www.strangerstudios.com
 */

/*
	includes
*/
define('PMPROBP_DIR', dirname(__file__));
require_once(PMPROBP_DIR . '/includes/pmpro-buddypress-settings.php');
require_once(PMPROBP_DIR . '/includes/membership-level-settings.php');
require_once(PMPROBP_DIR . '/includes/restrictions.php');
require_once(PMPROBP_DIR . '/includes/groups.php');
require_once(PMPROBP_DIR . '/includes/directory.php');
require_once(PMPROBP_DIR . '/includes/profiles.php');
