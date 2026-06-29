<?php
/**
 * Dashboard Tab: My Dogs
 * @package Petslist Dog Directory
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
$dogs    = dd_get_user_dogs( $user_id, 'any', -1 );
?>

<div class="dd-tab-dogs">

    <div class="dd-tab-dogs__header">
        <h2><?php _e( 'My Dogs', 'petslist' ); ?> <span class="dd-count"><?php echo count($dogs); ?></span></h2>
        <p class="dd-tab-dogs__subtitle"><?php _e('Manage and monitor the health records and status of your pets.', 'petslist'); ?></p>
        <a href="<?php echo esc_url( dd_dashboard_url('add-dog') ); ?>" class="dd-btn dd-btn--primary" style="position: absolute; right: 24px; top: 24px;">
            <i class="fa-solid fa-plus" style="margin-right: 6px;"></i><?php _e( 'Add New Dog', 'petslist' ); ?>
        </a>
    </div>

    <div id="dd-dog-list-message" class="dd-auth-message" style="display:none"></div>

    <?php if ( $dogs ) : ?>
    <div class="dd-dogs-card-panel">

        <!-- Filters Bar -->
        <div class="dd-table-filters">
            <div class="dd-table-filters__search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="dd-table-filter-input" data-column="name" placeholder="<?php esc_attr_e('Search Name...', 'petslist'); ?>">
            </div>
            <div class="dd-table-filters__select">
                <select class="dd-table-filter-select" data-column="breed">
                    <option value=""><?php _e('All Breeds', 'petslist'); ?></option>
                    <?php
                    $user_breeds = array_filter(array_unique(array_map(fn($d) => dd_get_dog_meta($d->ID)['breed'] ?? '', $dogs)));
                    asort($user_breeds);
                    foreach ( $user_breeds as $ub ) : if (empty($ub)) continue;
                    ?>
                    <option value="<?php echo esc_attr($ub); ?>"><?php echo esc_html($ub); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="dd-table-filters__select">
                <select class="dd-table-filter-select" data-column="gender">
                    <option value=""><?php _e('All Genders', 'petslist'); ?></option>
                    <option value="Male"><?php _e('Male', 'petslist'); ?></option>
                    <option value="Female"><?php _e('Female', 'petslist'); ?></option>
                </select>
            </div>
            <div class="dd-table-filters__select">
                <select class="dd-table-filter-select" data-column="status">
                    <option value=""><?php _e('All Statuses', 'petslist'); ?></option>
                    <option value="Live"><?php _e('Live', 'petslist'); ?></option>
                    <option value="Pending"><?php _e('Pending', 'petslist'); ?></option>
                    <option value="Draft"><?php _e('Draft', 'petslist'); ?></option>
                </select>
            </div>
        </div>

        <!-- Table Wrap -->
        <div class="dd-dogs-table-wrap">
            <table class="dd-dogs-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Photo', 'petslist' ); ?></th>
                        <th><?php _e( 'Name', 'petslist' ); ?></th>
                        <th><?php _e( 'Breed', 'petslist' ); ?></th>
                        <th><?php _e( 'Gender', 'petslist' ); ?></th>
                        <th><?php _e( 'Status', 'petslist' ); ?></th>
                        <th><?php _e( 'Added', 'petslist' ); ?></th>
                        <th><?php _e( 'Actions', 'petslist' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $dogs as $dog ) :
                        $meta  = dd_get_dog_meta( $dog->ID );
                        $thumb = get_the_post_thumbnail_url( $dog->ID, 'thumbnail' ) ?: dd_placeholder_image();
                        $status_map = [
                            'publish' => [ 'label' => __( 'Live', 'petslist' ), 'class' => 'active' ],
                            'pending' => [ 'label' => __( 'Pending', 'petslist' ), 'class' => 'pending' ],
                            'draft'   => [ 'label' => __( 'Draft', 'petslist' ), 'class' => 'draft' ],
                            'trash'   => [ 'label' => __( 'Deleted', 'petslist' ), 'class' => 'draft' ],
                        ];
                        $status_info = $status_map[ $dog->post_status ] ?? [ 'label' => ucfirst($dog->post_status), 'class' => 'draft' ];

                        // Generate preview link for drafts/pending so owner can preview them
                        $view_url = ($dog->post_status === 'publish') ? get_permalink($dog->ID) : get_preview_post_link($dog->ID);
                        if ( !$view_url ) {
                            $view_url = add_query_arg(['preview' => 'true'], get_permalink($dog->ID));
                        }
                    ?>
                    <tr data-post-id="<?php echo $dog->ID; ?>">
                        <td class="dd-dogs-table__photo">
                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($dog->post_title); ?>">
                        </td>
                        <td class="dd-dogs-table__name">
                            <span class="dd-dog-name-text"><?php echo esc_html($dog->post_title); ?></span>
                        </td>
                        <td class="dd-dogs-table__breed">
                            <?php echo esc_html( $meta['breed'] ?? '—' ); ?>
                        </td>
                        <td class="dd-dogs-table__gender">
                            <?php if ( $meta['gender'] ) : ?>
                            <span class="dd-gender-tag dd-gender-tag--<?php echo strtolower($meta['gender']); ?>">
                                <?php echo $meta['gender'] === 'Male' ? '♂' : '♀'; ?> <?php echo esc_html($meta['gender']); ?>
                            </span>
                            <?php else : ?>—<?php endif; ?>
                        </td>
                        <td class="dd-dogs-table__status">
                            <span class="dd-status-pill dd-status-pill--<?php echo esc_attr($status_info['class']); ?>">
                                <?php echo esc_html($status_info['label']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($dog->post_date)); ?></td>
                        <td>
                            <div class="dd-dogs-table__actions">
                                <a href="<?php echo esc_url($view_url); ?>" target="_blank" class="dd-action-btn dd-action-btn--view" title="<?php esc_attr_e('View Profile', 'petslist'); ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="<?php echo esc_url(dd_dashboard_url('dogs') . '&edit=' . $dog->ID); ?>" class="dd-action-btn dd-action-btn--edit" title="<?php esc_attr_e('Edit', 'petslist'); ?>">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                                <button class="dd-action-btn dd-action-btn--delete dd-delete-dog" data-id="<?php echo $dog->ID; ?>" title="<?php esc_attr_e('Delete', 'petslist'); ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div class="dd-dogs-table-footer">
            <div class="dd-dogs-table-footer__count">
                <?php printf(__('Showing 1 to %d of %d dogs', 'petslist'), count($dogs), count($dogs)); ?>
            </div>
            <div class="dd-dogs-table-footer__pagination">
                <button class="dd-page-btn" disabled><i class="fa-solid fa-angle-left"></i></button>
                <button class="dd-page-btn dd-page-btn--active">1</button>
                <button class="dd-page-btn" disabled><i class="fa-solid fa-angle-right"></i></button>
            </div>
        </div>

    </div>

    <!-- Right Side Profile Drawer Panel -->
    <div class="dd-drawer" id="dd-dog-drawer">
        <div class="dd-drawer__overlay"></div>
        <div class="dd-drawer__content">
            <div class="dd-drawer__header">
                <h3><?php _e('Dog Profile Details', 'petslist'); ?></h3>
                <button class="dd-drawer__close" id="dd-drawer-close-btn">&times;</button>
            </div>
            <div class="dd-drawer__body" id="dd-dog-drawer-body">
                <!-- Loaded Dynamically via AJAX -->
                <div class="dd-drawer-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p><?php _e('Loading profile details...', 'petslist'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php else : ?>
    <div class="dd-empty-state">
        <div class="dd-empty-state__icon">🐾</div>
        <h3><?php _e( 'No dogs listed yet', 'petslist' ); ?></h3>
        <p><?php _e( 'Start by adding your first dog profile. It will be reviewed and published within 24 hours.', 'petslist' ); ?></p>
        <a href="<?php echo esc_url( dd_dashboard_url('add-dog') ); ?>" class="dd-btn dd-btn--primary">
            <i class="fa-solid fa-plus"></i> <?php _e( 'Add Your First Dog', 'petslist' ); ?>
        </a>
    </div>
    <?php endif; ?>

</div>
