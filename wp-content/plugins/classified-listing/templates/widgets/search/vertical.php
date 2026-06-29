<?php
/**
 * @var string $id
 * @var bool $radius_search
 * @var boolean $can_search_by_location
 * @var boolean $can_search_by_category
 * @var boolean $can_search_by_listing_types
 * @var boolean $can_search_by_price
 */

/* phpcs:disable WordPress.Security.NonceVerification.Recommended */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Resources\Options;

$orderby = strtolower( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_orderby', 'name' ) );
$order   = strtoupper( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_order', 'DESC' ) );
?>
<div class="rtcl rtcl-search rtcl-search-vertical">
	<form action="<?php echo esc_url( Functions::get_filter_form_url() ) ?>"
		  class="rtcl-widget-search-vertical rtcl-widget-search-form">
		<div class="rtcl-form-group">
			<label class="screen-reader-text" for="rtcl-keyword-search-<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'keyword', 'classified-listing' ); ?>
			</label>
			<input type="text" name="q" class="rtcl-form-control" id="rtcl-keyword-search-<?php echo esc_attr( $id ); ?>"
				   placeholder="<?php esc_attr_e( 'Enter your keyword here ...', 'classified-listing' ); ?>"
				   value="<?php if ( isset( $_GET['q'] ) ) {
					   echo esc_attr( Functions::clean( wp_unslash( $_GET['q'] ) ) );
				   } ?>">
		</div>

		<?php if ( $radius_search ):
			$rs_data = Options::radius_search_options();
			?>
			<div class="rtcl-form-group">
				<label for="rtc-geo-search-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( Text::get_select_location_text() ); ?></label>
				<div class="rtcl-geo-address-field">
					<input type="text" name="geo_address" autocomplete="off"
						   value="<?php echo ! empty( $_GET['geo_address'] ) ? esc_attr( $_GET['geo_address'] ) : '' ?>"
						   placeholder="<?php esc_attr_e( 'Select a location', 'classified-listing' ) ?>"
						   class="rtcl-form-control rtcl-geo-address-input" id="rtc-geo-search-<?php echo esc_attr( $id ); ?>"/>
					<i class="rtcl-get-location rtcl-icon rtcl-icon-target"></i>
					<input type="hidden" class="latitude" name="center_lat"
						   value="<?php echo ! empty( $_GET['center_lat'] ) ? esc_attr( $_GET['center_lat'] ) : '' ?>">
					<input type="hidden" class="longitude" name="center_lng"
						   value="<?php echo ! empty( $_GET['center_lng'] ) ? esc_attr( $_GET['center_lng'] ) : '' ?>">
				</div>
				<div class="rtcl-range-slider-field">
					<div class="rtcl-range-label">
						<?php echo wp_kses(
							sprintf(
							/* translators: Radius unit */ __( 'Radius (%1$s %2$s)', 'classified-listing' ),
								sprintf( '<span class="rtcl-range-value">%s</span>', ! empty( $_GET['distance'] ) ? absint( $_GET['distance'] ) : 0 ),
								in_array( $rs_data['units'], [ 'km', 'kilometers' ] ) ? __( 'km', 'classified-listing' ) : __( 'Miles', 'classified-listing' ),
							),
							[
								'span' => [
									'class' => [],
								],
							],
						) ?>
					</div>
					<input type="range" class="form-control-range rtcl-range-slider-input" name="distance" min="0"
						   max="<?php echo absint( $rs_data['max_distance'] ) ?>"
						   value="<?php echo absint( isset( $_GET['distance'] ) ? $_GET['distance'] : $rs_data['default_distance'] ) ?>">
				</div>
			</div>
		<?php endif ?>

		<?php if ( 'local' === Functions::location_type() && $can_search_by_location ) : ?>
			<!-- Location field -->
			<div class="rtcl-form-group">
				<label for="rtcl-location-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select a location', 'classified-listing' ); ?></label>
				<?php
				$location = 0;
				if ( $loc_slug = get_query_var( '__loc' ) ) {
					$lTerm = get_term_by( 'slug', $loc_slug, rtcl()->location );
					if ( $lTerm ) {
						$location = $lTerm->term_id;
					}
				}
				Functions::dropdown_terms( [
					'show_option_none' => '-- ' . esc_html__( 'Select a location', 'classified-listing' ) . ' --',
					'taxonomy'         => rtcl()->location,
					'name'             => 'l',
					'id'               => 'rtcl-location-search-' . $id,
					'class'            => 'rtcl-form-control',
					'selected'         => $location,
				] );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $can_search_by_category ) : ?>
			<!-- Category field -->
			<div class="rtcl-form-group">
				<label for="rtcl-category-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select a category', 'classified-listing' ); ?></label>
				<?php
				$category = 0;
				if ( $cat_slug = get_query_var( '__cat' ) ) {
					$cTerm = get_term_by( 'slug', $cat_slug, rtcl()->category );
					if ( $cTerm ) {
						$category = $cTerm->term_id;
					}
				}
				Functions::dropdown_terms( [
					'show_option_none'  => '-- ' . esc_html__( 'Select a category', 'classified-listing' ) . ' --',
					'option_none_value' => - 1,
					'taxonomy'          => rtcl()->category,
					'name'              => 'c',
					'id'                => 'rtcl-category-search-' . $id,
					'class'             => 'rtcl-form-control rtcl-category-search',
					'selected'          => $category,
				] );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $can_search_by_listing_types ) : ?>
			<div class="rtcl-form-group">
				<label for="rtcl-search-type-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select Type', 'classified-listing' ); ?></label>
				<select class="rtcl-form-control" name="filters[ad_type]" id="rtcl-search-type-<?php echo esc_attr( $id ); ?>">
					<option value=""><?php esc_html_e( 'Select type', 'classified-listing' ); ?></option>
					<?php
					$listing_types = Functions::get_listing_types();
					if ( ! empty( $listing_types ) ) {
						foreach ( $listing_types as $key => $listing_type ) {
							?>
							<option
								value="<?php echo esc_attr( $key ) ?>" <?php echo isset( $_GET['filters']['ad_type'] ) && trim( $_GET['filters']['ad_type'] ) == $key ? ' selected' : null ?>><?php echo esc_html( $listing_type ) ?></option>
							<?php
						}
					}
					?>
				</select>
			</div>
		<?php endif; ?>

		<?php if ( $can_search_by_price ) : ?>
			<!-- Price fields -->
			<div class="rtcl-form-group">
				<label for="rtcl-search-price-min"><?php esc_html_e( 'Price Range', 'classified-listing' ); ?></label>
				<div class="rtcl-row">
					<div class="rtcl-col-md-6 rtcl-col-xs-6">
						<label class="screen-reader-text" for="rtcl-search-price-min"><?php esc_html_e( 'Min Price', 'classified-listing' ); ?></label>
						<input type="text" id="rtcl-search-price-min" name="filters[price][min]" class="rtcl-form-control"
							   placeholder="<?php esc_attr_e( 'min', 'classified-listing' ); ?>"
							   value="<?php if ( isset( $_GET['filters']['price'] ) ) {
								   echo esc_attr( $_GET['filters']['price']['min'] );
							   } ?>">
					</div>
					<div class="rtcl-col-md-6 rtcl-col-xs-6">
						<label class="screen-reader-text" for="rtcl-search-price-max"><?php esc_html_e( 'Max Price', 'classified-listing' ); ?></label>
						<input type="text" id="rtcl-search-price-max" name="filters[price][max]" class="rtcl-form-control"
							   placeholder="<?php esc_attr_e( 'max', 'classified-listing' ); ?>"
							   value="<?php if ( isset( $_GET['filters']['price'] ) ) {
								   echo esc_attr( $_GET['filters']['price']['max'] );
							   } ?>">
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php do_action( 'rtcl_widget_search_vertical_form', $can_search_by_location, $can_search_by_category ) ?>
		<!-- Action buttons -->
		<button type="submit"
				class="rtcl-btn btn-primary"><?php esc_html_e( 'Search Listings', 'classified-listing' ); ?></button>
		<a href="<?php echo esc_url( get_permalink() ); ?>"
		   class="rtcl-btn rtcl-btn-danger my-3"><?php esc_html_e( 'Reset', 'classified-listing' ); ?></a>
	</form>
</div>