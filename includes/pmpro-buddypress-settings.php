<?php
/*
	Code to create a Memberships -> BuddyPress page with settings.
*/

function pmpro_bp_extra_page_settings( $pages ) {
	$pages['pmprobp_restricted'] = array( 'title'=>'Access Restricted', 'content'=>'[pmpro_buddypress_restricted]', 'hint'=>'Include the shortcode [pmpro_buddypress_restricted].' );
	return $pages;
}
add_action( 'pmpro_extra_page_settings', 'pmpro_bp_extra_page_settings', 10, 1 );

function pmpro_bp_add_admin_menu_page() {
	if ( ! defined( 'PMPRO_VERSION' ) ) {
		return;
	}
	if( version_compare( PMPRO_VERSION, '2.0' ) >= 0 ) {
		$parent_page = 'pmpro-dashboard';
	} else {
		$parent_page = 'pmpro-membershiplevels';
	}
	add_submenu_page( $parent_page, esc_html__('PMPro BuddyPress', 'pmpro'), esc_html__('PMPro BuddyPress', 'pmpro'), 'manage_options', 'pmpro-buddypress', 'pmpro_bp_buddpress_admin_page' );
}
add_action( 'admin_menu', 'pmpro_bp_add_admin_menu_page' );

function pmpro_bp_buddpress_admin_page() {
	//get/set settings
	if(!empty($_REQUEST['savesettings'])) {
		// Non-member user Restrictions
		$can_create_groups = intval( $_REQUEST['pmpro_bp_group_creation'] );
		$can_view_single_group = intval( $_REQUEST['pmpro_bp_group_single_viewing'] );
		$can_view_groups_page = intval( $_REQUEST['pmpro_bp_groups_page_viewing'] );
		$can_join_groups = intval( $_REQUEST['pmpro_bp_groups_join'] );
		$pmpro_bp_restrictions = intval( $_REQUEST['pmpro_bp_restrictions'] );
		$pmpro_bp_public_messaging = intval( $_REQUEST['pmpro_bp_public_messaging'] );
		$pmpro_bp_private_messaging = intval( $_REQUEST['pmpro_bp_private_messaging'] );
		$pmpro_bp_send_friend_request = intval( $_REQUEST['pmpro_bp_send_friend_request'] );
		$pmpro_bp_member_directory = intval( $_REQUEST['pmpro_bp_member_directory'] );		
			
		$pmpro_bp_options = array(
			'pmpro_bp_restrictions'				=> $pmpro_bp_restrictions,
			'pmpro_bp_group_creation'			=> $can_create_groups,
			'pmpro_bp_group_single_viewing'		=> $can_view_single_group,
			'pmpro_bp_groups_page_viewing'		=> $can_view_groups_page,
			'pmpro_bp_groups_join'				=> $can_join_groups,			
			'pmpro_bp_private_messaging'		=> $pmpro_bp_private_messaging,
			'pmpro_bp_public_messaging'			=> $pmpro_bp_public_messaging,
			'pmpro_bp_send_friend_request'		=> $pmpro_bp_send_friend_request,
			'pmpro_bp_member_directory'			=> $pmpro_bp_member_directory,
			'pmpro_bp_group_automatic_add'		=> array(),
			'pmpro_bp_group_can_request_invite'	=> array(),
			'pmpro_bp_member_types'				=> array());
		update_option('pmpro_bp_options_users', $pmpro_bp_options, 'no');

		// General Settings
		update_option( 'pmpro_bp_registration_page', sanitize_key( $_POST['pmpro_bp_register'] ) );
		update_option( 'pmpro_bp_show_level_on_bp_profile', sanitize_key( $_POST['pmpro_bp_level_profile'] ), 'no' ) ;
	}
	
    $pmpro_bp_register = get_option( 'pmpro_bp_registration_page' );
    $pmpro_bp_level_profile = get_option( 'pmpro_bp_show_level_on_bp_profile' );
    
	if( empty( $pmpro_bp_register ) ) {
		$pmpro_bp_register = 'pmpro'; //default to the PMPro Levels page 
	}
    
	if( empty( $pmpro_bp_level_profile ) ) {
		$pmpro_bp_level_profile = 'yes'; //default to showing Level on BuddyPress Profile 
	}

	require_once( PMPRO_DIR . '/adminpages/admin_header.php' ); ?>

	<div id="poststuff">
		<form action="" method="post" enctype="multipart/form-data">

		<h1><?php esc_html_e( 'Paid Memberships Pro - BuddyPress Add On Settings', 'pmpro-buddypress' ); ?></h1>
		<p><?php printf( wp_kses_post( __( 'Integrate and manage your BuddyPress Community using Paid Memberships Pro. <a href="%s" target="_blank">Read the documentation</a> for more information about this Add On.', 'pmpro-buddypress' ) ), 'https://www.paidmembershipspro.com/add-ons/buddypress-integration/' ); ?></p>

		<h3 class="topborder"><?php esc_html_e( 'Page Settings', 'pmpro-buddypress' ); ?></h3>
		<p><?php esc_html_e( 'This plugin redirects users to a specific page if they try to access restricted BuddyPress features. The user is redirected to the page assigned as the "Access Restricted" page under Memberships > Page Settings.', 'pmpro-buddypress' ); ?></p>
		<?php
			global $pmpro_pages;
			$pmprobp_restricted_page = $pmpro_pages['pmprobp_restricted'];
			if ( ! empty( $pmprobp_restricted_page ) ) {
				$msgt = '<span class="dashicons dashicons-yes"></span>' . __( '"Access Restricted" page is configured.', 'pmpro-buddypress' );
				$msgc = '#46b450';
			} else {
				$msgt = '<span class="dashicons dashicons-no"></span>' . __( '"Access Restricted" page is not configured.', 'pmpro-buddypress' );
				$msgc = '#a00';
			}
		?>
		<p><strong style="color: <?php echo $msgc; ?>"><?php echo esc_html( $msgt ) ; ?></strong></p>
		<p><a href="<?php echo admin_url('admin.php?page=pmpro-pagesettings');?>" class="button button-primary"><?php esc_html_e('Manage Page Settings', 'paid-memberships-pro' );?></a></p>

		<h3 class="topborder"><?php esc_html_e( 'Non-member User Settings', 'pmpro-buddypress' ); ?></h3>
		<p><?php esc_html_e( 'Set how BuddyPress should be locked down for users without a membership level.', 'pmpro-buddypress' ); ?></p>
		<?php 
			// Settings for Level 0 are for non-member users.
			pmpro_bp_restriction_settings_form(0);
		?>
		<p class="submit">
			<input name="savesettings" type="submit" class="button button-primary" value="<?php esc_html_e('Save All Settings', 'pmpro');?>" />
		</p>

		<h3 class="topborder"><?php esc_html_e( 'Membership Level Settings', 'pmpro-buddypress' ); ?></h3>
		<p><?php esc_html_e( 'Edit your membership levels to set level-specific restrictions on BuddyPress features.', 'pmpro-buddypress' ); ?></p>
		<p><a href="<?php echo admin_url('admin.php?page=pmpro-membershiplevels');?>" class="button button-primary"><?php esc_html_e('Edit Membership Levels', 'paid-memberships-pro' );?></a></p>
		
		<h3 class="topborder"><?php esc_html_e( 'General Settings', 'pmpro-buddypress' ); ?></h3>
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_register"><?php esc_html_e("Registration Page", 'pmpro');?></label>
				</th>
				<td>
					<select id="pmpro_bp_register" name="pmpro_bp_register">
						<option value="pmpro" <?php if($pmpro_bp_register == 'pmpro') { ?>selected="selected"<?php } ?>><?php esc_html_e('Use PMPro Levels Page', 'pmpro');?></option>
						<option value="buddypress" <?php if($pmpro_bp_register == 'buddypress') { ?>selected="selected"<?php } ?>><?php esc_html_e('Use BuddyPress Registration Page', 'pmpro');?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_level_profile"><?php esc_html_e("Show Membership Level on BuddyPress Profile?", 'pmpro');?></label>
				</th>
				<td>
					<select id="pmpro_bp_level_profile" name="pmpro_bp_level_profile">
						<option value="yes" <?php if($pmpro_bp_level_profile == 'yes') { ?>selected="selected"<?php } ?>><?php esc_html_e('Yes', 'pmpro');?></option>
						<option value="no" <?php if($pmpro_bp_level_profile == 'no') { ?>selected="selected"<?php } ?>><?php esc_html_e('No', 'pmpro');?></option>
					</select>
				</td>
			</tr>
		</tbody>
		</table>		
		<p class="submit">
			<input name="savesettings" type="submit" class="button button-primary" value="<?php esc_attr_e('Save All Settings', 'pmpro');?>" />
		</p>

		</form>
<?php
}
