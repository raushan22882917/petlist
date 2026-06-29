<?php
/**
 * Login Form Information
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var Listing $listing
 * @var int     $title_limit
 * @var array   $hidden_fields
 * @var string  $selected_type
 * @var string  $title
 * @var string  $listing_pricing
 * @var string  $price_type
 * @var string  $price
 * @var string  $post_content
 * @var array   $tags
 * @var string  $editor
 * @var int     $category_id
 * @var int     $post_id
 * @var int     $description_limit
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Resources\Options;

?>
<div class="rtcl-post-details rtcl-post-section">
	<div class="rtcl-post-section-title">
		<h3>
			<i class="rtcl-icon rtcl-icon-picture"></i><?php esc_html_e( "Listing Information", "classified-listing" ); ?>
		</h3>
	</div>
	<div class="rtcl-form-group">
		<label for="rtcl-title" class="rtcl-field-label"><?php esc_html_e( 'Title', 'classified-listing' ); ?><span
				class="require-star">*</span></label>
		<input type="text"
			<?php echo $title_limit ? 'data-max-length="' . esc_attr( $title_limit ) . '" maxlength="' . esc_attr( $title_limit ) . '"' : ''; ?>
			   class="rtcl-form-control"
			   value="<?php echo esc_attr( $title ); ?>"
			   id="rtcl-title"
			   name="title"
			   required/>
		<?php
		if ( $title_limit ) {
			echo sprintf( '<div class="rtcl-hints">%s <span class="target-limit">%s</span></div>',
				esc_html( apply_filters( 'rtcl_listing_title_character_limit_hints', esc_html__( "Character limit", 'classified-listing' ) ) ),
				absint( $title_limit ) );
		}
		?>
	</div>

	<?php if ( ! in_array( 'price', $hidden_fields ) ):
		$listingPricingTypes = Options::get_listing_pricing_types();
		?>
		<div id="rtcl-pricing-wrap">
			<?php if ( ! Functions::is_pricing_disabled() ) { ?>
				<div class="rtcl-post-section-title">
					<h3><?php esc_html_e( "Pricing:", "classified-listing" ); ?></h3>
				</div>
				<div class="rtcl-form-group rtcl-checkbox-list rtcl-checkbox-inline rtcl-listing-pricing-types">
					<?php
					foreach ( $listingPricingTypes as $type_id => $type ) {
						?>
						<div class="rtcl-checkbox rtcl-listing-pricing-type">
							<input type="radio" name="_rtcl_listing_pricing"
								   id="_rtcl_listing_pricing_<?php echo esc_attr( $type_id ) ?>"
								<?php echo $listing_pricing === $type_id ? 'checked' : '' ?>
								   value="<?php echo esc_attr( $type_id ) ?>">
							<label for="_rtcl_listing_pricing_<?php echo esc_attr( $type_id ) ?>">
								<?php echo esc_html( $type ); ?>
							</label>
						</div>
						<?php
					}
					?>
				</div>
			<?php } ?>
			<div id="rtcl-pricing-items" class="<?php echo esc_attr( 'rtcl-pricing-' . $listing_pricing ) ?>">
				<?php if ( ! Functions::is_price_type_disabled() ): ?>
					<div class="rtcl-form-group rtcl-pricing-item">
						<label for="rtcl-price-type" class="rtcl-field-label">
							<?php esc_html_e( 'Price Type', 'classified-listing' ); ?>
							<span class="require-star">*</span>
						</label>
						<select class="rtcl-form-control" id="rtcl-price-type" name="price_type">
							<?php
							$price_types = Options::get_price_types();
							foreach ( $price_types as $key => $type ) {
								$slt = $price_type == $key ? " selected" : null;
								printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), esc_attr( $slt ), esc_html( $type ) );
							}
							?>
						</select>
					</div>
				<?php endif; ?>
				<?php do_action( 'rtcl_listing_form_price_items', $listing ); ?>
				<div id="rtcl-price-items"
					 class="rtcl-pricing-item<?php echo ! Functions::is_price_type_disabled() ? ' rtcl-price-type-' . esc_attr( $price_type ) : '' ?>">
					<div class="rtcl-form-group rtcl-price-item" id="rtcl-price-wrap">
						<div class="price-wrap">
							<label
								for="rtcl-price"
								class="rtcl-field-label"><?php echo sprintf( '<span class="price-label">%s [<span class="rtcl-currency-symbol">%s</span>]</span>',
									esc_html__( "Price", 'classified-listing' ),
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									apply_filters( 'rtcl_listing_price_currency_symbol', Functions::get_currency_symbol(), $listing )
								); ?>
								<span
									class="require-star">*</span></label>
							<input type="text"
								   class="rtcl-form-control rtcl-price"
								   value="<?php echo $listing ? esc_attr( $listing->get_price() ) : ''; ?>"
								   name="price"
								   id="rtcl-price"<?php echo esc_attr( ! $price_type || $price_type == 'fixed' ? " required" : '' ) ?>>
						</div>
						<div class="price-wrap rtcl-max-price rtcl-hide">
							<label
								for="rtcl-max-price" class="rtcl-field-label"><?php echo sprintf( '<span class="price-label">%s [<span class="rtcl-currency-symbol">%s</span>]</span>',
									esc_html__( "Max Price", 'classified-listing' ),
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									apply_filters( 'rtcl_listing_price_currency_symbol', Functions::get_currency_symbol(), $listing )
								); ?><span
									class="require-star">*</span></label>
							<input type="text"
								   class="rtcl-form-control rtcl-price"
								   value="<?php echo $listing ? esc_attr( $listing->get_max_price() ) : ''; ?>"
								   name="_rtcl_max_price"
								   id="rtcl-max-price"<?php echo esc_attr( ! $price_type || $price_type == 'fixed' ? " required" : '' ) ?>>
						</div>
					</div>
					<?php do_action( 'rtcl_listing_form_price_unit', $listing, $category_id ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div id="rtcl-custom-fields-list">
		<?php do_action( 'wp_ajax_rtcl_custom_fields_listings', $post_id, $category_id ); ?>
	</div>
	<?php if ( ! in_array( 'description', $hidden_fields ) ): ?>
		<div
			class="rtcl-form-group" <?php echo $description_limit ? 'data-max-length="' . esc_attr( $description_limit ) . '"' : ''; ?>>
			<label for="description"><?php esc_html_e( 'Description', 'classified-listing' ); ?></label>
			<?php

			if ( 'textarea' == $editor ) { ?>
				<textarea
					id="description"
					name="description"
					class="rtcl-form-control"
                        <?php echo $description_limit ? 'data-max-length="' . esc_attr( $description_limit ) . '" maxlength="' . esc_attr( $description_limit )
														. '"' : ''; ?>
                        rows="8"><?php Functions::print_html( $post_content ); ?></textarea>
				<?php
			} else {
				wp_editor(
					$post_content,
					'description',
					[
						'media_buttons' => false,
						'editor_height' => 200
					]
				);
			}


			if ( $description_limit ) {
				echo sprintf( '<div class="rtcl-hints">%s <span class="target-limit">%s</span></div>',
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					apply_filters( 'rtcl_listing_description_character_limit_hints', esc_html__( "Character limit ", 'classified-listing' ) ),
					esc_html( $description_limit )
				);
			}

			?>
		</div>
	<?php endif; ?>
	<?php if ( ! in_array( 'tags', $hidden_fields ) ): ?>
		<div class="rtcl-form-group">
			<label for="description" class="rtcl-field-label"><?php esc_html_e( 'Tags', 'classified-listing' ); ?></label>
			<div class="rtcl-tags-input-wrap">
				<div class="rtcl-tags-input">
					<?php
					$tags_data = [];
					if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
						foreach ( $tags as $tag ) {
							echo '<div><span class="rtcl-tag-term">' . esc_html( $tag->name ) . '</span><span class="remove">x</span></div>';
							$tags_data[] = esc_html( $tag->name );
						}
					}
					?>
					<input type="text" autocomplete="off" id="new-tag-rtcl_tag" class="rtcl-form-control"/>
				</div>
				<input type="hidden" id="rtcl_listing_tag" name="rtcl_listing_tag"
					   value="<?php echo esc_attr( implode( ',', $tags_data ) ); ?>"/>
			</div>
		</div>
	<?php endif; ?>
</div>