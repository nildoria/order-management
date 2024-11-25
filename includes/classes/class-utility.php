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
        add_action('rest_api_init', [$this, 'utility_rest_api_endpoints']);
    }

    public function utility_rest_api_endpoints()
    {
        // New route for minisite_created update clients by email and/or phone
        register_rest_route(
            'manage-client/v1',
            '/minisite-created-meta',
            array(
                'methods' => 'POST',
                'callback' => [$this, 'handle_minisite_created_meta'],
                'permission_callback' => [$this, 'check_basic_auth'],
                // 'permission_callback' => '__return_true',
            )
        );
    }

    public function check_basic_auth($request)
    {
        $authorization = $request->get_header('Authorization');

        if (strpos($authorization, 'Basic ') === 0) {
            // Get the username and password from the Authorization header
            $credentials = base64_decode(substr($authorization, 6));
            list($username, $password) = explode(':', $credentials);

            // Authenticate the user
            $user = wp_authenticate($username, $password);
            if (is_wp_error($user)) {
                return new WP_Error('unauthorized', 'Invalid credentials', array('status' => 401));
            }

            // Set the current user
            wp_set_current_user($user->ID);

            return true;
        }

        return new WP_Error('unauthorized', 'Authorization header not found', array('status' => 401));
    }

    public function handle_minisite_created_meta(WP_REST_Request $request)
    {
        // Get the parameters
        $email = sanitize_email($request->get_param('email'));
        $phone = sanitize_text_field($request->get_param('phone'));
        $minisite_created = sanitize_text_field($request->get_param('minisite_created'));

        // Check if email is provided and valid
        if ((empty($email) || !is_email($email)) && empty($phone)) {
            return new WP_Error('invalid_email', 'Email address or Phone is not valid.', array('status' => 400));
        }

        // Check if minisite_created is provided and valid
        if (empty($minisite_created) || !in_array($minisite_created, ['yes', 'no'])) {
            return new WP_Error('invalid_minisite_created', 'Minisite created value is not valid.', array('status' => 400));
        }

        // Create the meta query array
        $meta_query = array('relation' => 'AND');

        // Add email to the query if it's provided
        if (!empty($email)) {
            $meta_query[] = array(
                'key' => 'email',
                'value' => $email,
                'compare' => '='
            );
        }

        // Add phone to the query if it's provided
        if (!empty($phone)) {
            $meta_query[] = array(
                'key' => 'phone',
                'value' => $phone,
                'compare' => '='
            );
        }

        // Query arguments
        $args = array(
            'post_type' => 'client',
            'post_status' => 'publish',
            'posts_per_page' => 100, // Limit to 10 at a time for batch processing
            'meta_query' => $meta_query
        );

        // Run the query
        $query = new WP_Query($args);

        // If no clients found, return an error
        if (!$query->have_posts()) {
            return new WP_Error('client_not_found', 'No client found with the provided email and/or phone number.', array('status' => 404));
        }

        // Process each client found
        $updated_clients = array();
        while ($query->have_posts()) {
            $query->the_post();
            $client_id = get_the_ID();

            // Update 'subscribed' meta to 'no'
            update_post_meta($client_id, 'minisite_created', $minisite_created);

            // Log the updated client
            $updated_clients[] = array(
                'client_id' => $client_id,
                'email' => get_post_meta($client_id, 'email', true),
                'phone' => get_post_meta($client_id, 'phone', true),
                'mini_url' => get_post_meta($client_id, 'mini_url', true),
                'minisite_created' => get_post_meta($client_id, 'minisite_created', true)
            );
        }

        // Reset the post data
        wp_reset_postdata();

        // Return the list of updated clients
        return new WP_REST_Response(array(
            'message' => 'Clients data updated successfully.',
            'clients' => $updated_clients
        ), 200);
    }

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
    }

    private function is_current_user_contributor()
    {
        $current_user = wp_get_current_user();
        return in_array('contributor', (array) $current_user->roles);
    }

}

// Initialize the class
new Alarnd_Utility();
