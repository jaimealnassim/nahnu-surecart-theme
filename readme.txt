=== Nahnu SureCart Theme ===
Contributors: nahnuplugins
Tags: surecart, theme, woocommerce, checkout, storefront
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
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

* GitHub: https://github.com/jaimealnassim/nahnu-surecart-theme
* Nahnu Plugins: https://nahnuplugins.com

== Installation ==

1. Download the ZIP from https://github.com/jaimealnassim/nahnu-surecart-theme
2. Go to Plugins > Add New > Upload Plugin
3. Upload the ZIP and activate
4. Go to Settings > SureCart Theme

== Frequently Asked Questions ==

= Is this an official SureCart plugin? =

No. This is an unofficial community plugin built by Nahnu Plugins. It is not affiliated with, endorsed by, or supported by SureCart.

= Will this work with future SureCart versions? =

It is tested against SureCart 4.x. SureCart updates may change internal class names or CSS tokens. We maintain the plugin against the latest release but cannot guarantee compatibility with every version.

= Why doesn't the default avatar show on the dashboard? =

This is a known limitation. SureCart's dashboard template passes the avatar URL through WordPress's esc_url() function, which strips URLs that don't match standard protocols. A SureCart template update would be needed for the avatar to display. The feature works as a proof of concept and we plan to share this finding with the SureCart team.

= Where can I report issues or contribute? =

GitHub: https://github.com/jaimealnassim/nahnu-surecart-theme

== Changelog ==

= 1.0.0 =
* Initial release
* Mini cart theming — drawer width, bubble, checkout button
* Checkout page theming — input tokens via CSS custom properties
* Account dashboard theming — sidebar, cards, nav tabs
* Default profile photo (proof of concept)
* 6 built-in preset avatars

== Upgrade Notice ==

= 1.0.0 =
Initial release.
