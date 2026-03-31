<?php
/**
 * The template for displaying the sitemap
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

get_header();

$settings          = get_option( ALYNT_404_PREFIX . 'sitemap_settings', array() );
$settings          = wp_parse_args(
	is_array( $settings ) ? $settings : array(),
	Alynt_404_Settings_Defaults::get_sitemap_defaults()
);
$post_types        = ! empty( $settings['post_types'] ) ? $settings['post_types'] : array( 'post', 'page' );
$has_visible_posts = false;

$responsive_classes = implode(
	' ',
	array(
		'desktop-cols-' . ( $settings['columns_desktop'] ?? 4 ),
		'tablet-cols-' . ( $settings['columns_tablet'] ?? 2 ),
		'mobile-cols-' . ( $settings['columns_mobile'] ?? 1 ),
	)
);
?>

<main id="primary" class="alynt-sitemap <?php echo esc_attr( $responsive_classes ); ?>" role="main">
	<h1>
		<?php echo esc_html( $settings['heading'] ?? __( 'Sitemap', 'alynt-404-sitemap' ) ); ?>
	</h1>

	<p>
		<?php echo esc_html( $settings['message'] ?? __( "Here's our website at a glance. Use this sitemap to quickly find what you're looking for.", 'alynt-404-sitemap' ) ); ?>
	</p>

	<div class="alynt-sitemap-row">
		<?php
		foreach ( $post_types as $current_post_type ) {
			require ALYNT_404_PATH . 'templates/partials/archive-column.php';
		}
		?>
	</div>

	<?php if ( ! $has_visible_posts ) : ?>
		<div class="alynt-sitemap-empty-state">
			<h2><?php esc_html_e( 'No sitemap content is available yet', 'alynt-404-sitemap' ); ?></h2>
			<p><?php esc_html_e( 'Published pages and posts will appear here once content is available for the selected sitemap types.', 'alynt-404-sitemap' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
