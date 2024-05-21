<?php
/**
 * Single Post
 *
 */
get_header();

?>
<main class="site-main" role="main">

    <div class="alarnd--single-content mockup-revision-page">
        <?php
        // Start the loop
        if (have_posts()):
            while (have_posts()):
                the_post();

                $order_number = get_post_meta(get_the_ID(), 'order_number', true);
                $shipping_method = get_post_meta(get_the_ID(), 'shipping_method', true);
                $order_status = get_post_meta(get_the_ID(), 'order_status', true);
                // Set the order number to a input type hidden field's value
                echo '<input type="hidden" id="order_number" value="' . esc_attr($order_number) . '">';
                ?>
            <?php endwhile;
        endif; // End the loop
        ?>
        <div id="order_mngmnt_headings" class="order_mngmnt_headings">
            <h6><?php echo esc_html__('Order Number:', 'hello-elementor'); ?> <?php echo $order_number; ?></h6>
            <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?> <?php echo $shipping_method; ?></h6>
            <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?> <?php echo $order_status; ?></h6>
        </div>

        <div id="order_management_table_container" class="order_management_table_container">
            <?php
            echo fetch_display_order_details($order_number);
            ?>
        </div>

        <div class="mockup-revision-activity-container">
            <h4>היסטוריית שינויים</h4>
            <div class="revision-activities-all">
                <?php
                echo fetch_display_artwork_comments($order_number);
                ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
?>