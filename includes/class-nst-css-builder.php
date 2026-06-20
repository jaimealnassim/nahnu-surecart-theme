<?php
/**
 * CSS Builder — compiles plugin settings into SureCart overrides.
 *
 * Every selector here was verified against live rendered HTML from SureCart 4.2.2.
 * !important is used only where inline styles in the block editor output override
 * stylesheet rules — this is documented per-rule below.
 *
 * VERIFIED FROM LIVE HTML:
 * - The mini cart is entirely plain DOM (no Shadow DOM).
 * - Most colors/spacing are hardcoded as inline styles on the elements.
 * - .sc-drawer width is set inline as "width: 525px" — our CSS var has no effect.
 * - Checkout button border-radius is set inline as "border-radius:4px".
 * - Cart count badge styling is fully inline.
 * - Item title, price, amount colors are all inline rgb() values.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

class NST_CSS_Builder {

	private array $s;

	public function __construct( array $settings ) {
		$this->s = $settings;
	}

	public function build(): string {
		$css  = "/* Nahnu SureCart Theme v" . NST_VERSION . " */\n\n";
		$css .= $this->drawer_shell();
		$css .= $this->header();
		$css .= $this->line_items();
		$css .= $this->subtotal_footer();
		$css .= $this->checkout_button();
		$css .= $this->floating_bubble();
		$css .= $this->conditionals();
		$css .= $this->checkout_page();
		$css .= $this->dashboard();
		return $css;
	}

	// ── 1. Drawer shell ─────────────────────────────────────────────────────

	private function drawer_shell(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$w   = $s['cart_width'];

		// .sc-drawer has inline style "font-size:15px;width: 525px"
		// Width token --sc-drawer-width has no effect — inline style wins.
		// We must use !important on width.
		// background-color has no inline style — safe without !important.

		return <<<CSS
/* ── DRAWER SHELL
   .sc-drawer has inline style "width: 525px" set by block editor.
   !important required on width to override it.
   NOTE: We do NOT set z-index or box-shadow — SureCart manages its own
   stacking context. Overriding z-index breaks click routing. ── */
.sc-drawer.sc-cart-drawer {
  width: {$w} !important;
  background-color: #ffffff;
}

CSS;
	}

	// ── 2. Cart header ───────────────────────────────────────────────────────

	private function header(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];

		// Close button: inline style "color:#828c99;line-height:1" — needs !important on color.
		// "Review My Order" p: inline "color: rgb(75, 85, 99)" — needs !important.
		// Cart count badge: inline font-size, font-weight, border-radius, padding — all need !important.

		return <<<CSS
/* ── HEADER
   Close button has inline color:#828c99 — !important needed.
   Cart count badge has inline border-radius:4px and padding — !important needed. ── */

/* Close button / back arrow */
.wp-block-surecart-cart-close-button {
  color: {$acc} !important;
  opacity: 0.7;
  transition: opacity 150ms;
  cursor: pointer;
}

.wp-block-surecart-cart-close-button:hover {
  opacity: 1;
}

/* Cart item count badge
   Inline: font-size:14px; font-weight:600; border-radius:4px; padding:6px 10px */
.wp-block-surecart-cart-count {
  background: {$acc} !important;
  color: #ffffff !important;
  border-radius: 999px !important;
  padding: 3px 10px !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  line-height: 1.4 !important;
}

CSS;
	}

	// ── 3. Line items ────────────────────────────────────────────────────────

	private function line_items(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$r   = $s['input_radius'];

		// .sc-product-line-item: no inline style on the row itself — safe.
		// .wp-block-surecart-cart-line-item-title: inline color rgb(75,85,99) — needs !important.
		// .wp-block-surecart-cart-line-item-price-name: inline color + font-size — needs !important.
		// .wp-block-surecart-cart-line-item-amount: inline color + font-weight — needs !important.
		// .wp-block-surecart-cart-line-item-remove: no inline on hover — hover is safe.
		// Product image: inline border-color:rgb(220,224,230); border-radius:4px — needs !important.

		return <<<CSS
/* ── LINE ITEMS ── */

/* Row — no inline style on the row element itself */
.sc-product-line-item {
  border-bottom: 1px solid {$s['cart_border_color']};
  transition: background 120ms;
}

.sc-product-line-item:last-child {
  border-bottom: none;
}

.sc-product-line-item:hover {
  background: #fafafa;
}

/* Product image
   Inline: border-color:rgb(220,224,230); border-radius:4px; border-width:1px */
.wp-block-surecart-cart-line-item-image {
  border-radius: {$r} !important;
  border-color: {$s['input_border_color']} !important;
}

/* Item title — inline color: rgb(75, 85, 99) */
.wp-block-surecart-cart-line-item-title {
  color: #1a1a1a !important;
  font-weight: 500 !important;
  text-decoration: none !important;
}

.wp-block-surecart-cart-line-item-title:hover {
  color: {$acc} !important;
}

/* Price name / variant — inline color: rgb(130, 140, 153) */
.wp-block-surecart-cart-line-item-price-name,
.wp-block-surecart-cart-line-item-variant,
.wp-block-surecart-cart-line-item-note {
  color: #8a8a8a !important;
  font-size: 13px !important;
}

/* Line item amount — inline color: rgb(75, 85, 99); font-weight: 500 */
.wp-block-surecart-cart-line-item-amount {
  color: #1a1a1a !important;
  font-weight: 600 !important;
}

/* Scratch/original price */
.wp-block-surecart-cart-line-item-scratch-amount {
  color: #c0c0c0 !important;
  text-decoration: line-through;
}

/* Interval text (subscription period) */
.wp-block-surecart-cart-line-item-interval {
  color: #acacac !important;
  font-size: 12px !important;
}

/* Remove button — no inline style on the element, only on its children */
.wp-block-surecart-cart-line-item-remove {
  color: #c0c0c0 !important;
  font-size: 13px !important;
  transition: color 150ms;
  cursor: pointer;
}

.wp-block-surecart-cart-line-item-remove:hover {
  color: #e05252 !important;
}

/* Quantity stepper — sc-input-group, sc-quantity-selector
   No inline styles on the wrapper — safe without !important */
.sc-quantity-selector {
  border: 1px solid {$s['input_border_color']};
  border-radius: {$r};
  display: inline-flex;
  align-items: center;
}

.sc-quantity-selector__decrease,
.sc-quantity-selector__increase {
  color: #6b6b6b;
  cursor: pointer;
  padding: 0 8px;
  transition: background 120ms, color 120ms;
  display: flex;
  align-items: center;
  min-width: 28px;
  min-height: 28px;
  justify-content: center;
}

.sc-quantity-selector__decrease:hover,
.sc-quantity-selector__increase:hover {
  background: #f4f4f4;
  color: #1a1a1a;
}

.sc-quantity-selector__decrease.button--disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.sc-quantity-selector__control {
  border: none !important;
  outline: none !important;
  text-align: center;
  width: 32px;
  font-weight: 600;
  font-size: 13px;
  color: #1a1a1a;
  background: transparent;
  -moz-appearance: textfield;
}

.sc-quantity-selector__control::-webkit-outer-spin-button,
.sc-quantity-selector__control::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

CSS;
	}

	// ── 4. Subtotal / footer ─────────────────────────────────────────────────

	private function subtotal_footer(): string {
		$s = $this->s;

		// Subtotal section border-top: inline "border-top-color:#e5e7eb;border-top-width:1px" — needs !important.
		// Subtotal label "Subtotal" p: inline "color: rgb(75, 85, 99); font-size: 18px" — needs !important.
		// Subtotal amount: inline "color:#4b5563;font-size:18px;font-weight:500" — needs !important.
		// "Taxes & shipping" p: inline color rgb(130,140,153) — needs !important.

		return <<<CSS
/* ── SUBTOTAL FOOTER
   The section wrapper has inline border-top-color:#e5e7eb — !important needed. ── */

.wp-block-surecart-slide-out-cart-items-subtotal {
  border-top: 1px solid {$s['cart_border_color']} !important;
}

/* "Subtotal" label — inline color: rgb(75, 85, 99); font-size: 18px */
.wp-block-surecart-slide-out-cart-items-subtotal .wp-block-paragraph:first-child {
  color: #1a1a1a !important;
  font-size: 0.9375rem !important;
  font-weight: 600 !important;
}

/* "Taxes & shipping" note */
.wp-block-surecart-slide-out-cart-items-subtotal .wp-block-paragraph + .wp-block-paragraph {
  color: #acacac !important;
  font-size: 12px !important;
}

/* Subtotal amount — inline color:#4b5563; font-size:18px; font-weight:500 */
.wp-block-surecart-cart-subtotal-amount {
  color: #1a1a1a !important;
  font-size: 1.125rem !important;
  font-weight: 700 !important;
}

.wp-block-surecart-cart-subtotal-scratch-amount {
  color: #c0c0c0 !important;
  text-decoration: line-through;
  font-size: 1rem !important;
}

CSS;
	}

	// ── 5. Checkout button ───────────────────────────────────────────────────

	private function checkout_button(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$r   = $s['cart_btn_radius'];

		// .wp-block-button__link inside .sc-cart-items-submit__wrapper:
		// Has inline "border-radius:4px" — needs !important.
		// Background is set by SureCart stylesheet — needs !important to override.

		return <<<CSS
/* ── CHECKOUT BUTTON
   Inline style "border-radius:4px" on the <a> tag — !important required.
   Background color set by SureCart stylesheet — !important required. ── */

.sc-cart-items-submit__wrapper .wp-block-button__link,
.sc-cart-items-submit__wrapper .wp-block-button__link:visited {
  background: {$acc} !important;
  border-radius: {$r} !important;
  color: #ffffff !important;
  font-weight: 600;
  letter-spacing: 0.02em;
  transition: opacity 150ms;
}

.sc-cart-items-submit__wrapper .wp-block-button__link:hover {
  opacity: 0.88;
  background: {$acc} !important;
}

/* Spinner inside the button — hidden when not loading */
.sc-spinner {
  display: none;
}

.sc-button__link--busy .sc-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: nst-spin 0.7s linear infinite;
}

@keyframes nst-spin {
  to { transform: rotate(360deg); }
}

CSS;
	}

	// ── 6. Floating bubble ───────────────────────────────────────────────────

	private function floating_bubble(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];

		// .wp-block-surecart-cart-icon is plain DOM with no inline styles — safe.

		return <<<CSS
/* ── FLOATING CART BUBBLE
   Plain DOM, no inline styles — all safe without !important.
   Source: blocks-next/build/blocks/cart-icon/style-index.css ── */

.wp-block-surecart-cart-icon {
  background: {$acc} !important;
  color: #ffffff !important;
  width: {$s['bubble_width']} !important;
  height: {$s['bubble_height']} !important;
  border-radius: {$s['bubble_radius']} !important;
  bottom: {$s['bubble_bottom']} !important;
  right: {$s['bubble_right']} !important;
  z-index: 300 !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.18), 0 1px 4px rgba(0, 0, 0, 0.12);
  transition: transform 180ms ease, box-shadow 180ms ease;
}

.wp-block-surecart-cart-icon:hover {
  transform: scale(1.06);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.22);
}

/* Counter badge on bubble */
.wp-block-surecart-cart-icon__count,
.wp-block-surecart-cart-count-bubble {
  background: #ffffff !important;
  color: {$acc} !important;
  border-radius: 999px !important;
  font-weight: 800 !important;
  min-width: 18px !important;
  height: 18px !important;
  padding: 0 4px !important;
  font-size: 10px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  border: 2px solid #ffffff !important;
}

CSS;
	}

	// ── 7. Conditionals ──────────────────────────────────────────────────────

	private function conditionals(): string {
		$s   = $this->s;
		$css = '';

		if ( empty( $s['bubble_enabled'] ) ) {
			$css .= <<<CSS
/* Hide floating bubble */
.wp-block-surecart-cart-icon {
  display: none !important;
}

CSS;
		}

		if ( empty( $s['show_qty_controls'] ) ) {
			// From live HTML the element is:
			// <div class="sc-input-group sc-input-group-sm sc-quantity-selector
			//   wp-block-surecart-cart-line-item-quantity"
			//   data-wp-bind--hidden="!state.isEditable">
			//
			// WP Interactivity toggles the [hidden] attribute — it does NOT
			// set inline style="display:...". So display:none !important
			// on our class selector wins cleanly.
			//
			// We target by THREE of its classes for maximum specificity,
			// and also target the parent block wrapper as a belt-and-braces.
			$css .= <<<CSS
/* Hide quantity +/− stepper.
   Exact classes from live HTML:
   div.sc-input-group.sc-input-group-sm.sc-quantity-selector.wp-block-surecart-cart-line-item-quantity
   Targeting by multiple classes for specificity. */
.sc-quantity-selector,
.wp-block-surecart-cart-line-item-quantity,
.sc-input-group.sc-quantity-selector,
div.sc-input-group-sm.sc-quantity-selector {
  display: none !important;
}

CSS;
		}

		if ( empty( $s['show_coupon'] ) ) {
			// Coupon block class verified from SureCart blocks-next source.
			// No inline style on this element.
			$css .= <<<CSS
/* Hide coupon section.
   Class from blocks-next/build/blocks/cart-coupon/style-index.css */
.wp-block-surecart-slide-out-cart-coupon {
  display: none !important;
}

CSS;
		}

		return $css;
	}

	// ── 8. Checkout page ─────────────────────────────────────────────────────

	private function checkout_page(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];

		// NOTE: Checkout page uses Stencil Web Components (Shadow DOM).
		// CSS custom properties are the ONLY reliable way in.
		// These are verified against SureCart 4.2.2 source.

		$focus_ring = $this->hex_to_rgba( $acc, 0.22 );

		$font_line = '';
		if ( ! empty( $s['font_family'] ) ) {
			$font_line = "\n  --sc-font-sans: " . esc_attr( $s['font_family'] ) . ', -apple-system, BlinkMacSystemFont, sans-serif;';
		}

		return <<<CSS
/* ── CHECKOUT PAGE (Shadow DOM — tokens only)
   Verified against sc-button.css, sc-input.css, sc-choice-container.css,
   sc-radio.css, sc-checkbox.css, theme-base.css in SureCart 4.2.2. ── */

:root {
  /* Primary color — controls submit button, choices, radios, checkboxes,
     input focus border. Source: sc-button.css --primary-background: var(--sc-color-primary-500) */
  --sc-color-primary-500:          {$acc};
  --sc-color-primary-text:         #ffffff;

  /* Focus ring — source: theme-base.css + sc-input.css */
  --sc-focus-ring-color-primary:   {$focus_ring};
  --sc-focus-ring-width:           2px;{$font_line}

  /* Input fields — source: sc-input.css reads all of these */
  --sc-input-border-color:         {$s['input_border_color']};
  --sc-input-border-color-hover:   {$s['input_border_hover']};
  --sc-input-border-color-focus:   {$acc};
  --sc-input-border-radius-medium: {$s['input_radius']};
  --sc-input-border-radius-large:  {$s['input_radius']};
  --sc-input-background-color:     {$s['input_bg']};
  --sc-input-box-shadow:           {$s['input_shadow']};
  /* --sc-input-font-size-medium intentionally omitted — changing this
     affects em-based height/padding calculations inside sc-input shadow DOM */
  --sc-input-color:                {$s['input_color']};
  --sc-input-placeholder-color:    {$s['input_placeholder']};
  --sc-input-label-color:          {$s['input_label_color']};
  --sc-input-label-font-weight:    {$s['input_label_weight']};

  /* Price choice cards — source: sc-choice-container.css */
  --sc-choice-border-radius:       {$s['choice_radius']};

  /* Form spacing — source: theme-base.css */
  --sc-form-row-spacing:           {$s['form_row_spacing']};
}

CSS;
	}

	// ── 9. Account dashboard ─────────────────────────────────────────────────

	private function dashboard(): string {
		$s       = $this->s;
		$side_w  = $s['dashboard_sidebar_w'];

		return <<<CSS
/* ── ACCOUNT DASHBOARD
   Plain DOM page template: body.page-template-template-surecart-dashboard
   Source: dist/templates/customer-dashboard.css ── */

body.page-template-template-surecart-dashboard {
  background: {$s['dashboard_bg']};
}

/* Sidebar ::before pseudo-element — source: customer-dashboard.css */
body.page-template-template-surecart-dashboard.has-columns::before {
  background: {$s['dashboard_sidebar_bg']} !important;
  width: {$side_w} !important;
  box-shadow: none !important;
  border-right: 1px solid {$s['dashboard_card_border']};
}

/* Sidebar width must match ::before — both need !important */
@media screen and (min-width: 782px) {
  body.page-template-template-surecart-dashboard .dashboard-left {
    flex: 0 0 {$side_w} !important;
    max-width: {$side_w} !important;
  }
}

/* Dashboard tokens — sc-card, sc-stacked-list-row, sc-tab all inherit these */
:root {
  --sc-card-background-color:       #ffffff;
  --sc-card-border-color:           {$s['dashboard_card_border']};
  --sc-card-border-radius:          {$s['dashboard_card_radius']};
  --sc-card-padding:                1.5rem;
  --sc-shadow-small:                {$s['dashboard_card_shadow']};
  --sc-stacked-list-border-color:   {$s['dashboard_row_border']};
  --sc-stacked-list-row-hover-color:{$s['dashboard_row_hover']};
  --sc-tab-active-background:       {$s['tab_active_bg']};
  --sc-tab-active-color:            {$s['tab_active_color']};
  --sc-dashbaord-module-heading-size: 1.125rem;
  --sc-dashbaord-module-heading-weight: 700;
}

CSS;
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	private function hex_to_rgba( string $hex, float $alpha ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		return "rgba({$r}, {$g}, {$b}, {$alpha})";
	}
}
