<?php
/**
 * Template Name: Clients Without Logos
 */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

?>
<main class="site-main" role="main">
    <div id="client-lists">

        <div class="client-list-wrapper">
            <?php
            // Query clients that do not have dark_logo or lighter_logo
            $args = array(
                'post_type' => 'client',
                'posts_per_page' => 100, // Number of clients per page
                'paged' => $paged,      // Handle pagination
                'meta_query' => array(
                    'relation' => 'AND',
                    // Filter for company clients
                    array(
                        'key' => 'client_type',
                        'value' => 'company',
                        'compare' => '='
                    ),
                    // Filter for clients without dark_logo or lighter_logo
                    array(
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
                        ),
                    ),
                ),
            );

            $clients_query = new WP_Query($args);

            if ($clients_query->have_posts()): ?>

                <div class="allaround-client-header">
                    <div class="allaround-client-top-left">
                        <h2>Clients Without Logos: 
                        <?php
                        // Display the total count of clients without logos
                        $total_count = $clients_query->found_posts;
                        echo '<span>(' . esc_html($total_count) . ' clients)</span>';
                        ?></h2>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($clients_query->have_posts()):
                            $clients_query->the_post();
                            $first_name = get_post_meta(get_the_ID(), 'first_name', true);
                            $last_name = get_post_meta(get_the_ID(), 'last_name', true);
                            $full_name = trim($first_name . ' ' . $last_name);
                            ?>
                            <tr>
                                <td><?php echo !empty($full_name) ? esc_html($full_name) : get_the_title(); ?></td>
                                <td>
                                    <div class="allaround--client-actions">
                                        <a href="<?php echo esc_url(admin_url('admin-ajax.php') . '?action=get_client_orders&client_id=' . get_the_ID() . '&_nonce=' . wp_create_nonce('get_client_nonce')); ?> "
                                            class="allaround--client-orders">View Orders</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
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
                    ));
                    ?>
                </div>

                <?php wp_reset_postdata();
            else: ?>
                <p><?php _e('No clients found without logos.'); ?></p>
            <?php endif;
            ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>