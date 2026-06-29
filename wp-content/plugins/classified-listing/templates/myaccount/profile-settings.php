<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_User $user
 * @var string $show_phone
 * @var string $show_whatsapp
 * @var string $show_email
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<form class="rtcl-profile-settings-form rtcl-MyAccount-content-inner" id="rtcl-user-profile-settings" method="post">

	<h3 class="rtcl-myaccount-content-title"><?php esc_html_e( 'Privacy Settings', 'classified-listing' ); ?></h3>

	<div class="rtcl-form-group">
		<div>
			<i class="rtcl-icon rtcl-icon-user"></i>
			<label for="user_display_name" class="rtcl-field-label">
				<?php esc_html_e( 'Display Name Publicly as', 'classified-listing' ); ?>
			</label>
		</div>
		<div class="rtcl-field-col">
			<select name="user_display_name" id="user_display_name" class="rtcl-form-control">
				<?php
				$public_display = Functions::get_public_display_names( $user );
				foreach ( $public_display as $id => $item ) : ?>
					<option <?php selected( $user->display_name, $item ); ?>><?php echo $item; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="rtcl-form-group">
		<div>
			<i class="rtcl-icon rtcl-icon-mail"></i>
			<p class="rtcl-field-label">
				<?php esc_html_e( 'Display Email Publicly', 'classified-listing' ); ?>
			</p>
		</div>
		<div class="rtcl-form-radio-group">
			<label for="show_email_publicly">
				<input type="radio" id="show_email_publicly" name="display_email" value="yes" <?php checked( 'yes', $show_email ); ?> />
				<?php esc_html_e( 'Show to everyone', 'classified-listing' ); ?>
			</label>
			<label for="show_email_logged_in">
				<input type="radio" id="show_email_logged_in" name="display_email" value="logged_in" <?php checked( 'logged_in', $show_email ); ?> />
				<?php esc_html_e( 'Show only to logged-in users', 'classified-listing' ); ?>
			</label>
			<label for="hide_email_publicly">
				<input type="radio" id="hide_email_publicly" name="display_email" value="no" <?php checked( 'no', $show_email ); ?> />
				<?php esc_html_e( 'Hide for everyone', 'classified-listing' ); ?>
			</label>
		</div>
	</div>
	<div class="rtcl-form-group">
		<div>
			<i class="rtcl-icon rtcl-icon-phone"></i>
			<p class="rtcl-field-label">
				<?php esc_html_e( 'Display Phone Publicly', 'classified-listing' ); ?>
			</p>
		</div>
		<div class="rtcl-form-radio-group">
			<label for="show_phone_publicly">
				<input type="radio" id="show_phone_publicly" name="display_phone" value="yes" <?php checked( 'yes', $show_phone ); ?> />
				<?php esc_html_e( 'Show to everyone', 'classified-listing' ); ?>
			</label>
			<label for="show_phone_logged_in">
				<input type="radio" id="show_phone_logged_in" name="display_phone" value="logged_in" <?php checked( 'logged_in', $show_phone ); ?> />
				<?php esc_html_e( 'Show only to logged-in users', 'classified-listing' ); ?>
			</label>
			<label for="hide_phone_publicly">
				<input type="radio" id="hide_phone_publicly" name="display_phone" value="no" <?php checked( 'no', $show_phone ); ?> />
				<?php esc_html_e( 'Hide for everyone', 'classified-listing' ); ?>
			</label>
		</div>
	</div>
	<div class="rtcl-form-group">
		<div>
			<i class="rtcl-icon rtcl-icon-whatsapp"></i>
			<p class="rtcl-field-label">
				<?php esc_html_e( 'Display WhatsApp Publicly', 'classified-listing' ); ?>
			</p>
		</div>
		<div class="rtcl-form-radio-group">
			<label for="show_whatsapp_publicly">
				<input type="radio" id="show_whatsapp_publicly" name="display_whatsapp" value="yes" <?php checked( 'yes', $show_whatsapp ); ?> />
				<?php esc_html_e( 'Show to everyone', 'classified-listing' ); ?>
			</label>
			<label for="show_whatsapp_logged_in">
				<input type="radio" id="show_whatsapp_logged_in" name="display_whatsapp" value="logged_in" <?php checked( 'logged_in', $show_whatsapp ); ?> />
				<?php esc_html_e( 'Show only to logged-in users', 'classified-listing' ); ?>
			</label>
			<label for="hide_whatsapp_publicly">
				<input type="radio" id="hide_whatsapp_publicly" name="display_whatsapp" value="no" <?php checked( 'no', $show_whatsapp ); ?> />
				<?php esc_html_e( 'Hide for everyone', 'classified-listing' ); ?>
			</label>
		</div>
	</div>
	<div class="rtcl-form-group rtcl-form-group-submit">
		<div class="rtcl-field-col">
			<button type="submit" class="rtcl-btn"><?php esc_html_e( 'Update Settings', 'classified-listing' ); ?></button>
		</div>
	</div>
	<div class="rtcl-response"></div>

</form>
