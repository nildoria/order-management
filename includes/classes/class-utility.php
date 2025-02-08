<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Utilities class
 *
 * @since 2.0
 */

class Alarnd_Utility
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_translation_options_page'));
        add_action('admin_init', array($this, 'initialize_translation_options'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_translation_script'));

        // Register the REST API route
        // add_action('rest_api_init', [$this, 'utility_rest_api_endpoints']);
        
        add_action('init', array($this, 'register_order_group_post_type'));
        add_action('add_meta_boxes', array($this, 'add_order_group_meta_boxes'));
        add_action('save_post', array($this, 'save_order_group_products_meta'));
        add_action('admin_menu', array($this, 'add_products_api_options_page'));

        add_action('wp_ajax_create_order_group', array($this, 'create_order_group_ajax'));
        add_action('wp_ajax_nopriv_create_order_group', array($this, 'create_order_group_ajax'));

        add_action('wp_ajax_save_order_group_text', array($this, 'save_order_group_text'));
        add_action('wp_ajax_nopriv_save_order_group_text', array($this, 'save_order_group_text'));

        add_action('wp_ajax_filter_agent_sales', array($this, 'handle_agent_sales_ajax'));
        add_action('wp_ajax_filter_author_sales', array($this, 'handle_filter_author_sales'));
    }

    // public function utility_rest_api_endpoints()
    // {
    // }

    // public function check_basic_auth($request)
    // {
    //     $authorization = $request->get_header('Authorization');

    //     if (strpos($authorization, 'Basic ') === 0) {
    //         // Get the username and password from the Authorization header
    //         $credentials = base64_decode(substr($authorization, 6));
    //         list($username, $password) = explode(':', $credentials);

    //         // Authenticate the user
    //         $user = wp_authenticate($username, $password);
    //         if (is_wp_error($user)) {
    //             return new WP_Error('unauthorized', 'Invalid credentials', array('status' => 401));
    //         }

    //         // Set the current user
    //         wp_set_current_user($user->ID);

    //         return true;
    //     }

    //     return new WP_Error('unauthorized', 'Authorization header not found', array('status' => 401));
    // }


    public function add_translation_options_page()
    {
        add_menu_page(
            'Translations',
            'Translations',
            'manage_options',
            'translation-options',
            array($this, 'render_translation_options_page')
        );
    }

    public function render_translation_options_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Translations', 'your-textdomain'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('translation_options_group');
                do_settings_sections('translation-options');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function initialize_translation_options()
    {
        add_settings_section(
            'translation_section',
            'Translation Settings',
            array($this, 'render_translation_section'),
            'translation-options'
        );

        add_settings_field(
            'translations',
            'Translations',
            array($this, 'render_translations_fields'),
            'translation-options',
            'translation_section'
        );

        register_setting('translation_options_group', 'translations', array($this, 'sanitize_translations'));
    }

    public function render_translation_section()
    {
        echo '<p>Enter the translations below:</p>';
    }

    public function render_translations_fields()
    {
        $translations = get_option('translations', array());
        ?>
        <div id="translations-wrapper">
            <?php if (empty($translations)): ?>
                <div class="translation-pair">
                    <input type="text" name="translations[hebrew][]" placeholder="Hebrew Text">
                    <input type="text" name="translations[english][]" placeholder="English Translation">
                </div>
            <?php else: ?>
                <?php foreach ($translations['hebrew'] as $index => $hebrew): ?>
                    <div class="translation-pair">
                        <input type="text" name="translations[hebrew][]" value="<?php echo esc_attr($hebrew); ?>"
                            placeholder="Hebrew Text">
                        <input type="text" name="translations[english][]"
                            value="<?php echo esc_attr($translations['english'][$index]); ?>" placeholder="English Translation">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-translation"><?php _e('Add New Translation', 'your-textdomain'); ?></button>
        <?php
    }

    public function sanitize_translations($input)
    {
        $output = array(
            'hebrew' => array(),
            'english' => array()
        );

        if (isset($input['hebrew']) && is_array($input['hebrew'])) {
            foreach ($input['hebrew'] as $hebrew) {
                $output['hebrew'][] = sanitize_text_field($hebrew);
            }
        }

        if (isset($input['english']) && is_array($input['english'])) {
            foreach ($input['english'] as $english) {
                $output['english'][] = sanitize_text_field($english);
            }
        }

        return $output;
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_translation-options') {
            return;
        }

        wp_enqueue_script('translation-admin-script', get_template_directory_uri() . '/assets/js/translation-admin.js', array('jquery'), null, true);
    }

    public function enqueue_translation_script()
    {
        if ($this->is_current_user_contributor()) {
            wp_enqueue_script('translation-script', get_template_directory_uri() . '/assets/js/translation.js', array('jquery'), null, true);

            $translations = get_option('translations', array('hebrew' => array(), 'english' => array()));
            wp_localize_script('translation-script', 'translationData', $translations);
        }

        wp_enqueue_script('order-group-script', get_template_directory_uri() . '/assets/js/order-group.js', ['jquery'], HELLO_ELEMENTOR_VERSION, true);

        wp_localize_script(
            'order-group-script',
            'alarnd_order_group_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("order_group_nonce"),
                'redirecturl' => home_url(),
            )
        );
    }

    private function is_current_user_contributor()
    {
        $current_user = wp_get_current_user();
        return in_array('contributor', (array) $current_user->roles);
    }

    /**
     * Order Group Functions
     */
    public function add_products_api_options_page()
    {
        add_options_page(
            'Products API Settings', // Page Title
            'Products API', // Menu Title
            'manage_options', // Capability
            'products-api-settings', // Menu Slug
            [$this, 'render_products_api_options_page'], // Callback Function
        );
    }

    public function render_products_api_options_page()
    {
        // Check if the user has permission to access this page
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('save_products_api_settings')) {
            update_option('main_site_products_api', sanitize_text_field($_POST['main_site_products_api']));
            update_option('mini_site_products_api', sanitize_text_field($_POST['mini_site_products_api']));
            update_option('flash_sale_products_api', sanitize_text_field($_POST['flash_sale_products_api']));
            echo '<div class="updated"><p>Settings saved successfully!</p></div>';
        }

        // Get the saved options
        $main_site_api = get_option('main_site_products_api', 'https://allaround.co.il/wp-json/alarnd-main/v1/products');
        $mini_site_api = get_option('mini_site_products_api', 'https://sites.allaround.co.il/wp-json/alarnd-main/v1/products');
        $flash_sale_api = get_option('flash_sale_products_api', 'https://flash.allaround.co.il/wp-json/alarnd-main/v1/products');
        ?>

        <div class="wrap">
            <h1>Products API Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('save_products_api_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="main_site_products_api">Main Site API URL</label>
                        </th>
                        <td>
                            <input type="text" id="main_site_products_api" name="main_site_products_api" value="<?php echo esc_attr($main_site_api); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="mini_site_products_api">Mini Site API URL</label>
                        </th>
                        <td>
                            <input type="text" id="mini_site_products_api" name="mini_site_products_api" value="<?php echo esc_attr($mini_site_api); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="flash_sale_products_api">Flash Sale API URL</label>
                        </th>
                        <td>
                            <input type="text" id="flash_sale_products_api" name="flash_sale_products_api" value="<?php echo esc_attr($flash_sale_api); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    public function get_product_api_url($type)
    {
        $urls = array(
            'main' => get_option('main_site_products_api', ''),
            'mini' => get_option('mini_site_products_api', ''),
            'flash' => get_option('flash_sale_products_api', ''),
        );

        return isset($urls[$type]) ? $urls[$type] : '';  // Return the URL based on the provided type
    }

    public function register_order_group_post_type()
    {
        $labels = array(
            'name' => _x('Order Groups', 'post type general name', 'your-textdomain'),
            'singular_name' => _x('Order Group', 'post type singular name', 'your-textdomain'),
            'menu_name' => _x('Order Groups', 'admin menu', 'your-textdomain'),
            'name_admin_bar' => _x('Order Group', 'add new on admin bar', 'your-textdomain'),
            'add_new' => _x('Add New', 'order group', 'your-textdomain'),
            'add_new_item' => __('Add New Order Group', 'your-textdomain'),
            'new_item' => __('New Order Group', 'your-textdomain'),
            'edit_item' => __('Edit Order Group', 'your-textdomain'),
            'view_item' => __('View Order Group', 'your-textdomain'),
            'all_items' => __('All Order Groups', 'your-textdomain'),
            'search_items' => __('Search Order Groups', 'your-textdomain'),
            'parent_item_colon' => __('Parent Order Groups:', 'your-textdomain'),
            'not_found' => __('No order groups found.', 'your-textdomain'),
            'not_found_in_trash' => __('No order groups found in Trash.', 'your-textdomain')
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'order-group'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
        );

        register_post_type('order_group', $args);
    }

    public function add_order_group_meta_boxes()
    {
        add_meta_box(
            'order_group_products',
            'Select Products',
            [$this, 'order_group_products_meta_box_callback'],
            'order_group',
            'normal',
            'high'
        );
    }

    public function order_group_products_meta_box_callback($post)
    {
        wp_nonce_field('save_order_group_products_meta', 'order_group_products_meta_nonce');

        // Get selected products
        $selected_products_main = get_post_meta($post->ID, '_order_group_products_main', true);
        $selected_products_sites = get_post_meta($post->ID, '_order_group_products_sites', true);
        $selected_products_flash = get_post_meta($post->ID, '_order_group_products_flash', true);

        $selected_products_main = is_array($selected_products_main) ? $selected_products_main : array();
        $selected_products_sites = is_array($selected_products_sites) ? $selected_products_sites : array();
        $selected_products_flash = is_array($selected_products_flash) ? $selected_products_flash : array();

        // Fetch products from all APIs
        $products_main = $this->fetch_products_from_api($this->get_product_api_url('main'));
        $products_sites = $this->fetch_products_from_api($this->get_product_api_url('mini'));
        $products_flash = $this->fetch_products_from_api($this->get_product_api_url('flash'));


        // Render sections for each product source
        echo '<div class="order-group-products-meta-box">';

        // Main Site Products
        echo '<div class="product-group">';
        echo '<h4>Main Site Products</h4>';
        echo '<select id="order_group_products_main" name="order_group_products_main[]" multiple style="width: 100%;">';
        foreach ($products_main as $product) {
            $selected = in_array($product['id'], $selected_products_main) ? 'selected' : '';
            echo '<option value="' . esc_attr($product['id']) . '" ' . $selected . '>' . esc_html($product['name']) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        // Mini Site Products
        echo '<div class="product-group">';
        echo '<h4>Mini Site Products</h4>';
        echo '<select id="order_group_products_sites" name="order_group_products_sites[]" multiple style="width: 100%;">';
        foreach ($products_sites as $product) {
            $selected = in_array($product['id'], $selected_products_sites) ? 'selected' : '';
            echo '<option value="' . esc_attr($product['id']) . '" ' . $selected . '>' . esc_html($product['name']) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        // Flash Sale Products
        echo '<div class="product-group">';
        echo '<h4>Flash Sale Products</h4>';
        echo '<select id="order_group_products_flash" name="order_group_products_flash[]" multiple style="width: 100%;">';
        foreach ($products_flash as $product) {
            $selected = in_array($product['id'], $selected_products_flash) ? 'selected' : '';
            echo '<option value="' . esc_attr($product['id']) . '" ' . $selected . '>' . esc_html($product['name']) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '</div>';
    }


    public function save_order_group_products_meta($post_id)
    {
        if (!isset($_POST['order_group_products_meta_nonce']) || !wp_verify_nonce($_POST['order_group_products_meta_nonce'], 'save_order_group_products_meta')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save Main Site Products
        if (isset($_POST['order_group_products_main'])) {
            $products_main = array_map('intval', $_POST['order_group_products_main']);
            update_post_meta($post_id, '_order_group_products_main', $products_main);
        } else {
            delete_post_meta($post_id, '_order_group_products_main');
        }

        // Save Mini Site Products
        if (isset($_POST['order_group_products_sites'])) {
            $products_sites = array_map('intval', $_POST['order_group_products_sites']);
            update_post_meta($post_id, '_order_group_products_sites', $products_sites);
        } else {
            delete_post_meta($post_id, '_order_group_products_sites');
        }

        // Save Flash Sale Products
        if (isset($_POST['order_group_products_flash'])) {
            $products_flash = array_map('intval', $_POST['order_group_products_flash']);
            update_post_meta($post_id, '_order_group_products_flash', $products_flash);
        } else {
            delete_post_meta($post_id, '_order_group_products_flash');
        }
    }


    public function create_order_group_ajax()
    {
        check_ajax_referer('order_group_nonce', 'nonce');

        $post_title = sanitize_text_field($_POST['post_title']);
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $selected_products_main = isset($_POST['order_group_products_main']) ? array_map('intval', $_POST['order_group_products_main']) : array();
        $selected_products_sites = isset($_POST['order_group_products_sites']) ? array_map('intval', $_POST['order_group_products_sites']) : array();
        $selected_products_flash = isset($_POST['order_group_products_flash']) ? array_map('intval', $_POST['order_group_products_flash']) : array();

        if ($post_id) {
            // Update existing Order Group post
            $order_group_id = wp_update_post(array(
                'ID' => $post_id,
                'post_type' => 'order_group',
                'post_status' => 'publish'
            ));
        } else {
            // Create a new Order Group post
            $order_group_id = wp_insert_post(array(
                'post_title' => $post_title,
                'post_type' => 'order_group',
                'post_status' => 'publish'
            ));
        }

        if ($order_group_id && !is_wp_error($order_group_id)) {
            // Save the selected products separately
            update_post_meta($order_group_id, '_order_group_products_main', $selected_products_main);
            update_post_meta($order_group_id, '_order_group_products_sites', $selected_products_sites);
            update_post_meta($order_group_id, '_order_group_products_flash', $selected_products_flash);

            // Return success response with redirect URL
            wp_send_json_success(array('redirect_url' => get_permalink($order_group_id)));
        } else {
            wp_send_json_error(array('message' => 'Failed to create Order Group.'));
        }
    }

    public function fetch_products_from_api($api_url)
    {
        // Define a unique transient key based on the API URL
        $transient_key = 'products_' . md5($api_url);

        // Check if the transient exists
        $cached_products = get_transient($transient_key);

        if ($cached_products !== false) {
            // Transient exists, use cached data
            return $cached_products;
        }

        // Transient does not exist or expired, fetch data from API
        $response = wp_remote_get($api_url, array('sslverify' => false, 'timeout' => 30));
        if (is_wp_error($response)) {
            error_log('Error fetching products from ' . $api_url . ': ' . $response->get_error_message());
            return array();
        }

        $products = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error decoding JSON from ' . $api_url . ': ' . json_last_error_msg());
            return array();
        }

        // Store the fetched products in a transient for 24 hours
        set_transient($transient_key, $products, DAY_IN_SECONDS);

        return $products;
    }

    public function save_order_group_text()
    {
        // Check nonce
        check_ajax_referer('order_group_nonce', 'nonce');

        // Validate input
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $text_value = isset($_POST['text_value']) ? wp_kses_post($_POST['text_value']) : '';

        if (!$order_id || empty($text_value)) {
            wp_send_json_error(array('message' => 'Invalid input.'));
        }

        // Save text as meta
        $updated = update_post_meta($order_id, 'order_group_text', $text_value);

        if ($updated) {
            wp_send_json_success(array('message' => 'Text saved successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save text.'));
        }
    }

    // Agents Sales AJAX Handler Function
    public function handle_agent_sales_ajax()
    {
        // Check nonce
        check_ajax_referer('order_group_nonce', 'nonce');

        // Check and sanitize input
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $selected_year = isset($_POST['year_filter']) ? sanitize_text_field($_POST['year_filter']) : date('Y');

        // Fetch all agent role users
        $agents = get_users(array('role' => 'editor'));
        $sales_data = array();

        // Fetch sales data for each agent
        foreach ($agents as $agent) {
            $agent_id = $agent->ID;
            $agent_name = $agent->display_name;
            $agent_url = get_author_posts_url($agent_id);

            $sales_data[$agent_id] = array(
                'name' => $agent_name,
                'url' => $agent_url,
                'sales' => array()
            );

            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'author' => $agent_id,
                'meta_query' => array(
                    array(
                        'key' => 'order_total',
                        'compare' => 'EXISTS'
                    )
                )
            );
            $agent_posts = new WP_Query($args);

            if ($agent_posts->have_posts()) {
                while ($agent_posts->have_posts()) {
                    $agent_posts->the_post();
                    $order_total = get_post_meta(get_the_ID(), 'order_total', true);
                    $order_date = get_the_date('Y-m');

                    // Format the month as "Month YYYY"
                    $formatted_month = date_i18n('F Y', strtotime($order_date . '-01'));

                    if (!isset($sales_data[$agent_id]['sales'][$formatted_month])) {
                        $sales_data[$agent_id]['sales'][$formatted_month] = 0;
                    }

                    $sales_data[$agent_id]['sales'][$formatted_month] += floatval($order_total);
                }
                wp_reset_postdata();
            }
        }

        // Filter data based on provided filters
        if ($start_date && $end_date) {
            foreach ($sales_data as $agent_id => $data) {
                foreach ($data['sales'] as $date => $total) {
                    $timestamp = strtotime($date);
                    $month_year = date('Y-m', $timestamp);
                    if ($month_year < $start_date || $month_year > $end_date) {
                        unset($sales_data[$agent_id]['sales'][$date]);
                    }
                }
                if (empty($sales_data[$agent_id]['sales'])) {
                    unset($sales_data[$agent_id]);
                }
            }
        } else {
            foreach ($sales_data as $agent_id => $data) {
                foreach ($data['sales'] as $date => $total) {
                    $timestamp = strtotime($date);
                    $year = date('Y', $timestamp);
                    if ($year !== $selected_year) {
                        unset($sales_data[$agent_id]['sales'][$date]);
                    }
                }
                if (empty($sales_data[$agent_id]['sales'])) {
                    unset($sales_data[$agent_id]);
                }
            }
        }

        // Return JSON response
        wp_send_json_success($sales_data);
    }


    public function handle_filter_author_sales()
    {
        // Get and sanitize inputs
        $author_id = isset($_POST['author_id']) ? intval($_POST['author_id']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $selected_year = isset($_POST['year_filter']) ? sanitize_text_field($_POST['year_filter']) : date('Y');

        // Ensure the user has access
        if (!current_user_can('administrator') && get_current_user_id() !== $author_id) {
            wp_send_json_error(['message' => 'Access Denied']);
        }

        // Query the author's posts
        $args = [
            'author' => $author_id,
            'posts_per_page' => -1,
            'post_type' => 'post',
            'post_status' => 'publish',
        ];

        if ($start_date && $end_date) {
            $args['date_query'] = [
                'after' => $start_date,
                'before' => $end_date,
                'inclusive' => true,
            ];
        } elseif ($selected_year) {
            $args['date_query'] = [
                [
                    'year' => $selected_year,
                ],
            ];
        }

        $query = new WP_Query($args);

        // Initialize sales data
        $sales_by_month = [];
        $sales_by_day = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $order_total = get_post_meta(get_the_ID(), 'order_total', true);
                if (is_numeric($order_total)) {
                    $order_total = floatval($order_total);
                    $post_date = get_the_date('d/m/Y');
                    $post_month = get_the_date('Y-m');

                    // Format the month as "Month YYYY"
                    $formatted_month = date_i18n('F Y', strtotime($post_month . '-01'));

                    // Sales by Month
                    if (!isset($sales_by_month[$formatted_month])) {
                        $sales_by_month[$formatted_month] = 0;
                    }
                    $sales_by_month[$formatted_month] += $order_total;

                    // Sales by Day
                    if (!isset($sales_by_day[$post_date])) {
                        $sales_by_day[$post_date] = 0;
                    }
                    $sales_by_day[$post_date] += $order_total;
                }
            }
            wp_reset_postdata();
        }

        wp_send_json_success([
            'sales_by_month' => $sales_by_month,
            'sales_by_day' => $sales_by_day,
        ]);
    }

}

// Initialize the class
new Alarnd_Utility();
