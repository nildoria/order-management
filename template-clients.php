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
                        <input type="text" name="search" id="search_input" placeholder="Search..." value="<?php echo esc_attr($search_query); ?>">
                    </div>
                    <div class="filter-wrapper-client-search">
                        <!-- Client Type Filter -->
                        <div class="filter-group">
                            <label for="client-type-select">Client Type:</label>
                            <select name="client_type" id="client-type-select">
                                <option value="">Select Type</option>
                                <option value="personal" <?php selected('personal', $_GET['client_type']); ?>>Personal</option>
                                <option value="company" <?php selected('company', $_GET['client_type']); ?>>Company</option>
                                <option value="not_tagged" <?php selected('not_tagged', $_GET['client_type']); ?>>Not Tagged</option>
                            </select>
                        </div>

                        <!-- Checkbox for Lighter & Darker Logos -->
                        <div id="logo-filter" style="display:none;">
                            <label>
                                <input type="radio" name="logo_filter" value="no_logos" id="filter-no-logos" <?php checked(isset($_GET['logo_filter']) && $_GET['logo_filter'] === 'no_logos'); ?>> No Lighter & Darker Logos
                            </label>
                            <label>
                                <input type="radio" name="logo_filter" value="with_logos" id="filter-with-logos" <?php checked(isset($_GET['logo_filter']) && $_GET['logo_filter'] === 'with_logos'); ?>> With Lighter & Darker Logos
                            </label>

                            <!-- New MiniSite Filter -->
                            <label>
                                <input type="checkbox" name="mini_site_filter" value="no_mini_site" id="filter-no-mini-site" <?php checked(isset($_GET['mini_site_filter']) && $_GET['mini_site_filter'] === 'no_mini_site'); ?>> No MiniSite URL & MiniSite Header
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

            if ($clients_query->have_posts()): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="td_index"></th>
                            <th>Title</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $index = 1;
                            while ($clients_query->have_posts()):
                            $clients_query->the_post();
                            $first_name = get_post_meta(get_the_ID(), 'first_name', true);
                            $last_name = get_post_meta(get_the_ID(), 'last_name', true);
                            $full_name = trim($first_name . ' ' . $last_name);
                            ?>
                            <tr>
                                <td class="td_index"><?php echo $index; ?></td>
                                <td><?php echo !empty($full_name) ? esc_html($full_name) : get_the_title(); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), 'email', true)); ?></td>
                                <td>
                                    <div class="allaround--client-actions">
                                        <a href="<?php echo esc_url(admin_url('admin-ajax.php') . '?action=get_client_orders&client_id=' . get_the_ID() . '&_nonce=' . wp_create_nonce('get_client_nonce')); ?> "
                                            class="allaround--client-orders">View Orders</a>
                                        <a href="<?php echo esc_url(get_permalink()); ?>">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        $index++;
                        endwhile; ?>
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