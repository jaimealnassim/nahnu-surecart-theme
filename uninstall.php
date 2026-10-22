<?php
/**
 * Uninstall — runs when the plugin is deleted from the WP admin.
 *
 * Removes the settings option (current key and the pre-1.2 key). The plugin
 * never creates posts, tables or files, so nothing else needs cleaning up.
 *
 * @package NahnuSureCartTheme
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete every option this plugin has ever written on the current site.
 */
function nahnu_sct_uninstall_site(): void {
	delete_option( 'nahnu_sct_settings' );
	delete_option( 'nst_settings' ); // Option key used before the 1.2.0 prefix change.
}

nahnu_sct_uninstall_site();

if ( is_multisite() ) {
	$nahnu_sct_blog_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $nahnu_sct_blog_ids as $nahnu_sct_blog_id ) {
		switch_to_blog( $nahnu_sct_blog_id );
		nahnu_sct_uninstall_site();
		restore_current_blog();
	}
}
