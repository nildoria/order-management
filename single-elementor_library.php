<?php
/**
* Sinlge Post
*
*/
get_header();
?>
<main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : ?>
        <?php the_post(); ?>

        <div class="alarnd--elementor-content">
            <?php the_content(); ?>
        </div>

    <?php endwhile; // end of the loop. ?>
   
</main>
<?php
get_footer();