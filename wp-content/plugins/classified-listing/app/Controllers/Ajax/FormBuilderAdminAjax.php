<?php

namespace Rtcl\Controllers\Ajax;

use Exception;
use Rtcl\Controllers\AIServiceFactory;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Form\Form;
use Rtcl\Services\FormBuilder\AvailableFields;
use Rtcl\Services\FormBuilder\Components\FieldSanitization;
use Rtcl\Services\FormBuilder\Components\SectionSanitization;
use Rtcl\Services\FormBuilder\Components\SettingsFieldSanitization;
use Rtcl\Services\FormBuilder\Components\TranslationSanitization;
use Rtcl\Services\FormBuilder\ElementCustomization;
use Rtcl\Services\FormBuilder\FBHelper;
use Rtcl\Services\FormBuilder\FormPreDefined;
use Rtcl\Traits\SingletonTrait;
use RuntimeException;
use stdClass;

class FormBuilderAdminAjax {

	use SingletonTrait;


	function init(): void {
		add_action( 'wp_ajax_rtcl_fb_admin_form_update', [ $this, 'form_update' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_update_partial', [ $this, 'form_update_partial' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_create', [ $this, 'form_create' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_list', [ $this, 'form_list' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_get', [ $this, 'form_get' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_update_slug', [ $this, 'form_update_slug' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_mark_as_default', [ $this, 'form_mark_as_default' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_delete', [ $this, 'form_delete' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_bulk_delete', [ $this, 'form_bulk_delete' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_get_terms_by_keyword', [ $this, 'get_terms_by_keyword' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_update_translation', [ $this, 'update_translation' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_form_delete_translation', [ $this, 'delete_translation' ] );

		add_action( 'wp_ajax_rtcl_fb_admin_get_all_migration_data', [ $this, 'get_all_migration_data' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_update_migration_data', [ $this, 'update_migration_data' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_cf_fields', [ $this, 'cf_fields' ] );

		add_action( 'wp_ajax_rtcl_fb_admin_get_options', [ $this, 'get_fb_options' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_update_options', [ $this, 'update_fb_options' ] );


		add_action( 'wp_ajax_rtcl_fb_admin_export_forms', [ $this, 'export_forms' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_import_forms', [ $this, 'import_forms' ] );

		add_action( 'wp_ajax_rtcl_fb_admin_fetch_ai_keyword', [ $this, 'fetch_ai_keyword' ] );
		add_action( 'wp_ajax_rtcl_fb_admin_fetch_ai_form_fields', [ $this, 'fetch_ai_form_fields' ] );
	}

	public function import_forms() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ), 422 );
		}

		$file = !empty( $_FILES['file'] ) ? $_FILES['file'] : '';

		if ( empty( $file ) ) {
			wp_send_json_error( esc_html__( "No file found to import", "classified-listing" ), 424 );
		}

		try {
			$level = error_reporting( 0 );
			/* phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents */
			$content = file_get_contents( $file['tmp_name'] );
			error_reporting( $level );
			if ( false === $content ) {
				$error = error_get_last();
				throw new RuntimeException( $error['message'] );
			}

			$forms = json_decode( $content, true );

			$insertedForms = [];
			if ( empty( $forms ) || !is_array( $forms ) ) {
				wp_send_json_error( 'No data found to import form', 424 );

				return;
			}


			foreach ( $forms as $formItem ) {
				$title = !empty( $formItem['title'] ) ? sanitize_text_field( $formItem['title'] ) : __( 'Imported Form', 'classified-listing' );
				$formData = [
					'title'         => $title,
					'slug'          => FBHelper::getUniqueSlug( $title ),
					'status'        => !empty( $formItem['status'] ) && in_array( $formItem['status'], [
						'publish',
						'draft'
					] ) ? $formItem['status'] : 'publish',
					'default'       => 0,
					'settings'      => !empty( $formItem['settings'] ) ? $formItem['settings'] : null,
					'fields'        => !empty( $formItem['fields'] ) ? $formItem['fields'] : null,
					'sections'      => !empty( $formItem['sections'] ) ? $formItem['sections'] : [],
					'single_layout' => !empty( $formItem['single_layout'] ) ? $formItem['single_layout'] : null,
					'translations'  => !empty( $formItem['translations'] ) ? $formItem['translations'] : null,
					'created_by'    => !empty( $formItem['created_by'] ) ? absint( $formItem['created_by'] ) : get_current_user_id(),
				];

				if ( empty( $formData['fields'] ) || empty( $formData['sections'] ) ) {
					throw new Exception( __( 'You have a faulty JSON file, please export the classified listing directory forms again.', 'classified-listing' ) );
				}

				$form = Form::query()->insert( $formData );
				if ( $form ) {
					$insertedForms[] = [
						'title' => $formData['title'],
						'id'    => $form->id
					];
					do_action( 'rtcl/fb/form_imported', $form->id );
				} else {
					throw new Exception( __( 'Error while importing form', 'classified-listing' ) );
				}
			}

			wp_send_json_success( [
				'message'        => __( 'You form has been successfully imported.', 'classified-listing' ),
				'inserted_forms' => $insertedForms,
			] );

		} catch ( Exception $exception ) {
			wp_send_json_error( $exception->getMessage(), 424 );
		}

	}


	public function export_forms() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ), 422 );
		}

		$formIds = is_array( $_REQUEST['formIds'] ) && !empty( $_REQUEST['formIds'] ) ? array_filter( array_map( 'absint', $_REQUEST['formIds'] ) ) : [];

		if ( empty( $formIds ) ) {
			wp_send_json_error( esc_html__( "Empty from ids", "classified-listing" ), 424 );
		}

		try {
			$results = Form::query()->whereIn( 'id', $formIds )->get();

			if ( empty( $results ) ) {
				wp_send_json_error( esc_html__( "No from found to export", "classified-listing" ), 424 );
			}

			$jsonForms = [];
			foreach ( $results as $form ) {
				$jsonForms[] = $form->toArray();
			}

			if ( count( $jsonForms ) > 1 ) {
				$fileName = 'rtcl-export-forms-' . count( $jsonForms ) . '-' . gmdate( 'd-m-Y' ) . '.json';
			} else {
				$fileName = 'rtcl-export-form-' . $jsonForms[0]['id'] . '-' . gmdate( 'd-m-Y' ) . '.json';
			}

			header( 'Content-disposition: attachment; filename=' . $fileName );

			header( 'Content-type: application/json' );

			echo wp_json_encode( array_values( $jsonForms ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $forms is escaped before being passed in.

			die();
		} catch ( Exception $exception ) {
			wp_send_json( [
				'message' => $exception->getMessage()
			], 424 );
		}
	}

	/**
	 * @return void
	 */
	public function get_terms_by_keyword(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$taxonomy = !empty( $_POST['taxonomy'] ) && in_array( $_POST['taxonomy'], [
			'category',
			'location'
		] ) ? $_POST['taxonomy'] : 'category';
		$keyword = !empty( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
		$term_id = !empty( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$terms = [];
		if ( $term_id ) {
			$term = get_term_by( 'id', $term_id, $taxonomy === 'location' ? rtcl()->location : rtcl()->category );
			if ( $term || !is_wp_error( $term ) ) {
				$_term = [
					'label' => $term->name,
					'value' => $term->term_id
				];
				if ( rtcl()->category === $term->taxonomy ) {
					$image_id = get_term_meta( $term->term_id, '_rtcl_image', true );
					if ( $image_id ) {
						$image_attributes = wp_get_attachment_image_src( (int)$image_id, 'medium' );
						if ( !empty( $image_attributes[0] ) ) {
							$_term['img_url'] = $image_attributes[0];
						}
					}
					$icon_id = get_term_meta( $term->term_id, '_rtcl_icon', true );
					if ( $icon_id ) {
						$_term['icon'] = $icon_id;
					}
				}
				$terms[] = $_term;
			}
		} else {
			$number = isset( $_POST['number'] ) ? absint( $_POST['number'] ) : 0;
			$parentId = isset( $_POST['parentId'] ) ? ( $_POST['parentId'] == 0 ? 0 : absint( $_POST['parentId'] ) ) : '';
			$excludeIds = !empty( $_POST['excludeIds'] ) && is_array( $_POST['excludeIds'] ) ? array_map( 'absint', $_POST['excludeIds'] ) : [];
			$includeIds = !empty( $_POST['includeIds'] ) && is_array( $_POST['includeIds'] ) ? array_map( 'absint', $_POST['includeIds'] ) : [];
			if ( $parentId === '' && $keyword ) {
				if ( !empty( $excludeIds ) ) {
					$excludeIds = Functions::get_all_term_descendants( $excludeIds, $taxonomy === 'location' ? rtcl()->location : rtcl()->category );
				}
				if ( !empty( $includeIds ) ) {
					$includeIds = Functions::get_all_term_descendants( $includeIds, $taxonomy === 'location' ? rtcl()->location : rtcl()->category );
				}
			}

			$args = [
				'hide_empty' => 0,
				'search'     => $keyword,
				'parent'     => $parentId,
				'include'    => $includeIds,
				'exclude'    => $excludeIds,
				'number'     => $number,
				'taxonomy'   => $taxonomy === 'location' ? rtcl()->location : rtcl()->category,
			];
			$termsData = get_terms( $args );
			if ( is_wp_error( $termsData ) ) {
				wp_send_json_error( $termsData->get_error_message() );

				return;
			}
			if ( !empty( $termsData ) ) {
				foreach ( $termsData as $term ) {
					$_term = [
						'slug'  => $term->slug,
						'label' => $term->name,
						'value' => $term->term_id
					];

					if ( rtcl()->category === $term->taxonomy ) {
						$image_id = get_term_meta( $term->term_id, '_rtcl_image', true );
						if ( $image_id ) {
							$image_attributes = wp_get_attachment_image_src( (int)$image_id, 'medium' );
							if ( !empty( $image_attributes[0] ) ) {
								$_term['img_url'] = $image_attributes[0];
							}
						}
						$icon_id = get_term_meta( $term->term_id, '_rtcl_icon', true );
						if ( $icon_id ) {
							$_term['icon'] = $icon_id;
						}
					}

					$terms[] = $_term;
				}
			}
		}

		wp_send_json_success( $terms );
	}

	/**
	 * @return void
	 */
	public function form_list(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$forms = Form::query();
		$q = !empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		if ( $q ) {
			$forms = $forms->where( 'title', 'like', '%' . $q . '%' );
		}
		$perPage = !empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : null;
		$perPage = !empty( $perPage ) ? $perPage : null;
		$forms = $forms->order_by( 'created_at', 'DESC' )->paginate( $perPage );
		wp_send_json_success( $forms->toArray() );
	}

	/**
	 * @return void
	 */
	public function update_migration_data(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$rawMigration = !empty( $_POST['migration'] ) ? $_POST['migration'] : '';
		$rawMigration = is_array( $rawMigration ) ? $rawMigration : [];
		$migration = [];
		if ( !empty( $rawMigration['formId'] ) ) {
			$migration['formId'] = absint( $rawMigration['formId'] );
		}
		if ( !empty( $rawMigration['active'] ) ) {
			$migration['active'] = 1;
		}
		if ( !empty( $rawMigration['fields'] ) ) {
			$fields = [];
			foreach ( $rawMigration['fields'] as $uuid => $field ) {
				$parentId = !empty( $field['parentId'] ) ? absint( $field['parentId'] ) : 0;
				$id = !empty( $field['id'] ) ? absint( $field['id'] ) : 0;
				if ( $parentId && $id ) {
					$fields[$uuid] = [ 'parentId' => $parentId, 'id' => $id ];
				}
			}
			if ( !empty( $fields ) ) {
				$migration['fields'] = $fields;
			}
		}
		update_option( 'rtcl_fb_migration_data', $migration );

		if ( !empty( $migration['active'] ) ) {
			wp_clear_scheduled_hook( 'rtcl_form_cf_data_migration' );
			wp_schedule_single_event( time(), 'rtcl_form_cf_data_migration' );
		}
		wp_send_json_success( (object)$migration );
	}

	/**
	 * @return void
	 */
	public function get_all_migration_data(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$args = [
			'post_type'        => rtcl()->post_type_cfg,
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false
		];

		$groups = get_posts( $args );
		$migration = get_option( 'rtcl_fb_migration_data', [] );
		$migration = is_array( $migration ) ? $migration : [];
		wp_send_json_success( [
			'migration' => (object)$migration,
			'forms'     => Form::query()->order_by( 'created_at', 'DESC' )->get()->toArray(),
			'cf_groups' => array_map( function ( $group ) {
				return [
					'label'  => $group->post_title,
					'value'  => $group->ID,
					'parent' => $group->post_parent
				];
			}, $groups )
		] );
	}

	/**
	 * @return void
	 */
	public function get_fb_options(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}
		$options = get_option( 'rtcl_fb_options', [] );
		$options = is_array( $options ) ? $options : [];
		wp_send_json_success( $options );
	}

	/**
	 * @return void
	 */
	public function update_fb_options(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$rawOptions = !empty( $_POST['options'] ) ? $_POST['options'] : '';
		$rawOptions = is_array( $rawOptions ) ? $rawOptions : [];
		$options = [];
		if ( !empty( $rawOptions['active'] ) ) {
			$options['active'] = 1;
		}
		update_option( 'rtcl_fb_options', $options );

		wp_send_json_success( (object)$options );
	}

	/**
	 * @return void
	 */
	public function cf_fields(): void {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$parent_id = !empty( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0;
		$args = [
			'post_type'        => rtcl()->post_type_cf,
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'post_parent'      => $parent_id,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false
		];
		$fields = [];
		$posts = get_posts( $args );
		if ( !empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$fields[] = [
					'label'  => get_post_meta( $post->ID, '_label', true ),
					'value'  => $post->ID,
					'parent' => $post->post_parent
				];
			}
		}

		wp_send_json_success( $fields );
	}


	public function form_get() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}

		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$form = Form::query()->find( $formId );

		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( "No form found to edit", "classified-listing" ) );
		}
		wp_send_json_success( [
			'message' => __( 'Form data', 'classified-listing' ),
			'data'    => $form->toArray()
		] );
	}

	public function form_create() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );

			return;
		}
//		$formTitle = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
//		if ( !$formTitle ) {
//			wp_send_json_error( esc_html__( "Title field is empty", "classified-listing" ) );
//
//			return;
//		}

//		if ( !FBHelper::isUniqueTitle( $formTitle ) ) {
//			wp_send_json_error( esc_html__( "Title is already exist", "classified-listing" ) );
//
//			return;
//		}

		$type = isset( $_POST['id'] ) ? Functions::clean( $_POST['id'] ) : 'sample';

		if ( 'sample' === $type ) {
			$formData = FormPreDefined::sample();
		} elseif ( 'ai' === $type ) {
			$formData = !empty( $_POST['ai_form_data'] ) ? Functions::clean( $_POST['ai_form_data'] ) : [];
		} else {
			$formData = FormPreDefined::blank();
		};
		$title = sanitize_text_field( !empty( $formData['title'] ) ? $formData['title'] : 'Empty Directory' );
		$formData['slug'] = FBHelper::getUniqueSlug( sanitize_title( !empty($formData['slug']) ? $formData['slug'] : $title ) );
		$form = Form::query()->insert( $formData );
		if ( !$form ) {
			wp_send_json_error( esc_html__( 'Error while creating new form!', 'classified-listing' ) );
		}
		$title = sprintf( '%s (#%s)', $form->title, $form->id );
		$form->title = $title;
		$form->slug = FBHelper::getUniqueSlug( sanitize_title( $title ) );
		$form->update();
		wp_send_json_success( [
			'message' => __( 'Form has been created successfully', 'classified-listing' ),
			'data'    => $form->toArray()
		] );
	}

	public function form_delete() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}

		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$form = Form::query()->find( $formId );

		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( 'No form found to edit', 'classified-listing' ) );
		}

		if ( $form->default == 1 ) {
			wp_send_json_error( esc_html__( 'Default form can\'t be deleted.', 'classified-listing' ) );
		}

		if ( !$form->delete() ) {
			wp_send_json_error( esc_html__( 'Error while deleting form', 'classified-listing' ) );
		}
		wp_send_json_success( [
			'message' => __( 'Deleted successfully', 'classified-listing' ),
			'id'      => $formId
		] );
	}

	public function form_bulk_delete() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}

		$formId = isset( $_POST['ids'] ) ? array_map( 'absint', (array)$_POST['ids'] ) : [];

		if ( empty( $formId ) ) {
			wp_send_json_error( esc_html__( 'No form found to Delete', 'classified-listing' ) );
		}
		foreach ( $formId as $id ) {
			$form = Form::query()->find( $id );
			if ( empty( $form ) ) {
				wp_send_json_error( esc_html__( 'No form found to edit', 'classified-listing' ) );
			}
			if ( $form->default == 1 ) {
				wp_send_json_error( esc_html__( 'Default form can\'t be deleted.', 'classified-listing' ) );
			}
			if ( !$form->delete() ) {
				wp_send_json_error( esc_html__( 'Error while deleting form', 'classified-listing' ) );
			}
		}
		wp_send_json_success( [
			'message' => __( 'Deleted successfully', 'classified-listing' ),
			'id'      => $formId
		] );
	}

	public function form_update() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$form = Form::query()->find( $formId );

		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( 'No form found to update.', 'classified-listing' ) );

			return;
		}

		$sections = !empty( $_POST['sections'] ) ? json_decode( wp_unslash( $_POST['sections'] ), true ) : [];
		$fields = !empty( $_POST['fields'] ) ? json_decode( wp_unslash( $_POST['fields'] ), true ) : (object)[];
		$settings = empty( $_POST['settings'] ) ? (object)[] : json_decode( wp_unslash( $_POST['settings'] ), true );
		$raw_single_layout = empty( $_POST['single_layout'] ) ? [] : json_decode( wp_unslash( $_POST['single_layout'] ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( sprintf( '%s %s', __( 'Invalid JSON data: ', 'classified-listing' ), json_last_error_msg() ) );

			return;
		}

		$fields = ( new FieldSanitization( $fields ) )->get();
		$sections = ( new SectionSanitization( $sections, $fields ) )->get();
		$settings = ( new SettingsFieldSanitization( $settings ) )->get();
		$single_layout = [];
		if ( !empty( $raw_single_layout ) ) {
			$slSettingsFields = AvailableFields::singleLayoutSettingsFields();
			$slSettingsFields = !empty( $slSettingsFields ) ? array_column( $slSettingsFields, null, 'name' ) : [];
			foreach ( $raw_single_layout as $slKey => $_slValue ) {

				if ( $slKey === 'containers' ) {
					if ( is_array( $_slValue ) ) {
						$containers = [];
						foreach ( $_slValue as $containerIndex => $_container ) {
							if ( is_array( $_container ) ) {
								$container = [];
								foreach ( $_container as $_containerKey => $_containerValue ) {
									if ( in_array( $_containerKey, [ 'title', 'uuid', 'id', 'container_class' ] ) ) {
										$container[$_containerKey] = sanitize_text_field( wp_unslash( $_containerValue ) );
									} else if ( $_containerKey === 'columns' && is_array( $_containerValue ) && !empty( $_containerValue ) ) {
										$columns = [];
										foreach ( $_containerValue as $_columnIndex => $_columnData ) {
											if ( !empty( $_columnData ) && is_array( $_columnData ) ) {
												$column = [];
												foreach ( $_columnData as $_columnKey => $_columnValue ) {
													if ( $_columnKey === 'width' ) {
														$column['width'] = absint( $_columnValue );
													} elseif ( $_columnKey === 'sections' && is_array( $_columnValue ) ) {
														$slSections = ( new SectionSanitization( $_columnValue, $fields ) )->get();
														if ( !empty( $slSections ) ) {
															$column['sections'] = array_map( function ( $section ) {
																if ( isset( $section['logics'] ) ) {
																	unset( $section['logics'] );
																}
																if ( isset( $section['column'] ) ) {
																	unset( $section['column'] );
																}
																return $section;
															}, $slSections );
														}
													}
												}
												if ( !empty( $column ) ) {
													$columns[] = $column;
												}
											}
										}
										if ( !empty( $columns ) ) {
											$container['columns'] = $columns;
										}
									}
								}
								if ( !empty( $container ) ) {
									if ( !isset( $container['columns'] ) ) {
										$container['columns'] = [ 'width' => 100, 'sections' => [] ];
									}
									$containers[] = $container;
								}
							}
						}
						if ( !empty( $containers ) ) {
							$single_layout['containers'] = $containers;
						}
					}
				} else if ( $slKey === 'fields' ) {
					if ( is_array( $_slValue ) ) {
						$allSettingsFields = ElementCustomization::settingsFields();
						$labelPlacementOptions = array_filter( array_column( $allSettingsFields['label_placement']['options'], 'value' ) );
						$singleFieldElements = array_keys( AvailableFields::singleLayoutFields() );

						$slFields = [];
						foreach ( $_slValue as $fieldUuid => $fieldData ) {
							if ( is_array( $fieldData ) ) {
								$slField = [];
								if ( !empty( $fieldData['element'] ) && in_array( $fieldData['element'], $singleFieldElements ) ) {
									foreach ( $fieldData as $_optionKey => $_optionValue ) {
										if ( $_optionKey === 'value' && $fieldData['element'] === 'space' ) {
											$slField[$_optionKey] = absint( $_optionValue );
										} else if ( $_optionKey === 'value' && $fieldData['element'] === 'html' ) {
											$slField[$_optionKey] = wp_kses_post( $_optionValue );
										} else if ( $_optionKey === 'icon' ) {
											if ( !empty( $_optionValue['type'] ) && !empty( $_optionValue['class'] ) ) {
												$slField[$_optionKey] = [
													'type'  => 'class',
													'class' => sanitize_text_field( wp_unslash( $_optionValue['class'] ) ),
												];
											}
										} else if ( $_optionKey === 'items' ) {
											if ( is_array( $_optionValue ) ) {
												$slField[$_optionKey] = map_deep( $_optionValue, function ( $value ) {
													return sanitize_text_field( wp_unslash( $value ) );
												} );
											}
										} else {
											$slField[$_optionKey] = sanitize_text_field( wp_unslash( $_optionValue ) );
										}
									}
								} else {
									foreach ( $fieldData as $_optionKey => $_optionValue ) {
										if ( $_optionKey === 'label_placement' && !empty( $allSettingsFields['label_placement'] ) ) {
											if ( in_array( $_optionValue, $labelPlacementOptions ) ) {
												$slField[$_optionKey] = $_optionValue;
											}
										} else if ( $_optionKey === 'hide_video' ) {
											$slField[$_optionKey] = (bool)$_optionValue;
										}
									}
								}

								if ( !empty( $slField ) ) {
									$slFields[$fieldUuid] = $slField;
								}
							}
						}
						if ( !empty( $slFields ) ) {
							$single_layout['fields'] = $slFields;
						} else {
							$single_layout['fields'] = null;
						}
					}
				} else if ( $slKey === 'settings' ) {
					if ( is_array( $_slValue ) ) {
						$slSettings = [];
						foreach ( $_slValue as $fieldUuid => $fieldData ) {
							if ( !empty( $slSettingsFields[$fieldUuid] ) ) {
								$slsField = $slSettingsFields[$fieldUuid];
								if ( !empty( $slsField['type'] ) && $slsField['type'] === 'switch' ) {
									$slSettings[$fieldUuid] = !empty( $fieldData ) ? 1 : 0;
								}
							}
						}
						if ( !empty( $slSettings ) ) {
							$single_layout[$slKey] = $slSettings;
						} else {
							$single_layout[$slKey] = null;
						}
					}
				}
			}
		}

		$form->single_layout = !empty( $single_layout ) ? $single_layout : null;
		$form->sections = $sections;
		$form->fields = $fields;
		$form->settings = $settings;
		$form->update();
		wp_send_json_success( [
			'message' => __( 'The form is successfully updated.', 'classified-listing' ),
			'data'    => $form->toArray()
		] );
	}

	public function update_translation() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		$formId = isset( $_POST['formId'] ) ? absint( $_POST['formId'] ) : 0;

		$form = Form::query()->find( $formId );

		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( 'No form found to update.', 'classified-listing' ) );

			return;
		}

		$rawTranslations = $_POST['translations'] ?? [];
		$translations = ( new TranslationSanitization( $form, $rawTranslations ) )->get();
		$form->translations = $translations;
		$form->update();
		wp_send_json_success( [
			'message' => __( 'Translations are successfully updated.', 'classified-listing' ),
			'data'    => $translations
		] );
	}

	public function delete_translation() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( 'Session error !!', 'classified-listing' ) );
		}
		$formId = isset( $_POST['formId'] ) ? absint( $_POST['formId'] ) : 0;

		$form = Form::query()->find( $formId );

		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( 'No form found to remove translation.', 'classified-listing' ) );

			return;
		}

		$fieldUid = Functions::request( 'fieldUuid', '' );

		if ( empty( $fieldUid ) ) {
			wp_send_json_error( esc_html__( 'Field id is missing to remove translations.', 'classified-listing' ) );

			return;
		}

		$translations = $form->translations;

		if ( !empty( $translations ) && is_array( $translations ) ) {
			foreach ( $translations as $translationCode => $translation ) {
				if ( isset( $translation[$fieldUid] ) ) {
					unset( $translations[$translationCode][$fieldUid] );
				}
			}
			$form->translations = empty( $translations ) ? null : $translations;
			$form->update();
		}
		wp_send_json_success( [
			'message' => __( 'Translations are successfully deleted.', 'classified-listing' ),
			'data'    => $translations
		] );
	}

	public function form_update_slug() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}
		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$formSlug = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : '';
		$form = Form::query()->find( $formId );
		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( "No form found.", "classified-listing" ) );

			return;
		}

		if ( empty( $formSlug ) ) {
			wp_send_json_error( esc_html__( "Empty slug is not allowed.", "classified-listing" ) );

			return;
		}

		if ( !FBHelper::isUniqueSlug( $formSlug, $formId ) ) {
			wp_send_json_error( esc_html__( "Slug is already exist.", "classified-listing" ) );

			return;
		}

		$form->slug = $formSlug;
		$form->update();
		wp_send_json_success( [
			'message' => esc_html__( 'Slug is successfully updated.', 'classified-listing' ),
			'slug'    => $form->slug
		] );
	}

	public function form_update_partial() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}
		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$form = Form::query()->find( $formId );
		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( "No form found.", "classified-listing" ) );

			return;
		}
		$formTitle = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$formStatus = isset( $_POST['status'] ) && in_array( $_POST['status'], [
			'publish',
			'draft'
		], true ) ? $_POST['status'] : '';


		if ( $formTitle ) {
			$form->title = $formTitle;
		}
		if ( $formStatus ) {
			$form->status = $formStatus;
		}
		$form->update();
		wp_send_json_success( [
			'message' => esc_html__( 'Form is successfully updated.', 'classified-listing' ),
			'form'    => $form->toArray()
		] );
	}

	public function form_mark_as_default() {
		if ( !wp_verify_nonce( isset( $_REQUEST[rtcl()->nonceId] ) ? $_REQUEST[rtcl()->nonceId] : null, rtcl()->nonceText ) || !current_user_can( 'manage_rtcl_options' ) ) {
			wp_send_json_error( esc_html__( "Session error !!", "classified-listing" ) );
		}
		$formId = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$form = Form::query()->find( $formId );
		if ( empty( $form ) ) {
			wp_send_json_error( esc_html__( "No form found.", "classified-listing" ) );

			return;
		}

		if ( $form->default == 1 ) {
			wp_send_json_error( esc_html__( "This form is already mark as default.", "classified-listing" ) );

			return;
		}

		Form::query()
			->where( 'default', '=', 1 )
			->where( 'id', '!=', $form->id )
			->update( [ 'default' => 0 ] );


		$form->default = 1;
		$form->update();

		wp_send_json_success( [
			'message' => esc_html__( 'Mars as default successfully.', 'classified-listing' )
		] );
	}

	/**
	 * Fetch AI keyword
	 *
	 * @return void
	 */
	public function fetch_ai_keyword() {
		$this->get_ai_service( 'keyword' );
	}

	/**
	 * Fetch AI form fields
	 *
	 * @return void
	 */
	public function fetch_ai_form_fields() {
		$this->get_ai_service( 'fields' );
	}

	private function get_ai_service( string $type ) {
		$aiController = new AIController();
		switch ( $type ) {
			case 'keyword':
				$aiController->fetch_ai_keyword();
				break;
			case 'fields':
				$aiController->fetch_ai_form_fields();
				break;
			default:
				wp_send_json_error( esc_html__( 'Invalid AI service type.', 'classified-listing' ) );
		}
	}


}