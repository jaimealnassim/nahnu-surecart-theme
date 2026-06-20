<?php
/**
 * Plugin Name:       Nahnu SureCart Theme
 * Plugin URI:        https://github.com/jaimealnassim/nahnu-surecart-theme
 * Description:       Unofficial proof-of-concept plugin by Nahnu Plugins. Themes the SureCart mini cart, checkout, and account dashboard to match your brand — accent color, inputs, sidebar, and more — no CSS required.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Nahnu Plugins
 * Author URI:        https://nahnuplugins.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nahnu-surecart-theme
 * Domain Path:       /languages
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'NST_VERSION',  '1.0.0' );
define( 'NST_FILE',     __FILE__ );
define( 'NST_DIR',      plugin_dir_path( __FILE__ ) );
define( 'NST_URL',      plugin_dir_url( __FILE__ ) );
define( 'NST_OPTION',   'nst_settings' );

require_once NST_DIR . 'includes/class-nst-settings.php';
require_once NST_DIR . 'includes/class-nst-css-builder.php';
require_once NST_DIR . 'includes/class-nst-frontend.php';
require_once NST_DIR . 'includes/class-nst-admin.php';
require_once NST_DIR . 'includes/class-nst-avatar.php';

/**
 * Boot the plugin on plugins_loaded.
 */
function nst_init(): void {
	load_plugin_textdomain( 'nahnu-surecart-theme', false, dirname( plugin_basename( NST_FILE ) ) . '/languages' );
	new NST_Frontend();
	new NST_Avatar();

	if ( is_admin() ) {
		new NST_Admin();
	}
}
add_action( 'plugins_loaded', 'nst_init' );

