<?php
/**
 * Template Name: Create Order
 */

get_header();
?>
<main class="site-main" role="main">

    <div id="create-order-form">
        <h2>Create New Order</h2>
        <form id="orderForm" method="post">
            <?php wp_nonce_field('create_order', 'create_order_nonce'); ?>

            <div class="om_create_order_box">

                <div class="om_create__product_details">
                    <div class="om_create__product_details_title">
                        <h4><?php echo esc_html__('Product Details:', 'hello-elementor'); ?></h4>
                    </div>
                    <div class="form-group product-list-container">
                        <label for="fetchAddProductList">Select Products:</label>
                        <div id="selectedProductDisplay">Select Product...</div>
                        <ul id="productDropdown" class="product-dropdown"></ul>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="new_product_id">Product ID</label>
                        <input type="text" id="new_product_id" value="" placeholder="Enter Product ID" readonly />
                    </div>
                    <br>

                    <div class="grid-container">
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
                    <br>

                    <div class="form-group">
                        <label for="new_product_artwork">Artwork</label>
                        <input type="file" id="new_product_artwork" name="artwork" multiple />
                        <input type="hidden" id="uploaded_file_path" name="uploaded_file_path">
                    </div>
                    <br>


                    <label for="new_product_instruction_note">Instruction Note:</label>
                    <textarea id="new_product_instruction_note"></textarea>
                    <br>
                    <br>

                    <div class="shipping_method_update_box">
                        <div class="shipping_method_title">
                            <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?></h6>
                        </div>
                        <div class="shipping_method_value">
                            <select id="shipping-method-list" name="shipping_method">
                                <option value="">Select Shipping Option</option>
                                <option value="flat_rate" <?php echo $shipping_method == 'flat_rate' ? 'selected' : ''; ?>>
                                    שליח עד הבית לכל הארץ (3-5 ימי עסקים)</option>
                                <option value="free_shipping" <?php echo $shipping_method == 'free_shipping' ? 'selected' : ''; ?>>משלוח
                                    חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!</option>
                                <option value="local_pickup" <?php echo $shipping_method == 'local_pickup' ? 'selected' : ''; ?>>
                                    איסוף
                                    עצמי מקק"ל 37, גבעתיים (1-3 ימי עסקים) - חינם!</option>
                            </select>
                        </div>
                    </div>

                    <button type="button" class="allarnd--regular-button ml_add_loading" id="addProductButton"
                        disabled>Add Product</button>
                    <br>

                    <div id="line_items_added_om"></div>

                    <input type="hidden" id="line_items" name="line_items">
                </div>

                <div class="om_create__customer_details">
                    <div class="om_customer__details_title">
                        <h4><?php echo esc_html__('Customer Details:', 'hello-elementor'); ?></h4>
                    </div>

                    <div class="form-group">
                        <label for="firstName">First Name:</label>
                        <input type="text" id="firstName" name="first_name" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="lastName">Last Name:</label>
                        <input type="text" id="lastName" name="last_name" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="company">Company:</label>
                        <input type="text" id="company" name="company" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address_1" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <br>
                </div>
            </div>

            <button id="om--create-new-order" class="allarnd--regular-button ml_add_loading" type="submit">Create
                Order</button>
        </form>
    </div>

</main>
<?php get_footer(); ?>