<?php
/**
 * Login form
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 * @var bool $hidden
 * @var string $message
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}

?>
<div class="rtcl rtcl-login-form-wrap" <?php if ( $hidden ){ ?>style="display:none;"<?php } ?>>
	<?php Functions::print_notices(); ?>
	
	<div class="rtcl-tab rtcl-tab-login">
		<?php do_action( 'rtcl_before_login_form' ); ?>
		<div class="rtcl-tab-content">
			<?php do_action( 'rtcl_login_tab_inner_content' ); ?>
		</div>
	</div>
</div>
