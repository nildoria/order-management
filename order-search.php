<?php
/* Template Name: Search Order */

get_header();

// Restrict access to logged-in users
restrict_access_to_logged_in_users();


?>
<main id="om__searchOrderPage" class="site-main" role="main">
    <div id="post-search">
        <input type="text" id="search-field" placeholder="Search posts...">
        <div id="post-list">
            <!-- Posts will be loaded here -->
        </div>
    </div>

</main>
<script>
    jQuery(document).ready(function ($) {
        let debounceTimer;
        $('#search-field').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                const searchQuery = $('#search-field').val();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'search_posts',
                        query: searchQuery
                    },
                    success: function (response) {
                        $('#post-list').html(response);
                    }
                });
            }, 300); // 300ms debounce time
        });

        // Load all posts initially
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'search_posts',
                query: ''
            },
            success: function (response) {
                $('#post-list').html(response);
            }
        });
    });
</script>


<?php
get_footer();
?>