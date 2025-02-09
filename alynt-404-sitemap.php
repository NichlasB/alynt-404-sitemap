<?php
/**
 * Plugin Name: Alynt 404 & Sitemap
 * Plugin URI: 
 * Description: Enhanced 404 page and dynamic sitemap generator with extensive customization options.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Alynt
 * Author URI: https://alynt.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: alynt-404-sitemap
 *
 * @package Alynt_404_Sitemap
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin Update Checker
$composerAutoloaderPath = __DIR__ . '/vendor/autoload.php';
$composerAutoloaderExists = false;

// Check if any Composer autoloader is already loaded
foreach (get_included_files() as $file) {
    if (strpos($file, 'vendor/composer/autoload_real.php') !== false) {
        $composerAutoloaderExists = true;
        break;
    }
}

// Only load our autoloader if no other Composer autoloader is loaded
if (!$composerAutoloaderExists) {
    require_once $composerAutoloaderPath;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/NichlasB/alynt-404-sitemap',
        __FILE__,
        'alynt-404-sitemap'
    );

    // Set the branch that contains the stable release
    $myUpdateChecker->setBranch('main');
    
    // Enable GitHub releases
    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
}

// Plugin version
define('ALYNT_404_VERSION', '1.0.0');

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
 * @since 1.0.0
 */
function alynt_404_run() {
    $plugin = new Alynt_404_Sitemap();
    $plugin->run();
}
alynt_404_run();