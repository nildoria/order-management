<?php
/**
 * Single Post
 *
 */
get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

$createOrder = new AllAroundCreateOrder();
$clients = $createOrder->fetch_clients_data();
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
        $order_shipping = get_post_meta($current_id, 'shipping', true);
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
            // $client_address = get_post_meta($client_id, 'address_1', true);
            // $client_city = get_post_meta($client_id, 'city', true);
            // $client_phone = get_post_meta($client_id, 'phone', true);
            // $email = get_post_meta($client_id, 'email', true);
            // $invoice_name = get_post_meta($client_id, 'invoice', true);
            // $client_type = get_post_meta($client_id, 'client_type', true);
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
                                <?php if (!empty($client_name)): ?>
                                <div class="om__orderSummeryItem">
                                    <h6><?php echo esc_html__('Client:', 'hello-elementor'); ?><span class="om__orderedClientName"> <a target="_blank" href="<?php echo esc_url($client_url); ?>"><?php echo $client_name; ?></a><span class="om__edit_clientButton"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit.svg" alt=""></span></span></h6>

                                    <!-- Client Change -->
                                    <div class="content-client om__change-client">
                                        <select id="client-select" style="width: 100%;">
                                            <option value="">Select a Client</option>
                                            <?php foreach ($clients as $client): ?>
                                            <option data-phone="<?php echo esc_html($client['phone']); ?>"
                                                data-email="<?php echo esc_html($client['email']); ?>" value="<?php echo esc_attr($client['id']); ?>">
                                                <?php echo esc_html($client['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span title="Update Client" class="om__client_update_btn ml_add_loading">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mark_icon-svg.svg" alt="Mark Icon">
                                        </span>
                                    </div>

                                </div>
                                <?php endif; ?>
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
                        <div class="om_displayOrderNotesGrid<?php echo empty($order_manage_general_comment) && empty($order_extra_attachments) ? ' om_no_notes_gridNote' : ''; ?>">
                            <div class="om_addOrderNote<?php echo empty($order_manage_general_comment) && empty($order_extra_attachments) ? ' om_no_notes_addNote' : ''; ?>">
                                Add Note +</div>
                            <div class="om__displayOrderComment<?php echo empty($order_manage_general_comment) ? ' om_no_notes' : ''; ?>">
                                <div class="om__orderGeneralComment_text">
                                <?php if ($order_manage_general_comment): ?>
                                    <p><?php echo nl2br(esc_html($order_manage_general_comment)); ?></p>
                                <?php endif; ?>
                                </div>
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

                <div class="om__orderShippingDetails <?php echo ($shipping_method === "local_pickup") ? "local_pickup" : ""; ?>">
                    <label for="om__orderShippingDetailsGrid"><?php echo esc_html__('Order Shipping Details', 'hello-elementor'); ?></label>
                    <div class="om__orderShippingDetailsGrid">
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_first_name">First Name</label>
                            <input type="text" id="shipping_first_name" name="shipping_first_name" value="<?php echo esc_html($order_shipping['first_name']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_first_name">Last Name</label>
                            <input type="text" id="shipping_last_name" name="shipping_last_name" value="<?php echo esc_html($order_shipping['last_name']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem orderShippingFieldLong">
                            <label for="shipping_first_name">Street Address</label>
                            <input type="text" id="shipping_address_1" name="shipping_address_1" value="<?php echo esc_html($order_shipping['address_1']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_first_name">Street Number</label>
                            <input type="text" id="shipping_postcode" name="shipping_postcode" value="<?php echo esc_html($order_shipping['postcode']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_first_name">City</label>
                            <input type="text" id="shipping_city" name="shipping_city" value="<?php echo esc_html($order_shipping['city']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_first_name">Phone</label>
                            <input type="text" id="shipping_phone" name="shipping_phone" value="<?php echo esc_html($order_shipping['phone']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="update-shipping-details"><?php echo esc_html__('Update', 'hello-elementor'); ?></button>
                        </div>
                    </div>
                </div>

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
                                <textarea id="order_general_comment" placeholder="Order Notes"><?php echo $order_manage_general_comment; ?></textarea>
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
                    <div class="mockup-proof-admin-comments">
                        <div class="form-group">
                            <label for="mockup-proof-comments">Mockup Comments</label>
                            <input type="text" name="mockup-proof-comments" id="mockup-proof-comments" placeholder="Mockup Comments">
                        </div>
                    </div>
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