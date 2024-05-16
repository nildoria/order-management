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
            $approved_proof = get_field('approved_proof');
            ?>
            <div class="revision--product-title">
                <h3>
                    <?php the_title(); ?>
                </h3>
                <?php
                // Get the order_status from post meta and show here
                $order_status = get_post_meta(get_the_ID(), 'order_status', true);

                if ($order_status) {
                    ?>
                    <div class="order-status">
                        <span><?php esc_html_e('Status:', 'hello-elementor'); ?></span>
                        <span class="order-status-<?php echo esc_attr($order_status); ?>">
                            <?php echo esc_html($order_status); ?>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div class="revision--product-artwork-container">
                <div class="revision--product-artwork-image">
                    <?php
                    $gallery_images = get_field('mockup_proof_gallery');

                    if ($gallery_images) {
                        foreach ($gallery_images as $image) {
                            ?>
                            <div class="product-artwork-image-item">
                                <img class="zoom" data-magnify-src="<?php echo esc_url($image['url']); ?>"
                                    src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="product-artwork-image-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-placeholder.png"
                                alt="Placeholder Image" />
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php
                if (!$approved_proof) {
                    ?>
                    <div class="revision--product-artwork-buttons">
                        <button type="button" class="btn pre-approval-btn">לחצו לאישור</button>
                        <div class="request-changes">
                            <span>או</span> <a class="request-changes-trigger" href="#">שתבקשו שינויים</a>
                        </div>
                    </div>
                    <div id="mockup-approval-modal" class="white-popup-block mfp-hide">
                        <div class="approval-review-content">
                            <h2>האישור הוא סופי</h2>
                            <p>שינויים לא יוכלו להתבצע לאחר אישורכם</p>
                        </div>
                        <button type="button" class="btn ml_add_loading approval-btn"
                            data-post-id="<?php echo get_the_ID(); ?>">מאושר להדפסה 🥳</button>
                        <div class="request-changes">
                            <span>או</span> <a class="request-changes-trigger" href="#">שתבקשו שינויים</a>
                        </div>
                    </div>
                    <div id="mockup-comment-submission-modal" class="">
                        <div class="approval-review-content">
                            <h2>תודה רבה על הפידבק.</h2>
                            <p>הסטודיו שלנו יעבור על הערותיכם והדמיות חדשות יישלחו תוך 24 שעות.</p>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="proof-approved-title revision--product-title">
                        <h3>
                            ההדמיות אושרו בהצלחה!
                        </h3>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php if (!$approved_proof) {
                ?>
                <form id="custom-comment-form" class="artwork-revisionChangeForm" data-post-id="<?php echo get_the_ID(); ?>"
                    enctype="multipart/form-data">
                    <div class="artwork-comment-fields-container">
                        <div id="up-progress">
                            <div id="up-bar"></div>
                            <div id="up-percent">0%</div>
                        </div>
                        <div class="artwork-revision-upload-new fileUpload-trick">
                            <button id="uploadbrowsebutton">
                                <i class="fa fa-paperclip" aria-hidden="true"></i>העלו קובץ...
                            </button>
                            <textarea id="custom-comment-text" name="custom-comment-text" rows="5" cols="50"
                                allowhtml="true"></textarea>
                            <input type="file" id="fileuploadfield" class="fileuploadfield" name="custom_file_upload">
                            <div class="upload-button-kit">
                                <input type="text" id="uploadtextfield" name="uploadtextfield">
                            </div>
                        </div>
                    </div>

                    <div class="alarnd--progress-bar">
                        <span>
                            <?php esc_html_e('מעלה…', 'hello-elementor'); ?>
                        </span>
                    </div>

                    <div class="submit-feedback-btn-set">
                        <button class="btn ml_add_loading mockup-submit-feedback" type="submit">שלחו את הבקשה</button>
                        <span>או</span> <a class="cancel-feedback-request" href="#">לחצו לביטול</a>
                    </div>
                </form>

                <?php
            }
            ?>
            <div class="mockup-revision-activity-container">
                <h4>היסטוריית שינויים</h4>
                <div class="revision-activities-all">
                    <?php
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
                                        $proof_approved_time = get_post_meta(get_the_ID(), 'proof_approved_time', true);

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
                    $artwork_comments = get_field('artwork_comments');

                    if ($artwork_comments) {
                        $artwork_comments = array_reverse($artwork_comments);

                        foreach ($artwork_comments as $comment) {
                            $comment_name = $comment['artwork_comment_name'];

                            // Get the comment text and convert newline characters to HTML line breaks
                            $comment_text = nl2br($comment['artwork_comments_texts']);

                            $comment_date = '';
                            if (isset($comment['artwork_comment_date']) && !empty($comment['artwork_comment_date'])) {
                                $comment_date = date_i18n(get_option('date_format') . ' \ב- ' . get_option('time_format'), strtotime($comment['artwork_comment_date']));
                            }

                            $image_html = '';

                            if (!empty($comment['artwork_new_file'])) {
                                $image_html .= '<div class="artwork-new-file">';

                                if (pathinfo($comment['artwork_new_file'], PATHINFO_EXTENSION) == 'pdf') {
                                    $image_html .= '<img src="' . get_template_directory_uri() . '/assets/images/pdf-icon.svg" alt="Placeholder">';
                                } else {
                                    $image_html .= '<img src="' . esc_url($comment['artwork_new_file']) . '" alt="Artwork Image">';
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
        var itemCount = $('.revision--product-artwork-image .product-artwork-image-item').length;

        if (itemCount > 1) {
            $('.revision--product-artwork-image').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: true,
            });
        }
    });
</script>
<?php
get_footer();