<?php
/**
 * The core plugin class.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Sitemap {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Alynt_404_Loader    $loader    Maintains and registers all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->version = ALYNT_404_VERSION;
        $this->plugin_name = 'alynt-404-sitemap';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters
        require_once ALYNT_404_PATH . 'includes/class-loader.php';

        // The class responsible for defining all admin-specific functionality
        require_once ALYNT_404_PATH . 'admin/class-admin.php';

        // The class responsible for defining all public-facing functionality
        require_once ALYNT_404_PATH . 'public/class-public.php';

        // The class responsible for handling AJAX requests
        require_once ALYNT_404_PATH . 'includes/class-ajax-handler.php';

        // The class responsible for managing colors
        require_once ALYNT_404_PATH . 'includes/class-color-manager.php';

        // The class responsible for handling post types
        require_once ALYNT_404_PATH . 'includes/class-post-types.php';

        // The class responsible for loading templates
        require_once ALYNT_404_PATH . 'includes/class-template-loader.php';

        // The class responsible for utility functions
        require_once ALYNT_404_PATH . 'includes/class-utilities.php';

        $this->loader = new Alynt_404_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Alynt_404_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = Alynt_404_Public::get_instance();
        $template_loader = Alynt_404_Template_Loader::get_instance();
        
        // Initialize AJAX handler
        $ajax_handler = new Alynt_404_Ajax_Handler();

        // Public assets
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Template handling
        $this->loader->add_filter('404_template', $template_loader, 'load_404_template', 20);
        $this->loader->add_filter('template_include', $template_loader, 'load_sitemap_template');

        // Add custom rewrite rules
        $this->loader->add_action('init', $plugin_public, 'add_rewrite_rules');
        
        // Add meta tags
        $this->loader->add_action('wp_head', $plugin_public, 'add_meta_tags');

        // Register AJAX actions
        $this->loader->add_action('wp_ajax_alynt_404_search', $ajax_handler, 'handle_search');
        $this->loader->add_action('wp_ajax_nopriv_alynt_404_search', $ajax_handler, 'handle_search');
    }

    /**
     * Run the loader to execute all hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Alynt_404_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}