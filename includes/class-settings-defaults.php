<?php
/**
 * Centralized default settings for the plugin.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Provides centralized default plugin settings.
 *
 * @since 1.0.3
 */
class Alynt_404_Settings_Defaults {

	/**
	 * Get default color settings.
	 *
	 * @since 1.0.3
	 * @return array Default color settings.
	 */
	public static function get_color_defaults() {
		return array(
			'headings'          => '#333333',
			'paragraph'         => '#333333',
			'links'             => '#0073aa',
			'buttons'           => '#0073aa',
			'button_text'       => '#ffffff',
			'search_border'     => '#dddddd',
			'search_text'       => '#333333',
			'search_background' => '#ffffff',
		);
	}

	/**
	 * Get default 404 settings.
	 *
	 * @since 1.0.3
	 * @return array Default 404 page settings.
	 */
	public static function get_404_defaults() {
		return array(
			'heading'           => __( "Oops! That page can't be found.", 'alynt-404-sitemap' ),
			'message'           => __( "Looks like this page took a wrong turn. Let's get you back to where you need to be.", 'alynt-404-sitemap' ),
			'button_links'      => array(),
			'search_post_types' => array( 'post', 'page' ),
			'meta_description'  => __( 'Page not found. Use our search or navigation to find what you are looking for.', 'alynt-404-sitemap' ),
			'custom_css'        => '',
			'featured_image'    => 0,
		);
	}

	/**
	 * Get default sitemap settings.
	 *
	 * @since 1.0.3
	 * @return array Default sitemap settings.
	 */
	public static function get_sitemap_defaults() {
		return array(
			'heading'          => __( 'Sitemap', 'alynt-404-sitemap' ),
			'message'          => __( "Here's our website at a glance. Use this sitemap to quickly find what you're looking for.", 'alynt-404-sitemap' ),
			'url_slug'         => 'sitemap',
			'post_types'       => array( 'post', 'page' ),
			'excluded_ids'     => '',
			'meta_description' => __( 'Looking for something specific? Use our sitemap to easily navigate all our website content.', 'alynt-404-sitemap' ),
			'custom_css'       => '',
			'featured_image'   => 0,
			'columns_desktop'  => 4,
			'columns_tablet'   => 2,
			'columns_mobile'   => 1,
			'sort_order'       => array(
				'post' => 'alphabetical',
				'page' => 'alphabetical',
			),
		);
	}

	/**
	 * Get defaults by admin tab key.
	 *
	 * @since 1.0.3
	 * @param string $tab Tab key.
	 * @return array|null Defaults for the requested tab, or null when unsupported.
	 */
	public static function get_for_tab( $tab ) {
		switch ( $tab ) {
			case 'general':
				return self::get_color_defaults();
			case '404':
				return self::get_404_defaults();
			case 'sitemap':
				return self::get_sitemap_defaults();
			default:
				return null;
		}
	}

	/**
	 * Persist tab defaults.
	 *
	 * @since 1.0.3
	 * @param string $tab Tab key.
	 * @return bool True when defaults were persisted for the tab.
	 */
	public static function reset_tab( $tab ) {
		$defaults = self::get_for_tab( $tab );
		if ( ! is_array( $defaults ) ) {
			return false;
		}

		switch ( $tab ) {
			case 'general':
				$option_name = ALYNT_404_PREFIX . 'colors';
				break;
			case '404':
				$option_name = ALYNT_404_PREFIX . '404_settings';
				break;
			case 'sitemap':
				$option_name = ALYNT_404_PREFIX . 'sitemap_settings';
				break;
			default:
				return false;
		}

		$existing = get_option( $option_name, null );
		if ( null === $existing ) {
			return add_option( $option_name, $defaults );
		}

		if ( $existing === $defaults ) {
			return true;
		}

		return update_option( $option_name, $defaults );
	}
}
