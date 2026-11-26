<?php
/**
 * Settings schema — defaults and sanitisation.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings schema, storage and sanitisation.
 */
class Nahnu_SCT_Settings {

	/**
	 * Default values for every setting.
	 * These mirror exactly what the configurator exports.
	 */
	public static function defaults(): array {
		return array(
			// ── Accent color ───────────────────────────────────────
			'accent_color'          => '#1a1a1a',

			// ── Mini cart drawer ───────────────────────────────────
			'cart_width'            => '420px',
			'cart_border_color'     => '#f0f0f0',
			'cart_header_spacing'   => '1.5rem',
			'cart_footer_spacing'   => '1.5rem',
			'cart_backdrop_opacity' => '0.25',
			'cart_transition_speed' => '320ms',

			// ── Floating cart bubble ───────────────────────────────
			'bubble_enabled'        => true,
			'bubble_width'          => '56px',
			'bubble_height'         => '56px',
			'bubble_radius'         => '999px',
			'bubble_bottom'         => '28px',
			'bubble_right'          => '28px',

			// ── Checkout button (cart) ─────────────────────────────
			'cart_btn_radius'       => '9px',

			// ── Mini cart features ─────────────────────────────────
			'show_qty_controls'     => false,
			'show_coupon'           => true,

			// ── Checkout page inputs ───────────────────────────────
			'input_border_color'    => '#e5e5e5',
			'input_border_hover'    => '#d0d0d0',
			'input_radius'          => '8px',
			'input_bg'              => '#ffffff',
			'input_shadow'          => 'none',
			'input_font_size'       => '0.9375rem',
			'input_color'           => '#1a1a1a',
			'input_placeholder'     => '#c0c0c0',
			'input_label_color'     => '#4a4a4a',
			'input_label_weight'    => '600',
			'form_row_spacing'      => '1.125rem',
			'choice_radius'         => '8px',

			// ── Account dashboard ──────────────────────────────────
			'dashboard_bg'          => '#f7f7f5',
			'dashboard_sidebar_bg'  => '#ffffff',
			'dashboard_sidebar_w'   => '300px',
			'dashboard_card_radius' => '10px',
			'dashboard_card_border' => '#f0f0f0',
			'dashboard_card_shadow' => 'none',
			'dashboard_row_hover'   => '#fafafa',
			'dashboard_row_border'  => '#f0f0f0',
			'tab_active_bg'         => '#f4f4f4',
			'tab_active_color'      => '#1a1a1a',

			// ── Focus ring ─────────────────────────────────────────
			'focus_ring_width'      => '2px',

			// ── Typography ─────────────────────────────────────────
			'font_family'           => '',   // Empty = inherit theme font.

			// ── Default avatar ────────────────────────────────────────────────────────
			'default_avatar_id'     => 0,    // WP media attachment ID.
			'default_avatar_preset' => '',   // Preset key, e.g. 'blue'.
		);
	}

	/**
	 * Get all settings, merged with defaults so new keys always exist.
	 */
	public static function get(): array {
		$saved = get_option( NAHNU_SCT_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		// Validate again on every read ("escape late"): the stored option is
		// never trusted, so nothing unvalidated can reach the generated CSS.
		return self::sanitize( wp_parse_args( $saved, self::defaults() ), $saved );
	}

	/**
	 * Validate a font-family list and return it normalised, or '' if invalid.
	 *
	 * Accepts a comma-separated list of up to eight families. Each family is a
	 * quoted or bare name made of letters, digits, spaces, hyphens and
	 * underscores, or one of the CSS generic keywords. Anything else (braces,
	 * semicolons, parentheses, slashes, colons, angle brackets, backslashes…)
	 * rejects the whole value. Names are re-emitted in single quotes, so the
	 * result is always a plain, inert font-family value.
	 *
	 * @param string $value Raw value.
	 * @return string Safe value, or an empty string.
	 */
	public static function sanitize_font_family( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || strlen( $value ) > 200 ) {
			return '';
		}
		$generic = array( 'serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui', 'ui-serif', 'ui-sans-serif', 'ui-monospace', 'ui-rounded', 'inherit' );
		$parts   = explode( ',', $value );
		if ( count( $parts ) > 8 ) {
			return '';
		}
		$out = array();
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( preg_match( '/^([\'"])(.*)\1$/u', $part, $m ) ) {
				$part = trim( $m[2] );
			}
			if ( '' === $part || ! preg_match( '/^[\p{L}\p{N} _-]+$/u', $part ) ) {
				return '';
			}
			$out[] = in_array( strtolower( $part ), $generic, true ) ? strtolower( $part ) : "'" . $part . "'";
		}
		return implode( ', ', $out );
	}

	/**
	 * Sanitise and save POSTed settings.
	 *
	 * @param array $raw Unsanitised key => value pairs.
	 */
	public static function save( array $raw ): bool {
		return update_option( NAHNU_SCT_OPTION, self::sanitize( $raw ) );
	}

	/**
	 * Sanitise a raw settings array (form POST, imported JSON, preview request)
	 * into a complete, safe settings array. Pure — nothing is written.
	 *
	 * @param array      $raw      Untrusted key => value pairs.
	 * @param array|null $existing Stored settings to carry AJAX-managed keys
	 *                             (avatar) from. Defaults to the saved option.
	 */
	public static function sanitize( array $raw, ?array $existing = null ): array {
		$clean = array();
		$defs  = self::defaults();

		// Hex color fields.
		$hex_fields = array(
			'accent_color',
			'cart_border_color',
			'input_border_color',
			'input_border_hover',
			'input_bg',
			'input_color',
			'input_placeholder',
			'input_label_color',
			'dashboard_bg',
			'dashboard_sidebar_bg',
			'dashboard_card_border',
			'dashboard_row_hover',
			'dashboard_row_border',
			'tab_active_color',
		);

		foreach ( $hex_fields as $key ) {
			$val           = isset( $raw[ $key ] ) ? sanitize_hex_color( $raw[ $key ] ) : null;
			$clean[ $key ] = ( is_string( $val ) && '' !== $val ) ? $val : $defs[ $key ];
		}

		// CSS dimension fields (px / rem / em / %).
		$dim_fields = array(
			'cart_width',
			'cart_header_spacing',
			'cart_footer_spacing',
			'cart_transition_speed',
			'cart_btn_radius',
			'bubble_width',
			'bubble_height',
			'bubble_radius',
			'bubble_bottom',
			'bubble_right',
			'input_radius',
			'input_font_size',
			'form_row_spacing',
			'choice_radius',
			'dashboard_sidebar_w',
			'dashboard_card_radius',
			'focus_ring_width',
		);

		foreach ( $dim_fields as $key ) {
			$val = isset( $raw[ $key ] ) ? sanitize_text_field( $raw[ $key ] ) : '';
			// Allow only safe CSS dimension values.
			$clean[ $key ] = preg_match( '/^[\d.]+(px|rem|em|%|ms|s|vh|vw)$/', $val )
				? $val
				: $defs[ $key ];
		}

		// Numeric/decimal fields stored as text.
		$decimal_fields = array( 'cart_backdrop_opacity' );
		foreach ( $decimal_fields as $key ) {
			$val           = isset( $raw[ $key ] ) ? (float) $raw[ $key ] : (float) $defs[ $key ];
			$clean[ $key ] = (string) max( 0, min( 1, $val ) );
		}

		// Font weight (numeric).
		$clean['input_label_weight'] = in_array( (string) ( $raw['input_label_weight'] ?? '' ), array( '400', '500', '600', '700' ), true )
			? (string) $raw['input_label_weight']
			: $defs['input_label_weight'];

		// Box-shadow fields — only allow 'none' or a safe value.
		foreach ( array( 'input_shadow', 'dashboard_card_shadow' ) as $key ) {
			$val           = sanitize_text_field( $raw[ $key ] ?? '' );
			$clean[ $key ] = ( 'none' === $val || ( preg_match( '/^[\d\s\-\(\),\.#a-zA-Z%]+$/', $val ) && ! preg_match( '/url|expression|import|var\(/i', $val ) ) )
				? $val
				: $defs[ $key ];
		}

		// Bool toggles.
		foreach ( array( 'bubble_enabled', 'show_qty_controls', 'show_coupon' ) as $key ) {
			$clean[ $key ] = ! empty( $raw[ $key ] );
		}

		// Avatar — managed via AJAX, not the main form POST, so carry the stored
		// values forward. An import may legitimately supply a preset key.
		if ( null === $existing ) {
			$existing = (array) get_option( NAHNU_SCT_OPTION, array() );
		}
		$clean['default_avatar_id']     = isset( $existing['default_avatar_id'] ) ? (int) $existing['default_avatar_id'] : 0;
		$preset                         = $raw['default_avatar_preset'] ?? $existing['default_avatar_preset'] ?? '';
		$clean['default_avatar_preset'] = sanitize_key( (string) $preset );

		// Font family — strict allow-list (see sanitize_font_family()); empty inherits the theme font.
		$clean['font_family'] = self::sanitize_font_family( sanitize_text_field( (string) ( $raw['font_family'] ?? '' ) ) );

		// Tab active background — hex only.
		$tab_bg                 = sanitize_text_field( $raw['tab_active_bg'] ?? '' );
		$tab_bg                 = sanitize_hex_color( $tab_bg );
		$clean['tab_active_bg'] = ( is_string( $tab_bg ) && '' !== $tab_bg ) ? $tab_bg : $defs['tab_active_bg'];

		return $clean;
	}

	/**
	 * Keys that are safe to export/import between sites.
	 * The media attachment ID is site-specific and is excluded.
	 */
	public static function portable_keys(): array {
		return array_values( array_diff( array_keys( self::defaults() ), array( 'default_avatar_id' ) ) );
	}

	/**
	 * Reset to factory defaults.
	 */
	public static function reset(): void {
		update_option( NAHNU_SCT_OPTION, self::defaults() );
	}
}
