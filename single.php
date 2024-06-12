<?php
/**
 * Single Post
 *
 */
get_header();

?>
<main class="site-main" role="main">
    <?php
    while (have_posts()):
        the_post();

        $current_id = get_the_ID();
        $order_number = get_post_meta(get_the_ID(), 'order_number', true);
        $order_id = get_post_meta(get_the_ID(), 'order_id', true);
        $shipping_method = get_post_meta(get_the_ID(), 'shipping_method', true);
        $order_status = get_post_meta(get_the_ID(), 'order_status', true);
        $order_domain = get_post_meta(get_the_ID(), 'site_url', true);
        $mockup_count = get_post_meta(get_the_ID(), '_mockup_count', true);
        ?>

            <div class="alarnd--single-content mockup-revision-page">
                <?php echo '<input type="hidden" id="order_id" value="' . esc_attr($order_id) . '">'; ?>

                <div id="order_mngmnt_headings" class="order_mngmnt_headings">
                    <div class="om_headin_titles">
                        <h6><?php echo esc_html__('Order Number:', 'hello-elementor'); ?>         <?php echo $order_number; ?></h6>
                        <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?>         <?php echo $order_status; ?></h6>
                        <h6><?php echo esc_html__('Last Sent Mockup:', 'hello-elementor'); ?> V<?php echo $mockup_count; ?></h6>
                        <div class="shipping_method_update_box">
                            <div class="shipping_method_title">
                                <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?></h6>
                            </div>
                            <div class="shipping_method_value">
                                <form id="shipping-method-form">
                                    <select id="shipping-method-list" name="shipping_method">
                                        <option value="">Select Shipping Option</option>
                                        <option value="flat_rate" <?php echo $shipping_method == 'flat_rate' ? 'selected' : ''; ?>>
                                            שליח עד הבית לכל הארץ (3-5 ימי עסקים)</option>
                                        <option value="free_shipping" <?php echo $shipping_method == 'free_shipping' ? 'selected' : ''; ?>>משלוח חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!</option>
                                        <option value="local_pickup" <?php echo $shipping_method == 'local_pickup' ? 'selected' : ''; ?>>איסוף עצמי מקק"ל 37, גבעתיים (1-3 ימי עסקים) - חינם!</option>
                                    </select>
                                    <input class="om_shipping_submit" type="submit" value="Update">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="om_headin_cta_buttons">
                        <button type="button" class="allarnd--regular-button ml_add_loading add_order_heading_btn"><a href="/add-order/">Add Order</a></button>
                    </div>
                </div>

                <div id="order_management_table_container" class="order_management_table_container">
                    <?php echo fetch_display_order_details($order_id, $order_domain, $current_id); ?>

                    <div id="add-item-modal" class="mfp-hide add-item-to-order-modal">
                        <div class="form-container">
                            <div class="form-group">
                                <label for="fetchProductList">Add New Item</label>
                                <!-- <input type="text" id="fetchProductList" placeholder="Add New Item" /> -->
                                <div id="selectedProductDisplay">Select Product...</div>
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
                                <label for="new_product_artwork">Artwork</label>
                                <input type="file" id="new_product_artwork" name="artwork" />
                                <input type="hidden" id="uploaded_file_path" name="uploaded_file_path">
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

                    <?php if (!ml_current_user_editor()): ?>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="addProductModal">Add
                                Product</button>
                    <?php endif; ?>
                    <button type="button" class="allarnd--regular-button ml_add_loading" id="addMockupButton">Add
                        Mockup</button>
                    <button type="button" class="allarnd--regular-button ml_add_loading" id="send-proof-button">Send
                        Proof</button>
                </div>

                <div class="mockup-revision-activity-container">
                    <h4>היסטוריית שינויים</h4>
                    <div class="revision-activities-all">
                        <?php
                        echo fetch_display_artwork_comments($order_id, $current_id);
                        ?>
                    </div>
                </div>
            </div>
    <?php endwhile; ?>
</main>

<?php
get_footer();
?>