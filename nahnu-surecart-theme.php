<?php
/**
 * Plugin Name:       Nahnu Custom Theme for SureCart
 * Plugin URI:        https://www.nahnuplugins.com/wordpress-plugins/
 * Description:       Unofficial proof-of-concept plugin by Nahnu Plugins. Themes the SureCart mini cart, checkout, and account dashboard to match your brand — accent color, inputs, sidebar, and more — no CSS required.
 * Version:           1.0.3
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  surecart
 * Author:            ja1me4
 * Author URI:        https://www.nahnuplugins.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nahnu-surecart-theme
 * Domain Path:       /languages
 *
 * @package NahnuSureCartTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'NAHNU_SCT_VERSION', '1.0.3' );
define( 'NAHNU_SCT_FILE', __FILE__ );
define( 'NAHNU_SCT_DIR', plugin_dir_path( __FILE__ ) );
define( 'NAHNU_SCT_URL', plugin_dir_url( __FILE__ ) );
define( 'NAHNU_SCT_OPTION', 'nahnu_sct_settings' );

require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-settings.php';
require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-css-builder.php';
require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-frontend.php';
require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-admin.php';
require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-avatar.php';
require_once NAHNU_SCT_DIR . 'includes/class-nahnu-sct-tools.php';

/**
 * Carry settings over from the option key used before the WordPress.org release, once.
 *
 * The PHP prefix changed from nst_ to nahnu_sct_ for the WordPress.org
 * release; existing installs keep their configuration.
 */
function nahnu_sct_maybe_migrate(): void {
	if ( false !== get_option( NAHNU_SCT_OPTION, false ) ) {
		return;
	}
	$legacy = get_option( 'nst_settings', false );
	if ( is_array( $legacy ) ) {
		update_option( NAHNU_SCT_OPTION, $legacy );
		delete_option( 'nst_settings' );
	}
}

/**
 * Boot the plugin on plugins_loaded.
 */
function nahnu_sct_init(): void {
	nahnu_sct_maybe_migrate();
	new Nahnu_SCT_Frontend();
	new Nahnu_SCT_Avatar();

	if ( is_admin() ) {
		new Nahnu_SCT_Admin();
		new Nahnu_SCT_Tools();
	}
}
add_action( 'plugins_loaded', 'nahnu_sct_init' );
