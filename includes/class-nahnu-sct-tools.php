<?php
/**
 * Tools — live preview endpoint, export / import, and theme presets.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Live preview, presets, export and import.
 */
class Nahnu_SCT_Tools {

	const NONCE = 'nahnu_sct_tools';

	/**
	 * Register the AJAX and admin-post handlers.
	 */
	public function __construct() {
		add_action( 'wp_ajax_nahnu_sct_preview_css', array( $this, 'ajax_preview_css' ) );
		add_action( 'wp_ajax_nahnu_sct_export', array( $this, 'ajax_export' ) );
		add_action( 'admin_post_nahnu_sct_import', array( $this, 'handle_import' ) );
	}

	// ── Presets ──────────────────────────────────────────────────────────────

	/**
	 * Named, complete theme presets. Each is a partial settings array merged
	 * over defaults on apply. Keys must exist in Nahnu_SCT_Settings::defaults().
	 */
	public static function presets(): array {
		return array(
			'obsidian' => array(
				'label'    => 'Obsidian',
				'tagline'  => 'Near-black accent, flat surfaces, pill badges. The default.',
				'swatches' => array( '#1a1a1a', '#f7f7f5', '#e5e5e5' ),
				'values'   => Nahnu_SCT_Settings::defaults(),
			),
			'soft'     => array(
				'label'    => 'Soft',
				'tagline'  => 'Rounded corners, warm greys, gentle shadows.',
				'swatches' => array( '#3b5bdb', '#faf9f7', '#e8e4de' ),
				'values'   => array(
					'accent_color'          => '#3b5bdb',
					'cart_border_color'     => '#ece9e4',
					'cart_width'            => '440px',
					'cart_btn_radius'       => '14px',
					'bubble_radius'         => '18px',
					'input_border_color'    => '#e3dfd8',
					'input_border_hover'    => '#cfcac2',
					'input_radius'          => '12px',
					'input_bg'              => '#fffdfa',
					'input_shadow'          => 'rgba(0,0,0,.05) 0px 1px 2px 0px',
					'input_label_color'     => '#5a564f',
					'input_label_weight'    => '500',
					'choice_radius'         => '14px',
					'form_row_spacing'      => '1.25rem',
					'dashboard_bg'          => '#faf9f7',
					'dashboard_sidebar_bg'  => '#ffffff',
					'dashboard_card_radius' => '16px',
					'dashboard_card_border' => '#ece9e4',
					'dashboard_card_shadow' => '0 1px 2px rgba(0,0,0,0.06)',
					'dashboard_row_hover'   => '#faf9f7',
					'dashboard_row_border'  => '#f0ede8',
					'tab_active_bg'         => '#eef1fb',
					'tab_active_color'      => '#3b5bdb',
				),
			),
			'sharp'    => array(
				'label'    => 'Sharp',
				'tagline'  => 'Square corners, hairline borders, high contrast.',
				'swatches' => array( '#000000', '#ffffff', '#d0d0d0' ),
				'values'   => array(
					'accent_color'          => '#000000',
					'cart_border_color'     => '#e0e0e0',
					'cart_width'            => '400px',
					'cart_btn_radius'       => '0px',
					'bubble_radius'         => '0px',
					'bubble_width'          => '52px',
					'bubble_height'         => '52px',
					'input_border_color'    => '#c8c8c8',
					'input_border_hover'    => '#000000',
					'input_radius'          => '0px',
					'input_bg'              => '#ffffff',
					'input_shadow'          => 'none',
					'input_label_color'     => '#000000',
					'input_label_weight'    => '700',
					'choice_radius'         => '0px',
					'form_row_spacing'      => '1rem',
					'dashboard_bg'          => '#ffffff',
					'dashboard_sidebar_bg'  => '#f4f4f4',
					'dashboard_card_radius' => '0px',
					'dashboard_card_border' => '#d0d0d0',
					'dashboard_card_shadow' => 'none',
					'dashboard_row_hover'   => '#f6f6f6',
					'dashboard_row_border'  => '#e4e4e4',
					'tab_active_bg'         => '#000000',
					'tab_active_color'      => '#ffffff',
				),
			),
			'sage'     => array(
				'label'    => 'Sage',
				'tagline'  => 'Calm green accent on off-white, for wellness and fitness brands.',
				'swatches' => array( '#2f7a5b', '#f6f8f5', '#dfe6e0' ),
				'values'   => array(
					'accent_color'          => '#2f7a5b',
					'cart_border_color'     => '#e5ebe6',
					'cart_btn_radius'       => '10px',
					'bubble_radius'         => '999px',
					'input_border_color'    => '#d9e2db',
					'input_border_hover'    => '#b9c9bd',
					'input_radius'          => '10px',
					'input_bg'              => '#ffffff',
					'input_label_color'     => '#3f4a43',
					'choice_radius'         => '10px',
					'dashboard_bg'          => '#f6f8f5',
					'dashboard_sidebar_bg'  => '#ffffff',
					'dashboard_card_radius' => '12px',
					'dashboard_card_border' => '#e5ebe6',
					'dashboard_row_hover'   => '#f6f8f5',
					'dashboard_row_border'  => '#edf1ed',
					'tab_active_bg'         => '#e6f0ea',
					'tab_active_color'      => '#2f7a5b',
				),
			),
		);
	}

	// ── AJAX: live preview ───────────────────────────────────────────────────

	/**
	 * Takes the settings form (serialised) and returns the CSS the builder
	 * would generate, without saving anything.
	 */
	public function ajax_preview_css(): void {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}

		$raw = array();
		if ( isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ) {
			$raw = map_deep( wp_unslash( $_POST['settings'] ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- map_deep() sanitizes every leaf.
		}
		$settings = Nahnu_SCT_Settings::sanitize( $raw );
		$css      = ( new Nahnu_SCT_CSS_Builder( $settings ) )->build();

		wp_send_json_success( array( 'css' => $css ) );
	}

	// ── AJAX: export ─────────────────────────────────────────────────────────

	/**
	 * AJAX: return the portable settings as JSON for download.
	 */
	public function ajax_export(): void {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}

		$settings = Nahnu_SCT_Settings::get();
		$out      = array();
		foreach ( Nahnu_SCT_Settings::portable_keys() as $k ) {
			$out[ $k ] = $settings[ $k ];
		}

		wp_send_json_success(
			array(
				'filename' => 'nahnu-surecart-theme-' . sanitize_title( get_bloginfo( 'name' ) ) . '-' . gmdate( 'Y-m-d' ) . '.json',
				'json'     => array(
					'plugin'   => 'nahnu-surecart-theme',
					'version'  => NAHNU_SCT_VERSION,
					'exported' => gmdate( 'c' ),
					'site'     => home_url(),
					'settings' => $out,
				),
			)
		);
	}

	// ── Import (form POST) ───────────────────────────────────────────────────

	/**
	 * Admin-post: import settings from an uploaded or pasted JSON file.
	 */
	public function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'nahnu-surecart-theme' ) );
		}
		check_admin_referer( 'nahnu_sct_import' );

		$json = '';
		if (
			isset( $_FILES['nst_import_file']['tmp_name'], $_FILES['nst_import_file']['error'] )
			&& UPLOAD_ERR_OK === (int) $_FILES['nst_import_file']['error']
		) {
			$tmp = sanitize_text_field( wp_unslash( $_FILES['nst_import_file']['tmp_name'] ) );
			if ( is_uploaded_file( $tmp ) ) {
				// Reading PHP's own upload temp file; WP_Filesystem is not needed for a request-scoped temp file.
				$json = (string) file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			}
		} elseif ( ! empty( $_POST['nst_import_text'] ) ) {
			$json = sanitize_textarea_field( wp_unslash( $_POST['nst_import_text'] ) );
		}

		$data     = json_decode( (string) $json, true );
		$incoming = is_array( $data ) ? ( $data['settings'] ?? $data ) : null;

		if ( ! is_array( $incoming ) || empty( $incoming ) ) {
			$this->redirect( array( 'import' => 'invalid' ) );
		}

		// Only accept known, portable keys; sanitize() does the rest.
		$allowed = array_flip( Nahnu_SCT_Settings::portable_keys() );
		$raw     = array_intersect_key( $incoming, $allowed );

		// Booleans arrive as true/false in JSON; sanitize() treats "" as off.
		foreach ( array( 'bubble_enabled', 'show_qty_controls', 'show_coupon' ) as $b ) {
			if ( array_key_exists( $b, $raw ) ) {
				$raw[ $b ] = $raw[ $b ] ? '1' : '';
			}
		}

		// Keys missing from the file keep their current value rather than
		// falling back to defaults.
		$current = Nahnu_SCT_Settings::get();
		$clean   = Nahnu_SCT_Settings::sanitize( array_merge( $current, $raw ), $current );
		update_option( NAHNU_SCT_OPTION, $clean );

		$this->redirect( array( 'import' => 'ok' ) );
	}

	/**
	 * Redirect back to the Tools tab.
	 *
	 * @param array $args Extra query args.
	 */
	private function redirect( array $args ): void {
		wp_safe_redirect(
			add_query_arg(
				array_merge(
					array(
						'page' => 'nahnu-sct-settings',
						'tab'  => 'tools',
					),
					$args
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	// ── Data for the admin page / JS ─────────────────────────────────────────

	/**
	 * Data localised to admin.js.
	 *
	 * @return array
	 */
	public static function js_data(): array {
		$presets = array();
		foreach ( self::presets() as $key => $p ) {
			// Full value set so JS can fill every field deterministically.
			$presets[ $key ] = array_merge( Nahnu_SCT_Settings::defaults(), $p['values'] );
		}
		return array(
			'nonce'   => wp_create_nonce( self::NONCE ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'presets' => $presets,
			'i18n'    => array(
				'unsaved' => __( 'Unsaved changes', 'nahnu-surecart-theme' ),
			),
		);
	}

	/**
	 * Render the preview document as a string for the iframe srcdoc.
	 *
	 * @param array $settings Sanitized settings.
	 */
	public static function preview_doc( array $settings ): string {
		$preview_avatar_url = '';
		if ( ! empty( $settings['default_avatar_id'] ) ) {
			$preview_avatar_url = Nahnu_SCT_Avatar::attachment_url( (int) $settings['default_avatar_id'] );
		} elseif ( ! empty( $settings['default_avatar_preset'] ) ) {
			$preview_avatar_url = Nahnu_SCT_Avatar::preset_url( $settings['default_avatar_preset'] );
		}
		ob_start();
		include NAHNU_SCT_DIR . 'includes/views/preview.php';
		return (string) ob_get_clean();
	}

	// ── Tools tab markup ─────────────────────────────────────────────────────

	/** Presets, export and the import heading (inside the main form). */
	public static function render_tools_tab(): void {
		$import = isset( $_GET['import'] ) ? sanitize_key( wp_unslash( $_GET['import'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only result flag set by our own redirect.
		?>
		<div class="nst-panel-head">
			<h2><?php esc_html_e( 'Presets & Tools', 'nahnu-surecart-theme' ); ?></h2>
			<p><?php esc_html_e( 'Start from a preset, or move your settings between sites.', 'nahnu-surecart-theme' ); ?></p>
		</div>

		<?php if ( 'ok' === $import ) : ?>
			<div class="nst-notice nst-notice--success"><?php esc_html_e( 'Settings imported and saved.', 'nahnu-surecart-theme' ); ?></div>
		<?php elseif ( 'invalid' === $import ) : ?>
			<div class="nst-notice nst-notice--error"><?php esc_html_e( 'That file did not contain valid theme settings.', 'nahnu-surecart-theme' ); ?></div>
		<?php endif; ?>

		<section class="nst-section">
			<header class="nst-section-head">
				<h2><?php esc_html_e( 'Presets', 'nahnu-surecart-theme' ); ?></h2>
				<p><?php esc_html_e( 'Fills every setting from a starting point. The preview updates at once; nothing is saved until you click Save changes.', 'nahnu-surecart-theme' ); ?></p>
			</header>
			<div class="nst-presets">
				<?php foreach ( self::presets() as $key => $p ) : ?>
					<div class="nst-preset" role="button" tabindex="0" data-preset="<?php echo esc_attr( $key ); ?>">
						<div class="nst-preset-swatches">
							<?php foreach ( $p['swatches'] as $sw ) : ?>
								<span style="background:<?php echo esc_attr( $sw ); ?>"></span>
							<?php endforeach; ?>
						</div>
						<div class="nst-preset-label"><?php echo esc_html( $p['label'] ); ?></div>
						<div class="nst-preset-tagline"><?php echo esc_html( $p['tagline'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="nst-section">
			<header class="nst-section-head">
				<h2><?php esc_html_e( 'Export', 'nahnu-surecart-theme' ); ?></h2>
				<p><?php esc_html_e( 'Download the saved settings as JSON to reuse on another site. A Media Library avatar is site-specific and is not included; a preset avatar is.', 'nahnu-surecart-theme' ); ?></p>
			</header>
			<div class="nst-section-body">
				<div id="nst-export" class="nst-av-btn" role="button" tabindex="0">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
					<?php esc_html_e( 'Download settings JSON', 'nahnu-surecart-theme' ); ?>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * The import form must not nest inside the main settings form, so the
	 * admin page renders it after </form>.
	 */
	public static function render_import_form(): void {
		?>
		<section class="nst-section">
		<header class="nst-section-head">
			<h2><?php esc_html_e( 'Import', 'nahnu-surecart-theme' ); ?></h2>
			<p><?php esc_html_e( 'Upload a JSON file exported from this plugin, or paste its contents. Importing saves immediately and replaces the matching settings.', 'nahnu-surecart-theme' ); ?></p>
		</header>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="nst-import-form nst-section-body" id="nst-import-form">
			<?php wp_nonce_field( 'nahnu_sct_import' ); ?>
			<input type="hidden" name="action" value="nahnu_sct_import">
			<div class="nst-import-row">
				<label class="nst-av-btn" for="nst_import_file">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
					<?php esc_html_e( 'Choose JSON file', 'nahnu-surecart-theme' ); ?>
				</label>
				<input type="file" id="nst_import_file" name="nst_import_file" accept="application/json,.json" hidden>
				<span id="nst-import-filename" class="nst-hint"><?php esc_html_e( 'No file chosen', 'nahnu-surecart-theme' ); ?></span>
			</div>
			<textarea name="nst_import_text" class="nst-input nst-import-text" rows="5" placeholder='{"settings": { ... }}'></textarea>
			<div class="nst-import-actions">
				<button type="submit" class="nst-btn nst-btn--primary" onclick="return confirm('<?php echo esc_js( __( 'Import and overwrite the current settings?', 'nahnu-surecart-theme' ) ); ?>')">
					<?php esc_html_e( 'Import & save', 'nahnu-surecart-theme' ); ?>
				</button>
			</div>
		</form>
		</section>
		<?php
	}
}
