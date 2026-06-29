<?php

namespace Rtcl\Resources;

use Rtcl\Helpers\Functions;

class Gallery {

	public function __construct() {

	}

	/**
	 * @param null  $post
	 * @param array $conf
	 */
	static function rtcl_gallery_content( $post = null, $conf = [] ) {

		wp_nonce_field( plugin_basename( __FILE__ ), 'rtcl_gallery_content_nonce' );
		$field_name = "gallery";
		$button     = "rtcl-button";
		wp_enqueue_script( 'image-edit' );
		wp_enqueue_style( 'jcrop' );
		if ( is_admin() ) {
			$button = "button";
		}

		$conf            = shortcode_atts( [
			"button_class"  => "button-secondary",
			"post_id_input" => "#post_ID"
		], $conf );
		$max_image_limit = Functions::get_option_item( 'rtcl_moderation_settings', 'maximum_images_per_listing', 5 );
		$max_image_size  = Functions::get_max_upload();
		$init            = [
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => 'rtcl-gallery-browse-button',
			'container'           => 'rtcl-gallery-upload-ui-wrapper',
			'drop_element'        => 'rtcl-gallery-drag-drop-area',
			'file_data_name'      => 'async-upload',
			'multiple_queues'     => false,
			'max_file_size'       => $max_image_size . 'b',
			'max_files'           => $max_image_limit,
			'url'                 => admin_url( 'admin-ajax.php' ),
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => [
				[
					'title'      => __( 'Allowed Files', "classified-listing" ),
					'extensions' => implode( ",",
						apply_filters( 'rtcl_gallery_image_allowed_extensions', Functions::get_option_item( 'rtcl_misc_media_settings', 'image_allowed_type', [
							'jpg',
							'jpeg',
							'png'
						] ) ) )
				]
			],
			'multipart'           => true,
			'urlstream_upload'    => true,
			// additional post data to send to our ajax hook
			'multipart_params'    => [
				'_ajax_nonce' => wp_create_nonce( 'rtcl-gallery' ),
				'action'      => 'rtcl_gallery_upload',            // the ajax action name
				'form'        => 'rtcl_add',
				'form_scheme' => '',
				'field_name'  => $field_name

			]
		];
		?>
		<div class="form-group">
			<div id="rtcl-gallery-upload-ui-wrapper"
				 class="<?php echo is_admin() ? "rtcl-browser-admin" : "rtcl-browser-frontend" ?>">
				<div id="rtcl-gallery-drag-drop-area" class="rtcl-drag-drop-area"></div>
				<div class="rtcl-gallery">
					<p><?php esc_html_e( "Drop files here to add them.", "classified-listing" ) ?></p>
					<p><a href="#" id="rtcl-gallery-browse-button"
						  class="rtcl-gallery-browse-button rtcl-btn rtcl-btn-primary <?php echo esc_attr( $button ) ?>"><?php esc_html_e( "Browse files ...",
								"classified-listing" ) ?></a>
					</p>
				</div>
				<div class="rtcl-gallery-uploads"></div>
				<div class="rtcl-notices-wrapper">
					<div class="description alert alert-danger">
						<?php
						$image_size = Functions::get_option_item( 'rtcl_misc_media_settings', 'image_size_gallery', [] );
						if ( Functions::is_gallery_image_required() ) { ?>
							<p><?php esc_html_e( "Image is required.", "classified-listing" ); ?></p>
							<?php
						}
						printf(
						/* translators: Image size. 1: Width, 2: Height. */
							'<p>' . esc_html__( 'Recommended image size to (%1$s, %2$s)px', "classified-listing" ) . '</p>',
							isset( $image_size['width'] ) ? absint( $image_size['width'] ) : 0,
							isset( $image_size['height'] ) ? absint( $image_size['height'] ) : 0
						);
						printf(
						/* translators: Image max size */
							'<p>' . esc_html__( "Image maximum size %s.", "classified-listing" ) . '</p>',
							esc_html( Functions::formatBytes( $max_image_size, 0 ) )
						);
						printf(
						/* translators: Image allowed type */
							'<p>' . esc_html__( "Allowed image type (%s).", "classified-listing" ) . '</p>',
							esc_html( implode( ', ', (array) Functions::get_option_item( 'rtcl_misc_media_settings', 'image_allowed_type', [
								'png',
								'jpeg',
								'jpg'
							] ) ) )
						);
						printf(
						/* translators: Allowed image count */
							'<p>' . esc_html__( "You can upload up to %d images.", "classified-listing" ) . '</p>',
							absint( $max_image_limit )
						);
						?>
					</div>
				</div>
			</div>
		</div>
		<?php

		add_action( "wp_footer", [ self::class, "rtcl_gallery_modal" ] );
		add_action( "admin_footer", [ self::class, "rtcl_gallery_modal" ] );

		// Get data for uploaded items and format it as JSON.
		$data = [];
		if ( $post ) {

			$children = get_children( [
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'posts_per_page' => - 1,
				'post_status'    => 'inherit'
			] );

			$children = Functions::sort_images( $children, $post->ID );

			foreach ( $children as $child ) {
				$data[] = Functions::upload_item_data( $child->ID );
			}

		}

		$sizes = [];
		foreach ( rtcl()->gallery['image_sizes'] as $size_key => $size ) {
			$sizes[ str_replace( "-", "_", $size_key ) ] = $size;
		}
		$upload_config = apply_filters( 'rtcl_gallery_upload_config', [
			"init"  => $init,
			"data"  => $data,
			"conf"  => $conf,
			"sizes" => $sizes
		] );


		?>
		<script type="text/javascript">
			if (typeof RTCL_PLUPLOAD_DATA === "undefined") {
				var RTCL_PLUPLOAD_DATA = [];
			}
			RTCL_PLUPLOAD_DATA.push(<?php echo wp_json_encode( $upload_config ) ?>);

			if (typeof RTCL_IMAGE_SIZES === "undefined") {
				var RTCL_IMAGE_SIZES = <?php echo wp_json_encode( $upload_config['sizes'] ) ?>;
			}
		</script>
		<?php
	}

	static function rtcl_gallery_modal() {

		$button = "rtcl-button";

		if ( is_admin() ) {
			$button = "button";
		}

		?>

		<script type="text/html" id="tmpl-wprtcl-uploaded-file">
			<# if(data.result === null) { #>
			<div class="rtcl-gallery-upload-update rtcl-spinner"><span class="rtcl-icon-spinner animate-spin"></span>
			</div>
			<# } else if(typeof data.result.error != "undefined") { #>
			<div class="rtcl-gallery-upload-update rtcl-icon rtcl-icon-attention">
				<span class="rtcl-gallery-upload-failed">{{ data.result.error }}</span>
			</div>
			<# } else { #>
			<div class="rtcl-spinner rtcl-gallery-upload-update" style="position: absolute; display: none"><span
					class="rtcl-icon-spinner animate-spin"></span></div>

			<# if( data.result.sizes.rtcl_gallery_thumbnail.url ) { #>
			<img src="{{ data.result.sizes.rtcl_gallery_thumbnail.url }}" alt="" class="rtcl-gallery-upload-item-img"/>
			<# } else { #>
			<span class="rtcl-gallery-upload-item-file">
                <span class="rtcl-gallery-upload-item-file-icon {{ data.icon }}"></span>
                <span class="rtcl-gallery-upload-item-file-name">{{ data.result.readable.name }}</span>
            </span>
			<# } #>

			<div class="rtcl-gallery-item-features">
				<# if(data.result.featured) { #>
				<span class="rtcl-gallery-item-feature rtcl-icon rtcl-icon-flag"
					  title="<?php esc_html_e( "Featured", 'classified-listing' ) ?>"></span>
				<# } #>
			</div>


			<div class="rtcl-gallery-upload-actions">
				<?php if ( Functions::user_can_edit_image() ) : ?>
					<a href="#"
					   class="rtcl-button-edit <?php echo esc_attr( $button ) ?> rtcl-button-icon rtcl-icon-pencil"
					   title="<?php esc_html_e( "Edit File", 'classified-listing' ) ?>"></a>
				<?php endif; ?>
				<a href="#"
				   class="rtcl-button-remove <?php echo esc_attr( $button ) ?> rtcl-button-icon rtcl-icon-trash"
				   title="<?php esc_html_e( "Delete File", 'classified-listing' ) ?>"></a>
			</div>
			<# } #>
		</script>

		<script type="text/html" id="tmpl-wprtcl-browser">
			<div
				class="wprtcl-overlay wprtcl-overlay-dark <?php echo is_admin() ? "rtcl-browser-admin" : "rtcl-browser-frontend" ?>">

				<div class="wprtcl-overlay-body">

					<div class="wprtcl-overlay-header">
						<div class="wprtcl-overlay-title">
							<?php esc_html_e( "Attachment Details", 'classified-listing' ) ?>
						</div>
						<div class="wprtcl-overlay-buttons">
							<span
								class="wprtcl-overlay-button wprtcl-file-pagi-prev rtcl-icon-left-open wprtcl-navi-disabled"></span>
							<span
								class="wprtcl-overlay-button wprtcl-file-pagi-next rtcl-icon-right-open wprtcl-navi-disabled"></span>
							<a href="#" class="wprtcl-overlay-button wprtcl-overlay-close rtcl-icon-cancel"
							   title="Close"></a>
						</div>
					</div>

					<div class="wprtcl-attachment-details">

					</div>
				</div>
			</div>
		</script>

		<script type="text/html" id="tmpl-wprtcl-browser-attachment-view">
			<div class="wprtcl-attachment-media-view wprtcl-overlay-content">
				<# if( data.mime == "image" ) { #>
				<# for(var size in data.file.sizes) { #>
				<div class="rtcl-image-preview rtcl-image-preview-{{ size }}">
					<img src="{{ data.file.sizes[size].url }}?timestamp={{ data.timestamp }}" class="" alt=""/>
				</div>
				<# } #>
				<# } else if( data.mime == "audio" ) { #>
				<div class="wprtcl-attachment-audio">
					<div class="wprtcl-attachment-icon-big-wrap">
						<span class="wprtcl-attachment-icon-big {{ data.icon }}"></span>
					</div>
					<div class="wprtcl-attachment-icon-big-wrap">
						<span>{{ data.file.readable.name }} </span>
					</div>
					<audio src="{{ data.file.guid }}"></audio>
				</div>
				<# } else if(data.mime == "other") { #>
				<div class="wprtcl-attachment-other">
					<div class="wprtcl-attachment-icon-big-wrap">
						<span class="wprtcl-attachment-icon-big {{ data.icon }}"></span>
					</div>
					<div class="wprtcl-attachment-icon-big-wrap">
						<span>{{ data.file.readable.name }} </span>
					</div>
					<a href="{{ data.file.guid }}"
					   class="<?php echo esc_attr( $button ) ?>"><?php esc_html_e( "Download File", 'classified-listing' ) ?></a>
				</div>
				<# } #>
			</div>

			<div class="wprtcl-attachment-info">
				<form action="" method="post" class="rtcl-form rtcl-form-aligned">
					<fieldset>
						<# if( data.mime == "image") { #>
						<div class="rtcl-control-group">
							<label for="rtcl_featured"><?php esc_html_e( "Featured", "classified-listing" ) ?></label>
							<label for="rtcl_featured"
								   class="inline-block"><input type="checkbox" id="rtcl_featured" name="rtcl_featured"
															   value="1" <#
								if(data.file.featured) { #>checked="checked"<# } #>
								/> <?php esc_html_e( "Use this image as main image", "classified-listing" ) ?></label>
						</div>
						<# } #>

						<div class="rtcl-control-group">
							<label for="rtcl_caption"><?php esc_html_e( "Title", 'classified-listing' ) ?></label>
							<input type="text" id="rtcl_caption" name="rtcl_caption" value="{{ data.file.caption }}"/>
						</div>

						<div class="rtcl-control-group">
							<label for="rtcl_content"><?php esc_html_e( "Description", 'classified-listing' ) ?></label>
							<textarea id="rtcl_content" name="rtcl_content">{{ data.file.content }}</textarea>
						</div>

					</fieldset>
				</form>

				<div>
					<a href="#"
					   class="<?php echo esc_attr( $button ) ?> <?php echo is_admin() ? ' button-primary'
						   : 'btn btn-primary'; ?> rtcl-upload-modal-update"><?php esc_html_e( "Update Options", "classified-listing" ) ?></a>
					<span class="rtcl-spinner rtcl-icon-spinner animate-spin" style="display: none"></span>
					<span class="rtcl-update-description-success rtcl-icon-ok"></span>
				</div>


				<# if( data.mime == "image") { #>
				<div class="wprtcl-file-preview">
					<form action="" method="post" class="rtcl-form rtcl-form-aligned">
						<fieldset>
							<div class="rtcl-control-group">
								<label><?php esc_html_e( "Preview", "classified-listing" ) ?></label>
								<select class="wprtcl-image-sizes">
									<?php foreach ( self::rtcl_gallery_explain_size() as $key => $size ): ?>
										<?php if ( ( $key == "full" || has_image_size( $key ) ) ): ?>
											<option value="<?php echo esc_html( str_replace( "-", "_", $key ) ) ?>"
													data-explain="<?php echo esc_attr( isset( $size["desc_parsed"] ) ? $size["desc_parsed"]
														: $size["desc"] ) ?>"><?php echo esc_html( $size["title"] ) ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="wprtcl-file-size-explain rtcl-control-group">
								<span class="rtcl-icon-info-circled"></span>
								<span class="rtcl-icon-size-explain-desc">-</span>
							</div>

							<?php if ( Functions::user_can_edit_image() ): ?>
								<div class="rtcl-control-group wprtcl-file-browser-image-actions">
									<a href="#"
									   class="<?php echo esc_attr( $button ) ?> wprtcl-attachment-edit-image"><?php esc_html_e( "Edit Image",
											'classified-listing' ) ?></a>
									<a href="#" class="<?php echo esc_attr( $button ) ?> wprtcl-attachment-create-image"
									   title="<?php esc_html_e( "Create thumbnail from full size image.",
										   'classified-listing' ) ?>"><?php esc_html_e( "Create Image", 'classified-listing' ) ?></a>
								</div>
							<?php endif; ?>

						</fieldset>
					</form>
				</div>
				<# } #>

				<div class="details">
					<# if( data.file.readable.name ) { #>
					<div class="filename"><strong><?php esc_html_e( "File name:", 'classified-listing' ) ?></strong> {{
						data.file.readable.name }}
					</div>
					<# } #>

					<# if( data.file.readable.type ) { #>
					<div class="filename"><strong><?php esc_html_e( "File type:", 'classified-listing' ) ?></strong> {{
						data.file.readable.type }}
					</div>
					<# } #>

					<# if( data.file.readable.uploaded ) { #>
					<div class="uploaded"><strong><?php esc_html_e( "Uploaded on:", 'classified-listing' ) ?></strong>
						{{
						data.file.readable.uploaded
						}}
					</div>
					<# } #>

					<# if( data.file.readable.size ) { #>
					<div class="file-size"><strong><?php esc_html_e( "File size:", 'classified-listing' ) ?></strong> {{
						data.file.readable.size }}
					</div>
					<# } #>

					<# if( data.file.readable.dimensions ) { #>
					<div class="dimensions"><strong><?php esc_html_e( "Dimensions:", 'classified-listing' ) ?></strong>
						{{
						data.file.readable.dimensions }}
					</div>
					<# } #>

					<# if( data.file.readable.length ) { #>
					<div class="formatted-length">
						<strong><?php esc_html_e( "Length:", 'classified-listing' ) ?></strong> {{
						data.file.readable.length
						}}
					</div>
					<# } #>

					<div class="compat-meta"></div>
				</div>
			</div>
		</script>

		<?php if ( Functions::user_can_edit_image() ): ?>
			<script type="text/html" id="tmpl-wprtcl-browser-attachment-image">
				<div class="wprtcl-attachment-media-view wprtcl-overlay-content wprtcl-attachment-media-image-editor">
					<div class="wprtcl-attachment-image">
						<img src="#"
							 data-src="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>?action=rtcl_gallery_image_stream&post_id={{ data.file.post_id }}&attach_id={{ data.file.attach_id }}&size={{ data.size }}&history={{ data.history }}&rand={{ data.rand }}&_ajax_nonce={{ data.nonce }}"
							 id="wprtcl-image-crop" alt="" style="max-width: 100%; max-height: 100%;">
					</div>
				</div>

				<div class="wprtcl-attachment-info">

					<form action="" method="post" class="rtcl-form rtcl-form-aligned">
						<fieldset>
							<div class="rtcl-control-group">
								<label
									for="rtcl_featured"><?php esc_html_e( "Image Manipulation", 'classified-listing' ) ?></label>
								<a href="#"
								   class="rtcl-image-action-crop <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Crop", 'classified-listing' ) ?>"><span
										class="rtcl-icon-crop"></span></a>
								<a href="#"
								   class="rtcl-image-action-rotate-cw <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Rotate 90 degrees", 'classified-listing' ) ?>"><span
										class="rtcl-icon-cw"></span></a>
								<a href="#"
								   class="rtcl-image-action-rotate-ccw <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Rotate -90 degrees", 'classified-listing' ) ?>"><span
										class="rtcl-icon-ccw"></span></a>
								<a href="#"
								   class="rtcl-image-action-flip-h <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Flip Vertically", 'classified-listing' ) ?>"><span
										class="rtcl-icon-resize-vertical"></span></a>
								<a href="#"
								   class="rtcl-image-action-flip-v <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Flip Horizontally", 'classified-listing' ) ?>"><span
										class="rtcl-icon-resize-horizontal"></span></a>
								<a href="#"
								   class="rtcl-image-action-undo <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Undo", 'classified-listing' ) ?>"><span
										class="rtcl-icon-history"></span></a>

							</div>

							<div class="rtcl-control-group">
								<label
									for="rtcl_caption"><?php esc_html_e( "Image Size", 'classified-listing' ) ?></label>
								<input type="number" class="rtcl-image-scale-width" name="d_width"
									   value="{{ data.dim[0] }}" max="{{ data.dim[0] }}" step="1"/>
								x
								<input type="number" class="rtcl-image-scale-height" name="d_height"
									   value="{{ data.dim[1] }}" max="{{ data.dim[1] }}" step="1"/>
								<a href="#"
								   class="rtcl-image-action-scale <?php echo esc_attr( $button ) ?> rtcl-button-small"><?php esc_html_e( "Scale",
										'classified-listing' ) ?></a>
							</div>

							<div class="rtcl-control-group">
								<label for="rtcl_content"><?php esc_html_e( "Actions", 'classified-listing' ) ?></label>

								<a href="#"
								   class="rtcl-image-action-save <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Save Image", 'classified-listing' ) ?>"><?php esc_html_e( "Save", 'classified-listing' ) ?></a>
								<a href="#"
								   class="rtcl-image-action-cancel <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Cancel", "classified-listing" ) ?>"><?php esc_html_e( "Cancel", 'classified-listing' ) ?></a>
								&nbsp;
								<a href="#"
								   class="rtcl-image-action-restore <?php echo esc_attr( $button ) ?> rtcl-button-small"
								   title="<?php esc_html_e( "Restore original image", 'classified-listing' ) ?>"><?php esc_html_e( "Restore",
										'classified-listing' ) ?></a>

								&nbsp;

								<span class="wprtcl-image-edit-spinner rtcl-icon-spinner animate-spin"
									  style="display: none"></span>

								<# if(data.size == "full") { #>
								<div class="wprtcl-image-apply-to">
									<input type="checkbox" name="wprtcl-image-action-apply-to"
										   class="wprtcl-image-action-apply-to" value="1" checked="checked"/>
									<label
										for="wprtcl-image-action-apply-to"><?php esc_html_e( "Apply changes to all image sizes",
											'classified-listing' ) ?></label>
								</div>
								<# } else { #>
								<input type="hidden" name="wprtcl-image-action-apply-to"
									   class="wprtcl-image-action-apply-to" value="0"/>
								<# } #>
							</div>


						</fieldset>
					</form>

					<div class="details">

						<div class="filename">
							<strong><?php esc_html_e( "Original size:", 'classified-listing' ) ?></strong> <span
								class="rtcl-image-prop-original-size">-</span></div>
						<div class="filename">
							<strong><?php esc_html_e( "Current size:", 'classified-listing' ) ?></strong> <span
								class="rtcl-image-prop-current-size">-</span></div>
						<# if(data.recommended !== null ) { #>
						<div class="filename">
							<strong><?php esc_html_e( "Recommended size:", 'classified-listing' ) ?></strong>
							<span
								class="rtcl-image-prop-recommended-size"> {{ data.recommended.width }} x {{ data.recommended.height }}</span>
						</div>
						<# } #>
						<div class="filename"><strong><?php esc_html_e( "Zoom:", 'classified-listing' ) ?></strong>
							<span
								class="rtcl-image-prop-zoom">100%</span></div>
						<div class="wprtcl-image-selection">
							<strong><?php esc_html_e( "Selection:", 'classified-listing' ) ?></strong> <span
								class="rtcl-image-prop-selection">-</span></div>

					</div>
				</div>
			</script>
		<?php endif; ?>

		<script type="text/html" id="tmpl-wprtcl-browser-error">
			<# if(data.overlay === true) { #>
			<div class="wprtcl-overlay">
				<# } #>
				<div class="wprtcl-file-error">

					<div class="wprtcl-attachment-other">
						<div class="wprtcl-attachment-icon-big-wrap">
							<span class="wprtcl-attachment-icon-big rtcl-icon-attention"></span>
						</div>
						<div class="wprtcl-attachment-icon-big-wrap">
                    <span>
                        <strong>{{ data.error }}</strong>
                    </span>
						</div>
						<a href="#"
						   class="<?php echo esc_attr( $button ) ?>"><?php esc_html_e( "Close", 'classified-listing' ) ?></a>
					</div>
				</div>
				<# if(data.overlay === true) { #>
			</div>
			<# } #>
		</script>

		<?php
	}

	static function rtcl_gallery_explain_size( $size = null ) {

		$e = apply_filters( "rtcl_gallery_explain", [
			"full"                   => [
				"title" => __( "Gallery - Full Size", "classified-listing" ),
				"desc"  => __( "Image in original size - used on classified details page in the gallery.", "classified-listing" )
			],
			"rtcl-gallery"           => [
				"title" => __( "Gallery - Slider", "classified-listing" ),
				/* translators: Image Width and height */
				"desc"  => __( 'Image resized to %1$s x %2$s - used in the images slider on classified details page.', "classified-listing" )
			],
			"rtcl-thumbnail"         => [
				"title" => __( "Listing Thumbnail", "classified-listing" ),
				/* translators: Image Width and height */
				"desc"  => __( 'Image resized to  %1$s x %2$s - used on the classifieds list.', "classified-listing" )
			],
			"rtcl-gallery-thumbnail" => [
				"title" => __( "Gallery Thumbnail", "classified-listing" ),
				/* translators: Image Width and height */
				"desc"  => __( 'Image resized to %1$s x %2$s - the image visible in upload preview.', "classified-listing" )
			],
		], $size );

		$sizes = rtcl()->gallery['image_sizes'];

		foreach ( $e as $key => $s ) {
			if ( isset( $sizes[ $key ] ) ) {
				$e[ $key ]["desc_parsed"] = sprintf( $s["desc"], $sizes[$key ]["width"], $sizes[$key ]["height"] );
				$e[ $key ] = $e[ $key ] +  $sizes[ $key ]; 
			}
		}

		if ( $size === null ) {
			return $e;
		}

		if ( isset( $e["size"] ) ) {
			return $e["size"];
		}
	}

}
