<?php
/**
 * @var array      $filterData
 * @var AjaxFilter $object
 */

use Rtcl\Widgets\AjaxFilter;

?>
<div class="rtcl-ajax-filter-wrap">
	<?php
	do_action( 'rtcl_widget_ajax_filter_start', $filterData );
	foreach ( $filterData['items'] as $filterItem ) {
		if ( ! empty( $filterItem['id'] ) ) {
			do_action( 'rtcl_widget_ajax_filter_render_' . $filterItem['id'], $filterItem, $filterData['items'], $object );
		}
	}
	do_action( 'rtcl_widget_ajax_filter_end', $filterData );
	?>
</div>
