<?php
/**
 * Partial template for button links
 *
 * @package Alynt_404_Sitemap
 */

if (!defined('WPINC')) {
    die;
}

$button_links = $settings['button_links'] ?? array();
?>

<div class="alynt-404-buttons" role="navigation" aria-label="<?php esc_attr_e('Quick links', 'alynt-404-sitemap'); ?>">
    <?php foreach ($button_links as $button): ?>
        <a href="<?php echo esc_url($button['url']); ?>" 
           class="alynt-404-button">
            <?php echo esc_html($button['text']); ?>
        </a>
    <?php endforeach; ?>
</div>