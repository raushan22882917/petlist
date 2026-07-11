<?php
/**
 * Shared helpers for custom page templates (no Elementor).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central registry of demo image paths (uploads-relative or theme-relative).
 * All custom pages and branding use these paths — never attachment IDs.
 */
function petslist_image_paths() {
	// Images are bundled inside the theme (assets/img/demo) so they deploy with
	// the theme and never depend on wp-content/uploads existing on the server.
	return apply_filters(
		'petslist_image_paths',
		array(
			'logo_dark'        => 'uploads:2023/08/STUDLOGO.png',
			'logo_light'       => 'theme:demo/petslist_logo.svg',
			'logo_transparent' => 'theme:logo-white.png',
			'logo_mobile'      => 'uploads:2023/08/STUDLOGO.png',
			'footer_logo'      => 'uploads:2023/08/STUDLOGO.png',
			'footer_bg'        => 'theme:theme/footer-3.jpg',
			'hero_bg'          => 'uploads:2023/09/hero-banner3.png',
			'hero_img'         => 'theme:demo/hero-img-5.png',
			'banner_yellow'    => 'theme:demo/banner-img-4.webp',
			'banner_blue'      => 'theme:demo/banner-img-3-man.webp',
			'widget_banner'    => 'theme:demo/widget-banner1.jpg',
			'cta_img'          => 'theme:demo/call-action-img2.png',
			'about_1'          => 'theme:demo/about-1.jpg',
			'about_2'          => 'theme:demo/about-2.jpg',
			'about_3'          => 'theme:demo/about-3.jpg',
			'pricing_shape'    => 'theme:demo/pricing-card-shape.svg',
			'auth_banner'      => 'theme:demo/banner-img-1.png',
			'auth_bg'          => 'theme:demo/banner-bg.png',
			'team_1'           => 'theme:demo/team-img-1.jpg',
			'team_2'           => 'theme:demo/team-img-2.jpg',
			'team_3'           => 'theme:demo/team-img-3.jpg',
			'team_4'           => 'theme:demo/team-img-4.jpg',
		)
	);
}

function petslist_theme_img_url( $relative_path ) {
	return trailingslashit( get_template_directory_uri() ) . 'assets/img/' . ltrim( $relative_path, '/' );
}

function petslist_img_url( $key ) {
	$paths = petslist_image_paths();
	if ( ! isset( $paths[ $key ] ) ) {
		return '';
	}

	$path = $paths[ $key ];
	if ( 0 === strpos( $path, 'uploads:' ) ) {
		return petslist_upload_url( substr( $path, 8 ) );
	}
	if ( 0 === strpos( $path, 'theme:' ) ) {
		return petslist_theme_img_url( substr( $path, 6 ) );
	}

	return $path;
}

function petslist_logo_url( $variant = 'dark' ) {
	$map = array(
		'dark'        => 'logo_dark',
		'light'       => 'logo_light',
		'transparent' => 'logo_transparent',
		'mobile'      => 'logo_mobile',
		'footer'      => 'footer_logo',
	);
	$key = $map[ $variant ] ?? 'logo_dark';
	return petslist_img_url( $key );
}

/**
 * Logo <img> tag from hardcoded path (replaces wp_get_attachment_image for branding).
 */
function petslist_logo_img( $variant = 'dark', $attrs = array() ) {
	$url = petslist_logo_url( $variant );
	if ( ! $url ) {
		return '';
	}

	$defaults = array(
		'src'     => $url,
		'class'   => 'attachment-full size-full',
		'alt'     => get_bloginfo( 'name' ),
		'loading' => 'lazy',
	);
	$attrs = wp_parse_args( $attrs, $defaults );

	$html = '<img';
	foreach ( $attrs as $name => $value ) {
		if ( '' !== $value && null !== $value ) {
			$html .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
	}
	$html .= ' />';

	return $html;
}

/**
 * [url, width, height] tuple for mobile menu logo markup.
 */
function petslist_logo_src_tuple( $variant = 'dark' ) {
	$dims = array(
		'dark'        => array( 196, 41 ),
		'light'       => array( 196, 41 ),
		'transparent' => array( 157, 40 ),
		'mobile'      => array( 196, 41 ),
		'footer'      => array( 196, 41 ),
	);
	$size = $dims[ $variant ] ?? array( 196, 41 );

	return array( petslist_logo_url( $variant ), $size[0], $size[1] );
}

/**
 * Ensure header action buttons exist (demo-style navbar).
 */
function petslist_ensure_header_options() {
	// Header action button -> styled red pill, used as the Login button.
	set_theme_mod( 'header_btn', 1 );
	set_theme_mod( 'header_btn_txt', 'Login' );
	set_theme_mod( 'header_btn_url', function_exists( 'dd_login_url' ) ? dd_login_url() : home_url( '/login/' ) );

	// Remove the separate light login pill (red button now handles login).
	set_theme_mod( 'header_login_icon', 0 );

	// Live Chat button is disabled in the header.
	set_theme_mod( 'header_chat_icon', 0 );
}
add_action( 'after_setup_theme', 'petslist_ensure_header_options', 25 );

/**
 * Footer style 3 background + logo use hardcoded paths (not Customizer attachment IDs).
 */
function petslist_footer_hardcoded_styles() {
	$bg = petslist_img_url( 'footer_bg' );
	if ( ! $bg ) {
		return;
	}
	?>
	<style id="petslist-footer-hardcoded">
		footer.footer-style-3 {
			background-image: url(<?php echo esc_url( $bg ); ?>) !important;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'petslist_footer_hardcoded_styles', 100 );

/**
 * Force the custom (no-Elementor) templates for Home / About / FAQ by slug,
 * overriding any Elementor Canvas/Full-Width template assigned in the DB.
 *
 * Works on any environment (local + live) with just a theme deploy — no DB
 * changes needed. Our templates render hardcoded markup and never call
 * the_content(), so stored Elementor data is bypassed entirely.
 *
 * Priority 99 so it runs after Elementor's own template_include hook.
 */
function petslist_force_custom_templates( $template ) {
	$theme_dir = get_template_directory();

	// Front page -> front-page.php (already default in the hierarchy, but enforce).
	if ( is_front_page() && ! is_home() ) {
		$front = $theme_dir . '/front-page.php';
		if ( file_exists( $front ) ) {
			return $front;
		}
	}

	// Map of page slug => custom template file.
	$map = array(
		'about'   => 'page-about.php',
		'faq'     => 'page-faq.php',
		'contact' => 'page-contact.php',
	);

	if ( is_page() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && isset( $map[ $post->post_name ] ) ) {
			$custom = $theme_dir . '/' . $map[ $post->post_name ];
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}
	}

	return $template;
}
add_filter( 'template_include', 'petslist_force_custom_templates', 99 );

/**
 * Stop Elementor from enqueuing its page CSS/JS on our custom pages so the
 * design is 100% the theme's own styling.
 */
function petslist_is_custom_template_page() {
	if ( is_front_page() && ! is_home() ) {
		return true;
	}
	if ( is_page() ) {
		$post = get_queried_object();
		return $post instanceof WP_Post && in_array( $post->post_name, array( 'about', 'faq', 'contact' ), true );
	}
	return false;
}

function petslist_upload_url( $relative_path ) {
	return content_url( 'uploads/' . ltrim( $relative_path, '/' ) );
}

function petslist_custom_pages_assets() {
	$theme_uri = get_template_directory_uri();
	$ver       = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'petslist-pages', $theme_uri . '/assets/css/petslist-pages.css', array(), $ver );

	$core_css = WP_PLUGIN_DIR . '/petslist-core/assets/css/petslist-elementor.css';
	if ( file_exists( $core_css ) ) {
		wp_enqueue_style( 'petslist-elementor-widgets', plugins_url( 'petslist-core/assets/css/petslist-elementor.css' ), array(), $ver );
	}

	if ( is_front_page() && file_exists( get_template_directory() . '/assets/css/dog-directory.css' ) ) {
		wp_enqueue_style( 'petslist-dog-directory', $theme_uri . '/assets/css/dog-directory.css', array(), $ver );
	}
}
add_action( 'wp_enqueue_scripts', function () {
	if ( is_front_page() || is_page( array( 'about', 'faq', 'contact' ) ) ) {
		petslist_custom_pages_assets();
	}
}, 20 );

function petslist_check_icon() {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none" aria-hidden="true"><circle cx="13" cy="13" r="13" fill="#02C5BD"></circle><path d="M9.37 14L13.36 18.17L21.33 9.83M4.67 14L8.66 18.17M16.63 9.83L13.6 13.03" stroke="white" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
}

function petslist_faq_accordion_icon( $open = false ) {
	if ( $open ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true"><circle cx="15" cy="15" r="14.2" fill="white" stroke="#515167" stroke-width="1.6"></circle><rect x="7" y="14" width="16" height="2" rx="1" fill="#515167"></rect></svg>';
	}
	return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true"><circle cx="15" cy="15" r="14.2" fill="white" stroke="#515167" stroke-width="1.6"></circle><rect x="7" y="14" width="16" height="2" rx="1" fill="#515167"></rect><rect x="16" y="7" width="16" height="2" rx="1" transform="rotate(90 16 7)" fill="#515167"></rect></svg>';
}

function petslist_pricing_features() {
	return array(
		__( '3 Regular Ads', 'petslist' ),
		__( 'No Featured Ads', 'petslist' ),
		__( 'No Top Ads', 'petslist' ),
		__( 'No Ads Will Be Bumped Up', 'petslist' ),
		__( 'Limited Support', 'petslist' ),
	);
}

/**
 * Render the theme's listing category list (Dogs, Cats, Birds ...) exactly like
 * the petslist-core "Listing Categories" widget (colored circle + icon + count).
 */
function petslist_render_listing_categories( $limit = 7 ) {
	if ( ! taxonomy_exists( 'rtcl_category' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'rtcl_category',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}

	echo '<ul class="category-list layout-4">';
	foreach ( $terms as $term ) {
		$color = get_term_meta( $term->term_id, 'rt_category_color', true );
		$icon  = '';
		if ( class_exists( '\RadiusTheme\Petslist\Listing_Functions' ) ) {
			$icon = \RadiusTheme\Petslist\Listing_Functions::listing_cat_icon( $term->term_id, 'image' );
		}

		if ( class_exists( '\Rtcl\Helpers\Link' ) ) {
			$link = \Rtcl\Helpers\Link::get_location_page_link( $term );
		} else {
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				$link = '#';
			}
		}
		?>
		<li class="category-item">
			<div class="icon"<?php echo $color ? ' style="background-color:#' . esc_attr( $color ) . '"' : ''; ?>>
				<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="content">
				<a href="<?php echo esc_url( $link ); ?>" class="category-name"><?php echo esc_html( $term->name ); ?></a>
				<p class="item-number">(<?php echo esc_html( number_format_i18n( $term->count ) ); ?>)</p>
			</div>
		</li>
		<?php
	}
	echo '</ul>';
}

/**
 * Dog breed sidebar for home page (real dd_breed counts + colors).
 */
function petslist_render_dog_breeds( $limit = 16 ) {
	if ( ! function_exists( 'dd_get_breeds' ) ) {
		return;
	}

	$breeds      = dd_get_breeds( $limit );
	$directory   = function_exists( 'dd_dog_directory_url' ) ? dd_dog_directory_url() : home_url( '/dog-directory/' );
	$default_clr = array( 'ff3d41', 'ffb13d', 'ff27b6', '21cd1e', '03aaf2', '9b59b6', 'e67e22', '16B4A1' );

	if ( empty( $breeds ) ) {
		return;
	}

	echo '<ul class="category-list layout-4">';
	$add_dog_url = is_user_logged_in() 
		? dd_dashboard_url('add-dog') 
		: add_query_arg( 'redirect_to', urlencode( dd_dashboard_url('add-dog') ), dd_login_url() );
	?>
	<li class="category-item dda-post-ad-item" style="background: linear-gradient(135deg, #02c5bd 0%, #02a39d 100%); border-radius: 12px; margin-bottom: 16px; padding: 12px 18px; box-shadow: 0 4px 12px rgba(2, 197, 189, 0.2); transition: all 0.2s ease;">
		<a href="<?php echo esc_url( $add_dog_url ); ?>" style="display: flex; align-items: center; text-decoration: none; width: 100%;">
			<div class="icon" style="background: rgba(255, 255, 255, 0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 14px; flex-shrink: 0; box-shadow: none;">
				<i class="fa-solid fa-plus" style="color: #ffffff; font-size: 16px;"></i>
			</div>
			<div class="content" style="padding: 0;">
				<span class="category-name" style="color: #ffffff !important; font-weight: 700; font-size: 15px; letter-spacing: 0.2px;"><?php esc_html_e( 'Post an Ad / Add Dog', 'petslist' ); ?></span>
			</div>
		</a>
	</li>
	<?php
	foreach ( $breeds as $i => $term ) {
		$color = get_term_meta( $term->term_id, 'dd_breed_color', true );
		if ( ! $color ) {
			$color = $default_clr[ $i % count( $default_clr ) ];
		}
		$link = add_query_arg( 'breed', $term->name, $directory );
		?>
		<li class="category-item">
			<div class="icon">
				<img src="<?php echo esc_url( content_url( 'uploads/paw.png' ) ); ?>" alt="" style="max-width:38px;max-height:38px;object-fit:contain;">
			</div>
			<div class="content">
				<a href="<?php echo esc_url( $link ); ?>" class="category-name"><?php echo esc_html( $term->name ); ?></a>
			</div>
		</li>
		<?php
	}
	echo '</ul>';
}

function petslist_render_dog_cards( $limit = 6 ) {
	if ( ! function_exists( 'dd_get_dog_meta' ) ) {
		return;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'dd_dog',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	if ( ! $query->have_posts() ) {
		echo '<p class="petslist-empty-note">' . esc_html__( 'No dog profiles yet.', 'petslist' ) . '</p>';
		return;
	}

	$posts = $query->posts;

	// Filter posts: keep only sponsored dogs
	$posts = array_filter( $posts, function( $post ) {
		$meta = get_post_meta( $post->ID, '_dd_dog_meta', true ) ?: [];
		return isset( $meta['is_sponsored'] ) && $meta['is_sponsored'] === 'Yes';
	});

	if ( empty( $posts ) ) {
		echo '<p class="petslist-empty-note">' . esc_html__( 'No sponsored ads yet.', 'petslist' ) . '</p>';
		return;
	}

	echo '<div class="dd-dir-grid petslist-home-dogs-grid">';
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$pid     = $post->ID;
		$meta    = dd_get_dog_meta( $pid );
		$age     = dd_get_dog_age( $meta['dob'] ?? '' );
		$thumb   = get_the_post_thumbnail_url( $pid, 'large' ) ?: dd_placeholder_image();
		$terms   = get_the_terms( $pid, 'dd_breed' );
		$breed   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : ( $meta['breed'] ?? '' );
		$gender  = $meta['gender'] ?? '';
		$color   = $meta['color'] ?? '';
		$city    = $meta['city'] ?? '';
		$country = $meta['country'] ?? '';
		$loc     = trim( implode( ', ', array_filter( array( $city, $country ) ) ) );
		$is_male = strtolower( $gender ) === 'male';
		$is_sponsored = isset( $meta['is_sponsored'] ) && $meta['is_sponsored'] === 'Yes';
		$sponsored_class = $is_sponsored ? ' dd-dir-card--sponsored' : '';
		?>
		<article class="dd-dir-card<?php echo esc_attr( $sponsored_class ); ?>">
			<div class="dd-dir-card__image">
				<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="dd-dir-card__image-link">
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $pid ) ); ?>" loading="lazy">
					<div class="dd-dir-card__image-overlay"></div>
				</a>
				<?php if ( $is_sponsored ) : ?>
					<span class="dd-dir-card__sponsored-badge"><?php esc_html_e( 'Sponsored', 'petslist' ); ?></span>
				<?php endif; ?>
				<?php if ( $gender ) : ?>
					<span class="dd-dir-card__gender dd-dir-card__gender--<?php echo esc_attr( strtolower( $gender ) ); ?>">
						<?php echo $is_male ? '&#9794;' : '&#9792;'; ?> <?php echo esc_html( $gender ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $age ) : ?>
					<span class="dd-dir-card__age-pill"><?php echo esc_html( $age ); ?></span>
				<?php endif; ?>
			</div>
			<div class="dd-dir-card__body">
				<?php if ( $breed ) : ?>
					<div class="dd-dir-card__breed"><?php echo esc_html( $breed ); ?></div>
				<?php endif; ?>
				<h3 class="dd-dir-card__name">
					<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
				</h3>
				<div class="dd-dir-card__meta">
					<?php if ( $loc ) : ?>
						<span class="dd-dir-card__meta-item">
							<span class="dd-dir-card__meta-icon">&#128205;</span>
							<?php echo esc_html( $loc ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $color ) : ?>
						<span class="dd-dir-card__meta-item">
							<span class="dd-dir-card__meta-icon">&#127912;</span>
							<?php echo esc_html( $color ); ?>
						</span>
					<?php endif; ?>
				</div>
				<div class="dd-dir-card__footer">
					<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="dd-dir-card__cta">
						<?php esc_html_e( 'View Profile', 'petslist' ); ?>
						<span class="dd-dir-card__cta-arrow">&rarr;</span>
					</a>
				</div>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
}
