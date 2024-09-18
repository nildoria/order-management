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
        $post_date = get_the_date('d/m/Y', $current_id);
        $order_number = get_post_meta($current_id, 'order_number', true);
        $order_id = get_post_meta($current_id, 'order_id', true);
        $order_status = get_post_meta($current_id, 'order_status', true);
        $shipping_method = get_post_meta($current_id, 'shipping_method', true);
        $shipping_method_title = get_post_meta($current_id, 'shipping_method_title', true);
        $order_type = get_post_meta($current_id, 'order_type', true);
        // update_post_meta($current_id, 'order_type', '');
        $order_shipping = get_post_meta($current_id, 'shipping', true);
        $order_domain = get_post_meta($current_id, 'site_url', true);
        $order_source_value = get_post_meta($current_id, 'order_source', true);
        // Define the mapping of values to texts
        $order_source_options = array(
            'miniSite_order' => 'Mini Site',
            'mainSite_order' => 'Main Site',
            'manual_order' => 'Manual Order',
        );
        // Get the corresponding text
        $order_source_text = isset($order_source_options[$order_source_value]) ? $order_source_options[$order_source_value] : 'Unknown';

        $order_manage_general_comment = get_post_meta($current_id, '_order_manage_general_comment', true);
        $order_extra_attachments = get_post_meta($current_id, '_order_extra_attachments', true);

        $order_manage_designer_notes = get_post_meta($current_id, '_order_manage_designer_notes', true);
        $order_designer_extra_attachments = get_post_meta($current_id, '_order_designer_extra_attachments', true);

        // If the Order is Self Hosted or not
        $self_hosted_manual_orders = get_post_meta($current_id, 'self_hosted_manual_orders', true);

        $client_id = get_post_meta($current_id, 'client_id', true);
        $client_name = '';
        $client_url = '';
        if (!empty($client_id)) {
            // get the post url for the client post with the id
            $client_url = get_permalink($client_id);
            $first_name = get_post_meta($client_id, 'first_name', true);
            $last_name = get_post_meta($client_id, 'last_name', true);
            $client_name = $first_name . ' ' . $last_name;
            $client_address = get_post_meta($client_id, 'address_1', true);
            $client_address_2 = get_post_meta($client_id, 'address_2', true);
            $client_city = get_post_meta($client_id, 'city', true);
            $client_phone = get_post_meta($client_id, 'phone', true);
            $email = get_post_meta($client_id, 'email', true);
            $invoice_name = get_post_meta($client_id, 'invoice', true);
            $logo_type = get_post_meta($client_id, 'logo_type', true);
            $mini_url = get_post_meta($client_id, 'mini_url', true);
            $mini_header = get_post_meta($client_id, 'mini_header', true);
            $client_type = get_post_meta($client_id, 'client_type', true);
            $lighter_logo = '';
            $dark_logo = '';
            $back_light = '';
            $back_dark = '';

            if (!empty(get_post_meta($client_id, 'lighter_logo', true))) {
                $lighter_logo = esc_attr(get_post_meta($client_id, 'lighter_logo', true));
            }

            if (!empty(get_post_meta($client_id, 'dark_logo', true))) {
                $dark_logo = esc_attr(get_post_meta($client_id, 'dark_logo', true));
            }

            if (!empty(get_post_meta($client_id, 'back_light', true))) {
                $back_light = esc_attr(get_post_meta($client_id, 'back_light', true));
            }

            if (!empty(get_post_meta($client_id, 'back_dark', true))) {
                $back_dark = esc_attr(get_post_meta($client_id, 'back_dark', true));
            }
            // if $client_type is company, and $lighter_logo or $dark_logo is not empty, then update the _order_extra_attachments meta with the logos
            if ($client_type === "company" && (!empty($lighter_logo) || !empty($dark_logo))) {
                // Check if the meta data already exists
                $existing_attachments = get_post_meta($current_id, '_order_designer_extra_attachments', true);

                if (empty($existing_attachments)) {
                    $order_extra_attachments = array();
                    if (!empty($lighter_logo)) {
                        $order_extra_attachments[] = array(
                            'id' => $lighter_logo_id,
                            'name' => 'Lighter Logo',
                            'url' => $lighter_logo,
                        );
                    }
                    if (!empty($dark_logo)) {
                        $order_extra_attachments[] = array(
                            'id' => $dark_logo_id,
                            'name' => 'Dark Logo',
                            'url' => $dark_logo,
                        );
                    }

                    // Update the meta data only if it doesn't exist
                    update_post_meta($current_id, '_order_designer_extra_attachments', $order_extra_attachments);
                }
            }
        }
        ?>

        <div id="delete_order_transient"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/cached.png" alt="Delete Order Cache" title="Update Order Transient"></div>
        <div class="alarnd--single-content mockup-revision-page">
            <?php echo '<input type="hidden" id="order_id" value="' . esc_attr($order_id) . '">'; ?>
            <?php echo '<input type="hidden" id="post_id" value="' . esc_attr($current_id) . '">'; ?>

            <div id="order_mngmnt_headings" class="order_mngmnt_headings">
                <div class="om__summeryContainer">
                    <h4><?php echo esc_html__('Summary', 'hello-elementor'); ?></h4>
                    <div class="om_headin_titles">
                        <div class="om__orderSummeryOne">
                            <div class="om__orderSummeryItem">
                                <h6><?php echo esc_html__('Order Number:', 'hello-elementor'); ?><span><?php echo esc_attr($order_number); ?></span></h6>

                            </div>

                            <?php if (is_current_user_admin() || is_current_user_contributor()): ?>
                            <div class="om__orderSummeryItem">
                            <h6><?php echo esc_html__('Past Orders:', 'hello-elementor'); ?><span>
                            <a href="<?php echo esc_url(admin_url('admin-ajax.php') . '?action=get_client_orders&client_id=' . $client_id . '&_nonce=' . wp_create_nonce('get_client_nonce')); ?> "
                                class="allaround--client-orders"><?php echo esc_html__('View Orders', 'hello-elementor'); ?></a></span></h6>
                            </div>
                            <?php endif; ?>

                            <?php if (is_current_user_admin()): ?>
                            <div class="om__orderSummeryItem">
                            <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?><span id="om__orderStatus" data-order_status="<?php echo esc_attr($order_status); ?>"><?php echo esc_attr($order_status); ?></span></h6>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($client_name) && is_current_user_admin()): ?>
                            <div class="om__orderSummeryItem om__orderSummeryItemFull">
                                <div class="om__clientThatOrdered">
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
                                </div>
                                <div class="om__orderEmptyItem">
                                    <h6><?php echo esc_html__('Order Date:', 'hello-elementor'); ?><span><?php echo esc_attr($post_date); ?></span></h6>
                                </div>
                                <div class="om__orderSource">
                                    <h6><?php echo esc_html__('Source:', 'hello-elementor'); ?><span id="om__order_source" data-order_source="<?php echo $order_source_value; ?>"><?php echo $order_source_text; ?></span></h6>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Billing Information Form -->
                            <div id="billing-form-modal" class="mfp-hide billing-form om__billing-form-modal">
                                <h5>Client Information</h5>
                                <form id="billing-form">
                                    <div class="om__client_personal_info">
                                        <div class="form-group">
                                            <label for="billing_first_name">First Name:</label>
                                            <input type="text" id="billing_first_name" name="billing_first_name" value="<?php echo esc_attr($first_name); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_last_name">Last Name:</label>
                                            <input type="text" id="billing_last_name" name="billing_last_name" value="<?php echo esc_attr($last_name); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_address_1">Street Address:</label>
                                            <input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr($client_address); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_address_2">Street Number:</label>
                                            <input type="text" id="billing_address_2" name="billing_address_2" value="<?php echo esc_attr($client_address_2); ?>"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_company">Invoice Name:</label>
                                            <input type="text" id="billing_company" name="billing_company" value="<?php echo esc_attr($invoice_name); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_city">City:</label>
                                            <input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr($client_city); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_country" style="display: none;">Country:</label>
                                            <input type="hidden" id="billing_country" name="billing_country" value="Israel">
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_email">Email:</label>
                                            <input type="email" id="billing_email" name="billing_email" value="<?php echo esc_attr($email); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="billing_phone">Phone:</label>
                                            <input type="text" id="billing_phone" name="billing_phone" value="<?php echo esc_attr($client_phone); ?>" required>
                                        </div>
                                    </div>
                                    <div class="om__client_company_info "
                                    <?php if ($client_type === "company" && $order_type === "company"): ?> 
                                        style="display: block;" 
                                    <?php endif; ?>>

                                        <input type="hidden" name="client_type" id="client_type" value="<?php echo esc_attr($client_type); ?>">
                                        
                                        <div class="form-group">
                                            <label for="logo_type">Logo Type:</label>
                                                <select name="logo_type" id="logo_type">
                                                    <option value="same" <?php selected($logo_type, 'same'); ?>>Same</option>
                                                    <option value="chest_only" <?php selected($logo_type, 'chest_only'); ?>>Chest only</option>
                                                    <option value="big_front" <?php selected($logo_type, 'big_front'); ?>>Big front</option>
                                                    <option value="custom_back" <?php selected($logo_type, 'custom_back'); ?>>Custom back</option>
                                                </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="mini_url">Mini URL:</label>
                                            <input type="text" name="mini_url" id="mini_url" value="<?php echo esc_attr($mini_url); ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label for="mini_header">Mini Header:</label>
                                            <input type="text" name="mini_header" id="mini_header" value="<?php echo esc_attr($mini_header); ?>" />
                                        </div>
                                    </div>
                                </form>
                                <button type="button" id="update-order-client" class="ml_add_loading" data-client_id="<?php echo esc_attr($client_id); ?>"><?php echo esc_html__('Update Info', 'hello-elementor'); ?></button>
                            </div>
                        </div>
                        <div class="om__orderSummeryTwo">
                            <?php if (!is_current_user_contributor()): ?>
                            <div class="shipping_method_update_box">
                                <div class="shipping_method_title">
                                    <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?></h6>
                                </div>
                                <div class="shipping_method_value">
                                    <?php if (is_current_user_admin()): ?>
                                    <form id="shipping-method-form">
                                        <select id="shipping-method-list" name="shipping_method">
                                            <option value="">Select Shipping Option</option>
                                            <?php if ($shipping_method && $shipping_method_title): ?>
                                                <option value="<?php echo esc_attr($shipping_method); ?>" selected><?php echo esc_html($shipping_method_title); ?></option>
                                            <?php endif; ?>
                                            
                                            <?php if ($shipping_method_title !== 'שליח עד הבית לכל הארץ (3-5 ימי עסקים)'): ?>
                                            <option value="flat_rate"
                                                data-title="שליח עד הבית לכל הארץ (3-5 ימי עסקים)">
                                                שליח עד הבית לכל הארץ (3-5 ימי עסקים)
                                            </option>
                                            <?php endif; ?>


                                            <?php if ($shipping_method_title !== 'משלוח חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!'): ?>
                                            <option value="free_shipping" data-title="משלוח חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!">
                                                משלוח חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!
                                            </option>
                                            <?php endif; ?>

                                            <?php if ($shipping_method_title !== 'איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!'): ?>
                                            <option value="local_pickup" data-title="איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!">איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!</option>
                                            <?php endif; ?>

                                            <option value="getpackage" data-title="GetPackage">
                                                GetPackage
                                            </option>
                                        </select>
                                        <input class="om_shipping_submit" type="submit" value="Update">
                                    </form>
                                    <?php else: ?>
                                        <select id="shipping-method-list" class="non-admin-shipping-list" name="shipping_method" disabled>
                                            <option value="<?php echo esc_attr($shipping_method); ?>" selected>
                                                <?php echo esc_html($shipping_method_title); ?>
                                            </option>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="order_type_update_box">
                                <div class="order_type_title">
                                    <h6><?php echo esc_html__('Order Type:', 'hello-elementor'); ?></h6>
                                </div>
                                <div class="order_type_value">
                                    <?php if (is_current_user_admin()): ?>
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

                <!-- Not Designer Role check -->
                <?php if (!is_current_user_contributor()): ?>
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
                            <label for="shipping_last_name">Last Name</label>
                            <input type="text" id="shipping_last_name" name="shipping_last_name"
                                value="<?php echo esc_html($order_shipping['last_name']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem orderShippingFieldLong">
                            <label for="shipping_phone">Phone</label>
                            <input type="text" id="shipping_phone" name="shipping_phone"
                                value="<?php echo esc_html($order_shipping['phone']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_address_1">Street Address</label>
                            <input type="text" id="shipping_address_1" name="shipping_address_1"
                                value="<?php echo esc_html($order_shipping['address_1']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_address_2">Street Number</label>
                            <input type="text" id="shipping_address_2" name="shipping_address_2"
                                value="<?php echo esc_html($order_shipping['address_2']); ?>">
                        </div>
                        <div class="om__orderShippingDetailsItem">
                            <label for="shipping_city">City</label>
                            <input type="text" id="shipping_city" name="shipping_city"
                                value="<?php echo esc_html($order_shipping['city']); ?>">
                        </div>
                        <?php if (is_current_user_admin()): ?>
                        <div class="om__orderShippingDetailsItem omShippingSubmitBtn">
                            <button type="button" class="allarnd--regular-button ml_add_loading"
                                id="update-shipping-details"><?php echo esc_html__('Update', 'hello-elementor'); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Not Employee role Check -->
                <?php if (!is_current_user_author()): ?>
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
                <?php endif; ?>

            </div>
            
            <!-- Not Employee role Check -->
            <?php if (!is_current_user_author() && $order_type === "company"): ?>
                <div class="om__companyLogoUpload">
                    
                    <div class="form-group om_company_logoInput_group">
                        <label for="dark_logo">Dark Logo:</label>
                        <div class="form-group-flex">
                            <input type="text" name="dark_logo" id="dark_logo" value="<?php echo esc_attr($dark_logo); ?>" />
                            <input type="button" id="dark_logo_button" class="button upload-image-button"
                                value="" />
                            <input type="button" id="remove_dark_logo_button" class="button remove-image-button"
                                value="" style="display: none; ?>;" />
                        </div>
                        <img src="<?php echo esc_attr($dark_logo); ?>" style="max-width: 150px; display: none;" alt="" />
                    </div>
                    <div class="form-group om_company_logoInput_group">
                        <label for="lighter_logo">Lighter Logo:</label>
                        <div class="form-group-flex">
                            <input type="text" name="lighter_logo" id="lighter_logo" value="<?php echo esc_attr($lighter_logo); ?>" />
                            <input type="button" id="lighter_logo_button" class="button upload-image-button"
                                value="" />
                            <input type="button" id="remove_lighter_logo_button" class="button remove-image-button"
                                value="" style="display: none; ?>;" />
                        </div>
                        <img src="<?php echo esc_attr($lighter_logo); ?>" style="max-width: 150px; display: none;" alt="" />
                    </div>
                    <div class="form-group om_company_logoInput_group">
                        <label for="back_light">Back Light:</label>
                        <div class="form-group-flex">
                            <input type="text" name="back_light" id="back_light" value="<?php echo esc_attr($back_light); ?>" />
                            <input type="button" id="back_light_button" class="button upload-image-button"
                                value="" />
                            <input type="button" id="remove_back_light_button" class="button remove-image-button"
                                value="" style="display: none; ?>;" />
                        </div>
                        <img src="<?php echo esc_attr($back_light); ?>" style="max-width: 150px; display: none;" alt="" />
                    </div>
                    <div class="form-group om_company_logoInput_group">
                        <label for="back_dark">Back Dark:</label>
                        <div class="form-group-flex">
                            <input type="text" name="back_dark" id="back_dark" value="<?php echo esc_attr($back_dark); ?>" />
                            <input type="button" id="back_dark_button" class="button upload-image-button"
                                value="" />
                            <input type="button" id="remove_back_dark_button" class="button remove-image-button"
                                value="" style="display: none; ?>;" />
                        </div>
                        <img src="<?php echo esc_attr($back_dark); ?>" style="max-width: 150px; display: none;" alt="" />
                    </div>
                    
                    <div class="om__companyLogoUploadSubmit">
                        <button type="button" data-client_id="<?php echo esc_attr($client_id); ?>" class="allarnd--regular-button ml_add_loading"
                            id="submitOmCompanyLogo"><?php echo esc_html__('Update', 'hello-elementor'); ?></button>
                    </div>
                </div>
            <?php endif; ?>

            <div id="order_management_table_container" class="order_management_table_container">
                <div class="om_table_wraper">
                    <?php if (!$self_hosted_manual_orders): ?>
                        <?php echo fetch_display_order_details($order_id, $order_domain, $current_id); ?>
                    <?php else: ?>
                        <div class="om__selfHostedManualOrder">
                            <?php
                                // Retrieve the items meta data
                                $items = get_post_meta($current_id, 'items', true);


                                // Check if items exist and is an array
                                if (!empty($items) && is_array($items)) {
                                    echo '<table id="tableMain" data-order_status="'. $order_status .'">';
                                    echo '<thead>';
                                    echo '<tr>';
                                    echo '<th class="head">Product</th>';
                                    echo '<th class="head">Quantity</th>';
                                    echo '<th class="head">Total Price</th>';
                                    echo '</tr>';
                                    echo '</thead>';
                                    echo '<tbody>';

                                    // Loop through each item and display the details
                                    foreach ($items as $item) {
                                        echo '<tr class="om__orderRow">';
                                        echo '<td class="item_product_column">';
                                        echo '<span class="om_item_thumb_cont">';
                                        echo '<img src="' . get_template_directory_uri() . '/assets/images/allaround-logo.png' . '" alt="' . esc_attr($item['product_name']) . '">';
                                        echo '</span>';
                                        echo '<span class="item_name_variations">';
                                        echo '<strong class="product_item_title">' . esc_html($item['product_name']) . '</strong>';
                                        echo '</span>';
                                        echo '</td>';
                                        echo '<td class="item_quantity_column">' . esc_html($item['quantity']) . '</td>';
                                        echo '<td class="item_total_pricee">' . esc_html($item['total']) . '</td>';
                                        echo '</tr>';
                                    }

                                    echo '</tbody>';
                                    echo '</table>';
                                }
                                ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (is_current_user_admin()): ?>
                
                <div id="add-item-modal" class="mfp-hide add-item-to-order-modal product-details-modal" data-order_id="<?php echo $order_id; ?>" data-client_id="<?php echo $client_id; ?>">
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
                        <button type="button" class="allarnd--regular-button ml_add_loading" id="sendProofOpenModal"><?php echo esc_html__('Send Proof', 'hello-elementor'); ?></button>
                        
                        <div id="sendProofConfirmationModal" class="om__ConfirmationModal mfp-hide">
                            <h5><?php echo esc_html__('Are you sure the proofs are ready?', 'hello-elementor'); ?></h5>
                            <p>Please confirm before sending.</p>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="send-proof-button"><?php echo esc_html__('Send Proof', 'hello-elementor'); ?></button>
                        
                            <button type="button"
                                class="allarnd--regular-button confmodalCancel"><?php echo esc_html__('CANCEL', 'hello-elementor'); ?></button>
                        </div>
                    <?php endif; ?>
                    <?php if (is_current_user_contributor() || is_current_user_admin()): ?>
                        <button type="button" id="missingInfoOpenModal" class="allarnd--regular-button ml_add_loading warning_btn"><?php echo esc_html__('Missing info', 'hello-elementor'); ?></button>
                        
                        <div id="missingInfoConfirmationModal" class="om__ConfirmationModal mfp-hide">
                            <h5><?php echo esc_html__('Describe what informations are missing.', 'hello-elementor'); ?></h5>
                            <textarea name="missing-info" class="designer_missing_info_text"
                                placeholder="Write your message"></textarea><br><br>
                            <button type="button" data-status="Missing info"
                                class="designerSendWebhook allarnd--regular-button ml_add_loading"><?php echo esc_html__('SEND', 'hello-elementor'); ?></button>
                        
                            <button type="button" class="allarnd--regular-button confmodalCancel"><?php echo esc_html__('CANCEL', 'hello-elementor'); ?></button>
                        </div>
                        <button type="button" id="mockupDoneOpenModal" class="allarnd--regular-button ml_add_loading"><?php echo esc_html__('Mockups Done', 'hello-elementor'); ?></button>

                        <div id="mockupDoneConfirmationModal" class="om__ConfirmationModal mfp-hide">
                            <h5><?php echo esc_html__('Are you sure All Mockups Uploaded successfully?', 'hello-elementor'); ?></h5><br>
                            <button type="button" data-status="Mockups Done" class="designerSendWebhook allarnd--regular-button ml_add_loading"><?php echo esc_html__('YES UPLOADED', 'hello-elementor'); ?></button>

                            <button type="button" class="allarnd--regular-button confmodalCancel"><?php echo esc_html__('CANCEL', 'hello-elementor'); ?></button>
                        </div>
                        
                        <button type="button" id="DTFDoneOpenModal" class="allarnd--regular-button ml_add_loading"><?php echo esc_html__('DTF Done', 'hello-elementor'); ?></button>
                        
                        <div id="DTFDoneConfirmationModal" class="om__ConfirmationModal mfp-hide">
                            <h5><?php echo esc_html__('Are you sure DTF Done successfully?', 'hello-elementor'); ?></h5><br>
                            <button type="button" data-status="dtf_done"
                                class="designerSendWebhook allarnd--regular-button ml_add_loading"><?php echo esc_html__('YES DTF Done', 'hello-elementor'); ?></button>
                        
                            <button type="button"
                                class="allarnd--regular-button confmodalCancel"><?php echo esc_html__('CANCEL', 'hello-elementor'); ?></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (is_current_user_author() || is_current_user_admin()): ?>
                        <button type="button" class="allarnd--regular-button ml_add_loading"
                            id="printLabelOpenModal"><?php echo esc_html__('Print Label', 'hello-elementor'); ?></button>
                        <div id="printLabelConfirmationModal" class="om__ConfirmationModal mfp-hide">
                            <h5>Are you sure the order is 100% finalized? </h5>
                            <p>Please re-check all the order.</p>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="printLabelSendWebhook"><?php echo esc_html__('YES - IT\'S READY', 'hello-elementor'); ?></button>
                            <button type="button" class="allarnd--regular-button" id="printLabelCancel"><?php echo esc_html__('NO', 'hello-elementor'); ?></button>
                        </div>
                    <?php endif; ?>

                    <?php if (is_current_user_admin()): ?>
                        <button type="button" class="allarnd--regular-button ml_add_loading" id="missingGraphicOpenModal"><?php echo esc_html__('Missing Graphic', 'hello-elementor'); ?></button>
                        <div id="missingGraphicConfirmModal" class="om__ConfirmationModal mfp-hide">
                            <h5>Are you sure the Graphic is Missing? </h5>
                            <p>Please re-check all the items.</p>
                            <button type="button" class="allarnd--regular-button ml_add_loading" id="missingGraphic"><?php echo esc_html__('YES - Missing Graphic', 'hello-elementor'); ?></button>
                            <button type="button" class="allarnd--regular-button confmodalCancel" id="missingGraphicCancel"><?php echo esc_html__('NO', 'hello-elementor'); ?></button>
                        </div>
                        
                        <button type="button" class="allarnd--regular-button ml_add_loading" id="mockupApprovedOpenModal"><?php echo esc_html__('Mockups Approved', 'hello-elementor'); ?></button>
                        <div id="mockupApprovedConfirmModal" class="om__ConfirmationModal mfp-hide">
                            <h5>Are you sure the Mockup is Approved? </h5>
                            <p>Please re-check approval status.</p>
                            <button type="button" class="allarnd--regular-button ml_add_loading"
                                id="mockupApproved"><?php echo esc_html__('YES - Mockups Approved', 'hello-elementor'); ?></button>
                            <button type="button" class="allarnd--regular-button confmodalCancel"
                                id="mockupApprovedCancel"><?php echo esc_html__('NO', 'hello-elementor'); ?></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (is_current_user_admin()): ?>
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