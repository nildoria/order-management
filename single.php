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

        $order_manage_designer_notes = get_post_meta($current_id, '_order_manage_designer_notes', true);
        $order_designer_extra_attachments = get_post_meta($current_id, '_order_designer_extra_attachments', true);

        $client_id = get_post_meta($current_id, 'client_id', true);
        $client_name = '';
        $client_url = '';
        if (!empty($client_id)) {
            // get the post url for the client post with the id
            $first_name = get_post_meta($client_id, 'first_name', true);
            $last_name = get_post_meta($client_id, 'last_name', true);
            $client_address = get_post_meta($client_id, 'address_1', true);
            $client_city = get_post_meta($client_id, 'city', true);
            $client_phone = get_post_meta($client_id, 'phone', true);
            $email = get_post_meta($client_id, 'email', true);
            $invoice_name = get_post_meta($client_id, 'invoice', true);
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

                                <?php if (!is_current_user_author()): ?>
                                <div class="om__orderSummeryItem">
                                <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?><span><?php echo $order_status; ?></span></h6>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($client_name) && is_current_user_admin()): ?>
                                <div class="om__orderSummeryItem">
                                    <h6><?php echo esc_html__('Client:', 'hello-elementor'); ?><span class="om__orderedClientName"> <a target="_blank" href="<?php echo esc_url($client_url); ?>"><?php echo $client_name; ?></a><span class="om__edit_clientButton"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/edit.svg" alt=""></span></span></h6>

                                    <!-- Client Change -->
                                    <div class="content-client om__change-client">
                                        <span class="toogle-select-client">&times;</span>
                                        <select id="client-select" class="om__client-select" style="width: 100%;">
                                            <option value="">Select a Client</option>
                                            <?php foreach ($clients as $client): ?>
                                            <option data-phone="<?php echo esc_html($client['phone']); ?>"
                                                data-email="<?php echo esc_html($client['email']); ?>" value="<?php echo esc_attr($client['id']); ?>" <?php echo ($client['id'] == $client_id) ? 'selected' : ''; ?>>
                                                <?php echo esc_html($client['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span title="Edit" class="client_profile_URL om__client_profile_edit"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.733 8.86672V10.7334C10.733 10.9809 10.6347 11.2183 10.4596 11.3934C10.2846 11.5684 10.0472 11.6667 9.79967 11.6667H3.26634C3.01881 11.6667 2.78141 11.5684 2.60637 11.3934C2.43134 11.2183 2.33301 10.9809 2.33301 10.7334V4.20006C2.33301 3.95252 2.43134 3.71512 2.60637 3.54009C2.78141 3.36506 3.01881 3.26672 3.26634 3.26672H5.13301" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.23281 8.77337L11.6661 4.29337L9.70615 2.33337L5.27281 6.76671L5.13281 8.86671L7.23281 8.77337Z" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                        <span title="Update Client" class="om__client_update_btn ml_add_loading">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mark_icon-svg.svg" alt="Mark Icon">
                                        </span>
                                    </div>

                                    <!-- Billing Information Form -->
                                    <div id="billing-form-modal" class="mfp-hide billing-form">
                                        <h5>Client Information</h5>
                                        <form id="billing-form">
                                            <label for="billing_first_name">First Name:</label>
                                            <input type="text" id="billing_first_name" name="billing_first_name" value="<?php echo esc_html($first_name); ?>" required>
                                            <label for="billing_last_name">Last Name:</label>
                                            <input type="text" id="billing_last_name" name="billing_last_name" value="<?php echo esc_html($last_name); ?>">
                                            <label for="billing_address_1">Address:</label>
                                            <input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_html($client_address); ?>" required>
                                            <label for="billing_company">Invoice Name:</label>
                                            <input type="text" id="billing_company" name="billing_company" value="<?php echo esc_html($invoice_name); ?>">
                                            <label for="billing_city">City:</label>
                                            <input type="text" id="billing_city" name="billing_city" value="<?php echo esc_html($client_city); ?>" required>
                                            <label for="billing_country" style="display: none;">Country:</label>
                                            <input type="hidden" id="billing_country" name="billing_country" value="Israel">
                                            <label for="billing_email">Email:</label>
                                            <input type="email" id="billing_email" name="billing_email" value="<?php echo esc_html($email); ?>" required>
                                            <label for="billing_phone">Phone:</label>
                                            <input type="text" id="billing_phone" name="billing_phone" value="<?php echo esc_html($client_phone); ?>" required>
                                        </form>
                                        <button type="button" id="update-order-client" class="ml_add_loading" data-client_id=""><?php echo esc_html__('Update Info', 'hello-elementor'); ?></button>
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
                                        <?php if (is_current_user_admin()): ?>
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
                                <?php if (is_current_user_admin()): ?>
                                <div class="order_type_update_box">
                                    <div class="order_type_title">
                                        <h6><?php echo esc_html__('Order Type:', 'hello-elementor'); ?></h6>
                                    </div>
                                    <div class="order_type_value">
                                        <?//php if (is_current_user_admin()): ?>
                                        <form id="order_type-form">
                                            <input type="hidden" name="post_id" value="<?php echo $current_id; ?>">
                                            <select id="order_type" name="order_type">
                                                <option value="">Order Type</option>
                                                <option value="personal" <?php selected($order_type, 'personal'); ?>>Personal</option>
                                                <option value="company" <?php selected($order_type, 'company'); ?>>Company</option>
                                            </select>
                                            <input class="om_order_type_submit" type="submit" value="Update">
                                        </form>
                                        <?//php else: ?>
                                            <!-- <select id="order_type" class="non-admin-shipping-list" name="order_type">
                                                <option value="">Order Type</option>
                                                <option value="personal" <?//php selected($order_type, 'personal'); ?>>Personal</option>
                                                <option value="company" <?//php selected($order_type, 'company'); ?>>Company</option>
                                            </select> -->
                                        <?//php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="om__general_notes_cont">
                        <h4><?php echo esc_html__('Order Note', 'hello-elementor'); ?></h4>
                        <div
                            class="om_displayOrderNotesGrid<?php echo empty($order_manage_general_comment) && empty($order_extra_attachments) ? ' om_no_notes_gridNote' : ''; ?>">
                            <div
                                class="om_addOrderNote<?php echo empty($order_manage_general_comment) && empty($order_extra_attachments) ? ' om_no_notes_addNote' : ''; ?>">
                                Add Note +
                            </div>
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
                                            <div class="attachment-item">
                                                <a href="<?php echo esc_url($attachment['url']); ?>"
                                                    target="_blank"><?php echo esc_html($attachment['name']); ?></a>
                                                <span class="delete-attachment"
                                                    data-attachment-id="<?php echo esc_attr($attachment['id']); ?>" data-attachment-type="general">&times;</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div id="om__orderNote_container" class="mfp-hide om__orderNote_container">
                            <div class="om__orderComment_title">
                                <h5><?php echo esc_html__('Add Order Notes', 'hello-elementor'); ?></h5>
                            </div>
                            <div class="om__extraOrder_Note_File">
                                <div class="om__orderComment_input">
                                    <textarea id="order_general_comment"
                                        placeholder="Order Notes"><?php echo $order_manage_general_comment; ?></textarea>
                                </div>
                                <div class="om__extra_attachments">
                                    <input type="text" id="uploaded_extra_file_path" name="uploaded_extra_file_path"
                                        placeholder="Select Attachments" readonly>
                                    <label class="om__extraAttachment_label" for="order_extra_attachments">
                                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload"
                                            class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 512 512">
                                            <path fill="currentColor"
                                                d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z">
                                            </path>
                                        </svg>
                                        <span>Attachments</span>
                                    </label>
                                    <input type="file" id="order_extra_attachments" name="order_extra_attachments" multiple />
                                </div>
                                <button type="button" class="allarnd--regular-button ml_add_loading"
                                    id="add-order-comment"><?php echo esc_html__('Add Note', 'hello-elementor'); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="om__orderShippingDetails <?php echo ($shipping_method === "local_pickup") ? "local_pickup" : ""; ?>">
                        <label
                            for="om__orderShippingDetailsGrid"><?php echo esc_html__('Order Shipping Details', 'hello-elementor'); ?></label>
                        <div class="om__orderShippingDetailsGrid">
                            <div class="om__orderShippingDetailsItem">
                                <label for="shipping_first_name">First Name</label>
                                <input type="text" id="shipping_first_name" name="shipping_first_name"
                                    value="<?php echo esc_html($order_shipping['first_name']); ?>">
                            </div>
                            <div class="om__orderShippingDetailsItem">
                                <label for="shipping_first_name">Last Name</label>
                                <input type="text" id="shipping_last_name" name="shipping_last_name"
                                    value="<?php echo esc_html($order_shipping['last_name']); ?>">
                            </div>
                            <div class="om__orderShippingDetailsItem orderShippingFieldLong">
                                <label for="shipping_first_name">Street Address</label>
                                <input type="text" id="shipping_address_1" name="shipping_address_1"
                                    value="<?php echo esc_html($order_shipping['address_1']); ?>">
                            </div>
                            <div class="om__orderShippingDetailsItem">
                                <label for="shipping_first_name">Street Number</label>
                                <input type="text" id="shipping_postcode" name="shipping_postcode"
                                    value="<?php echo esc_html($order_shipping['postcode']); ?>">
                            </div>
                            <div class="om__orderShippingDetailsItem">
                                <label for="shipping_first_name">City</label>
                                <input type="text" id="shipping_city" name="shipping_city"
                                    value="<?php echo esc_html($order_shipping['city']); ?>">
                            </div>
                            <div class="om__orderShippingDetailsItem">
                                <label for="shipping_first_name">Phone</label>
                                <input type="text" id="shipping_phone" name="shipping_phone"
                                    value="<?php echo esc_html($order_shipping['phone']); ?>">
                            </div>
                            <?php if (is_current_user_admin()): ?>
                            <div class="om__orderShippingDetailsItem">
                                <button type="button" class="allarnd--regular-button ml_add_loading"
                                    id="update-shipping-details"><?php echo esc_html__('Update', 'hello-elementor'); ?></button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="om__ordernoteContainer">
                        <div class="om__designers_notes_cont">
                            <label><?php echo esc_html__('Designer Note', 'hello-elementor'); ?></label>
                            <div class="om_displayOrderNotesGrid<?php echo empty($order_manage_designer_notes) && empty($order_designer_extra_attachments) ? ' om_no_notes_gridNote' : ''; ?>">
                                <div class="om_addDesignerNote<?php echo empty($order_manage_designer_notes) && empty($order_designer_extra_attachments) ? ' om_no_notes_addNote' : ''; ?>">
                                    Add Note +
                                </div>
                                <div class="om__displayDesignerComment<?php echo empty($order_manage_designer_notes) ? ' om_no_notes' : ''; ?>">
                                    <div class="om__orderDesignerNote_text">
                                        <?php if ($order_manage_designer_notes): ?>
                                            <p><?php echo nl2br(esc_html($order_manage_designer_notes)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            
                                <div class="om__designerNoteFiles_container<?php echo empty($order_designer_extra_attachments) ? ' om_no_notes' : ''; ?>">
                                    <h5><?php echo esc_html__('Attachments!', 'hello-elementor'); ?></h5>
                                    <div class="om__designerNoteFiles">
                                        <?php if (!empty($order_designer_extra_attachments)): ?>
                                            <?php foreach ($order_designer_extra_attachments as $attachment): ?>
                                                <div class="attachment-item">
                                                    <a href="<?php echo esc_url($attachment['url']); ?>" target="_blank"><?php echo esc_html($attachment['name']); ?></a>
                                                    <span class="delete-attachment" data-attachment-id="<?php echo esc_attr($attachment['id']); ?>" data-attachment-type="designer">&times;</span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="om__designerNote_container" class="mfp-hide om__designerNote_container">
                                <div class="om__orderComment_title">
                                    <h5><?php echo esc_html__('Add Designer Notes', 'hello-elementor'); ?></h5>
                                </div>
                                <div class="om__extraOrder_Note_File">
                                    <div class="om__orderComment_input">
                                        <textarea id="order_designer_note" placeholder="Designer Notes"><?php echo $order_manage_designer_notes; ?></textarea>
                                    </div>
                                    <div class="om__extra_attachments">
                                        <input type="text" id="uploaded_designer_extra_file_path" name="uploaded_designer_extra_file_path" placeholder="Select Attachments" readonly>
                                        <label class="om__extraAttachment_label" for="order_designer_extra_attachments">
                                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="upload" class="svg-inline--fa fa-upload fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
                                            </svg>
                                            <span>Attachments</span>
                                        </label>
                                        <input type="file" id="order_designer_extra_attachments" name="order_designer_extra_attachments" multiple />
                                    </div>
                                    <button type="button" class="allarnd--regular-button ml_add_loading" id="add-designer-note"><?php echo esc_html__('Add Note', 'hello-elementor'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div id="order_management_table_container" class="order_management_table_container">
                    <div class="om_table_wraper">
                        <?php echo fetch_display_order_details($order_id, $order_domain, $current_id); ?>
                    </div>

                    <?php if (is_current_user_admin()): ?>
                    
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
                    
                    <?php endif; ?>
                    <div class="om__afterTable_buttonSet">
                        <?php if (is_current_user_admin()): ?>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="addProductModal"><?php echo esc_html__('Add Product', 'hello-elementor'); ?></button>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="send-proof-button"><?php echo esc_html__('Send Proof', 'hello-elementor'); ?></button>
                        <?php endif; ?>
                        <?php if (ml_current_user_contributor() || is_current_user_admin()): ?>
                            <button type="button" class="allarnd--regular-button ml_add_loading"
                                id="mockupDoneSendWebhook"><?php echo esc_html__('Mockups Done', 'hello-elementor'); ?></button>
                        <?php endif; ?>
                        
                        <?php if (is_current_user_author() || is_current_user_admin()): ?>
                            <button type="button" class="allarnd--regular-button ml_add_loading"
                                id="printLabelSendWebhook"><?php echo esc_html__('Print Label', 'hello-elementor'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!is_current_user_author()): ?>
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
                <?php endif; ?>

            </div>
    <?php endwhile; ?>
</main>

<?php
get_footer();
?>