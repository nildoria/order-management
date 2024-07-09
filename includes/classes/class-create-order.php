<?php
class AllAroundCreateOrder
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('admin_bar_menu', [$this, 'add_delete_transient_button'], 100);
        add_action('wp_ajax_delete_product_transient', [$this, 'delete_product_transient']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

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
                'https://allaround.co.il/wp-json/alarnd-main/v1/products';

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



}

new AllAroundCreateOrder();