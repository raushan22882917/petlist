<?php

namespace Rtcl\Services\FormBuilder;

class LocalizedString {

	public static function public() {
		$strings = [
			'no_options_found'   => __( 'No options found', 'classified-listing' ),
			'close'              => __( 'Close', 'classified-listing' ),
			'loading'            => __( 'Loading ....', 'classified-listing' ),
			'unload_message'     => __( 'Changes that you made may not be saved.', 'classified-listing' ),
			'enable'             => __( 'Enable', 'classified-listing' ),
			'type'               => __( 'Type', 'classified-listing' ),
			'open'               => __( 'Open', 'classified-listing' ),
			'save'               => __( 'Save', 'classified-listing' ),
			'term_suggest_close' => __( 'Close', 'classified-listing' ),
			'submit'             => __( 'Submit', 'classified-listing' ),
			'update'             => __( 'Update', 'classified-listing' ),
			'change'             => __( 'Change', 'classified-listing' ),
			'cancel'             => __( 'Cancel', 'classified-listing' ),
			'restore'            => __( 'Restore', 'classified-listing' ),
			'error_saving'       => __( 'Error while saving data', 'classified-listing' ),
			'character_limit'    => __( 'Character limit', 'classified-listing' ),
			'confirm'            => __( 'Are you sure to remove?', 'classified-listing' ),
			'select_item'        => __( 'Please select an item', 'classified-listing' ),
			'type_to_search'     => __( 'Type to search', 'classified-listing' ),
			'add_new'            => __( 'Add New', 'classified-listing' ),
			'required'           => __( 'Field is required', 'classified-listing' ),
			'upload'             => __( 'Upload', 'classified-listing' ),
			'edit'               => __( 'Edit', 'classified-listing' ),
			'delete'             => __( 'Delete', 'classified-listing' ),
			'preview'            => __( 'Preview', 'classified-listing' ),
			'undo'               => __( 'Undo', 'classified-listing' ),
			'back'               => __( 'Back', 'classified-listing' ),
			'scale'              => __( 'Scale', 'classified-listing' ),
			'past_error'         => __( 'Pasting this exceeds the maximum allowed number of ___ characters for the input.', 'classified-listing' ),
			'reCaptcha'          => [
				'error' => __( 'reCaptcha site key is missing.', 'classified-listing' ),
			],
			'location'           => [
				'select'  => __( 'Select a location', 'classified-listing' ),
				'no_data' => __( 'No location found', 'classified-listing' ),
			],
			'category'           => [
				'select'  => __( 'Select a category', 'classified-listing' ),
				'no_data' => __( 'No category found', 'classified-listing' ),
			],
			'file'               => [
				'description'        => __( 'Description', 'classified-listing' ),
				'caption'            => __( 'Caption', 'classified-listing' ),
				'featured'           => __( 'Featured', 'classified-listing' ),
				'add_feature'        => __( 'Add to feature', 'classified-listing' ),
				'file_name'          => __( 'File name', 'classified-listing' ),
				'file_type'          => __( 'File type', 'classified-listing' ),
				'file_size'          => __( 'File size', 'classified-listing' ),
				'dimensions'         => __( 'Dimensions', 'classified-listing' ),
				'uploaded_on'        => __( 'Uploaded on', 'classified-listing' ),
				'upload_btn'         => __( 'Click or drag file to this area to upload', 'classified-listing' ),
				'attachment_details' => __( 'Attachment Details', 'classified-listing' ),
				'upload_success'     => __( 'File successfully uploaded', 'classified-listing' ),
				'remove_success'     => __( 'File successfully removed', 'classified-listing' ),
				'upload_error'       => __( 'Error while uploading file', 'classified-listing' ),
				'remove_error'       => __( 'Error while removing file.', 'classified-listing' ),
				'updating_error'     => __( 'Error while updating file.', 'classified-listing' ),
				'getting_error'      => __( 'Error while getting data', 'classified-listing' ),
			],
			'image'              => [
				'edit_image'             => __( 'Edit Image', 'classified-listing' ),
				'edit_image_ai'          => __( 'Edit with AI', 'classified-listing' ),
				'edit_image_ai_title'    => __( 'Enhance Image with AI', 'classified-listing' ),
				'create_image'           => __( 'Create Image', 'classified-listing' ),
				'crop_area_not_selected' => __( 'Crop Area not selected!!', 'classified-listing' ),
				'apply_crop'             => __( 'Apply Crop', 'classified-listing' ),
				'rotate_90'              => __( 'Rotate 90 degrees', 'classified-listing' ),
				'rotate__90'             => __( 'Rotate -90 degrees', 'classified-listing' ),
				'flip_v'                 => __( 'Flip Vertically', 'classified-listing' ),
				'flip_h'                 => __( 'Flip Horizontally', 'classified-listing' ),
				'selection'              => __( 'Selection', 'classified-listing' ),
				'clear_selection'        => __( 'Clear Selection', 'classified-listing' ),
				'apply_to_all_image'     => __( 'Apply changes to all generated image sizes', 'classified-listing' ),
				'original_size'          => __( 'Original size', 'classified-listing' ),
				'current_size'           => __( 'Current size', 'classified-listing' ),
				'recommended_size'       => __( 'Recommended size', 'classified-listing' ),
				'zoom'                   => __( 'Zoom', 'classified-listing' ),
				'dimension'              => __( 'Dimension', 'classified-listing' ),
				'position'               => __( 'Position', 'classified-listing' ),
				'upscale'                => __( 'Enhance Resolution', 'classified-listing' ),
				'brightness'             => __( 'Adjust Brightness', 'classified-listing' ),
				'crop'                   => __( 'Crop Image', 'classified-listing' ),
				'resize'                 => __( 'Resize Resolution', 'classified-listing' ),
				'remove_bg'              => __( 'Remove Background', 'classified-listing' ),
				'others'                 => __( 'Custom Prompt', 'classified-listing' ),
				'prompt'                 => __( 'Prompt', 'classified-listing' ),
				'ai_request'             => __( 'Send to AI', 'classified-listing' ),
			],
			'map'                => [
				'enter_address'      => __( 'Enter address, please', 'classified-listing' ),
				'marker_with_pop_up' => __( 'A marker with a popup.', 'classified-listing' ),
				'latitude'           => __( 'Latitude', 'classified-listing' ),
				'longitude'          => __( 'Longitude', 'classified-listing' ),
				'dont_show_map'      => __( 'Don\'t show the Map', 'classified-listing' ),
			],
			'repeater'           => [
				'max_error' => __( 'Maximum repeater field applied', 'classified-listing' ),
			],
			'pricing'            => [
				'select_currency' => __( 'Select a currency', 'classified-listing' ),
				'currency'        => __( 'Currency', 'classified-listing' ),
				'no_unit'         => __( 'No unit', 'classified-listing' ),
				'max'             => __( 'Max', 'classified-listing' ),
			],
			'bsh'                => [
				'open_24'             => __( 'Open 24 hours', 'classified-listing' ),
				'open_24_7'           => __( 'Open 24 hours 7 days', 'classified-listing' ),
				'open_selected_hours' => __( 'Open for Selected Hours', 'classified-listing' ),
				'special_hours'       => __( 'Special Hours - Overrides', 'classified-listing' ),
				'once'                => __( 'Once', 'classified-listing' ),
				'repeat'              => __( 'Repeat', 'classified-listing' ),
				'timezone'            => __( 'Timezone', 'classified-listing' ),
				'select_timezone'     => __( 'Select a timezone', 'classified-listing' ),
			],
			'color_picker'       => [
				'select_color' => __( 'Select Color', 'classified-listing' ),
			],
		];

		return apply_filters( 'rtcl_fb_localized_public_strings', $strings );
	}

	public static function admin() {
		$adminStrings = [
			'step'                           => __( 'Step', 'classified-listing' ),
			'section'                        => __( 'Section', 'classified-listing' ),
			'check_all'                      => __( 'Check All', 'classified-listing' ),
			'pro'                            => __( 'Pro', 'classified-listing' ),
			'clear_filter'                   => __( 'Clear Filter', 'classified-listing' ),
			'search'                         => __( 'Search...', 'classified-listing' ),
			'assigned'                       => __( 'Assigned', 'classified-listing' ),
			'unassigned'                     => __( 'Unassigned', 'classified-listing' ),
			'bulk_assignment'                => __( 'Bulk Assignment', 'classified-listing' ),
			'bulk_dependency_assignment'     => __( 'Bulk Dependency Assignment ', 'classified-listing' ),
			'bulk_category'                  => __( 'Bulk Category', 'classified-listing' ),
			'deprecated'                     => __( 'Deprecated', 'classified-listing' ),
			'bulk_category_logic_assignment' => __( 'Bulk Category Assignment', 'classified-listing' ),
			'checked'                        => __( 'Checked', 'classified-listing' ),
			'_select_'                       => __( '- Select -', 'classified-listing' ),
			'form_list'                      => __( 'Form List', 'classified-listing' ),
			'form'                           => __( 'Form', 'classified-listing' ),
			'single_layout'                  => __( 'Single Listing Layout', 'classified-listing' ),
			'select_all_fields'              => __( 'Please select all field', 'classified-listing' ),
			'update_form'                    => __( 'Update From', 'classified-listing' ),
			'save_form'                      => __( 'Save Form', 'classified-listing' ),
			'form_name'                      => __( "From name", 'classified-listing' ),
			'rename_form'                    => __( "Rename from", 'classified-listing' ),
			'input_fields'                   => __( "Input Fields", 'classified-listing' ),
			'customization'                  => __( 'Customization', 'classified-listing' ),
			'settings'                       => __( 'Settings', 'classified-listing' ),
			'fields'                         => __( 'Fields', 'classified-listing' ),
			'input_customization'            => __( 'Input Customization', 'classified-listing' ),
			'add_new_section'                => __( 'Add New Section', 'classified-listing' ),
			'add_new_container'              => __( 'Add New Container', 'classified-listing' ),
			'drag_drop_your_field'           => __( 'Drag & Drop your field', 'classified-listing' ),
			'are_u_sure_to_remove'           => __( 'Are you sure you want to delete?', 'classified-listing' ),
			'status'                         => [
				'publish' => __( 'Publish', 'classified-listing' ),
				'draft'   => __( 'Draft', 'classified-listing' ),
			],
		];
		$adminStrings = apply_filters( 'rtcl_fb_localized_admin_strings', $adminStrings );

		$strings          = self::public();
		$strings['admin'] = $adminStrings;

		return $strings;
	}
}