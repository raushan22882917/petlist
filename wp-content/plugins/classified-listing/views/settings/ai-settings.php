<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for AI Integration
 */

$options = array(
	'ls_section'         => array(
		'title'       => esc_html__( 'AI Integration Settings', 'classified-listing' ),
		'type'        => 'section',
		'description' => wp_kses(
			__( 'To integrate with Write with AI services, you need to obtain an API key from your chosen provider. Visit the respective service provider’s website to generate an API key.',
				'classified-listing' ),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		),
	),
	'ai_tools'           => [
		'title'   => esc_html__( 'AI Tools', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'OpenAI',
		'options' => [
			'OpenAI'   => esc_html__( 'ChatGPT', 'classified-listing' ),
			'Gemini'   => esc_html__( 'Google Gemini', 'classified-listing' ),
			'DeepSeek' => esc_html__( 'DeepSeek', 'classified-listing' ), // Added DeepSeek option
		],
	],
	'gpt_models'         => [
		'title'      => esc_html__( 'GPT Model', 'classified-listing' ),
		'type'       => 'select',
		'default'    => 'gpt-4o',
		'required'   => true,
		'options'    => [
			'gpt-4o'      => esc_html__( 'GPT-4o (Full Version)', 'classified-listing' ),
			'gpt-4o-mini' => esc_html__( 'GPT-4o Mini (Light Version)', 'classified-listing' ),
		],
		'dependency' => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'OpenAI',
				],
			],
		],
	],
	'gpt_api_key'        => [
		'title'       => esc_html__( 'ChatGPT API Key', 'classified-listing' ),
		'type'        => 'password',
		'description' => wp_kses(
			__( 'To integrate with ChatGPT, you need to obtain an API key from OpenAI. Visit <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI API Keys</a> to generate one.',
				'classified-listing' ),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		),
		'default'     => '',
		'required'    => true,
		'placeholder' => esc_html__( 'sk-p********', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'OpenAI',
				],
			],
		],
	],
	'gpt_max_token'      => [
		'title'       => esc_html__( 'Maximum Characters in Prompt Input', 'classified-listing' ),
		'type'        => 'number',
		'description' => esc_html__( 'Set the maximum character limit for the prompt input. This controls how many characters users can enter, ensuring input stays concise and manageable. Higher limits may affect performance and response quality.',
			'classified-listing' ),
		'default'     => '200',
		'required'    => true,
		'placeholder' => esc_html__( '500', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'OpenAI',
				],
			],
		],
	],
	'gemini_api_key'     => [
		'title'       => esc_html__( 'Gemini API Key', 'classified-listing' ),
		'type'        => 'password',
		'description' => wp_kses(
			__( 'To integrate with Google Gemini, you need to obtain an API key from Google Cloud. Visit <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio API Keys</a> to generate one.',
				'classified-listing' ),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		),
		'default'     => '',
		'required'    => true,
		'placeholder' => esc_html__( 'AIzaSy***********************', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'Gemini',
				],
			],
		],
	],
	'gemini_max_token'   => [
		'title'       => esc_html__( 'Maximum Characters in Prompt Input', 'classified-listing' ),
		'type'        => 'number',
		'description' => esc_html__( 'Set the maximum character limit for the prompt input. This controls how many characters users can enter, ensuring input stays concise and manageable. Higher limits may affect performance and response quality.',
			'classified-listing' ),
		'default'     => '200',
		'required'    => true,
		'placeholder' => esc_html__( '500', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'Gemini',
				],
			],
		],
	],
	'deepseek_api_key'   => [
		'title'       => esc_html__( 'DeepSeek API Key', 'classified-listing' ),
		'type'        => 'password',
		'description' => wp_kses(
			__( 'To integrate with DeepSeek, you need to obtain an API key from DeepSeek. Visit <a href="https://www.deepseek.com/api-keys" target="_blank">DeepSeek API Keys</a> to generate one.',
				'classified-listing' ),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		),
		'default'     => '',
		'required'    => true,
		'placeholder' => esc_html__( 'ds-***********************', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'DeepSeek',
				],
			],
		],
	],
	'deepseek_models'    => [
		'title'      => esc_html__( 'DeepSeek Model', 'classified-listing' ),
		'type'       => 'select',
		'default'    => 'deepseek-4o',
		'required'   => true,
		'options'    => [
			'deepseek-chat'     => esc_html__( 'DeepSeek Chat (Full Version)', 'classified-listing' ),
			'deepseek-reasoner' => esc_html__( 'DeepSeek Reasoner Mini (Light Version)', 'classified-listing' ),
		],
		'dependency' => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'DeepSeek',
				],
			],
		],
	],
	'deepseek_max_token' => [
		'title'       => esc_html__( 'Maximum Characters in Prompt Input', 'classified-listing' ),
		'type'        => 'number',
		'description' => esc_html__( 'Set the maximum character limit for the prompt input. This controls how many characters users can enter, ensuring input stays concise and manageable. Higher limits may affect performance and response quality.',
			'classified-listing' ),
		'default'     => '200',
		'required'    => true,
		'placeholder' => esc_html__( '500', 'classified-listing' ),
		'dependency'  => [
			'rules' => [
				"select[id^=rtcl_ai_settings-ai_tools]" => [
					'type'  => 'equal',
					'value' => 'DeepSeek',
				],
			],
		],
	],
	
);

return apply_filters( 'rtcl_ai_settings_options', $options );