<?php
class AllAroundAddItem
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'additem_scripts']);
    }

    public function additem_scripts()
    {
        global $post;
        // Enqueue custom styles and scripts
        wp_enqueue_style('add-item-style', get_template_directory_uri() . '/assets/css/add-item.css', [], HELLO_ELEMENTOR_VERSION);

        wp_enqueue_script('add-item-script', get_template_directory_uri() . '/assets/js/add-item.js', ['jquery'], HELLO_ELEMENTOR_VERSION, true);

        $post_id = is_singular('post') && isset($post) ? $post->ID : null;

        if ($post_id) {
            $order_id = esc_attr(get_post_meta($post_id, 'order_id', true));
            $order_domain = esc_url(get_post_meta($post_id, 'site_url', true));
        } else {
            $order_id = '';
            $order_domain = '';
        }
        // Set default order_domain if not set
        if (empty($order_domain)) {
            $order_domain = 'https://allaround.co.il';
            // TODO: For Staging
            // $order_domain = 'https://main.lukpaluk.xyz';
        }

        wp_localize_script(
            'add-item-script',
            'alarnd_add_item_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("add_item_nonce"),
                'home_url' => home_url(),
                'assets' => get_template_directory_uri() . '/assets/',
                'post_id' => $post_id,
                'order_id' => $order_id,
                'order_domain' => $order_domain,
            )
        );
    }



    /**
     * Fetch products data from the API (Not Used)
     */
    public function fetch_products_data()
    {
        // Check if the transient exists
        $cached_products = get_transient('cached_products');

        if ($cached_products === false) {
            global $post;

            $current_domain = $_SERVER['SERVER_NAME'];
            $post_id = is_singular('post') && isset($post) ? $post->ID : null;
            $order_domain = '';

            if ($post_id) {
                $order_domain = esc_url(get_post_meta($post_id, 'site_url', true));
            }

            // Set default order_domain if not set or empty
            // TODO: For Staging
            // $order_domain = !empty($order_domain) ? $order_domain : 'https://main.lukpaluk.xyz';
            // $products_api_url = strpos($current_domain, '.test') !== false ?
            //     'https://allaround.test/wp-json/alarnd-main/v1/products' :
            //     'https://main.lukpaluk.xyz/wp-json/alarnd-main/v1/products';


            // TODO: For Live
            $order_domain = !empty($order_domain) ? $order_domain : 'https://allaround.co.il';
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

new AllAroundAddItem();