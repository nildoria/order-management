<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_VERSION', '2.4.2');

if (!isset($content_width)) {
    $content_width = 800; // Pixels.
}

if (!function_exists('hello_elementor_setup')) {
    /**
     * Set up theme support.
     *
     * @return void
     */
    function hello_elementor_setup()
    {
        if (is_admin()) {
            hello_maybe_update_theme_version_in_db();
        }

        $hook_result = apply_filters_deprecated('elementor_hello_theme_load_textdomain', [true], '2.0', 'hello_elementor_load_textdomain');
        if (apply_filters('hello_elementor_load_textdomain', $hook_result)) {
            load_theme_textdomain('hello-elementor', get_template_directory() . '/languages');
        }

        $hook_result = apply_filters_deprecated('elementor_hello_theme_register_menus', [true], '2.0', 'hello_elementor_register_menus');
        if (apply_filters('hello_elementor_register_menus', $hook_result)) {
            register_nav_menus(['menu-1' => __('Header', 'hello-elementor')]);
            register_nav_menus(['menu-2' => __('Footer', 'hello-elementor')]);
        }

        $hook_result = apply_filters_deprecated('elementor_hello_theme_add_theme_support', [true], '2.0', 'hello_elementor_add_theme_support');
        if (apply_filters('hello_elementor_add_theme_support', $hook_result)) {
            add_theme_support('post-thumbnails');
            add_theme_support('automatic-feed-links');
            add_theme_support('title-tag');
            add_theme_support(
                'html5',
                [
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
                ]
            );
            add_theme_support(
                'custom-logo',
                [
                    'height' => 100,
                    'width' => 350,
                    'flex-height' => true,
                    'flex-width' => true,
                ]
            );

            /*
             * Editor Style.
             */
            add_editor_style('classic-editor.css');

            /*
             * Gutenberg wide images.
             */
            add_theme_support('align-wide');

            /*
             * WooCommerce.
             */
            $hook_result = apply_filters_deprecated('elementor_hello_theme_add_woocommerce_support', [true], '2.0', 'hello_elementor_add_woocommerce_support');
            if (apply_filters('hello_elementor_add_woocommerce_support', $hook_result)) {
                // WooCommerce in general.
                add_theme_support('woocommerce');
                // Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
                // zoom.
                add_theme_support('wc-product-gallery-zoom');
                // lightbox.
                add_theme_support('wc-product-gallery-lightbox');
                // swipe.
                add_theme_support('wc-product-gallery-slider');
            }
        }

        add_image_size('related_thumb', 400, 270, true);
        add_image_size('blog_thumb', 450, 250, true);

        flush_rewrite_rules();
    }
}
add_action('after_setup_theme', 'hello_elementor_setup');

add_action('admin_enqueue_scripts', 'alarnd_enqueue_admin_script');
function alarnd_enqueue_admin_script()
{
    wp_enqueue_style(
        'alarnd--admin',
        get_template_directory_uri() . '/assets/css/order.css',
        [],
        HELLO_ELEMENTOR_VERSION
    );
}

function hello_maybe_update_theme_version_in_db()
{
    $theme_version_option_name = 'hello_theme_version';
    // The theme version saved in the database.
    $hello_theme_db_version = get_option($theme_version_option_name);

    // If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
    if (!$hello_theme_db_version || version_compare($hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<')) {
        update_option($theme_version_option_name, HELLO_ELEMENTOR_VERSION);
    }
}


if (!function_exists('hello_elementor_admin_scripts_styles')) {
    /**
     * Admin Scripts & Styles.
     *
     * @return void
     */
    function hello_elementor_admin_scripts_styles()
    {
        wp_enqueue_style(
            'hello-elementor-admin-style',
            get_template_directory_uri() . '/assets/css/admin-style.css',
            [],
            HELLO_ELEMENTOR_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'hello_elementor_admin_scripts_styles');


if (!function_exists('hello_elementor_scripts_styles')) {
    /**
     * Theme Scripts & Styles.
     *
     * @return void
     */
    function hello_elementor_scripts_styles()
    {
        global $post;

        $enqueue_basic_style = apply_filters_deprecated('elementor_hello_theme_enqueue_style', [true], '2.0', 'hello_elementor_enqueue_style');
        $min_suffix = '';

        if (apply_filters('hello_elementor_enqueue_style', $enqueue_basic_style)) {
            wp_enqueue_style(
                'hello-elementor',
                get_template_directory_uri() . '/style' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        if (apply_filters('hello_elementor_enqueue_theme_style', true)) {
            wp_enqueue_style(
                'hello-elementor-theme-style',
                get_template_directory_uri() . '/theme' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        wp_enqueue_style(
            'magnifyzoom',
            get_template_directory_uri() . '/assets/css/magnify.css',
            [],
            HELLO_ELEMENTOR_VERSION
        );
        wp_enqueue_style(
            'allaround-magnific',
            get_template_directory_uri() . '/assets/css/magnific-popup.css',
            [],
            HELLO_ELEMENTOR_VERSION
        );
        wp_enqueue_style(
            'slick-css',
            get_template_directory_uri() . '/assets/css/slick-carousal.css',
            [],
            HELLO_ELEMENTOR_VERSION
        );
        wp_enqueue_style(
            'toastify-css',
            'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css',
            [],
            HELLO_ELEMENTOR_VERSION
        );
        wp_enqueue_style(
            'allaround-style',
            get_template_directory_uri() . '/assets/css/style.css',
            [],
            filemtime(get_theme_file_path('/assets/css/style.css'))
        );

        wp_enqueue_script('magnifyzoom', get_template_directory_uri() . '/assets/js/jquery.magnify.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('magnific-popup', get_template_directory_uri() . '/assets/js/jquery.magnific-popup.min.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('slick-js', get_template_directory_uri() . '/assets/js/slick.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('allaround-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), filemtime(get_theme_file_path('/assets/js/main.js')), true);

        $post_id = is_singular('post') && isset($post) ? $post->ID : null;

        if ($post_id) {
            $order_id = esc_attr(get_post_meta($post_id, 'order_id', true));
            $order_number = esc_attr(get_post_meta($post_id, 'order_number', true));
            $order_domain = esc_url(get_post_meta($post_id, 'site_url', true));
            $billing = get_post_meta($post_id, 'billing', true);
            $firstName = isset($billing['first_name']) ? esc_attr($billing['first_name']) : '';
            $lastName = isset($billing['last_name']) ? esc_attr($billing['last_name']) : '';
            $email = isset($billing['email']) ? esc_attr($billing['email']) : '';
            $customer_name = esc_html($firstName) . ' ' . esc_html($lastName);
            $customer_email = esc_attr($email);
        } else {
            $order_id = '';
            $order_number = '';
            $customer_name = '';
            $customer_email = '';
            $order_domain = '';
        }
        // Set default order_domain if not set
        if (empty($order_domain)) {
            $order_domain = 'https://main.lukpaluk.xyz';
        }

        // print $billing in error_log without comment slash
        // error_log(print_r($billing, true));

        wp_localize_script(
            'allaround-main',
            'allaround_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'admin_email' => get_bloginfo('admin_email'),
                'nonce' => wp_create_nonce("order_management_nonce"),
                'assets' => get_template_directory_uri() . '/assets/',
                'fileupload_url' => get_template_directory_uri() . '/upload.php',
                'redirecturl' => home_url(),
                'post_id' => $post_id,
                'order_id' => $order_id,
                'order_number' => $order_number,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'order_domain' => $order_domain,
                'currency_symbol' => "₪",
            )
        );

    }
}
add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');

if (!function_exists('hello_elementor_register_elementor_locations')) {
    /**
     * Register Elementor Locations.
     *
     * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
     *
     * @return void
     */
    function hello_elementor_register_elementor_locations($elementor_theme_manager)
    {
        $hook_result = apply_filters_deprecated('elementor_hello_theme_register_elementor_locations', [true], '2.0', 'hello_elementor_register_elementor_locations');
        if (apply_filters('hello_elementor_register_elementor_locations', $hook_result)) {
            $elementor_theme_manager->register_all_core_location();
        }
    }
}
add_action('elementor/theme/register_locations', 'hello_elementor_register_elementor_locations');

if (!function_exists('hello_elementor_content_width')) {
    /**
     * Set default content width.
     *
     * @return void
     */
    function hello_elementor_content_width()
    {
        $GLOBALS['content_width'] = apply_filters('hello_elementor_content_width', 800);
    }
}
add_action('after_setup_theme', 'hello_elementor_content_width', 0);

if (is_admin()) {
    require get_template_directory() . '/includes/admin-functions.php';
}

/**
 * If Elementor is installed and active, we can load the Elementor-specific Settings & Features
 */

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';
require get_template_directory() . '/includes/classes/class-rules.php';
require get_template_directory() . '/includes/classes/class-utility.php';
// require get_template_directory() . '/includes/classes/class-ajax.php';

/**
 * Include customizer registration functions
 */
function hello_register_customizer_functions()
{
    if (hello_header_footer_experiment_active() && is_customize_preview()) {
        require get_template_directory() . '/includes/customizer-functions.php';
    }
}
add_action('init', 'hello_register_customizer_functions');

if (!function_exists('hello_elementor_check_hide_title')) {
    /**
     * Check hide title.
     *
     * @param bool $val default value.
     *
     * @return bool
     */
    function hello_elementor_check_hide_title($val)
    {
        if (defined('ELEMENTOR_VERSION')) {
            $current_doc = Elementor\Plugin::instance()->documents->get(get_the_ID());
            if ($current_doc && 'yes' === $current_doc->get_settings('hide_title')) {
                $val = false;
            }
        }
        return $val;
    }
}
add_filter('hello_elementor_page_title', 'hello_elementor_check_hide_title');

/**
 * Wrapper function to deal with backwards compatibility.
 */
if (!function_exists('hello_elementor_body_open')) {
    function hello_elementor_body_open()
    {
        if (function_exists('wp_body_open')) {
            wp_body_open();
        } else {
            do_action('wp_body_open');
        }
    }
}

function redirect_to_login_if_not_logged_in()
{
    if (!is_user_logged_in() && is_single()) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'redirect_to_login_if_not_logged_in');


function reset_user_contributor_transient($user_id)
{
    delete_transient('user_is_contributor_' . $user_id);
}

add_action('wp_login', 'reset_user_contributor_transient');
add_action('wp_logout', 'reset_user_contributor_transient');
add_action('set_user_role', 'reset_user_contributor_transient');
add_action('profile_update', 'reset_user_contributor_transient');


function change_post_type_labels()
{
    global $wp_post_types;

    // Get the post type object
    $post_type = 'post';
    $post_type_object = $wp_post_types[$post_type];

    // Define new labels
    $labels = array(
        'name' => _x('Orders', 'post type general name', 'your-textdomain'),
        'singular_name' => _x('Order', 'post type singular name', 'your-textdomain'),
        'menu_name' => _x('Orders', 'admin menu', 'your-textdomain'),
        'name_admin_bar' => _x('Order', 'add new on admin bar', 'your-textdomain'),
        'add_new' => _x('Add New', 'order', 'your-textdomain'),
        'add_new_item' => __('Add New Order', 'your-textdomain'),
        'new_item' => __('New Order', 'your-textdomain'),
        'edit_item' => __('Edit Order', 'your-textdomain'),
        'view_item' => __('View Order', 'your-textdomain'),
        'all_items' => __('All Orders', 'your-textdomain'),
        'search_items' => __('Search Orders', 'your-textdomain'),
        'parent_item_colon' => __('Parent Orders:', 'your-textdomain'),
        'not_found' => __('No orders found.', 'your-textdomain'),
        'not_found_in_trash' => __('No orders found in Trash.', 'your-textdomain')
    );

    // Update the labels
    $post_type_object->labels = (object) array_merge((array) $post_type_object->labels, $labels);
}
add_action('init', 'change_post_type_labels');


/**
 * Order Management Functionality Begines.
 *
 * 
 */
// Handle AJAX request
function update_order_shipping_method()
{
    // Verify nonce
    check_ajax_referer('order_management_nonce', 'nonce');

    $order_id = intval($_POST['order_id']);
    $shipping_method = sanitize_text_field($_POST['shipping_method']);
    $shipping_method_title = sanitize_text_field($_POST['shipping_method_title']);
    $domain = esc_url($_POST['order_domain']);
    //TODO: This is for local testing only and for staging
    if ($domain === 'https://main.lukpaluk.xyz') {
        $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
        $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
    } elseif ($domain === 'https://allaround.test') {
        $consumer_key = 'ck_481effc1659aae451f1b6a2e4f2adc3f7bc3829f';
        $consumer_secret = 'cs_b0af5f272796d15581feb8ed52fbf0d5469c67b4';
    } else {
        $domain = 'https://main.lukpaluk.xyz';
        $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
        $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
    }

    $api_url = $domain . '/wp-json/wc/v3/orders/' . $order_id;

    $response = wp_remote_get(
        $api_url,
        array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
            ),
            'sslverify' => false
        )
    );

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    } else {
        $order = json_decode(wp_remote_retrieve_body($response));

        if ($order && !isset($order->message)) {
            // Calculate shipping cost based on method ID
            $shipping_total = calculate_shipping_cost($shipping_method);

            // Update the first shipping line
            if (count($order->shipping_lines) > 0) {
                $order->shipping_lines[0]->method_id = $shipping_method;
                $order->shipping_lines[0]->method_title = $shipping_method_title;
                $order->shipping_lines[0]->total = strval($shipping_total);
            }

            // Prepare data for updating the shipping method
            $data = array(
                'shipping_lines' => $order->shipping_lines
            );

            $update_response = wp_remote_post(
                $api_url,
                array(
                    'method' => 'PUT',
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret),
                        'Content-Type' => 'application/json'
                    ),
                    'body' => json_encode($data),
                    'sslverify' => false
                )
            );

            if (is_wp_error($update_response)) {
                wp_send_json_error($update_response->get_error_message());
            } else {
                $post_id = find_post_id_by_order_id($order_id);
                update_post_meta($post_id, 'shipping_method', sanitize_text_field($shipping_method));

                // Delete the transient to reset cached data
                $transient_key = 'order_details_' . $order_id;
                delete_transient($transient_key);

                wp_send_json_success('Shipping method updated successfully.');
            }
        } else {
            wp_send_json_error('Order not found.');
        }
    }
}
add_action('wp_ajax_update_shipping_method', 'update_order_shipping_method');
add_action('wp_ajax_nopriv_update_shipping_method', 'update_order_shipping_method');

// Helper function to calculate shipping cost based on method ID
function calculate_shipping_cost($shipping_method_id)
{
    switch ($shipping_method_id) {
        case 'flat_rate':
            return 29.00;
        case 'free_shipping':
            return 0.00;
        case 'local_pickup':
            return 0.00;
        default:
            return 0.00;
    }
}
// Helper function to get the currency symbol
function get_currency_symbol($currency)
{
    $symbols = array(
        'ILS' => '₪',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        // Add more currencies and their symbols as needed
    );

    return isset($symbols[$currency]) ? $symbols[$currency] : $currency;
}

function find_post_id_by_order_id($order_id)
{
    // Query for the post with the given order number
    $query = new WP_Query(
        array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'order_id',
                    'value' => $order_id,
                ),
            ),
        )
    );

    // Check if the post was found
    if ($query->have_posts()) {
        $post = $query->posts[0];
        return $post->ID;
    } else {
        return false;
    }
}
// Write a rest API endpoint function to get order data from make.com http request and create a new post in wordpress
add_action('rest_api_init', 'order_management_api');
function order_management_api()
{
    register_rest_route(
        'manage-order/v1',
        'create',
        array(
            'methods' => 'POST',
            'callback' => 'create_order',
            'permission_callback' => function () {
                return true;
            }
        )
    );
}


function add_custom_order_metabox()
{
    add_meta_box(
        'order_details_metabox',
        'Order Details',
        'order_details_metabox_content',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_custom_order_metabox');

function order_details_metabox_content($post)
{
    wp_nonce_field('save_order_details_meta', 'order_details_nonce');

    $order_status = get_post_meta($post->ID, 'order_status', true);
    $order_number = get_post_meta($post->ID, 'order_number', true);
    $order_id = get_post_meta($post->ID, 'order_id', true);
    $shipping_method = get_post_meta($post->ID, 'shipping_method', true);
    $items = get_post_meta($post->ID, 'items', true);
    $billing = get_post_meta($post->ID, 'billing', true);
    $shipping = get_post_meta($post->ID, 'shipping', true);
    $payment_method = get_post_meta($post->ID, 'payment_method', true);
    $payment_method_title = get_post_meta($post->ID, 'payment_method_title', true);
    $order_site_url = get_post_meta($post->ID, 'site_url', true);


    echo '<label for="order_number">Order ID:</label>';
    echo '<input type="text" id="order_number" name="order_number" value="' . esc_attr($order_id) . '" /><br>';

    echo '<label for="order_number">Order Number:</label>';
    echo '<input type="text" id="order_number" name="order_number" value="' . esc_attr($order_number) . '" /><br>';

    echo '<label for="order_status">Order Status:</label>';
    echo '<input type="text" readonly id="order_status" name="order_status" value="' . esc_attr($order_status) . '" /><br>';

    echo '<label for="shipping_method">Shipping Method:</label>';
    echo '<input type="text" readonly id="shipping_method" name="shipping_method" value="' . esc_attr($shipping_method) . '" /><br>';

    // Display Ordered Items
    echo '<h3>Ordered Items</h3>';
    if (!empty($items)) {
        foreach ($items as $index => $item) {
            echo '<div class="item-group">';
            echo '<label>Product ID:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['product_id']) . '" /><br>';
            echo '<label>Product Name:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['product_name']) . '" /><br>';
            echo '<label>Quantity:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['quantity']) . '" /><br>';
            echo '<label>Total:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['total']) . '" /><br>';
            echo '</div><hr>';
        }
    } else {
        echo '<p>No items found.</p>';
    }

    // Display Billing Fields
    echo '<h3>Billing Details</h3>';
    foreach ($billing as $key => $value) {
        echo '<label for="billing_' . esc_attr($key) . '">' . ucfirst(str_replace('_', ' ', $key)) . ':</label>';
        echo '<input type="text" readonly id="billing_' . esc_attr($key) . '" name="billing[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" /><br>';
    }

    // Display Shipping Fields
    echo '<h3>Shipping Details</h3>';
    foreach ($shipping as $key => $value) {
        echo '<label for="shipping_' . esc_attr($key) . '">' . ucfirst(str_replace('_', ' ', $key)) . ':</label>';
        echo '<input type="text" readonly id="shipping_' . esc_attr($key) . '" name="shipping[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" /><br>';
    }

    // Display Payment Method
    echo '<h3>Payment Method</h3>';
    echo '<label for="payment_method">Payment Method:</label>';
    echo '<input type="text" readonly id="payment_method" name="payment_method" value="' . esc_attr($payment_method) . '" /><br>';

    echo '<label for="payment_method_title">Payment Method Title:</label>';
    echo '<input type="text" readonly id="payment_method_title" name="payment_method_title" value="' . esc_attr($payment_method_title) . '" /><br>';

    // Site URL
    echo '<label for="order_site_url">Order Site URL:</label>';
    echo '<input type="text" id="order_site_url" name="order_site_url" value="' . esc_attr($order_site_url) . '" />';
}

function save_order_details_meta($post_id)
{
    if (!isset($_POST['order_details_nonce']) || !wp_verify_nonce($_POST['order_details_nonce'], 'save_order_details_meta')) {
        return $post_id;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['order_id'])) {
        update_post_meta($post_id, 'order_id', sanitize_text_field($_POST['order_id']));
    }

    if (isset($_POST['order_number'])) {
        update_post_meta($post_id, 'order_number', sanitize_text_field($_POST['order_number']));
    }

    if (isset($_POST['order_status'])) {
        update_post_meta($post_id, 'order_status', sanitize_text_field($_POST['order_status']));
    }

    if (isset($_POST['shipping_method'])) {
        update_post_meta($post_id, 'shipping_method', sanitize_text_field($_POST['shipping_method']));
    }

    if (isset($_POST['items'])) {
        update_post_meta($post_id, 'items', $_POST['items']);  // Assuming items are properly sanitized before storing
    }

    if (isset($_POST['billing'])) {
        update_post_meta($post_id, 'billing', sanitize_textarea_field($_POST['billing']));
    }

    if (isset($_POST['shipping'])) {
        update_post_meta($post_id, 'shipping', sanitize_textarea_field($_POST['shipping']));
    }

    if (isset($_POST['payment_method'])) {
        update_post_meta($post_id, 'payment_method', sanitize_text_field($_POST['payment_method']));
    }

    if (isset($_POST['payment_method_title'])) {
        update_post_meta($post_id, 'payment_method_title', sanitize_text_field($_POST['payment_method_title']));
    }

    if (isset($_POST['site_url'])) {
        update_post_meta($post_id, 'site_url', sanitize_text_field($_POST['site_url']));
    }
}
add_action('save_post', 'save_order_details_meta');



// Modify post permalink to uniqueID before inserting
add_filter('wp_insert_post_data', 'manage_order_permalink_on_creation', 10, 2);

function manage_order_permalink_on_creation($data, $postarr)
{
    if ($data['post_type'] == 'post') {
        $random_string = substr(md5(uniqid(mt_rand(), true)), 0, 12);

        $data['post_name'] .= '-' . $random_string;
    }

    return $data;
}

function create_order(WP_REST_Request $request)
{
    // Get the order data from the request
    $order_data = $request->get_json_params();

    // error_log(print_r($order_data, true));

    $order_number = str_replace(' ', '', sanitize_text_field($order_data['order_number']));
    $order_id = str_replace(' ', '', sanitize_text_field($order_data['order_id']));

    $shipping_method_id = '';
    if (!empty($order_data['shipping_lines']) && is_array($order_data['shipping_lines'])) {
        foreach ($order_data['shipping_lines'] as $shipping_line) {
            if (isset($shipping_line['method_id'])) {
                $shipping_method_id = $shipping_line['method_id'];
                break; // Assuming you want the first shipping method ID
            }
        }
    }

    $post_title = '#' . $order_number;

    // Create a new post with the order data
    $post_id = wp_insert_post(
        array(
            'post_title' => $post_title, // Set the post title to the order number
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'post',
        )
    );

    // Check if post was created successfully
    if ($post_id == 0) {
        return new WP_Error('post_not_created', 'There was an error creating the post.', array('status' => 500));
    }

    // Save the order status to post meta
    update_post_meta($post_id, 'order_status', 'New Order');
    update_post_meta($post_id, 'order_id', $order_id);
    update_post_meta($post_id, 'order_number', $order_number);
    update_post_meta($post_id, 'shipping_method', $shipping_method_id);
    update_post_meta($post_id, 'items', isset( $order_data['items'] ) ? $order_data['items'] : []);
    update_post_meta($post_id, 'billing', $order_data['billing']);
    update_post_meta($post_id, 'shipping', $order_data['shipping']);
    update_post_meta($post_id, 'payment_method', $order_data['payment_method']);
    update_post_meta($post_id, 'payment_method_title', $order_data['payment_method_title']);
    update_post_meta($post_id, 'site_url', $order_data['site_url']);

    do_action( 'all_around_create_client', $post_id, $order_data, $order_id, $order_number );

    // Return the ID of the new post
    return new WP_REST_Response($post_id, 200);
}

function add_cors_http_header()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}
add_action('init', 'add_cors_http_header');


/**
 * Order Management Order List.
 *
 */
function fetch_display_order_details($order_id, $domain, $post_id = null)
{
    $transient_key = 'order_details_' . $order_id;
    $order_data = get_transient($transient_key);

    if (false === $order_data) {
        error_log("Fetching new order details for: $order_id");

        if ($domain === 'https://main.lukpaluk.xyz') {
            $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
            $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
        } elseif ($domain === 'https://allaround.test') {
            $consumer_key = 'ck_481effc1659aae451f1b6a2e4f2adc3f7bc3829f';
            $consumer_secret = 'cs_b0af5f272796d15581feb8ed52fbf0d5469c67b4';
        } else {
            $domain = 'https://main.lukpaluk.xyz';
            $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
            $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
        }

        $order_url = $domain . '/wp-json/wc/v3/orders/' . $order_id;
        $max_retries = 3;
        $attempt = 0;
        $timeout = 20;
        $order_response = null;

        while ($attempt < $max_retries) {
            $order_response = wp_remote_get(
                $order_url,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
                    ),
                    'timeout' => $timeout,
                    'sslverify' => false
                )
            );

            if (!is_wp_error($order_response)) {
                break;
            }

            error_log("Attempt $attempt failed: " . $order_response->get_error_message());
            $attempt++;
        }

        if (is_wp_error($order_response)) {
            return 'Something went wrong: ' . $order_response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body($order_response);
        $order = json_decode($response_body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return 'Failed to parse order data.';
        }

        if (!$order || !isset($order->line_items) || !is_array($order->line_items)) {
            return 'No items found for this order.';
        }

        $items_subtotal = 0;
        foreach ($order->line_items as $item) {
            $items_subtotal += $item->total;
        }

        $currency_symbol = get_currency_symbol($order->currency);
        $shipping_total = 0;
        if (isset($order->shipping_lines) && is_array($order->shipping_lines)) {
            foreach ($order->shipping_lines as $shipping_line) {
                $shipping_total += $shipping_line->total;
            }
        }

        $order_data = array(
            'order' => $order,
            'items_subtotal' => $items_subtotal,
            'currency_symbol' => $currency_symbol,
            'shipping_total' => $shipping_total
        );

        set_transient($transient_key, $order_data, HOUR_IN_SECONDS);
    }

    $order = $order_data['order'];
    $items_subtotal = $order_data['items_subtotal'];
    $currency_symbol = $order_data['currency_symbol'];
    $shipping_total = $order_data['shipping_total'];

    ob_start();

    echo '<table id="tableMain">';
    echo '<thead><tr>';
    echo '<th class="head"><strong>Product</strong></th>';
    if (!ml_current_user_contributor()):
        echo '<th class="head"><strong>Quantity</strong></th>';
    endif;
    echo '<th class="head"><strong>Graphics</strong></th>';
    echo '</tr></thead><tbody>';

    foreach ($order->line_items as $item) {

        $item_id = esc_attr($item->id);
        $product_id = esc_attr($item->product_id);

        echo '<tr class="alt" id="' . esc_attr($item_id) . '" data-product_id="' . esc_attr($item_id) . '" data-source_product_id="' . esc_attr($product_id) . '">';
        echo '<td class="item_product_column">';
        if (!ml_current_user_contributor()):
            echo '<span class="om_duplicate_item"><img src="' . get_template_directory_uri() . '/assets/images/copy.png" alt="Copy" /></span>';
            echo '<span class="om_delete_item"><img src="' . get_template_directory_uri() . '/assets/images/delete.png" alt="Delete" /></span>';
            echo '<span class="om__editItemMeta" data-item_id="' . $item_id . '"><img src="' . get_template_directory_uri() . '/assets/images/pen.png" alt="Delete" /></span>';
        endif;
        if (isset($item->id)) {
            echo '<input type="hidden" name="item_id" value="' . esc_attr($item_id) . '">';
        }
        if (isset($item->image->src)) {
            $thumbnail_url = $item->image->src;
            echo '<span class="om_item_thumb_cont"><img width="100" src="' . esc_url($thumbnail_url) . '" /></span>';
        }
        echo '<span class="item_name_variations">';
        echo '<strong class="product_item_title">' . esc_html($item->name) . '</strong>';
        echo '<ul>';
        foreach ($item->meta_data as $meta) {
            if (in_array($meta->key, ["קובץ מצורף", "Attachment", "Additional Attachment", "_allaround_artwork_id", "_allaround_art_pos_key"])) {
                continue;
            }
            echo '<li data-meta_key="' . esc_html($meta->key) . '">' . esc_html($meta->key) . ': ' . esc_html(strip_tags($meta->value)) . '</li>';
        }
        echo '</ul>';
        echo '</span>';
        echo '<span data-source_product_id="' . esc_attr($product_id) . '" id="om__itemVariUpdateModal_' . $item_id . '" class="mfp-hide om__itemVariUpdateModal">';
        echo '<strong class="om__itemVariUpdateTitle">' . esc_html($item->name) . '</strong>';
        echo '<span class="om__itemVariUpdateMeta">';
        foreach ($item->meta_data as $meta) {
            if (in_array($meta->key, ["קובץ מצורף", "Attachment", "Additional Attachment", "_allaround_artwork_id", "_allaround_art_pos_key"])) {
                continue;
            }
            if (in_array($meta->key, ["Color"])) {
                echo '<label for="color-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="color-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';
            }
            if (in_array($meta->key, ["Size"])) {
                echo '<label for="size-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="size-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';

            }
            if (in_array($meta->key, ["Art Position"])) {
                echo '<label for="art-position-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="art-position-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';
            }
            if (in_array($meta->key, ["Instruction Note"])) {
                echo '<label for="instruction-note-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<input type="text" id="instruction-note-input_' . $item_id . '" value="' . esc_html(strip_tags($meta->value)) . '" placeholder="Enter instruction note">';
            }
        }
        echo '</span>';
        echo '<button data-order_id="' . $order_id . '" data-item_id="' . $item_id . '" id="update-item-meta-btn_' . $item_id . '">Update Item Meta</button>';
        echo '</span>';
        echo '</td>';
        if (!ml_current_user_contributor()):
            echo '<td class="item_quantity_column">';
            echo '<span class="om__itemQuantity">' . esc_attr($item->quantity) . '</span>x';
            echo '<span class="om__itemRate">' . esc_attr(number_format($item->price, 2) . $currency_symbol) . '</span> = ';
            echo '<span class="om__itemCostTotal">' . esc_attr(number_format($item->total, 2) . $currency_symbol) . '</span>';
            echo '<span class="om_itemQuantPriceEdit">';
            echo '<input type="number" class="item-quantity-input" data-item-id="' . esc_attr($item->id) . '" value="' . esc_attr($item->quantity) . '" />';
            echo '<input type="number" class="item-cost-input" data-item-id="' . esc_attr($item->id) . '" value="' . esc_attr($item->price) . '" />';
            echo '</span>';
            echo '</td>';
        endif;
        echo '<td class="item_graphics_column">';
        $artworkFound = false;
        foreach ($item->meta_data as $meta) {
            if (in_array($meta->key, ["קובץ מצורף", "Attachment", "Additional Attachment"])) {
                if (preg_match('/<p>(.*?)<\/p>/', $meta->value, $matches)) {
                    $filename = $matches[1];
                    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $class_name = 'file-format-' . strtolower($file_extension);
                } else {
                    $class_name = 'file-format-unknown';
                }
                $value = preg_replace('/<p>.*?<\/p>/', '', $meta->value);
                $value = '<div class="uploaded_graphics ' . esc_attr($class_name) . '">' . $value . '</div>';
                echo $value;
                $artworkFound = true;
            }
        }
        if (!$artworkFound) {
            echo 'No Artwork Attached';
        }
        echo '</td>';

        echo '</tr>';
    }
    echo '</tbody><tfoot>';
    if (!ml_current_user_contributor()):
        echo '<tr>';
        echo '<td colspan="1"><span>Items Subtotal:</span><br>';
        echo '<span>Shipping:</span><br>';
        echo '<span>Order Total:</span></td>';
        echo '<td class="totals_column">';
        echo '<span class="om__items_subtotal">' . esc_attr(number_format($items_subtotal, 2) . ' ' . $currency_symbol) . '</span><br>';
        echo '<span class="om__shipping_total">' . esc_attr(number_format($shipping_total, 2) . ' ' . $currency_symbol) . '</span><br>';
        echo '<span class="om__orderTotal">' . esc_attr(number_format($order->total, 2) . ' ' . $currency_symbol) . '</span>';
        echo '</td>';
        echo '</tr>';
    endif;
    echo '</tfoot></table>';
    echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '">';
    echo '<input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">';

    return ob_get_clean();
}

add_action('wp_ajax_initialize_mockup_columns', 'initialize_mockup_columns');
add_action('wp_ajax_nopriv_initialize_mockup_columns', 'initialize_mockup_columns');

function initialize_mockup_columns()
{
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);

    $mockup_versions = [];
    $mockup_version = 1;
    while (true) {
        $file_list = get_file_list_from_ftp($order_id, $product_id, 'V' . $mockup_version);
        if (!empty($file_list)) {
            $mockup_versions[] = ['version' => $mockup_version];
            $mockup_version++;
        } else {
            break;
        }
    }

    wp_send_json_success(['mockup_versions' => $mockup_versions]);
}

add_action('wp_ajax_fetch_mockup_files', 'fetch_mockup_files');
add_action('wp_ajax_nopriv_fetch_mockup_files', 'fetch_mockup_files');

function fetch_mockup_files()
{
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $version = sanitize_text_field($_POST['version']);

    $attempts = 0;
    $max_attempts = 3;
    $success = false;

    while ($attempts < $max_attempts && !$success) {
        $file_list = get_file_list_from_ftp($order_id, $product_id, 'V' . $version);

        // Ensure $file_list is always an array
        if (!is_array($file_list)) {
            $file_list = $file_list ? [$file_list] : []; // Wrap single file object in an array or set as empty array if null
        }

        if (!empty($file_list)) {
            $file_list = array_map(function ($file) {
                return str_replace('/public_html', 'https://lukpaluk.xyz', $file);
            }, $file_list);
            wp_send_json_success(['file_list' => $file_list]); // Success case
            $success = true; // Break the loop on success
        } else {
            $attempts++;
            if ($attempts >= $max_attempts) {
                // Modified error response to include an empty 'file_list' and a 'message'
                wp_send_json_error(['file_list' => [], 'message' => 'No files found after ' . $max_attempts . ' attempts']);
            }
        }
    }
}



function get_file_list_from_ftp($order_id, $product_id, $version)
{
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';
    $remote_directory = "/public_html/artworks/$order_id/$product_id/$version/";

    // Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");

    // Login to FTP server
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        ftp_close($ftp_conn);
        die("Could not log in to FTP server");
    }

    // Enable passive mode
    ftp_pasv($ftp_conn, true);

    // Get the file list
    $file_list = ftp_nlist($ftp_conn, $remote_directory);

    // Close the FTP connection
    ftp_close($ftp_conn);

    // Handle the case where the directory might not exist or no files are found
    if ($file_list === false) {
        return array();
    }

    // Filter out "." and ".." entries
    $file_list = array_filter($file_list, function ($file) {
        return !in_array(basename($file), ['.', '..']);
    });

    return $file_list;
}

function mlCheckImgUrl($url)
{
    // Suppress warnings in case the URL is invalid
    $headers = @get_headers($url, 1);

    // Check if headers were fetched successfully
    if ($headers && isset($headers[0])) {
        // Check if the HTTP status code is 200 (OK)
        if (strpos($headers[0], '200') !== false) {
            return true;
        }
    }
    return false;
}


add_action('wp_ajax_update_order_transient', 'update_order_transient_cb');
function update_order_transient_cb()
{

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if (empty($order_id)) {
        wp_send_json_error(
            array(
                "message" => "OrderId is empty on request from update_order_transient."
            )
        );
        wp_die();
    }

    $transient_key = 'order_details_' . $order_id;
    delete_transient($transient_key);

    wp_send_json_success(
        array(
            "message" => 'order_details_' . $order_id . ' deleted. Request from update_order_transient.'
        )
    );

    wp_die();
}


add_action('wp_ajax_send_proof_version', 'send_proof_version_cb');
function send_proof_version_cb()
{

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $version = isset($_POST['version']) ? intval($_POST['version']) : 0;

    if (empty($version) || empty($post_id)) {
        wp_send_json_error(
            array(
                "message" => "post_id Or version is empty on request from send_proof_version."
            )
        );
        wp_die();
    }

    update_post_meta($post_id, 'send_proof_last_version', $version);

    wp_send_json_success(
        array(
            "message" => "send_proof_last_version updated with $version for post:$post_id"
        )
    );

    wp_die();
}

function ml_current_user_contributor()
{
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return false;
    }

    // Get the current user
    $user = wp_get_current_user();

    // Define transient key
    $transient_key = 'user_is_contributor_' . $user->ID;

    // Check if the transient exists
    $is_contributor = get_transient($transient_key);

    // If the transient does not exist, calculate and set it
    if ($is_contributor === false) {
        $is_contributor = in_array('contributor', (array) $user->roles) ? '1' : '0';
        set_transient($transient_key, $is_contributor, HOUR_IN_SECONDS);
    }

    // Return the result
    return $is_contributor === '1';
}

/**
 * Handle form submission to create an order
 */
add_action('wp_ajax_create_order', 'create_order_from_form');
add_action('wp_ajax_nopriv_create_order', 'create_order_from_form');

function create_order_from_form()
{
    check_ajax_referer('order_management_nonce', 'security');

    // Get the current site URL
    $site_url = site_url();

    //TODO: This is for local testing only and for staging
    // Set domain and credentials based on the site URL
    if (strpos($site_url, '.test') !== false) {
        $domain = 'https://allaround.test';
        $consumer_key = 'ck_481effc1659aae451f1b6a2e4f2adc3f7bc3829f';
        $consumer_secret = 'cs_b0af5f272796d15581feb8ed52fbf0d5469c67b4';
    } else {
        $domain = 'https://main.lukpaluk.xyz';
        $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
        $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
    }

    $billing = array(
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'address_1' => sanitize_text_field($_POST['address_1']),
        'address_2' => '',
        'company' => sanitize_text_field($_POST['company']),
        'city' => sanitize_text_field($_POST['city']),
        'country' => !empty($_POST['country']) ? sanitize_text_field($_POST['country']) : 'Israel',
        'email' => sanitize_email($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
    );
    $shipping = $billing;
    $line_items = json_decode(stripslashes($_POST['line_items']), true);
    $shipping_method = sanitize_text_field($_POST['shipping_method']);
    $shipping_method_title = sanitize_text_field($_POST['shipping_method_title']);

    $shipping_total = calculate_shipping_cost($shipping_method);

    foreach ($line_items as &$item) {
        foreach ($item['meta_data'] as &$meta) {
            if ($meta['key'] === 'Attachment') {
                $artwork_urls = json_decode($meta['value'], true);
                // Check if the value is an array and not empty
                if (!empty($artwork_urls) && is_array($artwork_urls)) {
                    $meta['value'] = ''; // Initialize as empty string to concatenate multiple artworks
                    foreach ($artwork_urls as $artwork_url) {
                        // Extract the file extension
                        $extension = pathinfo($artwork_url, PATHINFO_EXTENSION);
                        // Create the HTML content for each artwork with dynamic class based on file extension
                        $meta['value'] .= "<p>" . basename($artwork_url) . "</p><div class=\"uploaded_graphics file-format-" . strtolower($extension) . "\"><a href=\"" . $artwork_url . "\" target=\"_blank\"><img class=\"alarnd__artwork_img\" src=\"" . $artwork_url . "\" /></a></div>";
                    }
                } elseif (!empty($artwork_urls)) { // Handle single artwork URL (not an array)
                    // Extract the file extension for single URL
                    $extension = pathinfo($artwork_urls, PATHINFO_EXTENSION);
                    $meta['value'] = "<p>" . basename($artwork_urls) . "</p><div class=\"uploaded_graphics file-format-" . strtolower($extension) . "\"><a href=\"" . $artwork_urls . "\" target=\"_blank\"><img class=\"alarnd__artwork_img\" src=\"" . $artwork_urls . "\" /></a></div>";
                }
            }
        }
    }

    $order_data = array(
        'payment_method' => 'zcredit_checkout_payment',
        'payment_method_title' => 'Secure Credit Card Payment',
        'set_paid' => true,
        'billing' => $billing,
        'shipping' => $shipping,
        'line_items' => $line_items,
        'shipping_lines' => array(
            array(
                'method_id' => $shipping_method,
                'method_title' => $shipping_method_title,
                'total' => strval($shipping_total)
            )
        )
    );

    // Send order data to the specified domain
    $response = wp_remote_post(
        "$domain/wp-json/wc/v3/orders",
        array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("$consumer_key:$consumer_secret")
            ),
            'body' => json_encode($order_data),
            'sslverify' => false
        )
    );

    if (is_wp_error($response)) {
        wp_send_json_error('Error creating order: ' . $response->get_error_message());
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['id'])) {
            $data['order_id'] = $data['id'];
            $data['order_number'] = $data['number'];
            $data['site_url'] = $domain;
            wp_send_json_success($data);
        } else {
            wp_send_json_error('Failed to create order: ' . $data['message']);
        }
    }
}


/**
 * Delete a Mockup Version
 */
add_action('wp_ajax_delete_mockup_folder', 'delete_mockup_folder');
add_action('wp_ajax_nopriv_delete_mockup_folder', 'delete_mockup_folder');

function delete_mockup_folder()
{
    check_ajax_referer('order_management_nonce', 'security');

    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $version = sanitize_text_field($_POST['version']);

    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';

    $remote_directory = "/public_html/artworks/$order_id/$product_id/$version/";

    // Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server) or wp_send_json_error(array('message' => "Could not connect to $ftp_server"));

    // Login to FTP server
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        ftp_close($ftp_conn);
        wp_send_json_error(array('message' => "Could not log in to FTP server"));
    }

    // Enable passive mode
    ftp_pasv($ftp_conn, true);

    // Delete all files in the directory
    $file_list = ftp_nlist($ftp_conn, $remote_directory);
    // Filter out "." and ".." entries
    $file_list = array_filter($file_list, function ($file) {
        return !in_array(basename($file), ['.', '..']);
    });
    if ($file_list !== false) {
        foreach ($file_list as $file) {
            ftp_delete($ftp_conn, $file);
        }
    }

    // Delete the directory
    $delete_success = ftp_rmdir($ftp_conn, $remote_directory);

    ftp_close($ftp_conn);

    if ($delete_success) {
        wp_send_json_success(array('message' => "Successfully deleted $remote_directory"));
    } else {
        wp_send_json_error(array('message' => "Error deleting $remote_directory"));
    }
}


/**
 * Order Artwork Proof Comments.
 *
 */

// Function to fetch posts from a specific page
function fetch_artwork_posts_page($page, $per_page)
{
    $response = wp_remote_get("https://artwork.lukpaluk.xyz/wp-json/wp/v2/posts?per_page=$per_page&page=$page");

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Something went wrong: $error_message");
        return [];
    }

    $posts = json_decode(wp_remote_retrieve_body($response));

    if (empty($posts) || !is_array($posts)) {
        return [];
    }

    // Include post URLs
    foreach ($posts as &$post) {
        if (is_object($post) && isset($post->id)) {
            $post->url = get_permalink($post->id);
        } else {
            $post->url = '#'; // Default to '#' if no ID is found
        }
    }

    return $posts;
}

function fetch_artwork_post_by_id($post_id)
{
    $response = wp_remote_get("https://artwork.lukpaluk.xyz/wp-json/wp/v2/posts/$post_id");

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Something went wrong: $error_message");
        return null;
    }

    $post = json_decode(wp_remote_retrieve_body($response));
    if (isset($post->id)) {
        $post->url = get_permalink($post->id);
        return $post;
    }

    return null;
}

function fetch_display_artwork_comments($order_id, $post_id = null)
{
    $send_proof_last_version = get_post_meta($post_id, 'send_proof_last_version', true);

    // Skip fetching operations if $send_proof_last_version is empty
    if (empty($send_proof_last_version)) {
        return display_artwork_comments(false, '', [], '#');
    }

    $transient_key = 'artwork_post_' . $order_id;
    $post_id = get_transient($transient_key);

    if ($post_id) {
        // Fetch the post by ID directly
        $post = fetch_artwork_post_by_id($post_id);
        if ($post && isset($post->artwork_meta->order_number) && $post->artwork_meta->order_number === $order_id) {
            $approved_proof = $post->artwork_meta->approval_status;
            $proof_approved_time = $post->artwork_meta->proof_approved_time;
            $fetched_artwork_comments = $post->artwork_meta->artwork_comments;
            $post_url = $post->link;

            // Display the comments
            return display_artwork_comments($approved_proof, $proof_approved_time, $fetched_artwork_comments, $post_url);
        } else {
            // If post data is outdated, remove the transient
            delete_transient($transient_key);
        }
    }

    // Initialize variables
    $approved_proof = false;
    $proof_approved_time = '';
    $fetched_artwork_comments = [];
    $per_page = 20;
    $page = 1;
    $max_pages = 10;
    $post_url = '#';

    // Loop through pages to fetch all posts
    while ($page <= $max_pages) {
        $posts = fetch_artwork_posts_page($page, $per_page);

        if (empty($posts)) {
            break;
        }

        // Loop through the posts
        foreach ($posts as $post) {
            // Check if the order number matches
            if (isset($post->artwork_meta->order_number) && $post->artwork_meta->order_number === $order_id) {
                $approved_proof = $post->artwork_meta->approval_status;
                $proof_approved_time = $post->artwork_meta->proof_approved_time;
                $fetched_artwork_comments = $post->artwork_meta->artwork_comments;
                $post_url = $post->link;
                set_transient($transient_key, $post->id, 12 * HOUR_IN_SECONDS); // Cache the post ID for 12 hours
                break 2; // Exit both loops if matching order is found
            }
        }

        $page++; // Move to the next page
    }

    return display_artwork_comments($approved_proof, $proof_approved_time, $fetched_artwork_comments, $post_url);
}

function display_artwork_comments($approved_proof, $proof_approved_time, $fetched_artwork_comments, $post_url)
{
    // Start building the output
    ob_start();

    if ($post_url !== '#') {
        echo '<span class="om_artwork_url"><a href="' . esc_url($post_url) . '" target="_blank"><img src="' . get_template_directory_uri() . '/assets/images/icons8-info.svg" alt="Info Artwork" /></a></span>';
    }

    if ($approved_proof) {
        ?>
        <div class="revision-activity customer-message mockup-approved-comment">
            <div class="revision-activity-avatar">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/Favicon-2.png" />
            </div>
            <div class="revision-activity-content">
                <div class="revision-activity-title">
                    <h5>AllAround</h5>
                    <span>
                        <?php
                        if (!empty($proof_approved_time)) {
                            echo esc_html(date_i18n(get_option('date_format') . ' \ב- ' . get_option('time_format'), strtotime($proof_approved_time)));
                        }
                        ?>
                    </span>
                </div>
                <div class="revision-activity-description">
                    <span class="revision-comment-title">ההדמיות אושרו על ידי הלקוח <img
                            src="<?php echo get_template_directory_uri(); ?>/assets/images/mark_icon-svg.svg" alt=""></span>
                </div>
            </div>
        </div>
        <?php
    }

    if (empty($fetched_artwork_comments)) {
        echo '<p>No revision history available.</p>';
    } else {
        $fetched_artwork_comments = array_reverse($fetched_artwork_comments);

        foreach ($fetched_artwork_comments as $comment) {
            $comment_name = $comment->artwork_comment_name;
            $comment_text = nl2br($comment->artwork_comments_texts);
            $comment_date = '';

            if (!empty($comment->artwork_comment_date)) {
                $comment_date = date_i18n(get_option('date_format') . ' \ב- ' . get_option('time_format'), strtotime($comment->artwork_comment_date));
            }

            $image_html = '';
            if (!empty($comment->artwork_new_file)) {
                $image_html .= '<div class="artwork-new-file">';
                if (pathinfo($comment->artwork_new_file, PATHINFO_EXTENSION) == 'pdf') {
                    $image_html .= '<img src="' . get_template_directory_uri() . '/assets/images/pdf-icon.svg" alt="Placeholder">';
                } else {
                    $image_html .= '<img src="' . esc_url($comment->artwork_new_file) . '" alt="Artwork Image">';
                }
                $image_html .= '</div>';
            }

            ?>
            <div class="revision-activity <?php echo $comment_name === 'AllAround' ? 'allaround-message' : 'customer-message'; ?>">
                <div class="revision-activity-avatar">
                    <?php if ($comment_name === 'AllAround'): ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/Favicon-2.png" />
                    <?php else: ?>
                        <span><?php echo esc_html(substr($comment_name, 0, 2)); ?></span>
                    <?php endif; ?>
                </div>
                <div class="revision-activity-content">
                    <div class="revision-activity-title">
                        <h5><?php echo esc_html($comment_name); ?></h5>
                        <span><?php echo esc_html($comment_date); ?></span>
                    </div>
                    <div class="revision-activity-description">
                        <span class="revision-comment-title">
                            <?php echo $comment_name === 'AllAround' ? 'הדמיה הועלתה' : 'ההערות הבאות נוספו:'; ?>
                        </span>
                        <?php echo $image_html; ?>
                        <div><?php echo $comment_text; ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    return ob_get_clean();
}


require_once get_template_directory() . '/includes/classes/class-clients.php';