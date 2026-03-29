<?php
/**
 * Bootstrap file for plugin tests.
 *
 * @package Alynt_404_Sitemap
 */

// Define test constants.
define( 'PLUGIN_PATH', dirname( __DIR__ ) );
define( 'ABSPATH', PLUGIN_PATH . DIRECTORY_SEPARATOR );
define( 'WP_PLUGIN_DIR', dirname( PLUGIN_PATH ) );
define( 'WPINC', 'wp-includes' );

// Load Composer autoloader.
require_once PLUGIN_PATH . '/vendor/autoload.php';

// Shim the WordPress functions needed to load the plugin in unit tests.
if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( $value ) {
        return rtrim( $value, '/\\' ) . DIRECTORY_SEPARATOR;
    }
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return trailingslashit( dirname( $file ) );
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url( $file ) {
        return 'http://example.org/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return basename( dirname( $file ) ) . '/' . basename( $file );
    }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook( $file, $callback ) {
    }
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook( $file, $callback ) {
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    }
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ) {
        return true;
    }
}

// Load plugin autoloader for local tooling.
require_once PLUGIN_PATH . '/includes/autoload.php';

// Load plugin main file.
require_once PLUGIN_PATH . '/alynt-404-sitemap.php';
