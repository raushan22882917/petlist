<?php

namespace Rtcl\Services\FormBuilder;

class EditorShortCode {
	public static function getGeneralShortCodes() {
		return [
			'title'      => 'General SmartCodes',
			'shortcodes' => [
				'{get.param_name}'                  => __( 'Populate by GET Param', 'classified-listing' ),
				'{wp.admin_email}'                  => __( 'Admin Email', 'classified-listing' ),
				'{wp.site_url}'                     => __( 'Site URL', 'classified-listing' ),
				'{wp.site_title}'                   => __( 'Site Title', 'classified-listing' ),
				'{ip}'                              => __( 'IP Address', 'classified-listing' ),
				'{date.m/d/Y}'                      => __( 'Date (mm/dd/yyyy)', 'classified-listing' ),
				'{date.d/m/Y}'                      => __( 'Date (dd/mm/yyyy)', 'classified-listing' ),
				'{embed_post.ID}'                   => __( 'Embedded Post/Page ID', 'classified-listing' ),
				'{embed_post.post_title}'           => __( 'Embedded Post/Page Title', 'classified-listing' ),
				'{embed_post.permalink}'            => __( 'Embedded URL', 'classified-listing' ),
				'{http_referer}'                    => __( 'HTTP Referer URL', 'classified-listing' ),
				'{user.ID}'                         => __( 'User ID', 'classified-listing' ),
				'{user.display_name}'               => __( 'User Display Name', 'classified-listing' ),
				'{user.first_name}'                 => __( 'User First Name', 'classified-listing' ),
				'{user.last_name}'                  => __( 'User Last Name', 'classified-listing' ),
				'{user.user_email}'                 => __( 'User Email', 'classified-listing' ),
				'{user.user_login}'                 => __( 'User Username', 'classified-listing' ),
				'{user.meta._rtcl_phone}'           => __( 'User Phone', 'classified-listing' ),
				'{user.meta._rtcl_whatsapp_number}' => __( 'User Whatsapp', 'classified-listing' ),
				'{user.meta._rtcl_address}'         => __( 'User Address', 'classified-listing' ),
				'{user.meta._rtcl_zipcode}'         => __( 'User Zipcode', 'classified-listing' ),
				'{user.meta._rtcl_website}'         => __( 'User Website', 'classified-listing' ),
				'{browser.name}'                    => __( 'User Browser Client', 'classified-listing' ),
				'{browser.platform}'                => __( 'User Operating System', 'classified-listing' ),
				'{random_string.your_prefix}'       => __( 'Random String with Prefix', 'classified-listing' ),
				'{cookie.cookie_name}'              => __( 'Cookie Value', 'classified-listing' )
			],
		];
	}


	public static function parse( $string, $data, callable $arrayFormatter = null ) {
		if ( is_array( $string ) ) {
			return static::parseArray( $string, $data, $arrayFormatter );
		}

		return static::parseString( $string, $data, $arrayFormatter );
	}

	public static function parseArray( $string, $data, $arrayFormatter ) {
		foreach ( $string as $key => $value ) {
			if ( is_array( $value ) ) {
				$string[ $key ] = static::parseArray( $value, $data, $arrayFormatter );
			} else {
				$string[ $key ] = static::parseString( $value, $data, $arrayFormatter );
			}
		}

		return $string;
	}

	public static function parseString( $string, $data, callable $arrayFormatter = null ) {
		return preg_replace_callback( '/{+(.*?)}/',
			function ( $matches ) use ( &$data, &$arrayFormatter ) {
				if ( ! isset( $data[ $matches[1] ] ) ) {
					return $matches[0];
				} elseif ( is_array( $value = $data[ $matches[1] ] ) ) {
					return is_callable( $arrayFormatter ) ? $arrayFormatter( $value ) : implode( ', ', $value );
				}
				return $data[ $matches[1] ];
			},
		$string );
	}
}
