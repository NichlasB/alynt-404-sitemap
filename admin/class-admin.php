<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Alynt_404_Sitemap
 */

class Alynt_404_Admin {

    /**
     * The plugin name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Current active tab.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $active_tab    The current active settings tab.
     */
    private $active_tab;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if (!$this->is_plugin_page($screen)) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('dashicons');
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/admin-styles.css',
            array('wp-color-picker', 'jquery-ui', 'dashicons'),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if (!$this->is_plugin_page($screen)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/admin-scripts.js',
            array('jquery', 'wp-color-picker'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'alynt404Vars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(ALYNT_404_PREFIX . 'settings_nonce'),
                'messages' => array(
                    'saveSuccess' => __('Settings saved successfully.', 'alynt-404-sitemap'),
                    'saveError' => __('Error saving settings.', 'alynt-404-sitemap'),
                    'confirmReset' => __('Are you sure you want to reset these settings to defaults?', 'alynt-404-sitemap'),
                    'mediaTitle' => __('Choose Image', 'alynt-404-sitemap'),
                    'mediaButton' => __('Select', 'alynt-404-sitemap')
                )
            )
        );
    }

    /**
     * Add plugin admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('404 & Sitemap Settings', 'alynt-404-sitemap'),
            __('404 & Sitemap', 'alynt-404-sitemap'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-layout'
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // General Settings
        register_setting(
            ALYNT_404_PREFIX . 'general_settings',
            ALYNT_404_PREFIX . 'colors',
            array($this, 'sanitize_colors')
        );

        // 404 Settings
        register_setting(
            ALYNT_404_PREFIX . '404_settings',
            ALYNT_404_PREFIX . '404_settings',
            array($this, 'sanitize_404_settings')
        );

        // Sitemap Settings
        register_setting(
            ALYNT_404_PREFIX . 'sitemap_settings',
            ALYNT_404_PREFIX . 'sitemap_settings',
            array($this, 'sanitize_sitemap_settings')
        );
    }

    /**
     * Render the settings page.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle reset action
        if (isset($_POST['reset_settings'])) {
            check_admin_referer('reset_settings_action');
            $this->reset_settings($this->active_tab);
            add_settings_error(
                'alynt_404_messages',
                'settings_reset',
                __('Settings have been reset to defaults.', 'alynt-404-sitemap'),
                'updated'
            );
        }

        // Show reset message if transient exists
        if (get_transient('alynt_404_reset_message')) {
            delete_transient('alynt_404_reset_message');
            add_settings_error(
                'alynt_404_messages',
                'settings_reset',
                __('Settings have been reset to defaults.', 'alynt-404-sitemap'),
                'updated'
            );
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('alynt_404_messages'); ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=general" 
                   class="nav-tab <?php echo $this->active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'alynt-404-sitemap'); ?>
                </a>
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=404" 
                   class="nav-tab <?php echo $this->active_tab === '404' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('404 Page', 'alynt-404-sitemap'); ?>
                </a>
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=sitemap" 
                   class="nav-tab <?php echo $this->active_tab === 'sitemap' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Sitemap', 'alynt-404-sitemap'); ?>
                </a>
            </h2>

                <div class="settings-forms">
                <!-- Settings Form -->
                <form method="post" action="options.php" class="main-form">
                    <?php
                    settings_fields(ALYNT_404_PREFIX . $this->active_tab . '_settings');
                    
                    switch ($this->active_tab) {
                        case 'general':
                            require_once plugin_dir_path(__FILE__) . 'partials/tab-general.php';
                            break;
                        case '404':
                            require_once plugin_dir_path(__FILE__) . 'partials/tab-404.php';
                            break;
                        case 'sitemap':
                            require_once plugin_dir_path(__FILE__) . 'partials/tab-sitemap.php';
                            break;
                    }
                    ?>
                    <p class="submit">
                        <?php submit_button(null, 'primary', 'submit', false); ?>
                    </p>
                </form>

                <!-- Reset Form -->
                <form method="post" class="reset-form">
                    <?php wp_nonce_field('reset_settings_action'); ?>
                    <button type="submit" name="reset_settings" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to reset these settings to defaults?', 'alynt-404-sitemap')); ?>');">
                        <?php _e('Reset to Defaults', 'alynt-404-sitemap'); ?>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Check if current page is plugin settings page.
     *
     * @since    1.0.0
     * @param    WP_Screen    $screen    Current screen object.
     * @return   boolean
     */
    private function is_plugin_page($screen) {
        return strpos($screen->id, $this->plugin_name) !== false;
    }

    /**
     * Sanitize color settings.
     *
     * @since    1.0.0
     * @param    array    $input    Array of color settings.
     * @return   array    Sanitized settings.
     */
    public function sanitize_colors($input) {
        $sanitized = array();
        $color_manager = Alynt_404_Color_Manager::get_instance();

        foreach ($input as $key => $color) {
            if ($color_manager->validate_color($color)) {
                $sanitized[$key] = $color;
            } else {
                $default_colors = $color_manager->get_default_colors();
                $sanitized[$key] = $default_colors[$key];
                add_settings_error(
                    'alynt_404_messages',
                    'invalid_color',
                    sprintf(__('Invalid color format for %s. Reset to default.', 'alynt-404-sitemap'), $key),
                    'error'
                );
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize 404 page settings.
     *
     * @since    1.0.0
     * @param    array    $input    Array of 404 settings.
     * @return   array    Sanitized settings.
     */
    public function sanitize_404_settings($input) {
        $sanitized = array();
        
        // Heading
        $sanitized['heading'] = sanitize_text_field($input['heading']);
        
        // Message
        $sanitized['message'] = sanitize_text_field($input['message']);
        
        // Button Links
        $sanitized['button_links'] = array();
        if (!empty($input['button_links']) && is_array($input['button_links'])) {
            foreach ($input['button_links'] as $button) {
                if (!empty($button['text']) && !empty($button['url'])) {
                    $sanitized['button_links'][] = array(
                        'text' => sanitize_text_field($button['text']),
                        'url' => esc_url_raw($button['url'])
                    );
                }
            }
        }
        
        // Search Post Types
        $sanitized['search_post_types'] = array();
        if (!empty($input['search_post_types']) && is_array($input['search_post_types'])) {
            $post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
            foreach ($input['search_post_types'] as $post_type) {
                if (array_key_exists($post_type, $post_types)) {
                    $sanitized['search_post_types'][] = $post_type;
                }
            }
        }
        
        // Meta Description
        $sanitized['meta_description'] = sanitize_text_field($input['meta_description']);
        
        // Custom CSS
        $sanitized['custom_css'] = Alynt_404_Utilities::get_instance()->sanitize_css($input['custom_css']);
        
        // Featured Image
        $sanitized['featured_image'] = absint($input['featured_image']);
        if ($sanitized['featured_image'] && !wp_get_attachment_image($sanitized['featured_image'])) {
            $sanitized['featured_image'] = 0;
        }

        return $sanitized;
    }

    /**
     * Sanitize sitemap settings.
     *
     * @since    1.0.0
     * @param    array    $input    Array of sitemap settings.
     * @return   array    Sanitized settings.
     */
    public function sanitize_sitemap_settings($input) {
        $sanitized = array();
        $utilities = Alynt_404_Utilities::get_instance();
        
        // Heading
        $sanitized['heading'] = sanitize_text_field($input['heading']);
        
        // Message
        $sanitized['message'] = sanitize_text_field($input['message']);
        
        // URL Slug
        $slug = $utilities->sanitize_slug($input['url_slug']);
        if ($utilities->is_slug_available($slug)) {
            $sanitized['url_slug'] = $slug;
        } else {
            $sanitized['url_slug'] = $utilities->generate_unique_slug($slug);
            add_settings_error(
                'alynt_404_messages',
                'slug_modified',
                sprintf(__('URL slug "%s" was already taken. Modified to "%s".', 'alynt-404-sitemap'), 
                    $slug, 
                    $sanitized['url_slug']
                ),
                'warning'
            );
        }
        
        // Post Types
        $sanitized['post_types'] = array();
        if (!empty($input['post_types']) && is_array($input['post_types'])) {
            $post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
            foreach ($input['post_types'] as $post_type) {
                if (array_key_exists($post_type, $post_types)) {
                    $sanitized['post_types'][] = $post_type;
                }
            }
        }
        
        // Excluded IDs
        $excluded_ids = array();
        if (!empty($input['excluded_ids'])) {
            $ids = array_map('trim', explode(',', $input['excluded_ids']));
            foreach ($ids as $id) {
                if (is_numeric($id) && get_post($id)) {
                    $excluded_ids[] = absint($id);
                }
            }
        }
        $sanitized['excluded_ids'] = implode(',', $excluded_ids);
        
        // Meta Description
        $sanitized['meta_description'] = sanitize_text_field($input['meta_description']);
        
        // Custom CSS
        $sanitized['custom_css'] = $utilities->sanitize_css($input['custom_css']);
        
        // Featured Image
        $sanitized['featured_image'] = absint($input['featured_image']);
        if ($sanitized['featured_image'] && !wp_get_attachment_image($sanitized['featured_image'])) {
            $sanitized['featured_image'] = 0;
        }

        // Column Settings
        $sanitized['columns_desktop'] = min(4, max(1, absint($input['columns_desktop'])));
        $sanitized['columns_tablet'] = min(4, max(1, absint($input['columns_tablet'])));
        $sanitized['columns_mobile'] = min(2, max(1, absint($input['columns_mobile'])));

        return $sanitized;
    }

    /**
     * Reset settings to defaults.
     *
     * @since    1.0.0
     * @param    string    $tab    Settings tab to reset.
     */
    private function reset_settings($tab) {
        switch ($tab) {
            case 'general':
                $defaults = Alynt_404_Color_Manager::get_instance()->get_default_colors();
                delete_option(ALYNT_404_PREFIX . 'colors');
                add_option(ALYNT_404_PREFIX . 'colors', $defaults);
                Alynt_404_Color_Manager::get_instance()->regenerate_css();
                break;

            case '404':
                $defaults = array(
                    'heading' => "Oops! That page can't be found.",
                    'message' => "Looks like this page took a wrong turn. Let's get you back to where you need to be.",
                    'button_links' => array(),
                    'search_post_types' => array('post', 'page'),
                    'meta_description' => 'Page not found. Use our search or navigation to find what you are looking for.',
                    'custom_css' => '',
                    'featured_image' => 0
                );
                delete_option(ALYNT_404_PREFIX . '404_settings');
                add_option(ALYNT_404_PREFIX . '404_settings', $defaults);
                break;

            case 'sitemap':
                $defaults = array(
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
                    'columns_mobile' => 1
                );
                delete_option(ALYNT_404_PREFIX . 'sitemap_settings');
                add_option(ALYNT_404_PREFIX . 'sitemap_settings', $defaults);
                break;
        }
    }
}