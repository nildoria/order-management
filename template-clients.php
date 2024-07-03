<?php
/**
 * Template Name: Client List
 */

get_header();

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
            </div>
            <div class="allaround-client-search">
                <form method="get" action="<?php echo esc_url(home_url('/clients')); ?>">
                    <label for="search_input">Search Client:</label>
                    <input type="text" name="search" id="search_input" placeholder="Search..." value="<?php echo esc_attr($search_query); ?>">
                </form>
            </div>
        </div>
        
        <div class="client-list-wrapper">
            <?php
            

            $args = array(
                'post_type'      => 'client',
                'posts_per_page' => 10,
                'paged'          => $paged
            );

            if ( ! empty( $search_query ) ) {
                $args['meta_query'] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'first_name',
                        'value'   => $search_query,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_query,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'full_name',
                        'value'   => $search_query,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'email',
                        'value'   => $search_query,
                        'compare' => 'LIKE'
                    )
                );
            }

            $clients_query = new WP_Query($args);

            if ($clients_query->have_posts()) : ?>
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
                        while ($clients_query->have_posts()) : $clients_query->the_post(); ?>
                            <tr>
                                <td class="td_index"><?php echo $index; ?></td>
                                <td><?php the_title(); ?></td>
                                <td><?php echo esc_html(get_post_meta(get_the_ID(), 'email', true)); ?></td>
                                <td>
                                    <div class="allaround--client-actions">
                                        <a href="<?php echo esc_url( admin_url('admin-ajax.php') . '?action=get_client_orders&client_id=' . get_the_ID() . '&_nonce=' . wp_create_nonce( 'get_client_nonce' ) ); ?> " class="allaround--client-orders">View Orders</a>
                                        <a href="<?php echo esc_url(get_permalink()); ?>">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        $index++;
                        endwhile; ?>
                    </tbody>
                </table>

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
                        'add_args' => false,
                        'add_fragment' => '',
                    ));
                    ?>
                </div>

                <?php wp_reset_postdata();
            else : ?>
                <p><?php _e('No clients found.'); ?></p>
            <?php endif;
            ?>
        </div>
    </div>


    

</main>
<?php get_footer(); ?>