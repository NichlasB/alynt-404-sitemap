<?php
/**
 * Fired during plugin activation.
 *
 * @package Alynt_404_Sitemap
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
        // Get WordPress upload directory
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit($upload_dir['basedir']) . 'alynt-404-sitemap';

        // Create directory if it doesn't exist
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
            
            // Create .htaccess to protect directory
            $htaccess_content = "# Disable directory browsing\n";
            $htaccess_content .= "Options -Indexes\n\n";
            $htaccess_content .= "# Allow only CSS files\n";
            $htaccess_content .= "<FilesMatch \"^.*\\.css$\">\n";
            $htaccess_content .= "    Order Allow,Deny\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            
            file_put_contents($css_dir . '/.htaccess', $htaccess_content);
            
            // Create index.php to prevent directory listing
            file_put_contents($css_dir . '/index.php', '<?php // Silence is golden');
        }
    }

    /**
     * Set default plugin options.
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        // Default color options
        $default_colors = array(
            'headings' => '#333333',
            'paragraph' => '#333333',
            'links' => '#0073aa',
            'buttons' => '#0073aa',
            'button_text' => '#ffffff',
            'search_border' => '#dddddd',
            'search_text' => '#333333',
            'search_background' => '#ffffff'
        );

        // Default 404 page options
        $default_404 = array(
            'heading' => "Oops! That page can't be found.",
            'message' => "Looks like this page took a wrong turn. Let's get you back to where you need to be.",
            'button_links' => array(),
            'search_post_types' => array('post', 'page'),
            'meta_description' => 'Page not found. Use our search or navigation to find what you are looking for.',
            'custom_css' => '',
            'featured_image' => 0
        );

        // Default sitemap options
        $default_sitemap = array(
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
                'page' => 'alphabetical'
            )
        );

        // Add options only if they don't exist
        if (false === get_option(ALYNT_404_PREFIX . 'colors')) {
            add_option(ALYNT_404_PREFIX . 'colors', $default_colors);
        }
        if (false === get_option(ALYNT_404_PREFIX . '404_settings')) {
            add_option(ALYNT_404_PREFIX . '404_settings', $default_404);
        }
        if (false === get_option(ALYNT_404_PREFIX . 'sitemap_settings')) {
            add_option(ALYNT_404_PREFIX . 'sitemap_settings', $default_sitemap);
        }

        // Set version option for future updates
        update_option(ALYNT_404_PREFIX . 'version', ALYNT_404_VERSION);
    }

    /**
     * Setup rewrite rules for sitemap page.
     *
     * @since 1.0.0
     */
    private static function setup_rewrite_rules() {
        $sitemap_slug = get_option(ALYNT_404_PREFIX . 'sitemap_settings')['url_slug'] ?? 'sitemap';
        
        // Add rewrite rule for sitemap
        add_rewrite_rule(
            '^' . $sitemap_slug . '/?$',
            'index.php?' . ALYNT_404_PREFIX . 'sitemap=1',
            'top'
        );

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}