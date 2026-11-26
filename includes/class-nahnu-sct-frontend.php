<?php
/**
 * Frontend — outputs the compiled CSS on the front end.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the generated CSS on the front end, after SureCart's own styles.
 */
class Nahnu_SCT_Frontend {

	/**
	 * Hook the stylesheet.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'output_css' ), 999 );
	}

	/**
	 * Build CSS from current settings and inject it.
	 * Priority 999 ensures we load after SureCart's own stylesheets.
	 * We do NOT gate on SureCart being active — the CSS is harmless when
	 * SureCart isn't present, and gating was causing silent failures.
	 */
	public function output_css(): void {
		$settings = Nahnu_SCT_Settings::get();
		$builder  = new Nahnu_SCT_CSS_Builder( $settings );
		$css      = $builder->build();

		// Register our own handle so we're never dependent on another
		// plugin's handle being enqueued. Inline styles attached to a
		// registered (not necessarily enqueued) handle still output.
		wp_register_style( 'nahnu-sct-styles', false, array(), NAHNU_SCT_VERSION );
		wp_enqueue_style( 'nahnu-sct-styles' );
		// Every value in $css was validated by Nahnu_SCT_Settings::sanitize() (hex colours,
		// CSS lengths, allow-listed shadows and weights, a strict font-family list), both on
		// save and again on read. wp_strip_all_tags() is the final guard against a stray tag
		// ending the <style> element.
		wp_add_inline_style( 'nahnu-sct-styles', wp_strip_all_tags( $css ) );
	}
}
