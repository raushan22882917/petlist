<?php

namespace Rtcl\Helpers;


class Helper {

	/**
	 * Helper singleton
	 *
	 * @var Helper
	 */
	private static $_instance = null;

	/**
	 * Helper data container
	 *
	 * @var array
	 */
	private $_data = [];

	/**
	 * Singleton, returns Helper instance
	 *
	 * @return Helper
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Returns adverts saved data
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( isset( $this->_data[ $key ] ) ) {
			return $this->_data[ $key ];
		} else {
			return $default;
		}
	}

	/**
	 * Sets adverts option
	 *
	 * @param string $key
	 * @param mixed $data
	 */
	public function set( $key, $data ) {
		$this->_data[ $key ] = $data;
	}

	public static function getNumericFormatters() {
		return apply_filters( 'rtcl_numeric_formatter_styles', [
			'none'                 => [
				'value' => '',
				'label' => 'None',
			],
			'comma_dot_style'      => [
				'value'    => 'comma_dot_style',
				'label'    => __( 'US Style with Decimal (EX: 123,456.00)', 'classified-listing' ),
				'settings' => [
					'decimal'   => '.',
					'separator' => ',',
					'precision' => 2,
					'symbol'    => '',
				],
			],
			'dot_comma_style_zero' => [
				'value'    => 'dot_comma_style_zero',
				'label'    => __( 'US Style without Decimal (Ex: 123,456,789)', 'classified-listing' ),
				'settings' => [
					'decimal'   => '.',
					'separator' => ',',
					'precision' => 0,
					'symbol'    => '',
				],
			],
			'dot_comma_style'      => [
				'value'    => 'dot_comma_style',
				'label'    => __( 'EU Style with Decimal (Ex: 123.456,00)', 'classified-listing' ),
				'settings' => [
					'decimal'   => ',',
					'separator' => '.',
					'precision' => 2,
					'symbol'    => '',
				],
			],
			'comma_dot_style_zero' => [
				'value'    => 'comma_dot_style_zero',
				'label'    => __( 'EU Style without Decimal (EX: 123.456.789)', 'classified-listing' ),
				'settings' => [
					'decimal'   => ',',
					'separator' => '.',
					'precision' => 0,
					'symbol'    => '',
				],
			],
		] );
	}

	public static function fileUploadLocations() {
		$locations = [
			[
				'value' => 'default',
				'label' => __( 'Classified Listing Default', 'classified-listing' ),
			],
			[
				'value' => 'wp_media',
				'label' => __( 'Media Library', 'classified-listing' ),
			],
		];

		return apply_filters( 'rtcl_file_upload_location', $locations );
	}
}