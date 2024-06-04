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
                $order_id = get_post_meta(get_the_ID(), 'order_id', true);
                $shipping_method = get_post_meta(get_the_ID(), 'shipping_method', true);
                $order_status = get_post_meta(get_the_ID(), 'order_status', true);
                // Set the order number to a input type hidden field's value
                echo '<input type="hidden" id="order_id" value="' . esc_attr($order_id) . '">';
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
            <?php echo fetch_display_order_details($order_id); ?>

            <div id="add-item-modal" class="mfp-hide add-item-to-order-modal">
                <div class="form-container">
                    <div class="form-group">
                        <label for="fetchProductList">Add New Item</label>
                        <input type="text" id="fetchProductList" placeholder="Add New Item" />
                        <ul id="productDropdown" class="product-dropdown" style="display: none;"></ul>
                    </div>

                    <div class="form-group">
                        <label for="new_product_id">Product ID</label>
                        <input type="text" id="new_product_id" value="" placeholder="Enter Product ID" readonly />
                    </div>
                    <div class="form-group">
                        <label for="new_product_quantity">Quantity</label>
                        <input type="number" id="new_product_quantity" value="1" placeholder="Enter Quantity" />
                    </div>

                    <div class="form-group">
                        <label for="new_product_color">Color</label>
                        <select id="new_product_color">
                            <option value="">Select Color</option>
                        </select>
                    </div>

                    <div class="grid-container" id="size-color-container">
                        <div class="form-group om_default_hidden">
                            <label for="new_product_size">Size</label>
                            <select id="new_product_size"></select>
                        </div>
                        <div class="form-group om_default_hidden">
                            <label for="new_product_art_pos">Art Position</label>
                            <select id="new_product_art_pos">
                                <option value="">Select Art Position</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_product_instruction_note">Instruction Note</label>
                        <input type="text" id="new_product_instruction_note" value=""
                            placeholder="Enter Instruction Note" />
                    </div>
                    <div class="form-group">
                        <button id="addNewItemButton">Add New Item</button>
                    </div>
                </div>
            </div>
            <input type="button" value="Add Product" id="addProductModal" />


            <input type="button" value="Send Proof" id="send-proof-button" />
        </div>

        <div class="mockup-revision-activity-container">
            <h4>היסטוריית שינויים</h4>
            <div class="revision-activities-all">
                <?php
                echo fetch_display_artwork_comments($order_id);
                ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
?>