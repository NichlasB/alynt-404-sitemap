<?php
/**
 * Sanitize and validate plugin settings.
 *
 * @package Alynt_404_Sitemap
 * @since   1.0.3
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

class Alynt_404_Settings_Sanitizer {

    /**
     * Sanitize color settings.
     *
     * @since 1.0.3
     * @param array $input Raw values.
     * @return array Sanitized color settings.
     */
    public function sanitize_colors($input) {
        $input = is_array($input) ? $input : array();
        $sanitized = array();
        $color_manager = Alynt_404_Color_Manager::get_instance();
        $default_colors = Alynt_404_Settings_Defaults::get_color_defaults();

        foreach ($input as $key => $color) {
            if ($color_manager->validate_color($color)) {
                $sanitized[$key] = $color;
                continue;
            }

            if (array_key_exists($key, $default_colors)) {
                $sanitized[$key] = $default_colors[$key];
            }

            add_settings_error(
                'alynt_404_messages',
                'invalid_color',
                sprintf(__('Invalid color format for %s. Reset to default.', 'alynt-404-sitemap'), $key),
                'error'
            );
        }

        return wp_parse_args($sanitized, $default_colors);
    }

    /**
     * Sanitize 404 page settings.
     *
     * @since 1.0.3
     * @param array $input Raw values.
     * @return array Sanitized 404 settings.
     */
    public function sanitize_404_settings($input) {
        $input = is_array($input) ? $input : array();
        $defaults = Alynt_404_Settings_Defaults::get_404_defaults();
        $utilities = Alynt_404_Utilities::get_instance();
        $post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
        $merged = wp_parse_args($input, $defaults);

        $sanitized = array();
        $sanitized['heading'] = sanitize_text_field($merged['heading']);
        $sanitized['message'] = sanitize_text_field($merged['message']);
        $sanitized['meta_description'] = sanitize_text_field($merged['meta_description']);
        $sanitized['custom_css'] = $utilities->sanitize_css($merged['custom_css']);

        $sanitized['featured_image'] = absint($merged['featured_image']);
        if ($sanitized['featured_image'] && !wp_get_attachment_image($sanitized['featured_image'])) {
            $sanitized['featured_image'] = 0;
        }

        $sanitized['button_links'] = array();
        if (!empty($merged['button_links']) && is_array($merged['button_links'])) {
            foreach ($merged['button_links'] as $button) {
                if (empty($button['text']) || empty($button['url'])) {
                    continue;
                }
                $sanitized['button_links'][] = array(
                    'text' => sanitize_text_field($button['text']),
                    'url' => esc_url_raw($button['url']),
                );
            }
        }

        $sanitized['search_post_types'] = array();
        if (!empty($merged['search_post_types']) && is_array($merged['search_post_types'])) {
            foreach ($merged['search_post_types'] as $post_type) {
                if (array_key_exists($post_type, $post_types)) {
                    $sanitized['search_post_types'][] = $post_type;
                }
            }
        }

        if (empty($sanitized['search_post_types'])) {
            $sanitized['search_post_types'] = $defaults['search_post_types'];
        }

        return $sanitized;
    }

    /**
     * Sanitize sitemap settings.
     *
     * @since 1.0.3
     * @param array $input Raw values.
     * @return array Sanitized sitemap settings.
     */
    public function sanitize_sitemap_settings($input) {
        $input = is_array($input) ? $input : array();
        $defaults = Alynt_404_Settings_Defaults::get_sitemap_defaults();
        $utilities = Alynt_404_Utilities::get_instance();
        $merged = wp_parse_args($input, $defaults);

        $sanitized = array();
        $sanitized['heading'] = sanitize_text_field($merged['heading']);
        $sanitized['message'] = sanitize_text_field($merged['message']);
        $sanitized['meta_description'] = sanitize_text_field($merged['meta_description']);
        $sanitized['custom_css'] = $utilities->sanitize_css($merged['custom_css']);
        $sanitized['sort_order'] = isset($merged['sort_order']) && is_array($merged['sort_order']) ? $merged['sort_order'] : $defaults['sort_order'];
        $sanitized['url_slug'] = $this->sanitize_sitemap_slug($merged['url_slug'], $utilities);
        $sanitized['post_types'] = $this->sanitize_sitemap_post_types($merged['post_types'], $defaults['post_types']);
        $sanitized['excluded_ids'] = $this->sanitize_excluded_ids($merged['excluded_ids']);

        $sanitized['featured_image'] = absint($merged['featured_image']);
        if ($sanitized['featured_image'] && !wp_get_attachment_image($sanitized['featured_image'])) {
            $sanitized['featured_image'] = 0;
        }

        $sanitized['columns_desktop'] = min(4, max(1, absint($merged['columns_desktop'])));
        $sanitized['columns_tablet'] = min(4, max(1, absint($merged['columns_tablet'])));
        $sanitized['columns_mobile'] = min(2, max(1, absint($merged['columns_mobile'])));

        return $sanitized;
    }

    /**
     * Sanitize sitemap slug and ensure uniqueness.
     *
     * @since 1.0.3
     * @param string              $raw_slug  Raw input slug.
     * @param Alynt_404_Utilities $utilities Utility service.
     * @return string Sanitized unique sitemap slug.
     */
    private function sanitize_sitemap_slug($raw_slug, $utilities) {
        $slug = $utilities->sanitize_slug($raw_slug);
        if ($utilities->is_slug_available($slug)) {
            return $slug;
        }

        $unique = $utilities->generate_unique_slug($slug);
        add_settings_error(
            'alynt_404_messages',
            'slug_modified',
            sprintf(__('URL slug "%s" was already taken. Modified to "%s".', 'alynt-404-sitemap'), $slug, $unique),
            'warning'
        );
        return $unique;
    }

    /**
     * Keep only valid post types for sitemap.
     *
     * @since 1.0.3
     * @param mixed $post_types Raw post types.
     * @param array $fallback Fallback post types.
     * @return array Sanitized list of allowed sitemap post types.
     */
    private function sanitize_sitemap_post_types($post_types, $fallback) {
        $allowed_post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
        $sanitized = array();
        if (is_array($post_types)) {
            foreach ($post_types as $post_type) {
                if (array_key_exists($post_type, $allowed_post_types)) {
                    $sanitized[] = $post_type;
                }
            }
        }

        return !empty($sanitized) ? $sanitized : $fallback;
    }

    /**
     * Validate excluded post IDs.
     *
     * @since 1.0.3
     * @param string $excluded_ids Raw comma-delimited IDs.
     * @return string Validated comma-delimited post IDs.
     */
    private function sanitize_excluded_ids($excluded_ids) {
        $sanitized = array();
        if (!empty($excluded_ids)) {
            $ids = array_map('trim', explode(',', $excluded_ids));
            foreach ($ids as $id) {
                if (is_numeric($id) && get_post($id)) {
                    $sanitized[] = absint($id);
                }
            }
        }

        return implode(',', $sanitized);
    }
}

