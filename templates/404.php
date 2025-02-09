<?php
/**
 * The template for displaying 404 pages
 *
 * @package Alynt_404_Sitemap
 */

if (!defined('WPINC')) {
    die;
}

get_header();

$settings = get_option(ALYNT_404_PREFIX . '404_settings', array());
?>

<main id="primary" class="alynt-404-page" role="main">
    <?php if (!empty($settings['featured_image'])): ?>
        <div class="alynt-404-image" aria-hidden="true">
            <?php 
            echo wp_get_attachment_image(
                $settings['featured_image'],
                'full',
                false,
                array('alt' => '')
            ); 
            ?>
        </div>
    <?php endif; ?>

    <h1>
        <?php echo esc_html($settings['heading'] ?? "Oops! That page can't be found."); ?>
    </h1>

    <p>
        <?php echo esc_html($settings['message'] ?? "Looks like this page took a wrong turn. Let's get you back to where you need to be."); ?>
    </p>

    <?php 
    // Load search form partial
    require ALYNT_404_PATH . 'templates/partials/search-results.php';
    
    // Load button links partial if buttons exist
    if (!empty($settings['button_links'])) {
        require ALYNT_404_PATH . 'templates/partials/button-links.php';
    }
    ?>

    <div class="alynt-404-home-link">
        <a href="<?php echo esc_url(home_url('/')); ?>" 
           aria-label="<?php esc_attr_e('Return to homepage', 'alynt-404-sitemap'); ?>">
            <?php esc_html_e('Return to Homepage', 'alynt-404-sitemap'); ?>
        </a>
    </div>
</main>

<?php
get_footer();