<?php
/**
 * Handle color management and CSS generation.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Color_Manager {

    /**
     * Store instance of the class.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of the class.
     */
    private static $instance = null;

    /**
     * CSS cache duration in seconds (24 hours).
     *
     * @since 1.0.0
     * @access private
     * @var int
     */
    private $cache_duration = 86400;

    /**
     * Get instance of the class.
     *
     * @since 1.0.0
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the class.
     *
     * @since 1.0.0
     */
    private function __construct() {
        add_action('update_option_' . ALYNT_404_PREFIX . 'colors', array($this, 'regenerate_css'), 10, 0);
    }

    /**
     * Generate CSS from color settings.
     *
     * @since 1.0.0
     * @return string Generated CSS.
     */
    public function generate_css() {
        $colors = get_option(ALYNT_404_PREFIX . 'colors', $this->get_default_colors());
        
        // Start output buffering
        ob_start();
        
        // 404 Page Styles
        ?>
        <?php if (!empty($colors['headings'])): ?>
        .alynt-404-page h1,
        .alynt-404-page h2,
        .alynt-404-page h3 {
            color: <?php echo esc_attr($colors['headings']); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['paragraph'])): ?>
        .alynt-404-page p {
            color: <?php echo esc_attr($colors['paragraph']); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['links'])): ?>
        .alynt-404-page a {
            color: <?php echo esc_attr($colors['links']); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['search_text']) || !empty($colors['search_background']) || !empty($colors['search_border'])): ?>
        .alynt-404-search input[type="text"] {
            <?php if (!empty($colors['search_text'])): ?>
            color: <?php echo esc_attr($colors['search_text']); ?>;
            <?php endif; ?>
            <?php if (!empty($colors['search_background'])): ?>
            background-color: <?php echo esc_attr($colors['search_background']); ?>;
            <?php endif; ?>
            <?php if (!empty($colors['search_border'])): ?>
            border-color: <?php echo esc_attr($colors['search_border']); ?>;
            <?php endif; ?>
        }
        <?php endif; ?>

        <?php if (!empty($colors['buttons']) || !empty($colors['button_text'])): ?>
        .alynt-404-button {
            <?php if (!empty($colors['buttons'])): ?>
            background-color: <?php echo esc_attr($colors['buttons']); ?>;
            <?php endif; ?>
            <?php if (!empty($colors['button_text'])): ?>
            color: <?php echo esc_attr($colors['button_text']); ?>;
            <?php endif; ?>
        }
        <?php endif; ?>

        /* Sitemap Styles */
        <?php if (!empty($colors['headings'])): ?>
        .alynt-sitemap h1,
        .alynt-sitemap h2 {
            color: <?php echo esc_attr($colors['headings']); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['paragraph'])): ?>
        .alynt-sitemap p {
            color: <?php echo esc_attr($colors['paragraph']); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['links'])): ?>
        .alynt-sitemap a {
            color: <?php echo esc_attr($colors['links']); ?>;
        }
        <?php endif; ?>

        /* Hover States */
        <?php if (!empty($colors['buttons'])): ?>
        .alynt-404-button:hover {
            background-color: <?php echo esc_attr($this->adjust_brightness($colors['buttons'], -15)); ?>;
        }
        <?php endif; ?>

        <?php if (!empty($colors['links'])): ?>
        .alynt-404-page a:hover,
        .alynt-sitemap a:hover {
            color: <?php echo esc_attr($this->adjust_brightness($colors['links'], -15)); ?>;
        }
        <?php endif; ?>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Regenerate and cache CSS file.
     *
     * @since 1.0.0
     */
    public function regenerate_css() {
        $css = $this->generate_css();
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit($upload_dir['basedir']) . 'alynt-404-sitemap';
        $css_file = $css_dir . '/custom-colors.css';

        // Create directory if it doesn't exist
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }

        // Write CSS file
        file_put_contents($css_file, $css);

        // Update file modification time for cache busting
        $version = time();
        update_option(ALYNT_404_PREFIX . 'css_version', $version);
    }

    /**
     * Get CSS file URL.
     *
     * @since 1.0.0
     * @return string URL to CSS file.
     */
    public function get_css_url() {
        $upload_dir = wp_upload_dir();
        $css_file = 'alynt-404-sitemap/custom-colors.css';
        $version = get_option(ALYNT_404_PREFIX . 'css_version', '1.0.0');

        return trailingslashit($upload_dir['baseurl']) . $css_file . '?ver=' . $version;
    }

    /**
     * Validate hex color.
     *
     * @since 1.0.0
     * @param string $color Color to validate.
     * @return boolean True if valid, false otherwise.
     */
    public function validate_color($color) {
        // Allow empty or null values
        if (empty($color)) {
            return true;
        }
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color);
    }

    /**
     * Get default colors.
     *
     * @since 1.0.0
     * @return array Default colors.
     */
    public function get_default_colors() {
        return array(
            'headings' => '#333333',
            'paragraph' => '#666666',
            'links' => '#0073aa',
            'buttons' => '#0073aa',
            'button_text' => '#ffffff',
            'search_border' => '#dddddd',
            'search_text' => '#333333',
            'search_background' => '#ffffff'
        );
    }

    /**
     * Adjust color brightness.
     *
     * @since 1.0.0
     * @param string $hex Hex color.
     * @param int    $steps Steps to adjust (-255 to 255).
     * @return string Adjusted hex color.
     */
    private function adjust_brightness($hex, $steps) {
        // Remove # if present
        $hex = ltrim($hex, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust each color
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Convert hex to RGBA.
     *
     * @since 1.0.0
     * @param string  $hex Hex color.
     * @param float   $alpha Alpha value (0-1).
     * @return string RGBA color.
     */
    public function hex_to_rgba($hex, $alpha = 1) {
        $hex = ltrim($hex, '#');
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba($r, $g, $b, $alpha)";
    }
}