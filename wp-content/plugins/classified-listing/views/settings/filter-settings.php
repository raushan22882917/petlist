<?php

use Rtcl\Helpers\Functions;

$filterForms = Functions::get_option( 'rtcl_filter_settings' );
?>
<div class="rtcl-external-settings-wrap">
	<div class="rtcl-es-header">
		<h3 class="rtcl-es-header-title"><?php esc_html_e( 'Manage Filter form', 'classified-listing' ); ?></h3>
	</div>
	<div id="rtcl-filter-settings-wrap">
		<div class="rtcl-filter-list">
			<div class="rtcl-filter-list-wrap">
				<?php
				if ( ! empty( $filterForms ) ) {
					foreach ( $filterForms as $filterId => $filterForm ) {
						echo sprintf( '<a data-id="%s" class="rtcl-filter-action-wrap"><span class="rtcl-filter-name">%s</span><span class="rtcl-filter-actions"><i class="rtcl-filter-edit dashicons dashicons-edit"></i><i class="rtcl-filter-remove dashicons dashicons-remove"></i></span></a>', esc_attr( $filterId ), esc_html( $filterForm['name'] ) );
					}
				}
				?>
			</div>
			<a class="rtcl-admin-btn outline block rtcl-filter-add"
			   title="<?php esc_attr_e( 'Add Filter', 'classified-listing' ); ?>">
				<span
					class="dashicons dashicons-plus-alt2"></span> <?php esc_attr_e( 'Add Filter', 'classified-listing' ); ?>
			</a>
		</div>
		<div id="rtcl-filter-wrap"></div>
	</div>
</div>
