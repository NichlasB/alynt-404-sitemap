<?php
/**
 * Partial template for archive column
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alynt_404_render_sitemap_branch' ) ) {
	/**
	 * Recursively render a sitemap branch for hierarchical content.
	 *
	 * @since 1.0.4
	 * @param WP_Post $post            Current post object.
	 * @param array   $posts_by_parent Posts keyed by parent ID.
	 * @return void
	 */
	function alynt_404_render_sitemap_branch( $post, $posts_by_parent ) {
		$children   = $posts_by_parent[ $post->ID ] ?? array();
		$aria_label = sprintf(
			/* translators: %s: Post title. */
			__( 'View %s', 'alynt-404-sitemap' ),
			$post->post_title
		);
		?>
		<li>
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"
				aria-label="<?php echo esc_attr( $aria_label ); ?>">
				<?php echo esc_html( $post->post_title ); ?>
			</a>
			<?php if ( $children ) : ?>
				<ul>
					<?php foreach ( $children as $child ) : ?>
						<?php alynt_404_render_sitemap_branch( $child, $posts_by_parent ); ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
	}
}

$post_type_obj = get_post_type_object( $current_post_type );
if ( ! $post_type_obj ) {
	return;
}

$excluded_ids = ! empty( $settings['excluded_ids'] ) ?
	array_map( 'absint', explode( ',', $settings['excluded_ids'] ) ) :
	array();

$base_args = array(
	'post_type'              => $current_post_type,
	'post_status'            => 'publish',
	'post__not_in'           => $excluded_ids,
	'no_found_rows'          => true,
	'ignore_sticky_posts'    => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
);

$is_hierarchical = is_post_type_hierarchical( $current_post_type );
$posts_by_parent = array();
$archive_posts   = array();

if ( $is_hierarchical ) {
	$archive_posts = get_posts(
		array_merge(
			$base_args,
			array(
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		)
	);

	if ( ! $archive_posts ) {
		return;
	}

	foreach ( $archive_posts as $archive_post ) {
		$parent_id = (int) $archive_post->post_parent;

		if ( ! isset( $posts_by_parent[ $parent_id ] ) ) {
			$posts_by_parent[ $parent_id ] = array();
		}

		$posts_by_parent[ $parent_id ][] = $archive_post;
	}

	$archive_posts = $posts_by_parent[0] ?? array();

	if ( ! $archive_posts ) {
		return;
	}
}

$batch_size  = max( 1, (int) apply_filters( ALYNT_404_PREFIX . 'sitemap_posts_per_page', 100 ) );
$paged_query = null;

if ( ! $is_hierarchical ) {
	$paged_query = new WP_Query(
		array_merge(
			$base_args,
			array(
				'posts_per_page' => $batch_size,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'paged'          => 1,
			)
		)
	);

	if ( ! $paged_query->have_posts() ) {
		return;
	}
}

$has_visible_posts = true;
?>

<div class="alynt-sitemap-column">
	<h2><?php echo esc_html( $post_type_obj->labels->name ); ?></h2>

	<ul>
		<?php if ( $is_hierarchical ) : ?>
			<?php foreach ( $archive_posts as $archive_post ) : ?>
				<?php alynt_404_render_sitemap_branch( $archive_post, $posts_by_parent ); ?>
			<?php endforeach; ?>
		<?php else : ?>
			<?php
			$query_page = 1;

			while ( $paged_query instanceof WP_Query && $paged_query->have_posts() ) :
				while ( $paged_query->have_posts() ) :
					$paged_query->the_post();
					$aria_label = sprintf(
						/* translators: %s: Post title. */
						__( 'View %s', 'alynt-404-sitemap' ),
						get_the_title()
					);
					?>
					<li>
						<a href="<?php echo esc_url( get_permalink() ); ?>"
							aria-label="<?php echo esc_attr( $aria_label ); ?>">
							<?php echo esc_html( get_the_title() ); ?>
						</a>
					</li>
					<?php
				endwhile;

				if ( $query_page >= (int) $paged_query->max_num_pages ) {
					break;
				}

				++$query_page;
				wp_reset_postdata();
				$paged_query = new WP_Query(
					array_merge(
						$base_args,
						array(
							'posts_per_page' => $batch_size,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'paged'          => $query_page,
						)
					)
				);
			endwhile;

			wp_reset_postdata();
			?>
		<?php endif; ?>
	</ul>
</div>
