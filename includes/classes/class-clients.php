<?php
class AllAroundClientsDB
{

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_custom_metabox']);
        add_action('save_post', [$this, 'save_metabox_data']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_uploader']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        add_action('all_around_create_client', [$this, 'create_client_cb'], 10, 4);

        add_filter('manage_client_posts_columns', [$this, 'add_email_column_client']);
        add_action('manage_client_posts_custom_column', [$this, 'show_email_column_client'], 10, 2);

        add_action('wp_ajax_create_client', array($this, 'create_client_ajax'));
        add_action('wp_ajax_update_client', array($this, 'update_client_ajax'));
        add_action('wp_ajax_update_order_type', array($this, 'update_order_type_ajax'));
        add_action('wp_ajax_get_client_orders', array($this, 'get_client_orders'));

        add_action('init', [$this, 'register_post_type_cb'], 0);
        add_filter('post_type_link', [$this, 'client_post_type_link'], 1, 2);
        add_action('init', [$this, 'client_rewrite_rules']);
        add_action('admin_init', [$this, 'add_client_capabilities']);

        add_shortcode('allaround_client_lists', [$this, 'client_lists_shortcode']);

    }

    public function get_client_orders()
    {
        check_ajax_referer('get_client_nonce', '_nonce');

        $client_id = isset($_REQUEST['client_id']) ? sanitize_text_field($_REQUEST['client_id']) : 0;

        // get post type = post with meta_key client_id and value $client_id
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => 'client_id',
            'meta_value' => $client_id
        );

        $first_name = get_post_meta($client_id, 'first_name', true);
        $last_name = get_post_meta($client_id, 'last_name', true);
        $full_name = $first_name . ' ' . $last_name;

        $orders = get_posts($args);
        ?>
        <div class="allrnd--order-lists-modal white-popup-block">
            <div class="allrnd-client-order-list">
                <h4><?php echo $full_name; ?>'s orders</h4>
                <ul>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order):
                            $order_id = get_post_meta($order->ID, 'order_id', true);
                            $order_number = get_post_meta($order->ID, 'order_number', true);
                            $site_url = get_post_meta($order->ID, 'site_url', true);
                            $order_status = get_post_meta($order->ID, 'order_status', true);
                            ?>
                            <li>
                                <a target="_blank" href="<?php echo esc_url(get_permalink($order->ID)); ?>"
                                    class="allaround--client-orders"><?php echo esc_html(get_permalink($order->ID)); ?></a>
                                <?php echo !empty($order_id) ? '<span>Order ID: ' . $order_id . '</span>' : ''; ?>
                                <?php echo !empty($order_number) ? '<span>Order Number: ' . $order_number . '</span>' : ''; ?>
                                <?php echo !empty($site_url) ? '<span>Site URL: ' . $site_url . '</span>' : ''; ?>
                                <?php echo !empty($order_status) ? '<span>Order Status: ' . $order_status . '</span>' : ''; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No orders found</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php
        wp_die();
    }

    public function create_client_ajax()
    {
        check_ajax_referer('client_nonce', 'nonce');

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $client_type = isset($_POST['client_type']) ? sanitize_text_field($_POST['client_type']) : 'personal';

        // check email empty or email not valid then return
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => esc_html__("Please enter a valid email address.", "hello-elementor")
                )
            );
            wp_die();
        }

        $name = $this->createFullName($first_name, $last_name);

        $allowedFields = [
            'client_type',
            'first_name',
            'last_name',
            'email',
            'address_1',
            'address_number',
            'phone',
            'city',
            'status',
            'token',
            'email',
            'subscribed',
            'invoice'
        ];

        if ('company' === $client_type) {
            $addition_fields = [
                'dark_logo',
                'lighter_logo',
                'back_light',
                'back_dark',
                'logo_type',
                'mini_url',
                'mini_header',
                'logo'
            ];

            $allowedFields = array_merge($allowedFields, $addition_fields);
        }

        error_log(print_r($allowedFields, true));
        // Filter the $_POST array to include only the allowed fields
        $filteredPostData = array_intersect_key($_POST, array_flip($allowedFields));

        error_log(print_r($filteredPostData, true));

        // check by email if client already exists
        if ($client_id = $this->clientExistsByEmail($email)) {

            $old_client_type = get_post_meta($client_id, 'client_type', true);

            if (isset($filteredPostData) && !empty($filteredPostData)) {
                // error_log( print_r( $filteredPostData, true ) );
                foreach ((array) $filteredPostData as $key => $value) {
                    if ("client_type" === $key && "company" === $old_client_type) {
                        continue;
                    }
                    $this->ml_update_postmeta($client_id, $key, $value);
                }
            }

            $old_first_name = get_post_meta($client_id, 'first_name', true);
            $old_last_name = get_post_meta($client_id, 'last_name', true);

            // if name changed update post title
            if ($old_first_name !== $first_name || $old_last_name !== $last_name) {
                $new_name = $this->createFullName($first_name, $last_name);
                $this->update_post_title($client_id, $new_name);
            }

            wp_send_json_success(
                array(
                    "message_type" => 'reqular',
                    "message" => "Client $client_id successfully updated."
                )
            );
            wp_die();
        }

        // if( ! empty( $email ) ) {
        //     $name = "$name ($email)";
        // }

        error_log("Name: $name");
        error_log(print_r($_POST, true));

        $client_id = wp_insert_post(
            array(
                'post_title' => $name, // Set the post title to the order number
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'client',
            )
        );

        if (isset($filteredPostData) && !empty($filteredPostData)) {
            error_log(print_r($filteredPostData, true));
            foreach ((array) $filteredPostData as $key => $value) {
                error_log("Key: $key, Value: $value");
                update_post_meta($client_id, $key, $value);
            }
        }

        update_post_meta($client_id, 'full_name', $name);

        wp_send_json_success(
            array(
                "message_type" => 'reqular',
                "message" => "Client successfully created with ID: $client_id."
            )
        );
        wp_die();

    }

    public function update_order_type_ajax()
    {
        check_ajax_referer('client_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? sanitize_text_field(absint($_POST['post_id'])) : 0;
        $order_type = isset($_POST['order_type']) ? sanitize_text_field($_POST['order_type']) : '';

        // check email empty or email not valid then return
        if (empty($post_id)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => esc_html__("Unable to update client with ID: $post_id.", "hello-elementor")
                )
            );
            wp_die();
        }

        // check email empty or email not valid then return
        if (empty($order_type)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => esc_html__("Please enter a valid order type.", "hello-elementor")
                )
            );
            wp_die();
        }

        $client_id = get_post_meta($post_id, 'client_id', true);
        $client_type = get_post_meta($client_id, 'client_type', true);

        // update client_type to client only if client_type is not company
        if ('company' !== $client_type) {
            update_post_meta($client_id, 'client_type', $order_type);
        }

        update_post_meta($post_id, 'order_type', $order_type);

        wp_send_json_success(
            array(
                "message_type" => 'reqular',
                "message" => "Order #$post_id type successfully updated."
            )
        );
        wp_die();

    }

    public function update_client_ajax()
    {
        check_ajax_referer('client_nonce', 'nonce');

        $client_id = isset($_POST['client_id']) ? sanitize_text_field(absint($_POST['client_id'])) : 0;

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $client_type = isset($_POST['client_type']) ? sanitize_text_field($_POST['client_type']) : 'personal';

        $old_first_name = get_post_meta($client_id, 'first_name', true);
        $old_last_name = get_post_meta($client_id, 'last_name', true);

        // check email empty or email not valid then return
        if (empty($client_id)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => esc_html__("Unable to update client with ID: $client_id.", "hello-elementor")
                )
            );
            wp_die();
        }

        // check email empty or email not valid then return
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => esc_html__("Please enter a valid email address.", "hello-elementor")
                )
            );
            wp_die();
        }

        // TODO: check by email if client already exists but with different ID
        if ($this->clientExistsByEmail($email, $client_id)) {
            wp_send_json_error(
                array(
                    "message_type" => 'reqular',
                    "message" => "Client already exists with email: $email"
                )
            );
            wp_die();
        }

        $old_client_type = get_post_meta($client_id, 'client_type', true);

        $allowedFields = [
            'client_type',
            'first_name',
            'last_name',
            'email',
            'address_1',
            'address_number',
            'phone',
            'city',
            'status',
            'token',
            'email',
            'subscribed',
            'invoice'
        ];

        if ('company' === $client_type) {
            $addition_fields = [
                'dark_logo',
                'lighter_logo',
                'back_light',
                'back_dark',
                'logo_type',
                'mini_url',
                'mini_header',
                'logo'
            ];

            $allowedFields = array_merge($allowedFields, $addition_fields);
        }

        // error_log(print_r($client_type, true));
        // error_log(print_r($allowedFields, true));

        // Filter the $_POST array to include only the allowed fields
        $filteredPostData = array_intersect_key($_POST, array_flip($allowedFields));

        if (isset($filteredPostData) && !empty($filteredPostData)) {
            // error_log( print_r( $filteredPostData, true ) );
            foreach ((array) $filteredPostData as $key => $value) {
                if ("client_type" === $key && "company" === $old_client_type) {
                    continue;
                }
                $this->ml_update_postmeta($client_id, $key, $value);
            }
        }

        // Log old and new names for debugging
        error_log("Old First Name: $old_first_name, New First Name: $first_name");
        error_log("Old Last Name: $old_last_name, New Last Name: $last_name");

        // if name changed update post title
        if ($old_first_name !== $first_name || $old_last_name !== $last_name) {
            $name = $this->createFullName($first_name, $last_name);
            error_log("Updating post title for Client ID: $client_id, New Title: $name");
            $this->update_post_title($client_id, $name);
        }

        wp_send_json_success(
            array(
                "message_type" => 'reqular',
                "message" => "Client $client_id successfully updated."
            )
        );
        wp_die();

    }

    function update_post_title($post_id, $new_title)
    {
        // Make sure the post ID and new title are valid
        if (empty($post_id) || empty($new_title)) {
            return false;
        }

        // Get the current post title
        $current_post = get_post($post_id);
        if (!$current_post) {
            return false; // Post not found
        }

        // Check if the new title is different from the current title
        if ($current_post->post_title === $new_title) {
            return false; // Titles are the same, no need to update
        }

        // Create an array of arguments to pass to wp_update_post
        $post_args = array(
            'ID' => $post_id,
            'post_title' => $new_title,
        );

        // Update the post title
        $result = wp_update_post($post_args);

        // Check for errors and return result
        if (is_wp_error($result)) {
            return false;
        }

        $this->ml_update_postmeta($post_id, 'full_name', $new_title);

        return true;
    }

    function ml_update_postmeta($post_id, $meta_key, $new_value)
    {
        $current_value = get_post_meta($post_id, $meta_key, true);
        if ($new_value !== $current_value) {
            update_post_meta($post_id, $meta_key, $new_value);
        }
    }

    function clientExistsByEmail($email, $post_id = 0)
    {
        // Arguments for WP_Query
        $args = array(
            'post_type' => 'client',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $email,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1, // We only need to check if one post exists
            'fields' => 'ids' // We only need the IDs for checking existence
        );

        if ($post_id) {
            $args['post__not_in'] = array($post_id);
        }

        // Custom query
        $query = new WP_Query($args);

        // Get the post ID if a post was found
        $post_id = $query->have_posts() ? $query->posts[0] : false;

        // Clean up after WP_Query
        wp_reset_postdata();

        return $post_id;
    }

    function createFullName($first_name, $last_name)
    {
        // Trim the inputs to remove any extra spaces
        $first_name = trim($first_name);
        $last_name = trim($last_name);

        if (!empty($first_name) && !empty($last_name)) {
            return $first_name . " " . $last_name;
        } elseif (!empty($first_name)) {
            return $first_name;
        } elseif (!empty($last_name)) {
            return $last_name;
        } else {
            return "";
        }
    }

    public function create_client_cb($post_id, $order_data, $order_id, $order_number)
    {

        $first_name = isset($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : '';
        $last_name = isset($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : '';
        $email = isset($order_data['billing']['email']) ? $order_data['billing']['email'] : '';

        $filteredPostData = $order_data['billing'];

        // check if email is empty
        if (empty($email)) {
            error_log("Email is empty inside create_client_cb");
            return;
        }

        // check by email if client already exists and get client id
        if ($client_id = $this->clientExistsByEmail($email)) {

            $old_client_type = get_post_meta($client_id, 'client_type', true);

            if (isset($filteredPostData) && !empty($filteredPostData)) {
                // error_log( print_r( $filteredPostData, true ) );
                foreach ((array) $filteredPostData as $key => $value) {
                    if ("client_type" === $key && "company" === $old_client_type) {
                        continue;
                    }
                    if ("company" === $key) {
                        $current_value = get_post_meta($client_id, 'invoice', true);
                        if ($value !== $current_value) {
                            update_post_meta($client_id, 'invoice', $value);
                        }
                    } else {
                        $this->ml_update_postmeta($client_id, $key, $value);
                    }
                }
            }

            $old_first_name = get_post_meta($client_id, 'first_name', true);
            $old_last_name = get_post_meta($client_id, 'last_name', true);

            // if name changed update post title
            if ($old_first_name !== $first_name || $old_last_name !== $last_name) {
                $new_name = $this->createFullName($first_name, $last_name);
                $this->update_post_title($client_id, $new_name);
            }

            // Update the status to "client"
            $this->ml_update_postmeta($client_id, 'status', 'client');

            // update client_id to the order post
            update_post_meta($post_id, 'client_id', $client_id);

            wp_send_json_success(
                array(
                    "message_type" => 'reqular',
                    "message" => "Client $client_id successfully updated."
                )
            );
            wp_die();
        }

        $name = $this->createFullName($first_name, $last_name);

        // if( ! empty( $email ) ) {
        //     $name = "$name ($email)";
        // }

        error_log("Name: $name");
        // error_log( print_r( $order_data, true ) );

        $client_id = wp_insert_post(
            array(
                'post_title' => $name, // Set the post title to the order number
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'client',
            )
        );

        if (isset($filteredPostData) && !empty($filteredPostData)) {
            // error_log( print_r( $filteredPostData, true ) );
            foreach ((array) $filteredPostData as $key => $value) {
                // error_log( "Key: $key, Value: $value" );
                update_post_meta($client_id, $key, $value);
            }
        }

        // Update the status to "client"
        update_post_meta($client_id, 'status', 'client');

        // update client_id to the order post
        update_post_meta($post_id, 'client_id', $client_id);
    }

    public function add_custom_metabox()
    {
        add_meta_box(
            'metabox', // Unique ID
            'All Around Clients DB Metabox', // Box title
            [$this, 'display_metabox_html'], // Content callback
            'client', // Post type
            'normal', // Context (side, normal, advanced)
            'high' // Priority
        );
    }

    public function display_metabox_html($post)
    {
        // Add a nonce field for security
        wp_nonce_field(basename(__FILE__), 'nonce');

        $subscribed = get_post_meta($post->ID, 'subscribed', true);

        // Get existing values
        $fields = [
            'client_type' => get_post_meta($post->ID, 'client_type', true),
            'first_name' => get_post_meta($post->ID, 'first_name', true),
            'last_name' => get_post_meta($post->ID, 'last_name', true),
            'email' => get_post_meta($post->ID, 'email', true),
            'phone' => get_post_meta($post->ID, 'phone', true),
            'status' => get_post_meta($post->ID, 'status', true),
            'subscribed' => !empty($subscribed) ? $subscribed : 'yes',
            'token' => get_post_meta($post->ID, 'token', true),
            'address_1' => get_post_meta($post->ID, 'address_1', true),
            'city' => get_post_meta($post->ID, 'city', true),
            'dark_logo' => get_post_meta($post->ID, 'dark_logo', true),
            'lighter_logo' => get_post_meta($post->ID, 'lighter_logo', true),
            'back_light' => get_post_meta($post->ID, 'back_light', true),
            'back_dark' => get_post_meta($post->ID, 'back_dark', true),
            'logo_type' => get_post_meta($post->ID, 'logo_type', true),
            'mini_url' => get_post_meta($post->ID, 'mini_url', true),
            'mini_header' => get_post_meta($post->ID, 'mini_header', true),
            'invoice' => get_post_meta($post->ID, 'invoice', true),
            'logo' => get_post_meta($post->ID, 'logo', true),
        ];

        $token = esc_attr($fields['token']);
        if (!empty($token)) {
            $masked_token = substr($token, 0, 4) . str_repeat('*', strlen($token) - 4);
        } else {
            $masked_token = '';
        }

        ?>
        <p>
            <label for="client_type">Client Type:</label><br>
            <select name="client_type" id="client_type" required>
                <option value="personal" <?php selected($fields['client_type'], 'personal'); ?>>Personal</option>
                <option value="company" <?php selected($fields['client_type'], 'company'); ?>>Company</option>
            </select>
        </p>
        <p>
            <label for="first_name">First Name:</label><br>
            <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($fields['first_name']); ?>"
                required />
        </p>
        <p>
            <label for="last_name">Last Name:</label><br>
            <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($fields['last_name']); ?>" required />
        </p>
        <p>
            <label for="invoice">Invoice Name:</label><br>
            <input type="text" name="invoice" id="invoice" value="<?php echo esc_attr($fields['invoice']); ?>" />
        </p>
        <p>
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="<?php echo esc_attr($fields['email']); ?>" required />
        </p>
        <p>
            <label for="phone">Phone:</label><br>
            <input type="text" name="phone" id="phone" value="<?php echo esc_attr($fields['phone']); ?>" required />
        </p>
        <p>
            <label for="status">Status:</label><br>
            <select name="status" id="status">
                <option value="">-- Select Status --</option>
                <option value="client" <?php selected($fields['status'], 'client'); ?>>Client</option>
                <option value="welcome" <?php selected($fields['status'], 'welcome'); ?>>Welcome</option>
                <option value="company_prospect" <?php selected($fields['status'], 'company_prospect'); ?>>Company Prospect
                </option>
            </select>
        </p>
        <p>
            <label>Subscribed:</label><br>
            <label><input type="radio" name="subscribed" value="yes" <?php checked($fields['subscribed'], 'yes', true); ?> />
                Yes</label>
            <label><input type="radio" name="subscribed" value="no" <?php checked($fields['subscribed'], 'no'); ?> /> No</label>
        </p>
        <p>
            <label for="token">Token:</label><br>
            <input type="text" name="token" id="token" value="<?php echo $masked_token; ?>" />
        </p>
        <hr>
        <br>
        <p><b>Shipping details:</b></p>
        <hr>
        <p>
            <label for="address_1">Address:</label><br>
            <input type="text" name="address_1" id="address_1" value="<?php echo esc_attr($fields['address_1']); ?>" />
        </p>
        <p>
            <label for="city">City:</label><br>
            <input type="text" name="city" id="city" value="<?php echo esc_attr($fields['city']); ?>" />
        </p>
        <div class="all_arround_clients_db_company_details">
            <hr>
            <br>
            <p><b>Company details:</b></p>
            <hr>
            <p>
                <label for="dark_logo">Dark Logo:</label><br>
                <input type="text" name="dark_logo" id="dark_logo" value="<?php echo esc_attr($fields['dark_logo']); ?>" />
                <input type="button" id="dark_logo_button" class="button upload-image-button" value="Upload Image" />
                <input type="button" id="remove_dark_logo_button" class="button remove-image-button" value="Remove Image"
                    style="display: <?php echo $fields['dark_logo'] ? 'inline-block' : 'none'; ?>;" />
            </p>
            <p>
                <img id="dark_logo_preview" src="<?php echo esc_attr($fields['dark_logo']); ?>"
                    style="max-width: 300px; display: <?php echo $fields['dark_logo'] ? 'block' : 'none'; ?>;" />
            </p>
            <p>
                <label for="lighter_logo">Lighter Logo:</label><br>
                <input type="text" name="lighter_logo" id="lighter_logo"
                    value="<?php echo esc_attr($fields['lighter_logo']); ?>" />
                <input type="button" id="lighter_logo_button" class="button upload-image-button" value="Upload Image" />
                <input type="button" id="remove_lighter_logo_button" class="button remove-image-button" value="Remove Image"
                    style="display: <?php echo $fields['lighter_logo'] ? 'inline-block' : 'none'; ?>;" />
            </p>
            <p>
                <img id="lighter_logo_preview" src="<?php echo esc_attr($fields['lighter_logo']); ?>"
                    style="max-width: 300px; display: <?php echo $fields['lighter_logo'] ? 'block' : 'none'; ?>;" />
            </p>
            <p>
                <label for="back_light">Back Light:</label><br>
                <input type="text" name="back_light" id="back_light" value="<?php echo esc_attr($fields['back_light']); ?>" />
                <input type="button" id="back_light_button" class="button upload-image-button" value="Upload Image" />
                <input type="button" id="remove_back_light_button" class="button remove-image-button" value="Remove Image"
                    style="display: <?php echo $fields['back_light'] ? 'inline-block' : 'none'; ?>;" />
            </p>
            <p>
                <img id="back_light_preview" src="<?php echo esc_attr($fields['back_light']); ?>"
                    style="max-width: 300px; display: <?php echo $fields['back_light'] ? 'block' : 'none'; ?>;" />
            </p>
            <p>
                <label for="back_dark">Back Dark:</label><br>
                <input type="text" name="back_dark" id="back_dark" value="<?php echo esc_attr($fields['back_dark']); ?>" />
                <input type="button" id="back_dark_button" class="button upload-image-button" value="Upload Image" />
                <input type="button" id="remove_back_dark_button" class="button remove-image-button" value="Remove Image"
                    style="display: <?php echo $fields['back_dark'] ? 'inline-block' : 'none'; ?>;" />
            </p>
            <p>
                <img id="back_dark_preview" src="<?php echo esc_attr($fields['back_dark']); ?>"
                    style="max-width: 300px; display: <?php echo $fields['back_dark'] ? 'block' : 'none'; ?>;" />
            </p>
            <p>
                <label for="logo_type">Logo Type:</label><br>
                <select name="logo_type" id="logo_type">
                    <option>-- Select Logo Type --</option>
                    <option value="chest_only" <?php selected($fields['logo_type'], 'chest_only'); ?>>Chest only</option>
                    <option value="big_front" <?php selected($fields['logo_type'], 'big_front'); ?>>Big front</option>
                    <option value="custom_back" <?php selected($fields['logo_type'], 'custom_back'); ?>>Custom back</option>
                    <option value="same" <?php selected($fields['logo_type'], 'same'); ?>>Same</option>
                </select>
            </p>
            <p>
                <label for="mini_url">Mini URL:</label><br>
                <input type="text" name="mini_url" id="mini_url" value="<?php echo esc_attr($fields['mini_url']); ?>" />
            </p>
            <p>
                <label for="mini_header">Mini Header:</label><br>
                <input type="text" name="mini_header" id="mini_header"
                    value="<?php echo esc_attr($fields['mini_header']); ?>" />
            </p>
            <p>
                <label for="logo">Logo:</label><br>
                <input type="text" name="logo" id="logo" value="<?php echo esc_attr($fields['logo']); ?>" />
                <input type="button" id="logo_button" class="button upload-image-button" value="Upload Image" />
                <input type="button" id="remove_logo_button" class="button remove-image-button" value="Remove Image"
                    style="display: <?php echo $fields['logo'] ? 'inline-block' : 'none'; ?>;" />
            </p>
            <p>
                <img id="logo_preview" src="<?php echo esc_attr($fields['logo']); ?>"
                    style="max-width: 300px; display: <?php echo $fields['logo'] ? 'block' : 'none'; ?>;" />
            </p>
        </div>
        <?php
    }

    public function save_metabox_data($post_id)
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], basename(__FILE__))) {
            return $post_id;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // Save or update the fields
        $fields = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'status',
            'subscribed',
            'token',
            'address_1',
            'city',
            'dark_logo',
            'lighter_logo',
            'back_light',
            'back_dark',
            'logo_type',
            'mini_url',
            'mini_header',
            'invoice',
            'logo',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'token') {
                    $sanitized_value = base64_encode(sanitize_text_field($_POST[$field]));
                } else {
                    $sanitized_value = sanitize_text_field($_POST[$field]);
                }
                update_post_meta($post_id, $field, $sanitized_value);
            } else {
                delete_post_meta($post_id, $field);
            }
        }
    }

    public function enqueue_media_uploader()
    {
        wp_enqueue_media();
        wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/metabox.js', ['jquery'], null, true);
    }

    public function frontend_scripts()
    {

        // called asssets/css/clients.css
        wp_enqueue_style('style', get_template_directory_uri() . '/assets/css/clients.css', [], HELLO_ELEMENTOR_VERSION);

        wp_enqueue_media();
        wp_enqueue_script('jquery-validate', get_template_directory_uri() . '/assets/js/jquery.validate.min.js', ['jquery'], null, true);
        wp_enqueue_script('magnific-popup', get_template_directory_uri() . '/assets/js/jquery.magnific-popup.min.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);
        wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/metabox-frontend.js', ['jquery', 'jquery-validate', 'magnific-popup'], null, true);

        wp_localize_script(
            'script',
            'all_around_clients_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("client_nonce"),
            )
        );

        // Optionally, enqueue the media uploader styles
        wp_enqueue_style('wp-mediaelement');
    }

    // Add a custom column to the client list table
    function add_email_column_client($columns)
    {
        $columns['email'] = __('Email', 'textdomain');
        return $columns;
    }

    // Populate the custom column with the post meta value
    function show_email_column_client($column, $post_id)
    {
        if ($column === 'email') {
            $email = get_post_meta($post_id, 'email', true);
            echo esc_html($email);
        }
    }

    // Register Custom Post Type
    function register_post_type_cb()
    {

        $labels = array(
            'name' => _x('Client', 'Post Type General Name', 'text_domain'),
            'singular_name' => _x('Client', 'Post Type Singular Name', 'text_domain'),
            'menu_name' => __('Clients', 'text_domain'),
            'name_admin_bar' => __('Client', 'text_domain'),
            'archives' => __('Client Archives', 'text_domain'),
            'attributes' => __('Client Attributes', 'text_domain'),
            'parent_item_colon' => __('Parent Client:', 'text_domain'),
            'all_items' => __('All Clients', 'text_domain'),
            'add_new_item' => __('Add New Client', 'text_domain'),
            'add_new' => __('Add New', 'text_domain'),
            'new_item' => __('New Client', 'text_domain'),
            'edit_item' => __('Edit Client', 'text_domain'),
            'update_item' => __('Update Client', 'text_domain'),
            'view_item' => __('View Client', 'text_domain'),
            'view_items' => __('View Clients', 'text_domain'),
            'search_items' => __('Search Client', 'text_domain'),
            'not_found' => __('Not found', 'text_domain'),
            'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
            'featured_image' => __('Featured Image', 'text_domain'),
            'set_featured_image' => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image' => __('Use as featured image', 'text_domain'),
            'insert_into_item' => __('Insert into item', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
            'items_list' => __('Clients list', 'text_domain'),
            'items_list_navigation' => __('Clients list navigation', 'text_domain'),
            'filter_items_list' => __('Filter items list', 'text_domain'),
        );
        $args = array(
            'label' => __('Client', 'text_domain'),
            'description' => __('Post Type Description', 'text_domain'),
            'labels' => $labels,
            'supports' => array('title'),
            'taxonomies' => array(),
            'rewrite' => array('slug' => 'client', 'with_front' => false),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'capabilities' => array(
                'edit_post' => 'edit_client',
                'read_post' => 'read_client',
                'delete_post' => 'delete_client',
                'edit_posts' => 'edit_clients',
                'edit_others_posts' => 'edit_others_clients',
                'publish_posts' => 'publish_clients',
                'read_private_posts' => 'read_private_clients',
            ),
            'map_meta_cap' => true,
        );
        register_post_type('client', $args);

    }

    // Add custom capabilities to the administrator role
    function add_client_capabilities()
    {
        $roles = array('administrator');

        foreach ($roles as $the_role) {
            $role = get_role($the_role);

            $role->add_cap('edit_client');
            $role->add_cap('read_client');
            $role->add_cap('delete_client');
            $role->add_cap('edit_clients');
            $role->add_cap('edit_others_clients');
            $role->add_cap('publish_clients');
            $role->add_cap('read_private_clients');
        }
    }

    function client_post_type_link($post_link, $post)
    {
        if (is_object($post) && $post->post_type == 'client') {
            return home_url('client/' . $post->ID);
        }
        return $post_link;
    }

    function client_rewrite_rules()
    {
        add_rewrite_rule('^client/([0-9]+)/?$', 'index.php?post_type=client&p=$matches[1]', 'top');
    }

    function client_lists_shortcode()
    {
        ob_start();

        // Handle search if applicable
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = array(
            'post_type' => 'client',
            'posts_per_page' => 1,
            'paged' => $paged
        );

        if (!empty($search_query)) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'last_name',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'full_name',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'email',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                )
            );
        }

        $clients_query = new WP_Query($args);

        if ($clients_query->have_posts()): ?>
            <div class="client-search-box" style="text-align: right; margin-bottom: 20px;">
                <form method="get" action="<?php echo esc_url(home_url('/clients')); ?>">
                    <input type="text" name="search" placeholder="Search Clients" value="<?php echo esc_attr($search_query); ?>">
                    <input type="submit" value="Search">
                </form>
                <a href="<?php echo esc_url(home_url('/create-client/')); ?>">Add New Client</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="td_index"></th>
                        <th>Title</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $index = 1;
                    while ($clients_query->have_posts()):
                        $clients_query->the_post(); ?>
                        <tr>
                            <td class="td_index"><?php echo $index; ?></td>
                            <td><?php the_title(); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'email', true)); ?></td>
                        </tr>
                        <?php
                        $index++;
                    endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php
                echo paginate_links(
                    array(
                        'total' => $clients_query->max_num_pages,
                        'current' => $paged,
                        'format' => '?paged=%#%',
                        'show_all' => false,
                        'type' => 'plain',
                        'end_size' => 2,
                        'mid_size' => 2,
                        'prev_next' => true,
                        'prev_text' => __('« Prev'),
                        'next_text' => __('Next »'),
                        'add_args' => false,
                        'add_fragment' => '',
                    )
                );
                ?>
            </div>

            <?php wp_reset_postdata();
        else: ?>
            <p><?php _e('No clients found.'); ?></p>
        <?php endif;

        return ob_get_clean();
    }


}

// Initialize the class
new AllAroundClientsDB();