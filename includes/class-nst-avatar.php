<?php
/**
 * Default Avatar
 *
 * Replaces the Gravatar / initial-letter fallback with a custom image.
 *
 * HOW IT WORKS:
 * - Preset PNGs ship with the plugin in assets/avatars/.
 * - Their URLs are real https:// URLs (NST_URL . 'assets/avatars/NAME.png').
 * - We hook 'pre_get_avatar_data' at priority 5 and set $args['url'].
 * - WordPress skips Gravatar and returns our URL.
 * - SureCart does esc_url() on the result — passes through fine for https://.
 * - sc-avatar gets a real image URL and renders it.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

class NST_Avatar {

	public function __construct() {
		add_filter( 'pre_get_avatar_data',         [ $this, 'inject_avatar_data' ], 5, 2 );
		add_action( 'admin_enqueue_scripts',       [ $this, 'enqueue' ], 20 );
		add_action( 'wp_ajax_nst_save_avatar',     [ $this, 'ajax_save' ] );
		add_action( 'wp_ajax_nst_remove_avatar',   [ $this, 'ajax_remove' ] );
		add_action( 'wp_ajax_nst_save_preset',     [ $this, 'ajax_save_preset' ] );
	}

	// ── Presets ───────────────────────────────────────────────────────────────

	public static function presets(): array {
		return [
			'light'  => [ 'label' => 'Light',  'file' => 'light.png'  ],
			'dark'   => [ 'label' => 'Dark',   'file' => 'dark.png'   ],
			'blue'   => [ 'label' => 'Blue',   'file' => 'blue.png'   ],
			'green'  => [ 'label' => 'Green',  'file' => 'green.png'  ],
			'purple' => [ 'label' => 'Purple', 'file' => 'purple.png' ],
			'orange' => [ 'label' => 'Orange', 'file' => 'orange.png' ],
		];
	}

	public static function preset_url( string $key ): string {
		$presets = self::presets();
		if ( ! isset( $presets[ $key ] ) ) {
			return '';
		}
		return NST_URL . 'assets/avatars/' . $presets[ $key ]['file'];
	}

	// ── Front-end filter ──────────────────────────────────────────────────────

	public function inject_avatar_data( $args, $id_or_email ): array {
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
		$settings = NST_Settings::get();

		// Media library image.
		$attachment_id = (int) ( $settings['default_avatar_id'] ?? 0 );
		if ( $attachment_id ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'full' )
			    ?: wp_get_attachment_url( $attachment_id );
			if ( $url ) {
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

	public function enqueue( string $hook ): void {
		if ( 'settings_page_nst-settings' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_localize_script( 'nst-admin', 'nstAvatar', [
			'nonce'   => wp_create_nonce( 'nst_avatar_nonce' ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		] );
	}

	// ── AJAX: save media library attachment ───────────────────────────────────

	public function ajax_save(): void {
		check_ajax_referer( 'nst_avatar_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$id = max( 0, (int) ( $_POST['attachment_id'] ?? 0 ) );
		if ( ! $id || ! get_post( $id ) ) {
			wp_send_json_error( 'Invalid attachment.' );
		}
		$settings                          = NST_Settings::get();
		$settings['default_avatar_id']     = $id;
		$settings['default_avatar_preset'] = '';
		update_option( NST_OPTION, $settings );

		$preview = wp_get_attachment_image_url( $id, [ 80, 80 ] )
		        ?: wp_get_attachment_url( $id );

		wp_send_json_success( [ 'preview_url' => $preview ] );
	}

	// ── AJAX: save preset by key ──────────────────────────────────────────────

	public function ajax_save_preset(): void {
		check_ajax_referer( 'nst_avatar_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$key = sanitize_key( $_POST['preset'] ?? '' );
		if ( ! array_key_exists( $key, self::presets() ) ) {
			wp_send_json_error( 'Invalid preset.' );
		}
		$settings                          = NST_Settings::get();
		$settings['default_avatar_id']     = 0;
		$settings['default_avatar_preset'] = $key;
		update_option( NST_OPTION, $settings );

		wp_send_json_success( [ 'preview_url' => self::preset_url( $key ) ] );
	}

	// ── AJAX: remove ──────────────────────────────────────────────────────────

	public function ajax_remove(): void {
		check_ajax_referer( 'nst_avatar_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissions.' );
		}
		$settings                          = NST_Settings::get();
		$settings['default_avatar_id']     = 0;
		$settings['default_avatar_preset'] = '';
		update_option( NST_OPTION, $settings );
		wp_send_json_success();
	}

	// ── Admin UI ──────────────────────────────────────────────────────────────

	public function render_ui( array $s ): void {
		$preset_key    = $s['default_avatar_preset'] ?? '';
		$attachment_id = (int) ( $s['default_avatar_id'] ?? 0 );
		$preview_url   = '';

		if ( $attachment_id ) {
			$preview_url = wp_get_attachment_image_url( $attachment_id, [ 80, 80 ] )
			            ?: wp_get_attachment_url( $attachment_id );
		} elseif ( $preset_key ) {
			$preview_url = self::preset_url( $preset_key );
		}

		$has = ! empty( $preview_url );
		?>
		<div class="nst-section">

			<h2 class="nst-section-title"><?php esc_html_e( 'Default profile photo', 'nahnu-surecart-theme' ); ?></h2>
			<p class="nst-section-desc"><?php esc_html_e( "Shown on the customer dashboard for users who don't have a Gravatar — replaces the initial letter.", 'nahnu-surecart-theme' ); ?></p>

			<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:7px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;gap:.625rem;align-items:flex-start">
				<svg style="flex-shrink:0;margin-top:1px" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
				<p style="margin:0;font-size:.78rem;color:#92400e;line-height:1.5"><strong>Proof of concept</strong> &mdash; This feature requires a small modification to SureCart&rsquo;s dashboard template to work. SureCart currently passes the avatar URL through <code>esc_url()</code> which strips non-standard URLs. A future SureCart update or a custom template override would be needed for the avatar to display on the customer dashboard.</p>
			</div>
			<div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1rem">

				<div id="nst-av-circle" style="width:64px;height:64px;border-radius:50%;background:#f4f4f4;border:2px solid #ebebeb;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-sizing:border-box">
					<?php if ( $preview_url ) : ?>
						<img src="<?php echo esc_url( $preview_url ); ?>" alt="" id="nst-av-img" style="width:100%;height:100%;object-fit:cover;display:block">
					<?php else : ?>
						<svg id="nst-av-ph" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
						</svg>
					<?php endif; ?>
				</div>

				<div style="display:flex;flex-direction:column;gap:0.375rem;padding-top:0.25rem">
					<div id="nst-avatar-pick" class="nst-av-btn" role="button" tabindex="0">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
						<?php echo $has ? esc_html__( 'Change photo', 'nahnu-surecart-theme' ) : esc_html__( 'Upload / Media Library', 'nahnu-surecart-theme' ); ?>
					</div>
					<div id="nst-avatar-remove" class="nst-av-btn nst-av-remove" <?php echo $has ? '' : 'hidden'; ?>>
						<?php esc_html_e( 'Remove', 'nahnu-surecart-theme' ); ?>
					</div>
					<p id="nst-avatar-msg" hidden class="nst-avatar-msg"></p>
					<p class="nst-hint"><?php esc_html_e( 'PNG, JPG, or WebP · 200×200px recommended', 'nahnu-surecart-theme' ); ?></p>
				</div>

			</div>

			<p style="font-size:0.72rem;font-weight:600;color:#acacac;letter-spacing:0.06em;text-transform:uppercase;margin:0 0 0.5rem"><?php esc_html_e( 'Or pick a preset', 'nahnu-surecart-theme' ); ?></p>
			<div style="display:flex;flex-direction:row;flex-wrap:wrap;gap:10px;align-items:center">
				<?php foreach ( self::presets() as $key => $preset ) :
					$url = self::preset_url( $key );
					$selected = ( $preset_key === $key ) ? 'border-color:#1a1a1a;box-shadow:0 0 0 3px rgba(26,26,26,0.15)' : '';
				?>
					<div class="nst-av-preset"
						role="button" tabindex="0"
						data-preset="<?php echo esc_attr( $key ); ?>"
						title="<?php echo esc_attr( $preset['label'] ); ?>"
						style="width:52px;height:52px;border-radius:50%;border:2px solid #e0e0e0;overflow:hidden;cursor:pointer;flex-shrink:0;display:block;box-sizing:border-box;<?php echo esc_attr( $selected ); ?>">
						<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $preset['label'] ); ?>" style="width:52px;height:52px;display:block;pointer-events:none">
					</div>
				<?php endforeach; ?>
			</div>

		</div>
		<?php
	}
}
