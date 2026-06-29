<?php

namespace Rtcl\Controllers\Admin\Meta;

use Rtcl\Services\FormBuilder\FBHelper;

class RemoveMetaBox {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'remove_meta_box' ] );
	}

	function remove_meta_box() {
		remove_meta_box( rtcl()->category . 'div', rtcl()->post_type, 'side' );
		remove_meta_box( rtcl()->location . 'div', rtcl()->post_type, 'side' );
		remove_meta_box( 'submitdiv', rtcl()->post_type_payment, 'side' );
		remove_meta_box( 'postcustom', rtcl()->post_type, 'normal' );
		if ( FBHelper::isEnabled() ) {
			remove_meta_box( 'tagsdiv-' . rtcl()->tag, rtcl()->post_type, 'side' );
		}
	}
}