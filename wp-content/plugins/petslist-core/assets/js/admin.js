"use strict";

(function ($) {
	
	function templateForCustomFont(icon) {
		var originalOption = icon.element;
		var $icon_block = '<span class="' + $(originalOption).data('icon') + '"></span>' + icon.text;
		var $html = $("<div></div>").html($icon_block).addClass('icon-wrapper');
		return $html;
	}

	// option format function for select 2 icon
	function onReadyScripts() {
		if ($.fn.select2) {
			$('.select2_font_awesome').select2({
			width: "100%",
			templateResult: templateForCustomFont,
			templateSelection: templateForCustomFont,
			});
		}
	}

	$(document).ready(function () {
		onReadyScripts();

		//category color
		$( '.colorpicker' ).wpColorPicker();	
		$('.color-picker').wpColorPicker({
			change: function (event, ui) {
				var _self = $(this),
					parent = _self.parents('form'),
					targetBtn = $('input[name="savewidget"]', parent);
					targetBtn.prop('disabled', false).val('Save');
			}
		});

		//category image upload
	    let meta_image_frame;	     
	    $('#upload_image_btn').click(function(e){
	        e.preventDefault();
	        if ( meta_image_frame ) {
	            meta_image_frame.open();
	            return;
	        }

	        // Sets up the media library frame
	        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
	            title: 'Upload Category Image',
	            button: { text: 'Upload Image' },
	            library: { type: 'image' }
	        });

	        meta_image_frame.on('select', function(){
	            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
	            $('.category-image').html(`<div class='category-image-wrap'><img src='${media_attachment.url}' width='200' /><input type="hidden" name="rt_category_image" value='${media_attachment.id}' class="category-image-id"/><button>x</button></div>`); 
	        });

	        meta_image_frame.open();
	    });

	    $(document).on("click",".category-image-wrap button",function() {
	        $(this).parent().remove();
	    });
	})



// jquery passing
})(jQuery);