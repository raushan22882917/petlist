<?php

namespace Rtcl\Widgets;

use Rtcl\Helpers\Functions;
use Rtcl\Models\WidgetFields;
use WP_Widget;

class AjaxFilter extends WP_Widget {

	protected $widget_slug;
	protected $instance;
	protected $hideAbleCount = 6;
	public $filterData;

	public function __construct() {

		$this->widget_slug = 'rtcl-widget-ajax-filter';

		parent::__construct(
			$this->widget_slug,
			esc_html__( 'Classified Listing Ajax Filter', 'classified-listing' ),
			[
				'classname'   => 'rtcl ' . $this->widget_slug . '-class',
				'description' => esc_html__( 'Classified Listing Ajax Filter', 'classified-listing' )
			]
		);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		do_action( 'rtcl_widget_ajax_filter_scripts', $this );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$filters                 = Functions::get_option( 'rtcl_filter_settings' );
		$filterData              = ! empty( $filters[ $instance['filter_id'] ] ) ? $filters[ $instance['filter_id'] ] : [];
		$filterData['filter_id'] = $instance['filter_id'];
		$this->filterData        = $filterData;
		$this->instance          = $instance;
		?>
		<div class="rtcl-widget-ajax-filter-wrapper"
			 data-options="<?php
			 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			 echo htmlspecialchars( wp_json_encode( $filterData ) ); ?>">
			<?php if ( rtcl()->is_request( 'frontend' ) ): ?>
				<div class="rtcl-ajax-filter-floating-mobile">
					<h4><?php echo esc_html( apply_filters( 'rtcl_ajax_filter_off_canvas_title', __( 'Show Filter', 'classified-listing' ) ) ); ?></h4>
					<div class="rtcl-ajax-filter-open-filter">
						<svg width="32" height="21" viewBox="0 0 32 21" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M1 4.23184H17.2821C17.7312 5.71883 19.3727 6.82472 21.3241 6.82472C23.2756 6.82472 24.9171 5.71883 25.3662 4.23184H31C31.5523 4.23184 32 3.86493 32 3.41239C32 2.95985 31.5523 2.59294 31 2.59294H25.3661C24.9171 1.10595 23.2756 0 21.3241 0C19.3726 0 17.7311 1.10595 17.282 2.59294H1C0.44775 2.59294 0 2.95985 0 3.41239C0 3.86493 0.44775 4.23184 1 4.23184ZM21.3241 1.6389C22.5175 1.6389 23.4884 2.43448 23.4884 3.41234C23.4884 4.39025 22.5175 5.18583 21.3241 5.18583C20.1308 5.18583 19.1599 4.39025 19.1599 3.41234C19.1599 2.43448 20.1308 1.6389 21.3241 1.6389ZM1 11.3194H6.63387C7.083 12.8064 8.72444 13.9123 10.6759 13.9123C12.6274 13.9123 14.2689 12.8064 14.718 11.3194H31C31.5523 11.3194 32 10.9525 32 10.5C32 10.0475 31.5523 9.68055 31 9.68055H14.7179C14.2688 8.19356 12.6274 7.08761 10.6759 7.08761C8.72437 7.08761 7.08294 8.19356 6.63381 9.68055H1C0.44775 9.68055 0 10.0475 0 10.5C0 10.9525 0.447688 11.3194 1 11.3194ZM10.6759 8.72651C11.8692 8.72651 12.8401 9.52209 12.8401 10.5C12.8401 11.4779 11.8692 12.2734 10.6759 12.2734C9.4825 12.2734 8.51163 11.4779 8.51163 10.5C8.51163 9.52209 9.4825 8.72651 10.6759 8.72651ZM31 16.7682H25.3661C24.917 15.2812 23.2756 14.1752 21.3241 14.1752C19.3726 14.1752 17.7311 15.2812 17.282 16.7682H1C0.44775 16.7682 0 17.1351 0 17.5876C0 18.0402 0.44775 18.4071 1 18.4071H17.2821C17.7312 19.8941 19.3726 21 21.3241 21C23.2756 21 24.9171 19.8941 25.3662 18.4071H31C31.5523 18.4071 32 18.0402 32 17.5876C32 17.1351 31.5523 16.7682 31 16.7682ZM21.3241 19.3611C20.1308 19.3611 19.1599 18.5655 19.1599 17.5876C19.1599 16.6097 20.1308 15.8141 21.3241 15.8141C22.5175 15.8141 23.4884 16.6097 23.4884 17.5876C23.4884 18.5655 22.5175 19.3611 21.3241 19.3611Z"
								fill="white"/>
						</svg>
						<?php $filter_widget_title = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] )
							: esc_html__( 'Filters', 'classified-listing' ); ?>
						<strong><?php echo esc_html( $filter_widget_title ); ?></strong>
					</div>
				</div>
			<?php endif; ?>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_widget'];

			if ( ! empty( $instance['title'] ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			if ( empty( $this->filterData['items'] ) ) {
				?>
				<p><?php esc_html_e( 'No filter form is selected', 'classified-listing' ); ?></p>
				<?php
			} else {
				Functions::get_template( 'widgets/ajax-filter',
					[
						'object'     => $this,
						'filterData' => $filterData
					]
				);
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['after_widget'];
			?>
		</div>
		<?php
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$filters               = Functions::get_option( 'rtcl_filter_settings' );
		$instance              = $old_instance;
		$instance['title']     = ! empty( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['filter_id'] = ! empty( $new_instance['filter_id'] ) && in_array( $new_instance['filter_id'], array_keys( $filters ) )
			? $new_instance['filter_id'] : '';

		return $instance;
	}

	/**
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$filters = Functions::get_option( 'rtcl_filter_settings' );
		$filters = ! empty( $filters ) && is_array( $filters ) ? array_map( function ( $filter ) {
			return $filter['name'];
		},
			$filters ) : [];

		// Add none value at beginning of the array 
		$filters      = array_merge( [ '' => __( 'Select one', 'classified-listing' ) ], $filters );
		$fields       = [
			'title'     => [
				'label' => esc_html__( 'Title', 'classified-listing' ),
				'type'  => 'text'
			],
			'filter_id' => [
				'label'   => esc_html__( 'Select a filter form', 'classified-listing' ),
				'type'    => 'select',
				'options' => $filters
			]
		];
		$instance     = wp_parse_args( $instance, [ 'title' => esc_html__( 'Filter', 'classified-listing' ) ] );
		$widgetFields = new WidgetFields( $fields, $instance, $this );
		$widgetFields->render();
	}

	/**
	 * @param array $itemData
	 * @param array $options
	 * @param null|string $itemHtml
	 *
	 * @return string
	 */
	public function render_filter_item( $itemData, $options, $itemHtml = null ) {

		return sprintf( '<div class="rtcl-ajax-filter-item rtcl-%s is-open%s">
					                <div class="rtcl-filter-title-wrap">
										<div class="rtcl-filter-title">%s%s</div>
										<i class="rtcl-icon rtcl-icon-angle-down"></i>
									</div>
									<div class="rtcl-filter-content%s" data-options="%s">%s</div>
					            </div>',
			apply_filters( 'rtcl_ajax_filter_item_class', $options['name'], $itemData ),
			! empty( $itemData['active'] ) ? ' is-active' : '',
			apply_filters( 'rtcl_widget_ajax_filter_' . $options['name'] . '_title', $itemData['title'] ),
			! empty( $options['allow_rest'] ) ? ' <span class="rtcl-reset rtcl-icon rtcl-icon-cw">' : '',
			! empty( $options['ajax_load'] ) ? ' rtcl-ajax' : '',
			htmlspecialchars( wp_json_encode( $options ) ),
			$itemHtml
		);
	}
}