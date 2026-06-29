<?php

use Rtcl\Resources\Options;

$addons = Options::addons();
$themes = Options::themes();
?>
<div id="rtcl" class="rtcl-admin-wrap rtcl-extensions">
	<div class="rtcl-admin-header">
		<h1 class="rtcl-header-title"><?php esc_html_e( "Get Addons & Themes", 'classified-listing' ) ?></h1>
	</div>
	<div id="rtcl-ext-wrap" class="rtcl-extension-wrap rtcl-admin-settings-wrap">
		<div class="rtcl-product-addons">
			<div class="rtcl-product-heading">
				<h2>Addons & Apps</h2>
			</div>
			<div class="rtcl-product-list">
				<?php
				if ( !empty( $addons ) ) {
					foreach ( $addons as $addon ) {
						$addon = wp_parse_args( $addon, [
							'title'    => '',
							'img_url'  => rtcl()->get_assets_uri( 'images/placeholder.jpg' ),
							'demo_url' => '',
							'buy_url'  => '',
						] )
						?>
						<div class="rtcl-product <?php echo esc_attr( $addon['type'] ); ?>">
							<div class="rtcl-product-thumb">
								<img alt="<?php echo esc_attr( $addon['title'] ) ?>"
									 src="<?php echo esc_url( $addon['img_url'] ) ?>">
							</div>
							<div class="rtcl-product-info">
								<?php
								if ( 'bundle' == $addon['type'] ) {
									echo '<span class="type">' . esc_html( $addon['type'] ) . '</span>';
								}
								?>
								<h3 class="rtcl-p-title">
									<?php if ( !empty( $addon['buy_url'] ) ): ?>
										<a target="_blank" href="<?php echo esc_url( $addon['buy_url'] ) ?>">
											<?php echo esc_attr( $addon['title'] ) ?></a>
									<?php else: ?>
										<?php echo esc_attr( $addon['title'] ) ?>
									<?php endif; ?>
								</h3>
								<div class="rtcl-p-action">
									<?php if ( !empty( $addon['buy_url'] ) ): ?>
										<a class="rtcl__btn btn__buy" target="_blank"
										   href="<?php echo esc_url( $addon['buy_url'] ) ?>"><?php esc_html_e( "Buy Now", "classified-listing" ); ?></a>
									<?php endif; ?>
									<a class="rtcl__btn btn__demo" target="_blank"
									   href="<?php echo esc_url( $addon['demo_url'] ) ?>"><?php esc_html_e( "Live Demo", "classified-listing" ); ?></a>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="all-button"><a href="https://www.radiustheme.com/classified-listing-addons/" target="_blank">See
					All Addons</a></div>
		</div>
		<div class="rtcl-product-themes">
			<div class="rtcl-product-heading">
				<h2>Themes & Apps</h2>
			</div>
			<div class="rtcl-product-list">
				<?php
				if ( !empty( $themes ) ) {
					foreach ( $themes as $theme ) {
						$theme = wp_parse_args( $theme, [
							'title'    => '',
							'img_url'  => rtcl()->get_assets_uri( 'images/placeholder.jpg' ),
							'demo_url' => '',
							'buy_url'  => '',
						] );
						?>
						<div class="rtcl-product <?php echo esc_attr( $theme['type'] ); ?>">
							<div class="rtcl-product-thumb">
								<img alt="<?php echo esc_attr( $theme['title'] ) ?>"
									 src="<?php echo esc_url( $theme['img_url'] ) ?>">
							</div>
							<div class="rtcl-product-info">
								<?php
								if ( 'free' == $theme['type'] ) {
									echo '<span class="type">' . esc_html( $theme['type'] ) . '</span>';
								}
								?>
								<h3 class="rtcl-p-title">
									<a target="_blank" href="<?php echo esc_url( $theme['buy_url'] ) ?>">
										<?php echo esc_attr( $theme['title'] ) ?></a>
								</h3>
								<div class="rtcl-p-action">
									<?php
									$buy_btn_txt = ( 'free' == $theme['type'] ) ? esc_html__( 'Download Now', 'classified-listing' )
										: esc_html__( 'Buy Now', 'classified-listing' );
									?>
									<a class="rtcl__btn btn__buy" target="_blank"
									   href="<?php echo esc_url( $theme['buy_url'] ) ?>"><?php echo esc_html( $buy_btn_txt ); ?></a>
									<a class="rtcl__btn btn__demo" target="_blank"
									   href="<?php echo esc_url( $theme['demo_url'] ) ?>"><?php esc_html_e( "Live Demo", "classified-listing" ); ?></a>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="all-button"><a href="https://www.radiustheme.com/classified-listing-themes/" target="_blank">See
					All Themes</a></div>
		</div>
	</div>
</div>