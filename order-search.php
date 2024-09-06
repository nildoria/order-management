<?php
/* Template Name: Search Order */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();


?>
<main id="om__searchOrderPage" class="site-main" role="main">
    <div id="post-search">
        <h2>Filter Orders</h2>
        <!-- Search Field -->
        <input type="text" id="search-field" placeholder="Search posts...">

        <div class="filter-wrapper-order-search">
            <!-- Order Status Filter -->
            <div class="filter-group">
                <label class="bold">Order Status:</label>
                <label><input type="checkbox" name="order_status" value="processing" id="filter-status-processing">
                    Processing</label>
                <label><input type="checkbox" name="order_status" value="completed" id="filter-status-completed">
                    Completed</label>
                <label><input type="checkbox" name="order_status" value="static" id="filter-status-static">
                    Static</label>
            </div>

            <!-- Order Type Filter -->
            <div class="filter-group">
                <label class="bold" for="order-type-select">Order Type:</label>
                <select id="order-type-select">
                    <option value="">Select Type</option>
                    <option value="personal">Personal</option>
                    <option value="company">Company</option>
                    <option value="not_tagged">Not Tagged</option>
                </select>

                <!-- Checkbox for Lighter & Darker Logos -->
                <div id="logo-filter" style="display:none;">
                    <label><input type="radio" name="logo_filter" value="no_logos" id="filter-no-logos"> No Lighter & Darker Logos</label>
                    <label><input type="radio" name="logo_filter" value="with_logos" id="filter-with-logos"> With Lighter & Darker Logos</label>
                </div>

            </div>
        </div>

        <!-- Posts List -->
        <div id="post-list">
            <!-- Posts will be loaded here -->
        </div>
    </div>
</main>

<script>
    jQuery(document).ready(function ($) {
        let debounceTimer;

        function fetchPosts() {
            const searchQuery = $('#search-field').val();
            const orderStatus = $('input[name="order_status"]:checked').val(); // Get the checked order status
            const orderType = $('#order-type-select').val(); // Get the selected order type
            const logoFilter = $('input[name="logo_filter"]:checked').val(); // Get the selected logo filter option

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'search_posts',
                    query: searchQuery,
                    order_status: orderStatus,
                    order_type: orderType,
                    logo_filter: logoFilter // Pass the logo filter value
                },
                success: function (response) {
                    $('#post-list').html(response);
                }
            });
        }

        // Search functionality with debounce
        $('#search-field').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchPosts, 300); // 300ms debounce time
        });

        // Filter change event for order status, order type, and logo filter
        $('input[name="order_status"], #order-type-select, input[name="logo_filter"]').on('change', function () {
            fetchPosts(); // Fetch posts when filters change
        });

        // Show/hide the logo filter when company is selected
        $('#order-type-select').on('change', function () {
            if ($(this).val() === 'company') {
                $('#logo-filter').show();
            } else {
                $('#logo-filter').hide();
                $('input[name="logo_filter"]').prop('checked', false); // Reset the radio buttons when hiding
            }
        });

        // Load the first 30 posts initially
        fetchPosts();
    });

</script>


<?php
get_footer();
?>