<?php
/**
 * Fallback sitemap content template for theme integration.
 *
 * @package Alynt_404_Sitemap
 */


// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$responsive_classes = Alynt_404_Template_Loader::get_instance()->get_responsive_classes();
?>
<main id="primary" class="alynt-sitemap <?php echo esc_attr($responsive_classes); ?>" role="main">
    <h1><?php echo esc_html($settings['heading'] ?? 'Sitemap'); ?></h1>
    <p><?php echo esc_html($settings['message'] ?? "Here's our website at a glance. Use this sitemap to quickly find what you're looking for."); ?></p>
    <div class="alynt-sitemap-row">
        <?php foreach ($post_types as $post_type) : ?>
            <?php require ALYNT_404_PATH . 'templates/partials/archive-column.php'; ?>
        <?php endforeach; ?>
    </div>
</main>

