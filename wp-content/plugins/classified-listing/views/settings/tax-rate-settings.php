<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$countries = rtcl()->countries->get_countries();
$states = rtcl()->countries->get_states();
$tax_options = \Rtcl\Helpers\Functions::get_tax_options();
?>
<div class="rtcl-tax-rate-settings-wrap">
	<h3 class="rtcl-settings-section-title"><?php esc_html_e( 'Tax Rate Settings', 'classified-listing' ); ?></h3>
	<table class="rtcl-tax-rate-settings-table">
		<thead>
		<tr>
			<th width="20%"><?php esc_html_e( 'Country code', 'classified-listing' ); ?></th>
			<th width="18%"><?php esc_html_e( 'State code', 'classified-listing' ); ?></th>
			<th width="17%"><?php esc_html_e( 'City', 'classified-listing' ); ?></th>
			<th width="15%"><?php esc_html_e( 'Rate&nbsp;%', 'classified-listing' ); ?></th>
			<th width="15%"><?php esc_html_e( 'Tax name', 'classified-listing' ); ?></th>
			<th width="15%"><?php esc_html_e( 'Priority', 'classified-listing' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( !empty( $tax_options ) ) {
			foreach ( $tax_options as $key => $option ) {
				?>
				<tr data-id="<?php echo esc_attr( $option->tax_rate_id ); ?>">
					<td class="country" width="20%">
						<input type="text" value="<?php echo esc_attr( $option->country ); ?>" placeholder="*"
							   name="rtcl_tax_rate_country[]"
							   class="input-text regular-input" autocomplete="off"/>
					</td>
					<td class="state" width="18%">
						<input type="text" value="<?php echo esc_attr( $option->country_state ); ?>" placeholder="*"
							   name="rtcl_tax_rate_state[]"
							   class="input-text regular-input" autocomplete="off"/>
					</td>
					<td class="city" width="17%">
						<input type="text" value="<?php echo esc_attr( $option->country_city ); ?>" placeholder="*"
							   name="rtcl_tax_rate_city[]"
							   class="input-text regular-input"/>
					</td>
					<td class="rate" width="15%">
						<input type="text" value="<?php echo esc_attr( $option->tax_rate ); ?>" placeholder="0"
							   name="rtcl_tax_rate[]"
							   class="input-text regular-input"/>
					</td>
					<td class="name" width="15%">
						<input type="text" name="rtcl_tax_rate_name[]"
							   value="<?php echo esc_attr( !empty( $option->tax_rate_name ) ? $option->tax_rate_name : __( 'Tax', 'classified-listing' ) ); ?>"
							   class="input-text regular-input"/>
					</td>
					<td class="priority" width="15%">
						<input type="number" step="1" min="1"
							   value="<?php echo esc_attr( !empty( $option->tax_rate_priority ) ? $option->tax_rate_priority : 1 ); ?>"
							   name="rtcl_tax_rate_priority[]"
							   class="input-text regular-input"/>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td class="country" width="20%">
					<input type="text" value="" placeholder="*" name="rtcl_tax_rate_country[]"
						   class="input-text regular-input" autocomplete="off"/>
				</td>
				<td class="state" width="18%">
					<input type="text" value="" placeholder="*" name="rtcl_tax_rate_state[]"
						   class="input-text regular-input" autocomplete="off"/>
				</td>
				<td class="city" width="17%">
					<input type="text" value="" placeholder="*" name="rtcl_tax_rate_city[]"
						   class="input-text regular-input"/>
				</td>
				<td class="rate" width="15%">
					<input type="text" value="" placeholder="0" name="rtcl_tax_rate[]"
						   class="input-text regular-input"/>
				</td>
				<td class="name" width="15%">
					<input type="text" value="Tax" name="rtcl_tax_rate_name[]" class="input-text regular-input"/>
				</td>
				<td class="priority" width="15%">
					<input type="number" step="1" min="1" value="1" name="rtcl_tax_rate_priority[]"
						   class="input-text regular-input"/>
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
		<tr>
			<th colspan="9">
				<a href="#" class="rtcl-add-tax-row"><?php esc_html_e( 'Insert row', 'classified-listing' ); ?></a>
				<a href="#"
				   class="rtcl-remove-tax-row"><?php esc_html_e( 'Remove selected row(s)', 'classified-listing' ); ?></a>
			</th>
		</tr>
		</tfoot>
	</table>
	<ul class="rtcl-country-list">
		<?php
		foreach ( $countries as $key => $name ) {
			printf( '<li data-country-code="%s">%s</li>', esc_attr( $key ), esc_html( $name ) );
		}
		?>
	</ul>
</div>
