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

if (!function_exists('hello_elementor_scripts_styles')) {
    /**
     * Theme Scripts & Styles.
     *
     * @return void
     */
    function hello_elementor_scripts_styles()
    {
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
            'allaround-style',
            get_template_directory_uri() . '/assets/css/style.css',
            [],
            filemtime(get_theme_file_path('/assets/css/style.css'))
        );

        wp_enqueue_script('magnifyzoom', get_template_directory_uri() . '/assets/js/jquery.magnify.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('magnific-popup', get_template_directory_uri() . '/assets/js/jquery.magnific-popup.min.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);

        wp_enqueue_script('slick-js', get_template_directory_uri() . '/assets/js/slick.js', array('jquery'), HELLO_ELEMENTOR_VERSION, true);
        wp_enqueue_script('allaround-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), filemtime(get_theme_file_path('/assets/js/main.js')), true);

        wp_localize_script(
            'allaround-main',
            'allaround_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'admin_email' => get_bloginfo('admin_email'),
                'nonce' => wp_create_nonce("allaround_validation_nonce"),
                'assets' => get_template_directory_uri() . '/assets/',
                'redirecturl' => home_url()
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
require get_template_directory() . '/includes/classes/class-ajax.php';

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

/**
 * Artwork Functionality Begines.
 *
 * 
 */
// Modify post permalink to uniqueID before inserting
add_filter('wp_insert_post_data', 'custom_artwork_permalink_on_creation', 10, 2);

function custom_artwork_permalink_on_creation($data, $postarr)
{
    if ($data['post_type'] == 'post') {
        $random_string = substr(md5(uniqid(mt_rand(), true)), 0, 12);

        $data['post_name'] .= '-' . $random_string;
    }

    return $data;
}




// Hook to create a new endpoint for adding proof from Monday/Make.com
add_action('rest_api_init', 'register_artwork_review_endpoint');

function register_artwork_review_endpoint()
{
    register_rest_route(
        'artwork-review/v1',
        '/add-proof',
        array(
            'methods' => 'POST',
            'callback' => 'handle_add_proof_request',
            'permission_callback' => function () {
                return true;
            }
        )
    );
}

// Processing the request from Monday/Make.com
// function handle_add_proof_request($request)
// {
//     $params = $request->get_params();

//     if (isset($params['order_number']) && isset($params['proof_status']) && isset($params['customer_name'])) {

//         $comment_text = sanitize_text_field($params['comment_text']);
//         $order_number = sanitize_text_field($params['order_number']);
//         $order_number = str_replace(' ', '', $order_number);
//         $customer_name = sanitize_text_field($params['customer_name']);
//         $customer_email = sanitize_text_field($params['customer_email']);
//         $image_urls = $params['image_urls'];
//         $image_urls = explode(',', $image_urls);
//         $proof_status = $params['proof_status'];

//         // Check if proof status is "Mockup V1 Sent"
//         if ($proof_status === "Mockup V1 Sent") {
//             $existing_post_id = find_post_id_by_order_number($order_number);

//             if ($existing_post_id) {
//                 return new WP_REST_Response('Error: Order number already exists.', 400);
//             }

//             $post_title = '#' . $order_number;

//             $post_data = array(
//                 'post_title' => $post_title,
//                 'post_status' => 'publish',
//                 'post_type' => 'artwork'
//             );

//             // Insert the post
//             $new_post_id = wp_insert_post($post_data);

//             if (!is_wp_error($new_post_id)) {
//                 // Update post meta with order ID and customer email
//                 update_post_meta($new_post_id, 'order_id', $order_number);
//                 update_post_meta($new_post_id, 'customer_name', $customer_name);
//                 update_post_meta($new_post_id, 'customer_email', $customer_email);
//                 update_field('order_number', $order_number, $new_post_id);

//                 // Add comments and images
//                 $existing_comments = array();
//                 if (!empty($comment_text)) {
//                     $existing_comments[] = array(
//                         'artwork_comment_name' => 'AllAround',
//                         'artwork_comments_texts' => $comment_text,
//                         'artwork_comment_date' => current_time('mysql')
//                     );
//                 }


//                 $image_ids = array();
//                 foreach ($image_urls as $image_url) {
//                     $attachment_id = upload_image_from_url($image_url, $new_post_id);
//                     if ($attachment_id) {
//                         $image_ids[] = $attachment_id;
//                     }
//                 }
//                 update_field('mockup_proof_gallery', $image_ids, $new_post_id);

//                 // Update ACF field with comments
//                 update_field('artwork_comments', $existing_comments, $new_post_id);

//                 // Send data to webhook
//                 send_data_to_webhook($proof_status, $customer_name, $customer_email, $order_number, $new_post_id);

//                 return new WP_REST_Response('Post added successfully and data sent to webhook.', 200);
//             } else {
//                 return new WP_REST_Response('Error: Unable to add new post.', 500);
//             }

//         } else {
//             // Find the post ID by order number
//             $post_id = find_post_id_by_order_number($order_number);

//             if ($post_id) {
//                 $existing_comments = get_field('artwork_comments', $post_id) ?: array();

//                 // Add new comment
//                 $new_comment = array(
//                     'artwork_comment_name' => 'AllAround',
//                     'artwork_comments_texts' => $comment_text,
//                     'artwork_comment_date' => current_time('mysql')
//                 );

//                 if (!empty($image_urls)) {
//                     $image_ids = array();
//                     foreach ($image_urls as $image_url) {
//                         $attachment_id = upload_image_from_url($image_url, $post_id);
//                         if ($attachment_id) {
//                             $image_ids[] = $attachment_id;
//                         }
//                     }
//                     update_field('mockup_proof_gallery', $image_ids, $post_id);
//                 }

//                 $existing_comments[] = $new_comment;

//                 $success = update_field('artwork_comments', $existing_comments, $post_id);

//                 if ($success) {
//                     // Update customer email
//                     update_post_meta($post_id, 'customer_email', $customer_email);

//                     // Send data to webhook
//                     send_data_to_webhook($proof_status, $customer_name, $customer_email, $order_number, $post_id);

//                     return new WP_REST_Response('Comment added successfully and data sent to webhook.', 200);
//                 } else {
//                     return new WP_REST_Response('Error: Unable to update ACF field.', 500);
//                 }
//             } else {
//                 return new WP_REST_Response('Error: Post ID not found for the given order number.', 404);
//             }
//         }
//     } else {
//         return new WP_REST_Response('Error: Required parameters are missing.', 400);
//     }
// }

// Processing the request from Monday/Make.com
function handle_add_proof_request($request)
{
    $params = $request->get_params();

    if (isset($params['order_number'], $params['proof_status'], $params['customer_name'])) {

        $comment_text = sanitize_text_field($params['comment_text']);
        $order_number = str_replace(' ', '', sanitize_text_field($params['order_number']));
        $customer_name = sanitize_text_field($params['customer_name']);
        $customer_email = sanitize_text_field($params['customer_email']);
        $image_urls = isset($params['image_urls']) ? explode(',', $params['image_urls']) : array();
        $proof_status = $params['proof_status'];

        // Find the post ID by order number
        $post_id = find_post_id_by_order_number($order_number);

        if ($proof_status === "Mockup V1 Sent") {
            if ($post_id) {
                // Update existing post
                $existing_comments = get_field('artwork_comments', $post_id) ?: array();
            } else {
                // Insert new post
                $post_title = '#' . $order_number;
                $post_data = array(
                    'post_title' => $post_title,
                    'post_status' => 'publish',
                    'post_type' => 'post'
                );
                // Insert the post
                $post_id = wp_insert_post($post_data);
                $existing_comments = array();
                update_post_meta($post_id, 'order_number', $order_number);
            }
        } else {
            if (!$post_id) {
                return new WP_REST_Response('Error: Post ID not found for the given order number.', 404);
            }
            // Update existing post
            $existing_comments = get_field('artwork_comments', $post_id) ?: array();
        }

        $new_comment = array(
            'artwork_comment_name' => 'AllAround',
            'artwork_comments_texts' => $comment_text,
            'artwork_comment_date' => current_time('mysql')
        );

        if (!empty($image_urls)) {
            $image_ids = array();
            foreach ($image_urls as $image_url) {
                $attachment_id = upload_image_from_url($image_url, $post_id);
                if ($attachment_id) {
                    $image_ids[] = $attachment_id;
                }
            }
            update_field('mockup_proof_gallery', $image_ids, $post_id);
        }

        $existing_comments[] = $new_comment;

        $success = update_field('artwork_comments', $existing_comments, $post_id);

        if ($success) {
            update_post_meta($post_id, 'customer_email', $customer_email);
            update_post_meta($post_id, 'customer_name', $customer_name);

            $is_approved_proof = get_field('approved_proof', $post_id);
            if ($is_approved_proof) {
                // Update $is_approved_proof to false
                update_field('approved_proof', false, $post_id);
            }

            send_data_to_webhook($proof_status, $customer_name, $customer_email, $order_number, $post_id);
            return new WP_REST_Response('Comment added successfully and data sent to webhook.', 200);
        } else {
            return new WP_REST_Response('Error: Unable to update ACF field.', 500);
        }
    } else {
        return new WP_REST_Response('Error: Required parameters are missing.', 400);
    }
}


function send_data_to_webhook($proof_status, $customer_name, $customer_email, $order_number, $post_id)
{
    // Get the post URL
    $post_url = get_permalink($post_id);

    // Send data to webhook
    $data = array(
        'proofStatus' => $proof_status,
        'customerName' => $customer_name,
        'customerEmail' => $customer_email,
        'orderId' => $order_number,
        'postURL' => $post_url
    );

    // if $post_url contains .test or localhost then don't send webhook. While in Development mode. 
    //TODO: Remove this
    if (strpos($post_url, '.test') !== false || strpos($post_url, 'localhost') !== false || strpos($post_url, 'lukpaluk.xyz') !== false) {

        $response = wp_remote_post(
            'https://hook.us1.make.com/yb3erk9vt5yyidhjshwbc3er37pd9jas',
            array(
                'body' => json_encode($data),
                'headers' => array('Content-Type' => 'application/json'),
            )
        );
        return;
    }

    $response = wp_remote_post(
        'https://hook.eu1.make.com/ws02h18kcpzq7sfvit8ruh3dkx6q4cd7',
        array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
        )
    );

    if (is_wp_error($response)) {
        return new WP_REST_Response('Error sending data to webhook: ' . $response->get_error_message(), 500);
    }
}



// Function to upload an image from URL and return the attachment ID
function upload_image_from_url($image_url, $post_id)
{
    $image_name = basename($image_url);
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    if ($image_data) {
        $file = $upload_dir['path'] . '/' . $image_name;
        file_put_contents($file, $image_data);
        $wp_filetype = wp_check_filetype($image_name, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($image_name),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once (ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        return $attach_id;
    }
    return false;
}


function find_post_id_by_order_number($order_number)
{
    // Query for the post with the given order number
    $query = new WP_Query(
        array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'order_number',
                    'value' => $order_number,
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



// Hook to handle AJAX request for submitting custom comment
add_action('wp_ajax_submit_custom_comment', 'submit_custom_comment');
add_action('wp_ajax_nopriv_submit_custom_comment', 'submit_custom_comment');

function submit_custom_comment()
{
    // Get the comment text from the AJAX request and replace line breaks with HTML <br> tags
    $comment_text = isset($_POST['comment_text']) ? wp_kses_post($_POST['comment_text']) : '';
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    // Get the post URL
    $post_url = get_permalink($post_id);

    if ($comment_text && $post_id) {
        $uploaded_file_urls = array();

        if (!empty($_FILES['fileuploadfield']['name'])) {
            $attachment_id = media_handle_upload('fileuploadfield', $post_id);
            if (!is_wp_error($attachment_id)) {
                $file_url = wp_get_attachment_url($attachment_id);
                if ($file_url) {
                    $uploaded_file_urls = $file_url;
                } else {
                    error_log('Error getting attachment URL for attachment ID: ' . $attachment_id);
                }
            } else {
                error_log('Error uploading file: ' . $attachment_id->get_error_message());
            }
        }

        // Get existing comments
        $existing_comments = get_field('artwork_comments', $post_id) ?: array();

        // Get customer name from post meta
        $customer_name = get_post_meta($post_id, 'customer_name', true);
        $order_id = get_post_meta($post_id, 'order_number', true);

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $comment_author_name = $current_user->display_name;
        } else {
            $comment_author_name = $customer_name ? $customer_name : 'Guest';
        }

        // Add the comment to the existing comments array
        $new_comment = array(
            'artwork_comment_name' => $comment_author_name,
            'artwork_comments_texts' => $comment_text,
            'artwork_comment_date' => current_time('mysql'),
            'artwork_new_file' => $uploaded_file_urls
        );
        $existing_comments[] = $new_comment;

        // Update the ACF field with the new comments
        $success = update_field('artwork_comments', $existing_comments, $post_id);

        // Send data to webhook
        if ($success) {
            $data = array(
                'proofStatus' => 'Rejected with comment',
                'customerName' => $comment_author_name,
                'commentText' => $comment_text,
                'orderId' => $order_id,
                'uploadedFileURL' => $uploaded_file_urls
            );

            // if $post_url contains .test or localhost then don't send webhook. While in Development mode. 
            //TODO: Remove this
            if (strpos($post_url, '.test') !== false || strpos($post_url, 'localhost') !== false || strpos($post_url, 'lukpaluk.xyz') !== false) {
                $response = wp_remote_post(
                    'https://hook.us1.make.com/yb3erk9vt5yyidhjshwbc3er37pd9jas',
                    array(
                        'body' => json_encode($data),
                        'headers' => array('Content-Type' => 'application/json'),
                    )
                );
            } else {
                $response = wp_remote_post(
                    'https://hook.eu1.make.com/ws02h18kcpzq7sfvit8ruh3dkx6q4cd7',
                    array(
                        'body' => json_encode($data),
                        'headers' => array('Content-Type' => 'application/json'),
                    )
                );
            }

            if (!empty($uploaded_file_urls) && pathinfo($uploaded_file_urls, PATHINFO_EXTENSION) === 'pdf') {
                $data['uploadedFileURL'] = get_template_directory_uri() . '/assets/images/pdf-icon.svg';
            }

            if (is_wp_error($response)) {
                wp_send_json_error('Error sending data to webhook: ' . $response->get_error_message());
            } else {
                wp_send_json_success($data);
            }
        } else {
            wp_send_json_error('Error: Unable to update ACF field.');
        }
    } else {
        wp_send_json_error('Error: Invalid comment data.');
    }
}


// Hook to handle AJAX request for Approve Proof
add_action('wp_ajax_mockup_proof_approve', 'mockup_proof_approve');
add_action('wp_ajax_nopriv_mockup_proof_approve', 'mockup_proof_approve');
function mockup_proof_approve()
{
    // Get the post ID from the AJAX request
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    // Get customer name from post meta
    $customer_name = get_post_meta($post_id, 'customer_name', true);
    $order_id = get_post_meta($post_id, 'order_number', true);
    // Get the post URL
    $post_url = get_permalink($post_id);

    if ($post_id) {
        // Check if the approval button is clicked
        if (isset($_POST['approve']) && $_POST['approve'] === 'true') {
            // Update ACF post meta approved_proof to true
            update_field('approved_proof', true, $post_id);
            update_post_meta($post_id, 'proof_approved_time', current_time('mysql'));

            $data = array(
                'customerName' => $customer_name,
                'orderId' => $order_id,
                'proofStatus' => 'Approved'
            );

            // if $post_url contains .test or localhost then don't send webhook. While in Development mode. 
            //TODO: Remove this
            if (strpos($post_url, '.test') !== false || strpos($post_url, 'localhost') !== false || strpos($post_url, 'lukpaluk.xyz') !== false) {
                $response = wp_remote_post(
                    'https://hook.us1.make.com/yb3erk9vt5yyidhjshwbc3er37pd9jas',
                    array(
                        'body' => json_encode($data),
                        'headers' => array('Content-Type' => 'application/json'),
                    )
                );
            } else {
                $response = wp_remote_post(
                    'https://hook.eu1.make.com/ws02h18kcpzq7sfvit8ruh3dkx6q4cd7',
                    array(
                        'body' => json_encode($data),
                        'headers' => array('Content-Type' => 'application/json'),
                    )
                );
            }

            if (is_wp_error($response)) {
                wp_send_json_error('Error sending data to webhook: ' . $response->get_error_message());
            } else {
                wp_send_json_success('Proof approved successfully.');
            }
        } else {
            // Return error message
            wp_send_json_error('Error: Invalid request.');
        }
    } else {
        wp_send_json_error('Error: Invalid post ID.');
    }
}