<?php
/**
 * Ajax request
 */
if ( ! class_exists( 'AllAroundAjax' ) ) {
	class AllAroundAjax {

		/**
		 * Start up
		 */
		public function __construct() {
			 add_action( 'wp_ajax_alarnd_artwork_upload', array( $this, 'alarnd_artwork_upload' ) );
			add_action( 'wp_ajax_nopriv_alarnd_artwork_upload', array( $this, 'alarnd_artwork_upload' ) );

			add_action( 'wp_ajax_alarnd_cart_configure', array( $this, 'alarnd_cart_configure' ) );
			add_action( 'wp_ajax_nopriv_alarnd_cart_configure', array( $this, 'alarnd_cart_configure' ) );

			add_action( 'wp_ajax_alarnd_blog_fetch', array( $this, 'alarnd_blog_fetch' ) );
			add_action( 'wp_ajax_nopriv_alarnd_blog_fetch', array( $this, 'alarnd_blog_fetch' ) );

			add_action( 'wp_ajax_alarnd_use_loadmore', array( $this, 'alarnd_use_loadmore' ) );
			add_action( 'wp_ajax_nopriv_alarnd_use_loadmore', array( $this, 'alarnd_use_loadmore' ) );

            add_action( 'wp_ajax_alarnd_review_loadmore', array( $this, 'alarnd_review_loadmore' ) );
			add_action( 'wp_ajax_nopriv_alarnd_review_loadmore', array( $this, 'alarnd_review_loadmore' ) );

			add_action( 'wp_ajax_alarnd_use_search', array( $this, 'alarnd_use_search' ) );
			add_action( 'wp_ajax_nopriv_alarnd_use_search', array( $this, 'alarnd_use_search' ) );

			add_action( 'wp_ajax_alarnd_get_product_val', array( $this, 'alarnd_get_product_val' ) );
			add_action( 'wp_ajax_nopriv_alarnd_get_product_val', array( $this, 'alarnd_get_product_val' ) );
		}

        public function alarnd_review_loadmore() {
			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$itemcount = isset( $_POST['itemcount'] ) && ! empty( $_POST['itemcount'] ) ? intval( $_POST['itemcount'] ) : '';
			$ppp    = isset( $_POST['ppp'] ) && ! empty( $_POST['ppp'] ) ? intval( $_POST['ppp'] ) : '';

			$review_args = array(
                'posts_per_page' => 5000,
                'post_type'      => 'review',
                'post_status'    => 'publish',
                'order'          => 'DESC'
            );
            if( ! empty( $ppp ) ) {
                $review_args['posts_per_page'] = $ppp;
                
            }
            if ( ! empty( $itemcount ) ) {
                $review_args['offset'] = $itemcount;
            }

            $review_qry  = new WP_Query( $review_args );
            
            if ( $review_qry->have_posts() ) :
            while ( $review_qry->have_posts() ) : $review_qry->the_post(); 
            
            $rating = get_post_meta( get_the_ID(), 'rating', true );
            $name = get_post_meta( get_the_ID(), 'name', true );
            $custom_date = get_post_meta( get_the_ID(), 'custom_date', true );
            $email = get_post_meta( get_the_ID(), 'email', true );
            $avatar = get_post_meta( get_the_ID(), 'avatar', true );
            $thumb = get_post_meta( get_the_ID(), 'review_thumb', true );

            $user_email = ! empty( $email ) ? $email : null;
            $gravatar = ! empty( $avatar ) ? '<img src="'.wp_get_attachment_url( (int) $avatar ).'"/>' : get_avatar( $user_email, 100, 'mystery' );
            $review_thumb = ! empty( $thumb ) ? '<a href="'.wp_get_attachment_url( (int) $thumb ).'"><img src="'.wp_get_attachment_url( (int) $thumb ).'"/></a>' : null;
            $user_name = ! empty( $name ) ? $name : esc_html__('Anonymous', 'hello-elementor');

            $the_date = ! empty( $custom_date ) ? date_i18n('F j ,Y', strtotime($custom_date)) : get_the_date( 'F j ,Y' );

            ?>
            <div class="alarnd--single-review">
                <div class="review-item">
                    <div class="review-avatar">
                        <?php echo $gravatar; ?>
                    </div>
                    <div class="review-body">
                        <?php echo alarnd_single_review_avg( $rating ); ?>

                        <h4 class="review-title"><?php the_title(); ?></h4>

                        <div class="review-details">
                            <div class="review-avatar-mobile">
                                <?php echo $gravatar; ?>
                            </div>
                            <span class="reviewer-name">
                                <strong><?php echo $user_name; ?></strong>
                            </span>
                            <time class="review-date"><?php echo $the_date; ?></time>
                        </div>

                        <?php the_content(); ?>
                    </div>
						<div class="review-thumb">
                            <?php echo $review_thumb; ?>
						</div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata();
            endif;
			wp_die();
		}

		public function alarnd_get_product_val() {
			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$product_id = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : '';
			$total_qty = isset( $_POST['total_qty'] ) && ! empty( $_POST['total_qty'] ) ? absint( $_POST['total_qty'] ) : 0;

            $get_product = wc_get_product( $product_id );

            $first_line_keyword = get_field( 'first_line_keyword', $get_product->get_id() );
            $second_line_keyword = get_field( 'second_line_keyword', $get_product->get_id() );
            $first_line_keyword = ! empty( $first_line_keyword ) ? $first_line_keyword : esc_html__( 'Shirt', 'hello-elementor');
            $second_line_keyword = ! empty( $second_line_keyword ) ? $second_line_keyword : esc_html__( 'Total Shirts', 'hello-elementor');

            $keyword = get_field( 'keyword', $get_product->get_id() );
            $keyword = ! empty( $keyword ) ? $keyword : 'shirt';

            $discount_steps = get_field( 'discount_steps', $product_id );

            $regular_price = $get_product->get_regular_price();
            $final_price = Alarnd_Utility::instance()->get_final_amount( $product_id, $total_qty, $regular_price );
            $total_price = $final_price * $total_qty;
            ?>
            <h2 class="alarnd--total-price"><?php echo wc_price($total_price, array('decimals' => 0)); ?></h2>
            <div class="alarnd--price-by-shirt">
                <p class="alarnd--group-price"><?php echo wc_price($final_price, array('decimals' => 0)); ?> / <?php echo esc_html( $first_line_keyword ); ?></p>
                <p><?php echo esc_html( $second_line_keyword ); ?>: <span class="alarnd__total_qty"><?php echo $total_qty; ?></span></p>
            </div>
            <?php
			wp_die();
		}
		
        public function alarnd_use_search() {
			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$search = isset( $_POST['search'] ) && ! empty( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

			$taxonomy      = 'uses';
			$get_uses_args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 12,
				'fields'     => 'all',
				'name__like' => $search,
			);
			$get_uses      = get_terms( $get_uses_args );
			if ( ! empty( $get_uses ) && ! is_wp_error( $get_uses ) ) :
				foreach ( $get_uses as $children ) :
					$chilterm = get_term( $children, $taxonomy );
					if ( $chilterm->count === 0 ) {
						continue;
					}
					$title             = $chilterm->name;
					$term_link         = get_term_link( $chilterm );
					$get_product_count = alarnd_get_product_by_use( $children );
					if ( $chilterm->count === 1 && ! empty( $get_product_count ) ) {
						$term_link = get_permalink( $get_product_count );
						// $title = get_the_title( $get_product_count );
					}
					$thumb_id  = get_term_meta( $children->term_id, 'thumbnail', true );
					$thumb_url = ! empty( $term_link ) ? wp_get_attachment_image_url( $thumb_id, 'full' ) : get_template_directory_uri() . '/assets/images/icon-placeholder.png';
					?>
				<!-- single item -->
				<a href="<?php echo esc_url( $term_link ); ?>" class="allaround--service-single-item">
					<div class="allaround--service-thumbanil">
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
					</div>

					<div class="allaround--service-content">
						<h3><?php echo esc_html( $title ); ?></h3>
					</div>
				</a>
				<!-- single item -->
					<?php
			endforeach;
			else :
				?>
			<div class="allaround--uses-not-found">
				<p><?php esc_html_e( "Sorry, we didn't find any products.", 'hello-elementor' ); ?></p>
				<button class="reset_use_search"><?php esc_html_e( 'Show all Products', 'hello-elementor' ); ?></button>
			</div>
				<?php
			endif;
			wp_die();
		}

		public function alarnd_use_loadmore() {
			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$itemcount = isset( $_POST['itemcount'] ) && ! empty( $_POST['itemcount'] ) ? intval( $_POST['itemcount'] ) : '';
			$search    = isset( $_POST['search'] ) && ! empty( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

			$taxonomy      = 'uses';
			$get_uses_args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 12,
			);
			if ( ! empty( $itemcount ) && empty( $search ) ) {
				$get_uses_args['offset'] = $itemcount;
			}
			if ( ! empty( $search ) ) {
				$get_uses_args['fields']     = 'all';
				$get_uses_args['name__like'] = $search;
			}
			$get_uses = get_terms( $get_uses_args );
			if ( ! empty( $get_uses ) && ! is_wp_error( $get_uses ) ) :
				foreach ( $get_uses as $children ) :
					$chilterm = get_term( $children, $taxonomy );
					if ( $chilterm->count === 0 ) {
						continue;
					}
					$title             = $chilterm->name;
					$term_link         = get_term_link( $chilterm );
					$get_product_count = alarnd_get_product_by_use( $children );
					if ( $chilterm->count === 1 && ! empty( $get_product_count ) ) {
						$term_link = get_permalink( $get_product_count );
						// $title = get_the_title( $get_product_count );
					}
					$thumb_id  = get_term_meta( $children->term_id, 'thumbnail', true );
					$thumb_url = ! empty( $term_link ) ? wp_get_attachment_image_url( $thumb_id, 'full' ) : get_template_directory_uri() . '/assets/images/icon-placeholder.png';
					?>
				<!-- single item -->
				<a href="<?php echo esc_url( $term_link ); ?>" class="allaround--service-single-item">
					<div class="allaround--service-thumbanil">
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
					</div>

					<div class="allaround--service-content">
						<h3><?php echo esc_html( $title ); ?></h3>
					</div>
				</a>
				<!-- single item -->
					<?php
			endforeach;
			endif;
			wp_die();
		}

		public function alarnd_blog_fetch() {
			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$term_id   = isset( $_POST['term_id'] ) && ! empty( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : '';
			$paged_url = isset( $_POST['paged_url'] ) && ! empty( $_POST['paged_url'] ) ? sanitize_text_field( $_POST['paged_url'] ) : '';
			$base_url  = isset( $_POST['base_url'] ) && ! empty( $_POST['base_url'] ) ? sanitize_text_field( $_POST['base_url'] ) : '';

			$big            = 999999999; // need an unlikely integer
			$posts_per_page = get_option( 'posts_per_page' );
			$posts_per_page = ! empty( $posts_per_page ) ? (int) $posts_per_page : 10;
			$post_args      = array(
				'posts_per_page' => $posts_per_page,
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'order'          => 'DESC',
			);
			$get_paged      = '';
			if ( ! empty( $paged_url ) ) {
				$get_paged          = alarnd_get_paged( $paged_url );
				$post_args['paged'] = $get_paged;
			}
			if ( ! empty( $term_id ) && 'all' !== $term_id ) {
				$post_args['tax_query'] = array(
					array(
						'taxonomy' => 'category',
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				);
			}
			$post_qry = new WP_Query( $post_args );
			if ( $post_qry->have_posts() ) :
				while ( $post_qry->have_posts() ) :
					$post_qry->the_post();
					get_template_part( 'blog', 'item' );
			endwhile;
				wp_reset_postdata();
			else :
				?>
				<p><?php esc_html_e( 'Sorry, no review found.', 'hello-elementor' ); ?></p>
			<?php endif; ?>
			<div class="allaround--pagination-wrap" data-base-url="<?php echo esc_url( $base_url ); ?>">
				<?php
				echo paginate_links(
					array(
						'base'      => str_replace( $big, '%#%', esc_url( $base_url ) ),
						'format'    => '?paged=%#%',
						'current'   => ! empty( $get_paged ) ? $get_paged : max( 1, get_query_var( 'paged' ) ),
						'total'     => $post_qry->max_num_pages,
						'prev_text' => esc_html__( 'Previous', 'hello-elementor' ),
						'next_text' => esc_html__( 'Next', 'hello-elementor' ),
						'type'      => 'list',
					)
				);
				?>
			</div>
			<?php
			wp_die();
		}

		 /**
		  * User approve and delete ajax call
		  */
		function alarnd_cart_configure() {
			// First check the nonce, if it fails the function will break

			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$product_id         = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '';
			$art_position       = isset( $_POST['art_position'] ) ? $_POST['art_position'] : '';
			$variation_id       = isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : '';
			$group_uniqid       = isset( $_POST['alarnd_group_uniqid'] ) && ! empty( $_POST['alarnd_group_uniqid'] ) ? sanitize_text_field( $_POST['alarnd_group_uniqid'] ) : '';
			$alarnd_instruction = isset( $_POST['alarnd_instruction'] ) && ! empty( $_POST['alarnd_instruction'] ) ? sanitize_text_field( $_POST['alarnd_instruction'] ) : '';
			$alarnd_artwork_id  = isset( $_POST['alarnd_artwork_id'] ) && ! empty( $_POST['alarnd_artwork_id'] ) ? intval( $_POST['alarnd_artwork_id'] ) : '';
			$alarnd_artwork_id2  = isset( $_POST['alarnd_artwork_id2'] ) && ! empty( $_POST['alarnd_artwork_id2'] ) ? intval( $_POST['alarnd_artwork_id2'] ) : '';

            $art_positions = get_field( 'art_positions', $product_id );

            $art_item = (! empty( $art_positions ) && isset( $art_positions[$art_position] )) ? $art_positions[$art_position] : '';
            $art_item_price = ( ! empty( $art_item ) && isset( $art_item['price'] ) ) ? $art_item['price'] : '';

			// $fullsize_path = get_attached_file( $alarnd_artwork_id ); // Full path
			// $filename_only = basename( get_attached_file( $alarnd_artwork_id ) ); // Just the file name

			foreach ( WC()->cart->get_cart() as $cart_item_id => $cart_item ) {
				if ( $cart_item['product_id'] === $product_id ) {
                    if( 
						! empty( $alarnd_artwork_id ) &&
						isset( $cart_item['alarnd_group_id'] ) &&
						! empty( $cart_item['alarnd_group_id'] ) &&
						$cart_item['alarnd_group_id'] == $group_uniqid
					) {
                        $cart_item['allaround_artwork_id'] = $alarnd_artwork_id;
                    }
                    if( 
						! empty( $alarnd_artwork_id2 ) &&
						isset( $cart_item['alarnd_group_id'] ) &&
						! empty( $cart_item['alarnd_group_id'] ) &&
						$cart_item['alarnd_group_id'] == $group_uniqid
					) {
                        $cart_item['allaround_artwork_id2'] = $alarnd_artwork_id2;
                    }
                    if ( isset( $art_position ) ) {						
                        $cart_item['allaround_art_pos_key'] = $art_position;
						$item_id = isset( $cart_item['alarnd_group_id'] ) ? $cart_item['alarnd_group_id'] : '';
						
                        if( 
							! empty( $art_item_price ) &&
							isset( $cart_item['alarnd_group_id'] ) &&
							! empty( $cart_item['alarnd_group_id'] ) &&
							$cart_item['alarnd_group_id'] == $group_uniqid
						) {
							// error_log( "group_uniqid => $group_uniqid; item_id => $item_id; price => $art_item_price" );
							$cart_item['art_item_price'] = $art_item_price;
                        }
                    }
                    if ( ! empty( $alarnd_instruction ) ) {
                        $cart_item['allaround_instruction_note'] = $alarnd_instruction;
                    }
					WC()->cart->cart_contents[ $cart_item_id ] = $cart_item;
				}
			}
			WC()->cart->set_session();

			wp_send_json_success();

			// Don't forget to stop execution afterward.
			wp_die();
		}
		/**
		 * Artwork file upload
		 */
		public function alarnd_artwork_upload() {

			check_ajax_referer( 'allaround_validation_nonce', 'nonce' );

			$fileErrors = array(
				0 => esc_html__('There is no error, the file uploaded with success', 'hello-elementor'),
				1 => esc_html__('The uploaded file exceeds the upload_max_files in server settings', 'hello-elementor'),
				2 => esc_html__('The uploaded file exceeds the MAX_FILE_SIZE from html form', 'hello-elementor'),
				3 => esc_html__('The uploaded file uploaded only partially', 'hello-elementor'),
				4 => esc_html__('No file was uploaded', 'hello-elementor'),
				6 => esc_html__('Missing a temporary folder', 'hello-elementor'),
				7 => esc_html__('Failed to write file to disk', 'hello-elementor'),
				8 => esc_html__('A PHP extension stoped file to upload', 'hello-elementor'),
			);

			$posted_data = isset( $_POST ) ? $_POST : array();
			$file_data   = isset( $_FILES ) ? $_FILES : array();
			$data        = array_merge( $posted_data, $file_data );
			$name        = $file_data['alarnd_artwork_file']['name'];
			$filetype    = wp_check_filetype( $name );

			$allowed_file_types = array(
				'jpg',
				'JPG',
				'jpeg',
				'JPEG',
				'jpe',
				'gif',
				'png',
				'PNG',
				'bmp',
				'webp',
				'ico',
				'pdf',
				'svg',
				'SVG',
				'eps',
				'EPS',
				'psd',
				'PSD',
			);

			$response = array();

			if ( ! in_array( $filetype['ext'], $allowed_file_types ) ) {
				$response['response'] = 'error';
				$response['error']    = esc_html__('Wrong file format! please upload image type file only.', 'hello-elementor');
				echo json_encode( $response );
				die();
			}

			$attachment_id = media_handle_upload( 'alarnd_artwork_file', 0 );

			if ( is_wp_error( $attachment_id ) ) {
				$response['response'] = 'error';
				$response['error']    = $fileErrors[ $data['alarnd_artwork_file']['error'] ];
			} else {
				$url                       = get_attached_file( $attachment_id );
				$pathinfo                  = pathinfo( $url );
				$response['artwork_name']  = $name;
				$response['attachment_id'] = $attachment_id;
			}
			wp_send_json_success( $response );
			die();
		}


	}
}
$allaroundajax = new AllAroundAjax();
