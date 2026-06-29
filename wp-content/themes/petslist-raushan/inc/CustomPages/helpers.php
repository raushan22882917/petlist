<?php
/**
 * Shared helpers for custom page templates (no Elementor).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure header logo + action buttons exist (demo-style navbar).
 * Only fills empty theme_mod values so manual Customizer changes are kept.
 */
function petslist_ensure_header_options() {
	$logo_dark = get_theme_mod( 'logo_dark' );
	if ( empty( $logo_dark ) ) {
		$attachment = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_wp_attached_file',
						'value'   => '2023/08/petslist_logo2.svg',
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( $attachment ) {
			set_theme_mod( 'logo_dark', $attachment[0]->ID );
		}
	}

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

function petslist_upload_url( $relative_path ) {
	return content_url( 'uploads/' . ltrim( $relative_path, '/' ) );
}

function petslist_custom_pages_assets() {
	$theme_uri = get_template_directory_uri();
	$ver       = '1.0.0';

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
	foreach ( $breeds as $i => $term ) {
		$color = get_term_meta( $term->term_id, 'dd_breed_color', true );
		if ( ! $color ) {
			$color = $default_clr[ $i % count( $default_clr ) ];
		}
		$link = add_query_arg( 'breed', $term->name, $directory );
		?>
		<li class="category-item">
			<div class="icon" style="background-color:#<?php echo esc_attr( $color ); ?>">
				<i class="icon-pl-category" aria-hidden="true"></i>
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

	echo '<div class="dd-dir-grid petslist-home-dogs-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$pid     = get_the_ID();
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
		?>
		<article class="dd-dir-card">
			<div class="dd-dir-card__image">
				<a href="<?php the_permalink(); ?>" class="dd-dir-card__image-link">
					<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
					<div class="dd-dir-card__image-overlay"></div>
				</a>
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
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
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
					<a href="<?php the_permalink(); ?>" class="dd-dir-card__cta">
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
