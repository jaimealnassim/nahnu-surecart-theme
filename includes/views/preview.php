<?php
/**
 * Live-preview document, injected into the settings page iframe via srcdoc.
 *
 * Markup mirrors the real SureCart 4.10.0 output (templates/parts/cart.html,
 * template-surecart-dashboard.php, checkout form) closely enough that the
 * plugin's generated CSS applies to it exactly as it does on the front end.
 * No scripts — the parent page drives it directly (same-origin srcdoc).
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

// This file is a standalone HTML document rendered into an <iframe srcdoc> on
// the settings page (see Nahnu_SCT_Tools::preview_doc()). Its styles go through
// the normal registration APIs and are printed here with wp_print_styles().
wp_register_style( 'nahnu-sct-preview', NAHNU_SCT_URL . 'assets/css/preview-base.css', array(), NAHNU_SCT_VERSION );
wp_add_inline_style( 'nahnu-sct-preview', wp_strip_all_tags( ( new Nahnu_SCT_CSS_Builder( $nahnu_sct_preview_settings ) )->build() ) );
$nahnu_sct_avatar_url = isset( $preview_avatar_url ) ? $preview_avatar_url : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<?php wp_print_styles( 'nahnu-sct-preview' ); ?>
</head>
<body>

<!-- ═══════════════ CART ═══════════════ -->
<div id="pv-cart" class="pv-view is-active">
<div class="pv-stage">
	<div class="pv-page">
		<h1>Your store</h1>
		<p>Product page content sits behind the cart drawer.</p>
		<div class="pv-bar"></div><div class="pv-bar s"></div><div class="pv-bar"></div>
	</div>

	<div class="sc-cart-wrapper">
		<div class="sc-drawer sc-cart-drawer wp-block-surecart-slide-out-cart open" style="font-size:15px;width:525px" role="dialog">

			<div class="wp-block-group is-content-justification-space-between is-nowrap is-layout-flex" style="padding:1.5em 2em 0">
				<div class="wp-block-surecart-cart-close-button" style="line-height:1" role="button">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
				</div>
				<p class="wp-block-paragraph" style="margin:0;padding:0;font-size:16px;line-height:1;font-weight:500">Review My Order</p>
				<span class="wp-block-surecart-cart-count" style="font-size:14px;font-weight:600;line-height:1;border-radius:4px;padding:6px 10px">2</span>
			</div>

			<div class="wp-block-surecart-slide-out-cart-line-items is-layout-flow" style="padding:2em" role="list">

				<div class="sc-product-line-item" role="listitem">
					<div class="sc-product-line-item__content">
						<div class="wp-block-group"><div class="wp-block-group__inner-container">
							<div class="is-layout-flex">
								<figure class="sc-cart-line-item-image-wrap" style="margin:0">
									<img class="sc-is-covered wp-block-surecart-cart-line-item-image" alt="" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Crect width='80' height='80' fill='%23f3f4f6'/%3E%3Cpath d='M40 14l20 10v20L40 66 20 44V24z' fill='%2316a34a'/%3E%3Cpath d='M30 38l8 8 14-16' stroke='%23fff' stroke-width='4' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E" style="aspect-ratio:1/1;border-radius:4px;border-width:1px;border-style:solid">
								</figure>
								<div class="pv-li-body">
									<div class="pv-li-top">
										<div>
											<a class="wp-block-surecart-cart-line-item-title" href="#" style="font-weight:500;line-height:1.4;text-decoration:none">Sample Product</a>
											<div class="wp-block-surecart-cart-line-item-price-name" style="font-size:14px;line-height:1.4">Annual plan</div>
										</div>
										<div class="pv-li-right">
											<div class="wp-block-surecart-cart-line-item-scratch-amount">$599.00</div>
											<div class="wp-block-surecart-cart-line-item-amount" style="font-weight:500">$499.99</div>
											<div class="wp-block-surecart-cart-line-item-interval" style="font-size:14px">/ year</div>
										</div>
									</div>
									<div class="pv-li-bottom">
										<div class="sc-input-group sc-input-group-sm sc-quantity-selector wp-block-surecart-cart-line-item-quantity">
											<div class="sc-input-group-text sc-quantity-selector__decrease" role="button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
											<input type="number" class="sc-form-control sc-quantity-selector__control" value="1" readonly>
											<div class="sc-input-group-text sc-quantity-selector__increase" role="button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
										</div>
										<div class="wp-block-surecart-cart-line-item-remove" role="button" style="font-size:14px;font-weight:400">
											<svg class="wp-block-surecart-cart-line-item-remove__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
											<span class="wp-block-surecart-cart-line-item-remove__label">Remove</span>
										</div>
									</div>
								</div>
							</div>
						</div></div>
					</div>
				</div>

				<div class="sc-product-line-item" role="listitem">
					<div class="sc-product-line-item__content">
						<div class="wp-block-group"><div class="wp-block-group__inner-container">
							<div class="is-layout-flex">
								<figure class="sc-cart-line-item-image-wrap" style="margin:0">
									<img class="sc-is-covered wp-block-surecart-cart-line-item-image" alt="" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Crect width='80' height='80' fill='%23f3f4f6'/%3E%3Ccircle cx='40' cy='40' r='22' fill='%231a1a1a'/%3E%3Cpath d='M28 40h24M40 28v24' stroke='%23fff' stroke-width='4' stroke-linecap='round'/%3E%3C/svg%3E" style="aspect-ratio:1/1;border-radius:4px;border-width:1px;border-style:solid">
								</figure>
								<div class="pv-li-body">
									<div class="pv-li-top">
										<div>
											<a class="wp-block-surecart-cart-line-item-title" href="#" style="font-weight:500;line-height:1.4;text-decoration:none">Second Product</a>
											<div class="wp-block-surecart-cart-line-item-price-name" style="font-size:14px;line-height:1.4">Starter plan</div>
											<div class="wp-block-surecart-cart-line-item-note" style="font-size:14px;line-height:1.4"><div class="line-item-note__text">Customer note goes here</div></div>
										</div>
										<div class="pv-li-right">
											<div class="wp-block-surecart-cart-line-item-amount" style="font-weight:500">$79.00</div>
										</div>
									</div>
									<div class="pv-li-bottom">
										<div class="sc-input-group sc-input-group-sm sc-quantity-selector wp-block-surecart-cart-line-item-quantity">
											<div class="sc-input-group-text sc-quantity-selector__decrease button--disabled" role="button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
											<input type="number" class="sc-form-control sc-quantity-selector__control" value="1" readonly>
											<div class="sc-input-group-text sc-quantity-selector__increase" role="button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
										</div>
										<div class="wp-block-surecart-cart-line-item-remove" role="button" style="font-size:14px;font-weight:400">
											<svg class="wp-block-surecart-cart-line-item-remove__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
											<span class="wp-block-surecart-cart-line-item-remove__label">Remove</span>
										</div>
									</div>
								</div>
							</div>
						</div></div>
					</div>
				</div>

			</div>

			<div class="wp-block-surecart-slide-out-cart-coupon" style="padding:0 2em 1.5em">
				<span class="trigger" style="font-size:14px;color:var(--sc-input-help-text-color);text-decoration:underline">Add coupon code</span>
			</div>

			<div class="wp-block-group" style="border-top:1px solid #e5e7eb;padding:2em;margin-top:auto"><div class="wp-block-group__inner-container">
				<div class="wp-block-surecart-slide-out-cart-items-subtotal">
					<div>
						<p class="wp-block-paragraph" style="margin:0;font-size:18px;font-weight:500;line-height:1.4">Subtotal</p>
						<p class="wp-block-paragraph" style="color:var(--sc-input-help-text-color);font-size:14px;line-height:1.4">Taxes &amp; shipping calculated at checkout</p>
					</div>
					<div>
						<span class="wp-block-surecart-cart-subtotal-amount" style="font-size:18px;font-weight:500;line-height:1.4">$511.99</span>
					</div>
				</div>
				<div class="sc-cart-items-submit__wrapper">
					<div class="wp-block-button">
						<a class="wp-block-button__link wp-element-button sc-button__link wp-block-surecart-slide-out-cart-items-submit" href="#" style="border-radius:4px"><span class="sc-button__link-text">Checkout</span></a>
					</div>
				</div>
			</div></div>

		</div>
		<div class="sc-drawer__backdrop show"></div>
	</div>

	<div class="wp-block-surecart-cart-icon" role="button">
		<div class="wp-block-surecart-cart-icon__container">
			<div class="wp-block-surecart-cart-icon__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
			<span class="wp-block-surecart-cart-icon__count">2</span>
		</div>
	</div>
</div>
</div>

<!-- ═══════════════ CHECKOUT ═══════════════ -->
<div id="pv-checkout" class="pv-view">
<div class="pv-checkout">
	<div class="pv-h">Contact</div>
	<div class="pv-row"><span class="input__label">Email</span><div class="input input--focused">customer@example.com</div></div>
	<div class="pv-cols pv-row">
		<div><span class="input__label">First name</span><div class="input">First name</div></div>
		<div><span class="input__label">Last name</span><div class="input"><span class="ph">Last name</span></div></div>
	</div>

	<div class="pv-h" style="margin-top:1.5em">Plan</div>
	<div class="choices pv-row">
		<div class="choice choice--checked"><span class="radio"></span><div class="choice__title">Annual <div class="choice__desc">Billed once a year</div></div><span class="choice__price">$99 / yr</span></div>
		<div class="choice"><span class="radio"></span><div class="choice__title">Monthly <div class="choice__desc">Cancel any time</div></div><span class="choice__price">$12 / mo</span></div>
	</div>

	<div class="pv-h" style="margin-top:1.5em">Payment</div>
	<div class="pv-row"><span class="input__label">Card number</span><div class="input"><span class="ph">1234 1234 1234 1234</span></div></div>
	<div class="pv-cols pv-row">
		<div><span class="input__label">Expiry</span><div class="input"><span class="ph">MM / YY</span></div></div>
		<div><span class="input__label">CVC</span><div class="input"><span class="ph">CVC</span></div></div>
	</div>
	<div class="pv-row checkbox is-checked"><span class="box"></span>Save payment method for next time</div>

	<div class="pv-summary">Subtotal <span>$499.00</span></div>
	<div class="pv-summary">Tax <span>$0.00</span></div>
	<div class="pv-summary total">Total <span>$499.00</span></div>
	<div class="pv-row" style="margin-top:1.25em"><div class="button">Purchase</div></div>
</div>
</div>

<!-- ═══════════════ DASHBOARD ═══════════════ -->
<div id="pv-dashboard" class="pv-view">
<div class="page-template-template-surecart-dashboard has-columns">
	<div class="dashboard-left">
		<div class="pv-logo">YOUR STORE</div>
		<div>
			<a class="tab tab--active" href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard</a>
			<a class="tab" href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>Subscriptions</a>
			<a class="tab" href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Licenses</a>
			<a class="tab" href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Downloads</a>
			<a class="tab" href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Orders</a>
		</div>
		<div class="pv-user">
			<span class="pv-avatar" id="pv-avatar">
			<?php
			if ( $nahnu_sct_avatar_url ) :
				?>
				<img src="<?php echo esc_url( $nahnu_sct_avatar_url ); ?>" alt="">
				<?php
else :
	?>
				c<?php endif; ?></span>
			<span>customer</span>
		</div>
	</div>
	<div class="dashboard-right">
		<div>
			<div class="heading__title">Subscriptions</div>
			<div class="card">
				<div class="list-row"><div><b>Sample Product</b><small>Annual plan</small></div><div>$99.00 / year</div><div><span class="tag tag--success">Active</span></div><a class="button--text" href="#">Manage</a></div>
				<div class="list-row"><div><b>Second Product</b><small>Starter plan</small></div><div>$12.00 / month</div><div><span class="tag tag--success">Active</span></div><a class="button--text" href="#">Manage</a></div>
				<div class="list-row"><div><b>Third Product</b><small>Trial</small></div><div>$49.00 / year</div><div><span class="tag tag--default">Trialing</span></div><a class="button--text" href="#">Manage</a></div>
			</div>
			<div class="heading__title">Orders</div>
			<div class="card">
				<div class="list-row"><div><b>#1002</b><small>Jun 14, 2026</small></div><div>Sample Product</div><div><span class="tag tag--success">Paid</span></div><b>$99.00</b></div>
				<div class="list-row"><div><b>#1001</b><small>May 2, 2026</small></div><div>Second Product</div><div><span class="tag tag--success">Paid</span></div><b>$12.00</b></div>
			</div>
		</div>
	</div>
</div>
</div>

</body>
</html>
