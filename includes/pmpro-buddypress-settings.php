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

function pmpro_bp_add_admin_menu_page()
{
	add_submenu_page('pmpro-membershiplevels', __('PMPro BuddyPress', 'pmpro'), __('PMPro BuddyPress', 'pmpro'), 'manage_options', 'pmpro-buddypress', 'pmpro_bp_buddpress_admin_page');
}

add_action('admin_menu', 'pmpro_bp_add_admin_menu_page');

//redirect the Register button from wp-login.php
function pmpro_bp_registration_pmpro_to_bp_redirect($url)
{
	$bp_pages = get_option('bp-pages');
	
	$pmpro_bp_register = get_option('pmpro_bp_registration_page');
	if(!empty($pmpro_bp_register) && $pmpro_bp_register == 'buddypress')
	{
		$url = get_permalink($bp_pages['register']);
	}
	
	return $url;
}

add_filter('pmpro_register_redirect', 'pmpro_bp_registration_pmpro_to_bp_redirect');

function pmpro_bp_buddpress_admin_page()
{
	//get/set settings
	if(!empty($_REQUEST['savesettings']))
	{
		update_option('pmpro_bp_registration_page', $_POST['pmpro_bp_register']);
		update_option('pmpro_bp_show_level_on_bp_profile', $_POST['pmpro_bp_level_profile'], 'no') ;
	}
	
    $pmpro_bp_register = get_option('pmpro_bp_registration_page');
    $pmpro_bp_level_profile = get_option('pmpro_bp_show_level_on_bp_profile');
    
	if(empty($pmpro_bp_register))
		$pmpro_bp_register = 'pmpro'; //default to the PMPro Levels page 
    
	if(empty($pmpro_bp_level_profile))
		$pmpro_bp_level_profile = 'yes'; //default to showing Level on BuddyPress Profile 

	require_once( PMPRO_DIR . '/adminpages/admin_header.php' ); ?>

	<div id="poststuff">
		<h1><?php esc_attr_e( 'Paid Memberships Pro - BuddyPress Add On Settings', 'pmpro-buddypress' ); ?></h1>
		<p><?php printf( __( 'Integrate and manage your BuddyPress Community using Paid Memberships Pro. <a href="%s" target="_blank">Read the documentation</a> for more information about this Add On.', 'pmpro-buddypress' ), 'https://www.paidmembershipspro.com/add-ons/pmpro-buddypress/' ); ?></p>

		<h3 class="topborder"><?php esc_attr_e( 'Page Settings', 'pmpro-buddypress' ); ?></h3>
		<p><?php esc_attr_e( 'This plugin redirects users to a specific page if they try to access restricted BuddyPress features. The user is redirected to the page assigned as the "Access Restricted" page under Memberships > Page Settings.', 'pmpro-buddypress' ); ?></p>
		<?php
			global $pmpro_pages;
			$pmprobp_restricted_page = $pmpro_pages['pmprobp_restricted'];
			if ( ! empty( $pmprobp_restricted_page ) ) {
				$msgt = '<span class="dashicons dashicons-yes"></span>' . esc_attr( '"Access Restricted" page is configured.', 'pmpro-buddypress' );
				$msgc = '#46b450';
			} else {
				$msgt = '<span class="dashicons dashicons-no"></span>' . esc_attr( '"Access Restricted" page is not configured.', 'pmpro-buddypress' );
				$msgc = '#a00';
			}
		?>
		<p><strong style="color: <?php echo $msgc; ?>"><?php echo $msgt; ?></strong></p>
		<p><a href="<?php echo admin_url('admin.php?page=pmpro-pagesettings');?>" class="button button-primary"><?php _e('Manage Page Settings', 'paid-memberships-pro' );?></a></p>

		<h3 class="topborder"><?php esc_attr_e( 'Membership Level Settings', 'pmpro-buddypress' ); ?></h3>
		<p><?php esc_attr_e( 'Edit your membership levels to set level-specific restrictions on BuddyPress features. Features that can be restricted include: Group Creation, Single Group Viewing, Groups Page Viewing, Joining Groups, Public Messaging, Private Messaging, Send Friend Requests, and inclusion in the Member Directory.', 'pmpro-buddypress' ); ?></p>
		<p><a href="<?php echo admin_url('admin.php?page=pmpro-membershiplevels');?>" class="button button-primary"><?php _e('Edit Membership Levels', 'paid-memberships-pro' );?></a></p>
		<form action="" method="post" enctype="multipart/form-data">
			<h3 class="topborder"><?php esc_attr_e( 'General Settings', 'pmpro-buddypress' ); ?></h3>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="pmpro_bp_register"><?php _e("Registration Page", 'pmpro');?></label>
					</th>
					<td>
						<select id="pmpro_bp_register" name="pmpro_bp_register">
							<option value="pmpro" <?php if($pmpro_bp_register == 'pmpro') { ?>selected="selected"<?php } ?>><?php _e('Use PMPro Levels Page', 'pmpro');?></option>
							<option value="buddypress" <?php if($pmpro_bp_register == 'buddypress') { ?>selected="selected"<?php } ?>><?php _e('Use BuddyPress Registration Page', 'pmpro');?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<th scope="row" valign="top">
						<label for="pmpro_bp_level_profile"><?php _e("Show Membership Level on BuddyPress Profile?", 'pmpro');?></label>
					</th>
					<td>
						<select id="pmpro_bp_level_profile" name="pmpro_bp_level_profile">
							<option value="yes" <?php if($pmpro_bp_level_profile == 'yes') { ?>selected="selected"<?php } ?>><?php _e('Yes', 'pmpro');?></option>
							<option value="no" <?php if($pmpro_bp_level_profile == 'no') { ?>selected="selected"<?php } ?>><?php _e('No', 'pmpro');?></option>
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			<p class="submit">
				<input name="savesettings" type="submit" class="button button-primary" value="<?php _e('Save Settings', 'pmpro');?>" />
			</p>
		</form>
<?php
}
