<?php
/**
 * Frontend — outputs the compiled CSS on the front end.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

class NST_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'output_css' ], 999 );
	}

	/**
	 * Build CSS from current settings and inject it.
	 * Priority 999 ensures we load after SureCart's own stylesheets.
	 * We do NOT gate on SureCart being active — the CSS is harmless when
	 * SureCart isn't present, and gating was causing silent failures.
	 */
	public function output_css(): void {
		$settings = NST_Settings::get();
		$builder  = new NST_CSS_Builder( $settings );
		$css      = $builder->build();

		// Register our own handle so we're never dependent on another
		// plugin's handle being enqueued. Inline styles attached to a
		// registered (not necessarily enqueued) handle still output.
		wp_register_style( 'nst-styles', false, [], NST_VERSION );
		wp_enqueue_style( 'nst-styles' );
		wp_add_inline_style( 'nst-styles', $css );
	}

}
