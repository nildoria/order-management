<?php
/**
 * Sinlge Page
 *
 */
get_header();
?>
<?php while (have_posts()): ?>
    <?php the_post();
    $title = get_the_title(); ?>
    <div class="allaround--full-bg">
        <div class="alarnd--content-wrap">
            <div class="allaround--breadcrumb">
                <h2 class="allaround---page-title"><?php echo esc_html($title); ?></h2>
            </div>
        </div>
    </div>

    <main id="main" class="site-main" role="main">

        <div class="alarnd--single-content default-page-content">
            <?php the_content(); ?>
        </div>

    </main>

<?php endwhile; ?>

<?php
get_footer();