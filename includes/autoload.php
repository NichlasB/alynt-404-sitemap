<?php
/**
 * Lightweight plugin autoloader for local tooling and tests.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		$class_map = array(
			'Alynt_404_Activator'          => 'includes/class-alynt-404-activator.php',
			'Alynt_404_Admin'              => 'admin/class-alynt-404-admin.php',
			'Alynt_404_Admin_Page'         => 'admin/class-alynt-404-admin-page.php',
			'Alynt_404_Ajax_Handler'       => 'includes/class-alynt-404-ajax-handler.php',
			'Alynt_404_Color_Manager'      => 'includes/class-alynt-404-color-manager.php',
			'Alynt_404_Deactivator'        => 'includes/class-alynt-404-deactivator.php',
			'Alynt_404_Loader'             => 'includes/class-alynt-404-loader.php',
			'Alynt_404_Post_Types'         => 'includes/class-alynt-404-post-types.php',
			'Alynt_404_Public'             => 'public/class-alynt-404-public.php',
			'Alynt_404_Settings_Defaults'  => 'includes/class-alynt-404-settings-defaults.php',
			'Alynt_404_Settings_Sanitizer' => 'admin/class-alynt-404-settings-sanitizer.php',
			'Alynt_404_Sitemap'            => 'includes/class-alynt-404-sitemap.php',
			'Alynt_404_Template_Loader'    => 'includes/class-alynt-404-template-loader.php',
			'Alynt_404_Utilities'          => 'includes/class-alynt-404-utilities.php',
		);

		if ( ! isset( $class_map[ $class_name ] ) ) {
			return;
		}

		$file = dirname( __DIR__ ) . '/' . $class_map[ $class_name ];

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);
