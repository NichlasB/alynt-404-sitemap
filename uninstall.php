<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

// Prevent direct file access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Access WordPress globals.
global $wpdb;

/**
 * Delete all options that match one or more prefixes.
 *
 * @since 1.0.0
 *
 * @param array $prefixes SQL LIKE patterns for option keys to delete.
 *
 * @return void
 */
function alynt_404_delete_prefixed_options( $prefixes ) {
	global $wpdb;
	foreach ( $prefixes as $prefix ) {
		$prefix_like = $wpdb->esc_like( rtrim( $prefix, '%' ) ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup bulk-deletes plugin-owned option rows by prefix.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$prefix_like
			)
		);
	}
}

/**
 * Check whether this site should preserve plugin data on uninstall.
 *
 * @since 1.0.4
 *
 * @return bool True when data preservation is enabled.
 */
function alynt_404_should_preserve_data() {
	return (bool) get_option( 'alynt_404_preserve_data_on_uninstall', false );
}

/**
 * Delete plugin-specific post meta entries.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_404_delete_prefixed_post_meta() {
	global $wpdb;
	$meta_like = $wpdb->esc_like( 'alynt_404_' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup deletes plugin-owned post meta entries.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
			$meta_like
		)
	);
}

/**
 * Delete plugin-specific user meta entries.
 *
 * @since 1.0.4
 *
 * @return void
 */
function alynt_404_delete_prefixed_user_meta() {
	global $wpdb;
	$meta_like = $wpdb->esc_like( 'alynt_404_' ) . '%';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup deletes plugin-owned user meta entries.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
			$meta_like
		)
	);
}

/**
 * Recursively delete a directory and all contents.
 *
 * @since 1.0.4
 *
 * @param string $dir          Directory to delete.
 * @param string $uploads_root Uploads basedir for safety scoping.
 *
 * @return void
 */
function alynt_404_delete_directory_recursive( $dir, $uploads_root ) {
	$dir_real     = realpath( $dir );
	$uploads_real = realpath( $uploads_root );

	if ( ! $dir_real || ! $uploads_real ) {
		return;
	}

	$normalized_dir     = wp_normalize_path( $dir_real );
	$normalized_uploads = trailingslashit( wp_normalize_path( $uploads_real ) );

	// Safety check: only remove paths inside uploads.
	if ( strpos( $normalized_dir, $normalized_uploads ) !== 0 ) {
		return;
	}

	$entries = scandir( $dir_real );
	if ( ! is_array( $entries ) ) {
		return;
	}

	foreach ( $entries as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}

		$entry_path = $dir_real . DIRECTORY_SEPARATOR . $entry;
		if ( is_link( $entry_path ) ) {
			wp_delete_file( $entry_path );
			continue;
		}

		if ( is_dir( $entry_path ) ) {
			alynt_404_delete_directory_recursive( $entry_path, $uploads_root );
			continue;
		}

		if ( is_file( $entry_path ) ) {
			wp_delete_file( $entry_path );
		}
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Recursive uninstall cleanup requires removing plugin-owned directories.
	rmdir( $dir_real );
}

/**
 * Remove generated plugin CSS assets from uploads.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_404_cleanup_generated_files() {
	$upload_dir = wp_upload_dir();
	$css_dir    = trailingslashit( $upload_dir['basedir'] ) . 'alynt-404-sitemap';
	if ( ! file_exists( $css_dir ) ) {
		return;
	}

	alynt_404_delete_directory_recursive( $css_dir, $upload_dir['basedir'] );
}

/**
 * Clear scheduled plugin cron hooks.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_404_clear_cron_hooks() {
	wp_clear_scheduled_hook( 'alynt_404_cleanup_transients' );
	$crons = _get_cron_array();
	if ( ! $crons ) {
		return;
	}
	foreach ( $crons as $timestamp => $cron ) {
		foreach ( $cron as $hook => $events ) {
			if ( strpos( $hook, 'alynt_404_' ) === 0 ) {
				wp_clear_scheduled_hook( $hook );
			}
		}
	}
}

/**
 * Clean up all plugin data.
 *
 * @since 1.0.0
 *
 * @param bool $delete_user_meta Whether to remove plugin-owned user meta.
 *
 * @return bool True when this site's plugin data was removed.
 */
function alynt_404_clean_current_site_data( $delete_user_meta = true ) {
	if ( alynt_404_should_preserve_data() ) {
		return false;
	}

	alynt_404_delete_prefixed_options(
		array(
			'alynt_404_%',
			'_transient_alynt_404_%',
			'_transient_timeout_alynt_404_%',
		)
	);
	alynt_404_delete_prefixed_post_meta();

	if ( $delete_user_meta ) {
		alynt_404_delete_prefixed_user_meta();
	}

	alynt_404_cleanup_generated_files();
	alynt_404_clear_cron_hooks();
	delete_option( 'rewrite_rules' );
	flush_rewrite_rules();

	return true;
}

/**
 * Clean up plugin data for single-site or across multisite.
 *
 * @since 1.0.4
 *
 * @return void
 */
function alynt_404_clean_plugin_data() {
	if ( ! is_multisite() ) {
		alynt_404_clean_current_site_data();
		return;
	}

	$site_ids = get_sites(
		array(
			'fields' => 'ids',
		)
	);

	$should_delete_user_meta = true;

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		if ( alynt_404_should_preserve_data() ) {
			$should_delete_user_meta = false;
			restore_current_blog();
			continue;
		}

		alynt_404_clean_current_site_data( false );
		restore_current_blog();
	}

	if ( $should_delete_user_meta ) {
		alynt_404_delete_prefixed_user_meta();
	}
}

// Run cleanup.
alynt_404_clean_plugin_data();
