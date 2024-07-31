<?php
class AllAroundCreateOrder
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('admin_bar_menu', [$this, 'add_delete_transient_button'], 100);
        add_action('wp_ajax_delete_product_transient', [$this, 'delete_product_transient']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        add_action('wp_ajax_create_order_from_form', [$this, 'create_order_from_form']);
        add_action('wp_ajax_nopriv_create_order_from_form', [$this, 'create_order_from_form']);

        add_action('wp_ajax_get_client_details', [$this, 'get_client_details']);
        add_action('wp_ajax_nopriv_get_client_details', [$this, 'get_client_details']);

    }

    public function enqueue_admin_scripts()
    {
        wp_enqueue_script('delete-transient-script', get_template_directory_uri() . '/assets/js/delete-transient.js', array('jquery'), '1.0', true);
        wp_localize_script(
            'delete-transient-script',
            'deleteTransientVars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('delete_transient_nonce'),
            )
        );
    }

    public function frontend_scripts()
    {
        // Enqueue custom styles and scripts
        wp_enqueue_style('create-order-style', get_template_directory_uri() . '/assets/css/create-order.css', [], HELLO_ELEMENTOR_VERSION);

        wp_enqueue_script('create-order-script', get_template_directory_uri() . '/assets/js/create-order.js', ['jquery'], HELLO_ELEMENTOR_VERSION, true);

        // Enqueue Select2 CSS and JS
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

        // Determine the API URL based on the current domain
        $current_domain = $_SERVER['SERVER_NAME'];
        $products_api_url = strpos($current_domain, '.test') !== false ?
            'https://allaround.test/wp-json/alarnd-main/v1/products' :
            'https://main.lukpaluk.xyz/wp-json/alarnd-main/v1/products';

        wp_localize_script(
            'create-order-script',
            'alarnd_create_order_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("create_order_nonce"),
                'redirecturl' => home_url(),
                'products_api' => $products_api_url
            )
        );
    }

    /**
     * Delete the transient from admin bar
     */

    public function add_delete_transient_button($admin_bar)
    {
        $admin_bar->add_menu(
            array(
                'id' => 'delete-product-transient',
                'title' => 'Delete Product Transient',
                'href' => '#',
                'meta' => array(
                    'title' => __('Delete Product Transient'),
                    'onclick' => 'deleteProductTransient(); return false;', // Trigger JS function
                ),
            )
        );
    }

    public function delete_product_transient()
    {
        $nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
        $check_nonce1 = wp_verify_nonce($nonce, 'delete_transient_nonce');
        $check_nonce2 = wp_verify_nonce($nonce, 'create_order_nonce');

        if (!$check_nonce1 && !$check_nonce2) {
            wp_send_json_error('Invalid nonce', 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 401);
        }

        delete_transient('cached_products');

        wp_send_json_success('Product transient deleted.');
    }


    /**
     * Fetch products data from the API
     */
    public function fetch_products_data()
    {
        // Check if the transient exists
        $cached_products = get_transient('cached_products');

        if ($cached_products === false) {
            // Determine the API URL based on the current domain
            $current_domain = $_SERVER['SERVER_NAME'];
            $products_api_url = strpos($current_domain, '.test') !== false ?
                'https://allaround.test/wp-json/alarnd-main/v1/products' :
                'https://main.lukpaluk.xyz/wp-json/alarnd-main/v1/products';

            // Transient does not exist or expired, fetch data from API
            $response = wp_remote_get(
                $products_api_url,
                array(
                    'sslverify' => false,
                    'timeout' => 30 // timeout in seconds
                )
            );

            if (is_wp_error($response)) {
                error_log('Error fetching products: ' . $response->get_error_message());
                return [];
            }

            $products = json_decode(wp_remote_retrieve_body($response), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Error decoding JSON: ' . json_last_error_msg());
                return [];
            }

            // Log the structure of the fetched products
            error_log('Fetched products data: ' . print_r($products, true));

            // Set the transient to cache the data for 24 hours
            set_transient('cached_products', $products, DAY_IN_SECONDS);
        } else {
            // Transient exists, use cached data
            error_log('Using cached products data.');
            $products = $cached_products;
        }

        return $products;
    }

    /**
     * Fetch Client Data from Client Post Type
     */
    public function fetch_clients_data()
    {
        $args = array(
            'post_type' => 'client',
            'post_status' => 'publish',
            'numberposts' => -1
        );

        $clients = get_posts($args);
        $client_list = array();

        foreach ($clients as $client) {
            $first_name = get_post_meta($client->ID, 'first_name', true);
            $last_name = get_post_meta($client->ID, 'last_name', true);
            $email = get_post_meta($client->ID, 'email', true);
            $phone = get_post_meta($client->ID, 'phone', true);
            $name = $first_name . ' ' . $last_name;
            $client_list[] = array(
                'id' => $client->ID,
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            );
        }

        return $client_list;
    }

    /**
     * Fetch Manage Client Data on Selection
     */

    public function get_client_details()
    {
        check_ajax_referer('create_order_nonce', 'security');

        $client_id = intval($_POST['client_id']);

        // Fetch client details from the database
        $client = $this->get_client_by_id($client_id);

        if ($client) {
            $client_url = get_permalink($client_id);

            $client['url'] = $client_url;
            wp_send_json_success($client);
        } else {
            wp_send_json_error('Client not found');
        }
    }

    function get_client_by_id($client_id)
    {
        $client_post = get_post($client_id);
        if (!$client_post || $client_post->post_type !== 'client') {
            return false;
        }

        $client_meta = get_post_meta($client_id);

        $client = array(
            'client_type' => isset($client_meta['client_type'][0]) ? $client_meta['client_type'][0] : '',
            'first_name' => isset($client_meta['first_name'][0]) ? $client_meta['first_name'][0] : '',
            'last_name' => isset($client_meta['last_name'][0]) ? $client_meta['last_name'][0] : '',
            'invoice' => isset($client_meta['invoice'][0]) ? $client_meta['invoice'][0] : '',
            'address_1' => isset($client_meta['address_1'][0]) ? $client_meta['address_1'][0] : '',
            'postcode' => isset($client_meta['postcode'][0]) ? $client_meta['postcode'][0] : '',
            'city' => isset($client_meta['city'][0]) ? $client_meta['city'][0] : '',
            'email' => isset($client_meta['email'][0]) ? $client_meta['email'][0] : '',
            'phone' => isset($client_meta['phone'][0]) ? $client_meta['phone'][0] : '',
            'logo_type' => isset($client_meta['logo_type'][0]) ? $client_meta['logo_type'][0] : '',
            'mini_url' => isset($client_meta['mini_url'][0]) ? $client_meta['mini_url'][0] : '',
            'mini_header' => isset($client_meta['mini_header'][0]) ? $client_meta['mini_header'][0] : '',
        );

        return $client;
    }


    /**
     * Handle form submission to create an order
     */

    public function create_order_from_form()
    {
        check_ajax_referer('create_order_nonce', 'security');

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
            'postcode' => sanitize_text_field($_POST['postcode']),
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

        $shipping_total = sanitize_text_field($_POST['shipping_total']);

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
                            $meta['value'] .= "<p>" . basename($artwork_url) . "</p><a href=\"" . $artwork_url . "\" target=\"_blank\"><img class=\"alarnd__artwork_img\" src=\"" . $artwork_url . "\" /></a>";
                        }
                    } elseif (!empty($artwork_urls)) { // Handle single artwork URL (not an array)
                        // Extract the file extension for single URL
                        $extension = pathinfo($artwork_urls, PATHINFO_EXTENSION);
                        $meta['value'] = "<p>" . basename($artwork_urls) . "</p><a href=\"" . $artwork_urls . "\" target=\"_blank\"><img class=\"alarnd__artwork_img\" src=\"" . $artwork_urls . "\" /></a>";
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


}

new AllAroundCreateOrder();