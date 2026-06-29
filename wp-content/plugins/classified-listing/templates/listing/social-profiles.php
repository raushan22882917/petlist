<?php
/**
 * The template to display the Social profile
 *
 * @author  RadiousTheme
 * @package classified-listing/Templates
 * @version 1.5.72
 * @var array $social_profiles
 */

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;
use Rtcl\Services\FormBuilder\FBHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( empty( $social_profiles ) || empty( $social_list = Options::get_social_profiles_list() ) || ( ! FBHelper::isEnabled() && ! Functions::is_enable_social_profiles() ) ) {
	return;
}
?>
<div class="rtcl-social-profile-wrap">
	<?php
	$label_text    = esc_html__( 'Social Profiles:', 'classified-listing' );
	$profile_label = apply_filters( 'rtcl_social_profile_label', $label_text );
	if ( $profile_label ) {
		?>
		<div class="rtcl-social-profile-label">
			<?php echo esc_html( $profile_label ); ?>
		</div>
		<?php
	}
	?>
	<div class="rtcl-social-profiles">
		<?php
		foreach ( $social_list as $item => $value ) {
			if ( ! empty( $social_profiles[ $item ] ) ) {
				?>
				<a target="_blank" href="<?php echo esc_url( $social_profiles[ $item ] ); ?>"
				   title="<?php echo esc_attr( $value ); ?>">
					<?php
					if ( 'twitter' === $item ) {
						$iconClass = 'fa-brands fa-x-twitter';
					} else {
						$iconClass = 'rtcl-icon-' . $item;
					}
					?>
					<i class="rtcl-icon <?php echo esc_attr( $iconClass ); ?>"></i>
				</a>
				<?php
			}
		}
		?>
	</div>
</div>