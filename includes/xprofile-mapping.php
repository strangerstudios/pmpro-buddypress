<?php
/**
 * Settings screen to map BuddyPress/BuddyBoss Xprofile fields to Paid
 * Memberships Pro User Fields. (1:1 mapping only).
 * @since TBD
*/

/**
 * Get the saved Xprofile field => User Field map.
 *
 * @since TBD
 * @return array Map of xprofile field ID => user field meta_key.
 */
function pmpro_bp_get_xprofile_field_map() {
	$map = get_option( 'pmpro_bp_xprofile_field_map', array() );

	if ( ! is_array( $map ) ) {
		$map = array();
	}

	return $map;
}

/**
 * Get all registered PMPro user fields, flattened and de-duplicated by meta_key.
 *
 * Fields are stored per group/location and the same field can appear in more
 * than one location, so we collapse to a single entry per meta_key.
 *
 * @since TBD
 * @return PMPro_Field[] Array of field objects keyed by meta_key.
 */
function pmpro_bp_get_all_user_fields() {
	$fields = array();

	if ( ! class_exists( 'PMPro_Field_Group' ) ) {
		return $fields;
	}

	foreach ( PMPro_Field_Group::get_all() as $group ) {
		foreach ( $group->get_fields() as $user_field ) {
			if ( empty( $user_field->meta_key ) ) {
				continue;
			}

			if ( ! isset( $fields[ $user_field->meta_key ] ) ) {
				$fields[ $user_field->meta_key ] = $user_field;
			}
		}
	}

	return $fields;
}

/**
 * Get the list of available Xprofile fields for the mapping table.
 *
 * The primary "Name" field is excluded: BuddyPress already keeps that field
 * in sync with the WordPress user's display name on its own.
 *
 * @since TBD
 * @return array Map of xprofile field ID => field name.
 */
function pmpro_bp_get_xprofile_fields() {
	$xprofile_fields = array();

	if ( ! function_exists( 'bp_xprofile_get_groups' ) ) {
		return $xprofile_fields;
	}

	$groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );

	if ( empty( $groups ) ) {
		return $xprofile_fields;
	}

	$fullname_field_id = function_exists( 'bp_xprofile_fullname_field_id' ) ? (int) bp_xprofile_fullname_field_id() : 0;

	foreach ( $groups as $group ) {
		if ( empty( $group->fields ) ) {
			continue;
		}

		foreach ( $group->fields as $xprofile_field ) {
			// Skip the primary "Name" field; BuddyPress syncs it with display_name itself.
			if ( ! empty( $fullname_field_id ) && (int) $xprofile_field->id === $fullname_field_id ) {
				continue;
			}

			// Key on the field ID so the mapping survives a field being renamed.
			$xprofile_fields[ (int) $xprofile_field->id ] = $xprofile_field->name;
		}
	}

	return $xprofile_fields;
}

/**
 * Resolve a stored ->buddypress value to an Xprofile field ID.
 *
 * Accepts either an Xprofile field ID (the format saved by this settings
 * screen) or an Xprofile field name (the legacy format that code-based
 * mappings may still use), so both keep working.
 *
 * @since TBD
 * @param int|string $buddypress The ->buddypress attribute value.
 * @return int The Xprofile field ID, or 0 if it can't be resolved.
 */
function pmpro_bp_resolve_xprofile_field_id( $buddypress ) {
	if ( empty( $buddypress ) ) {
		return 0;
	}

	// Numeric value: treat as a field ID directly.
	if ( is_numeric( $buddypress ) ) {
		return (int) $buddypress;
	}

	// Otherwise treat it as a legacy field name.
	if ( function_exists( 'xprofile_get_field_id_from_name' ) ) {
		return (int) xprofile_get_field_id_from_name( $buddypress );
	}

	return 0;
}

/**
 * Apply the saved map to the registered user fields object.
 *
 * Loops through the registered user fields and stamps the ->buddypress attribute
 * onto each field that has a mapping. profiles.php then handles the actual
 * two-way sync based on that attribute.
 *
 * Runs after PMPro loads its settings-based fields (init, priority 1).
 *
 * A UI mapping always takes precedence over a code-set ->buddypress attribute
 * for the same meta_key: if a developer has set $field->buddypress in code and a
 * UI mapping exists for that field's meta_key, the UI mapping overwrites it.
 *
 * @since TBD
 */
function pmpro_bp_apply_xprofile_field_map() {
	if ( ! class_exists( 'PMPro_Field_Group' ) ) {
		return;
	}

	$map = pmpro_bp_get_xprofile_field_map();

	if ( empty( $map ) ) {
		return;
	}

	// The map is keyed by Xprofile field ID; flip it to meta_key => xprofile_id
	// so we can look up each user field as we loop.
	$xprofile_id_by_meta_key = array();
	foreach ( $map as $xprofile_id => $meta_key ) {
		$xprofile_id_by_meta_key[ $meta_key ] = (int) $xprofile_id;
	}

	foreach ( PMPro_Field_Group::get_all() as $group ) {
		foreach ( $group->get_fields() as $user_field ) {
			if ( empty( $user_field->meta_key ) ) {
				continue;
			}

			if ( ! empty( $xprofile_id_by_meta_key[ $user_field->meta_key ] ) ) {
				// Stamp the Xprofile field ID onto the field object so profiles.php picks it up.
				$user_field->buddypress = $xprofile_id_by_meta_key[ $user_field->meta_key ];
			}
		}
	}
}
add_action( 'init', 'pmpro_bp_apply_xprofile_field_map', 20 );

/**
 * Save the submitted Xprofile field map.
 *
 * Called from the main PMPro BuddyPress settings handler when its form is
 * submitted, so the map is persisted alongside the other settings. The caller
 * is responsible for capability and nonce checks on the request.
 *
 * @since TBD
 */
function pmpro_bp_save_xprofile_field_map() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$user_fields     = pmpro_bp_get_all_user_fields();
	$xprofile_fields = pmpro_bp_get_xprofile_fields();

	// If either side of the mapping is unavailable (e.g. the Xprofile component
	// is disabled or no user fields are registered right now), leave the saved
	// map alone rather than wiping it.
	if ( empty( $user_fields ) || empty( $xprofile_fields ) ) {
		return;
	}

	$submitted = isset( $_POST['pmpro_bp_xprofile_map'] ) ? (array) $_POST['pmpro_bp_xprofile_map'] : array();

	$new_map        = array();
	$used_meta_keys = array();

	// Submitted as xprofile_field_id => user_field_meta_key.
	foreach ( $submitted as $xprofile_id => $meta_key ) {
		$xprofile_id = (int) $xprofile_id;
		$meta_key    = sanitize_text_field( wp_unslash( $meta_key ) );

		// Skip blanks and anything we don't recognize.
		if ( empty( $meta_key ) || ! isset( $xprofile_fields[ $xprofile_id ] ) || ! isset( $user_fields[ $meta_key ] ) ) {
			continue;
		}

		// Enforce a strict one-to-one mapping: a user field can only be claimed once.
		if ( isset( $used_meta_keys[ $meta_key ] ) ) {
			continue;
		}

		$new_map[ $xprofile_id ]    = $meta_key;
		$used_meta_keys[ $meta_key ] = true;
	}

	update_option( 'pmpro_bp_xprofile_field_map', $new_map, 'no' );
}

/**
 * Render the Xprofile Field Mapping section content.
 *
 * Outputs the mapping table and helper JS. Designed to be called from inside
 * the main PMPro BuddyPress settings <form>, so it has no <form> tag, nonce,
 * heading, or submit button of its own — the settings page provides those and
 * pmpro_bp_save_xprofile_field_map() saves the map on submit.
 *
 * @since TBD
 */
function pmpro_bp_render_xprofile_mapping_section() {
	$user_fields     = pmpro_bp_get_all_user_fields();
	$xprofile_fields = pmpro_bp_get_xprofile_fields();
	$map             = pmpro_bp_get_xprofile_field_map();
	?>
	<p><?php esc_html_e( 'Map BuddyPress or BuddyBoss extended profile fields to user meta fields. Each extended profile field can be mapped to one user field. Fields stay in sync when updated in either location.', 'pmpro-buddypress' ); ?></p>

	<?php if ( empty( $xprofile_fields ) ) { ?>
		<div class="notice notice-warning inline"><p><?php esc_html_e( 'No Xprofile fields were found. Make sure BuddyPress or BuddyBoss is active and you have created Profile Fields.', 'pmpro-buddypress' ); ?></p></div>
	<?php } elseif ( empty( $user_fields ) ) { ?>
		<div class="notice notice-warning inline"><p>
			<?php
			printf(
				// translators: %s is a link to the PMPro User Fields settings screen.
				esc_html__( 'No Paid Memberships Pro User Fields were found. %s to add some first.', 'pmpro-buddypress' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=pmpro-userfields' ) ) . '">' . esc_html__( 'Edit User Fields', 'pmpro-buddypress' ) . '</a>'
			);
			?>
		</p></div>
	<?php } else { ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Xprofile Field', 'pmpro-buddypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'User Field', 'pmpro-buddypress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $xprofile_fields as $xprofile_id => $xprofile_name ) { ?>
					<?php $selected_meta_key = isset( $map[ $xprofile_id ] ) ? $map[ $xprofile_id ] : ''; ?>
					<tr<?php echo empty( $selected_meta_key ) ? ' class="pmpro_gray"' : ''; ?>>
						<td><strong><?php echo esc_html( $xprofile_name ); ?></strong></td>
						<td>
							<select class="pmpro-bp-xprofile-map-select" name="pmpro_bp_xprofile_map[<?php echo esc_attr( $xprofile_id ); ?>]">
								<option value=""><?php esc_html_e( '— Not mapped —', 'pmpro-buddypress' ); ?></option>
								<?php foreach ( $user_fields as $meta_key => $user_field ) { ?>
									<option value="<?php echo esc_attr( $meta_key ); ?>" <?php selected( $selected_meta_key, $meta_key ); ?>>
										<?php echo esc_html( ! empty( $user_field->label ) ? $user_field->label : $meta_key ); ?>
									</option>
								<?php } ?>
							</select>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<script>
		( function() {
			var selects = document.querySelectorAll( '.pmpro-bp-xprofile-map-select' );
			if ( ! selects.length ) {
				return;
			}

			var inUseSuffix = <?php echo wp_json_encode( ' ' . __( '(in use)', 'pmpro-buddypress' ) ); ?>;

			// Remember each option's original label so we can toggle the suffix.
			selects.forEach( function( select ) {
				Array.prototype.forEach.call( select.options, function( option ) {
					option.setAttribute( 'data-base-label', option.textContent.trim() );
				} );
			} );

			// Disable any user field already chosen in a different dropdown (strict 1:1).
			function refresh() {
				var chosen = {};
				selects.forEach( function( select ) {
					if ( select.value ) {
						chosen[ select.value ] = select;
					}
				} );

				selects.forEach( function( select ) {
					Array.prototype.forEach.call( select.options, function( option ) {
						if ( '' === option.value ) {
							return;
						}
						var owner = chosen[ option.value ];
						var taken = owner && owner !== select;
						option.disabled = !! taken;
						option.textContent = option.getAttribute( 'data-base-label' ) + ( taken ? inUseSuffix : '' );
					} );
				} );
			}

			selects.forEach( function( select ) {
				select.addEventListener( 'change', refresh );
			} );

			refresh();
		}() );
		</script>
	<?php } ?>
	<?php
}
