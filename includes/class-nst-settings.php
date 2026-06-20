<?php
/**
 * Settings schema — defaults and sanitisation.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

class NST_Settings {

	/**
	 * Default values for every setting.
	 * These mirror exactly what the configurator exports.
	 */
	public static function defaults(): array {
		return [
			// ── Accent color ───────────────────────────────────────
			'accent_color'           => '#1a1a1a',

			// ── Mini cart drawer ───────────────────────────────────
			'cart_width'             => '420px',
			'cart_border_color'      => '#f0f0f0',
			'cart_header_spacing'    => '1.5rem',
			'cart_footer_spacing'    => '1.5rem',
			'cart_backdrop_opacity'  => '0.25',
			'cart_transition_speed'  => '320ms',

			// ── Floating cart bubble ───────────────────────────────
			'bubble_enabled'         => true,
			'bubble_width'           => '56px',
			'bubble_height'          => '56px',
			'bubble_radius'          => '999px',
			'bubble_bottom'          => '28px',
			'bubble_right'           => '28px',

			// ── Checkout button (cart) ─────────────────────────────
			'cart_btn_radius'        => '9px',

			// ── Mini cart features ─────────────────────────────────
			'show_qty_controls'      => false,
			'show_coupon'            => true,

			// ── Checkout page inputs ───────────────────────────────
			'input_border_color'     => '#e5e5e5',
			'input_border_hover'     => '#d0d0d0',
			'input_radius'           => '8px',
			'input_bg'               => '#ffffff',
			'input_shadow'           => 'none',
			'input_font_size'        => '0.9375rem',
			'input_color'            => '#1a1a1a',
			'input_placeholder'      => '#c0c0c0',
			'input_label_color'      => '#4a4a4a',
			'input_label_weight'     => '600',
			'form_row_spacing'       => '1.125rem',
			'choice_radius'          => '8px',

			// ── Account dashboard ──────────────────────────────────
			'dashboard_bg'           => '#f7f7f5',
			'dashboard_sidebar_bg'   => '#ffffff',
			'dashboard_sidebar_w'    => '300px',
			'dashboard_card_radius'  => '10px',
			'dashboard_card_border'  => '#f0f0f0',
			'dashboard_card_shadow'  => 'none',
			'dashboard_row_hover'    => '#fafafa',
			'dashboard_row_border'   => '#f0f0f0',
			'tab_active_bg'          => '#f4f4f4',
			'tab_active_color'       => '#1a1a1a',

			// ── Focus ring ─────────────────────────────────────────
			'focus_ring_width'       => '2px',

			// ── Typography ─────────────────────────────────────────
			'font_family'            => '',   // empty = inherit theme font

			// ── Default avatar ────────────────────────────────────────────────────────
			'default_avatar_id'      => 0,    // WP media attachment ID
			'default_avatar_preset'  => '',   // Preset key e.g. 'blue'
		];
	}

	/**
	 * Get all settings, merged with defaults so new keys always exist.
	 */
	public static function get(): array {
		$saved = get_option( NST_OPTION, [] );
		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Sanitise and save POSTed settings.
	 */
	public static function save( array $raw ): bool {
		$clean  = [];
		$defs   = self::defaults();

		// Hex color fields.
		$hex_fields = [
			'accent_color', 'cart_border_color', 'input_border_color',
			'input_border_hover', 'input_bg', 'input_color',
			'input_placeholder', 'input_label_color',
			'dashboard_bg', 'dashboard_sidebar_bg',
			'dashboard_card_border', 'dashboard_row_hover', 'dashboard_row_border',
			'tab_active_color',
		];

		foreach ( $hex_fields as $key ) {
			$val = isset( $raw[ $key ] ) ? sanitize_hex_color( $raw[ $key ] ) : null;
			$clean[ $key ] = $val ?: $defs[ $key ];
		}

		// CSS dimension fields (px / rem / em / %).
		$dim_fields = [
			'cart_width', 'cart_header_spacing', 'cart_footer_spacing',
			'cart_transition_speed', 'cart_btn_radius',
			'bubble_width', 'bubble_height', 'bubble_radius',
			'bubble_bottom', 'bubble_right',
			'input_radius', 'input_font_size', 'form_row_spacing', 'choice_radius',
			'dashboard_sidebar_w', 'dashboard_card_radius',
			'focus_ring_width',
		];

		foreach ( $dim_fields as $key ) {
			$val = isset( $raw[ $key ] ) ? sanitize_text_field( $raw[ $key ] ) : '';
			// Allow only safe CSS dimension values.
			$clean[ $key ] = preg_match( '/^[\d.]+(px|rem|em|%|ms|s|vh|vw)$/', $val )
				? $val
				: $defs[ $key ];
		}

		// Numeric/decimal fields stored as text.
		$decimal_fields = [ 'cart_backdrop_opacity' ];
		foreach ( $decimal_fields as $key ) {
			$val = isset( $raw[ $key ] ) ? (float) $raw[ $key ] : (float) $defs[ $key ];
			$clean[ $key ] = (string) max( 0, min( 1, $val ) );
		}

		// Font weight (numeric).
		$clean['input_label_weight'] = in_array( (string) ( $raw['input_label_weight'] ?? '' ), [ '400', '500', '600', '700' ], true )
			? (string) $raw['input_label_weight']
			: $defs['input_label_weight'];

		// Box-shadow fields — only allow 'none' or a safe value.
		foreach ( [ 'input_shadow', 'dashboard_card_shadow' ] as $key ) {
			$val = sanitize_text_field( $raw[ $key ] ?? '' );
			$clean[ $key ] = ( 'none' === $val || preg_match( '/^[\d\s\-\(\),\.#a-zA-Z%]+$/', $val ) )
				? $val
				: $defs[ $key ];
		}

		// Bool toggles.
		foreach ( [ 'bubble_enabled', 'show_qty_controls', 'show_coupon' ] as $key ) {
			$clean[ $key ] = ! empty( $raw[ $key ] );
		}

		// Avatar — managed via AJAX, not the main form POST.
		// Read from existing saved option to preserve across Save Changes clicks.
		$existing = get_option( NST_OPTION, [] );
		$clean['default_avatar_id']     = isset( $existing['default_avatar_id'] )     ? (int) $existing['default_avatar_id'] : 0;
		$clean['default_avatar_preset'] = isset( $existing['default_avatar_preset'] ) ? sanitize_key( $existing['default_avatar_preset'] ) : '';

		// Font family — sanitise text, allow empty.
		$clean['font_family'] = sanitize_text_field( $raw['font_family'] ?? '' );

		// Tab active background — hex or rgba.
		$tab_bg = sanitize_text_field( $raw['tab_active_bg'] ?? '' );
		$clean['tab_active_bg'] = sanitize_hex_color( $tab_bg ) ?: $defs['tab_active_bg'];

		return update_option( NST_OPTION, $clean );
	}

	/**
	 * Reset to factory defaults.
	 */
	public static function reset(): void {
		update_option( NST_OPTION, self::defaults() );
	}
}
