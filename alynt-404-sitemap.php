<?php
/**
 * Plugin Name: Alynt 404 & Sitemap
 * Plugin URI: https://github.com/NichlasB/alynt-404-sitemap
 * Description: Enhanced 404 page and dynamic sitemap generator with extensive customization options.
 * Version: 1.0.2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Alynt
 * Author URI: https://alynt.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: alynt-404-sitemap
 *
 * GitHub Repository: NichlasB/alynt-404-sitemap
 * GitHub Branch: main
 *
 * @package Alynt_404_Sitemap
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('ALYNT_404_VERSION', '1.0.2');

// Plugin directory path
define('ALYNT_404_PATH', plugin_dir_path(__FILE__));

// Plugin directory URL
define('ALYNT_404_URL', plugin_dir_url(__FILE__));

// Plugin prefix
define('ALYNT_404_PREFIX', 'alynt_404_');

/**
 * The code that runs during plugin activation.
 */
function alynt_404_activate() {
    require_once ALYNT_404_PATH . 'includes/class-activator.php';
    Alynt_404_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function alynt_404_deactivate() {
    require_once ALYNT_404_PATH . 'includes/class-deactivator.php';
    Alynt_404_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'alynt_404_activate');
register_deactivation_hook(__FILE__, 'alynt_404_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once ALYNT_404_PATH . 'includes/class-alynt-404-sitemap.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.1
 */
function alynt_404_run() {
    $plugin = new Alynt_404_Sitemap();
    $plugin->run();
}
alynt_404_run();