<?php
/**
 * Template Name: Blog Tempalte
 *
 */
get_header();
?>

<?php while (have_posts()):
    the_post();
?>
<?php endwhile; ?>

<div class=" allaround-section-padding ">
    <div class="allaround-container">
        <div class="alarnd--overlay"></div>
        <div class="allaround--blog-categories">
            <?php
            $terms = get_terms('category');
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<ul>';
                echo '<li class="alarnd_blog_cat" data-term_id="all"><a href="#">' . esc_html__('All', 'hello-elementor') . '</a></li>';
                foreach ($terms as $term) {
                    echo '<li class="alarnd_blog_cat" data-term_id="' . $term->term_id . '"><a href="' . esc_url(get_term_link($term)) . '">' . $term->name . '</a></li>';
                }
                echo '</ul>';
            } ?>
        </div>
        <div class="allaround--blog-wraper">

            <?php
            $big = 999999999; // need an unlikely integer
            $posts_per_page = get_option('posts_per_page');
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $posts_per_page = !empty($posts_per_page) ? (int) $posts_per_page : 10;
            $post_args = array(
                'posts_per_page' => $posts_per_page,
                'post_type' => 'post',
                'post_status' => 'publish',
                'order' => 'DESC',
                'paged' => $paged
            );
            $post_qry = new WP_Query($post_args);
            if ($post_qry->have_posts()):
                while ($post_qry->have_posts()):
                    $post_qry->the_post();
                    get_template_part('blog', 'item');
                endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <p><?php esc_html_e('Sorry, no review found.', 'hello-elementor'); ?></p>
            <?php endif; ?>

            <div class="allaround--pagination-wrap" data-base-url="<?php echo esc_url(get_pagenum_link($big)); ?>">
                <?php

                // echo '<pre>';
                // print_r( esc_url( get_pagenum_link( $big ) ) );
                // echo '</pre>';
                echo paginate_links(
                    array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $post_qry->max_num_pages,
                        'prev_text' => esc_html__('Previous', 'hello-elementor'),
                        'next_text' => esc_html__('Next', 'hello-elementor'),
                        'type' => 'list',
                    ));
                ?>
            </div>

        </div>

    </div>
</div>


<?php
get_footer();