<?php
/**
 * 404 page settings tab partial.
 *
 * @package Alynt_404_Sitemap
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$settings = get_option(ALYNT_404_PREFIX . '404_settings', array());
$post_types = Alynt_404_Post_Types::get_instance()->get_public_post_types();
?>

<div class="alynt-404-settings-section">
    <h2><?php _e('404 Page Content', 'alynt-404-sitemap'); ?></h2>

    <!-- Featured Image -->
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="featured_image">
                    <?php _e('Featured Image', 'alynt-404-sitemap'); ?>
                </label>
            </th>
            <td>
                <div class="alynt-404-media-upload">
                    <input type="hidden" 
                    name="<?php echo ALYNT_404_PREFIX; ?>404_settings[featured_image]" 
                    id="featured_image" 
                    value="<?php echo esc_attr($settings['featured_image'] ?? ''); ?>" />
                    
                    <div class="image-preview">
                        <?php if (!empty($settings['featured_image'])): ?>
                            <?php echo wp_get_attachment_image($settings['featured_image'], 'medium'); ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" 
                    class="button upload-image-button" 
                    data-uploader-title="<?php esc_attr_e('Choose Featured Image', 'alynt-404-sitemap'); ?>"
                    data-uploader-button-text="<?php esc_attr_e('Select Image', 'alynt-404-sitemap'); ?>">
                    <?php _e('Upload Image', 'alynt-404-sitemap'); ?>
                </button>

                <button type="button" class="button remove-image-button <?php echo empty($settings['featured_image']) ? 'hidden' : ''; ?>">
                    <?php _e('Remove Image', 'alynt-404-sitemap'); ?>
                </button>
            </div>
        </td>
    </tr>

    <!-- Heading -->
    <tr>
        <th scope="row">
            <label for="404_heading">
                <?php _e('Page Heading', 'alynt-404-sitemap'); ?>
            </label>
        </th>
        <td>
            <input type="text" 
            id="404_heading" 
            name="<?php echo ALYNT_404_PREFIX; ?>404_settings[heading]" 
            value="<?php echo esc_attr($settings['heading'] ?? "Oops! That page can't be found."); ?>" 
            class="large-text"
            required />
            <p class="description">
                <?php _e('Main heading displayed on the 404 page.', 'alynt-404-sitemap'); ?>
            </p>
        </td>
    </tr>

    <!-- Error Message -->
    <tr>
        <th scope="row">
            <label for="404_message">
                <?php _e('Error Message', 'alynt-404-sitemap'); ?>
            </label>
        </th>
        <td>
            <textarea id="404_message" 
            name="<?php echo ALYNT_404_PREFIX; ?>404_settings[message]" 
            class="large-text" 
            rows="3" 
            required><?php echo esc_textarea($settings['message'] ?? "Looks like this page took a wrong turn. Let's get you back to where you need to be."); ?></textarea>
            <p class="description">
                <?php _e('Message displayed below the heading.', 'alynt-404-sitemap'); ?>
            </p>
        </td>
    </tr>

    <!-- Search Post Types -->
    <tr>
        <th scope="row">
            <?php _e('Search Content Types', 'alynt-404-sitemap'); ?>
        </th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <?php _e('Select content types to include in search results', 'alynt-404-sitemap'); ?>
                </legend>
                <?php foreach ($post_types as $post_type => $object): ?>
                    <label>
                        <input type="checkbox" 
                        name="<?php echo ALYNT_404_PREFIX; ?>404_settings[search_post_types][]" 
                        value="<?php echo esc_attr($post_type); ?>"
                        <?php checked(in_array($post_type, $settings['search_post_types'] ?? array('post', 'page'))); ?> />
                        <?php echo esc_html($object->labels->name); ?>
                    </label><br>
                <?php endforeach; ?>
                <p class="description">
                    <?php _e('Select which content types should appear in the AJAX search results.', 'alynt-404-sitemap'); ?>
                </p>
            </fieldset>
        </td>
    </tr>
</table>
</div>

<!-- Button Links Section -->
<div class="alynt-404-settings-section">
    <h2><?php _e('Quick Links', 'alynt-404-sitemap'); ?></h2>
    <div class="alynt-404-button-links" data-max-buttons="4">
        <div class="button-links-container">
            <?php 
            $button_links = $settings['button_links'] ?? array();
            if (!empty($button_links)):
                foreach ($button_links as $index => $button):
                    ?>
                    <div class="button-link-item" data-index="<?php echo esc_attr($index); ?>">
                        <div class="button-link-fields">
                            <input type="text" 
                                   name="<?php echo ALYNT_404_PREFIX; ?>404_settings[button_links][<?php echo $index; ?>][text]" 
                                   value="<?php echo esc_attr($button['text']); ?>"
                                   placeholder="<?php esc_attr_e('Button Text', 'alynt-404-sitemap'); ?>" 
                                   class="button-link-text regular-text" />
                            <input type="text" 
                                   name="<?php echo ALYNT_404_PREFIX; ?>404_settings[button_links][<?php echo $index; ?>][url]" 
                                   value="<?php echo esc_url($button['url']); ?>"
                                   placeholder="<?php esc_attr_e('Button URL', 'alynt-404-sitemap'); ?>" 
                                   class="button-link-url regular-text" />
                        </div>
                        <div class="button-link-actions">
                            <button type="button" class="button-link-move-up" title="<?php esc_attr_e('Move up', 'alynt-404-sitemap'); ?>">
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                            </button>
                            <button type="button" class="button-link-move-down" title="<?php esc_attr_e('Move down', 'alynt-404-sitemap'); ?>">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <button type="button" class="button-link-remove" title="<?php esc_attr_e('Remove', 'alynt-404-sitemap'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <?php 
                endforeach;
            endif;
            ?>
        </div>
        <div class="button-link-add">
            <button type="button" class="button add-button-link">
                <?php _e('Add Quick Link', 'alynt-404-sitemap'); ?>
            </button>
            <p class="description">
                <?php _e('Add quick navigation links that will appear as buttons on the 404 page. Maximum 4 buttons per row.', 'alynt-404-sitemap'); ?>
            </p>
        </div>
    </div>
</div>

<!-- SEO Section -->
<div class="alynt-404-settings-section">
    <h2><?php _e('SEO Settings', 'alynt-404-sitemap'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="404_meta_description">
                    <?php _e('Meta Description', 'alynt-404-sitemap'); ?>
                </label>
            </th>
            <td>
                <textarea id="404_meta_description" 
                name="<?php echo ALYNT_404_PREFIX; ?>404_settings[meta_description]" 
                class="large-text" 
                rows="3" 
                maxlength="160"><?php echo esc_textarea($settings['meta_description'] ?? ''); ?></textarea>
                <p class="description meta-description-counter">
                    <?php _e('Character count: ', 'alynt-404-sitemap'); ?>
                    <span class="counter">0</span>. 
                    <?php _e('Recommended length: 50-160 characters.', 'alynt-404-sitemap'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-- Custom CSS Section -->
<div class="alynt-404-settings-section">
    <h2><?php _e('Custom CSS', 'alynt-404-sitemap'); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="404_custom_css">
                    <?php _e('Custom Styles', 'alynt-404-sitemap'); ?>
                </label>
            </th>
            <td>
                <textarea id="404_custom_css" 
                name="<?php echo ALYNT_404_PREFIX; ?>404_settings[custom_css]" 
                class="large-text code" 
                rows="10"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                <p class="description">
                    <?php _e('Add custom CSS styles for the 404 page. These styles will only apply to the 404 page.', 'alynt-404-sitemap'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-- Button Link Template (Hidden) -->
<script type="text/template" id="button-link-template">
    <div class="button-link-item" data-index="{{index}}">
        <div class="button-link-fields">
            <input type="text" 
                   name="<?php echo ALYNT_404_PREFIX; ?>404_settings[button_links][{{index}}][text]" 
                   placeholder="<?php esc_attr_e('Button Text', 'alynt-404-sitemap'); ?>" 
                   class="button-link-text regular-text" />
            <input type="text" 
                   name="<?php echo ALYNT_404_PREFIX; ?>404_settings[button_links][{{index}}][url]" 
                   placeholder="<?php esc_attr_e('Button URL', 'alynt-404-sitemap'); ?>" 
                   class="button-link-url regular-text" />
        </div>
        <div class="button-link-actions">
            <button type="button" class="button-link-move-up" title="<?php esc_attr_e('Move up', 'alynt-404-sitemap'); ?>">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </button>
            <button type="button" class="button-link-move-down" title="<?php esc_attr_e('Move down', 'alynt-404-sitemap'); ?>">
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>
            <button type="button" class="button-link-remove" title="<?php esc_attr_e('Remove', 'alynt-404-sitemap'); ?>">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
    </div>
</script>