<?php
/**
 * Admin — settings page under Settings > SureCart Theme.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

class NST_Admin {

	public function __construct() {
		add_action( 'admin_menu',            [ $this, 'add_menu' ] );
		add_action( 'admin_init',            [ $this, 'handle_save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function add_menu(): void {
		add_options_page(
			__( 'SureCart Theme', 'nahnu-surecart-theme' ),
			__( 'SureCart Theme', 'nahnu-surecart-theme' ),
			'manage_options',
			'nst-settings',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue( string $hook ): void {
		if ( 'settings_page_nst-settings' !== $hook ) {
			return;
		}
		$css_ver = file_exists( NST_DIR . 'assets/css/admin.css' )
			? filemtime( NST_DIR . 'assets/css/admin.css' )
			: NST_VERSION;
		$js_ver = file_exists( NST_DIR . 'assets/js/admin.js' )
			? filemtime( NST_DIR . 'assets/js/admin.js' )
			: NST_VERSION;

		wp_enqueue_style(
			'nst-admin',
			NST_URL . 'assets/css/admin.css',
			[],
			$css_ver
		);
		wp_enqueue_script(
			'nst-admin',
			NST_URL . 'assets/js/admin.js',
			[ 'media-editor' ],
			$js_ver,
			true
		);
	}

	public function handle_save(): void {
		if ( ! isset( $_POST['nst_save'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'nahnu-surecart-theme' ) );
		}
		check_admin_referer( 'nst_save_settings' );

		if ( isset( $_POST['nst_reset'] ) ) {
			NST_Settings::reset();
			wp_safe_redirect( add_query_arg( [ 'page' => 'nst-settings', 'reset' => '1' ], admin_url( 'options-general.php' ) ) );
			exit;
		}

		NST_Settings::save( $_POST );
		wp_safe_redirect( add_query_arg( [ 'page' => 'nst-settings', 'saved' => '1' ], admin_url( 'options-general.php' ) ) );
		exit;
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = NST_Settings::get();
		$saved  = isset( $_GET['saved'] );
		$reset  = isset( $_GET['reset'] );
		?>
		<div class="wrap nst-wrap">

			<div class="nst-header">
				<div class="nst-header-logo">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
				</div>
				<div>
					<h1 class="nst-header-title"><?php esc_html_e( 'SureCart Theme', 'nahnu-surecart-theme' ); ?></h1>
					<p class="nst-header-sub"><?php esc_html_e( 'Style your cart, checkout, and account dashboard to match your brand.', 'nahnu-surecart-theme' ); ?></p>
				</div>
			</div>

			<?php if ( $saved ) : ?>
				<div class="nst-notice nst-notice--success">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
					<?php esc_html_e( 'Settings saved. CSS is now live on your site.', 'nahnu-surecart-theme' ); ?>
				</div>
			<?php elseif ( $reset ) : ?>
				<div class="nst-notice nst-notice--info">
					<?php esc_html_e( 'Settings reset to defaults.', 'nahnu-surecart-theme' ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="" class="nst-form">
				<?php wp_nonce_field( 'nst_save_settings' ); ?>
				<input type="hidden" name="nst_save" value="1">

				<!-- ── Tab nav ───────────────────────────────────── -->
				<div class="nst-tabs">
					<button type="button" class="nst-tab active" data-tab="brand"><?php esc_html_e( 'Brand', 'nahnu-surecart-theme' ); ?></button>
					<button type="button" class="nst-tab" data-tab="cart"><?php esc_html_e( 'Mini Cart', 'nahnu-surecart-theme' ); ?></button>
					<button type="button" class="nst-tab" data-tab="checkout"><?php esc_html_e( 'Checkout', 'nahnu-surecart-theme' ); ?></button>
					<button type="button" class="nst-tab" data-tab="account"><?php esc_html_e( 'Account', 'nahnu-surecart-theme' ); ?></button>
				</div>

				<!-- ══ BRAND ══════════════════════════════════════ -->
				<div class="nst-panel active" id="nst-tab-brand">

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Accent color', 'nahnu-surecart-theme' ); ?></h2>
						<p class="nst-section-desc"><?php esc_html_e( 'Used on every button, badge, focus ring, and selected state across the cart, checkout, and account dashboard.', 'nahnu-surecart-theme' ); ?></p>

						<div class="nst-color-row">
							<input type="color" name="accent_color" id="accent_color"
								value="<?php echo esc_attr( $s['accent_color'] ); ?>"
								class="nst-color-picker">
							<input type="text" name="accent_color_hex" id="accent_color_hex"
								value="<?php echo esc_attr( $s['accent_color'] ); ?>"
								class="nst-hex-input" maxlength="7" placeholder="#1a1a1a">
						</div>

						<div class="nst-swatches">
							<?php
							$presets = [
								'#1a1a1a' => 'Obsidian',
								'#2563eb' => 'Blue',
								'#7c3aed' => 'Violet',
								'#db2777' => 'Pink',
								'#dc2626' => 'Red',
								'#ea580c' => 'Orange',
								'#ca8a04' => 'Amber',
								'#16a34a' => 'Green',
								'#0891b2' => 'Cyan',
								'#475569' => 'Slate',
							];
							foreach ( $presets as $hex => $label ) :
								$active = ( strtolower( $s['accent_color'] ) === strtolower( $hex ) ) ? 'active' : '';
								?>
								<span class="nst-swatch <?php echo esc_attr( $active ); ?>"
									style="background:<?php echo esc_attr( $hex ); ?>"
									data-color="<?php echo esc_attr( $hex ); ?>"
									title="<?php echo esc_attr( $label ); ?>"></span>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Font family', 'nahnu-surecart-theme' ); ?></h2>
						<p class="nst-section-desc"><?php esc_html_e( 'Leave blank to inherit your theme\'s font. Use any Google Fonts value or CSS font stack.', 'nahnu-surecart-theme' ); ?></p>
						<input type="text" name="font_family" value="<?php echo esc_attr( $s['font_family'] ); ?>"
							class="nst-text-input" placeholder="e.g. 'Inter', sans-serif">
					</div>

				<?php ( new NST_Avatar() )->render_ui( $s ); ?>

				</div>

				<!-- ══ MINI CART ══════════════════════════════════ -->
				<div class="nst-panel" id="nst-tab-cart">

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Drawer', 'nahnu-surecart-theme' ); ?></h2>

						<div class="nst-field-grid">

							<div class="nst-field">
								<label><?php esc_html_e( 'Width', 'nahnu-surecart-theme' ); ?></label>
								<select name="cart_width" class="nst-select">
									<?php
									foreach ( [ '360px' => __( 'Narrow — 360px', 'nahnu-surecart-theme' ), '420px' => __( 'Default — 420px', 'nahnu-surecart-theme' ), '480px' => __( 'Wide — 480px', 'nahnu-surecart-theme' ), '540px' => __( 'Extra wide — 540px', 'nahnu-surecart-theme' ) ] as $val => $label ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( $val ),
											selected( $s['cart_width'], $val, false ),
											esc_html( $label )
										);
									}
									?>
								</select>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Section divider color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="cart_border_color" value="<?php echo esc_attr( $s['cart_border_color'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Backdrop opacity', 'nahnu-surecart-theme' ); ?></label>
								<input type="range" name="cart_backdrop_opacity" min="0" max="1" step="0.05"
									value="<?php echo esc_attr( $s['cart_backdrop_opacity'] ); ?>"
									class="nst-range">
								<span class="nst-range-val"><?php echo esc_html( $s['cart_backdrop_opacity'] ); ?></span>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Checkout button radius', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="cart_btn_radius" value="<?php echo esc_attr( $s['cart_btn_radius'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="9px">
							</div>

						</div>
					</div>

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Floating bubble', 'nahnu-surecart-theme' ); ?></h2>

						<div class="nst-field nst-field--toggle">
							<label><?php esc_html_e( 'Show floating cart bubble', 'nahnu-surecart-theme' ); ?></label>
							<label class="nst-toggle">
								<input type="checkbox" name="bubble_enabled" value="1" <?php checked( $s['bubble_enabled'] ); ?>>
								<span class="nst-toggle-track"></span>
							</label>
						</div>

						<div class="nst-field-grid nst-depends-bubble">
							<div class="nst-field">
								<label><?php esc_html_e( 'Size (width × height)', 'nahnu-surecart-theme' ); ?></label>
								<div style="display:flex;gap:0.5rem">
									<input type="text" name="bubble_width" value="<?php echo esc_attr( $s['bubble_width'] ); ?>"
										class="nst-text-input nst-text-input--sm" placeholder="56px">
									<input type="text" name="bubble_height" value="<?php echo esc_attr( $s['bubble_height'] ); ?>"
										class="nst-text-input nst-text-input--sm" placeholder="56px">
								</div>
							</div>
							<div class="nst-field">
								<label><?php esc_html_e( 'Border radius', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="bubble_radius" value="<?php echo esc_attr( $s['bubble_radius'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="999px">
								<span class="nst-hint"><?php esc_html_e( 'Use 999px for pill, 0 for square', 'nahnu-surecart-theme' ); ?></span>
							</div>
							<div class="nst-field">
								<label><?php esc_html_e( 'Position from bottom', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="bubble_bottom" value="<?php echo esc_attr( $s['bubble_bottom'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="28px">
							</div>
							<div class="nst-field">
								<label><?php esc_html_e( 'Position from right', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="bubble_right" value="<?php echo esc_attr( $s['bubble_right'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="28px">
							</div>
						</div>
					</div>

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Features', 'nahnu-surecart-theme' ); ?></h2>

						<div class="nst-field nst-field--toggle">
							<div>
								<label><?php esc_html_e( 'Show quantity controls', 'nahnu-surecart-theme' ); ?></label>
								<p class="nst-hint"><?php esc_html_e( '+/− buttons on line items', 'nahnu-surecart-theme' ); ?></p>
							</div>
							<label class="nst-toggle">
								<input type="checkbox" name="show_qty_controls" value="1" <?php checked( $s['show_qty_controls'] ); ?>>
								<span class="nst-toggle-track"></span>
							</label>
						</div>

						<div class="nst-field nst-field--toggle">
							<div>
								<label><?php esc_html_e( 'Show coupon field', 'nahnu-surecart-theme' ); ?></label>
								<p class="nst-hint"><?php esc_html_e( 'Collapsible promo code entry in the cart', 'nahnu-surecart-theme' ); ?></p>
							</div>
							<label class="nst-toggle">
								<input type="checkbox" name="show_coupon" value="1" <?php checked( $s['show_coupon'] ); ?>>
								<span class="nst-toggle-track"></span>
							</label>
						</div>
					</div>

				</div>

				<!-- ══ CHECKOUT ═══════════════════════════════════ -->
				<div class="nst-panel" id="nst-tab-checkout">

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Input fields', 'nahnu-surecart-theme' ); ?></h2>
						<p class="nst-section-desc"><?php esc_html_e( 'All checkout fields read these tokens via CSS custom property inheritance into Shadow DOM.', 'nahnu-surecart-theme' ); ?></p>

						<div class="nst-field-grid">

							<div class="nst-field">
								<label><?php esc_html_e( 'Border color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_border_color" value="<?php echo esc_attr( $s['input_border_color'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Border color on hover', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_border_hover" value="<?php echo esc_attr( $s['input_border_hover'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Border radius', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="input_radius" value="<?php echo esc_attr( $s['input_radius'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="8px">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Choice card radius', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="choice_radius" value="<?php echo esc_attr( $s['choice_radius'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="8px">
								<span class="nst-hint"><?php esc_html_e( 'Price/plan selector cards', 'nahnu-surecart-theme' ); ?></span>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Background', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_bg" value="<?php echo esc_attr( $s['input_bg'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Text color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_color" value="<?php echo esc_attr( $s['input_color'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Placeholder color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_placeholder" value="<?php echo esc_attr( $s['input_placeholder'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Label color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="input_label_color" value="<?php echo esc_attr( $s['input_label_color'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Label weight', 'nahnu-surecart-theme' ); ?></label>
								<select name="input_label_weight" class="nst-select">
									<?php foreach ( [ '400' => 'Normal', '500' => 'Medium', '600' => 'Semibold', '700' => 'Bold' ] as $val => $label ) :
										printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $s['input_label_weight'], $val, false ), esc_html( $label ) );
									endforeach; ?>
								</select>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Row spacing', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="form_row_spacing" value="<?php echo esc_attr( $s['form_row_spacing'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="1.125rem">
								<span class="nst-hint"><?php esc_html_e( 'Gap between form fields', 'nahnu-surecart-theme' ); ?></span>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Box shadow', 'nahnu-surecart-theme' ); ?></label>
								<select name="input_shadow" class="nst-select">
									<option value="none" <?php selected( $s['input_shadow'], 'none' ); ?>><?php esc_html_e( 'None (flat)', 'nahnu-surecart-theme' ); ?></option>
									<option value="rgba(0,0,0,.05) 0px 1px 2px 0px" <?php selected( $s['input_shadow'], 'rgba(0,0,0,.05) 0px 1px 2px 0px' ); ?>><?php esc_html_e( 'Subtle (SureCart default)', 'nahnu-surecart-theme' ); ?></option>
								</select>
							</div>

						</div>
					</div>

				</div>

				<!-- ══ ACCOUNT ════════════════════════════════════ -->
				<div class="nst-panel" id="nst-tab-account">

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Page & sidebar', 'nahnu-surecart-theme' ); ?></h2>
						<p class="nst-section-desc">
							<?php esc_html_e( 'The sidebar is a CSS ::before pseudo-element on the body. Setting width here updates both the visual panel and the flex column that controls layout.', 'nahnu-surecart-theme' ); ?>
						</p>

						<div class="nst-field-grid">

							<div class="nst-field">
								<label><?php esc_html_e( 'Page background', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="dashboard_bg" value="<?php echo esc_attr( $s['dashboard_bg'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Sidebar background', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="dashboard_sidebar_bg" value="<?php echo esc_attr( $s['dashboard_sidebar_bg'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Sidebar width', 'nahnu-surecart-theme' ); ?></label>
								<select name="dashboard_sidebar_w" class="nst-select">
									<?php foreach ( [ '260px' => '260px (narrow)', '300px' => '300px', '325px' => '325px (default)', '360px' => '360px (wide)' ] as $val => $label ) :
										printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $s['dashboard_sidebar_w'], $val, false ), esc_html( $label ) );
									endforeach; ?>
								</select>
							</div>

						</div>
					</div>

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Cards & rows', 'nahnu-surecart-theme' ); ?></h2>

						<div class="nst-field-grid">

							<div class="nst-field">
								<label><?php esc_html_e( 'Card border color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="dashboard_card_border" value="<?php echo esc_attr( $s['dashboard_card_border'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Card border radius', 'nahnu-surecart-theme' ); ?></label>
								<input type="text" name="dashboard_card_radius" value="<?php echo esc_attr( $s['dashboard_card_radius'] ); ?>"
									class="nst-text-input nst-text-input--sm" placeholder="10px">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Card shadow', 'nahnu-surecart-theme' ); ?></label>
								<select name="dashboard_card_shadow" class="nst-select">
									<option value="none" <?php selected( $s['dashboard_card_shadow'], 'none' ); ?>><?php esc_html_e( 'None', 'nahnu-surecart-theme' ); ?></option>
									<option value="0 1px 2px rgba(0,0,0,0.06)" <?php selected( $s['dashboard_card_shadow'], '0 1px 2px rgba(0,0,0,0.06)' ); ?>><?php esc_html_e( 'Subtle', 'nahnu-surecart-theme' ); ?></option>
									<option value="0 4px 16px rgba(0,0,0,0.08)" <?php selected( $s['dashboard_card_shadow'], '0 4px 16px rgba(0,0,0,0.08)' ); ?>><?php esc_html_e( 'Elevated', 'nahnu-surecart-theme' ); ?></option>
								</select>
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Row hover background', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="dashboard_row_hover" value="<?php echo esc_attr( $s['dashboard_row_hover'] ); ?>">
							</div>

							<div class="nst-field">
								<label><?php esc_html_e( 'Row divider color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="dashboard_row_border" value="<?php echo esc_attr( $s['dashboard_row_border'] ); ?>">
							</div>

						</div>
					</div>

					<div class="nst-section">
						<h2 class="nst-section-title"><?php esc_html_e( 'Navigation tabs', 'nahnu-surecart-theme' ); ?></h2>
						<p class="nst-section-desc"><?php esc_html_e( 'Active state for the sidebar nav items (sc-tab component).', 'nahnu-surecart-theme' ); ?></p>

						<div class="nst-field-grid">
							<div class="nst-field">
								<label><?php esc_html_e( 'Active tab background', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="tab_active_bg" value="<?php echo esc_attr( $s['tab_active_bg'] ); ?>">
							</div>
							<div class="nst-field">
								<label><?php esc_html_e( 'Active tab text color', 'nahnu-surecart-theme' ); ?></label>
								<input type="color" name="tab_active_color" value="<?php echo esc_attr( $s['tab_active_color'] ); ?>">
							</div>
						</div>
					</div>

				</div>

				<!-- ── Footer ──────────────────────────────────── -->
				<div class="nst-footer">
					<button type="submit" name="nst_reset" value="1" class="nst-btn nst-btn--ghost"
						onclick="return confirm('<?php echo esc_js( __( 'Reset all settings to defaults?', 'nahnu-surecart-theme' ) ); ?>')">
						<?php esc_html_e( 'Reset to defaults', 'nahnu-surecart-theme' ); ?>
					</button>
					<button type="submit" class="nst-btn nst-btn--primary">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
						<?php esc_html_e( 'Save changes', 'nahnu-surecart-theme' ); ?>
					</button>
				</div>

			</form>

		</div><!-- .nst-wrap -->
		<?php
	}
}
