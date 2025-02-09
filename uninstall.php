<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Alynt_404_Sitemap
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
 * Clean up all plugin data
 */
function alynt_404_clean_plugin_data() {
    global $wpdb;

    // Start transaction
    $wpdb->query('START TRANSACTION');

    try {
        // Get and remove all options with our prefix
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                'alynt_404_%'
            )
        );

        if ($options) {
            foreach ($options as $option) {
                delete_option($option->option_name);
            }
        }

        // Remove any plugin transients
        $transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_alynt_404_%',
                '_transient_timeout_alynt_404_%'
            )
        );

        if ($transients) {
            foreach ($transients as $transient) {
                delete_option($transient->option_name);
            }
        }

        // Remove any postmeta related to the plugin
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
                'alynt_404_%'
            )
        );

        // Remove any user meta related to the plugin
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
                'alynt_404_%'
            )
        );

        // Clean up custom CSS files
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit($upload_dir['basedir']) . 'alynt-404-sitemap';
        
        if (file_exists($css_dir)) {
            // Safely remove any CSS files
            $files = glob($css_dir . '/*.css');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file) && preg_match('/^[a-zA-Z0-9_-]+\.css$/', basename($file))) {
                        @unlink($file);
                    }
                }
            }

            // Remove htaccess if exists
            if (file_exists($css_dir . '/.htaccess')) {
                @unlink($css_dir . '/.htaccess');
            }

            // Remove index.php if exists
            if (file_exists($css_dir . '/index.php')) {
                @unlink($css_dir . '/index.php');
            }

            // Remove the directory
            @rmdir($css_dir);
        }

        // Clear any scheduled hooks
        wp_clear_scheduled_hook('alynt_404_cleanup_transients');
        
        // Clear any additional cron events that might have been registered
        $crons = _get_cron_array();
        if ($crons) {
            foreach ($crons as $timestamp => $cron) {
                foreach ($cron as $hook => $events) {
                    if (strpos($hook, 'alynt_404_') === 0) {
                        wp_clear_scheduled_hook($hook);
                    }
                }
            }
        }

        // Clean up any plugin cache
        wp_cache_flush();
        
        // Clear any plugin rewrite rules
        delete_option('rewrite_rules');

        // Commit transaction
        $wpdb->query('COMMIT');

        // Flush rewrite rules
        flush_rewrite_rules();

    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');

        // Log error if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('Alynt 404 & Sitemap Uninstall Error: ' . $e->getMessage());
        }
    }
}

// Run cleanup
alynt_404_clean_plugin_data();