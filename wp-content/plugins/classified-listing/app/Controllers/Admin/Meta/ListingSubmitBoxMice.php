<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped 
namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class ListingSubmitBoxMice {

	public function __construct() {
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox_misc_actions' ] );
		add_action( 'admin_footer-post.php', [ $this, 'jc_append_post_status_list' ] );
		add_action( 'admin_footer-post-new.php', [ $this, 'jc_append_post_status_list' ] );
	}

	public function post_submitbox_misc_actions() {

		global $post, $post_type;

		if ( rtcl()->post_type == $post_type ) {
			$never_expires = !empty( get_post_meta( $post->ID, 'never_expires', true ) ) ? 1 : 0;
			$expiry_date = get_post_meta( $post->ID, 'expiry_date', true );
			$expiry_date = $expiry_date ?: Functions::dummy_expiry_date();
			$promotions = Options::get_listing_promotions();
			$_views = get_post_meta( $post->ID, '_views', true );
			wp_nonce_field( rtcl()->nonceText, rtcl()->nonceId );
			?>
			<div
				class="misc-pub-section misc-pub-rtcl-expiration-time"<?php echo $never_expires ? ' style="display: none;"' : '' ?>>
				<?php Functions::touch_time( 'expiry_date', $expiry_date ); ?>
			</div>
			<div class="misc-pub-section misc-pub-rtcl-overwrite">
				<label>
					<input type="checkbox" id="rtcl-overwrite" name="overwrite" value="1">
					<strong><?php esc_html_e( "Overwrite Default", 'classified-listing' ); ?></strong>
				</label>
			</div>
			<div class="misc-pub-section rtcl-overwrite-item" data-id="never-expires">
				<label>
					<input disabled type="checkbox" name="never_expires"
						   value="1" <?php checked( $never_expires, 1 ) ?>>
					<strong><?php esc_html_e( "Never Expires", 'classified-listing' ); ?></strong>
				</label>
			</div>
			<?php
			if ( !empty( $promotions ) ) {
				foreach ( $promotions as $promo_id => $promotion ) {
					$promo_value = get_post_meta( $post->ID, $promo_id, true );
					?>
					<div class="misc-pub-section rtcl-overwrite-item" data-id="<?php echo esc_attr( $promo_id ) ?>">
						<label>
							<input disabled type="checkbox" name="<?php echo esc_attr( $promo_id ) ?>"
								   value="1" <?php checked( $promo_value, 1 ) ?>>
							<?php esc_html_e( "Mark as", 'classified-listing' ); ?>
							<strong><?php echo esc_html( $promotion ); ?></strong>
						</label>
					</div>
					<?php
					do_action( 'rtcl_listing_submit_box_misc_actions_' . $promo_id, "99", $post );
				}
			}
			?>
			<div class="misc-pub-section">
				<label for="rtcl-views">
					<strong><?php esc_html_e( "View", 'classified-listing' ); ?></strong>
					<input type="number" id="rtcl-views" name="_views" value="<?php echo absint( $_views ); ?>">
				</label>
			</div>

			<div class="misc-pub-section misc-pub-rtcl-action rtcl">
				<div class="form-group row">
					<label for="rtcl-listing-status"
						   class="col-sm-2 col-form-label"><?php esc_html_e( "Status", "classified-listing" ) ?></label>
					<div class="col-sm-10">
						<select name="post_status" id="rtcl-listing-status" class="form-control">
							<?php
							$status_list = Options::get_status_list();
							$c_status = get_post_status( $post->ID );
							foreach ( $status_list as $status_id => $status ) {
								printf( "<option value='%s'%s>%s</option>",
									esc_attr( $status_id ),
									$status_id === $c_status ? " selected" : null,
									esc_html( $status )
								);
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<?php
		}

	}

	function jc_append_post_status_list() {
		global $post, $post_type;
		if ( rtcl()->post_type === $post_type ) {
			$status_opt = null;
			$status_list = Options::get_status_list();
			$label = null;
			foreach ( $status_list as $status_key => $status ) {
				$slt = '';
				if ( $status_key == $post->post_status ) {
					$slt = " selected";
					$label = $status;
				}
				$status_opt .= '<option value="' . esc_attr( $status_key ) . '"' . esc_attr( $slt ) . '>' . esc_html( $status ) . '</option>';
			}
			$btnText = $post ? esc_html__( "Save", "classified-listing" ) : esc_html__( "Update", "classified-listing" );
			$js = $label ? '$("#post-status-display").text("' . $label . '");' : '';
			echo '
                  <script>
					  (function($){
						  $("select#post_status").html(\'' . $status_opt . '\');
						  $("#publish")
								.addClass("save_rtcl_listing")
								.attr("name", "save")
								.val("' . esc_html( $btnText ) . '");
								' . $js . '
					  })(jQuery)
                  </script>';
		}
	}
}