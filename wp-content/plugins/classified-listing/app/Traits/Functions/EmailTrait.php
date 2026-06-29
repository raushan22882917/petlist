<?php

namespace Rtcl\Traits\Functions;

use Rtcl\Helpers\Functions;

trait EmailTrait {
	public static function email_wrapper_style( $email ) {
		$bg = $email->get_option( 'email_background_color', '#f7f7f7' );

		$style = "overflow: hidden; background-color:$bg; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;";

		return $style;
	}

	public static function email_template_container_style( $email ) {
		$body         = $email->get_option( 'email_body_background_color', '#ffffff' );
		$bg           = $email->get_option( 'email_background_color', '#f7f7f7' );
		$bg_darker_10 = Functions::hex_darker( $bg, 10 );

		$style
			= "box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: <?php echo esc_attr($body); ?>; border: 1px solid <?php echo esc_attr($bg_darker_10); ?>; border-radius: 3px !important; width: 100%; max-width: 600px; table-layout: fixed";

		return $style;
	}

	public static function email_template_header_style( $email ) {
		$base      = $email->get_option( 'email_base_color', '#0071bd' );
		$base_text = Functions::light_or_dark( $base, '#202020', '#ffffff' );

		$style
			= "background-color: $base; border-radius: 3px 3px 0 0 !important; color: $base_text; border-bottom: 0;font-weight: bold; line-height: 100%;vertical-align: middle;width: 100%; max-width: 600px; table-layout: fixed; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;";

		return $style;
	}

	public static function email_template_header_h1_style( $email ) {
		$base      = $email->get_option( 'email_base_color', '#0071bd' );
		$base_text = Functions::light_or_dark( $base, '#202020', '#ffffff' );

		$style = "color: $base_text;";

		return $style;
	}

	public static function email_header_wrapper_style( $email ) {
		$style = "padding: 36px 48px; display: block;";

		return $style;
	}

	public static function email_body_content_style( $email ) {
		$body  = $email->get_option( 'email_body_background_color', '#ffffff' );
		$style = "background-color: $body;";

		return $style;
	}

	public static function email_body_content_inner_style( $email ) {
		$text            = $email->get_option( 'email_text_color', '#3c3c3c' );
		$text_lighter_20 = Functions::hex_lighter( $text, 20 );
		$text_align      = is_rtl() ? 'right' : 'left';

		$style
			= "color: $text_lighter_20; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: $text_align;";

		return $style;
	}

	public static function email_body_content_table_td_style( $email ) {
		return "padding: 48px 48px 0;";
	}

	public static function email_body_content_table_td_td_style( $email ) {
		$style = "padding: 12px;";

		return $style;
	}

	public static function email_body_content_table_td_th_style( $email ) {
		$style = "padding: 12px;";

		return $style;
	}

	public static function email_template_footer_td_style( $email ) {
		$style = "padding: 0; -webkit-border-radius: 6px;";

		return $style;
	}

	public static function email_template_footer_credit_style( $email ) {
		$base            = $email->get_option( 'email_base_color', '#0071bd' );
		$base_lighter_40 = Functions::hex_lighter( $base, 40 );

		$style = "border:0; color: $base_lighter_40; font-family: Arial; font-size:12px; line-height:125%; text-align:center; padding: 0 48px 48px 48px;";

		return $style;
	}

	public static function email_class_td_style( $email ) {
		$body            = $email->get_option( 'email_body_background_color', '#ffffff' );
		$text            = $email->get_option( 'email_text_color', '#3c3c3c' );
		$body_darker_10  = Functions::hex_darker( $body, 10 );
		$text_lighter_20 = Functions::hex_lighter( $text, 20 );

		$style = "color: $text_lighter_20; border: 1px solid $body_darker_10; vertical-align: middle;";

		return $style;
	}

	public static function email_class_address_style( $email ) {
		$body            = $email->get_option( 'email_body_background_color', '#ffffff' );
		$text            = $email->get_option( 'email_text_color', '#3c3c3c' );
		$body_darker_10  = Functions::hex_darker( $body, 10 );
		$text_lighter_20 = Functions::hex_lighter( $text, 20 );

		$style = "padding:12px 12px 0; color: $text_lighter_20 ); border: 1px solid $body_darker_10;";

		return $style;
	}

	public static function email_class_link_style( $email ) {
		$base = $email->get_option( 'email_base_color', '#0071bd' );

		$style = "color: $base;";

		return $style;
	}

	public static function email_class_text_style( $email ) {
		$text = $email->get_option( 'email_text_color', '#3c3c3c' );

		$style = "color: $text; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;";

		return $style;
	}

	public static function email_image_style( $email ) {
		$align = is_rtl() ? 'left' : 'right';

		$style
			= "border: none; display: inline-block; font-size: 14px; font-weight: bold; height: auto; outline: none; text-decoration: none;text-transform: capitalize;vertical-align: middle;margin-$align: 10px;";

		return $style;
	}

	public static function email_a_style( $email ) {
		$base      = $email->get_option( 'email_base_color', '#0071bd' );
		$body      = $email->get_option( 'email_body_background_color', '#ffffff' );
		$base_text = Functions::light_or_dark( $base, '#202020', '#ffffff' );
		$link      = Functions::hex_is_light( $base ) ? $base : $base_text;

		if ( Functions::hex_is_light( $body ) ) {
			$link = Functions::hex_is_light( $base ) ? $base_text : $base;
		}

		$style = "color: $link; font-weight: normal; text-decoration: underline;";

		return $style;
	}

	public static function email_h1_style( $email ) {
		$base            = $email->get_option( 'email_base_color', '#0071bd' );
		$text_align      = is_rtl() ? 'right' : 'left';
		$base_lighter_20 = Functions::hex_lighter( $base, 20 );

		$style
			= "color: $base; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: $text_align; text-shadow: 0 1px 0 $base_lighter_20;";

		return $style;
	}

	public static function email_h2_style( $email ) {
		$base            = $email->get_option( 'email_base_color', '#0071bd' );
		$text_align      = is_rtl() ? 'right' : 'left';

		$style = "color: $base; display: block; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: $text_align;";

		return $style;
	}

}