<?php
/**
 * CSS Builder — compiles plugin settings into SureCart overrides.
 *
 * Verified against SureCart 4.10.0 source (and confirmed unchanged from 4.2.2
 * for every selector and token used here). Three systems, three strategies:
 *
 *  1. MINI CART  — WordPress blocks (packages/blocks-next). Plain DOM.
 *     Strategy: set SureCart's own CSS custom properties, scoped to the cart
 *     wrapper so the checkout is unaffected. On a stock cart template this
 *     themes everything with zero !important. A second block of !important
 *     rules follows for carts customised in the Site Editor, where the block
 *     editor writes hard-coded inline colours onto the elements.
 *
 *  2. CHECKOUT   — Stencil web components (dist/components). Shadow DOM.
 *     Strategy: tokens on :root. Custom properties inherit through shadow
 *     roots; ordinary selectors cannot reach inside.
 *
 *  3. DASHBOARD  — PHP page template + Stencil components. Plain DOM shell.
 *     Strategy: scoped body selectors for the shell, tokens for the components.
 *
 * Every token below is read by a SureCart stylesheet; the reading file is
 * cited next to it.
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turns sanitized settings into the CSS printed on the front end.
 *
 * Every selector and token is verified against the SureCart 4.10.0 source;
 * see ROADMAP.md › Conventions.
 */
class Nahnu_SCT_CSS_Builder {

	/**
	 * Sanitized settings.
	 *
	 * @var array
	 */
	private array $s;

	/**
	 * Constructor.
	 *
	 * @param array $settings Sanitized settings.
	 */
	public function __construct( array $settings ) {
		$this->s = $settings;
	}

	/**
	 * Concatenate every section into one stylesheet.
	 *
	 * @return string CSS.
	 */
	public function build(): string {
		$css  = '/* Nahnu Custom Theme for SureCart v' . NAHNU_SCT_VERSION . " — generated, do not edit */\n\n";
		$css .= $this->cart_tokens();
		$css .= $this->cart_overrides();
		$css .= $this->cart_quantity();
		$css .= $this->cart_checkout_button();
		$css .= $this->cart_bubble();
		$css .= $this->cart_conditionals();
		$css .= $this->checkout_page();
		$css .= $this->dashboard();
		return $css;
	}

	// ── 1a. Mini cart — tokens (clean path) ──────────────────────────────────

	/**
	 * Mini cart: design tokens scoped to .sc-cart-wrapper (clean path).
	 */
	private function cart_tokens(): string {
		$s    = $this->s;
		$acc  = $s['accent_color'];
		$main = '#1a1a1a'; // Title, amounts, subtotal, "Review My Order".
		$help = '#8a8a8a'; // price name, variant, note, interval, scratch, remove, close, taxes line.

		// .sc-cart-wrapper is the outermost element rendered by
		// blocks-next/build/blocks/cart/view.php and wraps BOTH the drawer and
		// its backdrop, so tokens set here reach every cart element and nothing
		// else on the page.
		return "/* ── MINI CART: TOKENS
   Scoped to .sc-cart-wrapper (cart/view.php) so the checkout page keeps its
   own values. On the stock cart template every colour below flows through
   SureCart's own block CSS with no !important needed. ── */
.sc-cart-wrapper {
  /* Read by blocks/cart/style-index.css (drawer root), cart-line-item-title,
     cart-line-item-amount, cart-subtotal-amount. */
  --sc-cart-main-label-text-color: {$main};

  /* Read by cart-close-button, cart-line-item-price-name, -variant, -note,
     -interval, -scratch-amount, -remove, -fees, cart-subtotal-scratch-amount,
     and the \"Taxes & shipping\" paragraph in templates/parts/cart.html. */
  --sc-input-help-text-color:      {$help};

  /* Read by styles/drawer.css (.sc-drawer background) and
     blocks/cart-count/style-index.css. */
  --sc-panel-background-color:     #ffffff;

  /* Read by styles/drawer.css and blocks/cart/style-index.css. */
  --sc-drawer-border:              1px solid {$s['cart_border_color']};
  --sc-drawer-backdrop-opacity:    {$s['cart_backdrop_opacity']};
  --sc-drawer-transition-speed:    {$s['cart_transition_speed']};

  /* Quantity stepper input text — read via cart-line-item-quantity
     { color: var(--sc-input-color) } with the control set to inherit. */
  --sc-input-color:                {$main};
}

/* Drawer width is rendered inline (cart/controller.php: block.json gives
   \"width\" a default of 525px, so the inline style is always present).
   This is the one place in the cart where !important is unavoidable. */
.sc-drawer.sc-cart-drawer {
  width: {$s['cart_width']} !important;
}

";
	}

	// ── 1b. Mini cart — inline-style overrides (customised templates) ────────

	/**
	 * Mini cart: !important fallbacks for templates customised in the Site Editor.
	 */
	private function cart_overrides(): string {
		$s    = $this->s;
		$acc  = $s['accent_color'];
		$r    = $s['input_radius'];
		$main = '#1a1a1a';
		$help = '#8a8a8a';
		$mute = '#c0c0c0';

		// When a store owner edits the cart in the Site Editor, the block editor
		// serialises colours/typography as inline style="" on each element
		// (has-text-color + wp-elements-* classes). Inline styles beat any
		// stylesheet rule, so these need !important. They are harmless on a
		// stock template where the tokens above already did the job.
		return "/* ── MINI CART: INLINE-STYLE OVERRIDES
   Only needed when the cart was customised in the Site Editor, which writes
   inline colours. Same values as the tokens above. ── */

.sc-cart-wrapper .wp-block-surecart-cart-close-button {
  color: {$acc} !important;
  opacity: 0.7;
  transition: opacity 150ms;
}
.sc-cart-wrapper .wp-block-surecart-cart-close-button:hover {
  opacity: 1;
}

/* Count pill in the header. Stock template writes border-radius:4px and
   padding inline (templates/parts/cart.html). */
.sc-cart-wrapper .wp-block-surecart-cart-count {
  background: {$acc} !important;
  color: {$this->foreground( $acc )} !important;
  border: none !important;
  border-radius: 999px !important;
  padding: 3px 10px !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  line-height: 1.4 !important;
}

.sc-cart-wrapper .sc-product-line-item {
  border-bottom: 1px solid {$s['cart_border_color']};
  transition: background 120ms;
}
.sc-cart-wrapper .sc-product-line-item:last-child {
  border-bottom: none;
}
.sc-cart-wrapper .sc-product-line-item:hover {
  background: #fafafa;
}

/* Stock template writes border-width/radius inline. */
.sc-cart-wrapper .wp-block-surecart-cart-line-item-image {
  border-radius: {$r} !important;
  border-color: {$s['input_border_color']} !important;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-title {
  color: {$main} !important;
  font-weight: 500 !important;
  text-decoration: none !important;
}
.sc-cart-wrapper .wp-block-surecart-cart-line-item-title:hover {
  color: {$acc} !important;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-price-name,
.sc-cart-wrapper .wp-block-surecart-cart-line-item-variant,
.sc-cart-wrapper .wp-block-surecart-cart-line-item-note,
.sc-cart-wrapper .wp-block-surecart-cart-line-item-fees {
  color: {$help} !important;
  font-size: 13px !important;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-amount {
  color: {$main} !important;
  font-weight: 600 !important;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-scratch-amount,
.sc-cart-wrapper .wp-block-surecart-cart-subtotal-scratch-amount {
  color: {$mute} !important;
  text-decoration: line-through;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-interval {
  color: {$help} !important;
  font-size: 12px !important;
}

.sc-cart-wrapper .wp-block-surecart-cart-line-item-remove {
  color: {$mute} !important;
  font-size: 13px !important;
  transition: color 150ms;
}
.sc-cart-wrapper .wp-block-surecart-cart-line-item-remove:hover {
  color: #e05252 !important;
}

/* Subtotal block. Stock template writes border-top inline on the wrapping
   group and font-size/weight inline on the amount. */
.sc-cart-wrapper .wp-block-surecart-slide-out-cart-items-subtotal {
  border-top: 1px solid {$s['cart_border_color']} !important;
}
.sc-cart-wrapper .wp-block-surecart-slide-out-cart-items-subtotal .wp-block-paragraph:first-child {
  color: {$main} !important;
  font-size: 0.9375rem !important;
  font-weight: 600 !important;
}
.sc-cart-wrapper .wp-block-surecart-slide-out-cart-items-subtotal .wp-block-paragraph + .wp-block-paragraph {
  color: {$help} !important;
  font-size: 12px !important;
}
.sc-cart-wrapper .wp-block-surecart-cart-subtotal-amount {
  color: {$main} !important;
  font-size: 1.125rem !important;
  font-weight: 700 !important;
}
.sc-cart-wrapper .wp-block-surecart-cart-subtotal-scratch-amount {
  font-size: 1rem !important;
}

";
	}

	// ── 1c. Mini cart — quantity stepper ─────────────────────────────────────

	/**
	 * Mini cart: quantity stepper.
	 *
	 * SureCart's own stepper styles use two- and three-class selectors, so
	 * every selector here is prefixed with the cart wrapper class to be one
	 * class more specific than SureCart's, without !important.
	 */
	private function cart_quantity(): string {
		$s = $this->s;
		$r = $s['input_radius'];

		return "/* ── MINI CART: QUANTITY STEPPER
   Selectors match SureCart's specificity +1 (quantity-selector.css,
   input-group.css, cart-line-item-quantity/style-index.css). ── */

/* SureCart strips the border on the cart stepper; put it back. */
.sc-cart-wrapper .wp-block-surecart-cart-line-item-quantity.sc-input-group {
  border: 1px solid {$s['input_border_color']};
  border-radius: {$r};
  box-shadow: none;
}
.sc-cart-wrapper .wp-block-surecart-cart-line-item-quantity.sc-input-group:focus-within {
  border-color: {$s['accent_color']};
}

/* +/- buttons. input-group.css gives .sc-input-group-text opacity:.5. */
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__decrease,
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__increase {
  color: #6b6b6b;
  opacity: 1;
  transition: background 120ms, color 120ms;
}
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__decrease:hover,
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__increase:hover {
  background: #f4f4f4;
  color: #1a1a1a;
}
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__decrease.button--disabled,
.sc-cart-wrapper .sc-input-group.sc-quantity-selector .sc-quantity-selector__increase.button--disabled {
  opacity: 0.35;
  background: transparent;
}

.sc-cart-wrapper .sc-input-group.sc-quantity-selector input[type=number].sc-quantity-selector__control {
  font-weight: 600;
}

";
	}

	// ── 1d. Mini cart — checkout button ──────────────────────────────────────

	/**
	 * Mini cart: checkout button.
	 */
	private function cart_checkout_button(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$fg  = $this->foreground( $acc );

		// blocks/cart-items-submit/style-index.css sets background from
		// --sc-color-primary-500 (which we set on :root) but hard-codes
		// color:#fff and the stock template writes border-radius:4px inline.
		return "/* ── MINI CART: CHECKOUT BUTTON
   Stock template writes border-radius inline; SureCart hard-codes color:#fff. ── */
.sc-cart-items-submit__wrapper .wp-block-button__link,
.sc-cart-items-submit__wrapper .wp-block-button__link:visited {
  background: {$acc} !important;
  border-radius: {$s['cart_btn_radius']} !important;
  color: {$fg} !important;
  font-weight: 600;
  letter-spacing: 0.02em;
  transition: opacity 150ms;
}
.sc-cart-items-submit__wrapper .wp-block-button__link:hover {
  opacity: 0.88;
  background: {$acc} !important;
}

";
	}

	// ── 1e. Floating bubble ──────────────────────────────────────────────────

	/**
	 * Floating cart bubble tokens.
	 */
	private function cart_bubble(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$fg  = $this->foreground( $acc );

		// blocks/cart-icon/view.php renders with get_block_wrapper_attributes()
		// and NO inline styles. blocks/cart-icon/style-index.css reads every
		// visual property from a --sc-cart-icon-* / --sc-cart-counter-* token,
		// so setting the tokens on the element is all that is needed.
		return "/* ── FLOATING CART BUBBLE
   No inline styles on this block; cart-icon/style-index.css reads tokens. ── */
.wp-block-surecart-cart-icon {
  --sc-cart-icon-background:       {$acc};
  --sc-cart-icon-color:            {$fg};
  --sc-cart-icon-width:            {$s['bubble_width']};
  --sc-cart-icon-height:           {$s['bubble_height']};
  --sc-cart-icon-border-radius:    {$s['bubble_radius']};
  --sc-cart-icon-bottom:           {$s['bubble_bottom']};
  --sc-cart-icon-right:            {$s['bubble_right']};
  --sc-cart-icon-box-shadow:       none;

  --sc-cart-counter-background:    {$fg};
  --sc-cart-counter-color:         {$acc};
  --sc-cart-counter-border-radius: 999px;

  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.18), 0 1px 4px rgba(0, 0, 0, 0.12);
  transition: transform 180ms ease, box-shadow 180ms ease, opacity 150ms ease;
}
/* style-index.css hard-codes the SVG to --sc-color-white; follow the accent. */
.wp-block-surecart-cart-icon .wp-block-surecart-cart-icon__icon svg {
  color: {$fg};
}
.wp-block-surecart-cart-icon:hover {
  transform: scale(1.06);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.22);
}
.wp-block-surecart-cart-icon .wp-block-surecart-cart-icon__count {
  font-weight: 800;
  font-size: 10px;
  line-height: 18px;
  padding: 0 6px;
  border: 2px solid {$fg};
}

";
	}

	// ── 1f. Conditionals ─────────────────────────────────────────────────────

	/**
	 * Mini cart: hide quantity controls / coupon field when switched off.
	 */
	private function cart_conditionals(): string {
		$s   = $this->s;
		$css = '';

		if ( empty( $s['bubble_enabled'] ) ) {
			$css .= '/* Hide floating bubble */
.wp-block-surecart-cart-icon {
  display: none !important;
}

';
		}

		if ( empty( $s['show_qty_controls'] ) ) {
			// The stepper element carries both classes:
			// .sc-input-group.sc-quantity-selector.wp-block-surecart-cart-line-item-quantity
			// Visibility is toggled by WP Interactivity via the [hidden] attribute,
			// never an inline display, so !important here wins cleanly.
			$css .= '/* Hide quantity stepper (cart-line-item-quantity/static.php) */
.sc-cart-wrapper .wp-block-surecart-cart-line-item-quantity,
.sc-cart-wrapper .sc-input-group.sc-quantity-selector {
  display: none !important;
}

';
		}

		if ( empty( $s['show_coupon'] ) ) {
			$css .= '/* Hide coupon block (cart-coupon/style-index.css) */
.sc-cart-wrapper .wp-block-surecart-slide-out-cart-coupon {
  display: none !important;
}

';
		}

		return $css;
	}

	// ── 2. Checkout page ─────────────────────────────────────────────────────

	/**
	 * Checkout page tokens on :root (Shadow DOM components read them there).
	 */
	private function checkout_page(): string {
		$s   = $this->s;
		$acc = $s['accent_color'];
		$fg  = $this->foreground( $acc );

		$focus_ring = $this->hex_to_rgba( $acc, 0.22 );

		$font_line = '';
		if ( ! empty( $s['font_family'] ) ) {
			$font_line = "\n  --sc-font-sans: " . esc_attr( $s['font_family'] ) . ', -apple-system, BlinkMacSystemFont, sans-serif;';
		}

		// SureCart's own StylesService::register() writes --sc-color-primary-500,
		// --sc-focus-ring-color-primary, --sc-input-border-color-focus and
		// --sc-color-primary-text on :root as an inline style attached to its
		// 'surecart-theme-base' handle. Ours is enqueued at priority 999 and so
		// prints later in <head>, winning at equal specificity.
		return "/* ── CHECKOUT PAGE (Shadow DOM — tokens only)
   Read by sc-button.css, sc-input.css, sc-choice-container.css, sc-radio.css,
   sc-checkbox.css via inheritance through the shadow root. ── */
:root {
  --sc-color-primary-500:          {$acc};
  --sc-color-primary-text:         {$fg};

  --sc-focus-ring-color-primary:   {$focus_ring};
  --sc-focus-ring-width:           {$s['focus_ring_width']};{$font_line}

  --sc-input-border-color:         {$s['input_border_color']};
  --sc-input-border-color-hover:   {$s['input_border_hover']};
  --sc-input-border-color-focus:   {$acc};
  --sc-input-border-radius-medium: {$s['input_radius']};
  --sc-input-border-radius-large:  {$s['input_radius']};
  --sc-input-background-color:     {$s['input_bg']};
  --sc-input-box-shadow:           {$s['input_shadow']};
  --sc-input-color:                {$s['input_color']};
  --sc-input-placeholder-color:    {$s['input_placeholder']};
  --sc-input-label-color:          {$s['input_label_color']};
  --sc-input-label-font-weight:    {$s['input_label_weight']};

  /* sc-choice-container.css / styles/choice.css */
  --sc-choice-border-radius:       {$s['choice_radius']};

  /* theme-base.css; gap between form rows */
  --sc-form-row-spacing:           {$s['form_row_spacing']};
}

";
	}

	// ── 3. Account dashboard ─────────────────────────────────────────────────

	/**
	 * Customer account dashboard.
	 */
	private function dashboard(): string {
		$s      = $this->s;
		$side_w = $s['dashboard_sidebar_w'];

		// dist/templates/customer-dashboard.css. SureCart 4.10 switched the
		// sidebar ::before to logical properties (inset-inline-start) and added
		// an RTL rule, so the border here uses the logical side too.
		return "/* ── ACCOUNT DASHBOARD
   Plain DOM page template: body.page-template-template-surecart-dashboard
   (dist/templates/customer-dashboard.css) ── */
body.page-template-template-surecart-dashboard {
  background: {$s['dashboard_bg']};
}

/* The sidebar panel is a ::before on <body>, fixed at 325px with a shadow. */
body.page-template-template-surecart-dashboard.has-columns::before {
  background: {$s['dashboard_sidebar_bg']} !important;
  width: {$side_w} !important;
  box-shadow: none !important;
  border-inline-end: 1px solid {$s['dashboard_card_border']};
}

/* .dashboard-left is flex: 0 0 325px — must match the ::before width. */
@media screen and (min-width: 782px) {
  body.page-template-template-surecart-dashboard .dashboard-left {
    flex: 0 0 {$side_w} !important;
    max-width: {$side_w} !important;
  }
}

/* Replace the hard-coded #1e40af focus ring on the home link. */
body.page-template-template-surecart-dashboard .sc-link-home:focus {
  box-shadow: 0 0 0 var(--sc-focus-ring-width) var(--sc-focus-ring-color-primary);
}

/* Read by sc-card.css, sc-stacked-list-row.css, sc-tab.css, sc-dashboard-module.css */
:root {
  --sc-card-background-color:          #ffffff;
  --sc-card-border-color:              {$s['dashboard_card_border']};
  --sc-card-border-radius:             {$s['dashboard_card_radius']};
  --sc-card-padding:                   1.5rem;
  --sc-shadow-small:                   {$s['dashboard_card_shadow']};
  --sc-stacked-list-border-color:      {$s['dashboard_row_border']};
  --sc-stacked-list-row-hover-color:   {$s['dashboard_row_hover']};
  --sc-tab-active-background:          {$s['tab_active_bg']};
  --sc-tab-active-color:               {$s['tab_active_color']};
  --sc-dashbaord-module-heading-size:  1.125rem; /* sic — SureCart's spelling */
  --sc-dashbaord-module-heading-weight: 700;
}

";
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Black or white foreground for a given background, using the same YIQ
	 * brightness test as SureCart's ColorService::calculateForegroundColor()
	 * (threshold 180) so our buttons agree with SureCart's own.
	 *
	 * @param string $hex Background colour.
	 */
	private function foreground( string $hex ): string {
		[ $r, $g, $b ] = $this->hex_to_rgb( $hex );
		$brightness    = ( $r * 0.299 ) + ( $g * 0.587 ) + ( $b * 0.114 );
		return $brightness > 180 ? '#000000' : '#ffffff';
	}

	/**
	 * #rrggbb → rgba() string.
	 *
	 * @param string $hex   Hex colour.
	 * @param float  $alpha Alpha 0–1.
	 */
	private function hex_to_rgba( string $hex, float $alpha ): string {
		[ $r, $g, $b ] = $this->hex_to_rgb( $hex );
		return "rgba({$r}, {$g}, {$b}, {$alpha})";
	}

	/**
	 * #rrggbb → [r, g, b].
	 *
	 * @param string $hex Hex colour.
	 * @return int[]
	 */
	private function hex_to_rgb( string $hex ): array {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}
