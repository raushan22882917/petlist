<?php

namespace Rtcl\Services\FormBuilder;

use Rtcl\Helpers\Functions;

class AvailableFields {
	public static function get() {
		$fields = [
			'listing_type'        => [
				'element'         => 'listing_type',
				'preset'          => 1,
				'name'            => 'listing_type',
				'default_value'   => '',
				'container_class' => '',
				'placeholder'     => __( 'Select a type', 'classified-listing' ),
				'label'           => __( 'Listing type', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'validation'      => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Listing type', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-header',
					'template'   => 'inputText'
				]
			],
			'title'               => [
				'element'         => 'title',
				'preset'          => 1,
				'type'            => 'text',
				'name'            => 'title',
				'default_value'   => '',
				'placeholder'     => __( 'Listing Title', 'classified-listing' ),
				'container_class' => '',
				'label'           => __( 'Title', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'validation'      => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'min'      => [
						'value'   => 2,
						'message' => __( 'Minimum {value} character', 'classified-listing' ),
					],
					'max'      => [
						'value'   => 255,
						'message' => __( 'Maximum {value} character', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Title', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-header',
					'template'   => 'inputText',
				],
			],
			'description'         => [
				'element'         => 'description',
				'preset'          => 1,
				'editor_type'     => 'textarea',
				'name'            => 'description',
				'placeholder'     => '',
				'rows'            => 5,
				'cols'            => '',
				'container_class' => '',
				'label'           => __( 'Description', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'min'      => [
						'value'   => '',
						'message' => __( 'Minimum {value} character', 'classified-listing' ),
					],
					'max'      => [
						'value'   => '',
						'message' => __( 'Maximum {value} character', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Description', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-align-left',
					'template'   => 'inputTextarea',
				],
			],
			'excerpt'             => [
				'element'         => 'excerpt',
				'preset'          => 1,
				'name'            => 'excerpt',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Excerpt', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'min'      => [
						'value'   => '',
						'message' => __( 'Minimum {value} character', 'classified-listing' ),
					],
					'max'      => [
						'value'   => '',
						'message' => __( 'Maximum {value} character', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Excerpt', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-align-left',
					'template'   => 'inputTextarea',
				]
			],
			'pricing'             => [
				'element'            => 'pricing',
				'preset'             => 1,
				'name'               => 'pricing',
				'options'            => [ 'pricing_type', 'price_type' ],
				'class'              => '',
				'placeholder'        => '',
				'container_class'    => '',
				'pricing_type'       => 'price',
				'price_type'         => 'fixed',
				'label'              => __( 'Pricing', 'classified-listing' ),
				'label_placement'    => '',
				'pricing_type_label' => __( 'Pricing Type', 'classified-listing' ),
				'price_type_label'   => __( 'Price Type', 'classified-listing' ),
				'price_unit_label'   => __( 'Price Unit', 'classified-listing' ),
				'price_label'        => __( 'Price', 'classified-listing' ),
				'help_message'       => '',
				'validation'         => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'             => '',
				'admin_use_only'     => false,
				'editor'             => [
					'title'      => __( 'Pricing', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-dollar',
					'template'   => 'pricing',
				]
			],
			'category'            => [
				'element'         => 'category',
				'preset'          => 1,
				'type'            => 'single',
				'name'            => 'category',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Category', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Category', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-folder-open',
					'template'   => 'category',
				]
			],
			'tag'                 => [
				'element'         => 'tag',
				'preset'          => 1,
				'name'            => 'tags',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Tag', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'allow_new'       => true,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'admin_use_only'  => false,
				'editor'          => [
					'title'      => __( 'Tag', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-tag',
					'template'   => 'tag',
				]
			],
			'view_count'          => [
				'element'         => 'view_count',
				'preset'          => 1,
				'name'            => 'rtcl_view_count',
				'default_value'   => '',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'View Count', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => true,
				'editor'          => [
					'title'      => __( 'View Count', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-eye',
					'template'   => 'inputNumber',
				]
			],
			'location'            => [
				'element'         => 'location',
				'preset'          => 1,
				'name'            => 'location',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Location', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'is_unique'       => 'no',
				'editor'          => [
					'title'      => __( 'Location', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-location',
					'template'   => 'location',
				],
			],
			'geo_location'        => [
				'element'         => 'geo_location',
				'preset'          => 1,
				'name'            => 'geo_location',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Location', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Geo Location', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-location',
					'template'   => 'geoLocation',
				],
			],
			'map'                 => [
				'element'         => 'map',
				'preset'          => 1,
				'name'            => 'map',
				'container_class' => '',
				'label'           => __( 'Map', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'allow_hide_map'  => true,
				'admin_use_only'  => false,
				'visible_lat_lng' => false,
				'validation'      => [],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Map', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-map',
					'template'   => 'map',
				]
			],
			'address'             => [
				'element'         => 'address',
				'preset'          => 1,
				'type'            => 'text',
				'name'            => 'address',
				'container_class' => '',
				'label'           => __( 'Address', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => __( 'Listing address eg. New York, USA', 'classified-listing' ),
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'is_unique'       => 'no',
				'editor'          => [
					'title'      => __( 'Address', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-address-card',
					'template'   => 'inputText',
				]
			],
			'zipcode'             => [
				'element'         => 'zipcode',
				'preset'          => 1,
				'name'            => 'zipcode',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Zip/Post Code', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Zip/Post Code', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-map-pin',
					'template'   => 'inputText',
				]
			],
			'phone'               => [
				'element'         => 'phone',
				'preset'          => 1,
				'type'            => 'tel',
				'name'            => 'phone',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Phone', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'allow_whatsapp'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Phone', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-phone',
					'template'   => 'inputText',
				]
			],
			'whatsapp'            => [
				'element'         => 'whatsapp',
				'preset'          => 1,
				'type'            => 'tel',
				'name'            => '_rtcl_whatsapp_number',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Whatsapp number', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'allow_whatsapp'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Whatsapp', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-whatsapp',
					'template'   => 'inputText',
				]
			],
			'email'               => [
				'element'         => 'email',
				'preset'          => 1,
				'type'            => 'email',
				'name'            => 'email',
				'default_value'   => '',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Email', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'email'    => [
						'value'   => true,
						'message' => __( 'This field must contain a valid email', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Email', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-mail',
					'template'   => 'inputText',
				]
			],
			'website'             => [
				'element'         => 'website',
				'preset'          => 1,
				'name'            => 'website',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Website', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Website', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-globe',
					'template'   => 'inputText',
				]
			],
			'social_profiles'     => [
				'element'         => 'social_profiles',
				'preset'          => 1,
				'name'            => '_rtcl_social_profiles',
				'container_class' => '',
				'label'           => __( 'Social Profiles', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Social Profiles', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-users',
					'template'   => 'socialProfiles',
				]
			],
			'images'              => [
				'element'         => 'images',
				'preset'          => 1,
				'name'            => 'images',
				'placeholder'     => '',
				'container_class' => '',
				'label'           => __( 'Images', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'admin_use_only'  => false,
				'validation'      => [
					'required'            => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'max_file_size'       => [
						'value'      => 10,
						'_valueFrom' => 'MB',
						'message'    => __( 'Maximum file size limit is {value}MB', 'classified-listing' ),
					],
					'max_file_count'      => [
						'value'   => 5,
						'message' => __( 'You can upload maximum {value} image', 'classified-listing' ),
					],
					'allowed_image_types' => [
						'value'   => [ 'jpeg', 'jpg', 'png', 'webp' ],
						'message' => __( 'Allowed image types does not match. {value}', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Images', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-picture',
					'template'   => 'imagesUpload',
				]
			],
			'video_urls'          => [
				'element'         => 'video_urls',
				'preset'          => 1,
				'name'            => '_rtcl_video_urls',
				'container_class' => '',
				'label'           => __( 'Video Url', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => __( 'Only YouTube & Vimeo URLs.', 'classified-listing' ),
				'help_message'    => __( 'E.g. https://www.youtube.com/watch?v=RiXdDGk_XCU, https://vimeo.com/620922414', 'classified-listing' ),
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Video Url', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-videocam',
					'template'   => 'inputText',
				]
			],
			'business_hours'      => [
				'element'         => 'business_hours',
				'preset'          => 1,
				'name'            => '_rtcl_bhs',
				'container_class' => '',
				'label'           => __( 'Business Hours', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'time_format'     => 'H:i',
				'admin_use_only'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Business Hours', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-clock',
					'template'   => 'businessHours',
				]
			],
			'terms_and_condition' => [
				'element'           => 'terms_and_condition',
				'preset'            => 1,
				'type'              => 'checkbox',
				'name'              => 'rtcl_agree',
				'default_value'     => false,
				'class'             => '',
				'admin_field_label' => __( 'Terms & Conditions', 'classified-listing' ),
				'tnc_html'          => 'I have read and agree to the <a target="_blank" rel="noopener" href="#">Terms and Conditions</a> and <a target="_blank" rel="noopener" href="#">Privacy Policy</a>',
				'has_checkbox'      => true,
				'container_class'   => '',
				'validation'        => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'            => '',
				'editor'            => [
					'title'      => __( 'Terms & Conditions', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-doc-text',
					'template'   => 'termsCheckbox',
				],
			],
			'recaptcha'           => [
				'element'         => 'recaptcha',
				'preset'          => 1,
				'name'            => 'recaptcha',
				'container_class' => '',
				'label'           => __( 'reCaptcha', 'classified-listing' ),
				'label_placement' => '',
				'validation'      => [
					'required' => [
						'value'   => true,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'reCaptcha', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-shield',
					'template'   => 'recaptcha',
				],
			],
			// Custom fields
			'text'                => [
				'element'         => 'text',
				'name'            => 'custom_text',
				'default_value'   => '',
				'id'              => '',
				'container_class' => '',
				'label'           => __( 'Text Input', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => '',
				'filterable'      => false,
				'single_view'     => true,
				'archive_view'    => false,
				'help_message'    => '',
				'order'           => 0,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'min'      => [
						'value'   => '',
						'message' => __( 'Minimum {value} character', 'classified-listing' ),
					],
					'max'      => [
						'value'   => '',
						'message' => __( 'Maximum {value} character', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Text', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-text-width',
					'template'   => 'inputText'
				]
			],
			'textarea'            => [
				'element'         => 'textarea',
				'name'            => 'custom_textarea',
				'default_value'   => '',
				'id'              => '',
				'editor_type'     => 'textarea',
				'placeholder'     => '',
				'rows'            => 5,
				'cols'            => 2,
				'container_class' => '',
				'label'           => __( 'Textarea', 'classified-listing' ),
				'label_placement' => '',
				'help_message'    => '',
				'single_view'     => true,
				'archive_view'    => false,
				'order'           => 0,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'min'      => [
						'value'   => '',
						'message' => __( 'Minimum {value} character', 'classified-listing' ),
					],
					'max'      => [
						'value'   => '',
						'message' => __( 'Maximum {value} character', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Textarea', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-align-left',
					'template'   => 'inputTextarea'
				]
			],
			'number'              => [
				'element'           => 'number',
				'name'              => 'custom_number',
				'default_value'     => '',
				'id'                => '',
				'class'             => '',
				'placeholder'       => '',
				'container_class'   => '',
				'label'             => __( 'Number', 'classified-listing' ),
				'label_placement'   => '',
				'help_message'      => '',
				'numeric_formatter' => '',
				'filterable'        => false,
				'single_view'       => true,
				'archive_view'      => false,
				'order'             => 0,
				'validation'        => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'numeric'  => [
						'value'   => true,
						'message' => __( 'This field must contain numeric value', 'classified-listing' ),
					],
					'min'      => [
						'value'   => '',
						'message' => __( 'Minimum value is {value}', 'classified-listing' ),
					],
					'max'      => [
						'value'   => '',
						'message' => __( 'Maximum value is {value}', 'classified-listing' ),
					],
					'digits'   => [
						'value'   => '',
						'message' => __( 'The number of digits has to be {value}', 'classified-listing' ),
					],
				],
				'logics'            => '',
				'editor'            => [
					'title'      => __( 'Number', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-calc',
					'template'   => 'inputNumber'
				]
			],
			'url'                 => [
				'element'         => 'url',
				'name'            => 'custom_url',
				'default_value'   => '',
				'container_class' => '',
				'id'              => '',
				'label'           => __( 'Url', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => '',
				'help_message'    => '',
				'single_view'     => true,
				'order'           => 0,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'url'      => [
						'value'   => true,
						'message' => __( 'This field must contain a valid url', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Url', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-link',
					'template'   => 'inputText'
				]
			],
			'date'                => [
				'element'              => 'date',
				'name'                 => 'custom_datetime',
				'container_class'      => '',
				'class'                => '',
				'id'                   => '',
				'label'                => __( 'Date / Time', 'classified-listing' ),
				'label_placement'      => '',
				'date_format'          => 'd/m/Y H:i',
				'date_type'            => 'single',
				'filterable_date_type' => 'single',
				'date_config'          => '',
				'is_time_enabled'      => true,
				'placeholder'          => '',
				'help_message'         => '',
				'filterable'           => false,
				'single_view'          => true,
				'archive_view'         => false,
				'order'                => 0,
				'validation'           => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'logics'               => '',
				'editor'               => [
					'title'      => __( 'Date & Time', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-calendar',
					'template'   => 'datePicker'
				]
			],
			'color_picker'        => [
				'element'         => 'color_picker',
				'name'            => 'custom_color_picker',
				'default_value'   => '',
				'container_class' => '',
				'id'              => '',
				'label'           => __( 'Color', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => '',
				'help_message'    => '',
				'filterable'      => false,
				'single_view'     => true,
				'archive_view'    => false,
				'order'           => 0,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'Color Picker', 'classified-listing' ),
					'icon_class' => 'dashicons dashicons-color-picker',
					'template'   => 'colorPicker',
				]
			],
			'select'              => [
				'element'         => 'select',
				'name'            => 'custom_dropdown',
				'default_value'   => '',
				'container_class' => '',
				'id'              => '',
				'label'           => __( 'Dropdown', 'classified-listing' ),
				'label_placement' => '',
				'placeholder'     => __( '- Select -', 'classified-listing' ),
				'help_message'    => '',
				'logics'          => '',
				'filterable'      => false,
				'single_view'     => true,
				'archive_view'    => false,
				'order'           => 0,
				'options'         => [
					[
						'label' => __( 'Option 1', 'classified-listing' ),
						'value' => __( 'option_1', 'classified-listing' ),
					],
					[
						'label' => __( 'Option 2', 'classified-listing' ),
						'value' => __( 'option_2', 'classified-listing' ),
					],
				],
				'values_visible'  => false,
				'validation'      => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'editor'          => [
					'title'      => __( 'Dropdown', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-expand',
					'template'   => 'select',
				]
			],
			'switch'              => [
				'element'         => 'switch',
				'name'            => 'custom_switch',
				'default_value'   => '',
				'container_class' => '',
				'id'              => '',
				'label'           => __( 'Switch', 'classified-listing' ),
				'label_placement' => 'left',
				'help_message'    => '',
				'logics'          => '',
				'filterable'      => false,
				'single_view'     => true,
				'archive_view'    => false,
				'order'           => 0,
				'editor'          => [
					'title'      => __( 'Switch', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-toggle-on',
					'template'   => 'switch'
				]
			],
			'radio'               => [
				'element'           => 'radio',
				'name'              => 'custom_radio',
				'container_class'   => '',
				'class'             => '',
				'id'                => '',
				'label'             => __( 'Radio', 'classified-listing' ),
				'label_placement'   => '',
				'help_message'      => '',
				'logics'            => '',
				'filterable'        => false,
				'single_view'       => true,
				'archive_view'      => false,
				'options'           => [
					[
						'label'      => 'Yes',
						'value'      => 'yes',
						'icon_class' => ''
					],
					[
						'label'      => 'No',
						'value'      => 'no',
						'icon_class' => ''
					],
				],
				'enable_icon_class' => false,
				'values_visible'    => false,
				'direction'         => 'horizontal',
				'order'             => 0,
				'validation'        => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'editor'            => [
					'title'      => __( 'Radio', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-dot-circled',
					'template'   => 'inputRadio'
				]
			],
			'checkbox'            => [
				'element'           => 'checkbox',
				'name'              => 'custom_checkbox',
				'container_class'   => '',
				'class'             => '',
				'id'                => '',
				'label'             => __( 'Checkbox', 'classified-listing' ),
				'label_placement'   => '',
				'help_message'      => '',
				'logics'            => '',
				'filterable'        => false,
				'single_view'       => true,
				'archive_view'      => false,
				'order'             => 0,
				'options'           => [
					[
						'label'      => 'Item 1',
						'value'      => 'item_1',
						'icon_class' => '',
					],
					[
						'label'      => 'Item 2',
						'value'      => 'item_2',
						'icon_class' => '',
					],
					[
						'label'      => 'Item 3',
						'value'      => 'item_3',
						'icon_class' => '',
					],
				],
				'enable_icon_class' => false,
				'values_visible'    => false,
				'direction'         => 'horizontal',
				'validation'        => [
					'required' => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					]
				],
				'editor'            => [
					'title'      => __( 'Checkbox', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-check',
					'template'   => 'inputCheckbox'
				]
			],
			'file'                => [
				'element'         => 'file',
				'name'            => 'file',
				'container_class' => '',
				'id'              => '',
				'label'           => __( 'File Upload', 'classified-listing' ),
				'label_placement' => '',
				'btn_text'        => __( 'Choose File', 'classified-listing' ),
				'help_message'    => '',
				'file_location'   => 'default',
				'single_view'     => true,
				'order'           => 0,
				'validation'      => [
					'required'           => [
						'value'   => false,
						'message' => __( 'This field is required', 'classified-listing' ),
					],
					'max_file_size'      => [
						'value'      => 2,
						'_valueFrom' => 'MB',
						'message'    => __( 'Maximum file size limit is {value}MB', 'classified-listing' ),
					],
					'max_file_count'     => [
						'value'   => 1,
						'message' => __( 'You can upload maximum {value} file', 'classified-listing' ),
					],
					'allowed_file_types' => [
						'value'   => [ 'jpg|jpeg|webp|png|gif|bmp' ],
						'message' => __( 'Invalid file type', 'classified-listing' ),
					],
				],
				'logics'          => '',
				'editor'          => [
					'title'      => __( 'File Upload', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-upload',
					'template'   => 'fileUpload',
				]
			],
			'input_hidden'        => [
				'element'           => 'input_hidden',
				'name'              => 'hidden',
				'id'                => '',
				'default_value'     => '',
				'container_class'   => '',
				'admin_field_label' => '',
				'logics'            => '',
				'editor'            => [
					'title'      => __( 'Hidden Field', 'classified-listing' ),
					'icon_class' => 'dashicons dashicons-hidden',
					'template'   => 'inputHidden'
				]
			],
			'custom_html'         => [
				'element'         => 'custom_html',
				'id'              => '',
				'html_codes'      => 'Some description about this section',
				'logics'          => '',
				'container_class' => '',
				'editor'          => [
					'title'      => __( 'Custom HTML', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-code',
					'template'   => 'customHTML',
				],
			]
		];

		if ( Functions::location_type() !== 'local' ) {
			unset( $fields['location'] );
			unset( $fields['zipcode'] );
			unset( $fields['address'] );
		} else {
			unset( $fields['geo_location'] );
		}

		$fields = self::addAIFieldToElements( $fields );

		return apply_filters( 'rtcl_fb_fields', $fields );
	}

	/**
	 * @return string[]
	 */
	public static function getFieldElements() {
		return array_keys( self::get() );
	}

	public static function settings() {

		$fields = [
			'icon'            => [
				'name'    => 'icon',
				'type'    => 'icon',
				'label'   => __( 'Icon Type', 'classified-listing' ),
				'options' => [
					[
						'value' => '',
						'label' => __( 'None', 'classified-listing' ),
					],
					[
						'value' => 'class',
						'label' => __( 'Icon', 'classified-listing' ),
					]
				]
			],
			'submit_btn_text' => [
				'type'    => 'text',
				'label'   => __( 'Submit button text', 'classified-listing' ),
				'default' => __( 'Submit', 'classified-listing' ),
			],
			'update_btn_text' => [
				'type'    => 'text',
				'label'   => __( 'Update button text', 'classified-listing' ),
				'default' => __( 'Update', 'classified-listing' ),
			],
			//			'category_filter' => [
			//				'type'      => 'category_filter',
			//				'help_text' => __( 'Select top label categories for this form.', 'classified-listing' ),
			//				'label'     => __( 'Category Filter', 'classified-listing' ),
			//				'mode'      => [
			//					'default' => 'include',
			//					'options' => [
			//						[ 'value' => 'include', 'label' => __( 'Include', 'classified-listing' ) ],
			//						[ 'value' => 'exclude', 'label' => __( 'Exclude', 'classified-listing' ) ],
			//					]
			//				]
			//			],
			// 'form_type' => [
			// 'type'    => 'radio',
			// 'name'    => 'type',
			// 'label'   => __( 'Form type', 'classified-listing' ),
			// 'options' => [
			// [
			// 'value' => '',
			// 'label' => __( 'Regular', 'classified-listing' ),
			// ],
			// [
			// 'value' => 'step',
			// 'label' => __( 'Step', 'classified-listing' ),
			// ],
			// ],
			// ]
		];

		return apply_filters( 'rtcl/fb/settings_fields', $fields );
	}

	public static function getSectionField() {
		$field = [
			'element'         => 'section',
			'title'           => __( 'Section title', 'classified-listing' ),
			'id'              => '',
			'container_class' => '',
			'icon'            => '',
			'logics'          => '',
			'columns'         => [
				[
					'width'  => '',
					'fields' => []
				],
			]
		];

		return apply_filters( 'rtcl/fb/section_field', $field );
	}


	/**
	 * @return mixed|null
	 */
	public static function translatableFields() {
		$fields = [
			'title',
			'label',
			'pricing_type_label',
			'price_type_label',
			'price_label',
			'placeholder',
			'validation',
			'options',
			'advanced_options',
			'tnc_html',
			'html_codes',
			'help_message',
		];

		return apply_filters( 'rtcl_fb_translatable_fields', $fields );
	}

	/**
	 * @return mixed|null
	 */
	public static function translatableFormFields() {
		$fields = [
			[
				'id'    => 'title',
				'type'  => 'text',
				'label' => __( 'Form Title', 'classified-listing' ),
			],
		];

		return apply_filters( 'rtcl_fb_translatable_form_fields', $fields );
	}

	public static function optionFields() {
		$fields = [
			[
				'label' => __( 'Active From builder', 'classified-listing' ),
				'type'  => 'switch',
				'name'  => 'active',
			]
		];

		return apply_filters( 'rtcl/fb/option_fields', $fields );
	}

	public static function singleLayoutSettingsFields() {
		$fields = [
			[
				'label' => __( 'Active Single Layout builder', 'classified-listing' ),
				'type'  => 'switch',
				'name'  => 'active',
			]
		];

		return apply_filters( 'rtcl/fb/single_layout/settings', $fields );
	}

	public static function singleLayoutFields() {
		$fields = [
			'listing_meta'      => [
				'element' => 'listing_meta',
				'items'   => [
					[ 'type' => 'date', 'icon' => [ 'class' => 'rtcl-icon-calendar' ] ],
					[ 'type' => 'time', 'icon' => [ 'class' => 'rtcl-icon-clock' ] ],
					[ 'type' => 'author', 'icon' => [ 'class' => 'rtcl-icon-user-o' ] ],
					[ 'type' => 'location', 'icon' => [ 'class' => 'rtcl-icon-map-pin' ] ],
					[ 'type' => 'category', 'icon' => [ 'class' => 'rtcl-icon-folder-empty' ] ],
					[ 'type' => 'comments', 'icon' => [ 'class' => 'rtcl-icon-comment-empty' ] ],
					[ 'type' => 'view', 'icon' => [ 'class' => 'rtcl-icon-eye' ] ],
					[ 'type' => 'type', 'icon' => [ 'class' => 'rtcl-icon-tag' ] ],
				],
				'editor'  => [
					'title'      => __( 'Listing Meta', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-doc-text',
					'template'   => 'listable',
					'types'      => [ 'date', 'author', 'location', 'category', 'comments', 'view', 'type' ]
				],
			],
			'listing_actions'   => [
				'element' => 'listing_actions',
				'items'   => [
					[ 'type' => 'compare', 'icon' => [ 'class' => 'rtcl-icon-retweet' ] ],
					[ 'type' => 'favourite', 'icon' => [ 'class' => 'rtcl-icon-heart' ] ],
					[ 'type' => 'share', 'icon' => [ 'class' => 'rtcl-icon-share' ] ],
					[ 'type' => 'report_abuse', 'icon' => [ 'class' => 'rtcl-icon-trash-1' ] ],
					[ 'type' => 'print', 'icon' => [ 'class' => 'rtcl-icon-print' ] ]
				],
				'editor'  => [
					'title'      => __( 'Listing Actions', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-cog-2',
					'template'   => 'listable',
					'types'      => [ 'compare', 'favourite', 'share', 'report_abuse', 'print' ]
				],
			],
			'chat'              => [
				'element' => 'chat',
				'text'    => 'Chat',
				'icon'    => [
					'type'  => 'class',
					'class' => 'rtcl-icon-chat-empty'
				],
				'editor'  => [
					'title'      => __( 'Chat', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-chat-empty',
					'template'   => 'action_button',
				]
			],
			'contact_to_seller' => [
				'element' => 'contact_to_seller',
				'text'    => 'Message to Seller',
				'icon'    => [
					'type'  => 'class',
					'class' => 'rtcl-icon-envelope-open-o'
				],
				'editor'  => [
					'title'      => __( 'Contact to Seller', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-envelope-open-o',
					'template'   => 'action_button',
				]
			],
			'author_info'       => [
				'element' => 'author_info',
				'items'   => [ 'avatar', 'name', 'online_status' ],
				'editor'  => [
					'title'      => __( 'Author Info', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-user-o',
					'template'   => 'item_info',
					'direction'  => 'vertical',
					'items'      => [
						'avatar'        => __( 'Avatar', 'classified-listing' ),
						'name'          => __( 'Name', 'classified-listing' ),
						'author_badges' => __( 'Author Badges', 'classified-listing' ),
						'location'      => __( 'Location', 'classified-listing' ),
						'phone'         => __( 'Phone', 'classified-listing' ),
						'email'         => __( 'Email', 'classified-listing' ),
						'website'       => __( 'Website', 'classified-listing' ),
						'contact_form'  => __( 'Contact Form', 'classified-listing' ),
					]
				]
			],
			'store_info'        => [
				'element' => 'store_info',
				'items'   => [ 'logo', 'name' ],
				'editor'  => [
					'title'      => __( 'Store Info', 'classified-listing' ),
					'icon_class' => 'dashicons dashicons-store',
					'template'   => 'item_info',
					'items'      => [
						'logo' => __( 'Logo', 'classified-listing' ),
						'name' => __( 'Name', 'classified-listing' )
					]
				]
			],
			'shortcode'         => [
				'element' => 'shortcode',
				'editor'  => [
					'title'      => __( 'Shortcode', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-code',
					'template'   => 'shortcode',
					'hints'      => [
						'value' => __( 'Enter shortcode', 'classified-listing' ),
					]
				]
			],
			'spacer'            => [
				'element' => 'spacer',
				'value'   => 25,
				'editor'  => [
					'title'      => __( 'Spacer', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-i-cursor',
					'template'   => 'spacer',
				]
			],
			'html'              => [
				'element' => 'html',
				'editor'  => [
					'title'      => __( 'HTML', 'classified-listing' ),
					'icon_class' => 'rtcl-icon-code',
					'template'   => 'html',
				]
			],
		];

		return apply_filters( 'rtcl/fb/single_layout/fields', $fields );
	}

	private static function addAIFieldToElements( array $fields ): array {
		$typesWithAI = [ 'text', 'title', 'textarea', 'description', 'excerpt' ];
		$aiEnabled   = Functions::is_ai_enabled();
		$hasPro      = rtcl()->has_pro();

		return array_map( function ( $field ) use ( $aiEnabled, $typesWithAI, $hasPro ) {
			if ( in_array( $field['element'], $typesWithAI ) ) {
				$field['ai'] = in_array( $field['element'], [
					'title',
					'description',
					'excerpt'
				] ) ? $aiEnabled : $hasPro && $aiEnabled;
			}

			return $field;
		}, $fields );
	}
}