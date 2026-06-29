<?php

namespace Rtcl\Services\FormBuilder;

class SettingFields {
	public static function get() {
		$fields = [
			
		];

		return apply_filters( 'rtcl/fb/settings_fields', $fields );
	}

}
