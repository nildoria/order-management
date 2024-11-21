<?php
/* Template Name: Search Order */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();


?>
<main id="om__searchOrderPage" class="site-main" role="main">
    <div id="loading-indicator" style="display:none;">
        <div class="loading-indicator-inner">Loading...</div>
    </div>

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
        <!-- Month and Order Source Filter -->
        <div class="filter-group order-total-filters">

            <select id="year-select">
                <option value="">Select Year</option>
                <?php
                $current_year = date('Y');
                for ($year = $current_year; $year >= ($current_year - 5); $year--) {
                    // Automatically select the current year
                    $selected = ($year == $current_year) ? 'selected' : '';
                    echo "<option value='$year' $selected>$year</option>";
                }
                ?>
            </select>
            <select id="month-select">
                <option value="">Select Month</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>

            <!-- Day Filter -->
            <input type="text" id="day-select" placeholder="Select Year / Month / Day" autocomplete="off">

            <select id="order-source-select">
                <option value="">Select Source</option>
                <option value="mainSite_order">MainSite Order</option>
                <option value="miniSite_order">MiniSite Order</option>
                <option value="flashSale_order">FlashSale Order</option>
                <option value="manual_order">Manual Order</option>
                <!-- Add more sources as needed -->
            </select>
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
        // Custom handler function
        var setCalsClearButton = function(year,month,elem){

            var afterShow = function(){
                var d = new $.Deferred();
                var cnt = 0;
                setTimeout(function(){
                    if(elem.dpDiv[0].style.display === "block"){
                        d.resolve();
                    }
                    if(cnt >= 500){
                        d.reject("datepicker show timeout");
                    }
                    cnt++;
                },10);
                return d.promise();
            }();

            afterShow.done(function(){
                $('.ui-datepicker').css('z-index', 2000);

                var buttonPane = $( elem ).datepicker( "widget" ).find( ".ui-datepicker-buttonpane" );

                var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-primary ui-corner-all" type="button">Clear</button>');
                btn.off("click").on("click", function () {
                    $.datepicker._clearDate( elem.input[0] );
                });
                btn.appendTo( buttonPane );
            });
        }
        // Initialize datepicker for day selection
        $('#day-select').datepicker({
            dateFormat: 'yy-mm-dd', // Set date format for easy backend parsing
            changeMonth: true, // Enable month dropdown
            changeYear: true,  // Enable year dropdown
            showButtonPanel: true,
            beforeShow : function(inst, elem){
                setCalsClearButton(null, null, elem);
            },
            onChangeMonthYear: setCalsClearButton,
            onChangeMonthYear: function (year, month, inst) {
                // Format month and year as "YYYY-MM" and set it in #day-select field
                const formattedMonthYear = `${year}-${String(month).padStart(2, '0')}`;
                
                // Update the #day-select field with the selected month and year
                $('#day-select').val(formattedMonthYear);
                // Update the #year-select and #month-select dropdowns based on Datepicker month/year selection
                $('#year-select').val(year); // Set the year in the dropdown
                $('#month-select').val(String(month).padStart(2, '0')); // Set the month in the dropdown, padded to 2 digits

                toggleDateFields();
                fetchPosts(); // Trigger filtering as soon as the month or year changes
            },
            onSelect: function (dateText) {
                toggleDateFields();
                fetchPosts(); // Fetch posts when a full date (day included) is selected
            }
        });

        function fetchPosts() {
            const searchQuery = $('#search-field').val();
            const orderStatus = $('input[name="order_status"]:checked').val();
            const orderType = $('#order-type-select').val();
            const logoFilter = $('input[name="logo_filter"]:checked').val();
            const selectedMonth = $('#month-select').val(); // Get the selected month
            const orderSource = $('#order-source-select').val(); // Get the selected order source
            const selectedYear = $('#year-select').val();
            const selectedDay = $('#day-select').val();

            // Show the loading indicator
            $('#loading-indicator').show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'search_posts',
                    query: searchQuery,
                    order_status: orderStatus,
                    order_type: orderType,
                    logo_filter: logoFilter,
                    month: selectedMonth,
                    year: selectedYear,
                    order_source: orderSource,
                    day: selectedDay
                },
                beforeSend: function () {
                    // Optionally, show a loading state before the request starts
                    $('#loading-indicator .loading-indicator-inner').text('Fetching data, please wait...');
                },
                success: function (response) {
                    $('#post-list').html(response);
                },
                error: function () {
                    $('#post-list').html('<p>An error occurred while fetching data. Please try again.</p>');
                },
                complete: function () {
                    // Hide the loading indicator when the request is complete
                    $('#loading-indicator').hide();
                }
            });
        }

        // Add change event listeners for the new filters
        $('#month-select, #order-source-select, #year-select').on('change', function () {
            fetchPosts(); // Fetch posts when month or order source changes
        });

        function toggleDateFields() {
            const selectedDay = $('#day-select').val();

            if (selectedDay !== '') {
                // If #day-select is not empty, disable #year-select and #month-select
                $('#year-select').attr('disabled', 'disabled').addClass('disabled');
                $('#month-select').attr('disabled', 'disabled').addClass('disabled');
            } else {
                // If #day-select is empty, enable #year-select and #month-select
                $('#year-select').removeAttr('disabled').removeClass('disabled');
                $('#month-select').removeAttr('disabled').removeClass('disabled');
            }
        }

        // Trigger the toggleDateFields function when #day-select changes
        $('#day-select').on('change', function () {
            fetchPosts(); // Fetch posts when the day is selected or cleared
        });

        // Initial check when the page loads
        toggleDateFields();



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