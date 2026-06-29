<?php

namespace Rtcl\Widgets;

use Rtcl\Helpers\Functions;
use Rtcl\Models\WidgetFields;
use Rtcl\Resources\Options;
use WP_Widget;

class AjaxFilterResult extends WP_Widget {

	protected $widget_slug;
	protected $instance;
	protected $hideAbleCount = 6;
	public $filterItems;

	public function __construct() {

		$this->widget_slug = 'rtcl-widget-ajax-filter-result';

		parent::__construct(
			$this->widget_slug,
			esc_html__( 'Classified Listing Ajax Filter Result', 'classified-listing' ),
			[
				'classname'   => 'rtcl ' . $this->widget_slug . '-class',
				'description' => esc_html__( 'Classified Listing Ajax Filter Result container', 'classified-listing' )
			]
		);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$this->instance    = $instance;
		?>
		<div class="rtcl-widget-ajax-filter-result-wrapper">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_widget'];

			if ( ! empty( $instance['title'] ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			Functions::get_template( 'widgets/ajax-filter-result', [ 'object' => $this ] );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['after_widget'];
			?>
		</div>
		<?php
	}
}