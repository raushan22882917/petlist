<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/email-footer.php.
 *
 *
 * @package CalassifiedListing/Templates/Emails
 * @version 2.3.0
 *
 * @var RtclEmail $email
 */

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;

defined( 'ABSPATH' ) || exit;
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
</table>
</td>
</tr>
<tr>
	<td align="center" valign="top">
		<!-- Footer -->
		<table border="0" cellpadding="10" cellspacing="0" style="width: 100%; max-width: 600px" id="template_footer">
			<tr>
				<td valign="top" style="<?php echo esc_attr( Functions::email_template_footer_td_style( $email ) ); ?>">
					<table border="0" cellpadding="10" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" valign="middle" id="credit"
								style="<?php echo esc_attr( Functions::email_template_footer_credit_style( $email ) ); ?>">
								<?php echo wp_kses_post( wpautop( wptexturize( $email->get_footer_text() ) ) ); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!-- End Footer -->
	</td>
</tr>
</table>
</div>
</body>
</html>
