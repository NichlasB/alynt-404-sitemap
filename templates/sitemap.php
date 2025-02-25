<?php
/**
 * The template for displaying the sitemap
 *
 * @package Alynt_404_Sitemap
 */

if (!defined('WPINC')) {
    die;
}

get_header();

$settings = get_option(ALYNT_404_PREFIX . 'sitemap_settings', array());
$post_types = !empty($settings['post_types']) ? $settings['post_types'] : array('post', 'page');

// Get responsive classes
$responsive_classes = Alynt_404_Public::get_instance()->get_responsive_classes();
?>

<main id="primary" class="alynt-sitemap <?php echo esc_attr($responsive_classes); ?>" role="main">
    <h1>
        <?php echo esc_html($settings['heading'] ?? 'Sitemap'); ?>
    </h1>

    <p>
        <?php echo esc_html($settings['message'] ?? "Here's our website at a glance. Use this sitemap to quickly find what you're looking for."); ?>
    </p>

    <div class="alynt-sitemap-row">
        <?php
        foreach ($post_types as $post_type) {
            require ALYNT_404_PATH . 'templates/partials/archive-column.php';
        }
        ?>
    </div>
</main>

<?php
get_footer();