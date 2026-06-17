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
		$pmpro_bp_docs_view = isset( $_POST['pmpro_bp_docs_view'] ) ? intval( $_POST['pmpro_bp_docs_view'] ) : 0;
		$pmpro_bp_docs_upload = isset( $_POST['pmpro_bp_docs_upload'] ) ? intval( $_POST['pmpro_bp_docs_upload'] ) : 0;

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
			'pmpro_bp_docs_view'				=> $pmpro_bp_docs_view,
			'pmpro_bp_docs_upload'				=> $pmpro_bp_docs_upload,
			'pmpro_bp_group_automatic_add'		=> array(),
			'pmpro_bp_group_can_request_invite'	=> array(),
			'pmpro_bp_member_types'				=> array());
		update_option('pmpro_bp_options_users', $pmpro_bp_options, 'no');

		// General Settings
		$register_value = isset( $_POST['pmpro_bp_register'] ) ? sanitize_text_field( wp_unslash( $_POST['pmpro_bp_register'] ) ) : 'buddypress';
		if ( ! in_array( $register_value, array( 'pmpro', 'buddypress' ), true ) ) {
			$register_value = 'buddypress';
		}
		update_option( 'pmpro_bp_registration_page', $register_value );

		$level_profile_value = isset( $_POST['pmpro_bp_level_profile'] ) ? sanitize_text_field( wp_unslash( $_POST['pmpro_bp_level_profile'] ) ) : 'no';
		if ( ! in_array( $level_profile_value, array( 'yes', 'no' ), true ) ) {
			$level_profile_value = 'no';
		}
		update_option( 'pmpro_bp_show_level_on_bp_profile', $level_profile_value, 'no' );

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
				<p><?php esc_html_e( 'Edit your membership levels to set level-specific restrictions on community features. These settings are managed in the "BuddyPress Restrictions" section when editing a membership level.', 'pmpro-buddypress' ); ?></p>
				<?php
					$pmpro_bp_levels = pmpro_getAllLevels( true, true );
					if ( function_exists( 'pmpro_sort_levels_by_order' ) ) {
						$pmpro_bp_levels = pmpro_sort_levels_by_order( $pmpro_bp_levels );
					}
				?>
				<?php if ( empty( $pmpro_bp_levels ) ) { ?>
					<p><strong><?php esc_html_e( 'No membership levels found.', 'pmpro-buddypress' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'admin.php?page=pmpro-membershiplevels' ) ); ?>"><?php esc_html_e( 'Create a membership level to get started.', 'pmpro-buddypress' ); ?></a></p>
				<?php } else { ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Level', 'pmpro-buddypress' ); ?></th>
								<th scope="col"><?php esc_html_e( 'BuddyPress Access', 'pmpro-buddypress' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Member Types', 'pmpro-buddypress' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Auto-Join Groups', 'pmpro-buddypress' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Actions', 'pmpro-buddypress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $pmpro_bp_levels as $pmpro_bp_level ) { ?>
								<?php
									// Read the raw per-level option so "use non-member settings" is
									// reported as such instead of being resolved to its effective values.
									$level_options = get_option( 'pmpro_bp_options_' . $pmpro_bp_level->id, array() );
									$level_options = array_merge( pmpro_bp_get_level_options( -1 ), (array) $level_options );

									$access_note = '';
									switch ( (int) $level_options['pmpro_bp_restrictions'] ) {
										case PMPROBP_USE_NON_MEMBER_SETTINGS:
											$access_label     = __( 'Non-member Settings', 'pmpro-buddypress' );
											$access_tag_class = 'info';
											break;
										case PMPROBP_GIVE_ALL_ACCESS:
											$access_label     = __( 'Unlocked', 'pmpro-buddypress' );
											$access_tag_class = 'success';
											break;
										case PMPROBP_SPECIFIC_FEATURES:
											$feature_keys = array( 'pmpro_bp_group_creation', 'pmpro_bp_group_single_viewing', 'pmpro_bp_groups_page_viewing', 'pmpro_bp_groups_join', 'pmpro_bp_private_messaging', 'pmpro_bp_public_messaging', 'pmpro_bp_send_friend_request', 'pmpro_bp_member_directory' );
										if ( function_exists( 'bb_user_can_create_document' ) || function_exists( 'bp_docs_user_can' ) ) {
											$feature_keys[] = 'pmpro_bp_docs_view';
											$feature_keys[] = 'pmpro_bp_docs_upload';
										}
											$enabled      = 0;
											foreach ( $feature_keys as $feature_key ) {
												if ( ! empty( $level_options[ $feature_key ] ) ) {
													$enabled++;
												}
											}
											$access_label     = __( 'Partial Access', 'pmpro-buddypress' );
											$access_tag_class = 'alert';
											// translators: %1$d is the number of unlocked features, %2$d is the total number of features.
											$access_note      = sprintf( __( '%1$d of %2$d features unlocked', 'pmpro-buddypress' ), $enabled, count( $feature_keys ) );
											break;
										case PMPROBP_LOCK_ALL_ACCESS:
										default:
											$access_label     = __( 'Locked', 'pmpro-buddypress' );
											$access_tag_class = 'error';
											break;
									}

									// Member type names for this level. The saved option may be false or
									// contain empty entries when nothing is selected, so filter those out.
									$member_type_names = array();
									foreach ( array_filter( (array) $level_options['pmpro_bp_member_types'] ) as $member_type ) {
										$member_type_object  = function_exists( 'bp_get_member_type_object' ) ? bp_get_member_type_object( $member_type ) : null;
										$member_type_names[] = ! empty( $member_type_object->labels['singular_name'] ) ? $member_type_object->labels['singular_name'] : $member_type;
									}

									// Group names this level is automatically added to (same false/empty handling).
									$group_names = array();
									foreach ( array_filter( (array) $level_options['pmpro_bp_group_automatic_add'] ) as $group_id ) {
										$group         = function_exists( 'groups_get_group' ) ? groups_get_group( $group_id ) : null;
										$group_names[] = ! empty( $group->name ) ? $group->name : '#' . (int) $group_id;
									}

									$level_edit_url = admin_url( 'admin.php?page=pmpro-membershiplevels&edit=' . (int) $pmpro_bp_level->id );
								?>
								<tr>
									<td><a href="<?php echo esc_url( $level_edit_url ); ?>" target="_blank"><?php echo esc_html( $pmpro_bp_level->name ); ?></a></td>
									<td>
										<span class="pmpro_tag pmpro_tag-<?php echo esc_attr( $access_tag_class ); ?>"><?php echo esc_html( $access_label ); ?></span>
										<?php if ( ! empty( $access_note ) ) { ?>
											<p class="description"><?php echo esc_html( $access_note ); ?></p>
										<?php } ?>
									</td>
									<td><?php echo ! empty( $member_type_names ) ? esc_html( implode( ', ', $member_type_names ) ) : '&#8212;'; ?></td>
									<td><?php echo ! empty( $group_names ) ? esc_html( implode( ', ', $group_names ) ) : '&#8212;'; ?></td>
									<td><a href="<?php echo esc_url( $level_edit_url ); ?>" target="_blank"><?php esc_html_e( 'Edit Level', 'pmpro-buddypress' ); ?></a></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } ?>
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
					<?php esc_html_e( 'Extended Profile Field Mapping', 'pmpro-buddypress' ); ?>
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
