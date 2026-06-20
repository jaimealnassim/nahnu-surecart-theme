# Nahnu SureCart Theme

[![GitHub](https://img.shields.io/badge/GitHub-nahnu--surecart--theme-181717?logo=github)](https://github.com/jaimealnassim/nahnu-surecart-theme)

> **Unofficial proof-of-concept WordPress plugin** — not affiliated with, endorsed by, or supported by SureCart.

A WordPress plugin built by **[Nahnu Plugins](https://nahnuplugins.com)** that themes the SureCart mini cart, checkout page, and customer account dashboard to match your brand — accent colors, input styles, sidebar layout, and more — all from a single settings page with no CSS knowledge required.

---

## Why this exists

We build a lot of SureCart-powered sites for fitness coaches and agencies in the MENA region. SureCart is a great product but its storefront has no native theming layer. Every project meant writing the same CSS overrides by hand.

This plugin is our attempt at a proper theming solution. It documents which CSS custom properties actually work, which elements are plain DOM vs Shadow DOM, and packages everything into a point-and-click settings page.

**We're sharing this publicly and plan to send it to the SureCart team** in the hope that it inspires a native theming feature inside SureCart itself. Until then, this is a community solution — free to use, fork, and build on.

---

## Disclaimer

This is an **unofficial, community-built plugin** and is **not affiliated with, endorsed by, or supported by SureCart or Automattic** in any way. SureCart is a trademark of its respective owners.

- This plugin works by injecting CSS into the page and hooking WordPress filters. It does not modify SureCart's source code.
- SureCart updates may change class names, CSS custom properties, or DOM structure in ways that break this plugin's CSS rules. We maintain it against the latest SureCart release but cannot guarantee compatibility with every version.
- The **Default Profile Photo** feature is a proof of concept. SureCart's dashboard template currently passes the avatar URL through `esc_url()`, which strips non-standard URLs. For this feature to work fully, SureCart would need to update their template — this is one of the things we hope to raise with their team.
- Use this plugin at your own risk. Test thoroughly before deploying to production.
- No warranty is expressed or implied.

---

## Features

### Brand tab
- **Accent color** — single color that flows through buttons, checkboxes, radios, focus rings, and selected states across the cart, checkout, and dashboard
- **Font family** — override SureCart's font stack with your theme font
- **Default profile photo** — set a fallback avatar for customers without a Gravatar (proof of concept — see disclaimer above)

### Mini Cart tab
- Drawer width, backdrop opacity, transition speed
- Floating cart bubble — size, position, border radius, show/hide
- Checkout button border radius
- Show/hide quantity controls and coupon field

### Checkout tab
- Input field border color, hover color, focus color
- Border radius, background, text color, placeholder color
- Label color and weight
- Row spacing between form fields
- Choice/plan selector card radius

### Account Dashboard tab
- Page background color
- Sidebar background and width
- Card border color, radius, and shadow
- Row hover color and divider color
- Active navigation tab color

---

## How it works

SureCart's mini cart uses the WordPress Blocks (blocks-next) system and is **plain DOM** — no Shadow DOM. This means standard CSS selectors work, though many colors are set as inline styles in the block editor output, which requires `!important` overrides in a few targeted places.

SureCart's checkout page uses Stencil Web Components with **Shadow DOM**. CSS cannot reach inside shadow roots directly, but CSS custom properties (variables) cross the shadow boundary through inheritance. Every checkout token in this plugin was verified against the SureCart 4.2.2 source.

SureCart's account dashboard is a WordPress page template (`template-surecart-dashboard.php`). The shell is plain DOM and fully styleable. Content components inside are Shadow DOM and are themed via tokens.

---

## Technical notes

All generated CSS is output via `wp_add_inline_style()` at priority 999, ensuring it loads after SureCart's own stylesheets. Every CSS rule in the plugin has a source comment indicating which SureCart source file the selector or token was verified against.

CSS custom properties used by the plugin are set at `:root` level so they cross Shadow DOM boundaries into SureCart's Stencil components.

The plugin registers its own `nst-styles` script handle to avoid dependency on `wp-block-library` being enqueued, which varies by theme.

---

## Installation

1. Download the ZIP from [GitHub](https://github.com/jaimealnassim/nahnu-surecart-theme/releases) or clone the repo
2. In WordPress: **Plugins > Add New > Upload Plugin**
3. Upload the ZIP and activate
4. Go to **Settings > SureCart Theme**

---

## Requirements

- WordPress 6.0+
- SureCart 4.x (tested against 4.2.2)
- PHP 8.0+

---

## Contributing

**GitHub:** [github.com/jaimealnassim/nahnu-surecart-theme](https://github.com/jaimealnassim/nahnu-surecart-theme)

Issues and pull requests are welcome. If you're from the SureCart team and found this — hi! We'd love to talk about making theming a first-class feature in SureCart.

---

## License

GPL-2.0-or-later — free to use, modify, and distribute.

---

## Credits

Built by **[Nahnu Plugins](https://nahnuplugins.com)** — WordPress plugins for fitness coaches and agencies in the MENA region.

- Website: [nahnuplugins.com](https://nahnuplugins.com)
- GitHub: [github.com/jaimealnassim/nahnu-surecart-theme](https://github.com/jaimealnassim/nahnu-surecart-theme)
