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

$active_count = $can_search_by_location + $can_search_by_category + $can_search_by_listing_types + $can_search_by_price;

$orderby = strtolower( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_orderby', 'name' ) );
$order   = strtoupper( Functions::get_option_item( 'rtcl_archive_listing_settings', 'taxonomy_order', 'DESC' ) );
?>
<div class="rtcl rtcl-search rtcl-search-inline">
	<form action="<?php echo esc_url( Functions::get_filter_form_url() ) ?>"
		  class="rtcl-widget-search-form rtcl-widget-search-inline active-<?php echo esc_attr( $active_count ); ?>">
		<?php if ( $radius_search ):
			$rs_data = Options::radius_search_options();
			?>
			<div class="rtcl-form-group ws-item ws-location rtcl-col-sm-6 rtcl-col-12">
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
								in_array( $rs_data['units'], [
									'km',
									'kilometers',
								] ) ? __( 'km', 'classified-listing' ) : __( 'Miles', 'classified-listing' ),
							),
							[
								'span' => [
									'class' => [],
								],
							],
						) ?>
					</div>
					<input type="range" class="rtcl-form-control-range rtcl-range-slider-input" name="distance" min="0"
						   max="<?php echo absint( $rs_data['max_distance'] ) ?>"
						   value="<?php echo absint( isset( $_GET['distance'] ) ? $_GET['distance'] : $rs_data['default_distance'] ) ?>">
				</div>
			</div>
		<?php endif ?>
		<?php if ( 'local' === Functions::location_type() && $can_search_by_location ) : ?>
			<div class="rtcl-form-group ws-item ws-location rtcl-col-sm-6 rtcl-col-12">
				<label class="screen-reader-text" for="rtcl-location-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select a Location', 'classified-listing' ); ?></label>
				<?php
				$args = [
					'show_option_none'  => Text::get_select_location_text(),
					'taxonomy'          => rtcl()->location,
					'name'              => 'rtcl_location',
					'id'                => 'rtcl-location-search-' . $id,
					'class'             => 'rtcl-form-control rtcl-location-search',
					'selected'          => get_query_var( '__loc' ),
					'hierarchical'      => true,
					'option_none_value' => '',
					'value_field'       => 'slug',
					'orderby'           => $orderby,
					'order'             => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
					'depth'             => 3,
					'show_count'        => false,
					'hide_empty'        => false,
				];
				if ( '_rtcl_order' === $orderby ) {
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_rtcl_order';// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key	
				}
				wp_dropdown_categories( $args );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $can_search_by_category ) : ?>
			<div class="rtcl-form-group ws-item ws-category rtcl-col-sm-6 rtcl-col-12">
				<label class="screen-reader-text" for="rtcl-category-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select a Category', 'classified-listing' ); ?></label>
				<?php
				$args = [
					'show_option_none'  => Text::get_select_category_text(),
					'taxonomy'          => rtcl()->category,
					'name'              => 'rtcl_category',
					'id'                => 'rtcl-category-search-' . $id,
					'class'             => 'rtcl-form-control rtcl-category-search',
					'selected'          => get_query_var( '__cat' ),
					'hierarchical'      => true,
					'value_field'       => 'slug',
					'option_none_value' => '',
					'orderby'           => $orderby,
					'order'             => ( 'DESC' === $order ) ? 'DESC' : 'ASC',
					'depth'             => 2,
					'show_count'        => false,
					'hide_empty'        => false,
				];
				if ( '_rtcl_order' === $orderby ) {
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_rtcl_order';// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key	
				}
				wp_dropdown_categories( $args );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $can_search_by_listing_types ) : ?>
			<div class="rtcl-form-group ws-item ws-type col-sm-6 col-12">
				<label class="screen-reader-text" for="rtcl-type-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select Type', 'classified-listing' ); ?></label>
				<select class="rtcl-form-control" name="filters[ad_type]" id="rtcl-type-search-<?php echo esc_attr( $id ); ?>">
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
			<div class="rtcl-form-group ws-item ws-price col-sm-6  col-12">
				<div class="rtcl-row">
					<div class="rtcl-col-md-6 rtcl-col-xs-6">
						<label class="screen-reader-text" for="rtcl-search-price-min"><?php esc_html_e( 'Min Price', 'classified-listing' ); ?></label>
						<input type="text" name="filters[price][min]" class="rtcl-form-control" id="rtcl-search-price-min"
							   placeholder="<?php esc_attr_e( 'min', 'classified-listing' ); ?>"
							   value="<?php if ( isset( $_GET['filters']['price'] ) ) {
								   echo esc_attr( $_GET['filters']['price']['min'] );
							   } ?>">
					</div>
					<div class="rtcl-col-md-6 rtcl-col-xs-6">
						<label class="screen-reader-text" for="rtcl-search-price-max"><?php esc_html_e( 'Max Price', 'classified-listing' ); ?></label>
						<input type="text" name="filters[price][max]" class="form-control" id="rtcl-search-price-max"
							   placeholder="<?php esc_attr_e( 'max', 'classified-listing' ); ?>"
							   value="<?php if ( isset( $_GET['filters']['price'] ) ) {
								   echo esc_attr( $_GET['filters']['price']['max'] );
							   } ?>">
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="rtcl-form-group ws-item ws-text col-sm-6">
			<div class="rt-autocomplete-wrapper">
				<label class="screen-reader-text" for="rtcl-keyword-search-<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Search', 'classified-listing' ); ?></label>
				<input type="text" id="rtcl-keyword-search-<?php echo esc_attr( $id ); ?>" name="q" class="rtcl-autocomplete form-control"
					   placeholder="<?php esc_attr_e( 'Enter your keyword here ...', 'classified-listing' ); ?>"
					   value="<?php if ( isset( $_GET['q'] ) ) {
						   echo esc_attr( Functions::clean( wp_unslash( $_GET['q'] ) ) );
					   } ?>">
			</div>
		</div>

		<div class="rtcl-form-group ws-item ws-button">
			<div class="rtcl-action-buttons text-right">
				<button type="submit"
						class="rtcl-btn btn-primary"><?php esc_html_e( 'Search', 'classified-listing' ); ?></button>
			</div>
		</div>
		<?php do_action( 'rtcl_widget_search_inline_form', $can_search_by_location, $can_search_by_category ) ?>
	</form>
</div>