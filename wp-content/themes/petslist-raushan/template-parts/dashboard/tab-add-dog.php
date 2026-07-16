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
$dog_content = '';

if ( $is_edit ) {
    $post = get_post( $edit_id );
    if ( $post && $post->post_type === 'dd_dog' && ( (int)$post->post_author === get_current_user_id() || current_user_can('manage_options') ) ) {
        $dog_title   = $post->post_title;
        $dog_content = $post->post_content;
        $dog_meta    = get_post_meta( $edit_id, '_dd_dog_meta', true ) ?: [];
        $dog_health  = get_post_meta( $edit_id, '_dd_dog_health', true ) ?: [];
        $front_photo = get_post_meta( $edit_id, '_dd_front_photo', true );
        $side_photo  = get_post_meta( $edit_id, '_dd_side_photo', true );
        $thumb_id    = get_post_thumbnail_id( $edit_id );
    }
}

$breeds = dd_get_breeds(100);

function dd_field( $meta, $key, $fallback = '' ) {
    return esc_attr( $meta[$key] ?? $fallback );
}
?>

<div class="dd-tab-add-dog">

    <div class="dd-tab-add-dog__header">
        <h2><?php echo $is_edit ? __( 'Edit Dog Profile', 'petslist' ) : __( 'Add New Dog', 'petslist' ); ?></h2>
        <?php if ( $is_edit ) : ?>
        <a href="<?php echo esc_url(dd_dashboard_url('dogs')); ?>" class="dd-btn dd-btn--ghost">
            <i class="fa-solid fa-arrow-left"></i> <?php _e( 'Back to My Dogs', 'petslist' ); ?>
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
            <div class="dd-wizard-step__label"><?php _e( 'Health', 'petslist' ); ?></div>
        </div>
        <div class="dd-wizard-step__line"></div>
        <div class="dd-wizard-step" data-step="4">
            <div class="dd-wizard-step__number">4</div>
            <div class="dd-wizard-step__label"><?php _e( 'Photos', 'petslist' ); ?></div>
        </div>
    </div>

    <div id="dd-dog-form-message" class="dd-auth-message" style="display:none"></div>

    <form id="dd-dog-form" class="dd-dog-form" novalidate>
        <input type="hidden" name="post_id" value="<?php echo $is_edit ? $edit_id : 0; ?>">

        <!-- Section 1: Basic Info -->
        <div class="dd-dog-form__section" data-step="1">
            <h3 class="dd-dog-form__section-title"><span>1</span> <?php _e( 'Basic Information', 'petslist' ); ?></h3>
            <div class="dd-dog-form__grid">

                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-dog-name"><?php _e( 'Dog Name', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <input type="text" id="dd-dog-name" name="dog_data[dog_name]" value="<?php echo esc_attr($dog_title); ?>" placeholder="<?php esc_attr_e( 'Enter dog\'s registered name', 'petslist' ); ?>" required>
                </div>

                <div class="dd-form-group">
                    <label for="dd-breed"><?php _e( 'Breed', 'petslist' ); ?> <span class="dd-required">*</span></label>
                    <select id="dd-breed" name="dog_data[breed]" required>
                        <?php
                        $current_breed = dd_match_breed_name( $dog_meta['breed'] ?? '' );
                        $breed_options = dd_get_breed_options();
                        foreach ( $breed_options as $val => $label ) :
                            ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_breed, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label for="dd-gender"><?php _e( 'Gender', 'petslist' ); ?></label>
                    <select id="dd-gender" name="dog_data[gender]">
                        <option value=""><?php _e( 'Select Gender', 'petslist' ); ?></option>
                        <option value="Male" <?php selected( $dog_meta['gender'] ?? '', 'Male' ); ?>><?php _e( 'Male', 'petslist' ); ?></option>
                        <option value="Female" <?php selected( $dog_meta['gender'] ?? '', 'Female' ); ?>><?php _e( 'Female', 'petslist' ); ?></option>
                    </select>
                </div>

                <div class="dd-form-group">
                    <label for="dd-dob"><?php _e( 'Date of Birth', 'petslist' ); ?></label>
                    <input type="date" id="dd-dob" name="dog_data[dob]" value="<?php echo dd_field($dog_meta,'dob'); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-color"><?php _e( 'Color / Coat', 'petslist' ); ?></label>
                    <input type="text" id="dd-color" name="dog_data[color]" value="<?php echo dd_field($dog_meta,'color'); ?>" placeholder="<?php esc_attr_e( 'e.g. Golden, Black & Tan', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-weight"><?php _e( 'Weight (kg)', 'petslist' ); ?></label>
                    <input type="text" id="dd-weight" name="dog_data[weight]" value="<?php echo dd_field($dog_meta,'weight'); ?>" placeholder="<?php esc_attr_e( 'e.g. 28.5', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-registration"><?php _e( 'Registration Number', 'petslist' ); ?></label>
                    <input type="text" id="dd-registration" name="dog_data[registration_no]" value="<?php echo dd_field($dog_meta,'registration_no'); ?>" placeholder="<?php esc_attr_e( 'AKC-123456', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-description"><?php _e( 'Description', 'petslist' ); ?></label>
                    <textarea id="dd-description" name="dog_data[description]" rows="4" placeholder="<?php esc_attr_e( 'Describe the dog\'s temperament, history, achievements...', 'petslist' ); ?>"><?php echo esc_textarea($dog_content); ?></textarea>
                </div>

            </div>
        </div>

        <!-- Section 2: Location & Contact -->
        <div class="dd-dog-form__section" data-step="2" style="display:none;">
            <h3 class="dd-dog-form__section-title"><span>2</span> <?php _e( 'Location & Contact', 'petslist' ); ?></h3>
            <div class="dd-dog-form__grid">

                <div class="dd-form-group">
                    <label for="dd-country"><?php _e( 'Country', 'petslist' ); ?></label>
                    <input type="text" id="dd-country" name="dog_data[country]" value="<?php echo dd_field($dog_meta,'country'); ?>" placeholder="<?php esc_attr_e( 'e.g. United States', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-city"><?php _e( 'City / State', 'petslist' ); ?></label>
                    <input type="text" id="dd-city" name="dog_data[city]" value="<?php echo dd_field($dog_meta,'city'); ?>" placeholder="<?php esc_attr_e( 'e.g. New York, NY', 'petslist' ); ?>">
                </div>

                <div class="dd-form-group">
                    <label for="dd-phone"><?php _e( 'Contact Phone', 'petslist' ); ?></label>
                    <input type="tel" id="dd-phone" name="dog_data[contact_phone]" value="<?php echo dd_field($dog_meta,'contact_phone'); ?>" placeholder="+1 555 000 0000">
                </div>

                <div class="dd-form-group">
                    <label for="dd-email"><?php _e( 'Contact Email', 'petslist' ); ?></label>
                    <input type="email" id="dd-email" name="dog_data[contact_email]" value="<?php echo dd_field($dog_meta,'contact_email'); ?>" placeholder="breeder@example.com">
                </div>

                <div class="dd-form-group">
                    <label for="dd-website"><?php _e( 'Website / Kennel URL', 'petslist' ); ?></label>
                    <input type="url" id="dd-website" name="dog_data[contact_website]" value="<?php echo dd_field($dog_meta,'contact_website'); ?>" placeholder="https://my-kennel.com">
                </div>

            </div>
        </div>

        <!-- Section 3: Health & Pedigree -->
        <div class="dd-dog-form__section" data-step="3" style="display:none;">
            <h3 class="dd-dog-form__section-title"><span>3</span> <?php _e( 'Health & Pedigree', 'petslist' ); ?></h3>
            <div class="dd-dog-form__grid">

                <?php
                $health_fields = [
                    'health_clearances' => [ 'label' => __('Health Clearances', 'petslist'), 'placeholder' => __('OFA Hips: Excellent, Eyes: Clear...', 'petslist') ],
                    'vaccinations'      => [ 'label' => __('Vaccinations', 'petslist'), 'placeholder' => __('Rabies, DHPP, Bordetella...', 'petslist') ],
                    'pedigree'          => [ 'label' => __('Pedigree Information', 'petslist'), 'placeholder' => __('Sire, Dam, grandparents...', 'petslist') ],
                    'awards'            => [ 'label' => __('Awards & Titles', 'petslist'), 'placeholder' => __('CH, GCH, Best in Show 2023...', 'petslist') ],
                ];
                foreach ( $health_fields as $hkey => $hfield ) :
                ?>
                <div class="dd-form-group dd-form-group--full">
                    <label for="dd-health-<?php echo $hkey; ?>"><?php echo esc_html($hfield['label']); ?></label>
                    <textarea id="dd-health-<?php echo $hkey; ?>" name="dog_data[health][<?php echo $hkey; ?>]" rows="3" placeholder="<?php echo esc_attr($hfield['placeholder']); ?>"><?php echo esc_textarea($dog_health[$hkey] ?? ''); ?></textarea>
                </div>
                <?php endforeach; ?>

                <div class="dd-form-group">
                    <label for="dd-microchip"><?php _e( 'Microchip Number', 'petslist' ); ?></label>
                    <input type="text" id="dd-microchip" name="dog_data[health][microchip]" value="<?php echo esc_attr($dog_health['microchip'] ?? ''); ?>" placeholder="<?php esc_attr_e( '985112345678901', 'petslist' ); ?>">
                </div>

            </div>
        </div>

        <!-- Section 4: Photos -->
        <div class="dd-dog-form__section" data-step="4" style="display:none;">
            <h3 class="dd-dog-form__section-title"><span>4</span> <?php _e( 'Photos', 'petslist' ); ?></h3>
            <div class="dd-dog-form__photos-grid">

                <!-- Profile / Thumbnail -->
                <div class="dd-photo-upload-box">
                    <div class="dd-photo-upload-box__label"><?php _e( 'Profile Photo', 'petslist' ); ?> <span class="dd-required">*</span></div>
                    <div class="dd-photo-upload-area" id="dd-thumb-preview">
                        <?php if ( ! empty($thumb_id) ) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($thumb_id, 'medium')); ?>" alt="">
                        <?php else : ?>
                        <div class="dd-photo-upload-placeholder">
                            <i class="icon-pl-img"></i>
                            <span><?php _e( 'Click to upload', 'petslist' ); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="dd-thumb-id" name="dog_data[thumbnail_id]" value="<?php echo esc_attr($thumb_id ?? ''); ?>">
                    <button type="button" class="dd-btn dd-btn--ghost dd-btn--sm dd-upload-photo" data-target="dd-thumb-id" data-preview="dd-thumb-preview">
                        <i class="icon-pl-img"></i> <?php _e( 'Choose Photo', 'petslist' ); ?>
                    </button>
                </div>

                <!-- Front Photo -->
                <div class="dd-photo-upload-box">
                    <div class="dd-photo-upload-box__label"><?php _e( 'Front View Photo', 'petslist' ); ?> <span class="dd-required">*</span></div>
                    <div class="dd-photo-upload-area" id="dd-front-preview">
                        <?php if ( ! empty($front_photo) ) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($front_photo, 'medium')); ?>" alt="">
                        <?php else : ?>
                        <div class="dd-photo-upload-placeholder">
                            <i class="icon-pl-img"></i>
                            <span><?php _e( 'Front view photo', 'petslist' ); ?></span>
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
            <p class="dd-form-note"><i class="icon-pl-flash"></i> <?php _e( 'Minimum required: profile, front, and side photos. All photos are reviewed before publishing.', 'petslist' ); ?></p>
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
                        <?php echo $is_edit ? __( 'Update Dog Profile', 'petslist' ) : __( 'Submit Dog for Review', 'petslist' ); ?>
                    </span>
                    <span class="dd-btn__loader" style="display:none">
                        <i class="fa-solid fa-spinner fa-spin"></i> <?php _e( 'Saving...', 'petslist' ); ?>
                    </span>
                </button>
            </div>
        </div>

    </form>

</div>
