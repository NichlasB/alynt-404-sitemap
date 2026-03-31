<?php
/**
 * Utility functions for the plugin.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.0
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Provides shared utility helpers for plugin internals.
 *
 * @since 1.0.0
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
		if ( null === self::$instance ) {
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
	public function validate_url( $url ) {
		$url = trim( $url );

		// Handle empty URLs.
		if ( empty( $url ) ) {
			return false;
		}

		// Check if it's a relative path starting with /.
		if ( strpos( $url, '/' ) === 0 ) {
			return esc_url_raw( $url );
		}

		// Check if it's a relative path without leading /.
		if ( ! preg_match( '~^(?:f|ht)tps?://~i', $url ) ) {
			return esc_url_raw( '/' . ltrim( $url, '/' ) );
		}

		// It's an absolute URL, validate it normally.
		$url = esc_url_raw( $url );
		return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : false;
	}

	/**
	 * Sanitize and validate slug.
	 *
	 * @since 1.0.0
	 * @param string $slug Slug to validate.
	 * @return string Sanitized slug.
	 */
	public function sanitize_slug( $slug ) {
		return sanitize_title( trim( $slug ) );
	}

	/**
	 * Sanitize CSS input.
	 *
	 * @since 1.0.0
	 * @param string $css CSS to sanitize.
	 * @return string Sanitized CSS.
	 */
	public function sanitize_css( $css ) {
		if ( empty( $css ) ) {
			return '';
		}

		// Remove any HTML tags.
		$css = wp_strip_all_tags( $css );

		// Remove any null characters.
		$css = str_replace( chr( 0 ), '', $css );

		// Remove anything that might be used in a script tag.
		$css = str_replace( 'javascript:', '', $css );
		$css = str_replace( 'expression(', '', $css );
		$css = str_replace( 'vbscript:', '', $css );

		// Remove comments.
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove spaces before colons.
		$css = preg_replace( '/\s+:/', ':', $css );

		// Normalize whitespace.
		$css = preg_replace( '/\s+/', ' ', $css );

		return trim( $css );
	}

	/**
	 * Check if slug is available.
	 *
	 * @since 1.0.0
	 * @param string $slug Slug to check.
	 * @return boolean True if available.
	 */
	public function is_slug_available( $slug ) {
		$slug = $this->sanitize_slug( $slug );
		if ( '' === $slug ) {
			return false;
		}

		$public_post_types = get_post_types(
			array(
				'public' => true,
			),
			'names'
		);

		// Check pages.
		$page = get_page_by_path( $slug, OBJECT, $public_post_types );

		if ( $page ) {
			return false;
		}

		foreach ( $public_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( ! $post_type_object ) {
				continue;
			}

			$archive_slug = $post_type_object->has_archive;
			if ( true === $archive_slug ) {
				$archive_slug = $post_type_object->rewrite['slug'] ?? $post_type;
			}

			if ( is_string( $archive_slug ) && trim( $archive_slug, '/' ) === $slug ) {
				return false;
			}
		}

		$public_taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'objects'
		);

		foreach ( $public_taxonomies as $taxonomy ) {
			$taxonomy_slug = $taxonomy->rewrite['slug'] ?? '';
			if ( is_string( $taxonomy_slug ) && trim( $taxonomy_slug, '/' ) === $slug ) {
				return false;
			}
		}

		// Check if slug is used by other plugins or custom routes.
		$reserved_slugs = apply_filters(
			ALYNT_404_PREFIX . 'reserved_slugs',
			array(
				'wp-admin',
				'wp-content',
				'wp-includes',
				'wp-json',
				'feed',
				'embed',
				'sitemap.xml',
				'page',
				'comments',
				'author',
			)
		);

		if ( in_array( $slug, $reserved_slugs, true ) ) {
			return false;
		}

		$rewrite_rules = get_option( 'rewrite_rules', array() );
		if ( is_array( $rewrite_rules ) ) {
			$root_rule = '^' . $slug . '/?$';
			if ( isset( $rewrite_rules[ $root_rule ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Generate unique slug.
	 *
	 * @since 1.0.0
	 * @param string $base_slug Base slug.
	 * @return string Unique slug.
	 */
	public function generate_unique_slug( $base_slug ) {
		$slug          = $this->sanitize_slug( $base_slug );
		$counter       = 2;
		$original_slug = $slug;

		while ( ! $this->is_slug_available( $slug ) ) {
			$slug = $original_slug . '-' . $counter;
			++$counter;
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
	public function format_file_size( $bytes ) {
		$units  = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= pow( 1024, $pow );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * Clean filename.
	 *
	 * @since 1.0.0
	 * @param string $filename Filename to clean.
	 * @return string Cleaned filename.
	 */
	public function clean_filename( $filename ) {
		// Remove special characters.
		$filename = preg_replace( '/[^a-zA-Z0-9\-\_\.]/', '', $filename );
		// Remove multiple dots.
		$filename = preg_replace( '/\.+/', '.', $filename );
		// Ensure safe extension.
		$allowed_extensions = array( 'css', 'js', 'jpg', 'jpeg', 'png', 'gif' );
		$parts              = explode( '.', $filename );
		$extension          = strtolower( end( $parts ) );

		if ( ! in_array( $extension, $allowed_extensions, true ) ) {
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
	public function generate_meta_description( $text, $length = 160 ) {
		// Strip HTML tags.
		$text = wp_strip_all_tags( $text );
		// Convert entities.
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		// Remove extra whitespace.
		$text = preg_replace( '/\s+/', ' ', $text );
		// Trim to length.
		$text = wp_trim_words( $text, $length, '' );

		return sanitize_text_field( $text );
	}

	/**
	 * Check if request is AJAX.
	 *
	 * @since 1.0.0
	 * @return boolean True if AJAX request.
	 */
	public function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Safe redirect.
	 *
	 * @since 1.0.0
	 * @param string $url URL to redirect to.
	 * @param int    $status HTTP status code.
	 * @return void
	 */
	public function safe_redirect( $url, $status = 302 ) {
		$url = wp_sanitize_redirect( $url );
		$url = wp_validate_redirect( $url, home_url( '/' ) );

		wp_safe_redirect( $url, $status );
		exit;
	}

	/**
	 * Get plugin asset URL.
	 *
	 * @since 1.0.0
	 * @param string $path Asset path.
	 * @return string Asset URL.
	 */
	public function get_asset_url( $path ) {
		return ALYNT_404_URL . ltrim( $path, '/' );
	}

	/**
	 * Check if current user can manage plugin.
	 *
	 * @since 1.0.0
	 * @return boolean True if user can manage plugin.
	 */
	public function current_user_can_manage() {
		return current_user_can( 'manage_options' );
	}
	/**
	 * Clean up expired transients.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function cleanup_transients() {
		global $wpdb;
		$prefix_like  = $wpdb->esc_like( ALYNT_404_PREFIX );
		$current_time = time();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Targeted query only checks plugin-owned transient timeout rows.
		$expired_timeouts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'_transient_timeout_' . $prefix_like . '%',
				$current_time
			)
		);

		if ( empty( $expired_timeouts ) ) {
			return;
		}

		foreach ( $expired_timeouts as $timeout_name ) {
			$transient_name = str_replace( '_transient_timeout_', '_transient_', $timeout_name );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Targeted cleanup only removes expired plugin transients.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name IN ( %s, %s )",
					$timeout_name,
					$transient_name
				)
			);
		}
	}
}
