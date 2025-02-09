<?php
/**
 * General settings tab partial.
 *
 * @package Alynt_404_Sitemap
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$colors = get_option(ALYNT_404_PREFIX . 'colors', array());
$default_colors = Alynt_404_Color_Manager::get_instance()->get_default_colors();
?>

<div class="alynt-404-settings-section">
    <h2><?php _e('Color Settings', 'alynt-404-sitemap'); ?></h2>
    <p class="description">
        <?php _e('Customize colors for both 404 and sitemap pages. These colors will override theme defaults.', 'alynt-404-sitemap'); ?>
    </p>

    <table class="form-table" role="presentation">
        <tbody>
            <!-- Headings Color -->
            <tr>
                <th scope="row">
                    <label for="headings_color">
                        <?php _e('Headings Color', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="headings_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[headings]" 
                           value="<?php echo esc_attr($colors['headings'] ?? $default_colors['headings']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['headings']); ?>"
                           aria-describedby="headings_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="headings_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="headings_color_desc">
                        <?php _e('Color for all heading elements (H1, H2, etc.).', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Paragraph Color -->
            <tr>
                <th scope="row">
                    <label for="paragraph_color">
                        <?php _e('Paragraph Color', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="paragraph_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[paragraph]" 
                           value="<?php echo esc_attr($colors['paragraph'] ?? $default_colors['paragraph']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['paragraph']); ?>"
                           aria-describedby="paragraph_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="paragraph_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="paragraph_color_desc">
                        <?php _e('Color for paragraph text.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Links Color -->
            <tr>
                <th scope="row">
                    <label for="links_color">
                        <?php _e('Links Color', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="links_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[links]" 
                           value="<?php echo esc_attr($colors['links'] ?? $default_colors['links']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['links']); ?>"
                           aria-describedby="links_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="links_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="links_color_desc">
                        <?php _e('Color for all link elements.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Buttons Background Color -->
            <tr>
                <th scope="row">
                    <label for="buttons_color">
                        <?php _e('Button Background', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="buttons_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[buttons]" 
                           value="<?php echo esc_attr($colors['buttons'] ?? $default_colors['buttons']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['buttons']); ?>"
                           aria-describedby="buttons_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="buttons_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="buttons_color_desc">
                        <?php _e('Background color for buttons.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Button Text Color -->
            <tr>
                <th scope="row">
                    <label for="button_text_color">
                        <?php _e('Button Text', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="button_text_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[button_text]" 
                           value="<?php echo esc_attr($colors['button_text'] ?? $default_colors['button_text']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['button_text']); ?>"
                           aria-describedby="button_text_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="button_text_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="button_text_color_desc">
                        <?php _e('Text color for buttons.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Search Border Color -->
            <tr>
                <th scope="row">
                    <label for="search_border_color">
                        <?php _e('Search Border', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="search_border_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[search_border]" 
                           value="<?php echo esc_attr($colors['search_border'] ?? $default_colors['search_border']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['search_border']); ?>"
                           aria-describedby="search_border_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="search_border_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="search_border_color_desc">
                        <?php _e('Border color for search input.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Search Text Color -->
            <tr>
                <th scope="row">
                    <label for="search_text_color">
                        <?php _e('Search Text', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="search_text_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[search_text]" 
                           value="<?php echo esc_attr($colors['search_text'] ?? $default_colors['search_text']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['search_text']); ?>"
                           aria-describedby="search_text_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="search_text_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="search_text_color_desc">
                        <?php _e('Text color for search input.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>

            <!-- Search Background Color -->
            <tr>
                <th scope="row">
                    <label for="search_background_color">
                        <?php _e('Search Background', 'alynt-404-sitemap'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="search_background_color" 
                           name="<?php echo ALYNT_404_PREFIX; ?>colors[search_background]" 
                           value="<?php echo esc_attr($colors['search_background'] ?? $default_colors['search_background']); ?>" 
                           class="alynt-404-color-picker" 
                           data-default-color="<?php echo esc_attr($default_colors['search_background']); ?>"
                           aria-describedby="search_background_color_desc" />
                    <button type="button" 
                            class="button button-secondary alynt-404-clear-color" 
                            data-target="search_background_color">
                        <?php _e('Clear Color', 'alynt-404-sitemap'); ?>
                    </button>
                    <p class="description" id="search_background_color_desc">
                        <?php _e('Background color for search input.', 'alynt-404-sitemap'); ?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="alynt-404-settings-section">
    <h2><?php _e('Color Preview', 'alynt-404-sitemap'); ?></h2>
    <div class="alynt-404-color-preview">
        <!-- Live preview of color selections will be rendered here via JavaScript -->
    </div>
</div>