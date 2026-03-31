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
 * Delete plugin-specific post and user meta entries.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_404_delete_prefixed_meta() {
	global $wpdb;
	$meta_like = $wpdb->esc_like( 'alynt_404_' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup deletes plugin-owned post meta entries.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
			$meta_like
		)
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup deletes plugin-owned user meta entries.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
			$meta_like
		)
	);
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

	$files = glob( $css_dir . '/*.css' );
	if ( $files ) {
		foreach ( $files as $file ) {
			if ( is_file( $file ) && preg_match( '/^[a-zA-Z0-9_-]+\.css$/', basename( $file ) ) ) {
				wp_delete_file( $file );
			}
		}
	}

	if ( file_exists( $css_dir . '/.htaccess' ) ) {
		wp_delete_file( $css_dir . '/.htaccess' );
	}
	if ( file_exists( $css_dir . '/index.php' ) ) {
		wp_delete_file( $css_dir . '/index.php' );
	}
	if ( is_dir( $css_dir ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Directory should only be removed after generated files are deleted.
		rmdir( $css_dir );
	}
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
 * @return void
 */
function alynt_404_clean_plugin_data() {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction boundaries are direct SQL statements.
	$wpdb->query( 'START TRANSACTION' );

	try {
		alynt_404_delete_prefixed_options(
			array(
				'alynt_404_%',
				'_transient_alynt_404_%',
				'_transient_timeout_alynt_404_%',
			)
		);
		alynt_404_delete_prefixed_meta();
		alynt_404_cleanup_generated_files();
		alynt_404_clear_cron_hooks();
		delete_option( 'rewrite_rules' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction boundary for uninstall cleanup.
		$wpdb->query( 'COMMIT' );
		flush_rewrite_rules();
	} catch ( Exception $e ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction rollback on uninstall failure.
		$wpdb->query( 'ROLLBACK' );
	}
}

// Run cleanup.
alynt_404_clean_plugin_data();
