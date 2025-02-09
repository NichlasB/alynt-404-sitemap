<?php
/**
 * Partial template for archive column
 *
 * @package Alynt_404_Sitemap
 */

if (!defined('WPINC')) {
    die;
}

$post_type_obj = get_post_type_object($post_type);
if (!$post_type_obj) return;

$excluded_ids = !empty($settings['excluded_ids']) ? 
    array_map('absint', explode(',', $settings['excluded_ids'])) : 
    array();

$args = array(
    'post_type' => $post_type,
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
    'post__not_in' => $excluded_ids
);

$posts = get_posts($args);

if (!$posts) return;
?>

<div class="alynt-sitemap-column">
    <h2><?php echo esc_html($post_type_obj->labels->name); ?></h2>
    
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <a href="<?php echo esc_url(get_permalink($post)); ?>"
                   aria-label="<?php echo esc_attr(sprintf(
                       /* translators: %s: Post title */
                       __('View %s', 'alynt-404-sitemap'),
                       $post->post_title
                   )); ?>">
                    <?php echo esc_html($post->post_title); ?>
                </a>
                
                <?php
                // If hierarchical, show children
                if (is_post_type_hierarchical($post_type)):
                    $children = get_posts(array(
                        'post_type' => $post_type,
                        'post_parent' => $post->ID,
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                        'post__not_in' => $excluded_ids
                    ));

                    if ($children):
                    ?>
                        <ul>
                            <?php foreach ($children as $child): ?>
                                <li>
                                    <a href="<?php echo esc_url(get_permalink($child)); ?>"
                                       aria-label="<?php echo esc_attr(sprintf(
                                           /* translators: %s: Post title */
                                           __('View %s', 'alynt-404-sitemap'),
                                           $child->post_title
                                       )); ?>">
                                        <?php echo esc_html($child->post_title); ?>
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