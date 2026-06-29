<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Gutenberg locationbox.
 *
 * Locationbox style.
 *
 * @package  Classifid-listing
 * @since 1.0.0
 */
$wrap_class = '';
if (isset($settings['blockId'])) {
	$wrap_class .= 'rtcl-block-' . $settings['blockId'];
}
$wrap_class .= ' rtcl-block-frontend ';
if (isset($settings['className'])) {
	$wrap_class .= $settings['className'];
}
?>
<div class="<?php echo esc_attr($wrap_class); ?>">
	<div class="rtcl gb-all-locations list-style-1">
		<?php
		$classes = 'rtcl-col-12';
		if (!empty($terms)) {
			foreach ($terms as $trm) {
				$count_html = null;
				if ($settings['show_count'] && !empty($trm['count'])) {
					ob_start();
					$count_data = sprintf( /* translators: ads count */_n('(%s Ad)', '(%s Ads)', $trm['count'], 'classified-listing'), $trm['count']); ?>
					<span class="rtcl-counter">
						<?php if (!empty($settings['count_after_text'])) { ?>
							<span><?php echo esc_html($trm['count']); ?></span>
							<span><?php echo esc_html($settings['count_after_text']); ?></span>
						<?php } else { ?>
							<?php echo esc_html($count_data); ?>
						<?php } ?>
					</span>
					<?php
					$count_html = ob_get_clean();
				}
				?>
				<div class="location-boxes-wrapper">
					<div class="location-boxes">
						<?php
						if ( $settings['show_image'] && ! empty( $trm['image'] ) ) { ?>
							<div class="rtcl-location-image">
								<a href="<?php
								echo esc_url( $trm['permalink'] ); ?>">
									<?php
									echo wp_kses_post( $trm['image'] ); ?>
								</a>
							</div>
							<?php
						} ?>
						<div class="rtcl-location-content">
							<div class="title-wrap <?php echo esc_attr(($settings['count_position'] == 'inline') ? "count-inline" : "count-newline"); ?>">
								<h3 class="rtcl-title">
									<?php if ($settings['enable_link']) { ?>
										<a <?php echo esc_attr(isset($settings['enable_nofollow']) && $settings['enable_nofollow'] == '1' ? 'rel=nofollow' : ''); ?> href="<?php echo esc_url($trm['permalink']); ?>">
											<?php echo esc_html($trm['name']); ?>
										</a>
										<?php
									} else {
										echo esc_html($trm['name']);
									}
									?>
								</h3>
								<?php
								$arr = array('span' => array('class' => array()));
								echo wp_kses($count_html, $arr);
								?>
							</div>
							<?php if ($settings['show_desc'] && !empty($trm['description'])) { ?>
								<div class="rtcl-description">
									<?php
									if ($settings['desc_limit']) {
										echo esc_html(wp_trim_words($trm['description'], $settings['desc_limit']));
									} else {
										echo wp_kses_post($trm['description']);
									}
									?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>