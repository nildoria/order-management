<?php
/**
 * Template Name: Create Client
 */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();


?>
<main class="site-main" role="main">

    <div id="create-client-form">
        <h2>Create Client</h2>
        <form id="addClientForm" data-type="create" method="post">
            <?php wp_nonce_field('create_client', 'create_client_nonce'); ?>

            <div class="om_create_order_box">

                <div class="om_create__product_details">

                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="client_type" id="client_type">
                            <option value="client">Personal</option>
                            <option value="company">Company</option>
                        </select>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="firstName">First Name:</label>
                        <input type="text" id="firstName" name="first_name" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="lastName">Last Name:</label>
                        <input type="text" id="lastName" name="last_name" required>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="invoice">Invoice Name:</label>
                        <input type="text" name="invoice" id="invoice" value="" />
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address_1">
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city">
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">Select status</option>
                            <option value="client">Client</option>
                            <option value="welcome">Welcome</option>
                            <option value="company_prospect">Company Prospect</option>
                        </select>
                    </div>
                    <br>

                    <div class="form-group client_token_field">
                        <label for="token">Token:</label>
                        <input type="text" name="token" id="token" />
                    </div>
                    <br>

                    <div class="form-group form-group-row">
                        <label>Subscribed:</label>
                        <div class="form-group-flex">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="subscribed" id="yes" value="yes"
                                    checked>
                                <label class="form-check-label" for="yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="subscribed" id="no" value="no">
                                <label class="form-check-label" for="no">No</label>
                            </div>
                        </div>
                    </div>

                    <br>
                    <br>

                </div>

                <div class="om_create__customer_details">
                    <div class="om_hidden_details">
                        <div class="om_customer__details_title">
                            <h4><?php echo esc_html__('Company Details:', 'hello-elementor'); ?></h4>
                        </div>

                        <div class="form-group">
                            <label for="dark_logo">Dark Logo:</label>
                            <div class="form-group-flex">
                                <input type="text" name="dark_logo" id="dark_logo" value="" />
                                <input type="button" id="dark_logo_button" class="button upload-image-button"
                                    value="Upload Image" />
                                <input type="button" id="remove_dark_logo_button" class="button remove-image-button"
                                    value="Remove Image" style="display: none; ?>;" />
                            </div>
                            <img src="" style="max-width: 300px; display: none;" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="lighter_logo">Lighter Logo:</label>
                            <div class="form-group-flex">
                                <input type="text" name="lighter_logo" id="lighter_logo" value="" />
                                <input type="button" id="lighter_logo_button" class="button upload-image-button"
                                    value="Upload Image" />
                                <input type="button" id="remove_lighter_logo_button" class="button remove-image-button"
                                    value="Remove Image" style="display: none; ?>;" />
                            </div>
                            <img src="" style="max-width: 300px; display: none;" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="back_light">Back Light:</label>
                            <div class="form-group-flex">
                                <input type="text" name="back_light" id="back_light" value="" />
                                <input type="button" id="back_light_button" class="button upload-image-button"
                                    value="Upload Image" />
                                <input type="button" id="remove_back_light_button" class="button remove-image-button"
                                    value="Remove Image" style="display: none; ?>;" />
                            </div>
                            <img src="" style="max-width: 300px; display: none;" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="back_dark">Back Dark:</label>
                            <div class="form-group-flex">
                                <input type="text" name="back_dark" id="back_dark" value="" />
                                <input type="button" id="back_dark_button" class="button upload-image-button"
                                    value="Upload Image" />
                                <input type="button" id="remove_back_dark_button" class="button remove-image-button"
                                    value="Remove Image" style="display: none; ?>;" />
                            </div>
                            <img src="" style="max-width: 300px; display: none;" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="logo_type">Logo Type:</label>
                            <select name="logo_type" id="logo_type">
                                <option value="same">Same</option>
                                <option value="chest_only">Chest only</option>
                                <option value="big_front">Big front</option>
                                <option value="custom_back">Custom back</option>
                            </select>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="mini_url">Mini URL:</label>
                            <input type="text" name="mini_url" id="mini_url" value="" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="mini_header">Mini Header:</label>
                            <input type="text" name="mini_header" id="mini_header" value="" />
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="logo">Logo:</label>
                            <div class="form-group-flex">
                                <input type="text" name="logo" id="logo" value="" />
                                <input type="button" id="logo_button" class="button upload-image-button"
                                    value="Upload Image" />
                                <input type="button" id="remove_logo_button" class="button remove-image-button"
                                    value="Remove Image" style="display: none; ?>;" />
                            </div>
                            <img src="" style="max-width: 300px; display: none;" />
                        </div>
                        <br>
                    </div>
                </div>
            </div>

            <button id="om--create-new-client" class="allarnd--regular-button ml_add_loading" type="submit">Create
                Client</button>
        </form>
    </div>

</main>
<?php get_footer(); ?>