<?php
/**
 * Uninstall — runs when the plugin is deleted from the WP admin.
 * Removes all plugin data from the database.
 *
 * @package NahnuSureCartTheme
 */

// Only run when WP itself triggers an uninstall. Exit otherwise.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin settings from options table.
delete_option( 'nst_settings' );

// Remove any preset avatar attachment files we uploaded to the media library.
// These are tagged with _nst_avatar_hash post meta so we can find them.
$avatar_attachments = get_posts( [
	'post_type'      => 'attachment',
	'post_status'    => 'inherit',
	'meta_key'       => '_nst_avatar_hash',
	'numberposts'    => -1,
	'fields'         => 'ids',
] );

foreach ( $avatar_attachments as $attachment_id ) {
	wp_delete_attachment( $attachment_id, true );
}

// Multisite: clean up settings for every blog.
if ( is_multisite() ) {
	$blog_ids = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( 'nst_settings' );
		restore_current_blog();
	}
}
