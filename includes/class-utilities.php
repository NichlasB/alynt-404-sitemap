<?php
/**
 * Utility functions for the plugin.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Utilities {

    /**
     * Store instance of the class.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of the class.
     */
    private static $instance = null;

    /**
     * Get instance of the class.
     *
     * @since 1.0.0
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
    * Sanitize and validate URL.
    *
    * @since 1.0.0
    * @param string $url URL to validate.
    * @return string|bool Sanitized URL or false if invalid.
    */
    public function validate_url($url) {
        $url = trim($url);

        // Handle empty URLs
        if (empty($url)) {
            return false;
        }

        // Check if it's a relative path starting with /
        if (strpos($url, '/') === 0) {
            return esc_url_raw($url);
        }

        // Check if it's a relative path without leading /
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            return esc_url_raw('/' . ltrim($url, '/'));
        }

        // It's an absolute URL, validate it normally
        $url = esc_url_raw($url);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }

    /**
     * Sanitize and validate slug.
     *
     * @since 1.0.0
     * @param string $slug Slug to validate.
     * @return string Sanitized slug.
     */
    public function sanitize_slug($slug) {
        return sanitize_title(trim($slug));
    }

    /**
    * Sanitize CSS input.
    *
    * @since 1.0.0
    * @param string $css CSS to sanitize.
    * @return string Sanitized CSS.
    */
    public function sanitize_css($css) {
        if (empty($css)) {
            return '';
        }

        // Remove any HTML tags
        $css = strip_tags($css);

        // Remove any null characters
        $css = str_replace(chr(0), '', $css);

        // Remove anything that might be used in a script tag
        $css = str_replace('javascript:', '', $css);
        $css = str_replace('expression(', '', $css);
        $css = str_replace('vbscript:', '', $css);

        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove spaces before colons
        $css = preg_replace('/\s+:/', ':', $css);

        // Normalize whitespace
        $css = preg_replace('/\s+/', ' ', $css);

        return trim($css);
    }

    /**
     * Check if slug is available.
     *
     * @since 1.0.0
     * @param string $slug Slug to check.
     * @return boolean True if available.
     */
    public function is_slug_available($slug) {
        global $wpdb;
        
        // Check pages
        $page = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts 
            WHERE post_name = %s 
            AND post_type = 'page' 
            AND post_status = 'publish'",
            $slug
        ));

        if ($page) {
            return false;
        }

        // Check if slug is used by other plugins or custom routes
        $reserved_slugs = apply_filters(ALYNT_404_PREFIX . 'reserved_slugs', array(
            'wp-admin',
            'wp-content',
            'wp-includes',
            'wp-json',
            'feed',
            'embed',
            'sitemap.xml',
            'page',
            'comments',
            'author'
        ));

        return !in_array($slug, $reserved_slugs);
    }

    /**
     * Generate unique slug.
     *
     * @since 1.0.0
     * @param string $base_slug Base slug.
     * @return string Unique slug.
     */
    public function generate_unique_slug($base_slug) {
        $slug = $this->sanitize_slug($base_slug);
        $counter = 2;
        $original_slug = $slug;

        while (!$this->is_slug_available($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Format file size.
     *
     * @since 1.0.0
     * @param int $bytes Size in bytes.
     * @return string Formatted size.
     */
    public function format_file_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Clean filename.
     *
     * @since 1.0.0
     * @param string $filename Filename to clean.
     * @return string Cleaned filename.
     */
    public function clean_filename($filename) {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $filename);
        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);
        // Ensure safe extension
        $allowed_extensions = array('css', 'js', 'jpg', 'jpeg', 'png', 'gif');
        $parts = explode('.', $filename);
        $extension = strtolower(end($parts));
        
        if (!in_array($extension, $allowed_extensions)) {
            $filename .= '.txt';
        }
        
        return $filename;
    }

    /**
     * Generate meta description.
     *
     * @since 1.0.0
     * @param string $text Text to generate description from.
     * @param int    $length Maximum length.
     * @return string Generated description.
     */
    public function generate_meta_description($text, $length = 160) {
        // Strip HTML tags
        $text = wp_strip_all_tags($text);
        // Convert entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        // Trim to length
        $text = wp_trim_words($text, $length, '');
        
        return sanitize_text_field($text);
    }

    /**
     * Check if request is AJAX.
     *
     * @since 1.0.0
     * @return boolean True if AJAX request.
     */
    public function is_ajax() {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Safe redirect.
     *
     * @since 1.0.0
     * @param string $url URL to redirect to.
     * @param int    $status HTTP status code.
     */
    public function safe_redirect($url, $status = 302) {
        $url = wp_sanitize_redirect($url);
        $url = wp_validate_redirect($url, home_url('/'));
        
        wp_safe_redirect($url, $status);
        exit;
    }

    /**
     * Get plugin asset URL.
     *
     * @since 1.0.0
     * @param string $path Asset path.
     * @return string Asset URL.
     */
    public function get_asset_url($path) {
        return plugins_url($path, ALYNT_404_PATH);
    }

    /**
     * Check if current user can manage plugin.
     *
     * @since 1.0.0
     * @return boolean True if user can manage plugin.
     */
    public function current_user_can_manage() {
        return current_user_can('manage_options');
    }

    /**
     * Log error message if debugging is enabled.
     *
     * @since 1.0.0
     * @param string $message Error message.
     * @param mixed  $data Additional data to log.
     */
    public function log_error($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[Alynt 404 & Sitemap] %s | Data: %s',
                $message,
                print_r($data, true)
            ));
        }
    }

    /**
     * Clean up expired transients.
     *
     * @since 1.0.0
     */
    public function cleanup_transients() {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
                '_transient_timeout_' . ALYNT_404_PREFIX . '%',
                '_transient_' . ALYNT_404_PREFIX . '%'
            )
        );
    }
}