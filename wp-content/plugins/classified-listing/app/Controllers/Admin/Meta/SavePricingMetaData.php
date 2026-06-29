<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;

class SavePricingMetaData {

	public function __construct() {
		add_action( 'save_post', [ $this, 'save_pricing_meta_data' ], 10, 2 );
	}


	/**
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed|void
	 */
	function save_pricing_meta_data( $post_id, $post ) {
		
		if ( ! wp_verify_nonce( isset( $_REQUEST[ rtcl()->nonceId ] ) ? $_REQUEST[ rtcl()->nonceId ] : null, rtcl()->nonceText ) ) {
			return $post_id;
		}
		
		if ( !isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( rtcl()->post_type_pricing != $post->post_type ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Price
		$data = [
			'price'   => [
				'old' => get_post_meta( $post_id, 'price', true ),
				'new' => isset( $_POST['price'] ) ? Functions::format_decimal( $_POST['price'] ) : get_post_meta( $post_id, 'price', true )
			],
			'visible' => [
				'old' => get_post_meta( $post_id, 'visible', true ),
				'new' => isset( $_POST['visible'] ) ? absint( $_POST['visible'] ) : get_post_meta( $post_id, 'visible', true )
			]
		];

		$data = apply_filters( 'rtcl_before_save_pricing_meta_data', $data, $post_id, $post );

		if ( $data['price']['old'] !== $data['price']['new'] ) {
			update_post_meta( $post_id, 'price', $data['price']['new'] + 0 );
		}

		if ( $data['visible']['old'] !== $data['visible']['new'] ) {
			update_post_meta( $post_id, 'visible', $data['visible']['new'] );
		}

		if ( isset( $_POST['description'] ) ) {
			$description = Functions::sanitize( $_POST['description'], 'html_textarea' );
			update_post_meta( $post_id, 'description', $description );
		}

		if ( isset( $_POST['featured'] ) ) {
			update_post_meta( $post_id, 'featured', 1 );
		} else {
			delete_post_meta( $post_id, 'featured' );
		}

		do_action( 'rtcl_save_pricing_meta_data', $post_id, $_POST, $post, $data );
	}
}
