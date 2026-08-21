<?php
/** Safe source-directory normalization for 360 Global Blocks packages. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_global_blocks_rename_github_package( $source, $remote_source, $upgrader, $hook_extra ) {
	if ( empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
		return $source;
	}

	$source_path     = untrailingslashit( $source );
	$source_dir      = basename( $source_path );
	$expected_dir    = '360-global-blocks';
	$plugin          = (string) ( $hook_extra['plugin'] ?? '' );
	$slug            = (string) ( $hook_extra['slug'] ?? '' );
	$plugin_matches  = '' !== $plugin && 0 === strcasecmp( $plugin, '360-global-blocks/360-global-blocks.php' );
	$slug_matches    = '' !== $slug && $expected_dir === $slug;
	$has_bootstrap   = is_file( $source_path . '/360-global-blocks.php' );
	$name_matches    = 1 === preg_match( '/^(?:360-global-blocks|360-global-blocks-.+)$/i', $source_dir );
	$has_explicit_id = '' !== $plugin || '' !== $slug;

	if ( ! $has_bootstrap || ( ! $plugin_matches && ! $slug_matches && ( $has_explicit_id || ! $name_matches ) ) ) {
		return $source;
	}

	if ( 0 === strcasecmp( $source_dir, $expected_dir ) ) {
		return trailingslashit( $source_path );
	}

	$desired_path = trailingslashit( dirname( $source_path ) ) . $expected_dir;
	if ( file_exists( $desired_path ) ) {
		return $source;
	}

	global $wp_filesystem;
	$moved = false;
	if ( $wp_filesystem && $wp_filesystem->move( trailingslashit( $source_path ), trailingslashit( $desired_path ), false ) ) {
		$moved = true;
	}
	if ( ! $moved && @rename( $source_path, $desired_path ) ) {
		$moved = true;
	}

	return $moved ? trailingslashit( $desired_path ) : $source;
}
add_filter( 'upgrader_source_selection', 'sb_global_blocks_rename_github_package', 10, 4 );
