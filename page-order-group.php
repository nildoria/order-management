<?php
/**
 * Template Name: Order Group
 */

ob_start(); // Start output buffering
get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

// Check if user is not an admin or editor
if (!is_current_user_admin()) {
    // Display error message
    echo '<h3 class="login_require_error"><b>Access Denied</b>: You do not have permission to view this page.</h3>';
    // Optionally, you can exit the script to prevent further execution
    exit;
}

$alarndUtility = new Alarnd_Utility();

// Fetch the dynamic API URLs from options
$main_site_api = get_option('main_site_products_api', '');
$mini_site_api = get_option('mini_site_products_api', '');
$flash_sale_api = get_option('flash_sale_products_api', '');

// Fetch products using the dynamic URLs
$products_main = $alarndUtility->fetch_products_from_api($main_site_api);
$products_sites = $alarndUtility->fetch_products_from_api($mini_site_api);
$products_flash = $alarndUtility->fetch_products_from_api($flash_sale_api);
?>
<main id="om__orderGroupPage" class="site-main" role="main">
    <h2>Order Groups</h2>
    <form id="order-group-form" method="post" action="">
        <?php wp_nonce_field('add_order_group', 'order_group_nonce'); ?>
        <div class="orderGroup_title">
            <label for="post_title">Order Group Title:</label>
            <input type="text" id="post_title" name="post_title" required>
        </div>
        <div class="orderGroup_productList">
            <div class="orderGroup_siteItems">
                <label for="order_group_products_main">Main Site Products:</label>
                <select id="order_group_products_main" name="order_group_products_main[]" multiple style="width: 100%;">
                    <?php foreach ($products_main as $product): ?>
                        <option value="<?php echo esc_attr($product['id']); ?>" data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>"><?php echo esc_html($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="orderGroup_siteItems">
                <label for="order_group_products_sites">Mini Sites Products:</label>
                <select id="order_group_products_sites" name="order_group_products_sites[]" multiple style="width: 100%;">
                    <?php foreach ($products_sites as $product): ?>
                        <option value="<?php echo esc_attr($product['id']); ?>" data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>"><?php echo esc_html($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="orderGroup_siteItems">
                <label for="order_group_products_flash">FlashSale Products:</label>
                <select id="order_group_products_flash" name="order_group_products_flash[]" multiple style="width: 100%;">
                    <?php foreach ($products_flash as $product): ?>
                        <option value="<?php echo esc_attr($product['id']); ?>" data-thumbnail="<?php echo esc_url($product['thumbnail']); ?>"><?php echo esc_html($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="orderGroup_submit">
            <button type="submit" class="allarnd--regular-button ml_add_loading">Add Order Group</button>
        </div>
    </form>

    <div class="orderGroup_postsList">
        <!-- <h4>Order Groups and Their Orders</h4> -->

        <div class="orderGroup_groupedOrders">
            <?php
            // Fetch all Order Groups
            $order_groups = new WP_Query(array(
                'post_type' => 'order_group',
                'posts_per_page' => -1,
            ));

            if ($order_groups->have_posts()): ?>
                <?php while ($order_groups->have_posts()):
                    $order_groups->the_post();

                    $order_group_id = get_the_ID();
                    $order_group_title = get_the_title();
                    $order_group_url = get_permalink();

                    // Get products assigned to this group
                    $selected_products_main = get_post_meta($order_group_id, '_order_group_products_main', true);
                    $selected_products_sites = get_post_meta($order_group_id, '_order_group_products_sites', true);
                    $selected_products_flash = get_post_meta($order_group_id, '_order_group_products_flash', true);

                    $selected_products_main = is_array($selected_products_main) ? $selected_products_main : array();
                    $selected_products_sites = is_array($selected_products_sites) ? $selected_products_sites : array();
                    $selected_products_flash = is_array($selected_products_flash) ? $selected_products_flash : array();

                    // Fetch all orders with processing status
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
                    <div class="orderGroup_section">
                        <h6>Title: <a href="<?php echo esc_url($order_group_url); ?>" target="_blank"><?php echo esc_html($order_group_title); ?></a></h6>
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
                                        // Get saved meta text for this order
                                        $saved_text = get_post_meta($filtered_order_id, 'order_group_text', true);
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo esc_url(get_permalink($filtered_order_id)); ?>" target="_blank">
                                                    #<?php echo esc_html($filtered_order_id); ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html(get_the_date('d/m/Y', $filtered_order_id)); ?></td>
                                            <td class="order_group_text_cell">
                                                <textarea name="order_group_text_<?php echo esc_attr($filtered_order_id); ?>" class="orderGroup_textInput" rows="2" placeholder="Enter text here"><?php echo esc_textarea($saved_text); ?></textarea>
                                                <button class="save_order_group_text" data-order-id="<?php echo esc_attr($filtered_order_id); ?>">Save</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No active orders found for this group.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile;
                wp_reset_postdata(); ?>
            <?php else: ?>
                <p>No order groups found.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
get_footer();
ob_end_flush(); // Flush the output buffer
?>