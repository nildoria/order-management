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

        wp_enqueue_script('jquery-ui-sortable', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('allaround-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery', 'jquery-ui-sortable'), filemtime(get_theme_file_path('/assets/js/main.js')), true);

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
            $current_domain = $_SERVER['SERVER_NAME'];

            if (strpos($current_domain, '.test') !== false || strpos($current_domain, 'lukpaluk.xyz') !== false) {
                $order_domain = 'https://main.lukpaluk.xyz';
            } else {
                $order_domain = 'https://allaround.co.il';
            }
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
                'user_role' => get_current_user_role(),
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
// require get_template_directory() . '/includes/classes/class-ajax.php';

require_once get_template_directory() . '/includes/classes/class-utility.php';
require_once get_template_directory() . '/includes/classes/class-clients.php';
require_once get_template_directory() . '/includes/classes/class-create-order.php';
require_once get_template_directory() . '/includes/classes/class-add-item.php';

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

// Change user role labels
function change_user_role_labels()
{
    global $wp_roles;

    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }

    // Change Contributor to Designer
    if (isset($wp_roles->roles['contributor'])) {
        $wp_roles->roles['contributor']['name'] = 'Designer';
        $wp_roles->role_names['contributor'] = 'Designer';
    }

    // Change Author to Employee
    if (isset($wp_roles->roles['author'])) {
        $wp_roles->roles['author']['name'] = 'Employee';
        $wp_roles->role_names['author'] = 'Employee';
    }
}
add_action('init', 'change_user_role_labels');

function reset_editor_capabilities()
{
    // Remove the editor role entirely and then re-add it
    remove_role('editor');

    // Re-add the editor role with its default capabilities
    add_role(
        'editor',
        __('Agent'),
        array(
            'read' => true, // Can view the dashboard
            'edit_posts' => true, // Can’t edit posts
            'edit_others_posts' => false, // Can’t edit others' posts
            'publish_posts' => false, // Can’t publish posts
            'delete_posts' => false, // Can’t delete posts
            'delete_others_posts' => false, // Can’t delete others' posts
            'edit_published_posts' => false, // Can’t edit published posts
            'delete_published_posts' => false, // Can’t delete published posts
            'read_private_posts' => true, // Can view private posts
            'read_post' => true, // Can read individual posts
            // Add other capabilities as needed
        )
    );
}
add_action('init', 'reset_editor_capabilities');


function redirect_to_login_if_not_logged_in()
{
    if (!is_user_logged_in() && is_single()) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'redirect_to_login_if_not_logged_in');


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



function add_cors_http_header()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}
add_action('init', 'add_cors_http_header');

/**
 * Custom Metaboxes for Order Post
 */
function add_order_manage_metabox()
{
    add_meta_box(
        'order_manage_metabox',
        'Order Management',
        'order_manage_metabox_content',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_order_manage_metabox');

function order_manage_metabox_content($post)
{
    // Add a nonce field so we can check for it later.
    wp_nonce_field('order_manage_metabox_nonce', 'order_manage_metabox_nonce');

    // Use get_post_meta to retrieve an existing value from the database.
    $order_manage_general_comment = get_post_meta($post->ID, '_order_manage_general_comment', true);
    $order_manage_designer_notes = get_post_meta($post->ID, '_order_manage_designer_notes', true);

    // Display the form, using the current value.
    echo '<label for="order_manage_general_comment">Order Notes</label>';
    echo '<input type="text" readonly id="order_manage_general_comment" name="order_manage_general_comment" value="' . ($order_manage_general_comment) . '" style="width:100%;" />';
    echo '<br>';
    echo '<br>';
    echo '<hr>';
    echo '<br>';
    echo '<label for="order_manage_designer_notes">Designer Notes</label>';
    echo '<input type="text" readonly id="order_manage_designer_notes" name="order_manage_designer_notes" value="' . ($order_manage_designer_notes) . '" style="width:100%;" />';
}

function save_order_manage_metabox($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['order_manage_metabox_nonce'])) {
        return $post_id;
    }

    $nonce = $_POST['order_manage_metabox_nonce'];

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($nonce, 'order_manage_metabox_nonce')) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions.
    if ('post' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Sanitize user input.
    $order_manage_general_comment = sanitize_text_field($_POST['order_manage_general_comment']);
    $order_manage_designer_notes = sanitize_text_field($_POST['order_manage_designer_notes']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_order_manage_general_comment', $order_manage_general_comment);
    update_post_meta($post_id, '_order_manage_designer_notes', $order_manage_designer_notes);
}
add_action('save_post', 'save_order_manage_metabox');


function save_order_general_comment()
{
    // Check nonce for security
    check_ajax_referer('order_management_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $order_comment = sanitize_textarea_field($_POST['order_general_comment']);

    if (empty($post_id) || (empty($order_comment) && empty($_FILES['order_extra_attachments']))) {
        wp_send_json_error('Invalid post ID or comment.');
    }

    $existing_attachments = get_post_meta($post_id, '_order_extra_attachments', true);
    $attachments = is_array($existing_attachments) ? $existing_attachments : array();

    if (!empty($_FILES['order_extra_attachments'])) {
        $new_attachments = handle_file_uploads($_FILES['order_extra_attachments'], $post_id);
        if (is_wp_error($new_attachments)) {
            wp_send_json_error($new_attachments->get_error_message());
        }
        $attachments = array_merge($attachments, $new_attachments);
        update_post_meta($post_id, '_order_extra_attachments', $attachments);
    }

    if (!empty($order_comment)) {
        update_post_meta($post_id, '_order_manage_general_comment', $order_comment);
    } else {
        $order_comment = get_post_meta($post_id, '_order_manage_general_comment', true);
    }

    wp_send_json_success(
        array(
            'order_general_comment' => nl2br($order_comment),
            'attachments' => $attachments
        )
    );
}
add_action('wp_ajax_save_order_general_comment', 'save_order_general_comment');
add_action('wp_ajax_nopriv_save_order_general_comment', 'save_order_general_comment');

function delete_order_attachment()
{
    // Check nonce for security
    check_ajax_referer('order_management_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $attachment_id = intval($_POST['attachment_id']);
    $attachment_type = sanitize_text_field($_POST['attachment_type']);

    if (empty($post_id) || empty($attachment_id) || empty($attachment_type)) {
        wp_send_json_error('Invalid post ID, attachment ID, or attachment type.');
    }

    $meta_key = $attachment_type === 'designer' ? '_order_designer_extra_attachments' : '_order_extra_attachments';
    $attachments = get_post_meta($post_id, $meta_key, true);

    if (is_array($attachments)) {
        foreach ($attachments as $key => $attachment) {
            if ($attachment['id'] == $attachment_id) {
                unset($attachments[$key]);
                break;
            }
        }
        update_post_meta($post_id, $meta_key, array_values($attachments));
    }

    wp_send_json_success();
}
add_action('wp_ajax_delete_order_attachment', 'delete_order_attachment');
add_action('wp_ajax_nopriv_delete_order_attachment', 'delete_order_attachment');


function save_order_designer_notes()
{
    // Check nonce for security
    check_ajax_referer('order_management_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $order_designer_notes = sanitize_textarea_field($_POST['order_designer_notes']);

    if (empty($post_id) || (empty($order_designer_notes) && empty($_FILES['order_designer_extra_attachments']))) {
        wp_send_json_error('Invalid post ID or notes.');
    }

    $existing_attachments = get_post_meta($post_id, '_order_designer_extra_attachments', true);
    $attachments = is_array($existing_attachments) ? $existing_attachments : array();

    if (!empty($_FILES['order_designer_extra_attachments'])) {
        $new_attachments = handle_file_uploads($_FILES['order_designer_extra_attachments'], $post_id);
        if (is_wp_error($new_attachments)) {
            wp_send_json_error($new_attachments->get_error_message());
        }
        $attachments = array_merge($attachments, $new_attachments);
        update_post_meta($post_id, '_order_designer_extra_attachments', $attachments);
    }

    if (!empty($order_designer_notes)) {
        update_post_meta($post_id, '_order_manage_designer_notes', $order_designer_notes);
    } else {
        $order_designer_notes = get_post_meta($post_id, '_order_manage_designer_notes', true);
    }

    wp_send_json_success(
        array(
            'order_designer_notes' => nl2br($order_designer_notes), // Convert new lines to <br> tags
            'attachments' => $attachments
        )
    );
}
add_action('wp_ajax_save_order_designer_notes', 'save_order_designer_notes');
add_action('wp_ajax_nopriv_save_order_designer_notes', 'save_order_designer_notes');



/**
 * Handle file uploads for order attachments
 @returns array
 */
function handle_file_uploads($files, $post_id)
{
    ini_set('memory_limit', '1024M'); // Temporarily increase memory limit

    $attachments = array();

    foreach ($files['name'] as $key => $value) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file = array(
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            );

            // Validate the image dimensions
            $validation_result = validate_image_dimensions($file, 3000, 3000);
            if (is_wp_error($validation_result)) {
                // Resize the image if validation failed
                resize_image($file, 3000, 3000);

                // Validate the resized image dimensions again
                $validation_result = validate_image_dimensions($file, 3000, 3000);
                if (is_wp_error($validation_result)) {
                    return $validation_result;
                }
            }

            // Upload the file and get the attachment ID
            $attachment_id = media_handle_sideload($file, $post_id);

            if (is_wp_error($attachment_id)) {
                continue;
            } else {
                $attachment_url = wp_get_attachment_url($attachment_id);
                $attachments[] = array(
                    'id' => $attachment_id,
                    'url' => $attachment_url,
                    'name' => $file['name']
                );
            }
        }
    }

    return $attachments;
}

function validate_image_dimensions($file, $max_width, $max_height)
{
    list($original_width, $original_height) = getimagesize($file['tmp_name']);

    // Check if the image dimensions exceed the maximum allowed dimensions
    if ($original_width > $max_width || $original_height > $max_height) {
        return new WP_Error('image_too_large', 'Something went wrong, please try again.');
    }

    return true;
}

function resize_image($file, $max_width = 2500, $max_height = 2500)
{
    list($original_width, $original_height) = getimagesize($file['tmp_name']);

    // Check if the image dimensions exceed the maximum allowed dimensions
    if ($original_width <= $max_width && $original_height <= $max_height) {
        // No resizing needed
        return;
    }

    $scale = min($max_width / $original_width, $max_height / $original_height);

    $new_width = ceil($scale * $original_width);
    $new_height = ceil($scale * $original_height);

    $new_image = imagecreatetruecolor($new_width, $new_height);
    $source_image = imagecreatefromjpeg($file['tmp_name']);
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

    imagejpeg($new_image, $file['tmp_name'], 100); // Save the resized image

    imagedestroy($new_image);
    imagedestroy($source_image);
}


/**
 * Handle order shipping details meta update
 @returns
 */
function update_post_shipping_details()
{
    // Check nonce for security
    check_ajax_referer('order_management_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? sanitize_text_field(absint($_POST['post_id'])) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['address_1']) ? sanitize_text_field($_POST['address_1']) : '';
    $address_2 = isset($_POST['address_2']) ? sanitize_text_field($_POST['address_2']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';

    if (empty($post_id) || empty($phone)) {
        wp_send_json_error(
            array(
                "message_type" => 'reqular',
                "message" => esc_html__("Invalid post ID or phone number.", "hello-elementor")
            )
        );
        wp_die();
    }

    $shipping_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'address_1' => $address,
        'address_2' => $address_2,
        'city' => $city,
        'phone' => $phone
    ];

    update_post_meta($post_id, 'shipping', $shipping_data);

    wp_send_json_success(
        array(
            "shipping_details" => $shipping_data,
            "message" => "Order Shipping details successfully updated!"
        )
    );
    wp_die();

}
add_action('wp_ajax_update_post_shipping_details', 'update_post_shipping_details');
add_action('wp_ajax_nopriv_update_post_shipping_details', 'update_post_shipping_details');


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

    //TODO: Remove the Staging credentials when going live
    // Live credentials
    switch ($domain) {
        case 'https://allaround.co.il':
            $consumer_key = 'ck_c1785b09529d8d557cb2464de703be14f5db60ab';
            $consumer_secret = 'cs_92137acaafe08fb05efd20f846c4e6bd5c5d0834';
            break;
        case 'https://sites.allaround.co.il':
            $consumer_key = 'ck_30ee118f1704c40988482bf4fc688dcfd40ee56a';
            $consumer_secret = 'cs_c182834653750f23eb79c090d44741f3680e0a30';
            break;
        case 'https://main.lukpaluk.xyz':
            $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
            $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
            break;
        case 'https://min.lukpaluk.xyz':
            $consumer_key = 'ck_1d40409af527f48fd380cbdbbc84f6b96c9b5842';
            $consumer_secret = 'cs_6b95c1747901e41a8fb5b1fa863d476cd31d820b';
            break;
        case 'https://flash.allaround.co.il':
            $consumer_key = 'ck_68b709cb56ddc7704d68bb4fdbac0e89d708c651';
            $consumer_secret = 'cs_aaceb9b2a30adef0a6f16fe6842252d3747e693f';
            break;
        case 'https://fs.lukpaluk.xyz':
            $consumer_key = 'ck_88186089fa2d579b8c26ddc7d8acfe651da56f0f';
            $consumer_secret = 'cs_fa7a98acfb77960faa3b5d5e889f08840d0db584';
            break;
        case 'https://allaround.test':
            $consumer_key = 'ck_481effc1659aae451f1b6a2e4f2adc3f7bc3829f';
            $consumer_secret = 'cs_b0af5f272796d15581feb8ed52fbf0d5469c67b4';
            break;
        case 'https://localhost/ministore':
            $consumer_key = 'ck_53d09905b34decf87745f1095bae29f60e1d4059';
            $consumer_secret = 'cs_a3d20d1474717fc1f533813d57841563115d4b16';
            break;
        default:
            $domain = 'https://allaround.co.il';
            $consumer_key = 'ck_c1785b09529d8d557cb2464de703be14f5db60ab';
            $consumer_secret = 'cs_92137acaafe08fb05efd20f846c4e6bd5c5d0834';
            break;
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
            // Get the current shipping cost
            $current_shipping_total = $order->shipping_lines[0]->total;

            error_log(print_r($current_shipping_total, true));
            error_log(print_r($shipping_method, true));

            // If shipping method is 'getpackage', keep the existing shipping cost
            if ($shipping_method === 'getpackage') {
                $shipping_total = $current_shipping_total;
            } else {
                // Calculate the new shipping cost based on method ID if not 'getpackage'
                $shipping_total = calculate_shipping_cost($shipping_method);
            }

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
                update_post_meta($post_id, 'shipping_method_title', sanitize_text_field($shipping_method_title));

                // Delete the transient to reset cached data
                $transient_key = 'order_details_' . $order_id;
                delete_transient($transient_key);

                wp_send_json_success(
                    array(
                        'message' => 'Shipping method updated successfully.',
                        'shipping_method' => $shipping_method,
                        'shipping_total' => number_format((float) $shipping_total, 2, '.', '')
                    )
                );
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
            'permission_callback' => function ($request) {
                // Check if the request is coming from the same origin
                if ($_SERVER['HTTP_ORIGIN'] === get_site_url()) {
                    return true; // Allow requests from the same origin
                }

                // Otherwise, require Basic Authentication
                $auth_header = $request->get_header('Authorization');

                if (empty($auth_header) || strpos($auth_header, 'Basic ') !== 0) {
                    return new WP_Error('authorization', 'Authorization header missing or invalid', array('status' => 403));
                }

                // Extract and decode the username and password
                $auth_creds = base64_decode(substr($auth_header, 6));
                list($username, $password) = explode(':', $auth_creds);

                // Validate the username and password
                $valid_username = 'OmAdmin';
                if (strpos($_SERVER['HTTP_HOST'], 'allaround.co.il') !== false) {
                    $valid_password = 'Vlh4 F7Sw Zu26 ShUG 6AYu DuRI';
                } elseif (strpos($_SERVER['HTTP_HOST'], 'lukpaluk.xyz') !== false) {
                    $valid_password = 'vZmm GYw4 LKDg 4ry5 BMYC 4TMw';
                } else {
                    $valid_password = 'Qj0p rsPu eU2i Fzco pwpX eCPD';
                }

                if ($username === $valid_username && $password === $valid_password) {
                    return true; // Authentication successful
                }

                return new WP_Error('authorization', 'Invalid username or password', array('status' => 403));
            },
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
    $client_id = get_post_meta($post->ID, 'client_id', true);
    $agent_id = get_post_meta($post->ID, 'agent_id', true);
    $shipping_method = get_post_meta($post->ID, 'shipping_method', true);
    $shipping_method_title = get_post_meta($post->ID, 'shipping_method_title', true);
    $order_source = get_post_meta($post->ID, 'order_source', true);
    $items = get_post_meta($post->ID, 'items', true);
    $billing = get_post_meta($post->ID, 'billing', true);
    $shipping = get_post_meta($post->ID, 'shipping', true);
    $payment_method = get_post_meta($post->ID, 'payment_method', true);
    $payment_method_title = get_post_meta($post->ID, 'payment_method_title', true);
    $order_site_url = get_post_meta($post->ID, 'site_url', true);


    echo '<label for="order_id">Order ID:</label>';
    echo '<input type="text" id="order_id" name="order_id" readonly value="' . esc_attr($order_id) . '" /><br>';

    echo '<label for="order_number">Order Number:</label>';
    echo '<input type="text" id="order_number" name="order_number" readonly value="' . esc_attr($order_number) . '" /><br>';

    echo '<label for="client_id">Client ID:</label>';
    echo '<input type="text" id="client_id" name="client_id" readonly value="' . esc_attr($client_id) . '" /><br>';

    echo '<label for="agent_id">Agent ID:</label>';
    echo '<input type="text" id="agent_id" name="agent_id" readonly value="' . esc_attr($agent_id) . '" /><br>';

    echo '<label for="order_status">Order Status:</label>';
    echo '<input type="text" readonly id="order_status" name="order_status" value="' . esc_attr($order_status) . '" /><br>';

    echo '<label for="shipping_method">Shipping Method:</label>';
    echo '<input type="text" readonly id="shipping_method" name="shipping_method" value="' . esc_attr($shipping_method) . '" /><br>';

    echo '<label for="shipping_method_title">Shipping Method Title:</label>';
    echo '<input type="text" readonly id="shipping_method_title" name="shipping_method_title" value="' . esc_attr($shipping_method_title) . '" /><br>';

    echo '<label for="order_source">Order Source:</label>';
    echo '<select disabled name="order_source" id="order_source">';
    echo '<option value="">-- Order Source --</option>';
    echo '<option value="mainSite_order" ' . selected($order_source, 'mainSite_order', false) . '>Main Site</option>';
    echo '<option value="miniSite_order" ' . selected($order_source, 'miniSite_order', false) . '>Mini Site</option>';
    echo '<option value="manual_order" ' . selected($order_source, 'manual_order', false) . '>Manual Order</option>';
    echo '<option value="flashSale_order" ' . selected($order_source, 'flashSale_order', false) . '>FlashSale Order</option>';
    echo '</select>';

    // Display Ordered Items
    echo '<h3>Ordered Items</h3>';
    if (!empty($items)) {
        foreach ($items as $index => $item) {
            echo '<div class="item-group">';
            echo '<label>Item ID:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['item_id']) . '" /><br>';
            echo '<label>Product ID:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['product_id']) . '" /><br>';
            echo '<label>Product Name:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['product_name']) . '" /><br>';
            echo '<label>Quantity:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['quantity']) . '" /><br>';
            echo '<label>Total:</label>';
            echo '<input type="text" readonly value="' . esc_attr($item['total']) . '" /><br>';
            echo '<label>Printing Note:</label>';
            echo '<textarea readonly data-item_id="' . esc_attr($item['item_id']) . '">' . esc_attr($item['printing_note']) . '</textarea><br>';
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
    echo '<input type="text" id="order_site_url" name="order_site_url" readonly value="' . esc_attr($order_site_url) . '" />';
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

    if (isset($_POST['shipping_method_title'])) {
        update_post_meta($post_id, 'shipping_method_title', sanitize_text_field($_POST['shipping_method_title']));
    }

    if (isset($_POST['order_source'])) {
        update_post_meta($post_id, 'order_source', sanitize_text_field($_POST['order_source']));
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

    error_log(print_r($order_data, true));

    $order_number = isset($order_data['order_number']) ? str_replace(' ', '', sanitize_text_field($order_data['order_number'])) : '';
    $order_id = isset($order_data['order_id']) ? str_replace(' ', '', sanitize_text_field($order_data['order_id'])) : '';

    $order_status = isset($order_data['order_status']) ? $order_data['order_status'] : 'New Order';

    // Get order source and total price from order data
    $order_source = isset($order_data['order_source']) ? $order_data['order_source'] : '';
    $total_price = isset($order_data['total']) ? floatval($order_data['total']) : 0;
    $order_date = isset($order_data['date_created']) ? $order_data['date_created'] : current_time('mysql');

    $shipping_method_id = '';
    $shipping_method_title = '';
    if (!empty($order_data['shipping_lines']) && is_array($order_data['shipping_lines'])) {
        foreach ($order_data['shipping_lines'] as $shipping_line) {
            if (isset($shipping_line['method_id'])) {
                $shipping_method_id = $shipping_line['method_id'];
                $shipping_method_title = $shipping_line['method_title'];

                // Check if the shipping method title matches the specified string
                if ($shipping_method_title === 'איסוף עצמי מ- הלהב 2, חולון (1-3 ימי עסקים) - חינם!') {
                    $shipping_method_id = 'local_pickup';
                }

                break; // Assuming you want the first shipping method ID
            }
        }
    }

    // Fill empty shipping fields with values from billing
    if (!empty($order_data['billing']) && !empty($order_data['shipping'])) {
        foreach ($order_data['billing'] as $key => $value) {
            if (empty($order_data['shipping'][$key]) && !empty($value)) {
                $order_data['shipping'][$key] = $value;
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
            'post_date' => $order_date,
        )
    );

    // Check if post was created successfully
    if ($post_id == 0) {
        return new WP_Error('post_not_created', 'There was an error creating the post.', array('status' => 500));
    }

    // Save the order status to post meta
    update_post_meta($post_id, 'order_status', $order_status);
    update_post_meta($post_id, 'order_id', $order_id);
    update_post_meta($post_id, 'order_number', $order_number);
    update_post_meta($post_id, 'shipping_method', $shipping_method_id);
    update_post_meta($post_id, 'shipping_method_title', $shipping_method_title);
    update_post_meta($post_id, 'items', isset($order_data['items']) ? $order_data['items'] : $order_data['line_items']);
    update_post_meta($post_id, 'billing', $order_data['billing'] ? $order_data['billing'] : []);
    update_post_meta($post_id, 'shipping', $order_data['shipping'] ? $order_data['shipping'] : []);
    update_post_meta($post_id, 'payment_method', $order_data['payment_method'] ? $order_data['payment_method'] : []);
    update_post_meta($post_id, 'payment_method_title', $order_data['payment_method_title'] ? $order_data['payment_method_title'] : []);
    update_post_meta($post_id, 'site_url', $order_data['site_url'] ? $order_data['site_url'] : []);
    if (isset($order_data['order_type'])) {
        update_post_meta($post_id, 'order_type', $order_data['order_type'] ? $order_data['order_type'] : []);
    }

    // Set the order type to company if the order source is miniSite_order and send to client to make it company
    if (!empty($order_source) && $order_source === 'miniSite_order') {
        $customer_note = isset($order_data['customer_note']) ? $order_data['customer_note'] : '';
        update_post_meta($post_id, '_order_manage_general_comment', $customer_note);
        update_post_meta($post_id, 'order_type', 'company');
        $order_data['client_type'] = 'company';

        $dark_logo = isset($order_data['dark_logo']) ? $order_data['dark_logo'] : '';
        $lighter_logo = isset($order_data['lighter_logo']) ? $order_data['lighter_logo'] : '';
        if (!empty($dark_logo) || !empty($lighter_logo)) {
            // Prepare the attachments array
            $designer_extra_attachments = array();

            // Add dark logo if exists
            if (!empty($dark_logo)) {
                $designer_extra_attachments[] = array(
                    'name' => 'Dark Logo',
                    'url' => $dark_logo,
                );
            }

            // Add lighter logo if exists
            if (!empty($lighter_logo)) {
                $designer_extra_attachments[] = array(
                    'name' => 'Lighter Logo',
                    'url' => $lighter_logo,
                );
            }

            // Update _order_designer_extra_attachments meta for the post
            if (!empty($designer_extra_attachments)) {
                update_post_meta($post_id, '_order_designer_extra_attachments', $designer_extra_attachments);
            }
        }

        // Schedule the mockup upload to run after 5seconds (or any delay you need)
        if (!wp_next_scheduled('upload_mockups_to_ftp', array($order_id, $order_data))) {
            wp_schedule_single_event(time() + 5, 'upload_mockups_to_ftp', array($order_id, $order_data));
        }
    }

    $agent_id = isset($order_data['agent_id']) ? $order_data['agent_id'] : '';
    if (!empty($order_source) && $order_source === 'manual_order' && !empty($agent_id)) {
        update_post_meta($post_id, 'agent_id', $agent_id);
        // $agent_id is the user ID of the agent, I need to set this user as the post author
        wp_update_post(array('ID' => $post_id, 'post_author' => $agent_id));
    }
    do_action('all_around_create_client', $post_id, $order_data, $order_id, $order_number);

    // get the site_url meta
    $order_domain = get_post_meta($post_id, 'site_url', true);

    $client_id = get_post_meta($post_id, 'client_id', true);

    // call the function get_client_token to get the token from the order from woocommerce order source on main site
    if (!empty($order_id) && !empty($order_domain) && !empty($client_id)) {
        set_client_token($order_id, $client_id, $order_domain);
    }

    // Call the function to handle order source and update related meta
    if (!empty($order_source) && !empty($client_id)) {
        handle_order_source($post_id, $client_id, $order_source, $total_price);
    }

    // Handle FlashSale ID
    if (!empty($order_source) && $order_source === 'flashSale_order') {
        $flash_id = isset($order_data['flash_id']) ? $order_data['flash_id'] : '';
        if (!empty($flash_id)) {
            update_post_meta($post_id, 'flash_id', $flash_id);
        }
    }

    // Send data to webhook
    send_order_data_to_webhook($order_id, $order_number, $order_data, get_permalink($post_id));

    // Return the ID of the new post
    return new WP_REST_Response(array('post_id' => $post_id, 'post_url' => get_permalink($post_id)), 200);
}


// Hook into the scheduled event to process mockup uploads
add_action('upload_mockups_to_ftp', 'upload_mockups_to_ftp_callback', 10, 2);

/**
 * Handles the mockup uploads after the order is processed.
 *
 * @param int $order_id The ID of the order.
 * @param array $order_data The order data containing mockup information.
 */
function upload_mockups_to_ftp_callback($order_id, $order_data)
{
    // Iterate over the items and process each item's mockup_thumbnail for FTP upload
    if (isset($order_data['items']) && is_array($order_data['items'])) {
        foreach ($order_data['items'] as $item) {
            if (isset($item['mockup_thumbnail']) && !empty($item['mockup_thumbnail'])) {
                $mockup_url = $item['mockup_thumbnail'];
                $product_id = $item['id'];
                $mockup_version = 'V1'; // Assuming version 1 for now, adjust as needed

                // Use the function from upload.php to upload to the FTP
                handle_mockup_upload_to_ftp($mockup_url, $order_id, $product_id, $mockup_version);
            }
        }
    }
}

/**
 * Uploads a mockup to the FTP server.
 *
 * @param string $file_url The URL of the mockup file.
 * @param int $order_id The ID of the order.
 * @param int $product_id The ID of the product.
 * @param string $version The version of the mockup (e.g., V1, V2).
 */
function handle_mockup_upload_to_ftp($file_url, $order_id, $product_id, $version)
{
    // FTP server details
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';

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

    // Define the directory structure
    $remote_directory = "/public_html/artworks/$order_id/$product_id/$version/";

    // Check if directory exists, if not, create it
    if (!@ftp_chdir($ftp_conn, $remote_directory)) {
        $parts = explode('/', $remote_directory);
        $current_dir = '';
        foreach ($parts as $part) {
            if (empty($part))
                continue;
            $current_dir .= '/' . $part;
            if (!@ftp_chdir($ftp_conn, $current_dir)) {
                ftp_mkdir($ftp_conn, $current_dir);
            }
        }
        ftp_chdir($ftp_conn, $remote_directory);
    }

    // Ensure download_url is defined before using it
    if (!function_exists('download_url')) {
        function download_url($url)
        {
            // Use PHP's tempnam if wp_tempnam is unavailable
            if (function_exists('wp_tempnam')) {
                $temp_file = wp_tempnam($url);
            } else {
                $temp_file = tempnam(sys_get_temp_dir(), 'wp_tmp');
            }

            if (!$temp_file) {
                return new WP_Error('temp_file_failed', __('Failed to create a temporary file.'));
            }

            $response = wp_remote_get($url, array('timeout' => 300, 'stream' => true, 'filename' => $temp_file));
            if (is_wp_error($response)) {
                @unlink($temp_file);
                return $response;
            }

            if (200 != wp_remote_retrieve_response_code($response)) {
                @unlink($temp_file);
                return new WP_Error('invalid_response', __('Failed to download file.'), wp_remote_retrieve_response_message($response));
            }

            return $temp_file;
        }
    }


    // Download the file from the URL to a temporary location
    $temp_file = download_url($file_url);
    if (is_wp_error($temp_file)) {
        ftp_close($ftp_conn);
        error_log("Failed to download the mockup file: $file_url");
        return;
    }

    // Generate a unique ID for the file
    $unique_id = uniqid();

    // Define the remote file path with the new filename
    $new_filename = $product_id . '-' . $version . '-' . $unique_id . '.jpeg';
    $remote_file = $remote_directory . $new_filename;

    // Upload the file to the FTP server
    if (ftp_put($ftp_conn, $remote_file, $temp_file, FTP_BINARY)) {
        $file_path = "https://lukpaluk.xyz/artworks/$order_id/$product_id/$version/$new_filename";
        error_log("Mockup successfully uploaded: $file_path");
    } else {
        error_log("Failed to upload the mockup: $file_url");
    }

    // Clean up temporary file
    @unlink($temp_file);

    // Close the FTP connection after each upload to ensure it doesn't timeout between items
    ftp_close($ftp_conn);
}


/**
 * Set Client Table Data.
 */
function handle_order_source_action_callback()
{
    // Ensure all expected parameters are received
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
    $order_source = isset($_POST['order_source']) ? sanitize_text_field($_POST['order_source']) : '';
    $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;

    if ($post_id && $client_id && $order_source && $total_price) {
        // Call your function to handle the order source
        handle_order_source($post_id, $client_id, $order_source, $total_price, false);

        wp_send_json_success('Order source handled successfully');
    } else {
        wp_send_json_error('Invalid data provided');
    }
}
add_action('wp_ajax_handle_order_source_action', 'handle_order_source_action_callback');
add_action('wp_ajax_nopriv_handle_order_source_action', 'handle_order_source_action_callback');


function handle_order_source($post_id, $client_id, $order_source, $total_price, $increment_count = true)
{
    // Handle different order sources
    switch ($order_source) {
        case 'miniSite_order':
            if ($increment_count) {
                // Update the miniSite_orders meta
                $miniSite_orders = get_post_meta($client_id, 'miniSite_orders', true);
                $miniSite_orders = !empty($miniSite_orders) ? intval($miniSite_orders) + 1 : 1;
                update_post_meta($client_id, 'miniSite_orders', $miniSite_orders);
            }

            // Update the miniSite_order_value meta with the total price
            $miniSite_order_value = get_post_meta($client_id, 'miniSite_order_value', true);
            $miniSite_order_value = !empty($miniSite_order_value) ? floatval($miniSite_order_value) + $total_price : $total_price;
            update_post_meta($client_id, 'miniSite_order_value', $miniSite_order_value);

            update_post_meta($post_id, 'order_source', 'miniSite_order');
            break;

        case 'flashSale_order':
            if ($increment_count) {
                // Update the flashSale_orders meta
                $flashSale_orders = get_post_meta($client_id, 'flashSale_orders', true);
                $flashSale_orders = !empty($flashSale_orders) ? intval($flashSale_orders) + 1 : 1;
                update_post_meta($client_id, 'flashSale_orders', $flashSale_orders);
            }

            // Update the flashSale_order_value meta with the total price
            $flashSale_order_value = get_post_meta($client_id, 'flashSale_order_value', true);
            $flashSale_order_value = !empty($flashSale_order_value) ? floatval($flashSale_order_value) + $total_price : $total_price;
            update_post_meta($client_id, 'flashSale_order_value', $flashSale_order_value);

            update_post_meta($post_id, 'order_source', 'flashSale_order');
            break;

        case 'manual_order':
            if ($increment_count) {
                // Update the manual_orders meta
                $manual_orders = get_post_meta($client_id, 'manual_orders', true);
                $manual_orders = !empty($manual_orders) ? intval($manual_orders) + 1 : 1;
                update_post_meta($client_id, 'manual_orders', $manual_orders);
            }

            // Update the manual_order_value meta with the total price
            $manual_order_value = get_post_meta($client_id, 'manual_order_value', true);
            $manual_order_value = !empty($manual_order_value) ? floatval($manual_order_value) + $total_price : $total_price;
            update_post_meta($client_id, 'manual_order_value', $manual_order_value);

            update_post_meta($post_id, 'order_source', 'manual_order');
            break;

        case 'mainSite_order':
        default:
            if ($increment_count) {
                // Update the mainSite_orders meta
                $mainSite_orders = get_post_meta($client_id, 'mainSite_orders', true);
                $mainSite_orders = !empty($mainSite_orders) ? intval($mainSite_orders) + 1 : 1;
                update_post_meta($client_id, 'mainSite_orders', $mainSite_orders);
            }

            // Update the mainSite_order_value meta with the total price
            $mainSite_order_value = get_post_meta($client_id, 'mainSite_order_value', true);
            $mainSite_order_value = !empty($mainSite_order_value) ? floatval($mainSite_order_value) + $total_price : $total_price;
            update_post_meta($client_id, 'mainSite_order_value', $mainSite_order_value);

            update_post_meta($post_id, 'order_source', 'mainSite_order');
            break;
    }
}

/**
 * Send Order Data Webhook after Order.
 */
function send_order_data_to_webhook($order_id, $order_number, $order_data, $post_url)
{

    $root_domain = home_url();
    $webhook_url = "";

    if (strpos($root_domain, '.test') !== false || strpos($root_domain, 'lukpaluk.xyz') !== false) {
        $webhook_url = "https://hook.us1.make.com/wxcd9nyap2xz434oevuike8sydbfx5qn";
    } else {
        $webhook_url = "https://hook.eu1.make.com/n4vh84cwbial6chqwmm2utvsua7u8ck3";
    }

    $client_details = isset($order_data['billing']) ? $order_data['billing'] : array();
    $total_price = isset($order_data['total']) ? $order_data['total'] : 0;
    $order_source = isset($order_data['order_source']) ? $order_data['order_source'] : '';
    $order_type = isset($order_data['order_type']) ? $order_data['order_type'] : '';
    $shipping_method_title = isset($order_data['shipping_lines'][0]['method_title']) ? $order_data['shipping_lines'][0]['method_title'] : '';
    $payment_details = isset($order_data['payment_data']) ? $order_data['payment_data'] : '';

    // error_log(print_r($client_details, true));

    $webhook_data = array(
        'om_status' => 'new_order',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'client_details' => array(
            'firstName' => isset($client_details['first_name']) ? $client_details['first_name'] : '',
            'lastName' => isset($client_details['last_name']) ? $client_details['last_name'] : '',
            'email' => isset($client_details['email']) ? $client_details['email'] : '',
            'phone' => isset($client_details['phone']) ? $client_details['phone'] : '',
            'invoice' => isset($client_details['company']) ? $client_details['company'] : '',
        ),
        'shipping_method_title' => $shipping_method_title,
        'total_price' => $total_price,
        'order_source' => $order_source,
        'order_type' => $order_type,
        'post_url' => $post_url,
        'payment_details' => array(
            'invoice' => isset($payment_details['invoice']) ? $payment_details['invoice'] : '',
            'receipt' => isset($payment_details['receipt']) ? $payment_details['receipt'] : '',
            'order_date' => isset($payment_details['order_date']) ? $payment_details['order_date'] : '',
            'wire_transfer' => isset($payment_details['wire_transfer']) ? $payment_details['wire_transfer'] : '',
            'credit_card' => isset($payment_details['credit_card']) ? $payment_details['credit_card'] : '',
            'cash' => isset($payment_details['cash']) ? $payment_details['cash'] : '',
            'no_invoice' => isset($payment_details['no_invoice']) ? $payment_details['no_invoice'] : '',
        ),
    );

    $response = wp_remote_post(
        $webhook_url,
        array(
            'method' => 'POST',
            'timeout' => 30,
            'sslverify' => false,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($webhook_data),
        )
    );

    if (is_wp_error($response)) {
        error_log('Webhook request failed: ' . $response->get_error_message());
    } else {
        error_log('Webhook request successful: ' . $response['message']);
    }
}


/**
 * Order Management Order List.
 *
 */
function fetch_order_details($order_id, $domain)
{
    $transient_key = 'order_details_' . $order_id;
    $order_data = get_transient($transient_key);

    if (false === $order_data) {
        error_log("Fetching new order details for: $order_id");

        //TODO: Remove the Staging credentials when going live
        // Live credentials
        switch ($domain) {
            case 'https://allaround.co.il':
                $consumer_key = 'ck_c1785b09529d8d557cb2464de703be14f5db60ab';
                $consumer_secret = 'cs_92137acaafe08fb05efd20f846c4e6bd5c5d0834';
                break;
            case 'https://sites.allaround.co.il':
                $consumer_key = 'ck_30ee118f1704c40988482bf4fc688dcfd40ee56a';
                $consumer_secret = 'cs_c182834653750f23eb79c090d44741f3680e0a30';
                break;
            case 'https://main.lukpaluk.xyz':
                $consumer_key = 'ck_c18ff0701de8832f6887537107b75afce3914b4c';
                $consumer_secret = 'cs_cbc5250dea649ae1cc98fe5e2e81e854a60dacf4';
                break;
            case 'https://min.lukpaluk.xyz':
                $consumer_key = 'ck_1d40409af527f48fd380cbdbbc84f6b96c9b5842';
                $consumer_secret = 'cs_6b95c1747901e41a8fb5b1fa863d476cd31d820b';
                break;
            case 'https://flash.allaround.co.il':
                $consumer_key = 'ck_68b709cb56ddc7704d68bb4fdbac0e89d708c651';
                $consumer_secret = 'cs_aaceb9b2a30adef0a6f16fe6842252d3747e693f';
                break;
            case 'https://fs.lukpaluk.xyz':
                $consumer_key = 'ck_88186089fa2d579b8c26ddc7d8acfe651da56f0f';
                $consumer_secret = 'cs_fa7a98acfb77960faa3b5d5e889f08840d0db584';
                break;
            case 'https://allaround.test':
                $consumer_key = 'ck_481effc1659aae451f1b6a2e4f2adc3f7bc3829f';
                $consumer_secret = 'cs_b0af5f272796d15581feb8ed52fbf0d5469c67b4';
                break;
            case 'https://localhost/ministore':
                $consumer_key = 'ck_53d09905b34decf87745f1095bae29f60e1d4059';
                $consumer_secret = 'cs_a3d20d1474717fc1f533813d57841563115d4b16';
                break;
            default:
                $domain = 'https://allaround.co.il';
                $consumer_key = 'ck_c1785b09529d8d557cb2464de703be14f5db60ab';
                $consumer_secret = 'cs_92137acaafe08fb05efd20f846c4e6bd5c5d0834';
                break;
        }

        $order_url = $domain . '/wp-json/wc/v3/orders/' . $order_id;
        $max_retries = 3;
        $attempt = 0;
        $timeout = 20;
        $order_response = null;

        error_log("Fetching order details from: $order_url");

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

    return $order_data;
}

function fetch_display_order_details($order_id, $domain, $post_id = null)
{
    $order_data = fetch_order_details($order_id, $domain);

    if (is_string($order_data)) {
        // If fetch_order_details returns an error message, display it
        return $order_data;
    }

    $order = $order_data['order'];
    $status = $order->status;
    $items_subtotal = $order_data['items_subtotal'];
    $currency_symbol = $order_data['currency_symbol'];
    $shipping_total = $order_data['shipping_total'];
    $shipping_text = $order->shipping_lines[0]->method_title;
    $shipping_method_id = $order->shipping_lines[0]->method_id;

    $shipping_method_title = get_post_meta($post_id, 'shipping_method_title', true);
    $shipping_method_value = get_post_meta($post_id, 'shipping_method', true);
    $order_status_meta = get_post_meta($post_id, 'order_status', true);
    // if shipping_method_value is empty then update it with shipping_method_id
    if (empty($shipping_method_value)) {
        update_post_meta($post_id, 'shipping_method', $shipping_method_id);
    }
    if (empty($shipping_method_title)) {
        update_post_meta($post_id, 'shipping_method_title', $shipping_text);
    }
    if ($status !== $order_status_meta) {
        update_post_meta($post_id, 'order_status', $status);
    }

    // Fetch the items and deserialize the items
    $items = get_post_meta($post_id, 'items', true);
    $items = maybe_unserialize($items);
    if (!is_array($items)) {
        $items = [];
    }

    // Call the separate function to update items meta
    update_new_items($order->line_items, $items, $post_id);

    // Display the table with items (from existing meta or fetched items)
    ob_start();

    echo '<table id="tableMain" data-order_status="' . esc_attr($status) . '">';
    echo '<thead><tr>';
    echo '<th class="head"><strong>Product</strong></th>';
    if (!is_current_user_contributor()):
        echo '<th class="head"><strong>Quantity</strong></th>';
    endif;
    if (!is_current_user_author()):
        echo '<th class="head"><strong>Printing Note</strong></th>';
    endif;
    echo '<th class="head"><strong>Graphics</strong></th>';
    echo '<th class="head mockup-head" colspan=""><strong>Mockups</strong></th>';
    echo '</tr></thead><tbody>';

    foreach ($order->line_items as $item) {

        $item_id = esc_attr($item->id);
        $product_id = esc_attr($item->product_id);

        // Retrieve printing_note for the current product_id
        $printing_note = '';
        if (!empty($items)) {
            foreach ($items as $saved_item) {
                if (isset($saved_item['item_id']) && $saved_item['item_id'] == $item_id) {
                    if (isset($saved_item['printing_note'])) {
                        $printing_note = $saved_item['printing_note'];
                    }
                }
            }
        }

        echo '<tr class="om__orderRow" id="' . esc_attr($item_id) . '" data-product_id="' . esc_attr($item_id) . '" data-source_product_id="' . esc_attr($product_id) . '">';
        echo '<td class="item_product_column">';
        if (is_current_user_admin()):
            echo '<span class="om__editItemMeta" title="Edit" data-item_id="' . $item_id . '"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.733 8.86672V10.7334C10.733 10.9809 10.6347 11.2183 10.4596 11.3934C10.2846 11.5684 10.0472 11.6667 9.79967 11.6667H3.26634C3.01881 11.6667 2.78141 11.5684 2.60637 11.3934C2.43134 11.2183 2.33301 10.9809 2.33301 10.7334V4.20006C2.33301 3.95252 2.43134 3.71512 2.60637 3.54009C2.78141 3.36506 3.01881 3.26672 3.26634 3.26672H5.13301" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.23281 8.77337L11.6661 4.29337L9.70615 2.33337L5.27281 6.76671L5.13281 8.86671L7.23281 8.77337Z" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
            echo '<span class="om_duplicate_item" title="Duplicate"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="duplicate-icon" d="M10.5003 3.49996V1.74996C10.5003 1.59525 10.4389 1.44688 10.3295 1.33748C10.2201 1.22808 10.0717 1.16663 9.91699 1.16663H1.75033C1.59562 1.16663 1.44724 1.22808 1.33785 1.33748C1.22845 1.44688 1.16699 1.59525 1.16699 1.74996V9.91663C1.16699 10.0713 1.22845 10.2197 1.33785 10.3291C1.44724 10.4385 1.59562 10.5 1.75033 10.5H3.50033V12.25C3.50033 12.4047 3.56178 12.553 3.67118 12.6624C3.78058 12.7718 3.92895 12.8333 4.08366 12.8333H12.2503C12.405 12.8333 12.5534 12.7718 12.6628 12.6624C12.7722 12.553 12.8337 12.4047 12.8337 12.25V4.08329C12.8337 3.92858 12.7722 3.78021 12.6628 3.67081C12.5534 3.56142 12.405 3.49996 12.2503 3.49996H10.5003ZM2.33366 9.33329V2.33329H9.33366V9.33329H2.33366ZM11.667 11.6666H4.66699V10.5H9.91699C10.0717 10.5 10.2201 10.4385 10.3295 10.3291C10.4389 10.2197 10.5003 10.0713 10.5003 9.91663V4.66663H11.667V11.6666Z" fill="#1A1A1A"/></svg></span>';
            echo '<span class="om_delete_item" title="Delete"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.57313 3.65873H2.02533L3.08046 12.4715C3.10055 12.6826 3.28143 12.8333 3.49246 12.8333H10.5065C10.7176 12.8333 10.8884 12.6826 10.9185 12.4715L11.9737 3.65873H12.4258C12.657 3.65873 12.8378 3.47785 12.8378 3.24673C12.8378 3.01561 12.657 2.83473 12.4258 2.83473H11.6018H9.27052V1.57863C9.27052 1.3475 9.08964 1.16663 8.85852 1.16663H5.14046C4.90934 1.16663 4.72846 1.3475 4.72846 1.57863V2.83473H2.39714H1.57313C1.34201 2.83473 1.16113 3.01561 1.16113 3.24673C1.16113 3.47785 1.35206 3.65873 1.57313 3.65873ZM5.55246 1.99063H8.44652V2.83473H5.55246V1.99063ZM11.1396 3.65873L10.1448 12.0193H3.85421L2.85938 3.65873H11.1396Z" fill="#1A1A1A"/><path d="M5.6327 10.7633C5.86383 10.7633 6.04471 10.5825 6.04471 10.3513V5.41737C6.04471 5.18625 5.86383 5.00537 5.6327 5.00537C5.40158 5.00537 5.2207 5.18625 5.2207 5.41737V10.3513C5.2207 10.5825 5.40158 10.7633 5.6327 10.7633Z" fill="#1A1A1A"/><path d="M8.3661 10.7633C8.59723 10.7633 8.7781 10.5825 8.7781 10.3513V5.41737C8.7781 5.18625 8.59723 5.00537 8.3661 5.00537C8.13498 5.00537 7.9541 5.18625 7.9541 5.41737V10.3513C7.9541 10.5825 8.14503 10.7633 8.3661 10.7633Z" fill="#1A1A1A"/></svg></span>';
        endif;
        if (isset($item->id)) {
            echo '<input type="hidden" name="item_id" value="' . esc_attr($item_id) . '">';
        }
        $thumbnail_url = isset($item->image->src) && !empty($item->image->src) ? $item->image->src : get_template_directory_uri() . '/assets/images/allaround-logo.png';
        echo '<span class="om_item_thumb_cont"><img width="100" src="' . esc_url($thumbnail_url) . '" /></span>';
        echo '<span class="item_name_variations">';
        echo '<strong class="product_item_title">' . esc_html(__($item->name, 'hello-elementor')) . '</strong>';
        echo '<ul>';
        foreach ($item->meta_data as $meta) {
            if (in_array($meta->key, ["קובץ מצורף", "Attachment", "Additional Attachment", "_allaround_artwork_id", "_allaround_artwork_id2", "_allaround_art_pos_key", "_gallery_thumbnail"])) {
                continue;
            }

            // Convert specific meta keys to their respective values
            switch ($meta->key) {
                case 'צבע':
                    $meta_key = 'Color';
                    break;
                case 'מידה':
                    $meta_key = 'Size';
                    break;
                case 'מיקום אמנותי':
                    $meta_key = 'Art Position';
                    break;
                case 'הערת הוראה':
                    $meta_key = 'Instruction Note';
                    break;
                default:
                    $meta_key = $meta->key;
            }

            echo '<li data-meta_key="' . esc_html($meta_key) . '">' . esc_html($meta_key) . ': <strong>' . esc_html(strip_tags($meta->value)) . '</strong></li>';

        }
        echo '</ul>';
        echo '</span>';
        echo '<span data-source_product_id="' . esc_attr($product_id) . '" id="om__itemVariUpdateModal_' . $item_id . '" class="mfp-hide om__itemVariUpdateModal">';
        echo '<strong class="om__itemVariUpdateTitle">' . esc_html($item->name) . '</strong>';
        echo '<span class="om__itemVariUpdateMeta">';
        $instruction_note_found = false;
        $color_found = false;
        $size_found = false;
        $artPosition_found = false;
        foreach ($item->meta_data as $meta) {
            if ($meta->key === "Color" || $meta->key === "צבע") {
                echo '<span class="om__item_metaData_updateCon">';
                echo '<label for="color-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="color-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';
                echo '</span>';
                $color_found = true;
            }
            if ($meta->key === "Size" || $meta->key === "מידה") {
                echo '<label for="size-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="size-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';
                $size_found = true;
            }
            if ($meta->key === "Art Position" || $meta->key === "מיקום אמנותי") {
                echo '<span class="om__item_metaData_updateCon">';
                echo '<label for="art-position-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<select id="art-position-input_' . $item_id . '">';
                echo '<option value="' . esc_html(strip_tags($meta->value)) . '">' . esc_html(strip_tags($meta->value)) . '</option>';
                echo '</select>';
                echo '</span>';
                $artPosition_found = true;
            }
            if ($meta->key === "Instruction Note" || $meta->key === "הערת הוראה") {
                echo '<label for="instruction-note-input_' . $item_id . '">' . esc_html($meta->key) . '</label>';
                echo '<input type="text" id="instruction-note-input_' . $item_id . '" value="' . esc_html(strip_tags($meta->value)) . '" placeholder="Enter instruction note">';
                $instruction_note_found = true;
            }
        }

        // Add ArtPosition if there is Color and Size, but Art Position
        if ($color_found && $size_found && !$artPosition_found) {
            echo '<span class="om__item_metaData_updateCon">';
            echo '<label for="art-position-input_' . $item_id . '">Art Position</label>';
            echo '<select id="art-position-input_' . $item_id . '">';
            echo '<option value="">Select Art Position</option>';
            echo '</select>';
            echo '</span>';
        }

        // Add Instruction Note input if it was not found in the meta data
        if (!$instruction_note_found) {
            echo '<label for="instruction-note-input_' . $item_id . '">Instruction Note</label>';
            echo '<input type="text" id="instruction-note-input_' . $item_id . '" placeholder="Enter instruction note">';
        }

        echo '</span>';
        echo '<button data-order_id="' . $order_id . '" data-item_id="' . $item_id . '" class="update-item-meta-btn ml_add_loading" id="update-item-meta-btn_' . $item_id . '">Update Item Meta</button>';
        echo '</span>';
        echo '</td>';
        if (is_current_user_admin()) {
            echo '<td class="item_quantity_column">';
            echo '<span class="om__quantityNumbers">';
            echo '<span class="om__itemQuantity">' . esc_attr($item->quantity) . '</span>x';
            echo '<span class="om__itemRate">' . esc_attr(number_format($item->price, 2) . $currency_symbol) . '</span> = ';
            echo '<span class="om__itemCostTotal">' . esc_attr(number_format($item->total, 2) . $currency_symbol) . '</span>';
            echo '</span>';
            echo '<span class="om_itemQuantPriceEdit">';
            echo '<input type="number" class="item-quantity-input" data-item-id="' . esc_attr($item->id) . '" value="' . esc_attr($item->quantity) . '" />';
            echo '<input type="number" class="item-cost-input" data-item-id="' . esc_attr($item->id) . '" value="' . esc_attr($item->price) . '" />';
            echo '</span>';
            echo '</span>';
            echo '</td>';
            
            // Printing Note Column with textarea field
            echo '<td class="printing_note_column">';
            echo '<textarea class="printing_note_textarea" data-item_id="' . esc_attr($item_id) . '">' . esc_html($printing_note) . '</textarea>';
            echo '<button class="save_printing_note" data-item_id="' . esc_attr($item_id) . '">Save</button>';
            echo '</td>';
        } else if (is_current_user_contributor()) {
            // Printing Note Column with text for designers
            echo '<td class="printing_note_column">';
            echo '<span class="printing_note_text" data-item_id="' . esc_attr($item_id) . '">' . esc_html($printing_note) . '</span>';
            echo '</td>';
        } else if (is_current_user_author()) {
            echo '<td class="item_quantity_column">';
            echo '<span class="om__itemQuantity om_onlyQuantity">' . esc_attr($item->quantity) . '</span>';
            echo '</td>';
        }
        echo '<td class="item_graphics_column">';
        $artworkFound = false;
        foreach ($item->meta_data as $meta) {
            if (in_array($meta->key, ["קובץ מצורף", "Attachment", "Additional Attachment"])) {
                $clean_key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $meta->key));
                if (preg_match('/<p>(.*?)<\/p>/', $meta->value, $matches)) {
                    $filename = $matches[1];
                    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $class_name = 'file-format-' . strtolower($file_extension);
                } else {
                    $class_name = 'file-format-unknown';
                }
                $value = preg_replace('/<p>.*?<\/p>/', '', $meta->value);
                $artworkEdit = '<label class="om__editItemArtwork" for="om__upload_artwork_' . $clean_key . $item_id . '" data-meta_key="' . $clean_key . '" data-item_id="' . $item_id . '"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.733 8.86672V10.7334C10.733 10.9809 10.6347 11.2183 10.4596 11.3934C10.2846 11.5684 10.0472 11.6667 9.79967 11.6667H3.26634C3.01881 11.6667 2.78141 11.5684 2.60637 11.3934C2.43134 11.2183 2.33301 10.9809 2.33301 10.7334V4.20006C2.33301 3.95252 2.43134 3.71512 2.60637 3.54009C2.78141 3.36506 3.01881 3.26672 3.26634 3.26672H5.13301" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.23281 8.77337L11.6661 4.29337L9.70615 2.33337L5.27281 6.76671L5.13281 8.86671L7.23281 8.77337Z" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>';
                $artworkDelete = '<label class="om__DeleteArtwork" data-meta_id="' . $meta->id . '" data-meta_key="' . $clean_key . '" data-item_id="' . $item_id . '"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.57313 3.65873H2.02533L3.08046 12.4715C3.10055 12.6826 3.28143 12.8333 3.49246 12.8333H10.5065C10.7176 12.8333 10.8884 12.6826 10.9185 12.4715L11.9737 3.65873H12.4258C12.657 3.65873 12.8378 3.47785 12.8378 3.24673C12.8378 3.01561 12.657 2.83473 12.4258 2.83473H11.6018H9.27052V1.57863C9.27052 1.3475 9.08964 1.16663 8.85852 1.16663H5.14046C4.90934 1.16663 4.72846 1.3475 4.72846 1.57863V2.83473H2.39714H1.57313C1.34201 2.83473 1.16113 3.01561 1.16113 3.24673C1.16113 3.47785 1.35206 3.65873 1.57313 3.65873ZM5.55246 1.99063H8.44652V2.83473H5.55246V1.99063ZM11.1396 3.65873L10.1448 12.0193H3.85421L2.85938 3.65873H11.1396Z" fill="#1A1A1A"/><path d="M5.6327 10.7633C5.86383 10.7633 6.04471 10.5825 6.04471 10.3513V5.41737C6.04471 5.18625 5.86383 5.00537 5.6327 5.00537C5.40158 5.00537 5.2207 5.18625 5.2207 5.41737V10.3513C5.2207 10.5825 5.40158 10.7633 5.6327 10.7633Z" fill="#1A1A1A"/><path d="M8.3661 10.7633C8.59723 10.7633 8.7781 10.5825 8.7781 10.3513V5.41737C8.7781 5.18625 8.59723 5.00537 8.3661 5.00537C8.13498 5.00537 7.9541 5.18625 7.9541 5.41737V10.3513C7.9541 10.5825 8.14503 10.7633 8.3661 10.7633Z" fill="#1A1A1A"/></svg></label>';
                $artworkFileupload = '<input type="file" class="om__upload_artwork" id="om__upload_artwork_' . $clean_key . $item_id . '" data-item_id="' . $item_id . '" data-meta_id="' . $meta->id . '" data-meta_key="' . $clean_key . '" style="display:none" />';
                $value = '<div class="uploaded_graphics ' . esc_attr($class_name) . '">' . $artworkDelete . $artworkEdit . $artworkFileupload . $value . '</div>';
                echo $value;
                $artworkFound = true;
            }

        }
        if (!$artworkFound) {
            echo '<div class="uploaded_graphics">';
            echo '<input type="file" class="om__upload_artwork" id="om__upload_artwork_attachment_' . $item_id . '" data-item_id="' . $item_id . '" data-meta_key="attachment" style="display:none" />';
            echo '<label class="om__editItemArtwork" for="om__upload_artwork_attachment_' . $item_id . '" data-meta_key="attachment" data-item_id="' . $item_id . '"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.733 8.86672V10.7334C10.733 10.9809 10.6347 11.2183 10.4596 11.3934C10.2846 11.5684 10.0472 11.6667 9.79967 11.6667H3.26634C3.01881 11.6667 2.78141 11.5684 2.60637 11.3934C2.43134 11.2183 2.33301 10.9809 2.33301 10.7334V4.20006C2.33301 3.95252 2.43134 3.71512 2.60637 3.54009C2.78141 3.36506 3.01881 3.26672 3.26634 3.26672H5.13301" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7.23281 8.77337L11.6661 4.29337L9.70615 2.33337L5.27281 6.76671L5.13281 8.86671L7.23281 8.77337Z" stroke="#1A1A1A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg></label>';
            echo '<span class="no_artwork_text">No Artwork Attached</span>';
            echo '</div>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody><tfoot>';
    if (is_current_user_admin()):
        echo '<tr>';
        echo '<td class="subtotals_titles" colspan="1"><span>Items Subtotal:</span><br>';
        echo '<span>Shipping:</span><br>';
        echo '<span class="order_total_title">Order Total:</span></td>';
        echo '<td class="totals_column">';
        echo '<span class="om__items_subtotal">' . esc_attr(number_format($items_subtotal, 2) . ' ' . $currency_symbol) . '</span><br>';
        echo '<span class="om__shipping_total">' . esc_attr(number_format($shipping_total, 2) . ' ' . $currency_symbol) . '</span><br>';
        echo '<span class="om__orderTotal">' . esc_attr(number_format($order->total, 2) . ' ' . $currency_symbol) . '</span>';
        echo '<td class="tfoot_empty_column">';
        echo '</td>';
        echo '</td>';
        echo '</tr>';
    endif;
    echo '</tfoot></table>';
    echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '">';
    echo '<input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">';

    return ob_get_clean();
}

/**
 * Get Token from Clients Order.
 */
function set_client_token($order_id, $client_id, $domain)
{
    $order_data = fetch_order_details($order_id, $domain);

    if (is_string($order_data)) {
        // If fetch_order_details returns an error message, display it
        return $order_data;
    }

    $order = $order_data['order'];

    // Extract zc_payment_token from meta_data
    $zc_payment_token = '';
    foreach ($order->meta_data as $meta) {
        if ($meta->key === 'zc_payment_token') {
            $zc_payment_token = esc_attr($meta->value);
            break; // Stop loop once found
        }
    }

    // get the token meta from client
    $client_token = get_post_meta($client_id, 'token', true);

    // if client token is empty or different from zc_payment_token then update client token
    if ((empty($client_token) || $client_token !== $zc_payment_token) && !empty($zc_payment_token)) {
        update_post_meta($client_id, 'token', $zc_payment_token);
    }
}

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

    // Check if the current user is an Employee
    if (current_user_can('author')) {
        // Return only the last version for Employee role
        if (!empty($mockup_versions)) {
            $last_version = end($mockup_versions);
            wp_send_json_success(['mockup_versions' => [$last_version]]);
        } else {
            wp_send_json_success(['mockup_versions' => [], 'no_mockup_state' => true]);
        }
    } else {
        // Return all versions for other roles
        if (!empty($mockup_versions)) {
            wp_send_json_success(['mockup_versions' => $mockup_versions]);
        } else {
            wp_send_json_success(['mockup_versions' => [], 'no_mockup_state' => true]);
        }
    }
}
add_action('wp_ajax_initialize_mockup_columns', 'initialize_mockup_columns');
add_action('wp_ajax_nopriv_initialize_mockup_columns', 'initialize_mockup_columns');


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
add_action('wp_ajax_fetch_mockup_files', 'fetch_mockup_files');
add_action('wp_ajax_nopriv_fetch_mockup_files', 'fetch_mockup_files');




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

// ** Designer Role check ** //
function is_current_user_contributor()
{
    $current_user = wp_get_current_user();
    return in_array('contributor', (array) $current_user->roles);
}
function enable_contributor_uploads()
{
    $contributor = get_role('contributor');
    $contributor->add_cap('upload_files');
}
add_action('admin_init', 'enable_contributor_uploads');



// ** Employee / Author Role check ** //
function is_current_user_author()
{
    if (current_user_can('author')) {
        return true;
    } else {
        return false;
    }
}

function is_current_user_admin()
{
    if (current_user_can('administrator')) {
        return true;
    } else {
        return false;
    }
}

// ** Agent / Editor Role check ** //
function is_current_user_editor()
{
    if (current_user_can('editor')) {
        return true;
    } else {
        return false;
    }
}

// Function to get the current user's role
function get_current_user_role()
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        return $roles[0]; // Assuming the user has only one role
    } else {
        return null; // User is not logged in
    }
}

// Add User role class to body
function add_user_role_to_body_class($classes)
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        foreach ($roles as $role) {
            $classes[] = 'om__' . $role;
        }
    }
    return $classes;
}
add_filter('body_class', 'add_user_role_to_body_class');



/**
 * Delete a Mockup Version
 */
add_action('wp_ajax_delete_mockup_folder', 'delete_mockup_folder');
add_action('wp_ajax_nopriv_delete_mockup_folder', 'delete_mockup_folder');

/**
 * Update Client on Order Manage post
 */
function update_order_client()
{
    check_ajax_referer('order_management_nonce', 'nonce');

    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (empty($post_id) || empty($client_id)) {
        wp_send_json_error(array('message' => 'Missing post ID or client ID.'));
        wp_die();
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $address_1 = isset($_POST['address_1']) ? sanitize_text_field($_POST['address_1']) : '';
    $address_2 = isset($_POST['address_2']) ? sanitize_text_field($_POST['address_2']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

    // Update client ID
    $client_update = update_post_meta($post_id, 'client_id', $client_id);

    // Update shipping details
    $shipping_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'address_1' => $address_1,
        'address_2' => $address_2,
        'city' => $city,
        'phone' => $phone
    ];
    $shipping_update = update_post_meta($post_id, 'shipping', $shipping_data);

    if ($client_update && $shipping_update) {
        wp_send_json_success();
    } else {
        wp_send_json_error(['message' => 'Failed to update client or shipping information.']);
    }

    wp_die();
}
add_action('wp_ajax_update_order_client', 'update_order_client');
add_action('wp_ajax_nopriv_update_order_client', 'update_order_client');



function delete_mockup_folder()
{
    // Verify nonce for security
    check_ajax_referer('order_management_nonce', 'security');

    // Sanitize and validate inputs
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $version = sanitize_text_field($_POST['version']);

    // FTP connection details
    $ftp_server = '107.181.244.114';
    $ftp_user_name = 'lukpaluk';
    $ftp_user_pass = 'SK@8Ek9mZam45;';

    // Set remote directory path
    $remote_directory = "/public_html/artworks/$order_id/$product_id/$version/";

    // Connect to FTP server
    $ftp_conn = ftp_connect($ftp_server);
    if (!$ftp_conn) {
        wp_send_json_error(array('message' => "Could not connect to $ftp_server"));
    }

    // Login to FTP server
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        ftp_close($ftp_conn); // Always close connection
        wp_send_json_error(array('message' => "Could not log in to FTP server"));
    }

    // Enable passive mode
    ftp_pasv($ftp_conn, true);

    // List all files in the directory
    $file_list = ftp_nlist($ftp_conn, $remote_directory);
    if ($file_list === false) {
        ftp_close($ftp_conn);
        wp_send_json_error(array('message' => "Could not list files in $remote_directory"));
    }

    // Filter out '.' and '..'
    $file_list = array_filter($file_list, function ($file) {
        return !in_array(basename($file), ['.', '..']);
    });

    // Attempt to delete each file
    foreach ($file_list as $file) {
        if (!ftp_delete($ftp_conn, $file)) {
            // Handle failure to delete a specific file, log error if necessary
            ftp_close($ftp_conn);
            wp_send_json_error(array('message' => "Error deleting file: $file"));
        }
    }

    // Attempt to remove the directory after files are deleted
    $delete_success = ftp_rmdir($ftp_conn, $remote_directory);

    // Close the FTP connection
    ftp_close($ftp_conn);

    if ($delete_success) {
        wp_send_json_success(array('message' => "Successfully deleted $remote_directory"));
    } else {
        wp_send_json_error(array('message' => "Error deleting directory $remote_directory"));
    }
}

//** Printing Note Functions */
function save_printing_note()
{
    check_ajax_referer('order_management_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $item_id = intval($_POST['item_id']);
    $printing_note = sanitize_textarea_field($_POST['printing_note']);

    // Get the existing 'items' meta
    $items = get_post_meta($post_id, 'items', true);

    // Check if items is serialized or an array, unserialize if necessary
    if (!is_array($items)) {
        $items = maybe_unserialize($items);
    }

    // Check if items is a valid array
    if (is_array($items)) {
        // Initialize flag to track if item is found
        $item_found = false;

        // Loop through items to find the matching product_id
        foreach ($items as &$item) {
            if (isset($item['item_id']) && $item['item_id'] == $item_id) {
                // Update only the printing_note field
                $item['printing_note'] = $printing_note;
                $item_found = true;
                break;  // Exit the loop once the item is found and updated
            }
        }

        // Only update the database if the item was found and updated
        if ($item_found) {
            // Re-save the updated array back to the meta
            update_post_meta($post_id, 'items', $items);

            wp_send_json_success(['message' => 'Printing note updated successfully!']);
        } else {
            wp_send_json_error(['message' => 'Item not found in the meta data.']);
        }
    } else {
        // Log and send error if items meta is not an array
        error_log('Error: Items meta is not an array.');
        wp_send_json_error(['message' => 'Invalid items meta format.']);
    }
}
add_action('wp_ajax_save_printing_note', 'save_printing_note');
add_action('wp_ajax_nopriv_save_printing_note', 'save_printing_note');


function update_new_items($order_items, $items, $post_id)
{
    // Check for new items in the order that are not in the meta
    $updated = false;
    foreach ($order_items as $order_item) {
        $item_id = esc_attr($order_item->id);
        $product_id = esc_attr($order_item->product_id);

        // Check if the item is already present in the meta
        $item_exists = false;
        foreach ($items as $saved_item) {
            if (isset($saved_item['item_id']) && $saved_item['item_id'] == $item_id) {
                $item_exists = true;
                break;
            }
        }

        // If the item doesn't exist, add it to the meta array
        if (!$item_exists) {
            $new_item = [
                'item_id' => $item_id,
                'product_id' => $product_id,
                'quantity' => $order_item->quantity,
                'product_name' => $order_item->name,
                'total' => $order_item->total,
                'printing_note' => '' // Initialize with empty printing note
            ];
            $items[] = $new_item;
            $updated = true;
        }
    }

    // If there are updates, save the updated items back to the meta
    if ($updated) {
        update_post_meta($post_id, 'items', $items);
    }
}


/**
 * Order Artwork Proof Comments.
 *
 */

// Function to fetch posts from a specific page
function fetch_artwork_posts_page($page, $per_page)
{
    $current_domain = $_SERVER['SERVER_NAME'];

    if (strpos($current_domain, '.test') !== false || strpos($current_domain, 'lukpaluk.xyz') !== false) {
        $artwork_domain = 'https://artwork.lukpaluk.xyz';
    } else {
        $artwork_domain = 'https://artwork.allaround.co.il';
    }
    $response = wp_remote_get("$artwork_domain/wp-json/wp/v2/posts?per_page=$per_page&page=$page");

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
    $current_domain = $_SERVER['SERVER_NAME'];

    if (strpos($current_domain, '.test') !== false || strpos($current_domain, 'lukpaluk.xyz') !== false) {
        $artwork_domain = 'https://artwork.lukpaluk.xyz';
    } else {
        $artwork_domain = 'https://artwork.allaround.co.il';
    }
    $response = wp_remote_get("$artwork_domain/wp-json/wp/v2/posts/$post_id");

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
        // return display_artwork_comments(false, '', [], '#');
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

    if ($post_url !== '#' && is_current_user_admin()) {
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
                        <span class="revision-comment-title">
                            ההדמיות אושרו על ידי הלקוח 
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mark_icon-svg.svg" alt="">
                        </span>
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
                    $image_html .= '<img src="' . get_template_directory_uri() . '/assets/images/document.png" alt="Placeholder">';
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


/**
 * Order Searching.
 *
 */
function search_posts()
{
    $query = sanitize_text_field($_POST['query']);
    $order_status_filter = !empty($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : '';
    $order_type_filter = !empty($_POST['order_type']) ? sanitize_text_field($_POST['order_type']) : '';
    $logo_filter = !empty($_POST['logo_filter']) ? sanitize_text_field($_POST['logo_filter']) : '';
    $selected_month = !empty($_POST['month']) ? sanitize_text_field($_POST['month']) : '';
    $selected_year = !empty($_POST['year']) ? sanitize_text_field($_POST['year']) : '';
    $order_source_filter = !empty($_POST['order_source']) ? sanitize_text_field($_POST['order_source']) : '';

    // Determine if any filters are applied
    $is_filtering = !empty($query) || !empty($order_status_filter) || !empty($order_type_filter) || !empty($selected_month) || !empty($selected_year) || !empty($order_source_filter);

    // Set up base arguments for the query
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $is_filtering ? -1 : 30, // Return all posts if filtering, otherwise limit to 30
        'paged' => isset($_POST['page']) ? intval($_POST['page']) : 1, // Handle pagination if needed
        'meta_query' => array(
            'relation' => 'AND', // Both conditions (order_status and order_type) must match if provided
        ),
        'date_query' => array() // Initialize the date_query array
    );

    // Search by post title
    if (!empty($query)) {
        $args['s'] = $query;
        add_filter('posts_where', function ($where) use ($query) {
            global $wpdb;
            return $where . $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($query) . '%');
        });
    }

    // Filter by order status
    if (!empty($order_status_filter)) {
        $args['meta_query'][] = array(
            'key' => 'order_status',
            'value' => $order_status_filter,
            'compare' => '='
        );
    }

    // Filter by order type
    if (!empty($order_type_filter)) {
        if ($order_type_filter === 'not_tagged') {
            $args['meta_query'][] = array(
                'key' => 'order_type',
                'compare' => 'NOT EXISTS'
            );
        } else {
            $args['meta_query'][] = array(
                'key' => 'order_type',
                'value' => $order_type_filter,
                'compare' => '='
            );
        }
    }

    // Handle logo filter for company type orders
    if (!empty($order_type_filter) && $order_type_filter === 'company' && !empty($logo_filter)) {
        $args['meta_query'][] = array(
            'key' => 'client_id',
            'compare' => 'EXISTS'
        );
    }

    // Filter by selected month and year
    if (!empty($selected_month) && !empty($selected_year)) {
        $args['date_query'][] = array(
            'year' => intval($selected_year), // Use the selected year
            'monthnum' => intval($selected_month), // Use the selected month
        );
    } elseif (!empty($selected_year)) {
        // If only the year is selected, filter by the year
        $args['date_query'][] = array(
            'year' => intval($selected_year),
        );
    }

    // Filter by order source
    if (!empty($order_source_filter)) {
        $args['meta_query'][] = array(
            'key' => 'order_source',
            'value' => $order_source_filter,
            'compare' => '='
        );
    }

    $posts = new WP_Query($args);

    $has_posts = false; // Track whether any post is displayed
    $total_sum = 0; // Initialize total sum for items meta total

    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            $posts->the_post();

            // Get the meta data
            $order_status = get_post_meta(get_the_ID(), 'order_status', true);
            $order_type = get_post_meta(get_the_ID(), 'order_type', true);
            $client_id = get_post_meta(get_the_ID(), 'client_id', true);
            $items = get_post_meta(get_the_ID(), 'items', true); // Assuming items is stored as an array in the post meta

            // Check for logo filters
            $skip_post = false;
            if (!empty($client_id)) {
                $dark_logo = get_post_meta($client_id, 'dark_logo', true);
                $lighter_logo = get_post_meta($client_id, 'lighter_logo', true);

                if ($logo_filter === 'no_logos') {
                    if (!empty($dark_logo) && !empty($lighter_logo)) {
                        $skip_post = true;
                    }
                } elseif ($logo_filter === 'with_logos') {
                    if (empty($dark_logo) || empty($lighter_logo)) {
                        $skip_post = true;
                    }
                }
            }

            if ($skip_post) {
                continue; // Skip this post if it doesn't meet the logos condition
            }

            // Calculate the subtotal for the 'total' field in the 'items' meta
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (isset($item['total'])) {
                        $total_sum += floatval($item['total']); // Add the 'total' of each item to the subtotal
                    }
                }
            }

            // Display the post if it passes all filters
            $has_posts = true;
            ?>
            <div class="post-item">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <span>Order Status: <?php echo esc_html($order_status); ?></span><br>
                <span>Order Type: <?php echo esc_html($order_type); ?></span>
            </div>
            <?php
        }

        // Output the total sum of 'total' fields from items meta
        if ($total_sum > 0) {
            echo '<div class="total-sum">Total Sum of Items: <br>' . number_format($total_sum, 2) . ' ₪</div>';
        }
    }

    // Show fallback message if no posts were found
    if (!$has_posts) {
        echo '<p>No Orders Found.</p>';
    }

    wp_die();
}
add_action('wp_ajax_search_posts', 'search_posts');
add_action('wp_ajax_nopriv_search_posts', 'search_posts');


/**
 * Function to restrict access to logged-in users.
 * Redirects to login page if user is not logged in.
 */
function restrict_access_to_logged_in_users()
{
    // Check if user is logged in
    // Check if user is logged in
    if (!is_user_logged_in()) {
        // Display error message
        echo '<h3 class="login_require_error"><b>Login Required</b>: You must be logged in to view this page.</h3>';
        // Optionally, you can exit the script to prevent further execution
        exit;
    }
}

// function to restrict access to admin and editor role users only
function restrict_access_to_admin_and_editor()
{
    // Check if user is logged in
    if (!is_user_logged_in()) {
        // Display error message
        echo '<h3 class="login_require_error"><b>Login Required</b>: You must be logged in to view this page.</h3>';
        // Optionally, you can exit the script to prevent further execution
        exit;
    }

    // Check if user is not an admin or editor
    if (!is_current_user_admin() && !is_current_user_editor()) {
        // Display error message
        echo '<h3 class="login_require_error"><b>Access Denied</b>: You do not have permission to view this page.</h3>';
        // Optionally, you can exit the script to prevent further execution
        exit;
    }
}


add_action('admin_menu', 'unsubscribe_clients_menu');
function unsubscribe_clients_menu() {
    add_menu_page(
        'Unsubscribe Clients', // Page title
        'Unsubscribe Clients', // Menu title
        'manage_options', // Capability
        'unsubscribe-clients', // Menu slug
        'unsubscribe_clients_page', // Callback function
        'dashicons-email-alt2', // Icon
        6 // Position
    );
}
function unsubscribe_clients_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle file upload
    if (isset($_POST['submit']) && isset($_FILES['unsubscribe_csv'])) {
        $csv_file = $_FILES['unsubscribe_csv'];

        if ($csv_file['type'] === 'text/csv') {
            $csv_data = file_get_contents($csv_file['tmp_name']);
            unsubscribe_process_csv($csv_data);
        } else {
            echo '<div class="error"><p>Please upload a valid CSV file.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Unsubscribe Clients</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="unsubscribe_csv">Upload CSV File:</label><br>
            <input type="file" name="unsubscribe_csv" id="unsubscribe_csv" accept=".csv">
            <br><br>
            <input type="submit" name="submit" class="button button-primary" value="Unsubscribe">
        </form>
    </div>
    <?php
}
function unsubscribe_process_csv($csv_data) {
    $lines = explode(PHP_EOL, $csv_data);
    $emails = [];

    // Extract emails from CSV (Assuming column names in first row)
    $header = str_getcsv(array_shift($lines)); // Get headers
    $email_index = array_search('email', $header); // Find index of 'email' column

    foreach ($lines as $line) {
        $data = str_getcsv($line);
        if (isset($data[$email_index])) {
            $emails[] = trim($data[$email_index]);
        }
    }

    if (!empty($emails)) {
        global $wpdb;

        // Loop through each email and find the client post
        foreach ($emails as $email) {
            if (!empty($email)) {
                // Assuming clients are stored as custom post types with 'client' post type
                $meta_query = new WP_Query(array(
                    'post_type' => 'client', // Adjust as per your post type
                    'meta_query' => array(
                        array(
                            'key' => 'email', // Meta key for email
                            'value' => $email,
                            'compare' => '='
                        )
                    )
                ));

                if ($meta_query->have_posts()) {
                    while ($meta_query->have_posts()) {
                        $meta_query->the_post();
                        $client_id = get_the_ID();

                        // Update 'subscribed' meta field to 'no'
                        update_post_meta($client_id, 'subscribed', 'no');

                        // Log the unsubscribe action
                        error_log("Unsubscribed client with email: $email (Client ID: $client_id)");
                    }
                } else {
                    error_log("No client found with email: $email");
                }

                wp_reset_postdata();
            }
        }

        echo '<div class="updated"><p>CSV processed successfully and clients unsubscribed.</p></div>';
    } else {
        echo '<div class="error"><p>No emails found in the CSV file.</p></div>';
    }
}