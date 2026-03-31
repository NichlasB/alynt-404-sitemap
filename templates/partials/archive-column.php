<?php
/**
 * Partial template for archive column
 *
 * @package Alynt_404_Sitemap
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$post_type_obj = get_post_type_object( $current_post_type );
if ( ! $post_type_obj ) {
	return;
}

$excluded_ids = ! empty( $settings['excluded_ids'] ) ?
	array_map( 'absint', explode( ',', $settings['excluded_ids'] ) ) :
	array();

$args = array(
	'post_type'      => $current_post_type,
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
	'post__not_in'   => $excluded_ids,
	'no_found_rows'  => true,
);

$archive_posts = get_posts( $args );

if ( ! $archive_posts ) {
	return;
}

$is_hierarchical = is_post_type_hierarchical( $current_post_type );
$posts_by_parent = array();

if ( $is_hierarchical ) {
	foreach ( $archive_posts as $archive_post ) {
		$parent_id = (int) $archive_post->post_parent;

		if ( ! isset( $posts_by_parent[ $parent_id ] ) ) {
			$posts_by_parent[ $parent_id ] = array();
		}

		$posts_by_parent[ $parent_id ][] = $archive_post;
	}

	$archive_posts = $posts_by_parent[0] ?? array();
}

if ( ! $archive_posts ) {
	return;
}

$has_visible_posts = true;
?>

<div class="alynt-sitemap-column">
	<h2><?php echo esc_html( $post_type_obj->labels->name ); ?></h2>

	<ul>
		<?php foreach ( $archive_posts as $archive_post ) : ?>
			<li>
				<a href="<?php echo esc_url( get_permalink( $archive_post ) ); ?>"
					aria-label="
					<?php
					echo esc_attr(
						sprintf(
							/* translators: %s: Post title */
							__( 'View %s', 'alynt-404-sitemap' ),
							$archive_post->post_title
						)
					);
					?>
					">
					<?php echo esc_html( $archive_post->post_title ); ?>
				</a>

				<?php
				// Reuse the initial query results for hierarchical child output.
				if ( $is_hierarchical ) :
					$children = $posts_by_parent[ $archive_post->ID ] ?? array();

					if ( $children ) :
						?>
						<ul>
							<?php foreach ( $children as $child ) : ?>
								<li>
									<a href="<?php echo esc_url( get_permalink( $child ) ); ?>"
										aria-label="
										<?php
										echo esc_attr(
											sprintf(
												/* translators: %s: Post title */
												__( 'View %s', 'alynt-404-sitemap' ),
												$child->post_title
											)
										);
										?>
										">
										<?php echo esc_html( $child->post_title ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
