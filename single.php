<?php
/**
 * Sinlge Post
 *
 */
get_header();

?>
<main id="main" class="site-main" role="main">

    <div class="alarnd--single-content mockup-revision-page">
        <?php while (have_posts()):
            the_post();

            update_order_status();
            $order_number = get_post_meta(get_the_ID(), 'order_number', true);
            $shipping_method = get_post_meta(get_the_ID(), 'shipping_method', true);
            $order_status = get_post_meta(get_the_ID(), 'order_status', true);

            // Fetch the posts from the REST API
            $response = wp_remote_get('http://artwork.test/wp-json/wp/v2/posts/');

            if (is_wp_error($response)) {
                // Handle the error
            } else {
                // Decode the JSON response
                $posts = json_decode(wp_remote_retrieve_body($response));

                // Loop through the posts
                foreach ($posts as $post) {
                    // Check if the order number matches
                    if ($post->artwork_meta->order_number === $order_number) {
                        // Set the approved_proof variable
                        $approved_proof = $post->artwork_meta->approval_status;
                        $proof_approved_time = $post->artwork_meta->proof_approved_time;
                        $fetched_artwork_comments = $post->artwork_meta->artwork_comments;
                        break;
                    }
                }
            }
            ?>

            <div id="order_mngmnt_headings" class="order_mngmnt_headings">
                <h6><?php echo esc_html__('Order Number:', 'hello-elementor'); ?>     <?php echo $order_number; ?></h6>
                <h6><?php echo esc_html__('Shipping:', 'hello-elementor'); ?>     <?php echo $shipping_method; ?></h6>
                <h6><?php echo esc_html__('Status:', 'hello-elementor'); ?>     <?php echo $order_status; ?></h6>
            </div>


            <div id="order_management_table_container" class="order_management_table_container">
                <img src="<?php echo get_template_directory_uri() ?>/assets/images/order-thumb.jpg" />
            </div>

            <div class="mockup-revision-activity-container">

                <h4>היסטוריית שינויים</h4>
                <div class="revision-activities-all">
                    <?php
                    // set the order number to a input type hidden fields value
                    echo '<input type="hidden" id="order_number" value="' . $order_number . '">';
                    if ($approved_proof) {
                        ?>
                        <div class="revision-activity customer-message mockup-approved-comment">
                            <div class="revision-activity-avatar">
                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/Favicon-2.png" />
                            </div>
                            <div class="revision-activity-content">
                                <div class="revision-activity-title">
                                    <h5>AllAround</h5>
                                    <span>
                                        <?php
                                        // $proof_approved_time = get_post_meta(get_the_ID(), 'proof_approved_time', true);
                                
                                        if (isset($proof_approved_time) && !empty($proof_approved_time)) {
                                            $approval_date = date_i18n(get_option('date_format') . ' \ב- ' . get_option('time_format'), strtotime($proof_approved_time));
                                            echo $approval_date;
                                        } else {
                                            echo '';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="revision-activity-description">
                                    <span class="revision-comment-title">ההדמיות אושרו על ידי הלקוח <img
                                            src="<?php echo get_template_directory_uri() ?>/assets/images/mark_icon-svg.svg"
                                            alt=""></span></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    if ($fetched_artwork_comments) {
                        $fetched_artwork_comments = array_reverse($fetched_artwork_comments);

                        foreach ($fetched_artwork_comments as $comment) {
                            $comment_name = $comment->artwork_comment_name;

                            // Get the comment text and convert newline characters to HTML line breaks
                            $comment_text = nl2br($comment->artwork_comments_texts);

                            $comment_date = '';
                            if (isset($comment->artwork_comment_date) && !empty($comment->artwork_comment_date)) {
                                $comment_date = date_i18n(get_option('date_format') . ' \ב- ' . get_option('time_format'), strtotime($comment->artwork_comment_date));
                            }

                            $image_html = '';

                            if (!empty($comment->artwork_new_file)) {
                                $image_html .= '<div class="artwork-new-file">';

                                if (pathinfo($comment->artwork_new_file, PATHINFO_EXTENSION) == 'pdf') {
                                    $image_html .= '<img src="' . get_template_directory_uri() . '/assets/images/pdf-icon.svg" alt="Placeholder">';
                                } else {
                                    $image_html .= '<img src="' . esc_url($comment->artwork_new_file) . '" alt="Artwork Image">';
                                }

                                $image_html .= '</div>';
                            }


                            ?>
                            <div
                                class="revision-activity <?php echo $comment_name === 'AllAround' ? 'allaround-message' : 'customer-message'; ?>">
                                <div class="revision-activity-avatar">
                                    <?php if ($comment_name === 'AllAround'): ?>
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/images/Favicon-2.png" />
                                    <?php else: ?>
                                        <span>
                                            <?php echo substr($comment_name, 0, 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="revision-activity-content">
                                    <div class="revision-activity-title">
                                        <h5>
                                            <?php echo $comment_name; ?>
                                        </h5>
                                        <span>
                                            <?php echo $comment_date; ?>
                                        </span>
                                    </div>
                                    <div class="revision-activity-description">
                                        <span class="revision-comment-title">
                                            <?php echo $comment_name === 'AllAround' ? 'הדמיה הועלתה' : 'ההערות הבאות נוספו:'; ?>
                                        </span>
                                        <?php echo $image_html; ?>
                                        <div>
                                            <?php echo $comment_text; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        // If there are no comments
                        echo '<p>No revision history available.</p>';
                    }
                    ?>
                </div>
            </div>



        <?php endwhile; ?>
    </div>
</main>
<script>
    jQuery(document).ready(function ($) {
        // Get the order number from the current post
        var orderNumber = document.querySelector('#order_number').value;

        // Fetch the posts from the REST API
        fetch('http://artwork.test/wp-json/wp/v2/posts/')
            .then(response => response.json())
            .then(posts => {
                // Loop through the posts
                for (let post of posts) {
                    // Check if the order number matches
                    if (post.artwork_meta.order_number === orderNumber) {
                        // Display the artwork meta
                        console.log(post.artwork_meta);
                        break;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>
<?php
get_footer();