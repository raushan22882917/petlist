<?php
/**
 * Email Login Form
 *
 * @package classified-listing/Templates
 * @version 1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$active_class = ' active';
if ( class_exists( 'RtclVerification' ) && method_exists( \RtclVerification\Helpers\Functions::class, 'get_default_login_visibility' ) ) {
	if ( \RtclVerification\Helpers\Functions::is_enable_otp_login() && 'otp_login' === \RtclVerification\Helpers\Functions::get_default_login_visibility() ) {
		$active_class = '';
	}
}
?>
<div id="rtcl-email-login" class="rtcl-tab-pane<?php echo esc_attr( $active_class ); ?>">
	<form id="rtcl-login-form" class="form-horizontal" method="post">
		<?php do_action( 'rtcl_login_form_start' ); ?>
		<div class="rtcl-form-group">
			<label for="rtcl-user-login" class="rtcl-field-label">
				<?php esc_html_e( 'Username or E-mail', 'classified-listing' ); ?>
				<strong class="rtcl-required">*</strong>
			</label>
			<input type="text" name="username" autocomplete="username"
				   value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
				   id="rtcl-user-login" class="rtcl-form-control" required/>
		</div>

		<div class="rtcl-form-group">
			<label for="rtcl-user-pass" class="rtcl-field-label">
				<?php esc_html_e( 'Password', 'classified-listing' ); ?>
				<strong class="rtcl-required">*</strong>
			</label>
			<div class="rtcl-user-pass-wrap">
				<input type="password" name="password" id="rtcl-user-pass" autocomplete="current-password"
					   class="rtcl-form-control" required/>
				<span class="rtcl-toggle-pass rtcl-icon-eye-off"></span>
			</div>
		</div>

		<?php do_action( 'rtcl_login_form' ); ?>

		<div class="rtcl-form-group">
			<div id="rtcl-login-g-recaptcha"></div>
			<div id="rtcl-login-g-recaptcha-message"></div>
		</div>

		<div class="rtcl-form-group rtcl-login-form-submit-wrap">

			<button type="submit" name="rtcl-login" class="rtcl-btn" value="login">
				<?php esc_html_e( 'Login', 'classified-listing' ); ?>
			</button>
			<div class="rtcl-checkbox-list">
				<label class="rtcl-check" for="rtcl-rememberme">
					<input type="checkbox" id="rtcl-rememberme" name="rememberme" value="forever">
					<span class="rtcl-check-box"></span>
					<span class="rtcl-check-label"><?php esc_html_e( 'Remember Me', 'classified-listing' ); ?></span>
				</label>
			</div>
		</div>
		<?php do_action( 'rtcl_login_form_end' ); ?>
	</form>
	<?php do_action( 'rtcl_after_login_form' ); ?>
</div>