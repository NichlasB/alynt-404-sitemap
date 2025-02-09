<?php
/**
 * Handle post type operations.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Post_Types {

    /**
     * Store instance of the class.
     *
     * @since 1.0.0
     * @access private
     * @var object $instance The instance of the class.
     */
    private static $instance = null;

    /**
     * Cache duration in seconds (1 hour).
     *
     * @since 1.0.0
     * @access private
     * @var int
     */
    private $cache_duration = 3600;

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
     * Get all public post types.
     *
     * @since 1.0.0
     * @return array Array of post type objects.
     */
    public function get_public_post_types() {
        // Try to get from cache first
        $cached = get_transient(ALYNT_404_PREFIX . 'public_post_types');
        if (false !== $cached) {
            return $cached;
        }

        $args = array(
            'public'   => true,
            'show_ui'  => true,
            '_builtin' => false
        );

        // Get custom post types
        $custom_types = get_post_types($args, 'objects');

        // Add built-in post types we want to include
        $post_types = array(
            'post' => get_post_type_object('post'),
            'page' => get_post_type_object('page')
        );

        // Merge with custom post types
        $post_types = array_merge($post_types, $custom_types);

        // Remove any post types we don't want to include
        unset($post_types['attachment']);
        unset($post_types['revision']);
        unset($post_types['nav_menu_item']);
        unset($post_types['custom_css']);
        unset($post_types['customize_changeset']);
        unset($post_types['oembed_cache']);
        unset($post_types['user_request']);
        unset($post_types['wp_block']);

        // Allow filtering of post types
        $post_types = apply_filters(ALYNT_404_PREFIX . 'post_types', $post_types);

        // Cache the results
        set_transient(ALYNT_404_PREFIX . 'public_post_types', $post_types, $this->cache_duration);

        return $post_types;
    }

    /**
     * Get posts for a specific post type.
     *
     * @since 1.0.0
     * @param string $post_type Post type name.
     * @param array  $args Additional arguments.
     * @return array Array of posts.
     */
    public function get_posts_by_type($post_type, $args = array()) {
        $default_args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true
        );

        $args = wp_parse_args($args, $default_args);

        // Get excluded IDs from settings
        $settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings');
        if (!empty($settings['excluded_ids'])) {
            $excluded_ids = array_map('trim', explode(',', $settings['excluded_ids']));
            $args['post__not_in'] = array_merge(
                $args['post__not_in'] ?? array(),
                array_map('intval', $excluded_ids)
            );
        }

        return get_posts($args);
    }

    /**
     * Get post type label.
     *
     * @since 1.0.0
     * @param string $post_type Post type name.
     * @return string Post type label.
     */
    public function get_post_type_label($post_type) {
        $post_type_obj = get_post_type_object($post_type);
        return $post_type_obj ? $post_type_obj->labels->name : ucfirst($post_type);
    }

    /**
     * Check if post type exists and is public.
     *
     * @since 1.0.0
     * @param string $post_type Post type name.
     * @return boolean True if valid public post type.
     */
    public function is_valid_public_post_type($post_type) {
        $post_types = $this->get_public_post_types();
        return isset($post_types[$post_type]);
    }

    /**
     * Get post count by post type.
     *
     * @since 1.0.0
     * @param string $post_type Post type name.
     * @return int Post count.
     */
    public function get_post_type_count($post_type) {
        $counts = wp_count_posts($post_type);
        return $counts->publish ?? 0;
    }

    /**
     * Sort posts by specified criteria.
     *
     * @since 1.0.0
     * @param array  $posts Array of post objects.
     * @param string $orderby Sort criteria (title, date, menu_order).
     * @param string $order Sort order (ASC, DESC).
     * @return array Sorted posts array.
     */
    public function sort_posts($posts, $orderby = 'title', $order = 'ASC') {
        if (empty($posts)) {
            return $posts;
        }

        usort($posts, function($a, $b) use ($orderby, $order) {
            switch ($orderby) {
                case 'date':
                    $comparison = strtotime($a->post_date) - strtotime($b->post_date);
                    break;
                case 'menu_order':
                    $comparison = $a->menu_order - $b->menu_order;
                    break;
                case 'title':
                default:
                    $comparison = strcasecmp($a->post_title, $b->post_title);
                    break;
            }

            return ($order === 'DESC') ? -$comparison : $comparison;
        });

        return $posts;
    }

    /**
     * Clear post type cache.
     *
     * @since 1.0.0
     */
    public function clear_cache() {
        delete_transient(ALYNT_404_PREFIX . 'public_post_types');
    }

    /**
     * Get hierarchical post list.
     *
     * @since 1.0.0
     * @param string $post_type Post type name.
     * @return array Hierarchical post list.
     */
    public function get_hierarchical_posts($post_type) {
        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'post_status'    => 'publish'
        );

        $posts = get_posts($args);
        
        if (!is_post_type_hierarchical($post_type)) {
            return $posts;
        }

        return $this->build_hierarchy($posts);
    }

    /**
     * Build post hierarchy.
     *
     * @since 1.0.0
     * @param array $posts Array of posts.
     * @param int   $parent Parent ID.
     * @return array Hierarchical array.
     */
    private function build_hierarchy($posts, $parent = 0) {
        $hierarchy = array();

        foreach ($posts as $post) {
            if ($post->post_parent == $parent) {
                $children = $this->build_hierarchy($posts, $post->ID);
                if ($children) {
                    $post->children = $children;
                }
                $hierarchy[] = $post;
            }
        }

        return $hierarchy;
    }
}