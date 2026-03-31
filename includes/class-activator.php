<?php
/**
 * Fired during plugin activation.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation routines.
 *
 * @since 1.0.0
 */
class Alynt_404_Activator {

	/**
	 * Initialize plugin on activation.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::setup_directories();
		self::set_default_options();
		self::setup_rewrite_rules();
	}

	/**
	 * Create necessary directories.
	 *
	 * @since 1.0.0
	 */
	private static function setup_directories() {
		// Get WordPress upload directory.
		$upload_dir = wp_upload_dir();
		$css_dir    = trailingslashit( $upload_dir['basedir'] ) . 'alynt-404-sitemap';

		// Create directory if it doesn't exist.
		if ( ! file_exists( $css_dir ) ) {
			wp_mkdir_p( $css_dir );

			// Create .htaccess to protect directory.
			$htaccess_content  = "# Disable directory browsing\n";
			$htaccess_content .= "Options -Indexes\n\n";
			$htaccess_content .= "# Allow only CSS files\n";
			$htaccess_content .= "<FilesMatch \"^.*\\.css$\">\n";
			$htaccess_content .= "    Order Allow,Deny\n";
			$htaccess_content .= "    Allow from all\n";
			$htaccess_content .= "</FilesMatch>\n";

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writes generated protection file during one-time activation.
			file_put_contents( $css_dir . '/.htaccess', $htaccess_content );

			// Create index.php to prevent directory listing.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writes generated protection file during one-time activation.
			file_put_contents( $css_dir . '/index.php', '<?php // Silence is golden' );
		}
	}

	/**
	 * Set default plugin options.
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		if ( ! class_exists( 'Alynt_404_Settings_Defaults' ) ) {
			require_once ALYNT_404_PATH . 'includes/class-settings-defaults.php';
		}

		$default_colors  = Alynt_404_Settings_Defaults::get_color_defaults();
		$default_404     = Alynt_404_Settings_Defaults::get_404_defaults();
		$default_sitemap = Alynt_404_Settings_Defaults::get_sitemap_defaults();

		// Add options only if they don't exist.
		if ( false === get_option( ALYNT_404_PREFIX . 'colors' ) ) {
			add_option( ALYNT_404_PREFIX . 'colors', $default_colors );
		}
		if ( false === get_option( ALYNT_404_PREFIX . '404_settings' ) ) {
			add_option( ALYNT_404_PREFIX . '404_settings', $default_404 );
		}
		if ( false === get_option( ALYNT_404_PREFIX . 'sitemap_settings' ) ) {
			add_option( ALYNT_404_PREFIX . 'sitemap_settings', $default_sitemap );
		}

		// Set version option for future updates.
		update_option( ALYNT_404_PREFIX . 'version', ALYNT_404_VERSION );
	}

	/**
	 * Setup rewrite rules for sitemap page.
	 *
	 * @since 1.0.0
	 */
	private static function setup_rewrite_rules() {
		$settings     = get_option( ALYNT_404_PREFIX . 'sitemap_settings', array() );
		$sitemap_slug = $settings['url_slug'] ?? 'sitemap';

		// Add rewrite rule for sitemap.
		add_rewrite_rule(
			'^' . $sitemap_slug . '/?$',
			'index.php?' . ALYNT_404_PREFIX . 'sitemap=1',
			'top'
		);

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
