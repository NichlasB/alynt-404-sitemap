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
			'Alynt_404_Activator'          => 'includes/class-activator.php',
			'Alynt_404_Admin'              => 'admin/class-admin.php',
			'Alynt_404_Admin_Page'         => 'admin/class-admin-page.php',
			'Alynt_404_Ajax_Handler'       => 'includes/class-ajax-handler.php',
			'Alynt_404_Color_Manager'      => 'includes/class-color-manager.php',
			'Alynt_404_Deactivator'        => 'includes/class-deactivator.php',
			'Alynt_404_Loader'             => 'includes/class-loader.php',
			'Alynt_404_Post_Types'         => 'includes/class-post-types.php',
			'Alynt_404_Public'             => 'public/class-public.php',
			'Alynt_404_Settings_Defaults'  => 'includes/class-settings-defaults.php',
			'Alynt_404_Settings_Sanitizer' => 'admin/class-settings-sanitizer.php',
			'Alynt_404_Sitemap'            => 'includes/class-alynt-404-sitemap.php',
			'Alynt_404_Template_Loader'    => 'includes/class-template-loader.php',
			'Alynt_404_Utilities'          => 'includes/class-utilities.php',
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
