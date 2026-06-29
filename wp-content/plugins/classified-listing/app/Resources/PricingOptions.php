<?php

namespace Rtcl\Resources;


use Rtcl\Helpers\Functions;

class PricingOptions {

	static function rtcl_pricing_option( $post ) {
		$description = get_post_meta( $post->ID, "description", true );
		$price       = esc_attr( get_post_meta( $post->ID, "price", true ) );
		$visible     = get_post_meta( $post->ID, "visible", true );

		wp_nonce_field( rtcl()->nonceText, rtcl()->nonceId );

		$promotion_html = '';
		$promotions     = Options::get_listing_promotions();
		foreach ( $promotions as $promo_id => $promotion ) {
			$promo_value    = get_post_meta( $post->ID, $promo_id, true ) ? 1 : 0;
			$promotion_html .= sprintf( '<div class="form-check">
                                    <input class="form-check-input" name="%1$s" type="checkbox"
                                           value="1" %2$s id="allowed_featured_%1$s">
                                    <label class="rtcl-form-check-label" for="allowed_featured_%1$s">%3$s</label>
                                </div>', esc_attr( $promo_id ), checked( $promo_value, 1, false ), $promotion );
		}

		$data = [
			'price'       => [
				'id'         => 'rtcl-pricing-price',
				'type'       => 'text',
				'name'       => 'price',
				'label'      => sprintf( '%s [%s]', __( "Price", 'classified-listing' ), Functions::get_currency_symbol( Functions::get_order_currency() ) ),
				'attributes' => [ 'required' => true ],
				'value'      => $price
			],
			'visible'     => [
				'id'          => 'visible',
				'name'        => 'visible',
				'type'        => 'number',
				'label'       => __( "Validate until", "classified-listing" ),
				'attributes'  => [ 'required' => true ],
				'value'       => $visible,
				'description' => __( "Number of days the pricing will be validate.", "classified-listing" ),
			],
			'allowed'     => sprintf( '<div class="rtcl-row rtcl-form-group">
                            <label class="rtcl-col-2 rtcl-field-label"
                                   for="pricing-featured">%s</label>
                            <div class="rtcl-col-10"><div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked disabled id="allowed_pay_per_ad">
                                    <label class="rtcl-form-check-label" for="allowed_pay_per_ad">%s</label>
                                </div>%s</div>
                        </div>',
				__( "Allowed", 'classified-listing' ),
				__( "Pay per ad", 'classified-listing' ),
				$promotion_html,
			),
			'description' => [
				'id'          => 'pricing-description',
				'name'        => 'description',
				'type'        => 'textarea',
				'label'       => __( "Description", "classified-listing" ),
				'value'       => $description,
				'class'       => [ 'rtcl-form-control' ],
				'description' => __( "HTML is allowed :)", "classified-listing" ),
			],
		];

		$fields = apply_filters( 'rtcl_pricing_admin_options', $data, $post );

		self::render_fields( $fields );
	}

	private static function render_fields( $fields ) {
		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $k => $field ) {
			if ( is_string( $field ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $field;
			}
			if ( ! is_array( $field ) ) {
				continue;
			}
			$defaults = self::get_placeholder_data();
			$field    = wp_parse_args( $field, $defaults );
			$type     = self::get_field_type( $field );

			if ( method_exists( __CLASS__, 'generate_' . $type . '_html' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::{'generate_' . $type . '_html'}( $field, $k );
			} else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::generate_text_html( $field, $k );
			}
		}

	}

	private static function get_field_type( $field ) {
		return empty( $field['type'] ) ? 'text' : $field['type'];
	}


	private static function generate_text_html( $field, $key ) {
		$wrapperClass   = implode( ' ', $field['wrapper_class'] );
		$labelClass     = implode( ' ', $field['label_class'] );
		$fieldWrapClass = implode( ' ', $field['field_wrap_class'] );
		$class          = implode( ' ', $field['class'] );
		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrapperClass ); ?>">
			<label class="<?php echo esc_attr( $labelClass ); ?>"
				   for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
			<div class="<?php echo esc_attr( $fieldWrapClass ); ?>">
				<input
					type="number"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					name="<?php echo esc_attr( $field['name'] ); ?>"
					value="<?php echo esc_attr( $field['value'] ); ?>"
					class="<?php echo esc_attr( $class ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo self::get_attribute_html( $field ); ?>
				/>
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::get_description_html( $field ); ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}


	private static function generate_checkbox_html( $field, $key ) {
		$wrapperClass   = implode( ' ', $field['wrapper_class'] );
		$labelClass     = implode( ' ', $field['label_class'] );
		$fieldWrapClass = implode( ' ', $field['field_wrap_class'] );
		$class          = implode( ' ', $field['class'] );
		$field['value'] = is_array( $field['value'] ) ? $field['value'] : [];
		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrapperClass ); ?>">
			<label class="<?php echo esc_attr( $labelClass ); ?>"
				   for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
			<div class="<?php echo esc_attr( $fieldWrapClass ); ?>">
				<div class="checkbox-wrap">
					<?php foreach ( (array) $field['options'] as $option_key => $option_value ) : ?>
						<div class="form-check">
							<input
								class="form-check-input"
								id="<?php echo esc_attr( $field['id'] . '-' . $option_key ); ?>"
								name="<?php echo esc_attr( $field['name'] ); ?>[]"
								type="checkbox"
								value="<?php echo esc_attr( $option_key ); ?>"
								<?php checked( in_array( $option_key, $field['value'] ) ); ?>
							>
							<label class="rtcl-form-check-label"
								   for="<?php echo esc_attr( $field['id'] . '-' . $option_key ); ?>"><?php echo esc_html( $option_value ); ?></label>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::get_description_html( $field ); ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	private static function generate_textarea_html( $field, $key ) {
		$wrapperClass   = implode( ' ', $field['wrapper_class'] );
		$labelClass     = implode( ' ', $field['label_class'] );
		$fieldWrapClass = implode( ' ', $field['field_wrap_class'] );
		$class          = implode( ' ', $field['class'] );
		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrapperClass ); ?>">
			<label class="<?php echo esc_attr( $labelClass ); ?>"
				   for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
			<div class="<?php echo esc_attr( $fieldWrapClass ); ?>">
				<textarea
					rows="5" cols="20"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					name="<?php echo esc_attr( $field['name'] ); ?>"
					class="<?php echo esc_attr( $class ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo self::get_attribute_html( $field ); ?>
				><?php echo esc_attr( $field['value'] ); ?></textarea>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::get_description_html( $field ); ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	private static function generate_number_html( $field, $key ) {
		$wrapperClass   = implode( ' ', $field['wrapper_class'] );
		$labelClass     = implode( ' ', $field['label_class'] );
		$fieldWrapClass = implode( ' ', $field['field_wrap_class'] );
		$class          = implode( ' ', $field['class'] );
		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrapperClass ); ?>">
			<label class="<?php echo esc_attr( $labelClass ); ?>"
				   for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['label'] ); ?></label>
			<div class="<?php echo esc_attr( $fieldWrapClass ); ?>">
				<input
					type="text"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					name="<?php echo esc_attr( $field['name'] ); ?>"
					value="<?php echo esc_attr( $field['value'] ); ?>"
					class="<?php echo esc_attr( $class ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo self::get_attribute_html( $field ); ?>
				/>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::get_description_html( $field ); ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	private static function get_description_html( $field ) {
		$description = ! empty( $field['description'] ) ? $field['description'] : '';

		return $description ? '<div class="rtcl-hints">' . wp_kses_post( $description ) . '</div>' . "\n" : '';
	}

	private static function get_attribute_html( $field ) {
		$attributes = [];

		if ( ! empty( $field['attr'] ) && is_array( $field['attr'] ) ) {
			foreach ( $field['attr'] as $attribute => $attribute_value ) {
				$attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		return implode( ' ', $attributes );
	}


	private static function get_placeholder_data() {
		return [
			'label'            => '',
			'id'               => '',
			'disabled'         => false,
			'label_class'      => [ 'rtcl-col-2 rtcl-field-label' ],
			'field_wrap_class' => [ 'rtcl-col-10' ],
			'class'            => [ 'rtcl-form-control' ],
			'css'              => '',
			'placeholder'      => '',
			'blank'            => true,
			'blank_text'       => __( 'Select one', 'classified-listing' ),
			'blank_value'      => '',
			'type'             => 'text',
			'description'      => '',
			'attributes'       => [],
			'wrapper_class'    => [ 'rtcl-row', 'rtcl-form-group' ],
			'options'          => [],
			'select_buttons'   => false,
			'dependency'       => '',
		];
	}

}
