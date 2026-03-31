<?php
/**
 * Fallback sitemap content template for theme integration.
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$responsive_classes = Alynt_404_Template_Loader::get_instance()->get_responsive_classes();
$has_visible_posts  = false;
?>
<main id="primary" class="alynt-sitemap <?php echo esc_attr( $responsive_classes ); ?>" role="main">
	<h1><?php echo esc_html( $settings['heading'] ?? __( 'Sitemap', 'alynt-404-sitemap' ) ); ?></h1>
	<p><?php echo esc_html( $settings['message'] ?? __( "Here's our website at a glance. Use this sitemap to quickly find what you're looking for.", 'alynt-404-sitemap' ) ); ?></p>
	<div class="alynt-sitemap-row">
		<?php foreach ( $post_types as $current_post_type ) : ?>
			<?php require ALYNT_404_PATH . 'templates/partials/archive-column.php'; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( ! $has_visible_posts ) : ?>
		<div class="alynt-sitemap-empty-state">
			<h2><?php esc_html_e( 'No sitemap content is available yet', 'alynt-404-sitemap' ); ?></h2>
			<p><?php esc_html_e( 'Published pages and posts will appear here once content is available for the selected sitemap types.', 'alynt-404-sitemap' ); ?></p>
		</div>
	<?php endif; ?>
</main>
