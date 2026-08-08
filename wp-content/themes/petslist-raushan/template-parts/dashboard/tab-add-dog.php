<?php
/**
 * Dashboard Tab: Add / Edit Dog
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$edit_id  = absint( $_GET['edit'] ?? 0 );
$is_edit  = $edit_id > 0;
$dog_meta = [];
$dog_health = [];
$dog_title = '';

if ( $is_edit ) {
    $post = get_post( $edit_id );
    if ( $post && $post->post_type === 'dd_dog' && ( (int)$post->post_author === get_current_user_id() || current_user_can('manage_options') ) ) {
        $dog_title   = $post->post_title;
        $dog_meta    = get_post_meta( $edit_id, '_dd_dog_meta', true ) ?: [];
        $dog_health  = get_post_meta( $edit_id, '_dd_dog_health', true ) ?: [];
        $front_photo = get_post_meta( $edit_id, '_dd_front_photo', true );
        $side_photo  = get_post_meta( $edit_id, '_dd_side_photo', true );
        $thumb_id    = get_post_thumbnail_id( $edit_id ) ?: $front_photo;
    }
}

$breeds = dd_get_breeds(100);

function dd_field( $meta, $key, $fallback = '' ) {
    return esc_attr( $meta[$key] ?? $fallback );
}
?>

<div class="dd-tab-add-dog">

    <div class="dd-tab-add-dog__header">
        <h2><?php echo $is_edit ? __( 'Edit Stud Profile', 'petslist' ) : __( 'Add New Stud', 'petslist' ); ?></h2>
        <?php if ( $is_edit ) : ?>
        <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" class="dd-btn dd-btn--ghost">
            <i class="fa-solid fa-arrow-left"></i> <?php _e( 'Back to My Studs', 'petslist' ); ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Steps Progress Bar -->
    <div class="dd-wizard-steps">
        <div class="dd-wizard-step dd-wizard-step--active" data-step="1">
            <div class="dd-wizard-step__number">1</div>
            <div class="dd-wizard-step__label"><?php _e( 'Basic Info', 'petslist' ); ?></div>
        </div>
        <div class="dd-wizard-step__line"></div>
        <div class="dd-wizard-step" data-step="2">
            <div class="dd-wizard-step__number">2</div>
            <div class="dd-wizard-step__label"><?php _e( 'Location', 'petslist' ); ?></div>
        </div>
        <div class="dd-wizard-step__line"></div>
        <div class="dd-wizard-step" data-step="3">
            <div class="dd-wizard-step__number">3</div>
            <div class="dd-wizard-step__label"><?php _e( 'Photos', 'petslist' ); ?></div>
        </div>
    </div>

    <div id="dd-dog-form-message" class="dd-auth-message" style="display:none"></div>

    <form id="dd-dog-form" class="dd-dog-form" novalidate>
        <input type="hidden" name="post_id" value="<?php echo $is_edit ? $edit_id : 0; ?>">

        <!-- Section 1: Basic Info / Dog Profile -->
        <div class="dd-dog-form__section" data-step="1">
            <h3 class="dd-dog-form__section-title"><span>1</span> <?php _e( 'Stud Profile Details', 'petslist' ); ?></h3>
            <div class="dd-dog-form__grid">

                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-dog-name"><?php _e( 'Stud Name', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-dog-name" name="dog_data[dog_name]" value="<?php echo esc_attr($dog_title); ?>" placeholder="<?php esc_attr_e( 'Enter stud\'s registered name', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-breed"><?php _e( 'Breed', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <select id="dd-breed" name="dog_data[breed]" required>
                        <?php
                        $current_breed = dd_match_breed_name( $dog_meta['breed'] ?? '' );
                        dd_render_breed_options( $current_breed );
                        ?>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label for="dd-gender"><?php _e( 'Gender', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <select id="dd-gender" name="dog_data[gender]" required>
                        <option value="Male" <?php selected( $dog_meta['gender'] ?? 'Male', 'Male' ); ?>><?php _e( 'Male (Stud)', 'petslist' ); ?></option>
                        <option value="Female" <?php selected( $dog_meta['gender'] ?? '', 'Female' ); ?>><?php _e( 'Female', 'petslist' ); ?></option>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label for="dd-age"><?php _e( 'Age', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-age" name="dog_data[age]" value="<?php echo dd_field($dog_meta,'age'); ?>" placeholder="<?php esc_attr_e( 'e.g. 2 Years 4 Months', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-color"><?php _e( 'Color', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-color" name="dog_data[color]" value="<?php echo dd_field($dog_meta,'color'); ?>" placeholder="<?php esc_attr_e( 'e.g. Black & Tan, Fawn', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-height"><?php _e( 'Height', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-height" name="dog_data[height]" value="<?php echo dd_field($dog_meta,'height'); ?>" placeholder="<?php esc_attr_e( 'e.g. 26 inches', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-weight"><?php _e( 'Weight (Optional)', 'petslist' ); ?></label>
                    <input type="text" id="dd-weight" name="dog_data[weight]" value="<?php echo dd_field($dog_meta,'weight'); ?>" placeholder="<?php esc_attr_e( 'e.g. 75 lbs / 34 kg', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-stud-fee"><?php _e( 'Stud Fee', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-stud-fee" name="dog_data[stud_fee]" value="<?php echo dd_field($dog_meta,'stud_fee'); ?>" placeholder="<?php esc_attr_e( 'e.g. $1,500 or Contact for Fee', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-semen-type"><?php _e( 'Fresh or Frozen Semen', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <select id="dd-semen-type" name="dog_data[semen_type]" required>
                        <option value="Fresh" <?php selected( $dog_meta['semen_type'] ?? '', 'Fresh' ); ?>><?php _e( 'Fresh', 'petslist' ); ?></option>
                        <option value="Frozen" <?php selected( $dog_meta['semen_type'] ?? '', 'Frozen' ); ?>><?php _e( 'Frozen', 'petslist' ); ?></option>
                        <option value="Both (Fresh & Frozen)" <?php selected( $dog_meta['semen_type'] ?? '', 'Both (Fresh & Frozen)' ); ?>><?php _e( 'Both (Fresh & Frozen)', 'petslist' ); ?></option>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label for="dd-titles"><?php _e( 'Titles (Optional)', 'petslist' ); ?></label>
                    <input type="text" id="dd-titles" name="dog_data[titles]" value="<?php echo dd_field($dog_meta,'titles'); ?>" placeholder="<?php esc_attr_e( 'e.g. CH, GCH, BIS', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-registration"><?php _e( 'Registration Number', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-registration" name="dog_data[registration_no]" value="<?php echo dd_field($dog_meta,'registration_no'); ?>" placeholder="<?php esc_attr_e( 'AKC-123456', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-pedigree"><?php _e( 'Pedigree', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <textarea id="dd-pedigree" name="dog_data[pedigree]" rows="3" placeholder="<?php esc_attr_e( 'Sire, Dam, and lineage information...', 'petslist' ); ?>" required><?php echo esc_textarea($dog_meta['pedigree'] ?? ($dog_health['pedigree'] ?? '')); ?></textarea>
                </div>

                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-health-testing"><?php _e( 'Health Testing', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <textarea id="dd-health-testing" name="dog_data[health_testing]" rows="3" placeholder="<?php esc_attr_e( 'OFA Hips, Elbows, Eyes, Genetic clearances...', 'petslist' ); ?>" required><?php echo esc_textarea($dog_meta['health_testing'] ?? ($dog_health['health_clearances'] ?? '')); ?></textarea>
                </div>

            </div>
        </div>

        <!-- Section 2: Location & Contact -->
        <div class="dd-dog-form__section" data-step="2" style="display:none;">
            <h3 class="dd-dog-form__section-title"><span>2</span> <?php _e( 'Location & Contact', 'petslist' ); ?></h3>
            <div class="dd-dog-form__grid">

                <div class="dd-form-group">
                    <label for="dd-country"><?php _e( 'Country', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-country" name="dog_data[country]" value="<?php echo dd_field($dog_meta,'country'); ?>" placeholder="<?php esc_attr_e( 'e.g. United States', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-city"><?php _e( 'City / State', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-city" name="dog_data[city]" value="<?php echo dd_field($dog_meta,'city'); ?>" placeholder="<?php esc_attr_e( 'e.g. New York, NY', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-phone"><?php _e( 'Contact Phone', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="tel" id="dd-phone" name="dog_data[contact_phone]" value="<?php echo dd_field($dog_meta,'contact_phone'); ?>" placeholder="+1 555 000 0000" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-email"><?php _e( 'Contact Email', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="email" id="dd-email" name="dog_data[contact_email]" value="<?php echo dd_field($dog_meta,'contact_email'); ?>" placeholder="breeder@example.com" required>
                </div>

            </div>
        </div>

        <!-- Section 3: Photos (2 Photos Required: Front & Side) -->
        <div class="dd-dog-form__section" data-step="3" style="display:none;">
            <h3 class="dd-dog-form__section-title"><span>3</span> <?php _e( 'Photos (2 Photos Required)', 'petslist' ); ?></h3>
            <div class="dd-dog-form__photos-grid">

                <!-- Hidden Thumbnail ID (synced with Front Photo) -->
                <input type="hidden" id="dd-thumb-id" name="dog_data[thumbnail_id]" value="<?php echo esc_attr($thumb_id ?: ($front_photo ?? '')); ?>">

                <!-- Front Photo (Used for Profile Photo) -->
                <div class="dd-photo-upload-box">
                    <div class="dd-photo-upload-box__label"><?php _e( 'Front View Photo (Profile Photo)', 'petslist' ); ?> <span class="dd-required">*</span></div>
                    <div class="dd-photo-upload-area" id="dd-front-preview">
                        <?php if ( ! empty($front_photo) ) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($front_photo, 'medium')); ?>" alt="">
                        <?php else : ?>
                        <div class="dd-photo-upload-placeholder">
                            <i class="icon-pl-img"></i>
                            <span><?php _e( 'Front view photo (Used for profile)', 'petslist' ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="dd-front-id" name="dog_data[front_photo]" value="<?php echo esc_attr($front_photo ?? ''); ?>">
                    <button type="button" class="dd-btn dd-btn--ghost dd-btn--sm dd-upload-photo" data-target="dd-front-id" data-preview="dd-front-preview">
                        <i class="icon-pl-img"></i> <?php _e( 'Choose Front Photo', 'petslist' ); ?>
                    </button>
                </div>

                <!-- Side Photo -->
                <div class="dd-photo-upload-box">
                    <div class="dd-photo-upload-box__label"><?php _e( 'Side View Photo', 'petslist' ); ?> <span class="dd-required">*</span></div>
                    <div class="dd-photo-upload-area" id="dd-side-preview">
                        <?php if ( ! empty($side_photo) ) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($side_photo, 'medium')); ?>" alt="">
                        <?php else : ?>
                        <div class="dd-photo-upload-placeholder">
                            <i class="icon-pl-img"></i>
                            <span><?php _e( 'Side view photo', 'petslist' ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="dd-side-id" name="dog_data[side_photo]" value="<?php echo esc_attr($side_photo ?? ''); ?>">
                    <button type="button" class="dd-btn dd-btn--ghost dd-btn--sm dd-upload-photo" data-target="dd-side-id" data-preview="dd-side-preview">
                        <i class="icon-pl-img"></i> <?php _e( 'Choose Side Photo', 'petslist' ); ?>
                    </button>
                </div>

            </div>
            <p class="dd-form-note"><i class="icon-pl-flash"></i> <?php _e( 'Required: Front view photo (used for profile) and Side view photo.', 'petslist' ); ?></p>
        </div>

        <!-- Wizard Navigation -->
        <div class="dd-wizard-nav">
            <button type="button" class="dd-btn dd-btn--ghost dd-wizard-nav__prev dd-hide" id="dd-wizard-prev">
                <i class="fa-solid fa-arrow-left"></i> <?php _e( 'Previous', 'petslist' ); ?>
            </button>
            <div class="dd-wizard-nav__actions">
                <button type="button" class="dd-btn dd-btn--primary" id="dd-wizard-next">
                    <?php _e( 'Next', 'petslist' ); ?> <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button type="submit" class="dd-btn dd-btn--primary dd-btn--lg dd-hide" id="dd-dog-submit">
                    <span class="dd-btn__text">
                        <?php echo $is_edit ? __( 'Update Stud Profile', 'petslist' ) : __( 'Submit Stud for Review', 'petslist' ); ?>
                    </span>
                    <span class="dd-btn__loader" style="display:none">
                        <i class="fa-solid fa-spinner fa-spin"></i> <?php _e( 'Saving...', 'petslist' ); ?>
                    </span>
                </button>
            </div>
        </div>

    </form>

</div>
