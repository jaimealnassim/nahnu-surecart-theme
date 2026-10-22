=== Nahnu Custom Theme for SureCart ===
Contributors: ja1me4
Tags: surecart, checkout, cart, customization, theme
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Requires Plugins: surecart
Stable tag: 1.2.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Unofficial proof-of-concept plugin by Nahnu Plugins that themes the SureCart mini cart, checkout, and account dashboard to match your brand.

== Description ==

**UNOFFICIAL — Not affiliated with, endorsed by, or supported by SureCart.**

GitHub: https://github.com/jaimealnassim/nahnu-surecart-theme

A proof-of-concept WordPress plugin built by Nahnu Plugins (https://nahnuplugins.com) for SureCart-powered websites. Theme your SureCart mini cart, checkout page, and customer account dashboard from a single settings page — no CSS knowledge required.

We built this because we run a lot of SureCart sites for fitness coaches and agencies in the MENA region, and SureCart has no native theming layer. This plugin documents which CSS custom properties actually work, which elements are plain DOM vs Shadow DOM, and packages everything into a settings UI.

We are sharing this publicly and plan to send it to the SureCart team in the hope that it inspires a native theming feature inside SureCart itself. Until then, this is a free community solution — open to use, fork, and build on.

= Features =

* Accent color — flows through buttons, checkboxes, radios, focus rings, and selected states
* Font family override
* Mini cart drawer width, backdrop opacity, transition speed
* Floating cart bubble — size, position, shape, show/hide
* Checkout input field styles — border, radius, background, colors, label weight
* Account dashboard sidebar, cards, and navigation tab styles
* Default profile photo (proof of concept — see disclaimer)

= Disclaimer =

This is an unofficial, community-built plugin. It is NOT affiliated with, endorsed by, or supported by SureCart or Automattic in any way.

SureCart updates may change class names, CSS tokens, or DOM structure in ways that affect this plugin. Test thoroughly before using on production sites.

The Default Profile Photo feature is a proof of concept. SureCart's dashboard template currently wraps the avatar URL with esc_url() which prevents custom image URLs from loading. A SureCart template update would be needed for this feature to work fully on the customer dashboard.

No warranty is expressed or implied. Use at your own risk.

= Technical Notes =

SureCart's mini cart is plain DOM (blocks-next). Checkout uses Stencil Shadow DOM — styled via CSS custom properties at :root which cross the shadow boundary. The account dashboard is a WordPress page template with a plain DOM shell.

All CSS rules include source comments indicating which SureCart 4.2.2 source file the selector or token was verified against.

= Links =

* Plugin page: https://www.nahnuplugins.com/wordpress-plugins/
* GitHub: https://github.com/jaimealnassim/nahnu-surecart-theme
* Nahnu Plugins: https://nahnuplugins.com

== Installation ==

1. Download the ZIP from https://github.com/jaimealnassim/nahnu-surecart-theme
2. Go to Plugins > Add New > Upload Plugin
3. Upload the ZIP and activate
4. Go to Settings > Custom Theme for SureCart

== Frequently Asked Questions ==

= Is this an official SureCart plugin? =

No. This is an unofficial community plugin built by Nahnu Plugins. It is not affiliated with, endorsed by, or supported by SureCart.

= Will this work with future SureCart versions? =

It is tested against SureCart 4.2.2 and 4.10.0. SureCart updates may change internal class names or CSS tokens. We maintain the plugin against the latest release but cannot guarantee compatibility with every version.

= Why doesn't the default avatar show on the dashboard? =

This is a known limitation. SureCart's dashboard template passes the avatar URL through WordPress's esc_url() function, which strips URLs that don't match standard protocols. A SureCart template update would be needed for the avatar to display. The feature works as a proof of concept and we plan to share this finding with the SureCart team.

= Where can I report issues or contribute? =

GitHub: https://github.com/jaimealnassim/nahnu-surecart-theme

== Privacy ==

This plugin does not collect, store or transmit any personal data, and it does not connect to any external service. Settings are stored in a single option in your WordPress database and are removed when the plugin is deleted.

== Changelog ==

= 1.2.0 =
* Redesigned settings screen: sticky top bar with section tabs, save state and Save/Reset always in reach; inspector-style rows; half-width live preview
* Live preview beside the settings form. Every field change re-renders the generated CSS into a preview of the cart, checkout and account dashboard built from SureCart 4.10's own class names and stylesheet rules
* Theme presets (Obsidian, Soft, Sharp, Sage) that fill every setting in one click; nothing is saved until you choose to
* Export settings as JSON and import them on another site
* Settings tabs are deep-linkable (?tab=cart)
* Internal: PHP prefix changed from nst_ to nahnu_sct_ for the WordPress.org release. Existing settings are migrated automatically on the first load

= 1.1.0 =
* Re-verified every selector and token against SureCart 4.10.0 (all stable)
* Mini cart now themes through SureCart's own tokens (--sc-cart-main-label-text-color, --sc-input-help-text-color, --sc-panel-background-color, --sc-drawer-*) scoped to the cart wrapper. Stock cart templates need no !important; the !important fallbacks remain for carts customised in the Site Editor
* Floating cart bubble switched from ~20 !important rules to the --sc-cart-icon-* / --sc-cart-counter-* tokens it already reads; removed a dead selector and a pointless z-index
* Quantity stepper rules rewritten at the correct specificity (SureCart uses 2–3 class selectors; ours were being ignored)
* Restored backdrop opacity and transition speed, which had settings but no output
* Button and bubble text colour now computed for contrast with the same brightness test SureCart uses, so light accent colours stay readable
* Dashboard sidebar border uses border-inline-end to match SureCart 4.10's RTL change
* Avatar notice corrected: esc_url() strips data URIs and SVG, not ordinary image URLs

= 1.0.0 =
* Initial release
* Mini cart theming — drawer width, bubble, checkout button
* Checkout page theming — input tokens via CSS custom properties
* Account dashboard theming — sidebar, cards, nav tabs
* Default profile photo (experimental)
* 6 built-in preset avatars

== Upgrade Notice ==

= 1.0.0 =
Initial release.
