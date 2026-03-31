<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders admin-side plugin functionality.
 *
 * @since 1.0.0
 */
class Alynt_404_Admin {

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
	 * Admin page renderer.
	 *
	 * @var Alynt_404_Admin_Page
	 */
	private $admin_page;

	/**
	 * Settings sanitizer.
	 *
	 * @var Alynt_404_Settings_Sanitizer
	 */
	private $sanitizer;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string                            $plugin_name The name of this plugin.
	 * @param string                            $version     The version of this plugin.
	 * @param Alynt_404_Admin_Page|null         $admin_page  Optional page renderer.
	 * @param Alynt_404_Settings_Sanitizer|null $sanitizer   Optional settings sanitizer.
	 */
	public function __construct( $plugin_name, $version, $admin_page = null, $sanitizer = null ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->admin_page  = null !== $admin_page ? $admin_page : new Alynt_404_Admin_Page( $plugin_name );
		$this->sanitizer   = null !== $sanitizer ? $sanitizer : new Alynt_404_Settings_Sanitizer();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		if ( ! $this->is_plugin_page( $screen ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'dashicons' );
		$asset = Alynt_404_Asset_Manifest::resolve(
			'assets/dist/admin/index.css',
			$this->version
		);

		wp_enqueue_style(
			$this->plugin_name,
			$asset['url'],
			array( 'wp-color-picker', 'dashicons' ),
			$asset['version'],
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! $this->is_plugin_page( $screen ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		$core_handle = $this->enqueue_admin_script_modules();

		wp_localize_script(
			$core_handle,
			'alynt404Vars',
			$this->get_localized_admin_vars()
		);
	}

	/**
	 * Enqueue split admin JS modules.
	 *
	 * @since 1.0.3
	 * @return string Core script handle.
	 */
	private function enqueue_admin_script_modules() {
		$asset       = Alynt_404_Asset_Manifest::resolve(
			'assets/dist/admin/index.js',
			$this->version
		);
		$core_handle = $this->plugin_name . '-admin-bundle';
		wp_enqueue_script(
			$core_handle,
			$asset['url'],
			array( 'jquery', 'wp-color-picker' ),
			$asset['version'],
			true
		);

		return $core_handle;
	}

	/**
	 * Localized data for admin scripts.
	 *
	 * @since 1.0.3
	 * @return array Localized data passed to admin scripts.
	 */
	private function get_localized_admin_vars() {
		return array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'messages' => array(
				'saveSuccess'              => __( 'Settings saved successfully.', 'alynt-404-sitemap' ),
				'saveError'                => __( 'Error saving settings.', 'alynt-404-sitemap' ),
				'saving'                   => __( 'Saving…', 'alynt-404-sitemap' ),
				'resetting'                => __( 'Resetting…', 'alynt-404-sitemap' ),
				'confirmReset'             => __( 'Are you sure you want to reset these settings to defaults?', 'alynt-404-sitemap' ),
				'unsavedChanges'           => __( 'You have unsaved changes. If you leave this page, your changes will be lost.', 'alynt-404-sitemap' ),
				'removeLinkConfirm'        => __( 'Remove this quick link? Any unsaved text in this row will be lost.', 'alynt-404-sitemap' ),
				'mediaTitle'               => __( 'Choose Image', 'alynt-404-sitemap' ),
				'mediaButton'              => __( 'Select', 'alynt-404-sitemap' ),
				/* translators: %d: Maximum number of quick-link buttons per row. */
				'maxButtonsPerRow'         => __( 'Maximum %d buttons allowed per row.', 'alynt-404-sitemap' ),
				'newLinkTitle'             => __( 'New Link', 'alynt-404-sitemap' ),
				'invalidSlug'              => __( 'URL slug can only contain letters, numbers, and hyphens.', 'alynt-404-sitemap' ),
				'buttonTextAndUrlRequired' => __( 'Both text and URL are required for button links.', 'alynt-404-sitemap' ),
				'invalidUrlOrPath'         => __( 'Please enter a valid URL or relative path.', 'alynt-404-sitemap' ),
				'invalidColor'             => __( 'Enter a valid hex color, like #2271b1.', 'alynt-404-sitemap' ),
				/* translators: %d: Position number of the quick link button (1-based). */
				'buttonLinkTextLabel'      => __( 'Quick link %d: button text', 'alynt-404-sitemap' ),
				/* translators: %d: Position number of the quick link button (1-based). */
				'buttonLinkUrlLabel'       => __( 'Quick link %d: button URL', 'alynt-404-sitemap' ),
				'previewHeadingExample'    => __( 'Heading Example', 'alynt-404-sitemap' ),
				'previewParagraphExample'  => __( 'Paragraph text example', 'alynt-404-sitemap' ),
				'previewLinkExample'       => __( 'Link example', 'alynt-404-sitemap' ),
				'previewButtonExample'     => __( 'Button example', 'alynt-404-sitemap' ),
				'previewSearchPlaceholder' => __( 'Search example', 'alynt-404-sitemap' ),
			),
		);
	}

	/**
	 * Add plugin admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( '404 & Sitemap Settings', 'alynt-404-sitemap' ),
			__( '404 & Sitemap', 'alynt-404-sitemap' ),
			'manage_options',
			$this->plugin_name,
			array( $this->admin_page, 'display_plugin_admin_page' ),
			'dashicons-layout'
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		// General settings.
		register_setting(
			ALYNT_404_PREFIX . 'general_settings',
			ALYNT_404_PREFIX . 'colors',
			array( $this->sanitizer, 'sanitize_colors' )
		);

		// 404 settings.
		register_setting(
			ALYNT_404_PREFIX . '404_settings',
			ALYNT_404_PREFIX . '404_settings',
			array( $this->sanitizer, 'sanitize_404_settings' )
		);

		// Sitemap settings.
		register_setting(
			ALYNT_404_PREFIX . 'sitemap_settings',
			ALYNT_404_PREFIX . 'sitemap_settings',
			array( $this->sanitizer, 'sanitize_sitemap_settings' )
		);
	}

	/**
	 * Check if current page is plugin settings page.
	 *
	 * @since    1.0.0
	 * @param    WP_Screen $screen    Current screen object.
	 * @return   boolean
	 */
	private function is_plugin_page( $screen ) {
		if ( ! $screen || ! isset( $screen->id ) ) {
			return false;
		}
		return strpos( $screen->id, $this->plugin_name ) !== false;
	}
}
