<?php
/**
 *Manage Listing by user
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_Query $rtcl_query
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\FBHelper;

global $post;
?>

<div class="rtcl-manage-my-listings">

	<?php do_action( 'rtcl_my_account_before_my_listing', $rtcl_query ); ?>

	<!-- header here -->
	<div class="rtcl-action-wrap">
		<?php
		$status_filter = [
			'publish'      => esc_html__( "Published", 'classified-listing' ),
			'pending'      => esc_html__( "Pending", 'classified-listing' ),
			'rtcl-expired' => esc_html__( "Expired", 'classified-listing' ),
		];

		$active_status = ! empty( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'any';
		?>
		<div class="rtcl-my-listings-search-form">
			<form action="<?php echo esc_url( Link::get_account_endpoint_url( "listings" ) ); ?>" class="">
				<?php if ( FBHelper::isEnabled() ) {
					$allForms           = Form::query()->select( 'id,title,`default`' )->where( 'status', 'publish' )->order_by( 'created_at', 'DESC' )->get();
					$selected_directory = isset( $_GET['directory'] ) ? sanitize_text_field( wp_unslash( $_GET['directory'] ) ) : '';
					if ( ! empty( $allForms ) ) {
						?>
						<select class="rtcl-form-control" id="rtcl-my-listings-directory" aria-label="Select Directory" name="directory">
							<option value=""><?php esc_html_e( '--- Select Directory ---', 'classified-listing' ); ?></option>
							<?php
							foreach ( $allForms as $form ) {
								$formTitle = $form->title . ( $form->default === 1 ? esc_html__( 'Default', 'classified-listing' ) : '' );
								?>
								<option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $selected_directory, $form->id,
									true ); ?>><?php echo esc_html( $formTitle ); ?></option>
							<?php } ?>
						</select>
					<?php }
				} ?>
				<select class="rtcl-form-control" id="rtcl-my-listings-status" aria-label="Select Status" name="status">
					<option value="any"><?php esc_html_e( '--- Select Status ---', 'classified-listing' ); ?></option>
					<?php
					foreach ( $status_filter as $status => $title ) {
						?>
						<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $active_status, $status,
							true ); ?>><?php echo esc_html( $title ); ?></option>
					<?php } ?>
				</select>
				<div class="rtcl-my-listings-search-keyword">
					<input type="text" id="search-ml" name="u" class="rtcl-form-control"
						   aria-label="<?php esc_attr_e( "Search by title", 'classified-listing' ); ?>"
						   placeholder="<?php esc_attr_e( "Search by title", 'classified-listing' ); ?>"
						   value="<?php echo isset( $_GET['u'] ) ? esc_attr( wp_unslash( $_GET['u'] ) )
							   : ''; /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */ ?>">
					<button type="submit">
						<svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M8.65429 17.2954C3.88229 17.2954 0 13.4161 0 8.64769C0 3.87933 3.88229 0 8.65429 0C13.4263 0 17.3086 3.87933 17.3086 8.64769C17.3086 13.4161 13.4263 17.2954 8.65429 17.2954ZM8.65429 1.63937C4.78693 1.63937 1.64062 4.78328 1.64062 8.64769C1.64062 12.5121 4.78693 15.656 8.65429 15.656C12.5216 15.656 15.668 12.5121 15.668 8.64769C15.668 4.78328 12.5216 1.63937 8.65429 1.63937ZM20.7598 20.76C21.0801 20.4398 21.0801 19.9208 20.7598 19.6007L17.0889 15.9326C16.7685 15.6125 16.2491 15.6125 15.9287 15.9326C15.6084 16.2527 15.6084 16.7718 15.9287 17.0919L19.5996 20.76C19.7598 20.92 19.9697 21 20.1797 21C20.3897 21 20.5995 20.92 20.7598 20.76Z"
								fill="#646464"/>
						</svg>
					</button>
				</div>
				<?php Functions::query_string_form_fields( null, [ 'submit', 'paged', 'u' ] ); ?>
			</form>
		</div>
	</div>

	<div class="rtcl-my-listings-content">
		<?php Functions::get_template( "myaccount/my-listings-table", [ 'rtcl_query' => $rtcl_query ] ); ?>
	</div>

	<?php do_action( 'rtcl_my_account_after_my_listing', $rtcl_query ); ?>
</div>