<?php
/**
 * Centralized default settings for the plugin.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

class Alynt_404_Settings_Defaults {

    /**
     * Get default color settings.
     *
     * @since 1.0.3
     * @return array Default color settings.
     */
    public static function get_color_defaults() {
        return array(
            'headings' => '#333333',
            'paragraph' => '#333333',
            'links' => '#0073aa',
            'buttons' => '#0073aa',
            'button_text' => '#ffffff',
            'search_border' => '#dddddd',
            'search_text' => '#333333',
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
            'heading' => "Oops! That page can't be found.",
            'message' => "Looks like this page took a wrong turn. Let's get you back to where you need to be.",
            'button_links' => array(),
            'search_post_types' => array('post', 'page'),
            'meta_description' => 'Page not found. Use our search or navigation to find what you are looking for.',
            'custom_css' => '',
            'featured_image' => 0,
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
            'heading' => 'Sitemap',
            'message' => "Here's our website at a glance. Use this sitemap to quickly find what you're looking for.",
            'url_slug' => 'sitemap',
            'post_types' => array('post', 'page'),
            'excluded_ids' => '',
            'meta_description' => 'Looking for something specific? Use our sitemap to easily navigate all our website content.',
            'custom_css' => '',
            'featured_image' => 0,
            'columns_desktop' => 4,
            'columns_tablet' => 2,
            'columns_mobile' => 1,
            'sort_order' => array(
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
    public static function get_for_tab($tab) {
        switch ($tab) {
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
    public static function reset_tab($tab) {
        $defaults = self::get_for_tab($tab);
        if (!is_array($defaults)) {
            return false;
        }

        switch ($tab) {
            case 'general':
                delete_option(ALYNT_404_PREFIX . 'colors');
                add_option(ALYNT_404_PREFIX . 'colors', $defaults);
                break;
            case '404':
                delete_option(ALYNT_404_PREFIX . '404_settings');
                add_option(ALYNT_404_PREFIX . '404_settings', $defaults);
                break;
            case 'sitemap':
                delete_option(ALYNT_404_PREFIX . 'sitemap_settings');
                add_option(ALYNT_404_PREFIX . 'sitemap_settings', $defaults);
                break;
        }

        return true;
    }
}

