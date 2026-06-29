<?php

namespace Rtcl\Helpers;

class Tax {
	
	public static function get_tax_options() {
		return Functions::get_tax_options();
	}

	public static function set_tax_options( $settingsKey, $rawOptions, $fields ) {
		if ( $settingsKey == 'rtcl_tax_rates' && is_array( $rawOptions ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'rtcl_tax_rates';
			$existingTaxRates = [];
			$newTaxRates = [];
			if ( !empty( $rawOptions ) ) {
				foreach ( $rawOptions as $rawOption ) {
					if ( is_array( $rawOption ) ) {
						if ( !empty( $rawOption['tax_rate_id'] ) ) {
							$taxRat = [];
							if ( strpos( $rawOption['tax_rate_id'], 'tax_' ) === 0 ) {

							} else {
								$taxId = absint( $rawOption['tax_rate_id'] );
								if ( $taxId ) {
									$taxRat['tax_rate_id'] = $taxId;
								} else {
									continue;
								}
							}
							$taxRat['country'] = isset( $rawOption['country'] ) && array_key_exists( $rawOption['country'], rtcl()->countries->get_countries() ) ? $rawOption['country'] : '*';
							$taxRat['country_state'] = isset( $rawOption['country_state'] ) ? sanitize_text_field( wp_unslash( $rawOption['country_state'] ) ) : '*';
							$taxRat['country_city'] = isset( $rawOption['country_city'] ) ? sanitize_text_field( wp_unslash( $rawOption['country_city'] ) ) : '*';
							$taxRat['tax_rate'] = isset( $rawOption['tax_rate'] ) ? floatval( $rawOption['tax_rate'] ) : '0';
							$taxRat['tax_rate_name'] = isset( $rawOption['tax_rate_name'] ) ? sanitize_text_field( wp_unslash( $rawOption['tax_rate_name'] ) ) : 'Tax';
							$taxRat['tax_rate_priority'] = isset( $rawOption['tax_rate_priority'] ) ? absint( $rawOption['tax_rate_priority'] ) : 0;
							if ( !empty( $taxRat ) ) {
								if ( empty( $taxRat['tax_rate_id'] ) ) {
									$newTaxRates[] = $taxRat;
								} else {
									$existingTaxRates[] = $taxRat;
								}
							}
						}
					}
				}
				$existingIds = $wpdb->get_col( "SELECT tax_rate_id FROM $table_name" );
				if ( !empty( $existingTaxRates ) ) {
					foreach ( $existingTaxRates as $taxRate ) {
						$id = $taxRate['tax_rate_id'];
						$key = array_search( $id, $existingIds );
						if ( $key !== false ) {
							unset( $existingIds[$key] );
							$existingIds = array_values( $existingIds );
							unset( $taxRate['tax_rate_id'] );
							$result = $wpdb->update(
								$table_name,
								$taxRate,
								[ 'tax_rate_id' => $id ]
							);
							if ( false === $result ) {
								error_log( print_r( $wpdb->last_error, true ) . "\n", 3, ABSPATH . "wp-content/logs.log" );
							}
						}
					}
				}
				if ( !empty( $existingIds ) ) {
					foreach ( $existingIds as $existingId ) {
						$wpdb->delete(
							$table_name,
							[ 'tax_rate_id' => $existingId ],
							[ '%d' ]
						);
					}
				}
				if ( !empty( $newTaxRates ) ) {
					foreach ( $newTaxRates as $newTaxRate ) {
						$result = $wpdb->insert(
							$table_name,
							$newTaxRate
						);
						if ( false === $result ) {
							error_log( print_r( $wpdb->last_error, true ) . "\n", 3, ABSPATH . "wp-content/logs.log" );
						}
					}

				}
			}
			if ( empty( $existingTaxRates ) && empty( $newTaxRates ) ) {
				$wpdb->query( "TRUNCATE TABLE $table_name" );
			}
		}
	}


	public static function fields() {
		return [
			'field_title_tax'     => [
				'title' => __( 'Tax Settings', 'classified-listing' ),
				'type'  => 'section',
			],
			'enable_tax'          => [
				'title'   => __( 'Enable Tax', 'classified-listing' ),
				'type'    => 'switch',
				'default' => 'no',
			],
			'enable_multiple_tax' => [
				'title'       => __( 'Enable Multiple Tax', 'classified-listing' ),
				'type'        => 'switch',
				'default'     => 'no',
				'description' => __( 'Apply multiple tax option for same area.', 'classified-listing' ),
			],
			'field_title_rate'    => [
				'title' => __( 'Tax Rate Settings', 'classified-listing' ),
				'type'  => 'section',
			],
			'rtcl_tax_rates'      => [
				'type'        => 'tax_rate',
				'label'       => __( 'Tax Rates', 'classified-listing' ),
				'is_external' => true,
				'get_data_fn' => [ Tax::class, 'get_tax_options' ],
				'set_data_fn' => [ Tax::class, 'set_tax_options' ]
			],
		];
	}

}