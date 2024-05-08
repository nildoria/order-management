<!-- single item -->
<div class="allaround--blog-single-item">
    <div class="allaround--blog-thumbanil">
        <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail( 'blog_thumb' ); ?>
        </a>
    </div>

    <div class="allaround--blog-content">
        <h3><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h3>
        <p><?php the_excerpt(); ?></p>

        <div class="allaround--blog-meta">
            <span class="allaround--blog-author"><?php esc_html_e( 'ידי לע םסרופ:', 'hello-elementor' ); ?>
                <strong><?php the_author(); ?></strong>
            </span>
            <span class="allaround--blog-date"><?php the_time('j F Y'); ?></span>
        </div>
    </div>
</div>
<!-- single item -->