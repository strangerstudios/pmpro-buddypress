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
	add_submenu_page( $parent_page, __('PMPro BuddyPress', 'pmpro-buddypress'), __('PMPro BuddyPress', 'pmpro-buddypress'), 'manage_options', 'pmpro-buddypress', 'pmpro_bp_buddpress_admin_page' );
}
add_action( 'admin_menu', 'pmpro_bp_add_admin_menu_page' );

function pmpro_bp_buddpress_admin_page() {

	global $pmpro_pages, $msg, $msgt;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'pmpro-buddypress' ) );
	}

	//get/set settings
	if ( ! empty( $_POST['savesettings'] ) ) {
		check_admin_referer( 'pmpro_bp_save_settings', 'pmpro_bp_settings_nonce' );

		// Non-member user Restrictions
		$can_create_groups = isset( $_POST['pmpro_bp_group_creation'] ) ? intval( $_POST['pmpro_bp_group_creation'] ) : 0;
		$can_view_single_group = isset( $_POST['pmpro_bp_group_single_viewing'] ) ? intval( $_POST['pmpro_bp_group_single_viewing'] ) : 0;
		$can_view_groups_page = isset( $_POST['pmpro_bp_groups_page_viewing'] ) ? intval( $_POST['pmpro_bp_groups_page_viewing'] ) : 0;
		$can_join_groups = isset( $_POST['pmpro_bp_groups_join'] ) ? intval( $_POST['pmpro_bp_groups_join'] ) : 0;
		$pmpro_bp_restrictions = isset( $_POST['pmpro_bp_restrictions'] ) ? intval( $_POST['pmpro_bp_restrictions'] ) : 0;
		$pmpro_bp_public_messaging = isset( $_POST['pmpro_bp_public_messaging'] ) ? intval( $_POST['pmpro_bp_public_messaging'] ) : 0;
		$pmpro_bp_private_messaging = isset( $_POST['pmpro_bp_private_messaging'] ) ? intval( $_POST['pmpro_bp_private_messaging'] ) : 0;
		$pmpro_bp_send_friend_request = isset( $_POST['pmpro_bp_send_friend_request'] ) ? intval( $_POST['pmpro_bp_send_friend_request'] ) : 0;
		$pmpro_bp_member_directory = isset( $_POST['pmpro_bp_member_directory'] ) ? intval( $_POST['pmpro_bp_member_directory'] ) : 0;

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
		update_option( 'pmpro_bp_registration_page', isset( $_POST['pmpro_bp_register'] ) ? sanitize_text_field( wp_unslash( $_POST['pmpro_bp_register'] ) ) : 'pmpro' );
		update_option( 'pmpro_bp_show_level_on_bp_profile', isset( $_POST['pmpro_bp_level_profile'] ) ? sanitize_text_field( wp_unslash( $_POST['pmpro_bp_level_profile'] ) ) : 'yes', 'no' );

		// Xprofile Field Mapping
		pmpro_bp_save_xprofile_field_map();

		// Assume Success
		$msg = 1;
		$msgt = __( 'Your settings have been updated.', 'pmpro-buddypress' );
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

	<form action="" method="post">
		<?php wp_nonce_field( 'pmpro_bp_save_settings', 'pmpro_bp_settings_nonce' ); ?>
		<hr class="wp-header-end">
		<h1><?php esc_html_e( 'Paid Memberships Pro - BuddyPress & BuddyBoss Add On Settings', 'pmpro-buddypress' ); ?></h1>
		<p><?php printf( __( 'Restrict access to communities in BuddyPress and BuddyBoss for free or premium members with Paid Memberships Pro. <strong>This plugin is compatible with both BuddyPress and BuddyBoss.</strong> <a href="%s" target="_blank">Read the documentation</a> for more information about this Add On.', 'pmpro-buddypress' ), 'https://www.paidmembershipspro.com/add-ons/buddypress-integration/?utm_source=plugin&utm_medium=pmpro-buddpress-settings&utm_campaign=pmpro-buddypress' ); ?></p>

		<div id="pmpro-bp-page-settings" class="pmpro_section" data-visibility="shown" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
					<span class="dashicons dashicons-arrow-up-alt2"></span>
					<?php esc_html_e( 'Page Settings', 'pmpro-buddypress' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside">
				<p><?php esc_html_e( 'This plugin redirects users to a specific page if they try to access restricted features. The user is redirected to the page assigned as the "Access Restricted" page under Memberships > Settings > Page Settings.', 'pmpro-buddypress' ); ?></p>
				<?php
					$pmprobp_restricted_page = $pmpro_pages['pmprobp_restricted'];
					if ( ! empty( $pmprobp_restricted_page ) ) {
						$page_msgt = '<span class="dashicons dashicons-yes"></span>' . esc_html__( '"Access Restricted" page is configured.', 'pmpro-buddypress' );
						$page_msgc = '#46b450';
					} else {
						$page_msgt = '<span class="dashicons dashicons-no"></span>' . esc_html__( '"Access Restricted" page is not configured.', 'pmpro-buddypress' );
						$page_msgc = '#a00';
					}
				?>
				<p><strong style="color: <?php echo esc_attr( $page_msgc ); ?>"><?php echo $page_msgt; ?></strong></p>
				<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pmpro-pagesettings' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Manage Page Settings', 'pmpro-buddypress' ); ?></a></p>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

		<div id="pmpro-bp-non-member-settings" class="pmpro_section" data-visibility="hidden" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'Non-member User Settings', 'pmpro-buddypress' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside" style="display: none;">
				<p><?php esc_html_e( 'Set how BuddyPress should be locked down for users without a membership level.', 'pmpro-buddypress' ); ?></p>
				<?php
					// Settings for Level 0 are for non-member users.
					pmpro_bp_restriction_settings_form(0);
				?>
				<p class="submit">
					<input name="savesettings" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save All Settings', 'pmpro-buddypress' ); ?>" />
				</p>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

		<div id="pmpro-bp-level-settings" class="pmpro_section" data-visibility="hidden" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'Membership Level Settings', 'pmpro-buddypress' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside" style="display: none;">
				<p><?php esc_html_e( 'Edit your membership levels to set level-specific restrictions on community features.', 'pmpro-buddypress' ); ?></p>
				<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pmpro-membershiplevels' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit Membership Levels', 'pmpro-buddypress' ); ?></a></p>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

		<div id="pmpro-bp-general-settings" class="pmpro_section" data-visibility="hidden" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'General Settings', 'pmpro-buddypress' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside" style="display: none;">
				<?php if ( defined( 'BP_PLATFORM_VERSION' ) ) { ?>
					<p class="description"><?php esc_html_e( 'Note: These settings apply to sites running BuddyPress or BuddyBoss.', 'pmpro-buddypress' ); ?></p>
				<?php } ?>
				<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<label for="pmpro_bp_register"><?php esc_html_e( 'Registration Page', 'pmpro-buddypress' ); ?></label>
						</th>
						<td>
							<select id="pmpro_bp_register" name="pmpro_bp_register">
								<option value="pmpro" <?php selected( $pmpro_bp_register, 'pmpro' ); ?>><?php esc_html_e( 'Use PMPro Levels Page', 'pmpro-buddypress' ); ?></option>
								<option value="buddypress" <?php selected( $pmpro_bp_register, 'buddypress' ); ?>><?php esc_html_e( 'Use BuddyPress Registration Page', 'pmpro-buddypress' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="pmpro_bp_level_profile"><?php esc_html_e( 'Show Membership Level on Profiles?', 'pmpro-buddypress' ); ?></label>
						</th>
						<td>
							<select id="pmpro_bp_level_profile" name="pmpro_bp_level_profile">
								<option value="yes" <?php selected( $pmpro_bp_level_profile, 'yes' ); ?>><?php esc_html_e( 'Yes', 'pmpro-buddypress' ); ?></option>
								<option value="no" <?php selected( $pmpro_bp_level_profile, 'no' ); ?>><?php esc_html_e( 'No', 'pmpro-buddypress' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
				</table>
				<p class="submit">
					<input name="savesettings" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save All Settings', 'pmpro-buddypress' ); ?>" />
				</p>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->

		<div id="pmpro-bp-xprofile-mapping" class="pmpro_section" data-visibility="hidden" data-activated="true">
			<div class="pmpro_section_toggle">
				<button class="pmpro_section-toggle-button" type="button" aria-expanded="false">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'Xprofile Field Mapping', 'pmpro-buddypress' ); ?>
				</button>
			</div>
			<div class="pmpro_section_inside" style="display: none;">
				<?php pmpro_bp_render_xprofile_mapping_section(); ?>
				<p class="submit">
					<input name="savesettings" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save All Settings', 'pmpro-buddypress' ); ?>" />
				</p>
			</div> <!-- end pmpro_section_inside -->
		</div> <!-- end pmpro_section -->
	</form>
	<?php
	require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
}
