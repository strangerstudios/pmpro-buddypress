<?php
/**
 * Code to create a Memberships -> BuddyPress page with settings.
 */

function pmpro_bp_extra_page_settings( $pages ) {
	$pages['pmprobp_restricted'] = array(
		'title' => 'Access Restricted',
		'content' => '[pmpro_buddypress_restricted]',
		'hint' => 'Include the shortcode [pmpro_buddypress_restricted].',
	);
	return $pages;
}
add_action( 'pmpro_extra_page_settings', 'pmpro_bp_extra_page_settings', 10, 1 );

function pmpro_bp_add_admin_menu_page() {
	add_submenu_page( 'pmpro-membershiplevels', __( 'PMPro BuddyPress', 'pmpro' ), __( 'PMPro BuddyPress', 'pmpro' ), 'manage_options', 'pmpro-buddypress', 'pmpro_bp_buddpress_admin_page' );
}

add_action( 'admin_menu', 'pmpro_bp_add_admin_menu_page' );

// redirect the Register button from wp-login.php
function pmpro_bp_registration_pmpro_to_bp_redirect( $url ) {
	$bp_pages = get_option( 'bp-pages' );

	$pmpro_bp_register = get_option( 'pmpro_bp_registration_page' );
	if ( ! empty( $pmpro_bp_register ) && $pmpro_bp_register == 'buddypress' ) {
		$url = get_permalink( $bp_pages['register'] );
	}

	return $url;
}

add_filter( 'pmpro_register_redirect', 'pmpro_bp_registration_pmpro_to_bp_redirect' );

function pmpro_bp_buddpress_admin_page() {
		// get/set settings
	if ( ! empty( $_REQUEST['savesettings'] ) ) {
		update_option( 'pmpro_bp_registration_page', $_POST['pmpro_bp_register'] );
		update_option( 'pmpro_bp_show_level_on_bp_profile', $_POST['pmpro_bp_level_profile'], 'no' );
		update_option( 'pmpro_bp_multisite_redirect_target', $_POST['pmpro_bp_multisite_redirect'], 'activity' );
	}

	$pmpro_bp_register = get_option( 'pmpro_bp_registration_page' );
	$pmpro_bp_level_profile = get_option( 'pmpro_bp_show_level_on_bp_profile' );
	$pmpro_bp_multisite_redirect = get_option( 'pmpro_bp_multisite_redirect_target' );

	if ( empty( $pmpro_bp_register ) ) {
		$pmpro_bp_register = 'pmpro'; // default to the PMPro Levels page
	}

	if ( empty( $pmpro_bp_level_profile ) ) {
		$pmpro_bp_level_profile = 'yes';
	} // default to showing Level on BuddyPress Profile

	if ( empty( $pmpro_bp_multisite_redirect ) ) {
		$pmpro_bp_multisite_redirect = 'profile';
	} // default to showing Level on BuddyPress Profile ?>

	<h2>PMPro BuddyPress Settings</h2>
		<form action="" method="post" enctype="multipart/form-data">
		
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_register"><?php _e( 'Registration Page', 'pmpro' ); ?></label>
				</th>
				<td>
					<select id="pmpro_bp_register" name="pmpro_bp_register">
						<option value="pmpro" 
						<?php
						if ( $pmpro_bp_register == 'pmpro' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Use PMPro Levels Page', 'pmpro' ); ?></option>
						<option value="buddypress" 
						<?php
						if ( $pmpro_bp_register == 'buddypress' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Use BuddyPress Registration Page', 'pmpro' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_level_profile"><?php _e( 'Show Membership Level on BuddyPress Profile?', 'pmpro' ); ?></label>
				</th>
				<td>
					<select id="pmpro_bp_level_profile" name="pmpro_bp_level_profile">
						<option value="yes" 
						<?php
						if ( $pmpro_bp_level_profile == 'yes' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Yes', 'pmpro' ); ?></option>
						<option value="no" 
						<?php
						if ( $pmpro_bp_level_profile == 'no' ) {
?>
selected="selected"<?php } ?>><?php _e( 'No', 'pmpro' ); ?></option>
					</select>
				</td>
			</tr>
			<?php if ( is_multisite() ) : ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pmpro_bp_multisite_redirect"><?php _e( 'Multisite Redirect', 'pmpro' ); ?></label>
				</th>
				<td>
					<select id="pmpro_bp_multisite_redirect" name="pmpro_bp_multisite_redirect">
						<option value="activity" 
						<?php
						if ( $pmpro_bp_multisite_redirect == 'activity' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Activity Stream', 'pmpro' ); ?></option>
						<option value="profile" 
						<?php
						if ( $pmpro_bp_multisite_redirect == 'profile' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Profile', 'pmpro' ); ?></option>
						<option value="subsite" 
						<?php
						if ( $pmpro_bp_multisite_redirect == 'subsite' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Subsite Homepage', 'pmpro' ); ?></option>
						<option value="main-site" 
						<?php
						if ( $pmpro_bp_multisite_redirect == 'main-site' ) {
?>
selected="selected"<?php } ?>><?php _e( 'Main Site Homepage', 'pmpro' ); ?></option>
					</select>
					<?php
					if ( $pmpro_bp_multisite_redirect ) {
						echo '<h4>' . $pmpro_bp_multisite_redirect . '</h4>';
					}
					?>

				 </td>
			</tr>
		<?php endif; ?>		
	</tbody>
	</table>

	<p class="submit">
		<input name="savesettings" type="submit" class="button button-primary" value="<?php _e( 'Save Settings', 'pmpro' ); ?>" />
	</p>
</form>
<?php
}
