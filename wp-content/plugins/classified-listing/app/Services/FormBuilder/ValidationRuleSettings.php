<?php

namespace Rtcl\Services\FormBuilder;

class ValidationRuleSettings {

	public static function get() {

		$fileTypeOptions = [
			[
				'label' => __( 'Images (jpg, jpeg, webp, png, gif, bmp, heic, heif)', 'classified-listing' ),
				'value' => 'jpg|jpeg|webp|png|gif|bmp|heic|heif',
			],
			[
				'label' => __( 'Audio (mp3, wav, ogg, oga, wma, mka, m4a, ra, mid, midi)', 'classified-listing' ),
				'value' => 'mp3|wav|ogg|oga|wma|mka|m4a|ra|mid|midi|mpga',
			],
			[
				'label' => __( 'Video (avi, divx, flv, mov, ogv, mkv, mp4, m4v, divx, mpg, mpeg, mpe)', 'classified-listing' ),
				'value' => 'avi|divx|flv|mov|ogv|mkv|mp4|m4v|divx|mpg|mpeg|mpe|video/quicktime|qt',
			],
			[
				'label' => __( 'PDF (pdf)', 'classified-listing' ),
				'value' => 'pdf',
			],
			[
				'label' => __( 'Docs (doc, ppt, pps, xls, mdb, docx, xlsx, pptx, odt, odp, ods, odg, odc, odb, odf, rtf, txt)', 'classified-listing' ),
				'value' => 'doc|ppt|pps|xls|mdb|docx|xlsx|pptx|odt|odp|ods|odg|odc|odb|odf|rtf|txt',
			],
			[
				'label' => __( 'Zip Archives (zip, gz, gzip, rar, 7z)', 'classified-listing' ),
				'value' => 'zip|gz|gzip|rar|7z',
			],
			[
				'label' => __( 'CSV (csv)', 'classified-listing' ),
				'value' => 'csv',
			],
		];

		$fileTypeOptions = apply_filters( 'rtcl_file_type_options', $fileTypeOptions );

		$validation_rule_settings = [
			'required'            => [
				'template'  => 'inputRadio',
				'label'     => __( 'Required', 'classified-listing' ),
				'help_text' => __( 'Select whether this field is a required field for the form or not.', 'classified-listing' ),
				'options'   => [
					[
						'value' => true,
						'label' => __( 'Yes', 'classified-listing' ),
					],
					[
						'value' => false,
						'label' => __( 'No', 'classified-listing' ),
					],
				],
			],
			'valid_phone_number'  => [
				'template'  => 'inputRadio',
				'label'     => __( 'Validate Phone Number', 'classified-listing' ),
				'help_text' => __( 'Select whether the phone number should be validated or not.', 'classified-listing' ),
				'options'   => [
					[
						'value' => true,
						'label' => __( 'Yes', 'classified-listing' ),
					],
					[
						'value' => false,
						'label' => __( 'No', 'classified-listing' ),
					],
				],
			],
			'email'               => [
				'template'  => 'inputRadio',
				'label'     => __( 'Validate Email', 'classified-listing' ),
				'help_text' => __( 'Select whether to validate this field as email or not', 'classified-listing' ),
				'options'   => [
					[
						'value' => true,
						'label' => __( 'Yes', 'classified-listing' ),
					],
					[
						'value' => false,
						'label' => __( 'No', 'classified-listing' ),
					],
				],
			],
			'url'                 => [
				'template'  => 'inputRadio',
				'label'     => __( 'Validate URL', 'classified-listing' ),
				'help_text' => __( 'Select whether to validate this field as URL or not', 'classified-listing' ),
				'options'   => [
					[
						'value' => true,
						'label' => __( 'Yes', 'classified-listing' ),
					],
					[
						'value' => false,
						'label' => __( 'No', 'classified-listing' ),
					],
				],
			],
			'min'                 => [
				'template'  => 'inputText',
				'type'      => 'number',
				'label'     => __( 'Min Value', 'classified-listing' ),
				'help_text' => __( 'Minimum value for the input field.', 'classified-listing' ),
			],
			'digits'              => [
				'template'  => 'inputText',
				'type'      => 'number',
				'label'     => __( 'Digits', 'classified-listing' ),
				'help_text' => __( 'Number of digits for the input field.', 'classified-listing' ),
			],
			'max'                 => [
				'template'  => 'inputText',
				'type'      => 'number',
				'label'     => __( 'Max Value', 'classified-listing' ),
				'help_text' => __( 'Maximum value for the input field.', 'classified-listing' ),
			],
			'max_file_size'       => [
				'template'  => 'maxFileSize',
				'label'     => __( 'Max File Size (MB)', 'classified-listing' ),
				'help_text' => __( 'Max file size (MB) user can upload.', 'classified-listing' ),
			],
			'max_file_count'      => [
				'template'  => 'inputText',
				'type'      => 'number',
				'label'     => __( 'Max Files Count', 'classified-listing' ),
				'help_text' => __( 'Maximum user file upload number.', 'classified-listing' ),
			],
			'allowed_file_types'  => [
				'template'    => 'inputCheckbox',
				'orientation' => 'vertical',
				'label'       => __( 'Allowed Files', 'classified-listing' ),
				'help_text'   => __( 'Allowed Files', 'classified-listing' ),
				'fileTypes'   => [
					[
						'title' => __( 'Images', 'classified-listing' ),
						'types' => [ 'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'heic', 'heif' ],
					],
					[
						'title' => __( 'Audio', 'classified-listing' ),
						'types' => [ 'mp3', 'wav', 'ogg', 'oga', 'wma', 'mka', 'm4a', 'ra', 'mid', 'midi' ],
					],
					[
						'title' => __( 'Video', 'classified-listing' ),
						'types' => [
							'avi',
							'divx',
							'flv',
							'mov',
							'ogv',
							'mkv',
							'mp4',
							'm4v',
							'divx',
							'mpg',
							'mpeg',
							'mpe',
						],
					],
					[
						'title' => __( 'PDF', 'classified-listing' ),
						'types' => [ 'pdf' ],
					],
					[
						'title' => __( 'Docs', 'classified-listing' ),
						'types' => [
							'doc',
							'ppt',
							'pps',
							'xls',
							'mdb',
							'docx',
							'xlsx',
							'pptx',
							'odt',
							'odp',
							'ods',
							'odg',
							'odc',
							'odb',
							'odf',
							'rtf',
							'txt'
						],
					],
					[
						'title' => __( 'Zip Archives', 'classified-listing' ),
						'types' => [ 'zip', 'gz', 'gzip', 'rar', '7z' ],
					],
					[
						'title' => __( 'CSV', 'classified-listing' ),
						'types' => [ 'csv' ],
					],
				],
				'options'     => $fileTypeOptions,
			],
			'allowed_image_types' => [
				'template'  => 'inputCheckbox',
				'label'     => __( 'Allowed Images', 'classified-listing' ),
				'help_text' => __( 'Allowed Images', 'classified-listing' ),
				'options'   => [
					[
						'label' => __( 'JPEG', 'classified-listing' ),
						'value' => 'jpeg',
					],
					[
						'label' => __( 'JPG', 'classified-listing' ),
						'value' => 'jpg',
					],
					[
						'label' => __( 'PNG', 'classified-listing' ),
						'value' => 'png',
					],
					[
						'label' => __( 'WEBP', 'classified-listing' ),
						'value' => 'webp',
					],
					[
						'label' => __( 'GIF', 'classified-listing' ),
						'value' => 'gif',
					],
					[
						'label' => __( 'HEIC', 'classified-listing' ),
						'value' => 'heic',
					],
					[
						'label' => __( 'HEIF', 'classified-listing' ),
						'value' => 'heif',
					]
				],
			],
		];

		return apply_filters( 'rtcl_editor_validation_rule_settings', $validation_rule_settings );

	}
}