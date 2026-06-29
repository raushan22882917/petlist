<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

namespace RadiusTheme\Petslist;

use Rtcl\Helpers\Link;
use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclCFGField;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;
use Rtcl\Controllers\Hooks\TemplateHooks;
use RtclStore\Controllers\Hooks\TemplateHooks as StoreHooks;

class Listing_Functions {

	protected static $instance = null;

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'theme_support' ] );
		add_action( 'init', [ $this, 'rtcl_action_hook' ] );
		add_action( 'init', [ $this, 'rtcl_filter_hook' ] );
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function theme_support() {
		add_theme_support( 'rtcl' );
	}

	public function rtcl_action_hook() {
		if ( isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'grid', 'list' ], true ) ) {
			$view = esc_attr( $_GET['view'] );
		} else {
			$view = Functions::get_option_item( 'rtcl_general_settings', 'default_view', 'list' );
		}

		/* = Listing Archive Hooks
		=====================================================================================================*/
		//Remove Hooks
		remove_action( 'rtcl_before_main_content', [ TemplateHooks::class, 'output_main_wrapper_start' ], 8 );
		remove_action( 'rtcl_before_main_content', [ TemplateHooks::class, 'output_main_wrapper_end' ], 15 );
		remove_action( 'rtcl_sidebar', [ TemplateHooks::class, 'output_main_wrapper_end' ], 15 );
		
		remove_action( 'rtcl_listing_loop_item', [ TemplateHooks::class, 'loop_item_badges' ], 30 );
		remove_action( 'rtcl_before_main_content', [ TemplateHooks::class, 'breadcrumb' ], 6 );

		//Add Hooks 
		add_filter( 'rtcl_bootstrap_dequeue', '__return_false' );
		
		/* = Listing Single Hooks
		=====================================================================================================*/
		// remove action
		remove_action( 'rtcl_single_listing_content', [ TemplateHooks::class, 'add_single_listing_gallery' ], 30 );
		add_action( 'rt_pets_list_galley', [ __CLASS__, 'listing_details_gallery' ], 10 );
		remove_action( 'rtcl_single_listing_inner_sidebar', [
			TemplateHooks::class,
			'add_single_listing_inner_sidebar_custom_field'
		], 10 );
		remove_action( 'rtcl_single_listing_inner_sidebar', [
			TemplateHooks::class,
			'add_single_listing_inner_sidebar_action'
		], 20 );
		if ( class_exists( 'RtclStore' ) ) {
			remove_action( 'rtcl_single_store_information', [ StoreHooks::class, 'store_social_media' ], 40 );
			add_action( 'rtcl_single_store_information', [ StoreHooks::class, 'store_social_media' ], 60 );
		}
		// add action
		if ( 'list' === $view ) {
			remove_action( 'rtcl_listing_loop_item', [ TemplateHooks::class, 'listing_price' ], 80 );
			add_action( 'rtcl_listing_loop_item', [ TemplateHooks::class, 'loop_item_category_price' ], 15 );
		}
        // Seller Verification
        if ( class_exists('RtclSellerVerification' ) ) {
	        remove_action( 'rtcl_listing_seller_information', [ \RtclSellerActionHooks::class, 'listing_sidebar_verified_author' ], 5 );
        }

		remove_action( 'rtcl_single_listing_content', [ TemplateHooks::class, 'add_single_listing_title' ], 5 );
	}

	public function rtcl_filter_hook() {
		// Change Grid Column for listing
		add_filter( 'rtcl_listings_grid_columns_class', function () {
			// $columns = 'row row-cols-lg-2 row-cols-1 g-3';

			if (is_page_template('templates/listing-map.php')) {
				$columns = 'columns-' . Options::$options['listing_map_grid_cols'];
			} else {
				$columns = 'columns-' . Options::$options['listing_archive_columns'];
			}
			return $columns;
			
		} );
		add_filter( 'rtcl_listing_the_excerpt', function ( $excerpt ) {
			return wp_trim_words( $excerpt, 25 );
		} );
		// Override Related Listing Item Number
		add_filter( 'rtcl_related_slider_options', function ( $slider_options ) {
			$slider_options = [
				"loop"         => false,
				"autoplay"     => [
					"delay"                => 3000,
					"disableOnInteraction" => false,
					"pauseOnMouseEnter"    => true
				],
				"speed"        => 1000,
				"spaceBetween" => 20,
				"breakpoints"  => [
					0    => [
						"slidesPerView" => 1
					],
					500  => [
						"slidesPerView" => 2
					],
					1200 => [
						"slidesPerView" => 3
					]
				]
			];

			return $slider_options;
		} );

	}

	public static function get_listing_type( $listing ) {
		$listing_types = Functions::get_listing_types();
		$listing_types = empty( $listing_types ) ? [] : $listing_types;

		$type = $listing->get_ad_type();

		if ( $type && ! empty( $listing_types[ $type ] ) ) {
			$result = [
				'label' => $listing_types[ $type ],
				'icon'  => 'fa-tags',
			];
		} else {
			$result = false;
		}

		return $result;
	}

	public static function get_favourites_link( $post_id ) {
		$has_favourites = get_option( 'rtcl_moderation_settings' );
		if ( isset( $has_favourites['has_favourites'] ) && 'yes' !== $has_favourites['has_favourites'] ) {
			return;
		}
		if ( is_user_logged_in() ) {
			if ( $post_id == 0 ) {
				global $post;
				$post_id = $post->ID;
			}

			$favourites = (array) get_user_meta( get_current_user_id(), 'rtcl_favourites', true );

			if ( in_array( $post_id, $favourites ) ) {
				return '<a href="javascript:void(0)" class="rtcl-favourites" class="rtcl-favourites rtcl-active" data-id="' . $post_id . '"><span class="rtcl-icon rtcl-icon-heart"></span></a>';
			} else {
				return '<a href="javascript:void(0)" class="rtcl-favourites" data-id="' . $post_id . '"><i class="rtcl-icon rtcl-icon-heart-empty"></i></a>';
			}
		} else {
			return '<a href="#" class="rtcl-favourites" data-bs-toggle="modal" data-bs-target="#logoutModalCenter" title="' . esc_html__( "Favourites", 'petslist' )
			       . '"><i class="rtcl-icon rtcl-icon-heart-empty"></i></a>';
		}
	}

	public static function logout_user_favourite() {
		global $listing;
		?>
        <!-- Modal -->
        <div class="modal fade" id="logoutModalCenter" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog vertical-align-center" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoutModalTitle"><?php esc_html_e( 'Login', 'petslist' ); ?></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-pl-plus"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="share-icon">
                            <form id="rtcl-login-form" class="form-horizontal" method="post">
								<?php do_action( 'rtcl_login_form_start' ); ?>
                                <div class="form-group">
                                    <label for="rtcl-user-login" class="control-label">
										<?php esc_html_e( 'Username or E-mail', 'petslist' ); ?>
                                        <strong class="rtcl-required">*</strong>
                                    </label>
                                    <input type="text" name="username" autocomplete="username"
                                           value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                                           id="rtcl-user-login" class="form-control" required/>
                                </div>

                                <div class="form-group">
                                    <label for="rtcl-user-pass" class="control-label">
										<?php esc_html_e( 'Password', 'petslist' ); ?>
                                        <strong class="rtcl-required">*</strong>
                                    </label>
                                    <input type="password" name="password" id="rtcl-user-pass"
                                           autocomplete="current-password"
                                           class="form-control" required/>
                                </div>

								<?php do_action( 'rtcl_login_form' ); ?>

                                <div class="form-group">
                                    <div id="rtcl-login-g-recaptcha" class="mb-2"></div>
                                    <div id="rtcl-login-g-recaptcha-message"></div>
                                </div>

                                <div class="form-group d-flex align-items-center">
                                    <button type="submit" name="rtcl-login" class="btn btn-primary" value="login">
										<?php esc_html_e( 'Login', 'petslist' ); ?>
                                    </button>
                                    <div class="form-check">
                                        <input type="checkbox" name="rememberme" id="rtcl-rememberme" value="forever">
                                        <label class="form-check-label" for="rtcl-rememberme">
											<?php esc_html_e( 'Remember Me', 'petslist' ); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <p class="rtcl-forgot-password">
                                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot your password?', 'petslist' ); ?></a>
                                    </p>
                                </div>
								<?php do_action( 'rtcl_login_form_end' ); ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * @param bool $gmt
	 *
	 * @return string
	 */
	public static function petslist_the_time($theId) {
		global $listing;
		$published_date = get_post_datetime( $theId );
		$present_date = new \DateTime("now");
		$past = $published_date->format( 'Y-m-d 04:06:48' );
		$now = $present_date->format( 'Y-m-d 04:06:48' );
		$unix_published_date = get_post_timestamp($theId, 'date');
		$post_age = floor((time() - $unix_published_date) / 86400);
		if($past === $now){
			esc_html_e('Today', 'petslist');
		} else {
			if ($post_age < 1) {
				esc_html_e('Today', 'petslist');
			} else {
				$listing->the_time();
			}
		}		
	}

	public static function petslist_listing_categories() {
		global $listing;
		if ( $listing->has_category() && $listing->can_show_category() ){
			$category = $listing->get_categories();
			$category = end( $category );
			$term_id = $category->term_id;
		?>
		<a href="<?php echo esc_url( Link::get_category_page_link( $category ) ); ?>" class="category-list">
			<?php echo esc_html( $category->name ); ?>
		</a>
		<?php }
	}

	public static function petslist_listing_list_categories() {
		global $listing;
		if ( $listing->has_category() ){
			$category = $listing->get_categories();
			$category = end( $category );
			$term_id = $category->term_id;
		?>
		<a href="<?php echo esc_url( Link::get_category_page_link( $category ) ); ?>" class="category-list">
			<?php echo esc_html( $category->name ); ?>
		</a>
		<?php }
	}

	public static function petslist_listing_excerpt( $excerpt_length ) { ?>
            <p><?php echo Helper::petslist_excerpt( $excerpt_length ); ?></p>
	<?php }

	public static function petslist_listing_single_excerpt() {
		$excerpt_length = Options::$options['listing_excerpt'];
			?>
            <p><?php echo Helper::petslist_excerpt( 23 ); ?></p>
		<?php
	}

	public static function get_advanced_search_field_html( $field_id ) {
		$field      = new RtclCFGField( $field_id );
		$field_html = null;

		if ( $field_id && $field ) {
			$id = "rtcl_{$field->getType()}_{$field->getFieldId()}";

			switch ( $field->getType() ) {
				case 'text':
					$field_html = sprintf(
						'<input type="text" class="rtcl-text form-control rtcl-cf-field" id="%s" name="filters[_field_%d]" placeholder="%s" value="" />',
						$id,
						absint( $field->getFieldId() ),
						esc_attr( $field->getPlaceholder() )
					);
					break;
				case 'textarea':
					$field_html = sprintf(
						'<textarea class="rtcl-textarea form-control rtcl-cf-field" id="%s" name="filters[_field_%d]" rows="%d" placeholder="%s"></textarea>',
						$id,
						absint( $field->getFieldId() ),
						absint( $field->getRows() ),
						esc_attr( $field->getPlaceholder() )
					);
					break;
				case 'select':
					$options      = $field->getOptions();
					$choices      = ! empty( $options['choices'] ) && is_array( $options['choices'] ) ? $options['choices'] : [];
					$options_html = '<option value="">' . esc_html( $field->getLabel() ) . '</option>';

					if ( ! empty( $choices ) ) {
						foreach ( $choices as $key => $choice ) {
							$_attr = '';
							if ( isset( $_GET['filters'][ '_field_' . $field->getFieldId() ] ) && $_GET['filters'][ '_field_' . $field->getFieldId() ] == $choice ) {
								$_attr .= ' selected';
							}
							$options_html .= sprintf( '<option value="%s"%s>%s</option>', $key, $_attr, $choice );
						}
					}

					$field_html
						= sprintf(
						'<div class="search-item search-select"><select name="filters[_field_%d]" id="%s" data-placeholder="%s" class="select2">%s</select></div>',
						absint( $field->getFieldId() ),
						$id,
						$field->getLabel(),
						$options_html
					);
					break;
				case 'checkbox':
					$options       = $field->getOptions();
					$value         = isset( $_GET['filters'][ '_field_' . $field->getFieldId() ] ) ? $_GET['filters'][ '_field_' . $field->getFieldId() ] : [];
					$choices       = ! empty( $options['choices'] ) && is_array( $options['choices'] ) ? $options['choices'] : [];
					$check_options = null;
					if ( ! empty( $choices ) ) {
						foreach ( $choices as $key => $choice ) {
							$_attr = '';
							if ( in_array( $key, $value ) ) {
								$_attr .= ' checked="checked"';
							}
							$check_options .= sprintf(
								'<div class="form-check"><input class="form-check-input" id="%s" type="checkbox" name="filters[_field_%d][]" value="%s"%s><label class="form-check-label" for="%s">%s</label></div>',
								$id . $key,
								absint( $field->getFieldId() ),
								$key,
								$_attr,
								$id . $key,
								$choice
							);
						}
					}
					$field_html = sprintf( '<div class="search-item checkbox-wrapper">%s</div>', $check_options );
					break;
				case 'radio':
					$options       = $field->getOptions();
					$choices       = ! empty( $options['choices'] ) && is_array( $options['choices'] ) ? $options['choices'] : [];
					$check_options = null;
					if ( ! empty( $choices ) ) {
						foreach ( $choices as $key => $choice ) {
							$check_options .= sprintf(
								'<div class="form-check"><input class="form-check-input" id="%s" type="radio" name="filters[_field_%d]" value="%s"><label class="form-check-label" for="%s">%s</label></div>',
								$id . $key,
								absint( $field->getFieldId() ),
								$key,
								$id . $key,
								$choice
							);
						}
					}
					$field_html = sprintf( '<div class="search-item search-type"><div class="search-check-box">%s</div></div>', $check_options );
					break;
				case 'number':
					$hidden_field = sprintf(
						'<input type="hidden" class="min-volumn" name="filters[_field_%d][min]" value="%s">',
						absint( $field->getFieldId() ),
						isset( $_GET['filters'][ '_field_' . $field->getFieldId() ]['min'] ) ? absint( $_GET['filters'][ '_field_' . $field->getFieldId() ]['min'] ) : ''
					);
					$hidden_field .= sprintf(
						'<input type="hidden" class="max-volumn" name="filters[_field_%d][max]" value="%s">',
						absint( $field->getFieldId() ),
						isset( $_GET['filters'][ '_field_' . $field->getFieldId() ]['max'] ) ? absint( $_GET['filters'][ '_field_' . $field->getFieldId() ]['max'] ) : ''
					);

					$field_html = sprintf(
						'<div class="search-item">
							<div class="price-range">
								<label>%s</label>
								<input type="number" class="ion-rangeslider" id="%s" data-step="%s" %s %s data-min="%d" data-max="%s" />
								%s
							</div>
						</div>',
						esc_attr( $field->getLabel() ),
						$id,
						$field->getStepSize() ? esc_attr( $field->getStepSize() ) : 'any',
						isset( $_GET['filters'][ '_field_' . $field->getFieldId() ]['min'] ) ? sprintf(
							'data-from="%s"',
							absint( $_GET['filters'][ '_field_' . $field->getFieldId() ]['min'] )
						) : '',
						isset( $_GET['filters'][ '_field_' . $field->getFieldId() ]['max'] ) && ! empty( $_GET['filters'][ '_field_' . $field->getFieldId() ]['max'] ) ? sprintf(
							'data-to="%s"',
							absint( $_GET['filters'][ '_field_' . $field->getFieldId() ]['max'] )
						) : '',
						$field->getMin() !== '' ? absint( $field->getMin() ) : '',
						! empty( $field->getMax() ) ? absint( $field->getMax() ) : absint( $field->getMin() ) + 100,
						$hidden_field
					);
					break;
				case 'url':
					$field_html = sprintf(
						'<input type="url" class="rtcl-url form-control rtcl-cf-field" id="%s" name="filters[_field_%d]" placeholder="%s" value="" />',
						$id,
						absint( $field->getFieldId() ),
						esc_attr( $field->getPlaceholder() )
					);
					break;
				case 'date':
					echo "Bangladesh";
					break;
			}
		}

		return $field_html;
	}

	public static function get_share_link() {
		global $listing;
		?>
        <!-- Modal -->
        <div class="modal fade social-share" id="exampleModalCenter" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="socialShareModalTitle"><?php esc_html_e( 'Share This Link Via', 'petslist' ); ?></h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="icon-pl-plus"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="share-icon">
							<?php $listing->the_social_share(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public static function get_repost_abuse() {
		?>

		 <!-- Modal -->
		 <div class="modal fade report-abuse" id="rtcl-report-abuse-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<form id="rtcl-report-abuse-form" class="form-vertical">
						<div class="modal-header">
							<h5 class="modal-title" id="rtcl-report-abuse-modal-label"><?php esc_html_e('Report Abuse', 'petslist'); ?></h5>
							<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
								<i class="icon-pl-plus"></i></button>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="rtcl-report-abuse-message"><?php esc_html_e('Your Complaint', 'petslist'); ?>
									<span class="rtcl-star">*</span></label>
								<textarea class="form-control" name="message" id="rtcl-report-abuse-message" rows="3" placeholder="<?php esc_attr_e('Message... ', 'petslist'); ?>" required></textarea>
							</div>
							<div id="rtcl-report-abuse-g-recaptcha"></div>
							<div id="rtcl-report-abuse-message-display"></div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary"><?php esc_html_e('Submit', 'petslist'); ?></button>
						</div>
					</form>
				</div>
            </div>
        </div>
		<?php
	}

	public static function petslist_single_listing_meta() {
		global $listing; ?>
		<!-- Meta data -->
		<div class="rtcl-listing-meta">
			<?php $listing->the_meta(); ?>
		</div>
		<?php
	}

	public static function listing_details_gallery() {
		global $listing;

		$detailOption = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', [] ); 

		$video_urls = [];
		if ( ! Functions::is_video_urls_disabled() ) {
			$video_urls = get_post_meta( $listing->get_id(), '_rtcl_video_urls', true );
			$video_urls = ! empty( $video_urls ) && is_array( $video_urls ) ? $video_urls : [];
		}
		// Image Gallery
		$images              = $listing->get_images();
		$total_gallery_image = count( $images );

		$number = $total_gallery_image - 5;

		if ($total_gallery_image < 2) {
			$item_count = 'items-one';
		} elseif ($total_gallery_image < 3 ) {
			$item_count = 'items-two';
		} elseif ($total_gallery_image < 4) {
			$item_count = 'items-three';
		} elseif ($total_gallery_image < 5) {
			$item_count = 'items-four';
		} else {
			$item_count = 'items-five';
		}
		if ( $total_gallery_image ) {
		?>

            <div class="photo-swip-gallery-wrap <?php echo esc_attr($item_count); ?>">
				<?php
					if ( !in_array('video_url', $detailOption) ){
						if ( ! empty( $video_urls ) ) { ?>
						<div class="listing-gallery-item">
							<div class="video-info rtcl-slider-video-item ratio-16x9">
								<iframe class="rtcl-lightbox-iframe"
										src="<?php echo Functions::get_sanitized_embed_url( $video_urls[0] ) ?>"
										style="height: 404px"
										frameborder="0" webkitAllowFullScreen
										mozallowfullscreen allowFullScreen></iframe>
							</div>
						</div>
					<?php }
					}
					$counter = 0;
					foreach ( $images as $image ) {
						++$counter;

						if ($total_gallery_image < 4) {
							$img_size = 'full';
						} else {
							$img_size = 'rtcl-thumbnail';
						}					
						?>
						<div class="listing-gallery-item photoswip-item image-size-<?php echo esc_attr($img_size.' item-'.$counter); ?>">
							<?php 
								$img_url = wp_get_attachment_image_url( $image->ID, 'full' ); 
								$getimagesize = getimagesize($img_url);
								$width = $getimagesize[0];
								$height = $getimagesize[1];
							?>
							<a class="listing-popup-btn" href="<?php echo esc_url( $img_url ); ?>" data-width="<?php echo esc_attr($width); ?>" data-height="<?php echo esc_attr($height); ?>">
								<?php 
									echo wp_get_attachment_image( $image->ID, $img_size );
									if( ! empty( $number ) && $counter === 5 ) {
										echo '<span>+'.esc_html($number).'</span>';
									}
								?>
							</a>
						</div>
					<?php
					}
				?>
            </div>
		<?php }
	}

	// Listing Layout
	public static function listing_layout_class() {  
		if ( class_exists('Rtcl') && class_exists( 'RtclPro' ) ) {
			$bodyLayout = Options::$options['listing_archive_box_layout'] ? Options::$options['listing_archive_box_layout'] : 'container';
		}
		$ccols = $bodyLayout != 'container' ? '9' : '8'; 
		$listing_layout_class = is_active_sidebar('rtcl-archive-sidebar') && Options::$options['listing_layout'] != 'full-width' ? 'col-lg-'.$ccols : 'col-12';
		if ( Options::$options['listing_layout'] == 'right-sidebar' ) {
			$listing_layout_class = $listing_layout_class.' order-lg-1';
		} elseif ( Options::$options['listing_layout'] == 'left-sidebar' ) {
			$listing_layout_class = $listing_layout_class.' order-lg-2';
		} else {
			$listing_layout_class = $listing_layout_class;
		}
		echo apply_filters( 'listing_layout_class', $listing_layout_class );
	}

	public static function listing_sidebar() {
		if ( Options::$options['listing_layout'] == 'right-sidebar' || Options::$options['listing_layout'] == 'left-sidebar' && Options::$options['listing_layout'] != 'full-width' && is_active_sidebar('rtcl-archive-sidebar') ) {
			get_sidebar( 'listing' );
		}
	}

	public static function listing_sidebar_class() {
		if ( class_exists('Rtcl') && class_exists( 'RtclPro' ) ) {
			$bodyLayout = Options::$options['listing_archive_box_layout'] ? Options::$options['listing_archive_box_layout'] : 'container';
		}
		$scols = $bodyLayout != 'container' ? '3' : '4';
		if ( Options::$options['listing_layout'] == 'right-sidebar' ) {
			echo apply_filters( 'rt_sidebar_class', 'col-lg-'.$scols.' order-lg-2 listing-sidebar-right' );
		} else {
			echo apply_filters( 'rt_sidebar_class', 'col-lg-'.$scols.' order-lg-1 listing-sidebar-left' );
		}
	}

	/**
	 * Getting Custome taxanomy for portfolio - category- single service
	 */
	public static function listing_categories_slug() {
		if (class_exists('RtclPro')) {
			$terms = get_terms( "rtcl_category" );
			if(!empty($terms)){
			$category_links = array();
			foreach ($terms as $key => $value) {
				$category_links[$value->term_id] = $value->name;
			}
			return $category_links;
			}
		}
	}

	public static function listing_cat_icon( $term_id, $icon_type = NULL ) {
		$cat_img  = $cat_icon = $icon = null;
		$image_id = get_term_meta( $term_id, '_rtcl_image', true );
		if ( $image_id ) {
			$image_attributes = wp_get_attachment_image_src( (int) $image_id, 'medium' );
			$image            = $image_attributes[0];
			if ( '' !== $image ) {
				$cat_img = sprintf( '<img src="%s" class="rtcl-cat-img" alt="%s"/>', $image, esc_attr__( 'Category Image', 'petslist' ) );
			}
		}
		$icon_id = get_term_meta( $term_id, '_rtcl_icon', true );
		if ( $icon_id ) {
			$cat_icon = sprintf( '<span class="rtcl-cat-icon rtcl-icon rtcl-icon-%s"></span>', $icon_id );
		}

		$icon = $icon_type == 'icon' ? $cat_icon : $cat_img;

		return $icon;
	}

	public static function the_phone( $phone = '', $whatsapp_number = '', $telegram = '' ) {

		$mobileClass = wp_is_mobile() ? " rtcl-mobile" : null;
		$phone_options = [];
		if ( $phone ) {
			$phone_options = [
				'safe_phone'   => mb_substr( $phone, 0, mb_strlen( $phone ) - 3 ) . apply_filters( 'rtcl_phone_number_placeholder', 'XXX' ),
				'phone_hidden' => mb_substr( $phone, - 3 )
			];
		}
		if ( $whatsapp_number && ! Functions::is_field_disabled( 'whatsapp_number' ) ) {
			$phone_options['safe_whatsapp_number'] = mb_substr( $whatsapp_number, 0, mb_strlen( $whatsapp_number ) - 3 ) . apply_filters( 'rtcl_phone_number_placeholder', 'XXX' );
			$phone_options['whatsapp_hidden']      = mb_substr( $whatsapp_number, - 3 );
		}
		if ( $telegram ) {
			$phone_options['safe_telegram'] = mb_substr( $telegram, 0, mb_strlen( $telegram ) - 3 ) . apply_filters( 'rtcl_phone_number_placeholder', 'XXX' );
			$phone_options['telegram_hidden'] = mb_substr( $telegram, - 3 );
		}

		$phone_options = apply_filters( 'rtcl_phone_number_options', $phone_options, [
			'phone'             => $phone,
			'whatsapp_number'   => $whatsapp_number,
			'telegram'          => $telegram
		] );

		if ( $phone ) { ?>
			<div class='item-number rtcl-contact-reveal-wrapper reveal-phone<?php echo esc_attr( $mobileClass ); ?>' data-options="<?php echo htmlspecialchars( wp_json_encode( $phone_options ) ); ?>">
				<div class="number-icon">
					<i class="icon-pl-iocn-fill"></i>
					<div class='numbers'>
						<?php echo esc_html( $phone_options['safe_phone'] ); ?>
					</div>
				</div>
				<small class='text-muted'><?php esc_html_e( 'Click to reveal phone number', 'petslist' ); ?></small>
			</div>
		<?php } elseif ( $whatsapp_number ) { ?>
			<div class='item-number rtcl-contact-reveal-wrapper reveal-phone<?php echo esc_attr( $mobileClass ); ?>' data-options="<?php echo htmlspecialchars( wp_json_encode( $phone_options ) ); ?>">
				<div class="number-icon">
					<i class="fab fa-whatsapp"></i>
					<div class='numbers'><?php echo esc_html( $phone_options['safe_whatsapp_number'] ); ?></div>
				</div>
				<small class='text-muted'><?php esc_html_e( 'Click to reveal whatsapp number', 'petslist' ); ?></small>
			</div>
		<?php } elseif ( $telegram ) { ?>
			<div class='item-number rtcl-contact-reveal-wrapper reveal-phone<?php echo esc_attr( $mobileClass ); ?>' data-options="<?php echo htmlspecialchars( wp_json_encode( $phone_options ) ); ?>">
				<div class="number-icon">
					<i class="fa-brands fa-telegram"></i>
					<div class='numbers'> <?php echo esc_html( $phone_options['safe_telegram'] ); ?></div>
				</div>
				<small class='text-muted'><?php esc_html_e( 'Click to reveal telegram number', 'petslist' ); ?></small>
			</div>
		<?php }
	}

	public function rtcl_get_icon_list_modify( $icons_lists ) {
		$new_icons = [
			" icon-pl-plus",
			" icon-pl-plus-circle",
			" icon-pl-search",
			" icon-pl-tick-mark-fill-circle",
			" icon-share",
			" icon-star",
			" icon-star-fill",
			" icon-twitter",
			" icon-user",
			" icon-user-fill",
			" icon-facebook",
			" icon-instagram",
			" icon-pinterest",
			" icon-pl-account",
			" icon-pl-account-fill",
			" icon-pl-angle-down",
			" icon-pl-angle-down-fat",
			" icon-pl-angle-right",
			" icon-pl-angle-up",
			" icon-pl-angle-up-fat",
			" icon-pl-chat",
			" icon-pl-chat-fill",
			" icon-pl-clock",
			" icon-pl-edit",
			" icon-pl-flash",
			" icon-pl-flash-fill",
			" icon-pl-heart",
			" icon-pl-grid-view",
			" icon-pl-heart-fill",
			" icon-pl-img",
			" icon-pl-img-fill",
			" icon-pl-iocn-fill",
			" icon-pl-list",
			" icon-pl-list-view",
			" icon-pl-location-fill",
			" icon-pl-location",
			" icon-pl-lock-fill",
			" icon-pl-message-box",
			" icon-pl-messege-box-fill",
			" icon-pl-phone",
			" icon-pl-angle-down-fill",
			" icon-pl-calendar",
			" icon-camera",
			" icon-warning",
			" icon-compare",
			" icon-pl-tag",
			" icon-pl-right-arrow",
			" icon-pl-eye",
			" icon-linkedin",
			" icon-youtube-1",
			" icon-pl-earth",
			" icon-share-2",
			" icon-youtube",
		];

		return array_merge( $icons_lists, $new_icons );
	}
}