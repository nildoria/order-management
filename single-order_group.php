<?php
/**
 * Single Order Group
 */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

// Check if user is not an admin or editor
if (!is_current_user_admin()) {
    echo '<h3 class="login_require_error"><b>Access Denied</b>: You do not have permission to view this page.</h3>';
    exit;
}

if (have_posts()):
    while (have_posts()):
        the_post();
        $order_group_id = get_the_ID();
        $order_group_title = get_the_title();

        // Get saved product selections
        $selected_products_main = get_post_meta($order_group_id, '_order_group_products_main', true);
        $selected_products_sites = get_post_meta($order_group_id, '_order_group_products_sites', true);
        $selected_products_flash = get_post_meta($order_group_id, '_order_group_products_flash', true);

        $selected_products_main = is_array($selected_products_main) ? $selected_products_main : array();
        $selected_products_sites = is_array($selected_products_sites) ? $selected_products_sites : array();
        $selected_products_flash = is_array($selected_products_flash) ? $selected_products_flash : array();

        // Fetch product lists dynamically
        $alarndUtility = new Alarnd_Utility();
        $main_site_api = get_option('main_site_products_api', '');
        $mini_site_api = get_option('mini_site_products_api', '');
        $flash_sale_api = get_option('flash_sale_products_api', '');
        $products_main = $alarndUtility->fetch_products_from_api($main_site_api);
        $products_sites = $alarndUtility->fetch_products_from_api($mini_site_api);
        $products_flash = $alarndUtility->fetch_products_from_api($flash_sale_api);

        // Fetch all orders associated with the group
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'order_status',
                    'value' => 'processing',
                    'compare' => '='
                )
            )
        );

        $orders = new WP_Query($args);
        $filtered_orders = array();

        if ($orders->have_posts()) {
            while ($orders->have_posts()) {
                $orders->the_post();
                $order_id = get_the_ID();
                $items = get_post_meta($order_id, 'items', true);
                $items = maybe_unserialize($items);

                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (
                            in_array($item['product_id'], $selected_products_main) ||
                            in_array($item['product_id'], $selected_products_sites) ||
                            in_array($item['product_id'], $selected_products_flash)
                        ) {
                            $filtered_orders[] = $order_id;
                            break;
                        }
                    }
                }
            }
            wp_reset_postdata();
        }
        ?>
        <main id="om__orderGroupSingle" class="site-main" role="main">
            <?php 
            if (isset($_GET['dev'])) {
                echo '<pre>';
                print_r($selected_products_main);
                print_r($selected_products_sites);
                print_r($selected_products_flash);
                echo '</pre>';
            }
            ?>
            <h2><?php echo esc_html($order_group_title); ?></h2>

            <!-- Product Selection Options -->
            <form id="order-group-form" method="post" action="" data-post-id="<?php echo esc_attr($order_group_id); ?>">
                <?php wp_nonce_field('update_order_group_products', 'order_group_products_nonce'); ?>
                <div class="orderGroup_productList">
                    <!-- Main Site Products -->
                    <div class="orderGroup_siteItems">
                        <label for="order_group_products_main">Main Site Products:</label>
                        <select id="order_group_products_main" name="order_group_products_main[]" multiple style="width: 100%;">
                            <?php foreach ($products_main as $product): ?>
                                <option value="<?php echo esc_attr($product['id']); ?>" <?php echo in_array($product['id'], $selected_products_main) ? 'selected' : ''; ?> data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>">
                                    <?php echo esc_html($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Mini Site Products -->
                    <div class="orderGroup_siteItems">
                        <label for="order_group_products_sites">Mini Site Products:</label>
                        <select id="order_group_products_sites" name="order_group_products_sites[]" multiple
                            style="width: 100%;">
                            <?php foreach ($products_sites as $product): ?>
                                <option value="<?php echo esc_attr($product['id']); ?>" <?php echo in_array($product['id'], $selected_products_sites) ? 'selected' : ''; ?> data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>">
                                    <?php echo esc_html($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Flash Sale Products -->
                    <div class="orderGroup_siteItems">
                        <label for="order_group_products_flash">Flash Sale Products:</label>
                        <select id="order_group_products_flash" name="order_group_products_flash[]" multiple
                            style="width: 100%;">
                            <?php foreach ($products_flash as $product): ?>
                                <option value="<?php echo esc_attr($product['id']); ?>" <?php echo in_array($product['id'], $selected_products_flash) ? 'selected' : ''; ?> data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>">
                                    <?php echo esc_html($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="allarnd--regular-button ml_add_loading">Update Products</button>
            </form>

            <!-- Orders List -->
            <h4>Active Orders</h4>
            <?php if (!empty($filtered_orders)): ?>
                <table class="orderGroup_table">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Order Date</th>
                            <th>Text</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_orders as $filtered_order_id):
                            $saved_text = get_post_meta($filtered_order_id, 'order_group_text', true);
							$woo_order_number = get_post_meta($filtered_order_id, 'order_number', true);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_permalink($filtered_order_id)); ?>" target="_blank">
                                        #<?php echo esc_html($woo_order_number); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(get_the_date('d/m/Y', $filtered_order_id)); ?></td>
                                <td class="order_group_text_cell">
                                    <textarea name="order_group_text_<?php echo esc_attr($filtered_order_id); ?>"
                                        class="orderGroup_textInput" rows="2"><?php echo esc_textarea($saved_text); ?></textarea>
                                    <button class="save_order_group_text"
                                        data-order-id="<?php echo esc_attr($filtered_order_id); ?>">Save</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No active orders found.</p>
            <?php endif; ?>
        </main>
        <?php
    endwhile;
endif;

get_footer();
