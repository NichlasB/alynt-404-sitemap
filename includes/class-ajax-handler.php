<?php
/**
 * Handle all AJAX operations.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Ajax_Handler {

    /**
     * Duration for rate limiting in seconds
     *
     * @var int
     */
    private $rate_limit_duration;

    /**
     * Maximum number of requests allowed per duration
     *
     * @var int
     */
    private $rate_limit_requests;

    /**
     * Initialize the class.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Rate limiting properties
        $this->rate_limit_duration = 10; // seconds
        $this->rate_limit_requests = 5;  // max requests per duration

        // Register AJAX actions
        add_action('wp_ajax_alynt_404_search', [$this, 'handle_search']);
        add_action('wp_ajax_nopriv_alynt_404_search', [$this, 'handle_search']);
    }

    /**
     * Handle AJAX search requests.
     *
     * @since 1.0.0
     */
    public function handle_search() {
        // Verify nonce
        if (!check_ajax_referer(ALYNT_404_PREFIX . 'search_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid security token.']);
            return;
        }

        // Sanitize and validate search input
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        if (empty($search_term)) {
            wp_send_json_error(['message' => 'Search term is required.']);
            return;
        }

        // Get enabled post types for search
        $settings = get_option(ALYNT_404_PREFIX . '404_settings');
        $post_types = !empty($settings['search_post_types']) ? $settings['search_post_types'] : ['post', 'page'];

        // Perform search
        $results = $this->perform_search($search_term, $post_types);

        wp_send_json_success([
            'results' => $results,
            'count' => count($results)
        ]);
    }

    /**
     * Handle settings save requests.
     *
     * @since 1.0.0
     */
    public function save_settings() {
        // Verify nonce and capabilities
        if (!check_ajax_referer(ALYNT_404_PREFIX . 'settings_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid security token.']);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        // Validate and sanitize settings
        $settings_type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
        $settings = isset($_POST['settings']) ? $this->sanitize_settings($_POST['settings']) : [];

        if (empty($settings_type) || empty($settings)) {
            wp_send_json_error(['message' => 'Invalid settings data.']);
        }

        // Update settings based on type
        switch ($settings_type) {
            case 'colors':
            $this->save_color_settings($settings);
            break;
            case '404':
            $this->save_404_settings($settings);
            break;
            case 'sitemap':
            $this->save_sitemap_settings($settings);
            break;
            default:
            wp_send_json_error(['message' => 'Invalid settings type.']);
        }

        wp_send_json_success(['message' => 'Settings saved successfully.']);
    }

    /**
     * Perform search query.
     *
     * @since 1.0.0
     * @param string $search_term The search term.
     * @param array  $post_types Array of post types to search.
     * @return array Search results.
     */
    private function perform_search($search_term, $post_types) {
        $args = array(
            'post_type' => $post_types,
            'post_status' => 'publish',
            's' => $search_term,
            'orderby' => 'relevance',
            'posts_per_page' => -1
        );

        $query = new WP_Query($args);
        $results = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = [
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'type' => get_post_type_object(get_post_type())->labels->singular_name
                ];
            }
        }

        wp_reset_postdata();
        return $results;
    }

    /**
     * Check rate limiting.
     *
     * @since 1.0.0
     * @return boolean True if request is allowed, false if rate limited.
     */
    private function check_rate_limit() {
        $ip = $this->get_client_ip();
        $transient_key = ALYNT_404_PREFIX . 'rate_limit_' . md5($ip);
        $requests = get_transient($transient_key);

        if (false === $requests) {
            set_transient($transient_key, 1, $this->rate_limit_duration);
            return true;
        }

        if ($requests >= $this->rate_limit_requests) {
            return false;
        }

        set_transient($transient_key, $requests + 1, $this->rate_limit_duration);
        return true;
    }

    /**
     * Get client IP address.
     *
     * @since 1.0.0
     * @return string IP address.
     */
    private function get_client_ip() {
        $ip = '';
        
        // Check for CloudFlare IP
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // Check for proxy IP
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
        }
        // Get remote address
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    /**
     * Sanitize settings array.
     *
     * @since 1.0.0
     * @param array $settings Raw settings array.
     * @return array Sanitized settings array.
     */
    private function sanitize_settings($settings) {
        if (!is_array($settings)) {
            return [];
        }

        $sanitized = [];
        foreach ($settings as $key => $value) {
            $key = sanitize_key($key);
            
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_settings($value);
            } else {
                switch ($key) {
                    case 'custom_css':
                    $sanitized[$key] = wp_strip_all_tags($value);
                    break;
                    case 'meta_description':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
                    case 'excluded_ids':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
                    default:
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }

        return $sanitized;
    }

    /**
     * Save color settings.
     *
     * @since 1.0.0
     * @param array $settings Color settings array.
     */
    private function save_color_settings($settings) {
        // Validate hex colors
        foreach ($settings as $key => $color) {
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color)) {
                wp_send_json_error(['message' => 'Invalid color format for ' . $key]);
            }
        }

        update_option(ALYNT_404_PREFIX . 'colors', $settings);
    }

    /**
     * Save 404 page settings.
     *
     * @since 1.0.0
     * @param array $settings 404 page settings array.
     */
    private function save_404_settings($settings) {
        // Validate post types
        if (!empty($settings['search_post_types'])) {
            $settings['search_post_types'] = array_intersect(
                $settings['search_post_types'],
                array_keys(get_post_types(['public' => true]))
            );
        }

        update_option(ALYNT_404_PREFIX . '404_settings', $settings);
    }

    /**
     * Save sitemap settings.
     *
     * @since 1.0.0
     * @param array $settings Sitemap settings array.
     */
    private function save_sitemap_settings($settings) {
        // Validate post types
        if (!empty($settings['post_types'])) {
            $settings['post_types'] = array_intersect(
                $settings['post_types'],
                array_keys(get_post_types(['public' => true]))
            );
        }

        // Validate excluded IDs
        if (!empty($settings['excluded_ids'])) {
            $ids = array_map('trim', explode(',', $settings['excluded_ids']));
            $valid_ids = [];
            foreach ($ids as $id) {
                if (get_post($id)) {
                    $valid_ids[] = $id;
                }
            }
            $settings['excluded_ids'] = implode(',', $valid_ids);
        }

        update_option(ALYNT_404_PREFIX . 'sitemap_settings', $settings);
    }
}