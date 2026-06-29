<?php

namespace Rtcl\Controllers\Admin\Meta;

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class UserMeta {

	public static function init() {
		add_action( 'show_user_profile', [ __CLASS__, 'user_profile_fields' ], 8 );
		add_action( 'edit_user_profile', [ __CLASS__, 'user_profile_fields' ], 8 );
		// For own profile update
		add_action( 'personal_options_update', [ __CLASS__, 'save_user_profile_fields' ] );
		// For others user profile update
		add_action( 'edit_user_profile_update', [ __CLASS__, 'save_user_profile_fields' ] );
	}

	public static function user_profile_fields( $user ) {
		$user_id        = $user->ID;
		$phone          = get_user_meta( $user_id, '_rtcl_phone', true );
		$website        = get_user_meta( $user_id, '_rtcl_website', true );
		$whatsapp       = get_user_meta( $user_id, '_rtcl_whatsapp_number', true );
		$address        = get_user_meta( $user_id, '_rtcl_address', true );
		$social_options = Options::get_social_profiles_list();
		$social_media   = $user_id ? Functions::get_user_social_profile( $user_id ) : [];
		$user_type      = get_user_meta( $user_id, '_rtcl_user_type', true );
		?>
		<h2><?php
			esc_html_e( "Additional Information", "classified-listing" ); ?></h2>

		<table class="form-table rtcl-user-info-wrapper">
			<?php
			if ( Functions::is_user_type_enabled() && current_user_can( 'manage_options' ) ): ?>
				<tr>
					<th>
						<label for="rtcl_user_type"><?php
							esc_html_e( "Account Type", "classified-listing" ); ?></label>
					</th>
					<td>
						<select name="rtcl_user_type" id="rtcl_user_type">
							<option value="">— <?php
								esc_html_e( 'Not specified', 'classified-listing' ); ?> —
							</option>
							<option value="seller" <?php
							selected( $user_type, 'seller' ); ?>><?php
								echo esc_html( Functions::get_user_type_seller_label() ); ?></option>
							<option value="buyer" <?php
							selected( $user_type, 'buyer' ); ?>><?php
								echo esc_html( Functions::get_user_type_buyer_label() ); ?></option>
						</select>
					</td>
				</tr>
			<?php
			endif; ?>
			<tr>
				<th>
					<label for="_rtcl_phone"><?php
						esc_html_e( "Phone", "classified-listing" ); ?></label>
				</th>
				<td>
					<input type="text" name="_rtcl_phone" id="_rtcl_phone" value="<?php
					echo esc_attr( $phone ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th>
					<label for="_rtcl_whatsapp_number"><?php
						esc_html_e( "WhatsApp", "classified-listing" ); ?></label>
				</th>
				<td>
					<input type="text" name="_rtcl_whatsapp_number" id="_rtcl_whatsapp_number" value="<?php
					echo esc_attr( $whatsapp ); ?>"
						   class="regular-text">
				</td>
			</tr>
			<tr>
				<th>
					<label for="_rtcl_phone"><?php
						esc_html_e( "Website", "classified-listing" ); ?></label>
				</th>
				<td>
					<input type="url" name="_rtcl_website" id="_rtcl_website" value="<?php
					echo esc_url( $website ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th>
					<label for="_rtcl_address"><?php
						esc_html_e( "Address", "classified-listing" ); ?></label>
				</th>
				<td>
					<textarea name="_rtcl_address" id="_rtcl_address" rows="3" cols="30"><?php
						echo esc_textarea( $address ); ?></textarea>
				</td>
			</tr>
			<tr class="rtcl-social-profiles">
				<th>
					<?php
					esc_html_e( "Social Profiles", "classified-listing" ); ?>
				</th>
				<td>
					<?php
					foreach ( $social_options as $key => $social_option ) {
						echo sprintf(
							'<input type="url" name="_rtcl_social_media[%1$s]" id="rtcl-account-social-%1$s" value="%2$s" placeholder="%3$s" class="regular-text"/><br />',
							esc_attr( $key ),
							esc_url( isset( $social_media[ $key ] ) ? $social_media[ $key ] : '' ),
							esc_attr( $social_option ),
						);
					}
					?>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save_user_profile_fields( $user_id ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$user_meta = [];

		$user_meta['_rtcl_phone']           = ! empty( $_POST['_rtcl_phone'] ) ? sanitize_text_field( $_POST['_rtcl_phone'] ) : null;
		$user_meta['_rtcl_whatsapp_number'] = ! empty( $_POST['_rtcl_whatsapp_number'] ) ? sanitize_text_field( $_POST['_rtcl_whatsapp_number'] ) : null;
		$user_meta['_rtcl_website']         = ! empty( $_POST['_rtcl_website'] ) ? esc_url_raw( $_POST['_rtcl_website'] ) : null;
		$user_meta['_rtcl_address']         = ! empty( $_POST['_rtcl_address'] ) ? esc_textarea( $_POST['_rtcl_address'] ) : null;

		if ( Functions::is_user_type_enabled() && current_user_can( 'manage_options' ) ) {
			$user_meta['_rtcl_user_type'] = ! empty( $_POST['rtcl_user_type'] ) ? sanitize_text_field( $_POST['rtcl_user_type'] ) : null;
		}

		if ( isset( $_POST['_rtcl_social_media'] ) ) {
			delete_user_meta( $user_id, '_rtcl_social' );
			if ( is_array( $_POST['_rtcl_social_media'] ) && ! empty( $_POST['_rtcl_social_media'] ) ) {
				$_social = [];
				foreach ( $_POST['_rtcl_social_media'] as $_sm_key => $_sm_url ) {
					if ( ! empty( $_sm_url ) ) {
						$_social[ sanitize_text_field( $_sm_key ) ] = esc_url_raw( $_sm_url );
					}
				}
				if ( ! empty( $_social ) ) {
					$user_meta['_rtcl_social'] = $_social;
				}
			}
		}

		if ( ! empty( $user_meta ) ) {
			foreach ( $user_meta as $metaKey => $metaValue ) {
				update_user_meta( $user_id, $metaKey, $metaValue );
			}
		}
	}

}