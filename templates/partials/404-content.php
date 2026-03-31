<?php
/**
 * Fallback 404 content template for theme integration.
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

?>
<main id="primary" class="alynt-404-page" role="main">
	<h1><?php echo esc_html( $settings['heading'] ?? __( "Oops! That page can't be found.", 'alynt-404-sitemap' ) ); ?></h1>
	<p><?php echo esc_html( $settings['message'] ?? __( "Looks like this page took a wrong turn. Let's get you back to where you need to be.", 'alynt-404-sitemap' ) ); ?></p>
	<?php require ALYNT_404_PATH . 'templates/partials/search-results.php'; ?>
	<?php if ( ! empty( $settings['button_links'] ) ) : ?>
		<?php require ALYNT_404_PATH . 'templates/partials/button-links.php'; ?>
	<?php endif; ?>
	<div class="alynt-404-home-link">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Return to homepage', 'alynt-404-sitemap' ); ?>">
			<?php esc_html_e( 'Return to Homepage', 'alynt-404-sitemap' ); ?>
		</a>
	</div>
</main>

