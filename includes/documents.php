<?php
/*
	Code to restrict BuddyBoss Platform Documents and BP Docs based on membership level.
*/


/**
 * Block the BuddyBoss AJAX file-upload step for documents when upload is restricted.
 *
 * BuddyBoss registers wp_ajax_document_document_upload in admin_init at priority 10.
 * Running at priority 1 ensures we exit before their callback fires.
 * This covers both profile and group upload contexts.
 */
function pmpro_bp_ajax_document_upload_check() {
	if ( ! pmpro_bp_user_can( 'docs_upload' ) ) {
		wp_send_json_error(
			array( 'feedback' => __( 'You do not have permission to upload documents.', 'pmpro-buddypress' ) ),
			403
		);
	}
}
add_action( 'wp_ajax_document_document_upload', 'pmpro_bp_ajax_document_upload_check', 1 );

/**
 * Block the BuddyBoss AJAX document-save step when upload is restricted.
 *
 * The save handler calls groups_can_user_manage_document() directly for group uploads,
 * bypassing bb_user_can_create_document, so we must intercept at the AJAX action level.
 */
function pmpro_bp_ajax_document_save_check() {
	if ( ! pmpro_bp_user_can( 'docs_upload' ) ) {
		wp_send_json_error(
			array( 'feedback' => __( 'You do not have permission to upload documents.', 'pmpro-buddypress' ) ),
			403
		);
	}
}
add_action( 'wp_ajax_document_document_save', 'pmpro_bp_ajax_document_save_check', 1 );

/**
 * Redirect away from the BuddyBoss Documents component page when viewing is restricted.
 */
function pmpro_bp_restrict_bb_document_viewing() {
	if ( ! function_exists( 'bp_is_current_component' ) ) {
		return;
	}

	if ( bp_is_current_component( 'document' ) && ! pmpro_bp_user_can( 'docs_view' ) ) {
		pmpro_bp_redirect_to_access_required_page();
	}
}
if ( function_exists( 'bb_user_can_create_document' ) ) {
	add_action( 'template_redirect', 'pmpro_bp_restrict_bb_document_viewing' );
}

/**
 * Remove the Documents nav item from member profiles when viewing is restricted.
 */
function pmpro_bp_remove_document_nav() {
	if ( ! function_exists( 'bp_core_remove_nav_item' ) ) {
		return;
	}

	if ( ! pmpro_bp_user_can( 'docs_view' ) ) {
		bp_core_remove_nav_item( 'document' );
	}
}
if ( function_exists( 'bb_user_can_create_document' ) ) {
	add_action( 'bp_setup_nav', 'pmpro_bp_remove_document_nav', 100 );
}

// -------------------------------------------------------------------------
// BP Docs
// -------------------------------------------------------------------------

/**
 * Prevent users from creating BP Docs when restricted.
 * Hooks into BP Docs' own "can create in context" filter.
 */
function pmpro_bp_bp_docs_user_can_create( $can_create ) {
	if ( $can_create && ! pmpro_bp_user_can( 'docs_upload' ) ) {
		$can_create = false;
	}
	return $can_create;
}
add_filter( 'bp_docs_current_user_can_create_in_context', 'pmpro_bp_bp_docs_user_can_create' );

/**
 * Restrict per-doc read access for users who don't have view permission.
 * This fires on the bp_docs_user_can filter for 'read' and 'read_comments' actions.
 */
function pmpro_bp_bp_docs_user_can( $user_can, $action, $user_id, $doc_id ) {
	if ( in_array( $action, array( 'read', 'read_comments' ), true ) && ! pmpro_bp_user_can( 'docs_view', $user_id ) ) {
		$user_can = false;
	}

	if ( 'create' === $action && ! pmpro_bp_user_can( 'docs_upload', $user_id ) ) {
		$user_can = false;
	}

	return $user_can;
}
add_filter( 'bp_docs_user_can', 'pmpro_bp_bp_docs_user_can', 10, 4 );

/**
 * Redirect away from BP Docs pages when view access is restricted.
 */
function pmpro_bp_restrict_bp_docs_viewing() {
	if ( ! function_exists( 'bp_docs_is_docs_component' ) ) {
		return;
	}

	if ( bp_docs_is_docs_component() && ! pmpro_bp_user_can( 'docs_view' ) ) {
		pmpro_bp_redirect_to_access_required_page();
	}
}
if ( function_exists( 'bp_docs_is_docs_component' ) ) {
	add_action( 'template_redirect', 'pmpro_bp_restrict_bp_docs_viewing' );
}
