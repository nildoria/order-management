<?php
/**
* Sinlge Page
*
*/
get_header();
?>
<?php while ( have_posts() ) : ?>
<?php the_post();
$hero_title = get_field( 'hero_title', get_the_ID() );
$top_hero_header = get_field( 'top_hero_header', get_the_ID() );
$hero_description = get_field( 'hero_description', get_the_ID() );
$title = ! empty( $hero_title ) ? $hero_title : get_the_title();
if( ! empty( $top_hero_header ) ) : ?>
<div class="allaround--full-bg">
    <div class="alarnd--content-wrap">
        <div class="allaround--breadcrumb">
            <h2 class="allaround---page-title"><?php echo esc_html( $title ); ?></h2>
            <?php if( ! empty( $hero_description ) ) : ?>
            <p><?php echo allround_get_meta( $hero_description ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<main id="main" class="site-main" role="main">

    <div class="alarnd--single-content default-page-content">
        <?php the_content(); ?>
    </div>

</main>

<?php endwhile; ?>

<?php
get_footer();