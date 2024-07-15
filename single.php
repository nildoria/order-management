<?php
/**
 * Single Post
 *
 */
get_header();

$addItem = new AllAroundAddItem();
$products = $addItem->fetch_products_data();

?>
<main class="site-main" role="main">
    <?php
    while (have_posts()):
        the_post();

        $current_id = get_the_ID();
        $order_number = get_post_meta($current_id, 'order_number', true);
        $order_id = get_post_meta($current_id, 'order_id', true);
        $order_status = get_post_meta($current_id, 'order_status', true);
        $shipping_method = get_post_meta($current_id, 'shipping_method', true);
        $order_type = get_post_meta($current_id, 'order_type', true);
        $order_domain = get_post_meta($current_id, 'site_url', true);
        $order_manage_general_comment = get_post_meta($current_id, '_order_manage_general_comment', true);
        $order_extra_attachments = get_post_meta($current_id, '_order_extra_attachments', true);
        $client_id = get_post_meta($current_id, 'client_id', true);
        $client_name = '';
        $client_url = '';
        if (!empty($client_id)) {
            // get the post url for the client post with the id
            $first_name = get_post_meta($client_id, 'first_name', true);
            $last_name = get_post_meta($client_id, 'last_name', true);
            $email = get_post_meta($client_id, 'email', true);
            $client_name = $first_name . ' ' . $last_name;
            $client_url = get_permalink($client_id);
        }
        ?>


            <div class="alarnd--single-content mockup-revision-page">
                <?php echo '<input type="hidden" id="order_id" value="' . esc_attr($order_id) . '">'; ?>
                <?php echo '<input type="hidden" id="post_id" value="' . esc_attr($current_id) . '">'; ?>

                <div id="order_mngmnt_headings" class="order_mngmnt_headings">
                    <div class="om__summeryContainer">
                        <h4><?php echo esc_html__('Summary', 'hello-elementor'); ?></h4>
                        <div class="om_headin_titles">
                            <div class="om__orderSummeryOne">
                                <div class="om__orderSummeryItem">
                                <h6><?php echo esc_html__('Order Number:', 'hello-elementor'); ?><span><?php echo $order_number; ?></span></h6>
                                </div>
                                <div class="om__orderSummeryItem">
                                <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?><span><?php echo $order_status; ?></span></h6>
                                </div>
                                <div class="om__orderSummeryItem">
                                <h6><?php echo esc_html__('Client:', 'hello-elementor'); ?><span> <a href="<?php echo esc_url($client_url); ?>"><?php echo $client_name; ?></a></span></h6>
                                </div>
                            </div>
                            <div class="om__orderSummeryTwo">
                                <div class="shipping_method_update_box">
                                    <div class="shipping_method_title">
                                        <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?></h6>
                                    </div>
                                    <div class="shipping_method_value">
                                        <?php if (!ml_current_user_contributor()): ?>
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
                                        <?php else: ?>
                                            <select id="shipping-method-list" class="non-admin-shipping-list" name="shipping_method">
                                                <option value="">Select Shipping Option</option>
                                                <option value="flat_rate" <?php echo $shipping_method == 'flat_rate' ? 'selected' : ''; ?>>
                                                    שליח עד הבית לכל הארץ (3-5 ימי עסקים)</option>
                                                <option value="free_shipping" <?php echo $shipping_method == 'free_shipping' ? 'selected' : ''; ?>>משלוח חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!</option>
                                                <option value="local_pickup" <?php echo $shipping_method == 'local_pickup' ? 'selected' : ''; ?>>איסוף עצמי מקק"ל 37, גבעתיים (1-3 ימי עסקים) - חינם!</option>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="order_type_update_box">
                                    <div class="order_type_title">
                                        <h6><?php echo esc_html__('Order Type:', 'hello-elementor'); ?></h6>
                                    </div>
                                    <div class="order_type_value">
                                        <?php if (!ml_current_user_contributor()): ?>
                                        <form id="order_type-form">
                                            <input type="hidden" name="post_id" value="<?php echo $current_id; ?>">
                                            <select id="order_type" name="order_type">
                                                <option value="">Order Type</option>
                                                <option value="personal" <?php selected($order_type, 'personal'); ?>>Personal</option>
                                                <option value="company" <?php selected($order_type, 'company'); ?>>Company</option>
                                            </select>
                                            <input class="om_order_type_submit" type="submit" value="Update">
                                        </form>
                                        <?php else: ?>
                                            <select id="order_type" class="non-admin-shipping-list" name="order_type">
                                                <option value="">Order Type</option>
                                                <option value="personal" <?php selected($order_type, 'personal'); ?>>Personal</option>
                                                <option value="company" <?php selected($order_type, 'company'); ?>>Company</option>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="om__ordernoteContainer">
                        <h4><?php echo esc_html__('Order Note', 'hello-elementor'); ?></h4>
                        <div class="om_displayOrderNotesGrid">
                            <div class="om_addOrderNote<?php echo empty($order_manage_general_comment) && empty($order_extra_attachments) ? ' om_no_notes_addNote' : ''; ?>">
                                Add Note +</div>
                            <div class="om__displayOrderComment<?php echo empty($order_manage_general_comment) ? ' om_no_notes' : ''; ?>">
                                <?php if ($order_manage_general_comment): ?>
                                    <div class="om__orderGeneralComment_text">
                                        <p><?php echo $order_manage_general_comment; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        
                            <div class="om__orderNoteFiles_container<?php echo empty($order_extra_attachments) ? ' om_no_notes' : ''; ?>">
                                <h5><?php echo esc_html__('Attachments!', 'hello-elementor'); ?></h5>
                                <div class="om__orderNoteFiles">
                                    <?php if (!empty($order_extra_attachments)): ?>
                                        <?php foreach ($order_extra_attachments as $attachment): ?>
                                            <a href="<?php echo esc_url($attachment['url']); ?>"
                                                target="_blank"><?php echo esc_html($attachment['name']); ?></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php// if (!ml_current_user_contributor()): ?>
                    <!-- <div class="om_headin_cta_buttons">
                        <button type="button" class="allarnd--regular-button ml_add_loading add_order_heading_btn"><a href="/add-order/">Add
                                Order</a></button>
                    </div> -->
                <?php// endif; ?>

                <div id="order_management_table_container" class="order_management_table_container">
                    <div class="om_table_wraper">
                        <?php echo fetch_display_order_details($order_id, $order_domain, $current_id); ?>
                    </div>

                    <?php if (!ml_current_user_contributor()): ?>
                    
                    <div id="add-item-modal" class="mfp-hide add-item-to-order-modal product-details-modal" data-order_id="<?php echo $order_id; ?>">
                        <div class="form-container">
                            <div class="form-group select-product-input">
                                <label for="fetchingProductList">Add New Item</label>
                                <input type="text" id="fetchProductList" placeholder="Add New Item" />
                                <div id="selectedProductDisplay">Select Product...</div>
                                <ul id="productDropdown" class="product-dropdown" style="display: none;">
                                    
                                </ul>
                            </div>

                            <div id="productDetailsContainer"></div>

                            <input type="hidden" id="new_product_id" value="" placeholder="Enter Product ID" readonly />
                        </div>
                    </div>
                    <div class="om__afterTable_buttonSet">
                        <button type="button" class="allarnd--regular-button ml_add_loading" id="addProductModal"><?php echo esc_html__('Add Product', 'hello-elementor'); ?></button>
                        <button type="button" class="allarnd--regular-button ml_add_loading" id="send-proof-button"><?php echo esc_html__('Send Proof', 'hello-elementor'); ?></button>
                    </div>
                    <?php endif; ?>
                    <div id="om__orderNote_container" class="mfp-hide om__orderNote_container">
                        <div class="om__orderComment_title">
                            <h5><?php echo esc_html__('Add Order Notes', 'hello-elementor'); ?></h5>
                        </div>
                        <div class="om__extraOrder_Note_File">
                            <div class="om__orderComment_input">
                                <textarea id="order_general_comment" placeholder="Order Notes" ></textarea>
                            </div>
                            <div class="om__extra_attachments">
                                <input type="text" id="uploaded_extra_file_path" name="uploaded_extra_file_path" placeholder="Select Attachments" readonly>
                                <label class="om__extraAttachment_label" for="order_extra_attachments">
                                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
                                    </svg>
                                    <span>Attachments</span>
                                </label>
                                <input type="file" id="order_extra_attachments" name="order_extra_attachments" multiple />
                            </div>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="add-order-comment"><?php echo esc_html__('Add Note', 'hello-elementor'); ?></button>
                        </div>
                    </div>
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