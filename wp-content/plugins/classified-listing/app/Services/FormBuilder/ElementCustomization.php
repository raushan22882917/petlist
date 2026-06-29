<?php

namespace Rtcl\Services\FormBuilder;

use Rtcl\Helpers\Helper;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\Components\DateTime;

class ElementCustomization {


	public static function getSettingsPlacement(): array {
		$placement = [
			'listing_type'        => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message' ]
			],
			'title'               => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation', 'ai' ],
				'advance' => [ 'default_value', 'container_class', 'help_message' ]
			],
			'description'         => [
				'general' => [ 'label', 'label_placement', 'icon', 'editor_type', 'excluded_tags', 'rows', 'validation', 'ai' ],
				'advance' => [ 'container_class', 'help_message', 'admin_use_only' ]
			],
			'excerpt'             => [
				'general' => [ 'label', 'label_placement', 'icon', 'validation', 'ai' ],
				'advance' => [ 'container_class', 'help_message', 'admin_use_only' ]
			],
			'category'            => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'placeholder',
					'category_filter',
					'multiple',
					'max_selection',
					'category_limit',
					'validation'
				],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'location'            => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'map'                 => [
				'general' => [ 'label', 'label_placement', 'validation', 'allow_hide_map', 'visible_lat_lng' ]
			],
			'social_profiles'     => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'user_social_default',
					'validation',
					'help_message',
					'admin_use_only'
				]
			],
			'address'             => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'geo_location'        => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'zipcode'             => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'email'               => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'website'             => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'social_info'         => [
				'general' => [ 'label', 'label_placement', 'icon', 'validation', 'admin_use_only' ]
			],
			'phone'               => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'whatsapp'            => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [ 'default_value', 'container_class', 'help_message', 'logics', 'admin_use_only' ]
			],
			'tag'                 => [
				'general' => [ 'label', 'label_placement', 'icon', 'allow_new', 'validation' ],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'pricing'             => [
				'general' => [
					'label',
					'label_placement',
					'price_label',
					'pricing_options',
					'pricing_type_label',
					'price_type_label',
					'price_unit_label',
					'price_units',
					'validation'
				],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'view_count'          => [
				'general' => [ 'label', 'label_placement', 'placeholder', 'admin_use_only', 'default_value' ]
			],
			'images'              => [
				'general' => [
					'validation',
					'manipulation',
					'admin_use_only',
					'container_class',
					'help_message',
					'logics'
				]
			],
			'video_urls'          => [
				'general' => [ 'label', 'label_placement', 'icon', 'placeholder', 'validation' ],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'business_hours'      => [
				'general' => [ 'label', 'label_placement', 'icon', 'time_format', 'validation' ],
				'advance' => [
					'container_class',
					'help_message',
					'admin_use_only'
				]
			],
			'terms_and_condition' => [
				'general' => [ 'admin_field_label', 'validation', 'tnc_html', 'has_checkbox', 'admin_use_only' ]
			],
			// custom fields
			'select'              => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'placeholder',
					'validation',
					'option_depends_on',
					'advanced_options',
					'filterable',
					'filterable_disable_logic',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'id',
					'container_class',
					'help_message',
					'logics'
				]
			],
			'switch'              => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'default_checked',
					'id',
					'container_class',
					'help_message',
					'single_view',
					'archive_view',
					'logics',
					'order'
				]
			],
			'radio'               => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'option_depends_on',
					'advanced_options',
					'direction',
					'vertical_cols',
					'validation',
					'filterable',
					'filterable_disable_logic',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'id',
					'container_class',
					'help_message',
					'logics'
				]
			],
			'checkbox'            => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'option_depends_on',
					'advanced_options',
					'direction',
					'vertical_cols',
					'validation',
					'filterable',
					'filterable_disable_logic',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'id',
					'container_class',
					'help_message',
					'logics'
				]
			],
			'section'             => [
				'general' => [
					'section_title',
					'icon',
					'id',
					'container_class',
					'column',
					'order',
					'logics',
				]
			],
			'text'                => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'placeholder',
					'validation',
					'filterable',
					'filterable_disable_logic',
					'single_view',
					'archive_view',
					'order',
					'ai'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'textarea'            => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'editor_type',
					'excluded_tags',
					'rows',
					'placeholder',
					'validation',
					'single_view',
					'archive_view',
					'order',
					'ai'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'number'              => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'placeholder',
					'validation',
					'numeric_formatter',
					'filterable',
					'filterable_disable_logic',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'number_step',
					'admin_use_only',
					'logics'
				]
			],
			'url'                 => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'placeholder',
					'validation',
					'single_view',
					'order'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'date'                => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'placeholder',
					'date_format',
					'date_type',
					'validation',
					'filterable',
					'filterable_disable_logic',
					'filterable_date_type',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'color_picker'        => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'validation',
					'single_view',
					'archive_view',
					'order'
				],
				'advance' => [
					'default_value',
					'id',
					'container_class',
					'help_message',
					'admin_use_only',
					'logics'
				]
			],
			'file'                => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'btn_text',
					'validation',
					'file_location',
					'single_view',
					'order'
				],
				'advance' => [
					'id',
					'container_class',
					'help_message',
					'logics',
				],
			],
			'repeater'            => [
				'general' => [
					'label',
					'label_placement',
					'icon',
					'name',
					'single_view',
					'archive_view',
					'multi_column',
					'repeat_fields',
				],
				'advance' => [
					'id',
					'container_class',
					'help_message',
					'order',
					'logics',
					'max_repeat_field',
					'repeater_collapsable',
					'repeater_layout',
				],
			],
			'input_hidden'        => [
				'general' => [ 'admin_field_label', 'default_value', 'name', 'id' ]
			],
			'custom_html'         => [
				'general' => [
					'id',
					'html_codes',
					'logics',
					'container_class'
				]
			]
		];

		return apply_filters( 'rtcl_fb_editor_settings_placement', $placement );
	}

	public static function settingsFields() {
		$dateTime           = new DateTime();
		$dateFormats        = $dateTime->getAvailableDateFormats();
		$dateConfigSettings = [
			'template'         => 'inputTextarea',
			'label'            => __( 'Advanced Date Configuration', 'classified-listing' ),
			'placeholder'      => __( 'Advanced Date Configuration', 'classified-listing' ),
			'rows'             => 2,
			'start_text'       => 2,
			'disabled'         => false,
			'css_class'        => 'rtcl_code_editor',
			'inline_help_text' => '',
			'help_text'        => __( 'You can write your own date configuration as JS object. Please write valid configuration as per flatpickr config.', 'classified-listing' ),
		];
		$settingsFields     = [

			'name'                         => [
				'template'  => 'nameAttr',
				'label'     => __( 'Name Attribute', 'classified-listing' ),
				'help_text' => __( 'This is the field name attributes which is used to submit form data, name attribute must be unique.', 'classified-listing' ),
			],
			'editor_type'                  => [
				'template'  => 'radio',
				'default'   => 'textarea',
				'label'     => __( 'Editor Type', 'classified-listing' ),
				'help_text' => __( 'Editor Type', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'textarea',
						'label' => __( 'Textarea', 'classified-listing' ),
					],
					[
						'value' => 'wp_editor',
						'label' => __( 'WP Editor', 'classified-listing' ),
					],
				],
			],
			'excluded_tags'                => [
				'template'    => 'multiSelect',
				'label'       => __( 'Excluded Tags', 'classified-listing' ),
				'help_text'   => __( 'Select HTML tags to disallow in the editor. Script tag is always excluded.', 'classified-listing' ),
				'placeholder' => __( 'Select tags to exclude', 'classified-listing' ),
				'dependency'  => [
					'depends_on' => 'editor_type',
					'value'      => 'wp_editor',
					'operator'   => '==',
				],
				'options'     => apply_filters( 'rtcl_fb_excluded_tags_options', [
					[
						'label' => __( 'Anchor (a)', 'classified-listing' ),
						'value' => 'a',
					],
					[
						'label' => __( 'Image (img)', 'classified-listing' ),
						'value' => 'img',
					],
					[
						'label' => __( 'Iframe (iframe)', 'classified-listing' ),
						'value' => 'iframe',
					],
					[
						'label' => __( 'Table (table)', 'classified-listing' ),
						'value' => 'table',
					],
					[
						'label' => __( 'Form (form)', 'classified-listing' ),
						'value' => 'form',
					],
					[
						'label' => __( 'Video (video)', 'classified-listing' ),
						'value' => 'video',
					],
					[
						'label' => __( 'Audio (audio)', 'classified-listing' ),
						'value' => 'audio',
					],
					[
						'label' => __( 'Object (object)', 'classified-listing' ),
						'value' => 'object',
					],
					[
						'label' => __( 'Embed (embed)', 'classified-listing' ),
						'value' => 'embed',
					],
					[
						'label' => __( 'Code (code)', 'classified-listing' ),
						'value' => 'code',
					],
				] ),
			],
			'date_type'                    => [
				'template' => 'radio',
				'default'  => 'single',
				'label'    => __( 'Date Type', 'classified-listing' ),
				'options'  => [
					[
						'value' => 'single',
						'label' => __( 'Single', 'classified-listing' ),
					],
					[
						'value' => 'range',
						'label' => __( 'Range', 'classified-listing' ),
					],
				],
			],
			'filterable_date_type'         => [
				'template'   => 'radio',
				'default'    => 'single',
				'label'      => __( 'Filter Date Type', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'filterable',
					'value'      => true,
					'operator'   => '==',
				],
				'options'    => [
					[
						'value' => 'single',
						'label' => __( 'Single', 'classified-listing' ),
					],
					[
						'value' => 'range',
						'label' => __( 'Range', 'classified-listing' ),
					],
				],
			],
			'icon'                         => [
				'template' => 'icon',
				'label'    => __( 'Icon Type', 'classified-listing' ),
				'i18n'     => [
					'icon'           => __( 'Icon', 'classified-listing' ),
					'select_an_icon' => __( 'Select an icon', 'classified-listing' ),
				],
				'options'  => [
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
			'selection_type'               => [
				'key'      => 'type',
				'template' => 'radio',
				'label'    => __( 'Selection Type', 'classified-listing' ),
				'options'  => [
					[
						'value' => 'single',
						'label' => __( 'Single', 'classified-listing' ),
					],
					[
						'value' => 'multiple',
						'label' => __( 'Multiple', 'classified-listing' ),
					],
				],
			],
			'label'                        => [
				'template'  => 'inputText',
				'label'     => __( 'Label', 'classified-listing' ),
				'help_text' => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
			],
			'label_placement'              => [
				'template'  => 'radio',
				'style'     => 'button',
				'label'     => __( 'Label Placement', 'classified-listing' ),
				'help_text' => __( 'Determine the position of label title where the user will see this. By choosing "Default", global label placement setting will be applied.', 'classified-listing' ),
				'options'   => [
					[
						'value' => '',
						'label' => __( 'Default', 'classified-listing' ),
					],
					[
						'value' => 'left',
						'label' => __( 'Left', 'classified-listing' ),
					],
					[
						'value' => 'bottom',
						'label' => __( 'Bottom', 'classified-listing' ),
					],
					[
						'value' => 'top',
						'label' => __( 'Top', 'classified-listing' ),
					],
					[
						'value' => 'right',
						'label' => __( 'Right', 'classified-listing' ),
					],
					[
						'value' => 'hide',
						'label' => __( 'Hide', 'classified-listing' ),
					],
				],
			],
			'order'                        => [
				'template'  => 'inputNumber',
				'label'     => __( 'Display order', 'classified-listing' ),
				'help_text' => __( 'Custom field display order,  Default 0', 'classified-listing' ),
			],
			'category_filter'              => [
				'key'         => 'filter',
				'template'    => 'categoryFilter',
				'label'       => __( 'Category filter', 'classified-listing' ),
				'placeholder' => __( "Type to search category", 'classified-listing' ),
				'help_text'   => __( 'Filter top level categories', 'classified-listing' ),
				'mode'        => [
					'default' => 'include',
					'options' => [
						[ 'value' => 'include', 'label' => __( 'Include', 'classified-listing' ) ],
						[ 'value' => 'exclude', 'label' => __( 'Exclude', 'classified-listing' ) ],
					]
				]
			],
			'top_level_category_ids'       => [
				'key'       => 'top_level_ids',
				'template'  => 'topLevelCategory',
				'label'     => __( 'Allowed Top level categories', 'classified-listing' ),
				'help_text' => __( 'Allowed top level categories', 'classified-listing' ),
			],
			'button_style'                 => [
				'template'  => 'selectBtnStyle',
				'label'     => __( 'Button Style', 'classified-listing' ),
				'help_text' => __( 'Select a button style from the dropdown', 'classified-listing' ),
			],
			'button_size'                  => [
				'template'  => 'radio',
				'label'     => __( 'Button Size', 'classified-listing' ),
				'help_text' => __( 'Define a size of the button', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'sm',
						'label' => __( 'Small', 'classified-listing' ),
					],
					[
						'value' => 'md',
						'label' => __( 'Medium', 'classified-listing' ),
					],
					[
						'value' => 'lg',
						'label' => __( 'Large', 'classified-listing' ),
					],
				],
			],
			'direction'                    => [
				'template' => 'radio',
				'label'    => __( 'Direction', 'classified-listing' ),
				'options'  => [
					[
						'value' => 'horizontal',
						'label' => __( 'Horizontal', 'classified-listing' ),
					],
					[
						'value' => 'vertical',
						'label' => __( 'Vertical', 'classified-listing' ),
					],
				],
			],
			'vertical_cols'                => [
				'template'   => 'radio',
				'label'      => __( 'Column Count', 'classified-listing' ),
				'default'    => 1,
				'options'    => [
					[
						'value' => 1,
						'label' => __( '1 Columns', 'classified-listing' ),
					],
					[
						'value' => 2,
						'label' => __( '2 Columns', 'classified-listing' ),
					],
					[
						'value' => 3,
						'label' => __( '3 Columns', 'classified-listing' ),
					],
					[
						'value' => 4,
						'label' => __( '4 Columns', 'classified-listing' ),
					],
				],
				'dependency' => [
					'depends_on' => 'direction',
					'value'      => 'vertical',
					'operator'   => '==',
				],
			],
			'placeholder'                  => [
				'template'  => 'inputText',
				'label'     => __( 'Placeholder', 'classified-listing' ),
				'help_text' => __( 'This is the field placeholder, the user will see this if the input field is empty.', 'classified-listing' ),
			],
			'pricing_type_label'           => [
				'template'   => 'inputText',
				'label'      => __( 'Pricing Type Label', 'classified-listing' ),
				'help_text'  => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'options',
					'value'      => 'pricing_type',
					'operator'   => 'includes',
				]
			],
			'price_label'                  => [
				'template'  => 'inputText',
				'label'     => __( 'Pricing Label', 'classified-listing' ),
				'help_text' => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
			],
			'price_type_label'             => [
				'template'   => 'inputText',
				'label'      => __( 'Price Type Label', 'classified-listing' ),
				'help_text'  => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'options',
					'value'      => 'price_type',
					'operator'   => 'includes',
				]
			],
			'price_unit_label'             => [
				'template'   => 'inputText',
				'label'      => __( 'Price Unit Label', 'classified-listing' ),
				'help_text'  => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'options',
					'value'      => 'price_unit',
					'operator'   => 'includes',
				]
			],
			'price_units'                  => [
				'template'   => 'priceUnits',
				'i18n'       => [
					'available_units' => __( 'Available Units', 'classified-listing' )
				],
				'label'      => __( 'Price Units', 'classified-listing' ),
				'help_text'  => __( 'Define your price units ', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'options',
					'value'      => 'price_unit',
					'operator'   => 'includes',
				]
			],
			'pricing_options'              => [
				'key'       => 'options',
				'template'  => 'multiCheckbox',
				'label'     => __( 'Pricing Options', 'classified-listing' ),
				'options'   => [
					[
						'label' => __( 'Pricing Type', 'classified-listing' ),
						'value' => 'pricing_type'
					],
					[
						'label' => __( 'Price Type', 'classified-listing' ),
						'value' => 'price_type'
					],
					[
						'label' => __( 'Price Unit', 'classified-listing' ),
						'value' => 'price_unit'
					]
				],
				'help_text' => __( 'This is the field title the user will see when filling out the form.', 'classified-listing' ),
			],
			'max_image_limit'              => [
				'template' => 'inputNumber',
				'label'    => __( 'Max Image Limit', 'classified-listing' )
			],
			'category_limit'               => [
				'key'        => 'result_limit',
				'default'    => 10,
				'template'   => 'inputNumber',
				'label'      => __( 'Search result Limit', 'classified-listing' ),
				'help_text'  => __( 'Blank or 0 for all matched result', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'multiple',
					'value'      => true,
					'operator'   => '==',
				]
			],
			'max_upload_size'              => [
				'template'  => 'inputNumber',
				'label'     => __( 'Max Upload Size Per Image in MB', 'classified-listing' ),
				'help_text' => __( 'Here 0 means unlimited.', 'classified-listing' ),
			],
			'total_upload_size'            => [
				'template'  => 'inputNumber',
				'label'     => __( 'Total Upload Size in MB', 'classified-listing' ),
				'help_text' => __( 'Here 0 means unlimited.', 'classified-listing' ),
			],
			'date_format'                  => [
				'template'    => 'select',
				'label'       => __( 'Date Format', 'classified-listing' ),
				'filterable'  => true,
				'creatable'   => true,
				'placeholder' => __( 'Select Date Format', 'classified-listing' ),
				'help_text'   => __( 'Select any date format from the dropdown. The user will be able to choose a date in this given format.', 'classified-listing' ),
				'options'     => $dateFormats,
			],
			'time_format'                  => [
				'template'    => 'select',
				'label'       => __( 'Time Format', 'classified-listing' ),
				'placeholder' => __( 'Select time Format', 'classified-listing' ),
				'options'     => $dateTime->getAvailableTimeFormat(),
			],
			'output_time_format'           => [
				'template'    => 'select',
				'label'       => __( 'Display Time Format', 'classified-listing' ),
				'placeholder' => __( 'Select time Format', 'classified-listing' ),
				'options'     => $dateTime->getAvailableTimeFormat(),
			],
			'date_config'                  => $dateConfigSettings,
			'rows'                         => [
				'template'  => 'inputNumber',
				'default'   => 5,
				'label'     => __( 'Rows', 'classified-listing' ),
				'help_text' => __( 'How many rows will textarea take in a form. It\'s an HTML attributes for browser support.', 'classified-listing' ),
			],
			'cols'                         => [
				'template'  => 'inputNumber',
				'label'     => __( 'Columns', 'classified-listing' ),
				'help_text' => __( 'How many cols will textarea take in a form. It\'s an HTML attributes for browser support.', 'classified-listing' ),
			],
			'options'                      => [
				'template'  => 'selectOptions',
				'label'     => __( 'Options', 'classified-listing' ),
				'help_text' => __( 'Create options for the field and checkmark them for default selection.', 'classified-listing' ),
			],
			'option_depends_on'            => [
				'template'    => 'optionDependsOn',
				'isPro'       => true,
				'label'       => __( 'Enable Smart Dependency', 'classified-listing' ),
				'placeholder' => __( 'Select a depends on field', 'classified-listing' ),
			],
			'advanced_options'             => [
				'template'  => 'advancedOptions',
				'label'     => __( 'Options', 'classified-listing' ),
				'help_text' => __( 'Create visual options for the field and checkmark them for default selection.', 'classified-listing' ),
				'i18n'      => [
					'label'           => __( 'Label', 'classified-listing' ),
					'value'           => __( 'value', 'classified-listing' ),
					'assign'          => __( 'Assign', 'classified-listing' ),
					'dependsOn'       => __( 'Depends on', 'classified-listing' ),
					'bulk_add'        => __( 'Bulk Add', 'classified-listing' ),
					'hide_bulk_add'   => __( 'Hide Bulk Add', 'classified-listing' ),
					'clear_selection' => __( 'Clear Selection', 'classified-listing' ),
				],
				'bulk_add'  => [
					'label'   => __( 'Add Options (one per line)', 'classified-listing' ),
					'add_btn' => __( 'Add New Options', 'classified-listing' ),
				],
				'config'    => [
					[
						'label' => __( 'Show Values', 'classified-listing' ),
						'value' => 'values_visible'
					],
					[
						'label' => __( 'Icon', 'classified-listing' ),
						'value' => 'enable_icon_class'
					],
				]
			],
			'allow_new'                    => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Allow New', 'classified-listing' ),
				'help_text' => __( 'If you enable this it will create new if not exist.', 'classified-listing' ),
			],
			'allow_whatsapp'               => [
				'template' => 'inputYesNoCheckBox',
				'label'    => __( 'Link with WhatsApp', 'classified-listing' )
			],
			'required'                     => [
				'template' => 'inputYesNoCheckBox',
				'label'    => __( 'Required', 'classified-listing' ),
			],
			'user_social_default'          => [
				'template'  => 'inputYesNoCheckBox',
				'key'       => 'default_value',
				'value'     => '{user.meta._rtcl_social}',
				'label'     => __( 'Default value from current user', 'classified-listing' ),
				'help_text' => __( 'This will pre-populate the value from user', 'classified-listing' ),
			],
			'admin_use_only'               => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Admin use only', 'classified-listing' ),
				'help_text' => __( 'If you enable this then filed will only display at admin end.', 'classified-listing' ),
			],
			'manipulation'                 => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Allow user to update / manipulation image', 'classified-listing' ),
				'help_text' => __( 'If you enable this then user can manipulation image.', 'classified-listing' ),
			],
			'filterable'                   => [
				'isPro'     => true,
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Filterable', 'classified-listing' ),
				'help_text' => __( 'If you enable this then filed will allow to filter the listing.', 'classified-listing' ),
			],
			'filterable_disable_logic'     => [
				'isPro'      => true,
				'template'   => 'inputYesNoCheckBox',
				'label'      => __( 'Disable Conditional Logic for Filterable', 'classified-listing' ),
				'help_text'  => __( 'If you enable this field will not check the conditional logic.', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'filterable',
					'value'      => true,
					'operator'   => '==',
				],
			],
			'archive_view'                 => [
				'isPro'     => true,
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Display at archive page', 'classified-listing' ),
				'help_text' => __( 'if enable then display this field at archive listing view.', 'classified-listing' ),
			],
			'single_view'                  => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Display at single page', 'classified-listing' ),
				'help_text' => __( 'if enable then display this field at single listing view.', 'classified-listing' ),
			],
			'select_filter_option'         => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Enable filter Option', 'classified-listing' ),
				'help_text' => __( 'If you enable this then options will be searchable', 'classified-listing' ),
			],
			'subscription_options'         => [
				'template'  => 'subscriptionOptions',
				'label'     => __( 'Subscription Items', 'classified-listing' ),
				'help_text' => __( 'Set your subscription plans', 'classified-listing' ),
			],
			'validation'                   => [
				'template'  => 'validation',
				'label'     => __( 'Validation Rules', 'classified-listing' ),
				'help_text' => '',
			],
			'required_field_message'       => [
				'template'  => 'inputRequiredFieldText',
				'label'     => __( 'Required validation Message', 'classified-listing' ),
				'help_text' => 'Message for failed validation for this field',
			],
			'tnc_html'                     => [
				'template'  => 'inputHTML',
				'label'     => __( 'Terms & Conditions', 'classified-listing' ),
				'help_text' => __( 'Write HTML content for terms & condition checkbox', 'classified-listing' ),
				'rows'      => 4,
				'cols'      => 2,
			],
			'pnc_html'                     => [
				'template'  => 'inputHTML',
				'label'     => __( 'Privacy and Policy', 'classified-listing' ),
				'help_text' => __( 'Write HTML content for Privacy and Policy checkbox', 'classified-listing' ),
				'rows'      => 4,
				'cols'      => 2,
			],
			'hook_name'                    => [
				'template'  => 'customHookName',
				'label'     => __( 'Hook Name', 'classified-listing' ),
				'help_text' => __( 'WordPress Hook name to hook something in this place.', 'classified-listing' ),
			],
			'has_checkbox'                 => [
				'template' => 'inputCheckbox',
				'label'    => __( 'Show Checkbox', 'classified-listing' ),
				// 'options'  => [
				// [
				// 'value' => true,
				// 'label' => __( 'Show Checkbox', 'classified-listing' ),
				// ],
				// ],
			],
			'html_codes'                   => [
				'template'  => 'inputHTML',
				'rows'      => 4,
				'cols'      => 2,
				'label'     => __( 'HTML Code', 'classified-listing' ),
				'help_text' => __( 'Your valid HTML code will be shown to the user as normal content.', 'classified-listing' ),
			],
			'description'                  => [
				'template'  => 'inputHTML',
				'rows'      => 4,
				'cols'      => 2,
				'label'     => __( 'Description', 'classified-listing' ),
				'help_text' => __( 'Description will be shown to the user as normal text content.', 'classified-listing' ),
			],
			'btn_text'                     => [
				'template'  => 'inputText',
				'label'     => __( 'Button Text', 'classified-listing' ),
				'help_text' => __( 'This will be visible as button text for upload file.', 'classified-listing' ),
			],
			'button_ui'                    => [
				'template'  => 'prevNextButton',
				'label'     => __( 'Submit Button', 'classified-listing' ),
				'help_text' => __( 'This is form submission button.', 'classified-listing' ),
			],
			'align'                        => [
				'template'  => 'radio',
				'label'     => __( 'Content Alignment', 'classified-listing' ),
				'help_text' => __( 'How the content will be aligned.', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'left',
						'label' => __( 'Left', 'classified-listing' ),
					],
					[
						'value' => 'center',
						'label' => __( 'Center', 'classified-listing' ),
					],
					[
						'value' => 'right',
						'label' => __( 'Right', 'classified-listing' ),
					],
				],
			],
			'shortcode'                    => [
				'template'  => 'inputText',
				'label'     => __( 'Shortcode', 'classified-listing' ),
				'help_text' => __( 'Your shortcode to render desired content in current place.', 'classified-listing' ),
			],
			'apply_styles'                 => [
				'template'  => 'radio',
				'label'     => __( 'Apply Styles', 'classified-listing' ),
				'help_text' => __( 'Apply styles provided here', 'classified-listing' ),
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
			'step_title'                   => [
				'template'  => 'inputText',
				'label'     => __( 'Step Title', 'classified-listing' ),
				'help_text' => __( 'Form step titles, user will see each title in each step.', 'classified-listing' ),
			],
			'disable_auto_focus'           => [
				'template'  => 'switch',
				'label'     => __( 'Disable auto focus when changing each page', 'classified-listing' ),
				'help_text' => __( 'If you enable this then on page transition automatic scrolling will be disabled', 'classified-listing' ),
			],
			'enable'                       => [
				'template' => 'switch',
				'label'    => __( 'Enable', 'classified-listing' ),
			],
			'multiple'                     => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Multi Selection', 'classified-listing' ),
				'help_text' => __( 'User can able to select multiple item.', 'classified-listing' ),
			],
			'enable_auto_slider'           => [
				'template'  => 'switch',
				'label'     => __( 'Enable auto page change for single radio field', 'classified-listing' ),
				'help_text' => __( 'If you enable this then for last radio item field will trigger next page change', 'classified-listing' ),
			],
			'enable_step_data_persistency' => [
				'template'  => 'switch',
				'label'     => __( 'Enable Per step data save (Save and Continue)', 'classified-listing' ),
				'help_text' => __( 'If you enable this then on each step change the data current step data will be persisted in a step form<br />Your users can resume the form where they left', 'classified-listing' ),
			],
			'enable_step_page_resume'      => [
				'template'   => 'switch',
				'label'      => __( 'Resume Step from last form session', 'classified-listing' ),
				'help_text'  => __( 'If you enable this then users will see the form as step page where it has been left', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'enable_step_data_persistency',
					'value'      => 'yes',
					'operator'   => '==',
				],
			],
			'progress_indicator'           => [
				'template'  => 'radio',
				'label'     => __( 'Progress Indicator', 'classified-listing' ),
				'help_text' => __( 'Select any of them below, user will see progress of form steps according to your choice.', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'progress-bar',
						'label' => __( 'Progress Bar', 'classified-listing' ),
					],
					[
						'value' => 'steps',
						'label' => __( 'Steps', 'classified-listing' ),
					],
					[
						'value' => '',
						'label' => __( 'None', 'classified-listing' ),
					],
				],
			],
			'step_titles'                  => [
				'template'  => 'customStepTitles',
				'label'     => __( 'Step Titles', 'classified-listing' ),
				'help_text' => __( 'Form step titles, user will see each title in each step.', 'classified-listing' ),
			],
			'prev_btn'                     => [
				'template'  => 'prevNextButton',
				'label'     => __( 'Previous Button', 'classified-listing' ),
				'help_text' => __( 'Multi-step form\'s previous button', 'classified-listing' ),
			],
			'next_btn'                     => [
				'template'  => 'prevNextButton',
				'label'     => __( 'Next Button', 'classified-listing' ),
				'help_text' => __( 'Multi-step form\'s next button', 'classified-listing' ),
			],
			'address_fields'               => [
				'template' => 'addressFields',
				'label'    => __( 'Address Fields', 'classified-listing' ),
				'key'      => 'country_list',
			],
			'name_fields'                  => [
				'template' => 'nameFields',
				'label'    => __( 'Name Fields', 'classified-listing' ),
			],
			'multi_column'                 => [
				'template' => 'inputYesNoCheckBox',
				'label'    => __( 'Enable Multiple Columns', 'classified-listing' )
			],
			'repeater_collapsable'         => [
				'key'         => 'collapsable',
				'template'    => 'switch',
				'label'       => __( 'Enable Accordion?', 'classified-listing' ),
				'description' => __( 'The first repeater item will serve as the accordion title. Please add a text field for this item.', 'classified-listing' )
			],
			'repeater_layout'              => [
				'template'    => 'select',
				'key'         => 'layout',
				'label'       => __( 'Choose Layout', 'classified-listing' ),
				'placeholder' => __( '- Select -', 'classified-listing' ),
				'description' => __( 'Ue this filter to extend layout add_filter("rtcl_form_repeater_layout_options")', 'classified-listing' ),
				'options'     => apply_filters( 'rtcl_form_repeater_layout_options', [
					[
						'value' => 'default',
						'label' => __( 'Default', 'classified-listing' ),
					],
					[
						'value' => 'image_box',
						'label' => __( 'Image Box', 'classified-listing' ),
					],
				] ),
			],
			'repeat_fields'                => [
				'template'  => 'repeater',
				'label'     => __( 'Repeat Fields', 'classified-listing' ),
				'help_text' => __( 'This is a form field which a user will be able to repeat.', 'classified-listing' ),
			],
			'admin_field_label'            => [
				'template'  => 'inputText',
				'label'     => __( 'Admin Field Label', 'classified-listing' ),
				'help_text' => __( 'Admin field label is field title which will be used for admin field title.', 'classified-listing' ),
			],
			'maxlength'                    => [
				'template'  => 'inputNumber',
				'label'     => __( 'Max text length', 'classified-listing' ),
				'help_text' => __( 'The maximum number of characters the input should accept', 'classified-listing' ),
			],
			'minlength'                    => [
				'template'  => 'inputNumber',
				'label'     => __( 'Min text length', 'classified-listing' ),
				'help_text' => __( 'The maximum number of characters the input should accept', 'classified-listing' ),
			],
			'default_checked'              => [
				'template'  => 'switch',
				'key'       => 'default_value',
				'label'     => __( 'Default Checked', 'classified-listing' ),
				'help_text' => __( 'Default checked.', 'classified-listing' ),
			],
			'default_value'                => [
				'template'  => 'inputValue',
				'label'     => __( 'Default Value', 'classified-listing' ),
				'help_text' => __( 'If you would like to pre-populate the value of a field, enter it here.', 'classified-listing' ),
			],
			'dynamic_default_value'        => [
				'template'  => 'inputValue',
				'type'      => 'text',
				'label'     => __( 'Dynamic Default Value', 'classified-listing' ),
				'help_text' => __( 'If you would like to pre-populate the value of a field, enter it here.', 'classified-listing' ),
			],
			'max_selection'                => [
				'template'   => 'inputNumber',
				'type'       => 'text',
				'label'      => __( 'Max Selection', 'classified-listing' ),
				'help_text'  => __( 'Define Max selections items that a user can select .', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'multiple',
					'value'      => true,
					'operator'   => '==',
				],
			],
			'container_class'              => [
				'template'  => 'inputText',
				'label'     => __( 'Container Class', 'classified-listing' ),
				'help_text' => __( 'Class for the field wrapper. This can be used to style current element.', 'classified-listing' ),
			],
			'id'                           => [
				'template'  => 'inputText',
				'label'     => __( 'ID', 'classified-listing' ),
				'help_text' => __( 'This should be unique id', 'classified-listing' ),
			],
			'class'                        => [
				'template'  => 'inputText',
				'label'     => __( 'Element Class', 'classified-listing' ),
				'help_text' => __( 'Class for the field. This can be used to style current element.', 'classified-listing' ),
			],
			'country_list'                 => [
				'template' => 'customCountryList',
				'label'    => __( 'Country List', 'classified-listing' ),
				'key'      => 'country_list',
			],
			'product_field_types'          => [
				'template' => 'productFieldTypes',
				'label'    => __( 'Options', 'classified-listing' ),
			],
			'help_message'                 => [
				'template'  => 'inputTextarea',
				'label'     => __( 'Help Message', 'classified-listing' ),
				'help_text' => __( 'Help message will be shown as tooltip next to sidebar or below the field.', 'classified-listing' ),
			],
			'logics'                       => [
				'template'     => 'conditionalLogics',
				'label'        => __( 'Conditional Logic', 'classified-listing' ),
				'help_text'    => __( 'Create rules to dynamically display or hide this field based on values from another field.', 'classified-listing' ),
				'relation'     => [
					'label'     => __( 'Relation', 'classified-listing' ),
					'help_text' => __( 'Select to match whether all rules are required or any. if the match success then the field will be show', 'classified-listing' ),
					'options'   => [
						[
							'value' => 'or',
							'label' => __( 'OR', 'classified-listing' )
						],
						[
							'value' => 'and',
							'label' => __( 'AND', 'classified-listing' )
						]
					]
				],
				'operators'    => [
					[
						'value' => '=',
						'label' => __( 'Equal', 'classified-listing' )
					],
					[
						'value' => '!=',
						'label' => __( 'Not equal', 'classified-listing' )
					],
					[
						'value' => '>',
						'label' => __( 'greater than', 'classified-listing' )
					],
					[
						'value' => '<',
						'label' => __( 'less than', 'classified-listing' )
					],
					[
						'value' => '>=',
						'label' => __( 'greater than or equal', 'classified-listing' )
					],
					[
						'value' => '<=',
						'label' => __( 'less than or equal', 'classified-listing' )
					],
					[
						'value' => 'contains',
						'label' => __( 'Includes', 'classified-listing' )
					],
					[
						'value' => 'doNotContains',
						'label' => __( 'Not includes', 'classified-listing' )
					],
					[
						'value' => 'startsWith',
						'label' => __( 'Starts with', 'classified-listing' )
					],
					[
						'value' => 'endsWith',
						'label' => __( 'Ends with', 'classified-listing' )
					],
					[
						'value' => 'empty',
						'label' => __( 'Empty', 'classified-listing' )
					],
					[
						'value' => 'notEmpty',
						'label' => __( 'Not Empty', 'classified-listing' )
					],
					[
						'value' => 'test_regex',
						'label' => __( 'Regex match', 'classified-listing' )
					],
				],
				'selectFields' => [
					'listing_type',
					'location',
					'category',
					'select',
					'radio',
					'checkbox',
					'terms_and_condition'
				]
			],
			'background_color'             => [
				'template'  => 'inputColor',
				'label'     => __( 'Background Color', 'classified-listing' ),
				'help_text' => __( 'The Background color of the element', 'classified-listing' ),
			],
			'color'                        => [
				'template'  => 'inputColor',
				'label'     => __( 'Font Color', 'classified-listing' ),
				'help_text' => __( 'Font color of the element', 'classified-listing' ),
			],
			'data-mask'                    => [
				'template'   => 'customMask',
				'label'      => __( 'Custom Mask', 'classified-listing' ),
				'help_text'  => __( 'Write your own mask for this input', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'temp_mask',
					'value'      => 'custom',
					'operator'   => '==',
				],
			],
			'data-mask-reverse'            => [
				'template'   => 'switch',
				'label'      => __( 'Activating a reversible mask', 'classified-listing' ),
				'help_text'  => __( 'If you enable this then it the mask will work as reverse', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'temp_mask',
					'value'      => 'custom',
					'operator'   => '==',
				],
			],
			'randomize_options'            => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Shuffle the available options', 'classified-listing' ),
				'help_text' => __( 'If you enable this then the checkable options will be shuffled', 'classified-listing' ),
			],
			'data-clear-if-not-match'      => [
				'template'   => 'inputYesNoCheckBox',
				'label'      => __( 'Clear if not match', 'classified-listing' ),
				'help_text'  => __( 'Clear value if not match the mask', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'temp_mask',
					'value'      => 'custom',
					'operator'   => '==',
				],
			],
			'allow_hide_map'               => [
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Allow hide map', 'classified-listing' ),
				'help_text' => __( 'Allow user to hide map', 'classified-listing' )
			],
			'visible_lat_lng'              => [
				'template' => 'inputYesNoCheckBox',
				'label'    => __( 'Visible latitude and longitude field', 'classified-listing' ),
			],
			'temp_mask'                    => [
				'template'  => 'select',
				'label'     => __( 'Mask Input', 'classified-listing' ),
				'help_text' => __( 'Select a mask for the input field', 'classified-listing' ),
				'options'   => [
					[
						'value' => '',
						'label' => __( 'None', 'classified-listing' ),
					],
					[
						'value' => '(000) 000-0000',
						'label' => '(###) ###-####',
					],
					[
						'value' => '(00) 0000-0000',
						'label' => '(##) ####-####',
					],
					[
						'value' => '00/00/0000',
						'label' => __( '23/03/2018', 'classified-listing' ),
					],
					[
						'value' => '00:00:00',
						'label' => __( '23:59:59', 'classified-listing' ),
					],
					[
						'value' => '00/00/0000 00:00:00',
						'label' => __( '23/03/2018 23:59:59', 'classified-listing' ),
					],
					[
						'value' => 'custom',
						'label' => __( 'Custom', 'classified-listing' ),
					],
				],
			],
			'grid_columns'                 => [
				'template'  => 'gridRowCols',
				'label'     => __( 'Grid Columns', 'classified-listing' ),
				'help_text' => __( 'Write your own mask for this input', 'classified-listing' ),
			],
			'grid_rows'                    => [
				'template'  => 'gridRowCols',
				'label'     => __( 'Grid Rows', 'classified-listing' ),
				'help_text' => __( 'Write your own mask for this input', 'classified-listing' ),
			],
			'tabular_field_type'           => [
				'template'  => 'radio',
				'label'     => __( 'Field Type', 'classified-listing' ),
				'help_text' => __( 'Field Type', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'checkbox',
						'label' => __( 'Checkbox', 'classified-listing' ),
					],
					[
						'value' => 'radio',
						'label' => __( 'Radio', 'classified-listing' ),
					],
				],
			],
			'max_repeat_field'             => [
				'template'  => 'inputNumber',
				'label'     => __( 'Max Repeat inputs', 'classified-listing' ),
				'help_text' => __( 'Please provide max number of rows the user can fill up for this repeat field. Keep blank/0 for unlimited numbers', 'classified-listing' ),
			],
			'min'                          => [
				'template'  => 'inputNumber',
				'label'     => __( 'Min Value', 'classified-listing' ),
				'help_text' => __( 'Please provide minimum value', 'classified-listing' ),
			],
			'max'                          => [
				'template'  => 'inputNumber',
				'label'     => __( 'Max Value', 'classified-listing' ),
				'help_text' => __( 'Please provide Maximum value', 'classified-listing' ),
			],
			'digits'                       => [
				'template'  => 'inputNumber',
				'label'     => __( 'Digits Count', 'classified-listing' ),
				'help_text' => __( 'Please provide digits count value', 'classified-listing' ),
			],
			'number_step'                  => [
				'template'  => 'inputText',
				'label'     => __( 'Step', 'classified-listing' ),
				'help_text' => __( 'Please provide step attribute for this field. Give value "any" for floating value', 'classified-listing' ),
			],
			'prefix_label'                 => [
				'template'  => 'inputText',
				'label'     => __( 'Prefix Label', 'classified-listing' ),
				'help_text' => __( 'Provide Input Prefix Label. It will show in the input field as prefix label', 'classified-listing' ),
			],
			'suffix_label'                 => [
				'template'  => 'inputText',
				'label'     => __( 'Suffix Label', 'classified-listing' ),
				'help_text' => __( 'Provide Input Suffix Label. It will show in the input field as suffix label', 'classified-listing' ),
			],
			'is_unique'                    => [
				'template'  => 'switch',
				'label'     => __( 'Validate as Unique', 'classified-listing' ),
				'help_text' => __( 'If you make it unique then it will validate as unique from previous submissions of this form', 'classified-listing' ),
			],
			'show_text'                    => [
				'template'  => 'select',
				'label'     => __( 'Show Text', 'classified-listing' ),
				'help_text' => __( 'Show Text value on selection', 'classified-listing' ),
				'options'   => [
					[
						'value' => 'yes',
						'label' => __( 'Yes', 'classified-listing' ),
					],
					[
						'value' => 'no',
						'label' => __( 'No', 'classified-listing' ),
					],
				],
			],
			'numeric_formatter'            => [
				'template'  => 'select',
				'label'     => __( 'Number Format', 'classified-listing' ),
				'help_text' => __( 'Select the format of numbers that are allowed in this field. You have the option to use a comma or a dot as the decimal separator.', 'classified-listing' ),
				'options'   => Helper::getNumericFormatters(),
			],
			'unique_validation_message'    => [
				'template'   => 'inputText',
				'label'      => __( 'Validation Message for Duplicate', 'classified-listing' ),
				'help_text'  => __( 'If validation failed then it will show this message', 'classified-listing' ),
				'dependency' => [
					'depends_on' => 'is_unique',
					'value'      => 'yes',
					'operator'   => '==',
				],
			],
			'layout_class'                 => [
				'template'  => 'select',
				'label'     => __( 'Layout', 'classified-listing' ),
				'help_text' => __( 'Select the Layout for check able items', 'classified-listing' ),
				'options'   => [
					[
						'value' => '',
						'label' => __( 'Default', 'classified-listing' ),
					],
					[
						'value' => 'ff_list_inline',
						'label' => 'Inline Layout',
					],
					[
						'value' => 'ff_list_buttons',
						'label' => 'Button Type Styles',
					],
					[
						'value' => 'ff_list_2col',
						'label' => '2-Column Layout',
					],
					[
						'value' => 'ff_list_3col',
						'label' => '3-Column Layout',
					],
					[
						'value' => 'ff_list_4col',
						'label' => '4-Column Layout',
					],
					[
						'value' => 'ff_list_5col',
						'label' => '5-Column Layout',
					],
				],
			],
			'ai'                           => [
				'isPro'     => true,
				'template'  => 'inputYesNoCheckBox',
				'label'     => __( 'Enable AI Integration', 'classified-listing' ),
				'help_text' => __( 'Toggle this option to enable or disable AI-powered features for this listing.', 'classified-listing' ),
			],
			'file_location'                => [
				'template'  => 'radio',
				'label'     => __( 'File Location', 'classified-listing' ),
				'help_text' => __( 'Uploaded files can be stored in classified-listing or media library.', 'classified-listing' ),
				'default'   => 'default',
				'options'   => Helper::fileUploadLocations(),
			],
			'section_title'                => [
				'key'       => 'title',
				'template'  => 'inputText',
				'label'     => __( 'Section title', 'classified-listing' ),
				'help_text' => __( 'Set the section title.', 'classified-listing' ),
			],
			'column'                       => [
				'template'       => 'column',
				'label'          => __( 'Column Settings', 'classified-listing' ),
				'help_text'      => __( 'Set the width of the columns. The minimum column width is 10%.', 'classified-listing' ),
				'limitation_msg' => __( 'The minimum column width is 10%, Maximum column number 4', 'classified-listing' ),
			]
		];

		return apply_filters( 'rtcl_fb_editor_settings_fields', $settingsFields );
	}

	/**
	 * @param $type
	 *
	 * @return mixed|null
	 */
	static function allowedHtml( $type = 'tnc_html' ) {
		$htmlTags = [
			'a'      => [
				'rel'    => [],
				'target' => [],
				'href'   => [],
				'title'  => []
			],
			'br'     => [],
			'em'     => [],
			'strong' => [],
		];

		return apply_filters( 'rtcl_fb_allowed_html', $htmlTags, $type );
	}
}