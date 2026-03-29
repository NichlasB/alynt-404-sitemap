<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN') || !current_user_can('activate_plugins')) {
    exit;
}

// Prevent direct file access
if (!defined('WPINC')) {
    die;
}

// Access WordPress globals
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
function alynt_404_delete_prefixed_options($prefixes) {
    global $wpdb;
    foreach ($prefixes as $prefix) {
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $prefix
            )
        );
        if (!$options) {
            continue;
        }
        foreach ($options as $option) {
            delete_option($option->option_name);
        }
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
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            'alynt_404_%'
        )
    );
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            'alynt_404_%'
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
    $css_dir = trailingslashit($upload_dir['basedir']) . 'alynt-404-sitemap';
    if (!file_exists($css_dir)) {
        return;
    }

    $files = glob($css_dir . '/*.css');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && preg_match('/^[a-zA-Z0-9_-]+\.css$/', basename($file))) {
                @unlink($file);
            }
        }
    }

    if (file_exists($css_dir . '/.htaccess')) {
        @unlink($css_dir . '/.htaccess');
    }
    if (file_exists($css_dir . '/index.php')) {
        @unlink($css_dir . '/index.php');
    }
    @rmdir($css_dir);
}

/**
 * Clear scheduled plugin cron hooks.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_404_clear_cron_hooks() {
    wp_clear_scheduled_hook('alynt_404_cleanup_transients');
    $crons = _get_cron_array();
    if (!$crons) {
        return;
    }
    foreach ($crons as $timestamp => $cron) {
        foreach ($cron as $hook => $events) {
            if (strpos($hook, 'alynt_404_') === 0) {
                wp_clear_scheduled_hook($hook);
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
    $wpdb->query('START TRANSACTION');

    try {
        alynt_404_delete_prefixed_options(array(
            'alynt_404_%',
            '_transient_alynt_404_%',
            '_transient_timeout_alynt_404_%',
        ));
        alynt_404_delete_prefixed_meta();
        alynt_404_cleanup_generated_files();
        alynt_404_clear_cron_hooks();
        wp_cache_flush();
        delete_option('rewrite_rules');
        $wpdb->query('COMMIT');
        flush_rewrite_rules();
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
    }
}

// Run cleanup
alynt_404_clean_plugin_data();
