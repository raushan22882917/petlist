<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_User $user
 * @var string  $phone
 * @var string  $whatsapp_number
 * @var string  $website
 * @var string  $geo_address
 * @var string  $state_text
 * @var string  $city_text
 * @var array   $user_locations
 * @var int     $sub_location_id
 * @var int     $location_id
 * @var string  $town_text
 * @var string  $zipcode
 * @var float   $latitude
 * @var float   $longitude
 * @var int     $pp_id
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'rtcl_before_edit_account_form' ); ?>

<form class="rtcl-EditAccountForm rtcl-MyAccount-content-inner" id="rtcl-user-account" method="post">
	
	<h3 class="rtcl-myaccount-content-title"><?php esc_html_e( 'Account Details', 'classified-listing' ); ?></h3>

	<?php do_action( 'rtcl_edit_account_form_start' ); ?>

	<div class="rtcl-form-group-wrap">
		<div class="rtcl-form-group rtcl-no-field-group rtcl-username-row">
			<label for="rtcl-username" class="rtcl-field-label">
				<?php esc_html_e( 'Username:', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<p class="rtcl-form-control-static"><strong><?php echo esc_html( $user->user_login ); ?></strong></p>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-first-name" class="rtcl-field-label">
				<?php esc_html_e( 'First Name', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<input type="text" name="first_name" id="rtcl-first-name" value="<?php echo esc_attr( $user->first_name ); ?>" class="rtcl-form-control"/>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-last-name" class="rtcl-field-label">
				<?php esc_html_e( 'Last Name', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<input type="text" name="last_name" id="rtcl-last-name" value="<?php echo esc_attr( $user->last_name ); ?>"
					   class="rtcl-form-control"/>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-email" class="rtcl-field-label">
				<?php esc_html_e( 'E-mail', 'classified-listing' ); ?>
				<span class="require-star">*</span>
			</label>
			<div class="rtcl-field-col">
				<input type="email" name="email" id="rtcl-email" class="rtcl-form-control"
					   value="<?php echo esc_attr( $user->user_email ); ?>" required="required"/>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-last-name" class="rtcl-field-label">
				<?php esc_html_e( 'Whatsapp number', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<input type="text" name="whatsapp_number" id="rtcl-whatsapp-phone"
					   value="<?php echo esc_attr( $whatsapp_number ); ?>"
					   class="rtcl-form-control"/>
				<p class="description small"><?php esc_html_e( "WhatsApp number with your country code. e.g.+1xxxxxxxxxx", 'classified-listing' ) ?></p>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-phone" class="rtcl-field-label">
				<?php esc_html_e( 'Phone', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<?php
				$phone = esc_attr( $phone );
				$field = "<input type='text' name='phone' id='rtcl-phone' value='{$phone}' class='rtcl-form-control'/>";
				Functions::print_html( apply_filters( 'rtcl_edit_account_phone_field', $field, $phone ), true );
				?>
			</div>
		</div>
		<div class="rtcl-form-group">
			<label for="rtcl-website" class="rtcl-field-label">
				<?php esc_html_e( 'Website', 'classified-listing' ); ?>
			</label>
			<div class="rtcl-field-col">
				<input type="url" name="website" id="rtcl-website" value="<?php echo esc_attr( $website ); ?>" class="rtcl-form-control"/>
				<p class="description small"><?php esc_html_e('e.g. https://example.com', 'classified-listing'); ?></p>
			</div>
		</div>
		<div class="rtcl-form-group rtcl-no-field-group">
			<div class="form-check">
				<input type="hidden" name="change_password" value="0">
				<input type="checkbox" name="change_password" class="form-check-input" id="rtcl-change-password" value="1">
				<label class="rtcl-form-check-label" for="rtcl-change-password">
					<?php esc_html_e( 'Change Password', 'classified-listing' ); ?>
				</label>
			</div>
		</div>
		<div class="rtcl-form-group rtcl-password-fields" style="display: none;">
			<label for="password" class="rtcl-field-label">
				<?php esc_html_e( 'New Password', 'classified-listing' ); ?>
				<span class="require-star">*</span>
			</label>
			<div class="rtcl-field-col">
				<input type="password" name="pass1" id="password" class="rtcl-form-control rtcl-password" autocomplete="off"
					   required="required"/>
			</div>
		</div>
		<div class="rtcl-form-group rtcl-password-fields" style="display: none">
			<label for="password_confirm" class="rtcl-field-label">
				<?php esc_html_e( 'Confirm Password', 'classified-listing' ); ?>
				<span class="require-star">*</span>
			</label>
			<div class="rtcl-field-col">
				<input type="password" name="pass2" id="password_confirm" class="rtcl-form-control" autocomplete="off"
					   data-rule-equalTo="#password" data-msg-equalTo="<?php esc_attr_e( 'Password does not match.', 'classified-listing' ); ?>"
					   required/>
			</div>
		</div>
	</div>

	<?php do_action( 'rtcl_edit_account_form' ); ?>

	<div class="rtcl-form-group rtcl-profile-picture-row">
		<label for="rtcl-profile-picture" class="rtcl-field-label">
			<?php esc_html_e( 'Profile Picture', 'classified-listing' ); ?>
			<span class="require-star">*</span>
		</label>
		<div class="rtcl-field-col">
			<div class="rtcl-profile-picture-wrap">
				<?php if ( ! $pp_id ): ?>
					<div class="rtcl-gravatar-wrap">
						<div class="rtcl-gravatar-img">
							<?php echo get_avatar( $user->ID ); ?>
						</div>
						<?php
						echo "<p><a href='https://en.gravatar.com/'>" . esc_html__( 'Change on Gravatar.', 'classified-listing' ) . "</a></p>";
						?>
					</div>
				<?php endif; ?>
				<div class="rtcl-media-upload-wrap">
					<div class="rtcl-media-upload rtcl-media-upload-pp<?php echo( $pp_id ? ' has-media' : ' no-media' ) ?>">
						<div class="rtcl-media-action">
							<span class="rtcl-icon-plus add"><?php esc_html_e( "Add Logo", "classified-listing" ); ?></span>
							<span class="rtcl-icon-trash remove"><?php esc_html_e( "Delete Logo", "classified-listing" ); ?></span>
						</div>
						<div class="rtcl-media-item">
							<?php echo( $pp_id ? wp_get_attachment_image( $pp_id, [ 100, 100 ] ) : '' ) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php do_action( 'rtcl_edit_account_form_end' ); ?>

</form>

<?php do_action( 'rtcl_after_edit_account_form' ); ?>
