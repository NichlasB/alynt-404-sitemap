<?php
/**
 * The core plugin class.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps plugin dependencies and hooks.
 *
 * @since 1.0.0
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
		$this->version     = ALYNT_404_VERSION;
		$this->plugin_name = 'alynt-404-sitemap';

		$this->load_dependencies();

		if ( is_admin() && ! wp_doing_ajax() ) {
			$this->define_admin_hooks();
		}

		if ( ! is_admin() ) {
			$this->define_public_hooks();
		}

		if ( wp_doing_ajax() ) {
			$this->define_ajax_hooks();
		}

		$this->define_shared_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// The class responsible for orchestrating the actions and filters.
		require_once ALYNT_404_PATH . 'includes/class-loader.php';
		require_once ALYNT_404_PATH . 'includes/class-asset-manifest.php';

		if ( is_admin() && ! wp_doing_ajax() ) {
			require_once ALYNT_404_PATH . 'admin/class-admin.php';
			require_once ALYNT_404_PATH . 'admin/class-admin-page.php';
			require_once ALYNT_404_PATH . 'admin/class-settings-sanitizer.php';
			require_once ALYNT_404_PATH . 'includes/class-color-manager.php';
			require_once ALYNT_404_PATH . 'includes/class-post-types.php';
			require_once ALYNT_404_PATH . 'includes/class-utilities.php';
			require_once ALYNT_404_PATH . 'includes/class-settings-defaults.php';
		}

		if ( ! is_admin() ) {
			require_once ALYNT_404_PATH . 'public/class-public.php';
			require_once ALYNT_404_PATH . 'includes/class-color-manager.php';
			require_once ALYNT_404_PATH . 'includes/class-template-loader.php';
		}

		if ( wp_doing_ajax() ) {
			require_once ALYNT_404_PATH . 'includes/class-ajax-handler.php';
		}

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

		$admin_page   = new Alynt_404_Admin_Page( $this->get_plugin_name() );
		$sanitizer    = new Alynt_404_Settings_Sanitizer();
		$plugin_admin = new Alynt_404_Admin( $this->get_plugin_name(), $this->get_version(), $admin_page, $sanitizer );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public   = Alynt_404_Public::get_instance();
		$template_loader = Alynt_404_Template_Loader::get_instance();

		// Public assets.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Template handling.
		$this->loader->add_filter( '404_template', $template_loader, 'load_404_template', 20 );
		$this->loader->add_filter( 'template_include', $template_loader, 'load_sitemap_template' );

		// Add meta tags.
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_meta_tags' );
	}

	/**
	 * Register AJAX-specific hooks only when WordPress is handling an AJAX request.
	 *
	 * @since 1.0.3
	 * @access private
	 * @return void
	 */
	private function define_ajax_hooks() {
		new Alynt_404_Ajax_Handler();
	}

	/**
	 * Register hooks needed across request types.
	 *
	 * @since 1.0.3
	 * @access private
	 * @return void
	 */
	private function define_shared_hooks() {
		$this->loader->add_action( 'init', $this, 'add_sitemap_rewrite_rules' );
		$this->loader->add_action( 'before_delete_post', $this, 'cleanup_deleted_post_references' );
		$this->loader->add_action( 'delete_attachment', $this, 'cleanup_deleted_post_references' );
	}

	/**
	 * Register the sitemap rewrite rule without instantiating the public stack.
	 *
	 * @since 1.0.3
	 * @return void
	 */
	public function add_sitemap_rewrite_rules() {
		$settings = get_option( ALYNT_404_PREFIX . 'sitemap_settings', array() );
		$slug     = $settings['url_slug'] ?? 'sitemap';

		add_rewrite_rule(
			'^' . $slug . '/?$',
			'index.php?' . ALYNT_404_PREFIX . 'sitemap=1',
			'top'
		);
	}

	/**
	 * Remove deleted content references from persisted settings.
	 *
	 * @since 1.0.3
	 * @param int $post_id Deleted post or attachment ID.
	 * @return void
	 */
	public function cleanup_deleted_post_references( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return;
		}

		$this->cleanup_featured_image_setting( ALYNT_404_PREFIX . '404_settings', $post_id );
		$this->cleanup_featured_image_setting( ALYNT_404_PREFIX . 'sitemap_settings', $post_id );
		$this->cleanup_excluded_ids_setting( ALYNT_404_PREFIX . 'sitemap_settings', $post_id );
	}

	/**
	 * Clear a stored featured image reference when the attachment is deleted.
	 *
	 * @since 1.0.3
	 * @param string $option_name Settings option name.
	 * @param int    $post_id     Deleted post or attachment ID.
	 * @return void
	 */
	private function cleanup_featured_image_setting( $option_name, $post_id ) {
		$settings = get_option( $option_name, array() );
		if ( empty( $settings['featured_image'] ) || absint( $settings['featured_image'] ) !== $post_id ) {
			return;
		}

		$settings['featured_image'] = 0;
		update_option( $option_name, $settings );
	}

	/**
	 * Remove deleted posts from the sitemap exclusion list.
	 *
	 * @since 1.0.3
	 * @param string $option_name Settings option name.
	 * @param int    $post_id     Deleted post ID.
	 * @return void
	 */
	private function cleanup_excluded_ids_setting( $option_name, $post_id ) {
		$settings = get_option( $option_name, array() );
		if ( empty( $settings['excluded_ids'] ) ) {
			return;
		}

		$excluded_ids = array_values(
			array_filter(
				array_map(
					'absint',
					array_map( 'trim', explode( ',', (string) $settings['excluded_ids'] ) )
				)
			)
		);

		if ( ! in_array( $post_id, $excluded_ids, true ) ) {
			return;
		}

		$settings['excluded_ids'] = implode(
			',',
			array_values( array_diff( $excluded_ids, array( $post_id ) ) )
		);
		update_option( $option_name, $settings );
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
