<?php
/**
 * Template Name: Agent Sales
 */

get_header();

// Restrict access to admin users only
if (!current_user_can('administrator')) {
    echo '<h3 class="login_require_error"><b>Access Denied</b>: You do not have permission to view this page.</h3>';
    exit;
}

// Fetch the current year's sales data
$current_year = date('Y');

// Include a hidden field with the current year for AJAX
?>
<main id="agent-sales-page" class="site-main" role="main">
    <h2>Agent Sales</h2>

    <!-- Date Range Filter Form -->
    <form id="filter-form" class="date-range-form">
        <div class="form-group">
            <label for="year-select">Select Year:</label>
            <select id="year-select" name="year_filter">
                <?php
                for ($year = $current_year; $year >= ($current_year - 5); $year--) {
                    $selected = ($year == $current_year) ? 'selected' : '';
                    echo "<option value='$year' $selected>$year</option>";
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
            <button type="button" id="filter-submit" class="filter_submit">Filter</button>
            <button type="reset" class="reset_button">Reset</button>
        </div>
    </form>
    <div id="agent-sales-results">
        <!-- Current year's sales data will be loaded here -->
        <p>Loading current year sales...</p>
    </div>
</main>

<script>
    const currentYear = "<?php echo $current_year; ?>";
</script>

<?php get_footer(); ?>