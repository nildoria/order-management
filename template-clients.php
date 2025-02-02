<?php
/**
 * Template Name: Client List
 */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

// Handle search if applicable
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

?>
<main class="site-main" role="main">

    <div id="client-lists">
        <div class="allaround-client-header">
            <div class="allaround-client-top-left">
                <h2>Clients</h2>
                <a href="<?php echo esc_url(home_url('/create-client/')); ?>">Add New Client</a>
                <?php do_action('om_campaign_action'); ?>
            </div>
            <div class="allaround-client-search">
                <form method="get" action="<?php echo esc_url(home_url('/clients')); ?>">
                    <div class="client-search-wrapper">
                        <label for="search_input">Search Client:</label>
                        <input type="text" name="search" id="search_input" placeholder="Search..."
                            value="<?php echo esc_attr($search_query); ?>">
                    </div>
                    <div class="filter-wrapper-client-search">
                        <!-- Client Type Filter -->
                        <div class="filter-group">
                            <label for="client-type-select">Client Type:</label>
                            <select name="client_type" id="client-type-select">
                                <option value="">Select Type</option>
                                <option value="personal" <?php selected('personal', $_GET['client_type']); ?>>Personal
                                </option>
                                <option value="company" <?php selected('company', $_GET['client_type']); ?>>Company
                                </option>
                                <option value="not_tagged" <?php selected('not_tagged', $_GET['client_type']); ?>>Not
                                    Tagged</option>
                            </select>
                        </div>

                        <!-- Checkbox for Lighter & Darker Logos -->
                        <div id="logo-filter" style="display:none;">
                            <label>
                                <input type="radio" name="logo_filter" value="no_logos" id="filter-no-logos" <?php checked(isset($_GET['logo_filter']) && $_GET['logo_filter'] === 'no_logos'); ?>> No
                                Lighter & Darker Logos
                            </label>
                            <label>
                                <input type="radio" name="logo_filter" value="with_logos" id="filter-with-logos" <?php checked(isset($_GET['logo_filter']) && $_GET['logo_filter'] === 'with_logos'); ?>>
                                With Lighter & Darker Logos
                            </label>

                            <!-- New MiniSite Filter -->
                            <label>
                                <input type="checkbox" name="mini_site_filter" value="no_mini_site"
                                    id="filter-no-mini-site" <?php checked(isset($_GET['mini_site_filter']) && $_GET['mini_site_filter'] === 'no_mini_site'); ?>> No MiniSite URL & MiniSite Header
                            </label>
                        </div>

                        <a href="#" id="export-csv-btn" class="button">Export Clients</a>

                        <input type="submit" value="Filter">
                    </div>
                </form>
            </div>

        </div>

        <div class="client-list-wrapper">
            <?php
            $args = array(
                'post_type' => 'client',
                'posts_per_page' => 100, // Number of clients per page
                'paged' => $paged,      // Handle pagination
                'meta_query' => array(
                    'relation' => 'AND'
                ),
            );

            if (!empty($search_query)) {
                $args['meta_query'][] = array(
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
                    ),
                );
            }

            // Handle client type filtering
            $client_type = isset($_GET['client_type']) ? sanitize_text_field($_GET['client_type']) : '';

            if ($client_type === 'company') {
                $args['meta_query'][] = array(
                    'key' => 'client_type',
                    'value' => 'company',
                    'compare' => '='
                );

                // Check the logo filter value
                $logo_filter = isset($_GET['logo_filter']) ? sanitize_text_field($_GET['logo_filter']) : '';
                // Check the MiniSite filter value
                $mini_site_filter = isset($_GET['mini_site_filter']) ? sanitize_text_field($_GET['mini_site_filter']) : '';

                if ($logo_filter === 'no_logos') {
                    // Filter for clients without logos
                    $args['meta_query'][] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'dark_logo',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'lighter_logo',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'dark_logo',
                            'value' => '',
                            'compare' => '='
                        ),
                        array(
                            'key' => 'lighter_logo',
                            'value' => '',
                            'compare' => '='
                        )
                    );
                } elseif ($logo_filter === 'with_logos') {
                    // Filter for clients with both logos
                    $args['meta_query'][] = array(
                        'relation' => 'AND',
                        array(
                            'key' => 'dark_logo',
                            'compare' => 'EXISTS'
                        ),
                        array(
                            'key' => 'lighter_logo',
                            'compare' => 'EXISTS'
                        ),
                        array(
                            'key' => 'dark_logo',
                            'value' => '',
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'lighter_logo',
                            'value' => '',
                            'compare' => '!='
                        )
                    );
                }

                // Handle the MiniSite filter
                if ($mini_site_filter === 'no_mini_site') {
                    // Filter for clients without MiniSite URL & MiniSite Header
                    $args['meta_query'][] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'mini_url',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'mini_header',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'mini_url',
                            'value' => '',
                            'compare' => '='
                        ),
                        array(
                            'key' => 'mini_header',
                            'value' => '',
                            'compare' => '='
                        )
                    );
                }
            } elseif ($client_type === 'personal') {
                $args['meta_query'][] = array(
                    'key' => 'client_type',
                    'value' => 'personal',
                    'compare' => '='
                );
            } elseif ($client_type === 'not_tagged') {
                $args['meta_query'][] = array(
                    'key' => 'client_type',
                    'compare' => 'NOT EXISTS'
                );
            }

            $clients_query = new WP_Query($args);

            // Preload order counts for all clients
            $client_ids = wp_list_pluck($clients_query->posts, 'ID');
            $order_counts = array();

            if (!empty($client_ids)) {
                global $wpdb;

                $results = $wpdb->get_results(
                    "SELECT meta_value AS client_id, COUNT(*) AS order_count
                    FROM {$wpdb->prefix}postmeta
                    WHERE meta_key = 'client_id'
                    AND meta_value IN (" . implode(',', array_map('intval', $client_ids)) . ")
                    GROUP BY meta_value"
                );

                foreach ($results as $result) {
                    $order_counts[$result->client_id] = $result->order_count;
                }
            }

            if ($clients_query->have_posts()): ?>
                <table id="client-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Mini Header</th>
                            <th>Mini URL</th>
                            <th>MiniCreated</th>
                            <th>Initial Mini Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($clients_query->have_posts()) {
                            $clients_query->the_post();
                            $client_id = get_the_ID();
                            $first_name = get_post_meta($client_id, 'first_name', true);
                            $last_name = get_post_meta($client_id, 'last_name', true);
                            $email = get_post_meta($client_id, 'email', true);
                            $mini_header = get_post_meta($client_id, 'mini_header', true);
                            $mini_url = get_post_meta($client_id, 'mini_url', true);
                            $minisite_created = get_post_meta($client_id, 'minisite_created', true);
                            $initial_msg = get_post_meta($client_id, 'initial_minisite_message', true);

                            // Get preloaded order count for this client
                            $order_count = isset($order_counts[get_the_ID()]) ? $order_counts[get_the_ID()] : 0;
                            ?>
                            <tr data-client-id="<?php echo $client_id; ?>">
                                <!-- First Name -->
                                <td class="client-first-name">
                                    <span class="cell-text"><?php echo esc_html($first_name); ?></span>
                                    <input type="text" class="editable-field" data-field="first_name"
                                        value="<?php echo esc_attr($first_name); ?>" style="display: none;">
                                    <span class="edit-icon">✏️</span>
                                    <span class="submit-icon" style="display: none;">✔️</span>
                                </td>
                                <!-- Last Name -->
                                <td class="client-last-name">
                                    <span class="cell-text"><?php echo esc_html($last_name); ?></span>
                                    <input type="text" class="editable-field" data-field="last_name"
                                        value="<?php echo esc_attr($last_name); ?>" style="display: none;">
                                    <span class="edit-icon">✏️</span>
                                    <span class="submit-icon" style="display: none;">✔️</span>
                                </td>
                                <!-- Email -->
                                <td class="client-email">
                                    <span class="cell-text"><?php echo esc_html($email); ?></span>
                                    <input type="email" class="editable-field" data-field="email"
                                        value="<?php echo esc_attr($email); ?>" style="display: none;">
                                    <span class="edit-icon">✏️</span>
                                    <span class="submit-icon" style="display: none;">✔️</span>
                                </td>
                                <!-- Mini Header -->
                                <td class="client-mini-header">
                                    <span class="cell-text"><?php echo esc_html($mini_header); ?></span>
                                    <input type="text" class="editable-field" data-field="mini_header"
                                        value="<?php echo esc_attr($mini_header); ?>" style="display: none;">
                                    <span class="edit-icon">✏️</span>
                                    <span class="submit-icon" style="display: none;">✔️</span>
                                </td>
                                <!-- Mini URL -->
                                <td class="client-mini-url">
                                    <span class="cell-text"><?php echo esc_html($mini_url); ?></span>
                                    <input type="url" class="editable-field" data-field="mini_url"
                                        value="<?php echo esc_attr($mini_url); ?>" style="display: none;">
                                    <span class="edit-icon">✏️</span>
                                    <span class="submit-icon" style="display: none;">✔️</span>
                                </td>
                                <!-- MiniCreated -->
                                <td class="client-mini-created">
                                    <span class="cell-text"><?php echo ($minisite_created === 'yes') ? 'Yes' : 'No'; ?></span>
                                </td>
                                <!-- Initial Minisite Message -->
                                <td class="client-initial-minisite-message">
                                    <span class="cell-text"><?php echo ($initial_msg === 'yes') ? 'Yes' : 'No'; ?></span>
                                </td>
                                <!-- Actions -->
                                <td class="client-actions">
                                    <div class="allaround--client-actions">
                                        <a href="<?php echo esc_url(admin_url('admin-ajax.php') . '?action=get_client_orders&client_id=' . get_the_ID() . '&_nonce=' . wp_create_nonce('get_client_nonce')); ?>"
                                            class="allaround--client-orders">
                                            View Orders
                                            <?php if ($order_count > 1): ?>
                                                <span
                                                    class="order-count-bubble client-list-bubble"><?php echo $order_count; ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <a href="<?php echo esc_url(get_permalink()); ?>">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        wp_reset_postdata();
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
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
                        'add_args' => array(
                            'search' => !empty($search_query) ? $search_query : false,
                            'client_type' => !empty($client_type) ? $client_type : false,
                            'no_logos' => isset($_GET['no_logos']) ? true : false,
                        ),
                    ));
                    ?>
                </div>

                <?php wp_reset_postdata();
            else: ?>
                <p><?php _e('No clients found.'); ?></p>
            <?php endif;
            ?>
        </div>
    </div>

</main>

<script>
    jQuery(document).ready(function ($) {
        // Show/hide logo filter based on client type
        $('#client-type-select').on('change', function () {
            if ($(this).val() === 'company') {
                $('#logo-filter').show();
            } else {
                $('#logo-filter').hide();
                $('input[name="logo_filter"]').prop('checked', false); // Uncheck the radio buttons when hiding
                $('input[name="mini_site_filter"]').prop('checked', false); // Uncheck the checkbox for MiniSite filter
            }
        });

        // Initially hide or show the radio buttons based on the selected option
        if ($('#client-type-select').val() === 'company') {
            $('#logo-filter').show();
        } else {
            $('#logo-filter').hide();
        }
    });
</script>
<?php get_footer(); ?>