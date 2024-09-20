<?php
/**
 * Template Name: Create Order
 */

get_header();

// Restrict access to logged-in users
restrict_access_to_admin_and_editor();


$createOrder = new AllAroundCreateOrder();
$products = $createOrder->fetch_products_data();
$clients = $createOrder->fetch_clients_data();

// Collect all categories from the products
$all_categories = [];
foreach ($products as $product) {
    if (is_array($product['categories'])) {
        foreach ($product['categories'] as $category) {
            if (!isset($all_categories[$category['slug']])) {
                $all_categories[$category['slug']] = $category['name'];
            }
        }
    }
}

// Fetch users with the editor role
$agents = [];
if (is_current_user_admin()) {
    $agents = get_users(array('role' => 'editor'));
}

$current_user = wp_get_current_user();
$current_user_is_editor = is_current_user_editor();
?>
<main id="om__createOrderPage" class="site-main" role="main">
    <div id="create-order-page">
        <div class="content-product">
            <div class="top-panel">
                <div class="search-bar">
                    <div class="search-box">
                        <input type="text" name="search" id="product-search" placeholder="Search product by typing">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                </div>
                <div class="category">
                    <select id="category-select" style="width: 100%;">
                        <option value="all">All Categories</option>
                        <?php foreach ($all_categories as $slug => $name): ?>
                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="items-wrapper grid">
                <?php if (is_array($products) && !empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php if (is_array($product)): ?>
                            <?php
                            $variable_product = $product['is_variable_product'] === true;
                            $custom_quantity_product = $product['is_custom_quantity'] === true;
                            $grouped_product = $product['is_group_quantity'] === true;
                            $is_grouped_class = $grouped_product ? ' grouped-product' : '';
                            if ($grouped_product) {
                                $size_number = is_array($product['sizes']) ? count($product['sizes']) : 0;
                                error_log('Size number: ' . var_export($size_number, true) . ' (Type: ' . gettype($size_number) . ')');
                                $size_modal_width = (intval($size_number) * 70) + 130;
                            }

                            // Collect product category slugs
                            $categories = array_map(function ($cat) {
                                return $cat['slug']; }, $product['categories']);
                            $categories = implode(' ', $categories);
                            ?>

                            <div class="item <?php echo esc_attr($categories); ?>" data-category="<?php echo esc_attr($categories); ?>">
                                <div class="item-wrap"
                                    data-modal-id="product-details-modal-<?php echo esc_html($product['id']); ?>">
                                    <div class="img">
                                        <img src="<?php echo esc_url($product['image']); ?>"
                                            alt="<?php echo esc_attr($product['name']); ?>">
                                    </div>
                                    <div class="item-content">
                                        <h6 class="title"><?php echo esc_html($product['name']); ?></h6>
                                        <?php
                                        if (isset($product['quantity_steps']) && is_array($product['quantity_steps'])) {
                                            $range_to_amount = number_format($product['price'], 2);
                                            $range_from_amount = number_format($product['quantity_steps'][count($product['quantity_steps']) - 1]['amount'], 2);
                                            echo '<span class="product-price">' . esc_html($range_from_amount) . '₪ - ' . esc_html($range_to_amount) . '₪</span>';
                                        } else {
                                            echo '<span class="product-price">' . esc_html($product['price']) . '₪</span>';
                                        }
                                        ?>
                                        <button class="item-select-quantity">Select Quantity</button>
                                    </div>
                                </div>
                                <div id="product-details-modal-<?php echo esc_html($product['id']) ?>"
                                    class="mfp-hide product-details-modal <?php echo $is_grouped_class ?>" data-product_id="<?php echo esc_html($product['id']) ?>">
                                    <input type="hidden" name="product-thumb" class="product-thumb" value="<?php echo esc_url($product['thumbnail']); ?>">
                                    <h4 class="modal-title"><?php echo esc_html($product['name']); ?></h4>

                                    <!-- Custom Quantity START -->
                                    <?php if ($custom_quantity_product): ?>
                                        <div class="product-custom-quantity-wraper">
                                            <?php if (is_array($product['colors']) && $product['colors'] !== false): ?>
                                                <div class="form-group">
                                                    <label for="new_product_color">Select a Color</label>
                                                    <div class="custom-colors-wrapper">
                                                        <?php $index = 0;
                                                        foreach ($product['colors'] as $color): ?>
                                                            <span class="alarnd--single-var-info">
                                                                <input type="radio" id="custom_color-<?php echo $product['id'] . $index; ?>"
                                                                    name="custom_color" value="<?php echo htmlspecialchars($color['color']); ?>"
                                                                    <?php if ($index == 0)
                                                                        echo 'checked="checked"'; ?>>
                                                                <label
                                                                    for="custom_color-<?php echo $product['id'] . $index; ?>"><?php echo htmlspecialchars($color['color']); ?></label>
                                                            </span>
                                                            <?php $index++; endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (is_array($product['quantity_steps']) && $product['quantity_steps']): ?>
                                                <div class="form-group">
                                                    <label for="custom-quantity">Quantity</label>
                                                    <div class="quantity-wrapper">
                                                        <input type="text" name="custom-quantity" class="custom-quantity" value="1"
                                                            data-steps='<?php echo json_encode($product['quantity_steps']); ?>'>
                                                        <div class="price-total">
                                                            <span class="item-total-number">0</span>₪
                                                            <input type="hidden" class="item_total_price" name="item_total_price">
                                                        </div>
                                                        <div class="price-item">
                                                            <span
                                                                class="item-rate-number">0</span><span><?php echo esc_html__(' per unit', 'hello-elementor'); ?></span>
                                                        </div>
                                                    </div>
                                                    <input type="text" placeholder="Custom Rate" name="item-rate-number" class="item-rate-number-input">
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-group">
                                                <label for="new_product_artwork">Upload Artwork</label>
                                                <input type="file" class="new_product_artwork" name="artwork" multiple />
                                                <input type="hidden" class="uploaded_file_path" name="uploaded_file_path">
                                            </div>
                                            <div class="form-group">
                                                <label for="new_product_instruction_note">Instruction Note</label>
                                                <input type="text" class="new_product_instruction_note" value=""
                                                    placeholder="Enter Instruction Note" />
                                            </div>
                                            <button name="add-to-cart" value="<?php echo esc_html($product['id']) ?>"
                                                class="single_add_to_cart_button ml_add_loading button alt "
                                                ><?php echo esc_html__('Add to cart', 'hello-elementor'); ?></button>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Custom Quantity END -->

                                    <!-- Variable Product START -->
                                    <?php if ($variable_product): ?>
                                        <div class="product-variable-quantity-wrapper">
                                            <?php
                                            $first_variation = $product['quantity_steps'][0];
                                            $has_size = isset($first_variation['attribute_key1']) && $first_variation['attribute_key1'] === 'Size';
                                            $quantity_label = isset($first_variation['attribute_key2']) ? $first_variation['attribute_key2'] : $first_variation['attribute_key1'];
                                            ?>

                                            <?php if ($has_size): ?>
                                            <div class="form-group">
                                                <label><?php echo esc_html($first_variation['attribute_key1']); ?></label>
                                                <div class="custom-sizes-wrapper">
                                                    <?php foreach ($product['quantity_steps'] as $index => $variation): ?>
                                                        <span class="alarnd--single-var-info">
                                                            <input type="radio" 
                                                                id="size-<?php echo esc_attr($product['id'] . '-' . $variation['id']); ?>"
                                                                name="product_size" 
                                                                value="<?php echo esc_attr($variation['id']); ?>"
                                                                data-steps='<?php echo esc_attr(json_encode($variation['steps'])); ?>'
                                                                <?php echo $index === 0 ? 'checked="checked"' : ''; ?>>
                                                            <label for="size-<?php echo esc_attr($product['id'] . '-' . $variation['id']); ?>">
                                                                <?php echo esc_html($variation['name']); ?>
                                                            </label>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <div class="form-group">
                                                <label for="variable-quantity-<?php echo esc_attr($product['id']); ?>"><?php echo esc_html($quantity_label); ?></label>
                                                <div class="quantity-wrapper">
                                                    <select id="variable-quantity-<?php echo esc_attr($product['id']); ?>" name="variable-quantity"
                                                        class="variable-quantity" data-product-id="<?php echo esc_attr($product['id']); ?>"
                                                        data-has-size="<?php echo $has_size ? 'true' : 'false'; ?>"
                                                        data-steps='<?php echo esc_attr(json_encode($product['quantity_steps'])); ?>'>
                                                        <?php
                                                                $steps = $has_size ? $first_variation['steps'] : $product['quantity_steps'];
                                                                foreach ($steps as $step):
                                                                    $quantity = $has_size ? $step['quantity'] : $step['name'];
                                                                    $amount = $has_size ? $step['amount'] : $step['steps'][0]['amount'];
                                                                    ?>
                                                            <option value="<?php echo esc_attr($quantity); ?>" data-amount="<?php echo esc_attr($amount); ?>">
                                                                <?php echo esc_html($quantity); ?> <!-- - <?//php echo esc_html($amount); ?>₪ -->
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="price-total">
                                                        <span class="item-total-number">0</span>₪
                                                        <input type="hidden" class="item_total_price" name="item_total_price">
                                                    </div>
                                                    <input type="text" name="variableProductCustomRate" placeholder="Custom Total" class="variableItem-total-number-input">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="new_product_artwork">Upload Artwork</label>
                                                <input type="file" class="new_product_artwork" name="artwork" multiple />
                                                <input type="hidden" class="uploaded_file_path" name="uploaded_file_path">
                                            </div>
                                            <div class="form-group">
                                                <label for="new_product_instruction_note">Instruction Note</label>
                                                <input type="text" class="new_product_instruction_note" value="" placeholder="Enter Instruction Note" />
                                            </div>
                                            <button name="add-to-cart" value="<?php echo esc_attr($product['id']); ?>"
                                                class="variable_add_to_cart_button ml_add_loading button alt">
                                                <?php echo esc_html__('Add to cart', 'hello-elementor'); ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Variable Product END -->
                                    <!-- Group Product START -->
                                    <?php if ($grouped_product): ?>
                                        <div class="product-grouped-product-wraper" style="width: <?php echo esc_attr($size_modal_width) ?>px" data-regular_price='<?php echo esc_attr($product['price']); ?>' data-steps='<?php echo json_encode($product['quantity_steps']); ?>'>
                                            <div class="alarnd--select-options-cart-wrap">
                                                <div class="alarnd--select-options">
                                                    <div class="alarnd--select-opt-wrapper">
                                                        <div class="alarnd--select-opt-header">
                                                            <?php foreach ($product['sizes'] as $size): ?>
                                                                <span><?php echo esc_html($size); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div class="alarnd--select-qty-body">
                                                            <?php foreach ($product['colors'] as $color_index => $color): ?>
                                                                <div class="alarn--opt-single-row">
                                                                    <?php foreach ($product['sizes'] as $size): ?>
                                                                        <?php
                                                                        // Check if the current size is in the omit_sizes array for this color
                                                                        $omit_size = false;
                                                                        if (is_array($color['omit_sizes'])) {
                                                                            foreach ($color['omit_sizes'] as $omit_size_data) {
                                                                                if ($omit_size_data['value'] === $size) {
                                                                                    $omit_size = true;
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="tshirt-qty-input-field<?php echo $omit_size ? ' omit-size' : ''; ?>">
                                                                            <input type="text" autocomplete="off"
                                                                                name="alarnd__color_qty[<?php echo $color_index; ?>][<?php echo $size; ?>]"
                                                                                class="group-product-input"
                                                                                data-color="<?php echo htmlspecialchars($color['title']); ?>"
                                                                                data-size="<?php echo htmlspecialchars($size); ?>"
                                                                                <?php echo $omit_size ? 'disabled' : ''; ?> <?php echo $omit_size ? 'placeholder="N/A"' : ''; ?>>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                    <div class="alarnd--opt-color">
                                                                        <span style="background-color: <?php echo htmlspecialchars($color['color_hex_code']); ?>;"><?php echo htmlspecialchars($color['title']); ?></span>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grouped-product-meta-data">
                                            <div class="form-group">
                                                <label for="new_product_artwork">Upload Artwork</label>
                                                <input type="file" class="new_product_artwork" name="artwork" multiple />
                                                <input type="hidden" class="uploaded_file_path" name="uploaded_file_path">
                                            </div>
                                            <div class="form-group">
                                                <label for="new_product_art_pos">Art Position</label>
                                                <select class="new_product_art_pos">
                                                    <option value="">Select Art Position</option>
                                                    <?php foreach ($product['art_positions'] as $artPosition): ?>
                                                        <option value="<?php echo esc_html($artPosition['title']); ?>"><?php echo esc_html($artPosition['title']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_product_instruction_note">Instruction Note</label>
                                                <input type="text" class="new_product_instruction_note" value=""
                                                    placeholder="Enter Instruction Note" />
                                            </div>
                                            <div class="form-group">
                                                <label for="grouped_custom_rate_input">Custom Rate</label>
                                                <input type="text" class="grouped_custom_rate_input" name="grouped_custom_rate_input"
                                                    placeholder="Custom Rate" />
                                            </div>
                                        </div>
                                        <div class="grouped-modal-actions">
                                            <div class="alarnd--price-by-shirt">
                                                <p class="alarnd--group-price">
                                                    <span class="group_unite_price">0</span>₪ / <?php echo esc_html__('Unit', 'hello-elementor'); ?>
                                                    <input type="hidden" class="item_unit_rate" name="item_unit_rate">
                                                </p>
                                                <p class="total-units">
                                                    <?php echo esc_html__('Total Units: ', 'hello-elementor'); ?><span class="total_units">0</span>
                                                    <input type="hidden" class="item_total_units" name="item_total_units">
                                                </p>
                                                <div class="price-total">
                                                    <?php echo esc_html__('Total: ', 'hello-elementor'); ?><span class="item-total-number">0</span>₪
                                                    <input type="hidden" class="item_total_price" name="item_total_price">
                                                </div>
                                            </div>
                                            <button name="add-to-cart" value="<?php echo esc_html($product['id']) ?>"
                                            class="grouped_product_add_to_cart ml_add_loading button alt "><?php echo esc_html__('Add to cart', 'hello-elementor'); ?></button>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Group Product END -->

                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="item all freestyle" data-category="all freestyle">
                        <div class="item-wrap" data-modal-id="product-details-modal-freestyle">
                            <div class="img">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/allaround-logo.png" alt="Freestyle">
                            </div>
                            <div class="item-content">
                                <h6 class="title">Freestyle Item</h6>
                                <span class="product-price">0.00₪ - 0.00₪</span>
                                <button class="item-select-quantity">Select Quantity</button>
                            </div>
                        </div>
                        <div id="product-details-modal-freestyle" class="product-details-modal mfp-hide" data-product_id="freestyle">
                            <input type="hidden" name="product-thumb" class="product-thumb" value="<?php echo get_template_directory_uri(); ?>/assets/images/allaround-logo.png">
                            <h4 class="modal-title">Freestyle Item</h4>
                            <div class="product-custom-quantity-wraper">
                                <div class="form-group">
                                    <label for="custom-quantity">Quantity</label>
                                    <div class="quantity-wrapper">
                                        <input type="text" name="freestyle-custom-quantity" class="freestyle-custom-quantity" value="1" data-steps='[]'>
                                        <div class="price-total">
                                            <span class="item-total-number">0</span>₪
                                            <input type="hidden" class="item_total_price" name="item_total_price" value="0">
                                        </div>
                                        <div class="price-item freestyle-price-rate">
                                            <input type="text" value="1" name="item-rate-number" class="freestyle-rate-number-input">
                                            <span> per unit</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="new_product_artwork">Upload Artwork</label>
                                    <input type="file" class="new_product_artwork" name="artwork" multiple="">
                                    <input type="hidden" class="uploaded_file_path" name="uploaded_file_path">
                                </div>
                                <div class="form-group">
                                    <label for="new_product_instruction_note">Instruction Note</label>
                                    <input type="text" class="new_product_instruction_note" value="" placeholder="Enter Instruction Note">
                                </div>
                                <button name="add-to-cart" value="freestyle" class="single_add_to_cart_button ml_add_loading button alt ">Add to cart</button>
                            </div>
                                                                
                        </div>
                    </div>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="content-cart-user-wraper">
            <!-- Agent Selection Dropdown -->
            <?php if (is_current_user_admin() || is_current_user_editor()): ?>
                <div class="content-agent">
                    <select id="agent-select" name="agent_id" style="width: 100%;">
                        <?php if (is_current_user_admin()): ?>
                            <option value="">Select an Agent</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?php echo esc_attr($agent->ID); ?>">
                                    <?php echo esc_html($agent->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php elseif ($current_user_is_editor): ?>
                            <option value="<?php echo esc_attr($current_user->ID); ?>" selected>
                                <?php echo esc_html($current_user->display_name); ?>
                            </option>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endif; ?>
            <!-- Client selection -->
            <div class="content-client">
                <select id="client-select" style="width: 100%;">
                    <option value="">Select a Client</option>
                    <?php foreach ($clients as $client): ?>
                        <option data-phone="<?php echo esc_html($client['phone']); ?>" data-email="<?php echo esc_html($client['email']); ?>" value="<?php echo esc_attr($client['id']); ?>">
                            <?php echo esc_html($client['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span title="Edit" class="client_profile_URL"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.733 8.86672V10.7334C10.733 10.9809 10.6347 11.2183 10.4596 11.3934C10.2846 11.5684 10.0472 11.6667 9.79967 11.6667H3.26634C3.01881 11.6667 2.78141 11.5684 2.60637 11.3934C2.43134 11.2183 2.33301 10.9809 2.33301 10.7334V4.20006C2.33301 3.95252 2.43134 3.71512 2.60637 3.54009C2.78141 3.36506 3.01881 3.26672 3.26634 3.26672H5.13301" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.23281 8.77337L11.6661 4.29337L9.70615 2.33337L5.27281 6.76671L5.13281 8.86671L7.23281 8.77337Z" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>

            <!-- Billing Information Form -->
            <div id="billing-form-modal" class="mfp-hide billing-form">
                <h5>Client Information</h5>
                <form id="billing-form">
                    <label for="billing_first_name">First Name:</label>
                    <input type="text" id="billing_first_name" name="billing_first_name" required>
                    <label for="billing_last_name">Last Name:</label>
                    <input type="text" id="billing_last_name" name="billing_last_name">
                    <label for="billing_address_1">Street Address:</label>
                    <input type="text" id="billing_address_1" name="billing_address_1" required>
                    <label for="billing_address_2">Street Number:</label>
                    <input type="text" id="billing_address_2" name="billing_address_2" required>
                    <label for="billing_company">Invoice Name:</label>
                    <input type="text" id="billing_company" name="billing_company">
                    <label for="billing_city">City:</label>
                    <input type="text" id="billing_city" name="billing_city" required>
                    <label for="billing_country" style="display: none;">Country:</label>
                    <input type="hidden" id="billing_country" name="billing_country" value="Israel">
                    <label for="billing_email">Email:</label>
                    <input type="email" id="billing_email" name="billing_email" required>
                    <label for="billing_phone">Phone:</label>
                    <input type="text" id="billing_phone" name="billing_phone" required>
                    <input type="hidden" id="client_type" name="client_type" value="">
                </form>
                <button type="button" id="update-order-client" class="ml_add_loading"><?php echo esc_html__('Update Info', 'hello-elementor'); ?></button>
            </div>

            <div class="createOrder_orderType">
                <select name="order_type" id="order_type">
                    <option value="">Select Order Type</option>
                    <option value="personal">Personal</option>
                    <option value="company">Company</option>
                </select>
            </div>

            <!-- Shipping Method Select (optional) -->
            <div class="shipping-method">
                <select id="shipping_method" name="shipping_method">
                    <option value="">Select Shipping Option</option>
                    <option value="flat_rate">
                        שליח עד הבית לכל הארץ (3-5 ימי עסקים)</option>
                    <option value="free_shipping">משלוח
                        חינם ע"י שליח לכל הארץ בקניה מעל 500 ש"ח!</option>
                    <option value="local_pickup">איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!</option>
                </select>
            </div>

            <div class="content-cart">
                <h4>Cart</h4>
                <div class="empty-cart-message">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cart-large-minimalistic.svg" alt="Empty Cart">
                    <p class="empty-cart-text"><?php echo esc_html__('Your cart is empty!', 'hello-elementor'); ?></p>
                </div>
                <ul>
                    <!-- Cart items will be added here -->
                </ul>
                <div class="shipping-total">
                    <span>Shipping: <span class="shipping-total-number">0.00</span>₪</span>
                </div>
                <div class="cart-total">
                    <span>Total: <span class="cart-total-number">0.00</span>₪</span>
                </div>
                <button id="checkout" disabled>Checkout</button>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>