<?php

namespace Rtcl\Resources;

class FormPreDefined {

	public static function blank(): array {
		return [
			'title'  => __( 'Blank Form', 'classified-listing' ),
			'status' => 'publish',
			'data'   => [
				'fields'       => [],
				'submitButton' => [
					"element"        => "button",
					"attributes"     => [
						"type"  => "submit",
						"class" => ""
					],
					"settings"       => [
						"align"            => "left",
						"button_style"     => "default",
						"container_class"  => "",
						"help_message"     => "",
						"background_color" => "#409EFF",
						"button_size"      => "md",
						"color"            => "#ffffff",
						"button_ui"        => [
							"type"    => "default",
							"text"    => "Submit Form",
							"img_url" => ""
						]
					],
					"editor_options" => [
						"title" => "Submit Button"
					]
				]
			]
		];

	}
}