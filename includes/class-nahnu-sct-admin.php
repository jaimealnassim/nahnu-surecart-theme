<?php
/**
 * Admin — settings page under Settings > Custom Theme for SureCart.
 *
 * Layout: top bar (title + save/reset, always visible), left section nav,
 * inspector-style field rows in the middle, sticky live preview on the right.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings page.
 */
class Nahnu_SCT_Admin {

	/**
	 * Current settings, set in render_page().
	 *
	 * @var array
	 */
	private array $s = array();

	/**
	 * Section currently shown (from ?tab=, validated in render_page()).
	 *
	 * @var string
	 */
	private string $tab = 'brand';

	/** Hook into admin. */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/** Register the Settings submenu page. */
	public function add_menu(): void {
		add_options_page(
			__( 'Nahnu Custom Theme for SureCart', 'nahnu-surecart-theme' ),
			__( 'Custom Theme for SureCart', 'nahnu-surecart-theme' ),
			'manage_options',
			'nahnu-sct-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Load admin assets on our page only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue( string $hook ): void {
		if ( 'settings_page_nahnu-sct-settings' !== $hook ) {
			return;
		}
		$css_ver = file_exists( NAHNU_SCT_DIR . 'assets/css/admin.css' ) ? filemtime( NAHNU_SCT_DIR . 'assets/css/admin.css' ) : NAHNU_SCT_VERSION;
		$js_ver  = file_exists( NAHNU_SCT_DIR . 'assets/js/admin.js' ) ? filemtime( NAHNU_SCT_DIR . 'assets/js/admin.js' ) : NAHNU_SCT_VERSION;

		wp_enqueue_style( 'nahnu-sct-admin', NAHNU_SCT_URL . 'assets/css/admin.css', array(), $css_ver );
		wp_enqueue_script( 'nahnu-sct-admin', NAHNU_SCT_URL . 'assets/js/admin.js', array( 'media-editor' ), $js_ver, true );
		wp_localize_script( 'nahnu-sct-admin', 'nahnuSctTools', Nahnu_SCT_Tools::js_data() );
	}

	/** Handle the Save / Reset submit, then redirect back. */
	public function handle_save(): void {
		if ( ! isset( $_POST['nst_save'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'nahnu-surecart-theme' ) );
		}
		check_admin_referer( 'nahnu_sct_save_settings' );

		$tab = isset( $_POST['nst_tab'] ) ? sanitize_key( wp_unslash( $_POST['nst_tab'] ) ) : 'brand';

		if ( isset( $_POST['nst_reset'] ) ) {
			Nahnu_SCT_Settings::reset();
			$this->redirect(
				array(
					'reset' => '1',
					'tab'   => $tab,
				)
			);
		}

		// Every value is re-validated against its own rule in Nahnu_SCT_Settings::sanitize().
		Nahnu_SCT_Settings::save( map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- map_deep() sanitizes every leaf.
		$this->redirect(
			array(
				'saved' => '1',
				'tab'   => $tab,
			)
		);
	}

	/**
	 * Redirect back to the settings page.
	 *
	 * @param array $args Extra query args.
	 */
	private function redirect( array $args ): void {
		wp_safe_redirect( add_query_arg( array_merge( array( 'page' => 'nahnu-sct-settings' ), $args ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	// ── Field helpers ────────────────────────────────────────────────────────
	// Every setting renders as one row: label + hint on the left, control right.

	/**
	 * Output one settings row.
	 *
	 * @param string $label    Row label.
	 * @param string $control  Control markup from one of the helpers below (already escaped).
	 * @param string $hint     Optional helper text under the label.
	 * @param string $input_id Optional id of the control the label points at.
	 */
	private function row( string $label, string $control, string $hint = '', string $input_id = '' ): void {
		echo '<div class="nst-row">';
		echo '<div class="nst-row-label"><label' . ( '' !== $input_id ? ' for="' . esc_attr( $input_id ) . '"' : '' ) . '>' . esc_html( $label ) . '</label>';
		if ( '' !== $hint ) {
			echo '<span class="nst-hint">' . esc_html( $hint ) . '</span>';
		}
		echo '</div>';
		echo '<div class="nst-row-control">' . $control . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by color()/text()/select()/toggle()/range(), which escape every value.
		echo '</div>';
	}

	/**
	 * Colour picker with a hex readout.
	 *
	 * @param string $name Setting key.
	 */
	private function color( string $name ): string {
		$v = esc_attr( $this->s[ $name ] );
		return '<label class="nst-color">'
			. '<input type="color" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . $v . '">'
			. '<span class="nst-color-hex" data-for="' . esc_attr( $name ) . '">' . $v . '</span>'
			. '</label>';
	}

	/**
	 * Text input.
	 *
	 * @param string $name        Setting key.
	 * @param string $placeholder Placeholder text.
	 * @param string $size        'sm' or 'lg'.
	 */
	private function text( string $name, string $placeholder = '', string $size = 'sm' ): string {
		return '<input type="text" class="nst-input nst-input--' . esc_attr( $size ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '"'
			. ' value="' . esc_attr( $this->s[ $name ] ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
	}

	/**
	 * Select box.
	 *
	 * @param string $name    Setting key.
	 * @param array  $options value => label.
	 */
	private function select( string $name, array $options ): string {
		$h = '<select class="nst-select" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';
		foreach ( $options as $val => $label ) {
			$h .= '<option value="' . esc_attr( $val ) . '"' . selected( (string) $this->s[ $name ], (string) $val, false ) . '>' . esc_html( $label ) . '</option>';
		}
		return $h . '</select>';
	}

	/**
	 * On/off switch.
	 *
	 * @param string $name Setting key.
	 */
	private function toggle( string $name ): string {
		return '<label class="nst-toggle"><input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="1"'
			. checked( ! empty( $this->s[ $name ] ), true, false ) . '><span class="nst-toggle-track"></span></label>';
	}

	/**
	 * Range slider with a live value readout.
	 *
	 * @param string $name Setting key.
	 * @param string $min  Minimum.
	 * @param string $max  Maximum.
	 * @param string $step Step.
	 */
	private function range( string $name, string $min, string $max, string $step ): string {
		$v = esc_attr( $this->s[ $name ] );
		return '<span class="nst-range-wrap"><input type="range" class="nst-range" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '"'
			. ' min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . $v . '">'
			. '<output class="nst-range-val">' . $v . '</output></span>';
	}

	/**
	 * Open a titled card of rows.
	 *
	 * @param string $title Card title.
	 * @param string $desc  Optional description.
	 */
	private function section_open( string $title, string $desc = '' ): void {
		echo '<section class="nst-section"><header class="nst-section-head"><h2>' . esc_html( $title ) . '</h2>';
		if ( $desc ) {
			echo '<p>' . esc_html( $desc ) . '</p>';
		}
		echo '</header><div class="nst-rows">';
	}

	/** Close a card opened with section_open(). */
	private function section_close(): void {
		echo '</div></section>';
	}

	// ── Page ─────────────────────────────────────────────────────────────────

	/** Render the settings page. */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->s = Nahnu_SCT_Settings::get();
		$s       = $this->s;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display state from the redirect after save; nothing is written here.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'brand';
		if ( ! in_array( $tab, array( 'brand', 'cart', 'checkout', 'account', 'tools' ), true ) ) {
			$tab = 'brand';
		}
		$notice = '';
		if ( isset( $_GET['saved'] ) ) {
			$notice = 'saved';
		} elseif ( isset( $_GET['reset'] ) ) {
			$notice = 'reset';
		}
		// phpcs:enable
		$this->tab = $tab;

		$nav = array(
			'brand'    => array( __( 'Brand', 'nahnu-surecart-theme' ), 'M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5' ),
			'cart'     => array( __( 'Mini Cart', 'nahnu-surecart-theme' ), 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0' ),
			'checkout' => array( __( 'Checkout', 'nahnu-surecart-theme' ), 'M1 4h22v16H1zM1 10h22' ),
			'account'  => array( __( 'Account', 'nahnu-surecart-theme' ), 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z' ),
			'tools'    => array( __( 'Presets & Tools', 'nahnu-surecart-theme' ), 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z' ),
		);
		?>
		<div class="nst-app" data-tab="<?php echo esc_attr( $tab ); ?>">

			<!-- ── Top bar ──────────────────────────────────────── -->
			<header class="nst-topbar">
				<div class="nst-brand">
					<span class="nst-brand-mark"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></span>
					<div>
						<h1><?php esc_html_e( 'Nahnu Custom Theme for SureCart', 'nahnu-surecart-theme' ); ?></h1>
						<p><?php esc_html_e( 'Unofficial. Styles the cart, checkout and account dashboard.', 'nahnu-surecart-theme' ); ?></p>
					</div>
				</div>
				<nav class="nst-nav" aria-label="<?php esc_attr_e( 'Settings sections', 'nahnu-surecart-theme' ); ?>">
					<?php foreach ( $nav as $key => [ $label, $icon ] ) : ?>
						<button type="button" class="nst-tab<?php echo $key === $tab ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo esc_attr( $icon ); ?>"/></svg>
							<span class="nst-tab-text"><?php echo esc_html( $label ); ?></span>
						</button>
					<?php endforeach; ?>
				</nav>
				<div class="nst-topbar-actions">
					<span class="nst-save-state" id="nst-save-state" data-state="<?php echo esc_attr( '' !== $notice ? $notice : 'clean' ); ?>">
						<span class="nst-save-state-dot"></span>
						<span class="nst-save-state-text">
						<?php
						if ( 'saved' === $notice ) {
							esc_html_e( 'Saved — live on your site', 'nahnu-surecart-theme' ); } elseif ( 'reset' === $notice ) {
							esc_html_e( 'Reset to defaults', 'nahnu-surecart-theme' ); } else {
								esc_html_e( 'No unsaved changes', 'nahnu-surecart-theme' ); }
							?>
						</span>
					</span>
					<button type="submit" form="nst-form" name="nst_reset" value="1" class="nst-btn nst-btn--ghost"
						onclick="return confirm('<?php echo esc_js( __( 'Reset every setting to its default?', 'nahnu-surecart-theme' ) ); ?>')">
						<?php esc_html_e( 'Reset', 'nahnu-surecart-theme' ); ?>
					</button>
					<button type="submit" form="nst-form" class="nst-btn nst-btn--primary" id="nst-save-btn">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
						<?php esc_html_e( 'Save changes', 'nahnu-surecart-theme' ); ?>
					</button>
				</div>
			</header>

			<div class="nst-body">

				<!-- ── Settings ─────────────────────────────────── -->
				<main class="nst-main">
					<form method="post" action="" id="nst-form" class="nst-form">
						<?php wp_nonce_field( 'nahnu_sct_save_settings' ); ?>
						<input type="hidden" name="nst_save" value="1">
						<input type="hidden" name="nst_tab" id="nst_tab" value="<?php echo esc_attr( $tab ); ?>">

						<?php $this->panel_brand(); ?>
						<?php $this->panel_cart(); ?>
						<?php $this->panel_checkout(); ?>
						<?php $this->panel_account(); ?>

						<div class="nst-panel<?php echo 'tools' === $tab ? ' active' : ''; ?>" id="nst-tab-tools">
							<?php Nahnu_SCT_Tools::render_tools_tab(); ?>
						</div>
					</form>

					<div class="nst-panel<?php echo 'tools' === $tab ? ' active' : ''; ?>" id="nst-tab-tools-import">
						<?php Nahnu_SCT_Tools::render_import_form(); ?>
					</div>
				</main>

				<!-- ── Preview ──────────────────────────────────── -->
				<aside class="nst-preview">
					<div class="nst-preview-bar">
						<div class="nst-preview-views">
							<button type="button" class="nst-pv-btn active" data-view="cart"><?php esc_html_e( 'Cart', 'nahnu-surecart-theme' ); ?></button>
							<button type="button" class="nst-pv-btn" data-view="checkout"><?php esc_html_e( 'Checkout', 'nahnu-surecart-theme' ); ?></button>
							<button type="button" class="nst-pv-btn" data-view="dashboard"><?php esc_html_e( 'Dashboard', 'nahnu-surecart-theme' ); ?></button>
						</div>
						<span class="nst-preview-status" id="nst-preview-status"><?php esc_html_e( 'Live preview', 'nahnu-surecart-theme' ); ?></span>
					</div>
					<iframe id="nst-preview" class="nst-preview-frame" title="<?php esc_attr_e( 'Theme preview', 'nahnu-surecart-theme' ); ?>"
						srcdoc="<?php echo esc_attr( Nahnu_SCT_Tools::preview_doc( $s ) ); ?>"></iframe>
					<p class="nst-preview-note"><?php esc_html_e( 'Built from SureCart 4.10 class names and stylesheet rules. A cart customised in the Site Editor may differ slightly.', 'nahnu-surecart-theme' ); ?></p>
				</aside>

			</div>
		</div>
		<?php
	}

	// ── Panels ───────────────────────────────────────────────────────────────

	/**
	 * Open a tab panel.
	 *
	 * @param string $key   Tab key.
	 * @param string $title Panel heading.
	 * @param string $lede  One-line intro.
	 */
	private function panel_open( string $key, string $title, string $lede ): void {
		echo '<div class="nst-panel' . ( $this->tab === $key ? ' active' : '' ) . '" id="nst-tab-' . esc_attr( $key ) . '">';
		echo '<div class="nst-panel-head"><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $lede ) . '</p></div>';
	}

	/** Close a panel opened with panel_open(). */
	private function panel_close(): void {
		echo '</div>';
	}

	/** Brand tab: accent, font, avatar. */
	private function panel_brand(): void {
		$s = $this->s;
		$this->panel_open( 'brand', __( 'Brand', 'nahnu-surecart-theme' ), __( 'The accent colour is used on every button, badge, focus ring and selected state across all three areas.', 'nahnu-surecart-theme' ) );

		$this->section_open( __( 'Accent colour', 'nahnu-surecart-theme' ) );
		$swatches = array(
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
		);
		$sw       = '<div class="nst-swatches">';
		foreach ( $swatches as $hex => $label ) {
			$on  = strtolower( $s['accent_color'] ) === $hex ? ' active' : '';
			$sw .= '<span class="nst-swatch' . $on . '" style="background:' . esc_attr( $hex ) . '" data-color="' . esc_attr( $hex ) . '" title="' . esc_attr( $label ) . '"></span>';
		}
		$sw .= '</div>';
		$this->row( __( 'Accent', 'nahnu-surecart-theme' ), $this->color( 'accent_color' ) . '<input type="text" class="nst-input nst-input--hex" id="accent_color_hex" value="' . esc_attr( $s['accent_color'] ) . '" maxlength="7" aria-label="Hex">', __( 'Buttons, selected states, focus rings. Text colour on it is chosen automatically for contrast.', 'nahnu-surecart-theme' ), 'accent_color' );
		$this->row( __( 'Quick picks', 'nahnu-surecart-theme' ), $sw );
		$this->section_close();

		$this->section_open( __( 'Typography', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Font family', 'nahnu-surecart-theme' ), $this->text( 'font_family', "'Inter', sans-serif", 'lg' ), __( 'Blank inherits your theme font. Any CSS font stack works.', 'nahnu-surecart-theme' ), 'font_family' );
		$this->section_close();

		( new Nahnu_SCT_Avatar() )->render_ui( $s );

		$this->panel_close();
	}

	/** Mini Cart tab. */
	private function panel_cart(): void {
		$this->panel_open( 'cart', __( 'Mini Cart', 'nahnu-surecart-theme' ), __( 'The slide-out cart drawer and the floating cart bubble.', 'nahnu-surecart-theme' ) );

		$this->section_open( __( 'Drawer', 'nahnu-surecart-theme' ) );
		$this->row(
			__( 'Width', 'nahnu-surecart-theme' ),
			$this->select(
				'cart_width',
				array(
					'360px' => __( 'Narrow · 360px', 'nahnu-surecart-theme' ),
					'420px' => __( 'Default · 420px', 'nahnu-surecart-theme' ),
					'480px' => __( 'Wide · 480px', 'nahnu-surecart-theme' ),
					'540px' => __( 'Extra wide · 540px', 'nahnu-surecart-theme' ),
				)
			),
			'',
			'cart_width'
		);
		$this->row( __( 'Divider colour', 'nahnu-surecart-theme' ), $this->color( 'cart_border_color' ), __( 'Lines between items and above the subtotal.', 'nahnu-surecart-theme' ), 'cart_border_color' );
		$this->row( __( 'Backdrop opacity', 'nahnu-surecart-theme' ), $this->range( 'cart_backdrop_opacity', '0', '1', '0.05' ), __( 'How much the page dims behind the open cart.', 'nahnu-surecart-theme' ), 'cart_backdrop_opacity' );
		$this->row( __( 'Checkout button radius', 'nahnu-surecart-theme' ), $this->text( 'cart_btn_radius', '9px' ), '', 'cart_btn_radius' );
		$this->section_close();

		$this->section_open( __( 'Floating bubble', 'nahnu-surecart-theme' ), __( 'The fixed cart button shown while the cart has items.', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Show bubble', 'nahnu-surecart-theme' ), $this->toggle( 'bubble_enabled' ), '', 'bubble_enabled' );
		$this->row( __( 'Size', 'nahnu-surecart-theme' ), '<span class="nst-pair">' . $this->text( 'bubble_width', '56px' ) . '<span class="nst-pair-x">×</span>' . $this->text( 'bubble_height', '56px' ) . '</span>', __( 'Width × height.', 'nahnu-surecart-theme' ), 'bubble_width' );
		$this->row( __( 'Corner radius', 'nahnu-surecart-theme' ), $this->text( 'bubble_radius', '999px' ), __( '999px for a circle, 0 for square.', 'nahnu-surecart-theme' ), 'bubble_radius' );
		$this->row( __( 'Offset from edge', 'nahnu-surecart-theme' ), '<span class="nst-pair">' . $this->text( 'bubble_bottom', '28px' ) . '<span class="nst-pair-x">·</span>' . $this->text( 'bubble_right', '28px' ) . '</span>', __( 'Bottom · right.', 'nahnu-surecart-theme' ), 'bubble_bottom' );
		$this->section_close();

		$this->section_open( __( 'Features', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Quantity controls', 'nahnu-surecart-theme' ), $this->toggle( 'show_qty_controls' ), __( '+ / − stepper on each line item.', 'nahnu-surecart-theme' ), 'show_qty_controls' );
		$this->row( __( 'Coupon field', 'nahnu-surecart-theme' ), $this->toggle( 'show_coupon' ), __( 'Promo-code entry inside the cart.', 'nahnu-surecart-theme' ), 'show_coupon' );
		$this->section_close();

		$this->panel_close();
	}

	/** Checkout tab. */
	private function panel_checkout(): void {
		$this->panel_open( 'checkout', __( 'Checkout', 'nahnu-surecart-theme' ), __( 'Form fields and plan selectors on the checkout page. These are applied through SureCart\'s own design tokens.', 'nahnu-surecart-theme' ) );

		$this->section_open( __( 'Input fields', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Border', 'nahnu-surecart-theme' ), $this->color( 'input_border_color' ), '', 'input_border_color' );
		$this->row( __( 'Border on hover', 'nahnu-surecart-theme' ), $this->color( 'input_border_hover' ), __( 'Focus uses the accent colour.', 'nahnu-surecart-theme' ), 'input_border_hover' );
		$this->row( __( 'Background', 'nahnu-surecart-theme' ), $this->color( 'input_bg' ), '', 'input_bg' );
		$this->row( __( 'Text', 'nahnu-surecart-theme' ), $this->color( 'input_color' ), '', 'input_color' );
		$this->row( __( 'Placeholder', 'nahnu-surecart-theme' ), $this->color( 'input_placeholder' ), '', 'input_placeholder' );
		$this->row( __( 'Corner radius', 'nahnu-surecart-theme' ), $this->text( 'input_radius', '8px' ), '', 'input_radius' );
		$this->row(
			__( 'Shadow', 'nahnu-surecart-theme' ),
			$this->select(
				'input_shadow',
				array(
					'none'                            => __( 'None · flat', 'nahnu-surecart-theme' ),
					'rgba(0,0,0,.05) 0px 1px 2px 0px' => __( 'Subtle · SureCart default', 'nahnu-surecart-theme' ),
				)
			),
			'',
			'input_shadow'
		);
		$this->section_close();

		$this->section_open( __( 'Labels & layout', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Label colour', 'nahnu-surecart-theme' ), $this->color( 'input_label_color' ), '', 'input_label_color' );
		$this->row(
			__( 'Label weight', 'nahnu-surecart-theme' ),
			$this->select(
				'input_label_weight',
				array(
					'400' => __( 'Normal', 'nahnu-surecart-theme' ),
					'500' => __( 'Medium', 'nahnu-surecart-theme' ),
					'600' => __( 'Semibold', 'nahnu-surecart-theme' ),
					'700' => __( 'Bold', 'nahnu-surecart-theme' ),
				)
			),
			'',
			'input_label_weight'
		);
		$this->row( __( 'Row spacing', 'nahnu-surecart-theme' ), $this->text( 'form_row_spacing', '1.125rem' ), __( 'Gap between form rows.', 'nahnu-surecart-theme' ), 'form_row_spacing' );
		$this->row( __( 'Plan card radius', 'nahnu-surecart-theme' ), $this->text( 'choice_radius', '8px' ), __( 'Price / plan selector cards.', 'nahnu-surecart-theme' ), 'choice_radius' );
		$this->section_close();

		$this->panel_close();
	}

	/** Account dashboard tab. */
	private function panel_account(): void {
		$this->panel_open( 'account', __( 'Account', 'nahnu-surecart-theme' ), __( 'The customer dashboard: sidebar, cards, rows and navigation.', 'nahnu-surecart-theme' ) );

		$this->section_open( __( 'Page & sidebar', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Page background', 'nahnu-surecart-theme' ), $this->color( 'dashboard_bg' ), '', 'dashboard_bg' );
		$this->row( __( 'Sidebar background', 'nahnu-surecart-theme' ), $this->color( 'dashboard_sidebar_bg' ), '', 'dashboard_sidebar_bg' );
		$this->row(
			__( 'Sidebar width', 'nahnu-surecart-theme' ),
			$this->select(
				'dashboard_sidebar_w',
				array(
					'260px' => __( 'Narrow · 260px', 'nahnu-surecart-theme' ),
					'300px' => '300px',
					'325px' => __( 'SureCart default · 325px', 'nahnu-surecart-theme' ),
					'360px' => __( 'Wide · 360px', 'nahnu-surecart-theme' ),
				)
			),
			'',
			'dashboard_sidebar_w'
		);
		$this->section_close();

		$this->section_open( __( 'Cards & rows', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Card border', 'nahnu-surecart-theme' ), $this->color( 'dashboard_card_border' ), '', 'dashboard_card_border' );
		$this->row( __( 'Card radius', 'nahnu-surecart-theme' ), $this->text( 'dashboard_card_radius', '10px' ), '', 'dashboard_card_radius' );
		$this->row(
			__( 'Card shadow', 'nahnu-surecart-theme' ),
			$this->select(
				'dashboard_card_shadow',
				array(
					'none'                        => __( 'None', 'nahnu-surecart-theme' ),
					'0 1px 2px rgba(0,0,0,0.06)'  => __( 'Subtle', 'nahnu-surecart-theme' ),
					'0 4px 16px rgba(0,0,0,0.08)' => __( 'Elevated', 'nahnu-surecart-theme' ),
				)
			),
			'',
			'dashboard_card_shadow'
		);
		$this->row( __( 'Row divider', 'nahnu-surecart-theme' ), $this->color( 'dashboard_row_border' ), '', 'dashboard_row_border' );
		$this->row( __( 'Row hover', 'nahnu-surecart-theme' ), $this->color( 'dashboard_row_hover' ), '', 'dashboard_row_hover' );
		$this->section_close();

		$this->section_open( __( 'Navigation', 'nahnu-surecart-theme' ) );
		$this->row( __( 'Active item background', 'nahnu-surecart-theme' ), $this->color( 'tab_active_bg' ), '', 'tab_active_bg' );
		$this->row( __( 'Active item text', 'nahnu-surecart-theme' ), $this->color( 'tab_active_color' ), '', 'tab_active_color' );
		$this->section_close();

		$this->panel_close();
	}
}
