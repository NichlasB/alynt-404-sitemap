<?php
/**
 * Asset path and version helper.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Centralized asset resolver for built files.
 */
class Alynt_404_Asset_Manifest {

	/**
	 * Resolve a plugin asset URL and version.
	 *
	 * @since 1.0.3
	 *
	 * @param string $built_relative  Relative path to built asset.
	 * @param string $default_version Plugin version fallback.
	 *
	 * @return array
	 */
	public static function resolve( $built_relative, $default_version ) {
		static $cache = array();

		$cache_key = $built_relative . '|' . $default_version;
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$built_path = ALYNT_404_PATH . ltrim( $built_relative, '/\\' );
		$is_built   = file_exists( $built_path );

		$cache[ $cache_key ] = array(
			'url'       => ALYNT_404_URL . str_replace( '\\', '/', ltrim( $built_relative, '/\\' ) ),
			'version'   => $is_built ? (string) filemtime( $built_path ) : (string) $default_version,
			'is_built'  => $is_built,
			'file_path' => $built_path,
		);

		return $cache[ $cache_key ];
	}
}
