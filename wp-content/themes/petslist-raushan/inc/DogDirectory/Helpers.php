<?php
/**
 * Dog Directory - Global Helper Functions
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// -------------------------------------------------------
// URL HELPERS
// -------------------------------------------------------

function dd_page_url( $option_key, $fallback_slug = '' ) {
    $page_id = get_option( $option_key );
    if ( $page_id ) return get_permalink( $page_id );
    return home_url( '/' . trim($fallback_slug, '/') . '/' );
}

function dd_login_url()     { return dd_page_url('dd_page_login',    'login'); }
function dd_register_url()  { return dd_page_url('dd_page_register', 'register'); }
function dd_pricing_url( $plan = '' ) {
    $url = dd_page_url('dd_page_pricing', 'dog-directory-plans');
    return $plan ? add_query_arg('plan', $plan, $url) : $url;
}
function dd_checkout_url( $plan = '' ) {
    $url = dd_page_url('dd_page_checkout', 'dog-checkout');
    return $plan ? add_query_arg('plan', $plan, $url) : $url;
}
function dd_dashboard_url( $tab = '' ) {
    $url = dd_page_url('dd_page_dashboard', 'my-account');
    return $tab ? add_query_arg('tab', $tab, $url) : $url;
}
function dd_dog_directory_url() {
    return get_post_type_archive_link('dd_dog') ?: home_url('/dog-directory/');
}

// -------------------------------------------------------
// PAGE DETECTION
// -------------------------------------------------------

function dd_is_page( $option_key ) {
    if ( ! is_page() ) return false;
    return (int) get_option($option_key) === (int) get_the_ID();
}

function dd_is_login_page()     { return dd_is_page('dd_page_login'); }
function dd_is_register_page()  { return dd_is_page('dd_page_register'); }
function dd_is_pricing_page()   { return dd_is_page('dd_page_pricing'); }
function dd_is_checkout_page()  { return dd_is_page('dd_page_checkout'); }
function dd_is_dashboard_page() { return dd_is_page('dd_page_dashboard'); }
function dd_is_forgot_page()    { return dd_is_page('dd_page_forgot'); }

// -------------------------------------------------------
// DOG DATA HELPERS
// -------------------------------------------------------

function dd_get_dog_meta( $post_id, $key = '' ) {
    $meta = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: [];
    return $key ? ( $meta[$key] ?? '' ) : $meta;
}

function dd_get_dog_health( $post_id, $key = '' ) {
    $health = get_post_meta( $post_id, '_dd_dog_health', true ) ?: [];
    return $key ? ( $health[$key] ?? '' ) : $health;
}

function dd_get_dog_age( $dob ) {
    if ( empty($dob) ) return '';
    try {
        $birth = new DateTime($dob);
        $now   = new DateTime();
        $diff  = $now->diff($birth);
        if ( $diff->y > 0 ) {
            return $diff->y . ' ' . _n('year', 'years', $diff->y, 'petslist');
        }
        return $diff->m . ' ' . _n('month', 'months', $diff->m, 'petslist');
    } catch ( Exception $e ) {
        return '';
    }
}

function dd_get_front_photo_url( $post_id, $size = 'large' ) {
    $id = get_post_meta($post_id, '_dd_front_photo', true);
    if ( $id ) return wp_get_attachment_image_url($id, $size);
    return get_the_post_thumbnail_url($post_id, $size) ?: dd_placeholder_image();
}

function dd_get_side_photo_url( $post_id, $size = 'large' ) {
    $id = get_post_meta($post_id, '_dd_side_photo', true);
    if ( $id ) return wp_get_attachment_image_url($id, $size);
    return dd_placeholder_image();
}

function dd_placeholder_image() {
    return get_template_directory_uri() . '/assets/img/dog-placeholder.svg';
}

// -------------------------------------------------------
// USER DOGS
// -------------------------------------------------------

function dd_get_user_dogs( $user_id = 0, $status = 'any', $per_page = -1 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    return get_posts([
        'post_type'      => 'dd_dog',
        'post_status'    => $status,
        'author'         => $user_id,
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

function dd_get_user_dog_count( $user_id = 0 ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    $query = new WP_Query([
        'post_type'   => 'dd_dog',
        'post_status' => ['publish','pending','draft'],
        'author'      => $user_id,
        'fields'      => 'ids',
    ]);
    return $query->found_posts;
}

// -------------------------------------------------------
// SUBSCRIPTION DISPLAY
// -------------------------------------------------------

function dd_subscription_badge( $user_id = 0 ) {
    $sub = \RadiusTheme\Petslist\DogDirectory\Subscription::get_user_subscription($user_id);
    if ( ! $sub ) {
        return '<span class="dd-badge dd-badge--inactive">' . __('No Subscription', 'petslist') . '</span>';
    }
    $expires = human_time_diff(strtotime($sub->expires_at), time());
    $label   = $sub->status === 'active'
        ? sprintf(__('Active — %s (%s left)', 'petslist'), esc_html($sub->plan_name), $expires)
        : ucfirst($sub->status);
    return '<span class="dd-badge dd-badge--' . esc_attr($sub->status) . '">' . $label . '</span>';
}

// -------------------------------------------------------
// BREED LIST (for search filters)
// -------------------------------------------------------

/**
 * Canonical dog breeds shown in Add Dog dropdown and home sidebar.
 */
function dd_default_breed_names() {
	return array(
		'American Bully',
		'French Bulldog',
		'English Bulldog',
		'American Pitbull Terrier',
		'American Bulldog',
		'Olde English Bulldogge',
		'American Staffordshire Terrier',
		'Brazilian Bull',
		'Shorty Bull',
		'German Shepherd',
		'Rottweiler',
		'Chihuahua',
		'Golden Retriever',
		'Doberman Pinscher',
		'Cane Corso',
		'Labrador Retriever',
		'Fluffy Frenchy',
	);
}

/**
 * Create default breed terms (once) and assign colors for the home sidebar.
 */
function dd_ensure_default_breeds() {
	if ( get_option( 'dd_breeds_seeded_v3' ) && term_exists( 'American Bully', 'dd_breed' ) ) {
		return;
	}

	$colors = array( 'ff3d41', 'ffb13d', 'ff27b6', '21cd1e', '03aaf2', '9b59b6', 'e67e22', '16B4A1', '070C46', '02C5BD', 'FF6B6B', '4A3AFF', 'FFC107', '8E44AD', '2ECC71', 'F39C12' );

	foreach ( dd_default_breed_names() as $i => $name ) {
		$term = term_exists( $name, 'dd_breed' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'dd_breed' );
		}
		if ( ! is_wp_error( $term ) ) {
			$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			update_term_meta( $term_id, 'dd_breed_color', $colors[ $i % count( $colors ) ] );
		}
	}

	// Ensure American Bully subcategories are seeded under parent
	$parent_term = get_term_by( 'name', 'American Bully', 'dd_breed' );
	if ( $parent_term && ! is_wp_error( $parent_term ) ) {
		$parent_id = (int) $parent_term->term_id;
		$subs = array( 'Pocket', 'Classic', 'Standard', 'XL', 'XXL' );
		foreach ( $subs as $sub_name ) {
			$sub_term = term_exists( $sub_name, 'dd_breed' );
			if ( ! $sub_term ) {
				wp_insert_term( $sub_name, 'dd_breed', array( 'parent' => $parent_id ) );
			} else {
				$sub_term_id = (int) ( is_array( $sub_term ) ? $sub_term['term_id'] : $sub_term );
				wp_update_term( $sub_term_id, 'dd_breed', array( 'parent' => $parent_id ) );
			}
		}
	}

	update_option( 'dd_breeds_seeded_v3', 1, false );
	dd_sync_dog_breed_taxonomy();
}

/**
 * Match free-text breed meta to a canonical breed name.
 */
function dd_match_breed_name( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}

	foreach ( dd_default_breed_names() as $name ) {
		if ( strcasecmp( $raw, $name ) === 0 ) {
			return $name;
		}
	}

	$subs = array( 'Pocket', 'Classic', 'Standard', 'XL', 'XXL' );
	foreach ( $subs as $name ) {
		if ( strcasecmp( $raw, $name ) === 0 ) {
			return $name;
		}
	}

	$raw_lc = strtolower( $raw );
	foreach ( dd_default_breed_names() as $name ) {
		$name_lc = strtolower( $name );
		if ( str_contains( $name_lc, $raw_lc ) || str_contains( $raw_lc, $name_lc ) ) {
			return $name;
		}
	}

	foreach ( $subs as $name ) {
		$name_lc = strtolower( $name );
		if ( str_contains( $name_lc, $raw_lc ) || str_contains( $raw_lc, $name_lc ) ) {
			return $name;
		}
	}

	return $raw;
}

/**
 * Returns a structured breed list for select rendering.
 *
 * Each entry is either:
 *   [ 'value' => string, 'label' => string ]                         – plain option
 *   [ 'value' => string, 'label' => string, 'children' => [...] ]    – optgroup parent
 *
 * @param bool $include_counts Append "(n)" counts to labels.
 * @return array
 */
function dd_get_breed_options( $include_counts = false ) {
	// Placeholder row (value '' means "nothing selected")
	$entries = [];

	$terms = get_terms( [
		'taxonomy'   => 'dd_breed',
		'hide_empty' => false,
		'parent'     => 0,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	// Fallback: no taxonomy terms yet → return flat list
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		foreach ( dd_default_breed_names() as $name ) {
			$entries[] = [ 'value' => $name, 'label' => $name ];
		}
		return $entries;
	}

	// Order parents to match dd_default_breed_names() ordering
	$order = array_flip( dd_default_breed_names() );
	usort( $terms, function( $a, $b ) use ( $order ) {
		$ia = $order[ $a->name ] ?? 999;
		$ib = $order[ $b->name ] ?? 999;
		return $ia - $ib;
	} );

	foreach ( $terms as $parent ) {
		$label = $parent->name;
		if ( $include_counts ) {
			$label .= ' (' . $parent->count . ')';
		}

		// Fetch children for ANY parent that has sub-terms
		$children = get_terms( [
			'taxonomy'   => 'dd_breed',
			'hide_empty' => false,
			'parent'     => $parent->term_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		] );

		if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
			// Build children rows
			$child_entries = [];
			foreach ( $children as $child ) {
				$child_label = $child->name;
				if ( $include_counts ) {
					$child_label .= ' (' . $child->count . ')';
				}
				// display_label shows full context: "ParentName — ChildName"
				$child_display = $parent->name . ' — ' . $child->name;
				if ( $include_counts ) {
					$child_display .= ' (' . $child->count . ')';
				}
				$child_entries[] = [
					'value'         => $child->name,
					'label'         => $child_label,
					'display_label' => $child_display,
				];
			}
			$entries[] = [
				'value'    => $parent->name,
				'label'    => $label,
				'children' => $child_entries,
			];
		} else {
			$entries[] = [ 'value' => $parent->name, 'label' => $label ];
		}
	}

	return $entries;
}

/**
 * Render <option>/<optgroup> HTML from dd_get_breed_options().
 *
 * Usage: dd_render_breed_options( $selected_value, $include_counts, $placeholder_text )
 *
 * @param string $selected       Currently selected breed value.
 * @param bool   $include_counts Whether to append dog counts.
 * @param string $placeholder    Text for the blank "pick one" option.
 */
function dd_render_breed_options( $selected = '', $include_counts = false, $placeholder = '' ) {
	if ( $placeholder === '' ) {
		$placeholder = $include_counts ? __( 'All Breeds', 'petslist' ) : __( 'Select Breed', 'petslist' );
	}

	// Blank / placeholder option
	printf(
		'<option value=""%s>%s</option>',
		selected( $selected, '', false ),
		esc_html( $placeholder )
	);

	$entries = dd_get_breed_options( $include_counts );

	foreach ( $entries as $entry ) {
		if ( ! empty( $entry['children'] ) ) {
			// Parent with sub-breeds → render as <optgroup>
			printf( '<optgroup label="%s">', esc_attr( $entry['label'] ) );

			// First option inside the group represents the parent itself
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $entry['value'] ),
				selected( $selected, $entry['value'], false ),
				esc_html( __( 'All', 'petslist' ) . ' ' . $entry['label'] )
			);

			foreach ( $entry['children'] as $child ) {
				// Use display_label so the selected text reads "ParentBreed — SubBreed"
				$display = $child['display_label'] ?? $child['label'];
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $child['value'] ),
					selected( $selected, $child['value'], false ),
					esc_html( $display )
				);
			}

			echo '</optgroup>';
		} else {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $entry['value'] ),
				selected( $selected, $entry['value'], false ),
				esc_html( $entry['label'] )
			);
		}
	}
}

/**
 * Assign taxonomy terms from stored breed meta (fixes counts on home page).
 */
function dd_sync_dog_breed_taxonomy() {
	$dogs = get_posts(
		array(
			'post_type'      => 'dd_dog',
			'post_status'    => array( 'publish', 'pending', 'draft' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $dogs as $post_id ) {
		$meta  = get_post_meta( $post_id, '_dd_dog_meta', true ) ?: array();
		$breed = dd_match_breed_name( $meta['breed'] ?? '' );
		if ( '' === $breed ) {
			continue;
		}

		if ( ( $meta['breed'] ?? '' ) !== $breed ) {
			$meta['breed'] = $breed;
			update_post_meta( $post_id, '_dd_dog_meta', $meta );
		}

		$term = term_exists( $breed, 'dd_breed' );
		if ( ! $term ) {
			$term = wp_insert_term( $breed, 'dd_breed' );
		}
		if ( ! is_wp_error( $term ) ) {
			wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), 'dd_breed', false );
		}
	}
}
add_action( 'init', 'dd_ensure_default_breeds', 20 );

function dd_get_breeds( $limit = 0 ) {
	$ordered = array();
	foreach ( dd_default_breed_names() as $name ) {
		$term = get_term_by( 'name', $name, 'dd_breed' );
		if ( $term && ! is_wp_error( $term ) ) {
			$ordered[] = $term;
		}
	}

	if ( $limit > 0 ) {
		$ordered = array_slice( $ordered, 0, $limit );
	}

	return $ordered;
}

function dd_get_breed_dog_count( $term_id ) {
	$term = get_term( $term_id, 'dd_breed' );
	if ( ! $term || is_wp_error( $term ) ) {
		return 0;
	}

	$term_ids = array( (int) $term->term_id );
	$children = get_terms( array(
		'taxonomy'   => 'dd_breed',
		'hide_empty' => false,
		'parent'     => $term->term_id,
		'fields'     => 'ids',
	) );
	if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
		$term_ids = array_merge( $term_ids, array_map( 'intval', $children ) );
	}

	$breed_names = array();
	foreach ( $term_ids as $tid ) {
		$t = get_term( $tid, 'dd_breed' );
		if ( $t && ! is_wp_error( $t ) ) {
			$breed_names[] = $t->name;
		}
	}

	$query = new WP_Query( array(
		'post_type'      => 'dd_dog',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array(
			array(
				'taxonomy' => 'dd_breed',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			),
		),
	) );
	$count = $query->found_posts;

	if ( ! empty( $breed_names ) ) {
		$all_dogs = get_posts( array(
			'post_type'      => 'dd_dog',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		foreach ( $all_dogs as $dog_id ) {
			if ( in_array( $dog_id, $query->posts, true ) ) {
				continue;
			}
			$meta = get_post_meta( $dog_id, '_dd_dog_meta', true ) ?: array();
			$b_name = $meta['breed'] ?? '';
			if ( ! empty( $b_name ) && in_array( $b_name, $breed_names, true ) ) {
				$count++;
			}
		}
	}

	return $count;
}

function dd_get_locations() {
    return get_terms(['taxonomy' => 'dd_location', 'orderby' => 'name', 'hide_empty' => false]);
}

// -------------------------------------------------------
// ACCESS GATES
// -------------------------------------------------------

function dd_require_login( $redirect = '' ) {
    if ( ! is_user_logged_in() ) {
        $url = $redirect ?: dd_login_url();
        wp_safe_redirect( add_query_arg('redirect_to', urlencode(get_permalink()), $url) );
        exit;
    }
}

function dd_require_subscription( $redirect = '' ) {
    dd_require_login();
    if ( ! \RadiusTheme\Petslist\DogDirectory\Subscription::can_access_directory() ) {
        wp_safe_redirect( $redirect ?: dd_pricing_url() );
        exit;
    }
}

// -------------------------------------------------------
// STRIPE HELPERS
// -------------------------------------------------------

function dd_stripe_publishable_key() {
    return get_option('dd_stripe_publishable_key', '');
}

function dd_stripe_secret_key() {
    return get_option('dd_stripe_secret_key', '');
}

function dd_stripe_webhook_secret() {
    return get_option('dd_stripe_webhook_secret', '');
}

// -------------------------------------------------------
// PAYPAL HELPERS
// -------------------------------------------------------

function dd_paypal_client_id() {
    return get_option('dd_paypal_client_id', '');
}

function dd_paypal_secret() {
    return get_option('dd_paypal_secret', '');
}

function dd_paypal_mode() {
    return get_option('dd_paypal_mode', 'sandbox');
}

// -------------------------------------------------------
// MISC
// -------------------------------------------------------

function dd_format_price( $amount, $currency = 'USD' ) {
    return '$' . number_format($amount, 2);
}

function dd_gender_icon( $gender ) {
    if ( $gender === 'Male' ) return '<i class="icon-pl-account dd-icon--male" title="Male"></i>';
    if ( $gender === 'Female' ) return '<i class="icon-pl-account-fill dd-icon--female" title="Female"></i>';
    return '';
}
