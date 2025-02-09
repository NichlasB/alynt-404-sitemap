<?php
/**
 * Handle template loading operations.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Template_Loader {

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
     * Initialize the class.
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Add query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    /**
     * Prevent cloning of the instance
     *
     * @since 1.0.0
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     *
     * @since 1.0.0
     */
    public function __wakeup() {}

    /**
     * Add custom query vars.
     *
     * @since 1.0.0
     * @param array $vars Existing query vars.
     * @return array Modified query vars.
     */
    public function add_query_vars($vars) {
        $vars[] = ALYNT_404_PREFIX . 'sitemap';
        return $vars;
    }

    /**
     * Load 404 template.
     *
     * @since 1.0.0
     * @param string $template Current template path.
     * @return string Modified template path.
     */
    public function load_404_template($template) {
        if (!is_404()) {
            return $template;
        }

        // Load our custom 404 template first
        $custom_template = ALYNT_404_PATH . 'templates/404.php';

        if (file_exists($custom_template)) {
            return $custom_template;
        }

        // Fall back to theme template if our template doesn't exist
        $theme_template = locate_template('404.php');
        if ($theme_template) {
            add_filter('the_content', array($this, 'get_404_content'));
            return $theme_template;
        }

        return $template;
    }

    /**
     * Load sitemap template.
     *
     * @since 1.0.0
     * @param string $template Current template path.
     * @return string Modified template path.
     */
    public function load_sitemap_template($template) {
        if (!get_query_var(ALYNT_404_PREFIX . 'sitemap')) {
            return $template;
        }

        // Check if theme has custom sitemap template
        $theme_template = locate_template('sitemap.php');
        
        if ($theme_template) {
            // Add our content filter
            add_filter('the_content', array($this, 'get_sitemap_content'));
            return $theme_template;
        }

        // Load our custom sitemap template
        $custom_template = ALYNT_404_PATH . 'templates/sitemap.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    /**
     * Get 404 page content.
     *
     * @since 1.0.0
     * @param string $content Current content.
     * @return string Modified content.
     */
    public function get_404_content($content) {
        // Only modify 404 page content
        if (!is_404()) {
            return $content;
        }

        ob_start();

        $template_path = ALYNT_404_PATH . 'templates/404.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback to inline template if file doesn't exist
            $settings = get_option(ALYNT_404_PREFIX . '404_settings', array());
            require ALYNT_404_PATH . 'templates/partials/404-content.php';
        }

        return ob_get_clean();
    }

    /**
     * Get sitemap content.
     *
     * @since 1.0.0
     * @param string $content Current content.
     * @return string Modified content.
     */
    public function get_sitemap_content($content) {
        // Only modify sitemap page content
        if (!get_query_var(ALYNT_404_PREFIX . 'sitemap')) {
            return $content;
        }

        ob_start();

        $template_path = ALYNT_404_PATH . 'templates/sitemap.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback to inline template if file doesn't exist
            $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings', array());
            $post_types = !empty($settings['post_types']) ? $settings['post_types'] : ['post', 'page'];
            $excluded_ids = !empty($settings['excluded_ids']) ? array_map('trim', explode(',', $settings['excluded_ids'])) : [];
            
            require ALYNT_404_PATH . 'templates/partials/sitemap-content.php';
        }

        return ob_get_clean();
    }

    /**
     * Get responsive classes for sitemap layout.
     *
     * @since 1.0.0
     * @return string CSS classes for responsive layout.
     */
    public function get_responsive_classes() {
        $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings', array());
        
        $classes = array(
            'desktop-cols-' . ($settings['columns_desktop'] ?? 4),
            'tablet-cols-' . ($settings['columns_tablet'] ?? 2),
            'mobile-cols-' . ($settings['columns_mobile'] ?? 1)
        );

        return implode(' ', array_map('sanitize_html_class', $classes));
    }

    /**
     * Check if current page is sitemap.
     *
     * @since 1.0.0
     * @return boolean True if current page is sitemap.
     */
    public function is_sitemap() {
        return (bool) get_query_var(ALYNT_404_PREFIX . 'sitemap');
    }
}