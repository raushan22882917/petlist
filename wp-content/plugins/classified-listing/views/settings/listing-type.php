<?php

use Rtcl\Helpers\Functions;

?>
<div class="wrap rtcl" id="rtcl-listing-types-wrap">
	<h2><?php esc_html_e( "Listing Types", 'classified-listing' ) ?></h2>
	<div class="rtcl-listing-types-wrapper rtcl-row">
		<div id="input-new-type-wrapper" class="rtcl-col-md-4 rtcl-col-12">
			<form id="input-new-type-form">
				<div class="rtcl-form-group">
					<label class="rtcl-field-label"><?php esc_html_e( "Add new type", "classified-listing" ); ?></label>
					<input type="text" name="type" id="add-input-type" class="rtcl-form-control">
				</div>
				<?php do_action( 'rtcl_after_listing_type_input' ); ?>
				<div class="rtcl-form-group">
					<button class="rtcl-btn" type="submit"
							id="rtcl-add-btn"><?php esc_html_e( "Add Type", "classified-listing" ); ?></button>
				</div>
			</form>
		</div>
		<div class="rtcl-col-md-8 rtcl-col-12" id="rtcl-listing-type-wrap">
			<?php
			$types = Functions::get_listing_types();
			if ( ! empty( $types ) ) {
				?>
				<ul id="listing-types" class="rtcl-list-group">
					<?php
					foreach ( $types as $typeId => $type ) {
						?>
						<li class="rtcl-list-group-item listing-type" data-id="<?php echo esc_attr( $typeId ); ?>">
							<div class="type-details d-flex">
								<div class="type-info">
									<div class="type-info-id"><?php echo esc_html( $typeId ); ?></div>
									<div class="type-info-name"><?php echo esc_html( $type ); ?></div>
								</div>
								<div class="action ml-auto">
									<span class="rtcl-btn edit"><?php esc_html_e( 'Edit', 'classified-listing' ); ?></span>
									<span class="rtcl-btn rtcl-btn-danger delete"><?php esc_html_e( 'Delete', 'classified-listing' ); ?></span>
								</div>
							</div>
							<div class="edit-action">
								<form class="rtcl-row input-update-type-form">
									<div class="rtcl-form-group rtcl-col-6">
										<label class="rtcl-field-label"><?php esc_html_e( 'ID', 'classified-listing' ); ?></label>
										<input type="text" name="id" class="rtcl-form-control"
											   value="<?php echo esc_attr( $typeId ); ?>">
									</div>
									<div class="rtcl-form-group rtcl-col-6">
										<label class="rtcl-field-label"><?php esc_html_e( 'Type', 'classified-listing' ); ?></label>
										<input type="text" name="name" class="rtcl-form-control"
											   value="<?php echo esc_attr( $type ); ?>">
									</div>
									<?php do_action( 'rtcl_after_listing_type_input', $typeId ); ?>
									<div class="rtcl-form-group rtcl-col-12">
										<button type="submit"
												class="rtcl-btn"><?php esc_html_e( 'Update', 'classified-listing' ); ?></button>
									</div>
								</form>
							</div>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
			} else {
				esc_html_e( "No listing type found", "classified-listing" );
			}
			?>
		</div>
	</div>
</div>