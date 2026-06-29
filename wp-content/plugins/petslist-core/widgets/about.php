<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

use \WP_Widget;
use \RT_Widget_Fields;

class About_Widget extends WP_Widget {
	public function __construct() {
		$id = PETSLIST_CORE_THEME_PREFIX . '_about';
		parent::__construct(
			$id, // Base ID
			esc_html__( 'A1::About', 'petslist-core' ), // Name
			[
				'description' => esc_html__( 'Petslist: About widget', 'petslist-core' )
			] );
	}

	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );
		$title = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		if (!empty($instance['title'] || $instance['logo'])) {
			$title_logo = '';
		} else {
			$title_logo = 'title-logo-none';
		}
		?>
		<div class="widget-about <?php echo esc_attr( $title_logo ); ?>">
			<div class="rt-footer-logo">
				<?php if ( ! empty( $instance['logo'] ) ) { ?>
					<div class="widget-title">
					<a href="<?php echo esc_url( home_url('/') ); ?>">
						<?php echo wp_get_attachment_image( $instance['logo'], 'full' ); ?>
					</a>
					</div>
				<?php } else { ?>
					<h3 class="widget-title">
						<?php echo wp_kses_post( $title ); ?>
					<h3 class="widget-title">
				<?php } ?>
			</div>
			<?php if ( ! empty( $instance['description'] ) ) { ?>
				<p class="rtin-des">
					<?php echo wp_kses_post( $instance['description'] ); ?>
				</p>
			<?php } ?>
			<?php if ( ! empty( $instance['app_logo'] || $instance['app_link'] || $instance['play_logo'] || $instance['play_link'] ) ) { ?>
				<div class="app-download-area d-flex gap-1">
					<?php if ( ! empty( $instance['app_link'] ) ) { ?>
						<a href="<?php echo esc_url($instance['app_link']); ?>">
							<?php 
								if ( ! empty( $instance['app_logo'] ) ) {
									echo wp_get_attachment_image( $instance['app_logo'], 'full' );
								}
							?>
						</a>
					<?php } if ( ! empty( $instance['play_link'] ) ) { ?>
						<a href="<?php echo esc_url($instance['play_link']); ?>">
							<?php 
								if ( ! empty( $instance['play_logo'] ) ) {
									echo wp_get_attachment_image( $instance['play_logo'], 'full' );
								}
							?>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<?php 
		echo wp_kses_post( $args['after_widget'] );
	}

	public function update( $new_instance, $old_instance ) {
		$instance                = [];
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['logo']        = ( ! empty( $new_instance['logo'] ) ) ? sanitize_text_field( $new_instance['logo'] ) : '';
		$instance['description'] = ( ! empty( $new_instance['description'] ) ) ? wp_kses_post( $new_instance['description'] ) : '';

		$instance['app_logo']    = ( ! empty( $new_instance['app_logo'] ) ) ? sanitize_text_field( $new_instance['app_logo'] ) : '';
		$instance['app_link']    = ( ! empty( $new_instance['app_link'] ) ) ? sanitize_text_field( $new_instance['app_link'] ) : '';
		$instance['play_logo']   = ( ! empty( $new_instance['play_logo'] ) ) ? sanitize_text_field( $new_instance['play_logo'] ) : '';
		$instance['play_link']   = ( ! empty( $new_instance['play_link'] ) ) ? sanitize_text_field( $new_instance['play_link'] ) : '';
		
		return $instance;
	}

	public function form( $instance ) {
		$defaults = [
			'title'       => '',
			'logo'        => '',
			'description' => '',
			'app_logo'    => '',
			'app_link'    => '',
			'play_logo'   => '',
			'play_link'   => '',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );

		$fields = [
			'title'       => [
				'label' => esc_html__( 'Title', 'petslist-core' ),
				'type'  => 'text',
			],
			'logo'        => [
				'label' => esc_html__( 'Logo', 'petslist-core' ),
				'type'  => 'image',
			],
			'description' => [
				'label' => esc_html__( 'Description', 'petslist-core' ),
				'type'  => 'textarea',
			],
			'app_logo'        => [
				'label' => esc_html__( 'App Store Image', 'petslist-core' ),
				'type'  => 'image',
			],
			'app_link'     => [
				'label' => esc_html__( 'App Store URL', 'petslist-core' ),
				'type'  => 'url',
			],
			'play_logo'    => [
				'label' => esc_html__( 'Play Store Image', 'petslist-core' ),
				'type'  => 'image',
			],
			'play_link'   => [
				'label' => esc_html__( 'Play Store URL', 'petslist-core' ),
				'type'  => 'url',
			],
		];

		RT_Widget_Fields::display( $fields, $instance, $this );
	}
}