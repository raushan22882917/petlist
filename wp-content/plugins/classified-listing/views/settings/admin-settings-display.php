<?php
/**
 * Admin settings form
 *
 */

use Rtcl\Controllers\Settings\AdminSettings;
use Rtcl\Helpers\Functions;

?>
<div class="rtcl-admin-wrap">
	<div class="rtcl-admin-header">
		<h4 class="rtcl-header-title">
			<?php esc_html_e( 'Settings', 'classified-listing' ); ?>
		</h4>
	</div>
	<div class="rtcl-admin-settings-wrap">
		<div
			class="rtcl-admin-main-settings rtcl-settings-active-<?php echo esc_attr( $this->active_tab ); ?><?php echo esc_attr( in_array( $this->active_tab,
				AdminSettings::EXTERNAL_IDS ) ? ' external' : '' ) ?>">
			<?php
			settings_errors();
			$this->show_messages();
			Functions::print_notices();
			?>
			<div class="rtcl-settings-nav-wrap">
				<ul class="nav-tab-wrapper">
					<?php
					foreach ( $this->option_group as $slug => $group ) {
						$li_class = "nav-list-" . $slug;
						$class    = "nav-tab nav-" . $slug;
						if ( $this->active_tab === $slug ) {
							$li_class .= ' nav-tab-active nav-open';
						}
						if ( ! empty( $group['subtab'] ) ) {
							$li_class .= ' have-sub-item';
						}
						echo '<li class="' . esc_attr( $li_class ) . '">';
						echo '<a href="?page=rtcl-settings&tab=' . esc_attr( $slug ) . '" class="' . esc_attr( $class )
							 . '">' . esc_html( $group['title'] ) . '</a>';
						if ( ! empty( $group['subtab'] ) ) {
							echo '<ul class="sub-settings">';
							foreach ( $group['subtab'] as $id => $label ) {
								$current_section_class = $this->active_tab === $slug && $this->current_section === $id ? ' current' : '';
								echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=rtcl-settings&tab=' . $slug
																		   . '&section=' . sanitize_title( $id ) ) ) . '" class="nav-sub-'
									 . esc_attr( strtolower( $label ) )
									 . esc_attr( $current_section_class ) . '">'
									 . esc_html( $label )
									 . '</a></li>';
							}
							echo '</ul>';
						}
						echo '</li>';
					}
					?>
				</ul>
			</div>
			<div class="rtcl-settings-form-wrap">
				<?php
				if ( in_array( $this->active_tab, AdminSettings::EXTERNAL_IDS ) ) {
					do_action( 'rtcl_admin_external_settings', $this->active_tab, $this->current_section );
				} else {
					?>
					<form method="post" action="">
						<?php
						do_action( 'rtcl_admin_settings_groups', $this->active_tab, $this->current_section );
						wp_nonce_field( 'rtcl-settings' );
						if ( $this->active_tab !== "addon_theme" ) {
							if ( 'tax_rate' === $this->current_section ) {
								submit_button( '', 'primary', 'submit', true, [ 'id' => 'rtcl-tax-save-button' ] );
							} else {
								submit_button();
							}

						}
						?>
					</form>
				<?php } ?>
			</div>
		</div>
		<div class="rtcl-admin-settings-promo">
			<div class="rtcl-settings-promo-wrap">
				<div class="bundle-card">
					<div class="image-container">
						<div class="product-image">
							<img alt="Classified Listing Plugins and Themes Bundled" src="<?php echo esc_url(rtcl()->get_assets_uri( "images/bundle-extension.png" )) ?>">
						</div>
						<div class="bundle-badge">Bundle Deal</div>
					</div>

					<div class="content">
						<h3 class="rtcl-promo-title">
							<a href="https://www.radiustheme.com/downloads/classified-listing-pro-plugins-bundle/" target="_blank">Classified Listing Plugins and
								Themes Bundle</a>
						</h3>
						<p class="rtcl-promo-subtitle">Everything you need in one package</p>

						<ul class="rtcl-promo-features">
							<li><a href="https://www.radiustheme.com/classified-listing-addons/" target="_blank">20+ Addons</a></li>
							<li><a href="https://www.radiustheme.com/classified-listing-themes/" target="_blank">12+ Themes</a></li>
							<li>All Future Addons and Themes</li>
							<li>14 Days Money Back Guarantee</li>
						</ul>

						<div class="price-section">
							<div class="savings">Save 40%</div>
						</div>

						<a class="cta-button" href="https://www.radiustheme.com/downloads/classified-listing-pro-plugins-bundle/?utm_source=clplugin_banner&utm_medium=banner&utm_campaign=Discount" target="_blank">Get Bundle Now</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>