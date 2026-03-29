<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

class Alynt_404_Public {

    /**
     * The plugin name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @return   void
     */
    private function __construct() {
        $this->plugin_name = 'alynt-404-sitemap';
        $this->version = ALYNT_404_VERSION;
        add_filter('document_title_parts', array($this, 'filter_document_title')); 
    }

    /**
     * Prevent cloning of the instance
     *
     * @since 1.0.0
     * @return void
     */
    private function __clone() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cloning is not allowed for this singleton.', 'alynt-404-sitemap'), '1.0.3');
    }

    /**
     * Prevent unserializing of the instance
     *
     * @since 1.0.0
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, esc_html__('Unserializing is not allowed for this singleton.', 'alynt-404-sitemap'), '1.0.3');
    }

        /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     * @return   void Enqueues public styles when the plugin output is in use.
     */
    public function enqueue_styles() {
        // Only load styles when needed
            if (is_404()) {
                wp_enqueue_style(
                    $this->plugin_name . '-404',
                    plugin_dir_url(__FILE__) . 'css/404-styles.css',
                    array(),
                    $this->version,
                    'all'
                );
            } elseif (get_query_var(ALYNT_404_PREFIX . 'sitemap')) {
                wp_enqueue_style(
                    $this->plugin_name . '-sitemap',
                    plugin_dir_url(__FILE__) . 'css/sitemap-styles.css',
                    array(),
                    $this->version,
                    'all'
                );
            }

        // Load custom colors CSS if exists
            $custom_css_url = Alynt_404_Color_Manager::get_instance()->get_css_url();
            if ($custom_css_url) {
                wp_enqueue_style(
                    $this->plugin_name . '-custom-colors',
                    $custom_css_url,
                    array(),
                    get_option(ALYNT_404_PREFIX . 'css_version', '1.0.0')
                );
            }
        }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     * @return   void Enqueues public scripts for the 404 search experience.
     */
    public function enqueue_scripts() {
        // Only load scripts when needed
        if (!is_404()) {
            return;
        }

        wp_enqueue_script('jquery');
        $core_handle = $this->enqueue_search_script_modules();
        wp_localize_script($core_handle, 'alynt404Search', $this->get_localized_search_vars());
    }

    /**
     * Enqueue split public search modules.
     *
     * @since 1.0.3
     * @return string Core script handle.
     */
    private function enqueue_search_script_modules() {
        $core_handle = $this->plugin_name . '-search-core';
        wp_enqueue_script($core_handle, plugin_dir_url(__FILE__) . 'js/search-core.js', array('jquery'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-search-ui', plugin_dir_url(__FILE__) . 'js/search-ui.js', array($core_handle), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-search-api', plugin_dir_url(__FILE__) . 'js/search-api.js', array($this->plugin_name . '-search-ui'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-ajax-search', plugin_dir_url(__FILE__) . 'js/ajax-search.js', array($this->plugin_name . '-search-api'), $this->version, true);
        return $core_handle;
    }

    /**
     * Localized search config.
     *
     * @since 1.0.3
     * @return array Localized search configuration for client scripts.
     */
    private function get_localized_search_vars() {
        return array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(ALYNT_404_PREFIX . 'search_nonce'),
            'messages' => array(
                'error' => esc_html__('Error loading results.', 'alynt-404-sitemap'),
                'noResults' => esc_html__('No results found.', 'alynt-404-sitemap'),
            ),
        );
    }

    /**
     * Add custom rewrite rules.
     *
     * @since    1.0.0
     * @return   void Registers the sitemap rewrite rule.
     */
    public function add_rewrite_rules() {
        $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings');
        $slug = $settings['url_slug'] ?? 'sitemap';

        add_rewrite_rule(
            '^' . $slug . '/?$',
            'index.php?' . ALYNT_404_PREFIX . 'sitemap=1',
            'top'
        );
    }

    /**
     * Add meta tags to head.
     *
     * @since    1.0.0
     * @return   void Outputs meta description and custom CSS for plugin-managed views.
     */
    public function add_meta_tags() {
        if (is_404()) {
            $settings = get_option(ALYNT_404_PREFIX . '404_settings');
            if (!empty($settings['meta_description'])) {
                echo '<meta name="description" content="' . esc_attr($settings['meta_description']) . '" />' . "\n";
            }
            if (!empty($settings['custom_css'])) {
                echo '<style type="text/css">' . wp_strip_all_tags($settings['custom_css']) . '</style>' . "\n";
            }
        } elseif (get_query_var(ALYNT_404_PREFIX . 'sitemap')) {
            $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings');
            if (!empty($settings['meta_description'])) {
                echo '<meta name="description" content="' . esc_attr($settings['meta_description']) . '" />' . "\n";
            }
            if (!empty($settings['custom_css'])) {
                echo '<style type="text/css">' . wp_strip_all_tags($settings['custom_css']) . '</style>' . "\n";
            }
        }
    }

    /**
     * Get responsive classes based on settings.
     *
     * @since    1.0.0
     * @return   string    Responsive layout classes for the sitemap template.
     */
    public function get_responsive_classes() {
        $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings');
        $classes = array(
            'desktop-cols-' . ($settings['columns_desktop'] ?? 4),
            'tablet-cols-' . ($settings['columns_tablet'] ?? 2),
            'mobile-cols-' . ($settings['columns_mobile'] ?? 1)
        );
        return implode(' ', $classes);
    }

    /**
     * Check if current page is sitemap.
     *
     * @since    1.0.0
     * @return   boolean   True when the current request matches the sitemap route.
     */
    public function is_sitemap() {
        return (bool) get_query_var(ALYNT_404_PREFIX . 'sitemap');
    }

    /**
     * Add body classes.
     *
     * @since    1.0.0
     * @param    array    $classes    Current body classes.
     * @return   array    Updated body classes for plugin-rendered pages.
     */
    public function add_body_classes($classes) {
        if (is_404()) {
            $classes[] = 'alynt-404-page';
        } elseif ($this->is_sitemap()) {
            $classes[] = 'alynt-sitemap-page';
        }
        return $classes;
    }

    /**
     * Filter document title for sitemap page.
     *
     * @since    1.0.0
     * @param    array    $title    The document title parts.
     * @return   array    Filtered document title parts.
     */
    public function filter_document_title($title) {
        if (get_query_var(ALYNT_404_PREFIX . 'sitemap')) {
            $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings');
            $title['title'] = !empty($settings['heading']) ? $settings['heading'] : 'Sitemap';
        }
        return $title;
    }

}

