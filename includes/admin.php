<?php
/**
 * Runs only when the plugin is activated.
 *
 * @since 0.1.0
 */
function pmpro_bp_admin_notice_activation_hook() {
	// Create transient data.
	set_transient( 'pmpro-bp-admin-notice', true, 5 );
}
register_activation_hook( PMPROBP_BASENAME, 'pmpro_bp_admin_notice_activation_hook' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */
function pmpro_bp_admin_notice() {
	// Check transient, if available display notice.
	if ( get_transient( 'pmpro-bp-admin-notice' ) ) { ?>
		<div class="updated notice is-dismissible">
			<p><?php printf( __( 'Thank you for activating. <a href="%s">Visit the settings page</a> to get started with the BuddyPress Add On.', 'pmpro-buddypress' ), get_admin_url( null, 'admin.php?page=pmpro-buddypress' ) ); ?></p>
		</div>
		<?php
		// Delete transient, only display this notice once.
		delete_transient( 'pmpro-bp-admin-notice' );
	}
}
add_action( 'admin_notices', 'pmpro_bp_admin_notice' );

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function pmpro_bp_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . get_admin_url( null, 'admin.php?page=pmpro-buddypress' ) . '">' . __( 'Settings', 'pmpro-buddypress' ) . '</a>',
		);
	}
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . PMPROBP_BASENAME, 'pmpro_bp_plugin_action_links' );

/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function pmpro_bp_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-buddypress.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/buddypress-integration/' ) . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro-buddypress' ) . '</a>',
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro-buddypress' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_bp_plugin_row_meta', 10, 2 );