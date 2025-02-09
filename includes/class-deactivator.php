<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Deactivator {

    /**
     * Plugin deactivation tasks.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        self::clear_temporary_data();
        self::remove_rewrite_rules();
        self::clear_scheduled_tasks();
    }

    /**
     * Clear any temporary data and transients.
     *
     * @since 1.0.0
     */
    private static function clear_temporary_data() {
        global $wpdb;

        // Delete all transients with our prefix
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
                '_transient_' . ALYNT_404_PREFIX . '%',
                '_transient_timeout_' . ALYNT_404_PREFIX . '%'
            )
        );

        // Clear any cached CSS files
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit($upload_dir['basedir']) . 'alynt-404-sitemap';
        
        if (file_exists($css_dir)) {
            $css_files = glob($css_dir . '/*.css');
            if (is_array($css_files)) {
                foreach ($css_files as $file) {
                    if (is_file($file) && strpos($file, 'cache') !== false) {
                        @unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Remove plugin rewrite rules.
     *
     * @since 1.0.0
     */
    private static function remove_rewrite_rules() {
        // Remove the sitemap rewrite rule by flushing
        delete_option('rewrite_rules');
        flush_rewrite_rules();
    }

    /**
     * Clear any scheduled tasks/crons.
     *
     * @since 1.0.0
     */
    private static function clear_scheduled_tasks() {
        // Get all scheduled tasks with our prefix
        $cron_tasks = _get_cron_array();
        
        if (!empty($cron_tasks)) {
            foreach ($cron_tasks as $timestamp => $cron) {
                foreach ($cron as $hook => $task) {
                    if (strpos($hook, ALYNT_404_PREFIX) !== false) {
                        wp_unschedule_event($timestamp, $hook);
                    }
                }
            }
        }
    }

    /**
     * Log deactivation for debugging purposes if WP_DEBUG is enabled.
     *
     * @param string $message The message to log
     * @return void
     */
    private static function log_deactivation($message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('Alynt 404 & Sitemap Deactivation: ' . $message);
        }
    }
}