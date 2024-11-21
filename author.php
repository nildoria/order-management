<?php
/**
 * The template for displaying author pages.
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
get_header();
// Get the current author's ID
$author_id = get_query_var('author');
?>
<main class="site-main" role="main">

    <?php if (apply_filters('hello_elementor_page_title', true)): ?>
        <header class="page-header om_agent_page_header">
            <?php
                $author_display_name = get_the_author_meta('display_name', $author_id);
                ?>
            <h2>Agent Name: <?php echo esc_html($author_display_name); ?></h2>
        </header>
    <?php endif; ?>
    <div class="page-content om_agent_sale_page">
        <?php
        // Query the author's posts
        $args = [
            'author' => $author_id,
            'posts_per_page' => -1, // Fetch all posts
            'post_type' => 'post',
            'post_status' => 'publish',
        ];
        $author_posts = new WP_Query($args);

        // Initialize arrays for storing sales data
        $sales_by_day = [];
        $sales_by_month = [];

        // Process posts
        if ($author_posts->have_posts()) {
            while ($author_posts->have_posts()) {
                $author_posts->the_post();

                // Get the order_total meta value
                $order_total = get_post_meta(get_the_ID(), 'order_total', true);
                if (is_numeric($order_total)) {
                    $order_total = floatval($order_total);

                    // Get the post date
                    $post_date = get_the_date('d/m/Y'); // Format: Day/Month/Year
                    $post_month = get_the_date('m/Y');  // Format: Month/Year
        
                    // Add to daily total
                    if (!isset($sales_by_day[$post_date])) {
                        $sales_by_day[$post_date] = 0;
                    }
                    $sales_by_day[$post_date] += $order_total;

                    // Add to monthly total
                    if (!isset($sales_by_month[$post_month])) {
                        $sales_by_month[$post_month] = 0;
                    }
                    $sales_by_month[$post_month] += $order_total;
                }
            }
            wp_reset_postdata();
        }

        // Display Sales By Month
        if (!empty($sales_by_month)) {
            echo '<div class="om__sales_by_month">';
            echo '<h3>Sales By Month</h3>';
            echo '<ul>';
            foreach ($sales_by_month as $month => $total) {
                echo '<li>' . esc_html($month) . ' : ' . number_format($total, 2) . '₪</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Display Sales By Days for the Current Month
        if (!empty($sales_by_day)) {
            // Get the current month and year
            $current_month = date('m');
            $current_year = date('Y');

            // Filter sales by current month
            $current_month_sales_by_day = [];
            foreach ($sales_by_day as $day => $total) {
                list($day_month, $day_year) = explode('/', substr($day, 3)); // Extract month/year
        
                if ($day_month == $current_month && $day_year == $current_year) {
                    $current_month_sales_by_day[$day] = $total;
                }
            }

            if (!empty($current_month_sales_by_day)) {
                echo '<div class="om__sales_by_days">';
                echo '<h3>Sales By Days (This Month)</h3>';
                echo '<ul>';
                foreach ($current_month_sales_by_day as $day => $total) {
                    echo '<li>' . esc_html($day) . ' : ' . number_format($total, 2) . '₪</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }
        ?>

    </div>
</main>

<?php
get_footer();