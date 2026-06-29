<?php

namespace Rtcl\Services\FormBuilder\Components;

class DateTime {

	public function getAvailableTimeFormat() {
		return [
			[
				'label' => '21:55',
				'value' => 'H:i',
			],
			[
				'label' => '08:55 PM',
				'value' => 'h:i A'
			],
			[
				'label' => '08:55 am',
				'value' => 'h:i a'
			]
		];
	}

	/**
	 * @return array
	 */
	public function getAvailableDateFormats() {
		$dateFormats = apply_filters( 'rtcl/available_date_formats', [
			'm/d/Y'       => 'm/d/Y - (Ex: 04/28/2025)', // USA
			'd/m/Y'       => 'd/m/Y - (Ex: 28/04/2025)', // Canada, UK
			'd.m.Y'       => 'd.m.Y - (Ex: 28.04.2025)', // Germany
			'n/j/y'       => 'n/j/y - (Ex: 4/28/25)',
			'm/d/y'       => 'm/d/y - (Ex: 04/28/25)',
			'M/d/Y'       => 'M/d/Y - (Ex: Apr/28/2025)',
			'y/m/d'       => 'y/m/d - (Ex: 25/04/28)',
			'Y-m-d'       => 'Y-m-d - (Ex: 2025-04-28)',
			'd-M-y'       => 'd-M-y - (Ex: 28-Apr-25)',
			'F j, Y'      => 'F j, Y - (November 12, 2025)',
			'j F, Y'      => 'j F, Y - (12 November, 2025)',
			'j F Y'       => 'j F Y - (12 November 2025)',
			'm/d/Y h:i A' => 'm/d/Y h:i A - (Ex: 04/28/2025 08:55 PM)', // USA
			'm/d/Y H:i'   => 'm/d/Y H:i - (Ex: 04/28/2025 20:55)', // USA
			'd/m/Y h:i A' => 'd/m/Y h:i A - (Ex: 28/04/2025 08:55 PM)', // Canada, UK
			'd/m/Y H:i'   => 'd/m/Y H:i - (Ex: 28/04/2025 20:55)', // Canada, UK
			'd.m.Y h:i A' => 'd.m.Y h:i A - (Ex: 28.04.2025 08:55 PM)', // Germany
			'd.m.Y H:i'   => 'd.m.Y H:i - (Ex: 28.04.2025 20:55)', // Germany
			'h:i A'       => 'h:i A (Only Time Ex: 08:55 PM)',
			'H:i'         => 'H:i (Only Time Ex: 20:55)',
		] );

		$formatted = [];
		foreach ( $dateFormats as $format => $label ) {
			$formatted[] = [
				'label' => $label,
				'value' => $format,
			];
		}

		return $formatted;
	}

	public function getDateFormatConfigJSON( $settings, $form ) {
		$dateFormat = ArrayHelper::get( $settings, 'date_format' );

		if ( ! $dateFormat ) {
			$dateFormat = 'm/d/Y';
		}

		$hasTime = $this->hasTime( $dateFormat );
		$time24  = false;

		if ( $hasTime && false !== strpos( $dateFormat, 'H' ) ) {
			$time24 = true;
		}

		$config = apply_filters( 'rtcl/frontend_date_format', [
			'dateFormat'    => $dateFormat,
			'enableTime'    => $hasTime,
			'noCalendar'    => ! $this->hasDate( $dateFormat ),
			'disableMobile' => true,
			'time_24hr'     => $time24,
		], $settings, $form );

		return wp_json_encode( $config, JSON_FORCE_OBJECT );
	}

	public function getCustomConfig( $settings ) {
		$customConfigObject = trim( ArrayHelper::get( $settings, 'date_config' ) );

		if ( ! $customConfigObject || '{' != substr( $customConfigObject, 0, 1 ) || '}' != substr( $customConfigObject, - 1 ) ) {
			$customConfigObject = '{}';
		}

		return $customConfigObject;
	}


	private function hasTime( $string ) {
		$timeStrings = [ 'H', 'h', 'G', 'i', 'S', 's', 'K' ];
		foreach ( $timeStrings as $timeString ) {
			if ( false != strpos( $string, $timeString ) ) {
				return true;
			}
		}

		return false;
	}

	private function hasDate( $string ) {
		$dateStrings = [ 'd', 'D', 'l', 'j', 'J', 'w', 'W', 'F', 'm', 'n', 'M', 'U', 'Y', 'y', 'Z' ];
		foreach ( $dateStrings as $dateString ) {
			if ( false != strpos( $string, $dateString ) ) {
				return 'true';
			}
		}

		return false;
	}
}
