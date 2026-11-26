=== Nahnu Custom Theme for SureCart ===
Contributors: ja1me4
Tags: surecart, checkout, cart, customization, theme
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Requires Plugins: surecart
Stable tag: 1.0.3
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

== Screenshots ==

1. Brand tab: accent colour, font and default profile photo, with the live cart preview beside it.
2. Presets recolour every setting at once; nothing is saved until you click Save changes.
3. Checkout tab: input fields, labels and plan selectors, previewed on a checkout form.
4. Account tab: sidebar, cards, rows and navigation, previewed on the customer dashboard.
5. Presets & Tools: starting points, plus JSON export and import to move settings between sites.
6. About tab: our other plugins and a notice that this is an independent plugin, not made or endorsed by SureCart.

== Changelog ==

= 1.0.3 =
* First WordPress.org release
* New About tab: who we are, why we built this for our own SureCart-powered plugin sites, and links to our other plugins (plain links, no requests made)
* About tab now opens with a prominent notice that this is an independent plugin, not made, endorsed or supported by SureCart
* Settings screen with a sticky top bar (section tabs, save state, Save/Reset always in reach), inspector-style rows and a half-width live preview
* Live preview of the mini cart, checkout and account dashboard, built from SureCart 4.10's own class names and stylesheet rules; every field change re-renders it
* Theme presets (Obsidian, Soft, Sharp, Sage) that fill every setting in one click; nothing is saved until you choose to
* Export settings as JSON and import them on another site; settings tabs are deep-linkable (?tab=cart)
* Mini cart themed through SureCart's own tokens scoped to the cart wrapper; !important fallbacks only for carts customised in the Site Editor
* Floating cart bubble driven by the --sc-cart-icon-* / --sc-cart-counter-* tokens; quantity stepper at the correct specificity
* Button and bubble text colour computed for contrast with the same brightness test SureCart uses, so light accent colours stay readable
* Dashboard sidebar border uses border-inline-end to match SureCart 4.10's RTL handling
* Font family is validated against a strict allow-list and every setting is re-validated when read, so nothing unvalidated reaches the generated CSS
* Verified against SureCart 4.10.0; passes PHP_CodeSniffer (WordPress, WordPress-Extra) and Plugin Check
* Internal: PHP prefix is now nahnu_sct_. Settings saved by earlier pre-release builds are migrated automatically on first load

= 1.0.0 =
* Initial pre-release
* Mini cart, checkout and account dashboard theming
* Default profile photo (experimental) and 6 built-in preset avatars

== Upgrade Notice ==

= 1.0.3 =
First WordPress.org release. Settings from earlier builds are migrated automatically.
