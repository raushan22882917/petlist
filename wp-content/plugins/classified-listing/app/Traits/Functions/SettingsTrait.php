<?php


namespace Rtcl\Traits\Functions;

trait SettingsTrait {
	static function get_privacy_policy_page_id() {
		$page_id = self::get_option_item( 'rtcl_account_settings', 'page_for_privacy_policy', 0 );

		return apply_filters( 'rtcl_privacy_policy_page_id', 0 < $page_id ? absint( $page_id ) : 0 );
	}

	static function get_terms_and_conditions_page_id() {
		$page_id = self::get_option_item( 'rtcl_account_settings', 'page_for_terms_and_conditions', 0 );

		return apply_filters( 'rtcl_terms_and_conditions_page_id', 0 < $page_id ? absint( $page_id ) : 0 );
	}

	public static function get_listings_default_view() {
		$default_view = self::get_option_item( 'rtcl_archive_listing_settings', 'default_view', 'list' );

		return apply_filters( 'rtcl_archive_listings_default_view', $default_view );
	}

	public static function get_map_view_center_position() {
		return self::get_option_item( 'rtcl_misc_map_settings', 'map_view_center_position', 'crowded' );
	}

	public static function get_listings_per_row() {
		$per_row = wp_parse_args(
			self::get_option_item( 'rtcl_archive_listing_settings', 'listings_per_row' ),
			[
				'desktop' => 3,
				'tablet'  => 2,
				'mobile'  => 1,
			],
		);

		return apply_filters( 'rtcl_archive_listings_grid_view_per_row', $per_row );
	}

	public static function get_listing_details_disable_settings() {
		$disable_single = self::get_option_item( 'rtcl_single_listing_settings', 'disable_single_listing', false, 'checkbox' );

		return apply_filters( 'rtcl_single_listing_disable_option', $disable_single );
	}

	public static function get_base_template() {
		$base_template = self::get_option_item( 'rtcl_advanced_settings', 'template_base', 'rtcl_template' );

		return apply_filters( 'rtcl_listing_base_template', $base_template );
	}

	/**
	 * @return bool
	 */
	public static function is_user_type_enabled() {
		$enabled = self::get_option_item( 'rtcl_account_settings', 'enable_user_type', 'no' );

		return $enabled === 'yes';
	}

	/**
	 * @return bool|int|mixed|null
	 */
	public static function get_user_type_seller_label() {
		return self::get_option_item( 'rtcl_account_settings', 'seller_user_type_label', 'Seller' );
	}

	/**
	 * @return bool|int|mixed|null
	 */
	public static function get_user_type_buyer_label() {
		return self::get_option_item( 'rtcl_account_settings', 'buyer_user_type_label', 'Buyer' );
	}

	/**
	 * @param  string  $user_type
	 *
	 * @return string
	 */
	public static function get_user_type_label( string $user_type ): string {
		return $user_type === 'buyer' ? self::get_user_type_buyer_label() : self::get_user_type_seller_label();
	}

	/**
	 * @return bool
	 */
	public static function is_semantic_search_enabled(): bool {
		if ( ! self::is_openai_enabled() && ! self::is_gemini_enabled() ) {
			return false;
		}

		$enabled = self::get_option_item( 'rtcl_ai_settings', 'semantic_search', 'no' );

		return $enabled === 'yes';
	}

	/**
	 * @return bool
	 */
	public static function is_semantic_quick_search_enabled(): bool {
		if ( ! self::is_semantic_search_enabled() ) {
			return false;
		}

		$enabled = self::get_option_item( 'rtcl_ai_settings', 'enable_ai_quick_search', 'no' );

		return $enabled === 'yes';
	}

	/**
	 * @return bool
	 */
	public static function is_image_enhancement_enabled(): bool {
		if ( ! self::is_gemini_enabled() ) {
			return false;
		}

		$enabled = self::get_option_item( 'rtcl_ai_settings', 'image_enhancement', 'no' );

		return $enabled === 'yes';
	}

	/**
	 * @return bool
	 */
	public static function is_openai_enabled(): bool {
		$ai_tools = self::get_ai_client();

		return $ai_tools === 'openai';
	}

	/**
	 * @return bool
	 */
	public static function is_gemini_enabled(): bool {
		$ai_tools = self::get_ai_client();

		return $ai_tools === 'gemini';
	}

	/**
	 * @return bool
	 */
	public static function is_deepseek_enabled(): bool {
		$ai_tools = self::get_ai_client();

		return $ai_tools === 'deepseek';
	}

	/**
	 * @return bool
	 */
	public static function is_claude_enabled(): bool {
		$ai_tools = self::get_ai_client();

		return $ai_tools === 'claude';
	}

	/**
	 * @return string
	 */
	public static function get_ai_client() {
		$ai_client = self::get_option_item( 'rtcl_ai_settings', 'ai_tools' );

		return ! empty( $ai_client ) ? strtolower( $ai_client ) : '';
	}

	/**
	 * @return float|int
	 */
	public static function get_embedding_minimum_accuracy() {
		$minimum_matching = (int) self::get_option_item( 'rtcl_ai_settings', 'minimum_matching_percentage', 40 );

		return ! empty( $minimum_matching ) ? $minimum_matching / 100 : 0.4;
	}

	/**
	 * @return float|int
	 */
	public static function get_embedding_best_matching() {
		$best_matching = (int) self::get_option_item( 'rtcl_ai_settings', 'best_matching_percentage', 75 );

		return ! empty( $best_matching ) ? $best_matching / 100 : 0.75;
	}

}
