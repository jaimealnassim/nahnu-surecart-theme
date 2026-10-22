<?php
/**
 * Default Avatar
 *
 * Replaces the Gravatar / initial-letter fallback with a custom image.
 *
 * HOW IT WORKS:
 * - Preset PNGs ship with the plugin in assets/avatars/.
 * - Their URLs are real https:// URLs (NAHNU_SCT_URL . 'assets/avatars/NAME.png').
 * - We hook 'pre_get_avatar_data' at priority 5 and set $args['url'].
 * - WordPress skips Gravatar and returns our URL.
 * - SureCart does esc_url() on the result — passes through fine for https://.
 * - sc-avatar gets a real image URL and renders it.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default profile photo for customers without a Gravatar.
 */
class Nahnu_SCT_Avatar {

	/**
	 * Hook into the avatar filter and the admin AJAX endpoints.
	 */
	public function __construct() {
		add_filter( 'pre_get_avatar_data', array( $this, 'inject_avatar_data' ), 5, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 20 );
		add_action( 'wp_ajax_nahnu_sct_save_avatar', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_nahnu_sct_remove_avatar', array( $this, 'ajax_remove' ) );
		add_action( 'wp_ajax_nahnu_sct_save_preset', array( $this, 'ajax_save_preset' ) );
	}

	// ── Presets ───────────────────────────────────────────────────────────────

	/**
	 * Bundled preset avatars (PNG files in assets/avatars/).
	 *
	 * @return array<string, array{label: string, file: string}>
	 */
	public static function presets(): array {
		return array(
			'light'  => array(
				'label' => 'Light',
				'file'  => 'light.png',
			),
			'dark'   => array(
				'label' => 'Dark',
				'file'  => 'dark.png',
			),
			'blue'   => array(
				'label' => 'Blue',
				'file'  => 'blue.png',
			),
			'green'  => array(
				'label' => 'Green',
				'file'  => 'green.png',
			),
			'purple' => array(
				'label' => 'Purple',
				'file'  => 'purple.png',
			),
			'orange' => array(
				'label' => 'Orange',
				'file'  => 'orange.png',
			),
		);
	}

	/**
	 * URL of an attachment at the given size, falling back to the original file.
	 *
	 * @param int          $attachment_id Attachment ID.
	 * @param string|int[] $size          Image size; defaults to 80×80.
	 * @return string Empty string when the attachment has no file.
	 */
	public static function attachment_url( int $attachment_id, $size = array( 80, 80 ) ): string {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( ! $url ) {
			$url = wp_get_attachment_url( $attachment_id );
		}
		return is_string( $url ) ? $url : '';
	}

	/**
	 * Public URL of a preset image.
	 *
	 * @param string $key Preset key.
	 */
	public static function preset_url( string $key ): string {
		$presets = self::presets();
		if ( ! isset( $presets[ $key ] ) ) {
			return '';
		}
		return NAHNU_SCT_URL . 'assets/avatars/' . $presets[ $key ]['file'];
	}

	// ── Front-end filter ──────────────────────────────────────────────────────

	/**
	 * Swap in the chosen default image when the user has no Gravatar.
	 *
	 * @param array $args        Avatar args from get_avatar_data().
	 * @param mixed $id_or_email Unused; required by the filter signature.
	 * @return array
	 */
	public function inject_avatar_data( $args, $id_or_email ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Filter signature.
		if ( ! is_array( $args ) || ! empty( $args['force_default'] ) ) {
			return (array) $args;
		}

		$url = $this->get_avatar_url();
		if ( ! $url ) {
			return $args;
		}

		$args['url']          = $url;
		$args['found_avatar'] = true;
		return $args;
	}

	/**
	 * Get the configured avatar URL.
	 * Checks: media library attachment → preset PNG → empty.
	 */
	private function get_avatar_url(): string {
		$settings = Nahnu_SCT_Settings::get();

		// Media library image.
		$attachment_id = (int) ( $settings['default_avatar_id'] ?? 0 );
		if ( $attachment_id ) {
			$url = self::attachment_url( $attachment_id, 'full' );
			if ( '' !== $url ) {
				return $url;
			}
		}

		// Preset PNG (stored as key e.g. 'blue').
		$preset_key = $settings['default_avatar_preset'] ?? '';
		if ( $preset_key ) {
			return self::preset_url( $preset_key );
		}

		return '';
	}

	// ── Admin enqueue ─────────────────────────────────────────────────────────

	/**
	 * Media modal + localised data, on our settings page only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue( string $hook ): void {
		if ( 'settings_page_nahnu-sct-settings' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_localize_script(
			'nahnu-sct-admin',
			'nahnuSctAvatar',
			array(
				'nonce'   => wp_create_nonce( 'nahnu_sct_avatar' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	// ── AJAX: save media library attachment ───────────────────────────────────

	/**
	 * AJAX: store a Media Library attachment as the default avatar.
	 */
	public function ajax_save(): void {
		check_ajax_referer( 'nahnu_sct_avatar', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$id = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0;
		if ( ! $id || ! get_post( $id ) ) {
			wp_send_json_error( 'Invalid attachment.' );
		}
		$settings                          = Nahnu_SCT_Settings::get();
		$settings['default_avatar_id']     = $id;
		$settings['default_avatar_preset'] = '';
		update_option( NAHNU_SCT_OPTION, $settings );

		$preview = self::attachment_url( $id );

		wp_send_json_success( array( 'preview_url' => $preview ) );
	}

	// ── AJAX: save preset by key ──────────────────────────────────────────────

	/**
	 * AJAX: store a bundled preset as the default avatar.
	 */
	public function ajax_save_preset(): void {
		check_ajax_referer( 'nahnu_sct_avatar', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$key = sanitize_key( $_POST['preset'] ?? '' );
		if ( ! array_key_exists( $key, self::presets() ) ) {
			wp_send_json_error( 'Invalid preset.' );
		}
		$settings                          = Nahnu_SCT_Settings::get();
		$settings['default_avatar_id']     = 0;
		$settings['default_avatar_preset'] = $key;
		update_option( NAHNU_SCT_OPTION, $settings );

		wp_send_json_success( array( 'preview_url' => self::preset_url( $key ) ) );
	}

	// ── AJAX: remove ──────────────────────────────────────────────────────────

	/**
	 * AJAX: clear the default avatar.
	 */
	public function ajax_remove(): void {
		check_ajax_referer( 'nahnu_sct_avatar', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$settings                          = Nahnu_SCT_Settings::get();
		$settings['default_avatar_id']     = 0;
		$settings['default_avatar_preset'] = '';
		update_option( NAHNU_SCT_OPTION, $settings );
		wp_send_json_success();
	}

	// ── Admin UI ──────────────────────────────────────────────────────────────

	/**
	 * Avatar section of the Brand tab.
	 *
	 * @param array $s Current settings.
	 */
	public function render_ui( array $s ): void {
		$preset_key    = $s['default_avatar_preset'] ?? '';
		$attachment_id = (int) ( $s['default_avatar_id'] ?? 0 );
		$preview_url   = '';

		if ( $attachment_id ) {
			$preview_url = self::attachment_url( $attachment_id );
		} elseif ( $preset_key ) {
			$preview_url = self::preset_url( $preset_key );
		}

		$has = ! empty( $preview_url );
		?>
		<section class="nst-section">
			<header class="nst-section-head">
				<h2><?php esc_html_e( 'Default profile photo', 'nahnu-surecart-theme' ); ?></h2>
				<p><?php esc_html_e( 'Shown on the account dashboard for customers without a Gravatar, instead of their initial.', 'nahnu-surecart-theme' ); ?></p>
			</header>
			<div class="nst-rows">

				<div class="nst-row">
					<div class="nst-row-label">
						<label><?php esc_html_e( 'Custom image', 'nahnu-surecart-theme' ); ?></label>
						<span class="nst-hint"><?php esc_html_e( 'PNG, JPG or WebP · 200×200px recommended.', 'nahnu-surecart-theme' ); ?></span>
					</div>
					<div class="nst-row-control nst-av">
						<div id="nst-av-circle" class="nst-av-circle">
							<?php if ( $preview_url ) : ?>
								<img src="<?php echo esc_url( $preview_url ); ?>" alt="" id="nst-av-img">
							<?php else : ?>
								<svg id="nst-av-ph" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
							<?php endif; ?>
						</div>
						<div class="nst-av-actions">
							<div id="nst-avatar-pick" class="nst-av-btn" role="button" tabindex="0">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
								<?php echo $has ? esc_html__( 'Change photo', 'nahnu-surecart-theme' ) : esc_html__( 'Media Library', 'nahnu-surecart-theme' ); ?>
							</div>
							<div id="nst-avatar-remove" class="nst-av-btn nst-av-remove" role="button" tabindex="0" <?php echo $has ? '' : 'hidden'; ?>>
								<?php esc_html_e( 'Remove', 'nahnu-surecart-theme' ); ?>
							</div>
							<p id="nst-avatar-msg" hidden class="nst-avatar-msg"></p>
						</div>
					</div>
				</div>

				<div class="nst-row">
					<div class="nst-row-label">
						<label><?php esc_html_e( 'Or a preset', 'nahnu-surecart-theme' ); ?></label>
						<span class="nst-hint"><?php esc_html_e( 'Simple neutral silhouettes.', 'nahnu-surecart-theme' ); ?></span>
					</div>
					<div class="nst-row-control">
						<div class="nst-av-presets">
							<?php foreach ( self::presets() as $key => $preset ) : ?>
								<div class="nst-av-preset<?php echo $preset_key === $key ? ' is-selected' : ''; ?>" role="button" tabindex="0"
									data-preset="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $preset['label'] ); ?>">
									<img src="<?php echo esc_url( self::preset_url( $key ) ); ?>" alt="<?php echo esc_attr( $preset['label'] ); ?>">
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

			</div>
			<footer class="nst-section-foot">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
				<span><strong><?php esc_html_e( 'Experimental.', 'nahnu-surecart-theme' ); ?></strong> <?php esc_html_e( 'SureCart passes the avatar URL through esc_url(), so only regular https:// images work (presets and Media Library uploads do). If the dashboard still shows an initial after saving, clear any page cache.', 'nahnu-surecart-theme' ); ?></span>
			</footer>
		</section>
		<?php
	}
}
