<?php
get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

?>
<main class="site-main" role="main">
    <?php
    while (have_posts()):
        the_post();

        $client_id = get_the_ID();

        $subscribed = get_post_meta($client_id, 'subscribed', true);

        $fields = [
            'client_type' => get_post_meta($client_id, 'client_type', true),
            'first_name' => get_post_meta($client_id, 'first_name', true),
            'last_name' => get_post_meta($client_id, 'last_name', true),
            'email' => get_post_meta($client_id, 'email', true),
            'phone' => get_post_meta($client_id, 'phone', true),
            'status' => get_post_meta($client_id, 'status', true),
            'subscribed' => !empty($subscribed) ? $subscribed : 'yes',
            'token' => get_post_meta($client_id, 'token', true),
            'address_1' => get_post_meta($client_id, 'address_1', true),
            'city' => get_post_meta($client_id, 'city', true),
            'dark_logo' => get_post_meta($client_id, 'dark_logo', true),
            'lighter_logo' => get_post_meta($client_id, 'lighter_logo', true),
            'back_light' => get_post_meta($client_id, 'back_light', true),
            'back_dark' => get_post_meta($client_id, 'back_dark', true),
            'logo_type' => get_post_meta($client_id, 'logo_type', true),
            'mini_url' => get_post_meta($client_id, 'mini_url', true),
            'mini_header' => get_post_meta($client_id, 'mini_header', true),
            'invoice' => get_post_meta($client_id, 'invoice', true),
            'logo' => get_post_meta($client_id, 'logo', true),
        ];

        $token = esc_attr($fields['token']);
        if (!empty($token)) {
            $masked_token = substr($token, 0, 4) . str_repeat('*', strlen($token) - 4);
        } else {
            $masked_token = '';
        }

        ?>
        <div id="create-client-form">
            <h2>Edit: <?php the_title(); ?></h2>
            <form id="addClientForm" data-type="edit" method="post">
                <?php wp_nonce_field('create_client', 'create_client_nonce'); ?>
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

                <div class="om_create_order_box">

                    <div class="om_create__product_details">

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="client_type" id="client_type">
                                <option value="personal" <?php selected($fields['client_type'], 'personal'); ?>>Personal
                                </option>
                                <option value="company" <?php selected($fields['client_type'], 'company'); ?>>Company
                                </option>
                            </select>
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="firstName">First Name:</label>
                            <input type="text" id="firstName" name="first_name"
                                value="<?php echo esc_attr($fields['first_name']); ?>" required>
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="lastName">Last Name:</label>
                            <input type="text" id="lastName" name="last_name"
                                value="<?php echo esc_attr($fields['last_name']); ?>" required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="invoice">Invoice Name:</label>
                            <input type="text" name="invoice" id="invoice"
                                value="<?php echo esc_attr($fields['invoice']); ?>" />
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address_1"
                                value="<?php echo esc_attr($fields['address_1']); ?>">
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" id="city" name="city" value="<?php echo esc_attr($fields['city']); ?>">
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo esc_attr($fields['email']); ?>"
                                required>
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($fields['phone']); ?>"
                                required>
                        </div>
                        <br>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <option value="">Select status</option>
                                <option value="client" <?php selected($fields['status'], 'client'); ?>>Client</option>
                                <option value="welcome" <?php selected($fields['status'], 'welcome'); ?>>Welcome</option>
                                <option value="company_prospect" <?php selected($fields['status'], 'company_prospect'); ?>>
                                    Company Prospect</option>
                            </select>
                        </div>
                        <br>

                        <div class="form-group client_token_field">
                            <label for="token">Token:</label>
                            <input type="text" name="token" id="token" value="<?php echo $masked_token; ?>" />
                        </div>
                        <br>

                        <div class="form-group form-group-row">
                            <label>Subscribed:</label>
                            <div class="form-group-flex">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="subscribed" id="yes" value="yes"
                                        <?php checked($fields['subscribed'], 'yes', true); ?>>
                                    <label class="form-check-label" for="yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="subscribed" id="no" value="no" <?php checked($fields['subscribed'], 'no'); ?>>
                                    <label class="form-check-label" for="no">No</label>
                                </div>
                            </div>
                        </div>

                        <br>
                        <br>

                    </div>

                    <div class="om_create__customer_details">
                        <div class="om_hidden_details"
                            style="display: <?php echo $fields['client_type'] == 'company' ? 'block' : 'none'; ?>;">
                            <div class="om_customer__details_title">
                                <h4><?php echo esc_html__('Company Details:', 'hello-elementor'); ?></h4>
                            </div>

                            <div class="form-group">
                                <label for="dark_logo">Dark Logo:</label>
                                <div class="form-group-flex">
                                    <input type="text" name="dark_logo" id="dark_logo"
                                        value="<?php echo esc_attr($fields['dark_logo']); ?>" />
                                    <input type="button" id="dark_logo_button" class="button upload-image-button"
                                        value="Upload Image" />
                                    <input type="button" id="remove_dark_logo_button" class="button remove-image-button"
                                        value="Remove Image"
                                        style="display: <?php echo $fields['dark_logo'] ? 'inline-block' : 'none'; ?>;" />
                                </div>
                                <img src="<?php echo esc_attr($fields['dark_logo']); ?>"
                                    style="max-width: 300px; display: <?php echo $fields['dark_logo'] ? 'block' : 'none'; ?>;;" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="lighter_logo">Lighter Logo:</label>
                                <div class="form-group-flex">
                                    <input type="text" name="lighter_logo" id="lighter_logo"
                                        value="<?php echo esc_attr($fields['lighter_logo']); ?>" />
                                    <input type="button" id="lighter_logo_button" class="button upload-image-button"
                                        value="Upload Image" />
                                    <input type="button" id="remove_lighter_logo_button" class="button remove-image-button"
                                        value="Remove Image"
                                        style="display: <?php echo $fields['lighter_logo'] ? 'inline-block' : 'none'; ?>;" />
                                </div>
                                <img src="<?php echo esc_attr($fields['lighter_logo']); ?>"
                                    style="max-width: 300px; display: <?php echo $fields['lighter_logo'] ? 'block' : 'none'; ?>;;" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="back_light">Back Light:</label>
                                <div class="form-group-flex">
                                    <input type="text" name="back_light" id="back_light"
                                        value="<?php echo esc_attr($fields['back_light']); ?>" />
                                    <input type="button" id="back_light_button" class="button upload-image-button"
                                        value="Upload Image" />
                                    <input type="button" id="remove_back_light_button" class="button remove-image-button"
                                        value="Remove Image"
                                        style="display: <?php echo $fields['back_light'] ? 'inline-block' : 'none'; ?>;" />
                                </div>
                                <img src="<?php echo esc_attr($fields['back_light']); ?>"
                                    style="max-width: 300px; display: <?php echo $fields['back_light'] ? 'block' : 'none'; ?>;;" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="back_dark">Back Dark:</label>
                                <div class="form-group-flex">
                                    <input type="text" name="back_dark" id="back_dark"
                                        value="<?php echo esc_attr($fields['back_dark']); ?>" />
                                    <input type="button" id="back_dark_button" class="button upload-image-button"
                                        value="Upload Image" />
                                    <input type="button" id="remove_back_dark_button" class="button remove-image-button"
                                        value="Remove Image"
                                        style="display: <?php echo $fields['back_dark'] ? 'inline-block' : 'none'; ?>;" />
                                </div>
                                <img src="<?php echo esc_attr($fields['back_dark']); ?>"
                                    style="max-width: 300px; display: <?php echo $fields['back_dark'] ? 'block' : 'none'; ?>;;" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="logo_type">Logo Type:</label>
                                <select name="logo_type" id="logo_type">
                                    <option>-- Select Logo Type --</option>
                                    <option value="chest_only" <?php selected($fields['logo_type'], 'chest_only'); ?>>Chest
                                        only</option>
                                    <option value="big_front" <?php selected($fields['logo_type'], 'big_front'); ?>>Big
                                        front</option>
                                    <option value="custom_back" <?php selected($fields['logo_type'], 'custom_back'); ?>>
                                        Custom back</option>
                                    <option value="same" <?php selected($fields['logo_type'], 'same'); ?>>Same</option>
                                </select>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="mini_url">Mini URL:</label>
                                <input type="text" name="mini_url" id="mini_url"
                                    value="<?php echo esc_attr($fields['mini_url']); ?>" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="mini_header">Mini Header:</label>
                                <input type="text" name="mini_header" id="mini_header"
                                    value="<?php echo esc_attr($fields['mini_header']); ?>" />
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="logo">Logo:</label>
                                <div class="form-group-flex">
                                    <input type="text" name="logo" id="logo"
                                        value="<?php echo esc_attr($fields['logo']); ?>" />
                                    <input type="button" id="logo_button" class="button upload-image-button"
                                        value="Upload Image" />
                                    <input type="button" id="remove_logo_button" class="button remove-image-button"
                                        value="Remove Image"
                                        style="display: <?php echo $fields['logo'] ? 'inline-block' : 'none'; ?>;" />
                                </div>
                                <img src="<?php echo esc_attr($fields['logo']); ?>"
                                    style="max-width: 300px; display: <?php echo $fields['logo'] ? 'block' : 'none'; ?>;" />
                            </div>
                            <br>
                        </div>
                    </div>
                </div>

                <button id="om--create-new-client" class="allarnd--regular-button ml_add_loading" type="submit">Save
                    Client</button>
            </form>
        </div>
    <?php endwhile; ?>

</main>
<?php get_footer(); ?>