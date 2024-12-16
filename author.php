<?php
/**
 * The template for displaying author pages with AJAX filtering.
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
get_header();

// Restrict access to logged-in users only
if (!is_user_logged_in()) {
    wp_redirect(home_url()); // Redirect to home page if not logged in
    exit;
}

// Get the current user's data
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// Get the current author's ID
$author_id = get_query_var('author');

// Allow access if the user is an admin or the author of the page
if (!current_user_can('administrator') && $current_user_id !== (int) $author_id) {
    echo '<h3 class="login_require_error"><b>Access Denied</b>: You do not have permission to view this page.</h3>';
    exit;
}

$current_year = date('Y');
?>

<main class="site-main" role="main">
    <header class="page-header om_agent_page_header">
        <?php
        $author_display_name = get_the_author_meta('display_name', $author_id);
        ?>
        <h2>Agent Name: <?php echo esc_html($author_display_name); ?></h2>
    </header>

    <div class="page-content">
        <!-- Filter Form -->
        <form id="filter-form" class="om_agent_filter_form date-range-form">
            <input type="hidden" id="author-id" name="author_id" value="<?php echo esc_attr($author_id); ?>">
            <div class="form-group">
                <label for="year-select">Select Year:</label>
                <select id="year-select" name="year_filter">
                    <?php
                    for ($year = $current_year; $year >= ($current_year - 5); $year--) {
                        $selected = ($year == $current_year) ? 'selected' : '';
                        echo "<option value='$year'>$year</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="text" id="start_date" name="start_date">
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="text" id="end_date" name="end_date">
            </div>
            <div class="form-group">
                <button type="button" id="filter-submit-agent" class="filter_submit">Filter</button>
                <button type="reset" class="reset_button">Reset</button>
            </div>
        </form>

        <!-- Results Container -->
        <div id="ajax-results" class="">
            <p>Loading current year sales...</p>
        </div>
    </div>
</main>

<script>
    const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    const currentYear = "<?php echo $current_year; ?>";
    const authorId = "<?php echo esc_attr($author_id); ?>";
</script>

<?php
get_footer();
