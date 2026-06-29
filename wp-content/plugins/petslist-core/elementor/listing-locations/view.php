<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/locations/view-2.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use Rtcl\Helpers\Link;

extract( $data );

$class = $data['display_count'] ? 'rtin-has-count' : '';

$cols_list = '';
$cols = [
    'xl' => $data['desktop_grid_column'],
    'lg' => $data['medium_desktop_grid_column'],
    'md' => $data['tablet_grid_column'],
    'sm' => $data['mobile_grid_column'],
    'xs' => $data['samll_mobile_grid_column']
];

foreach( $cols as $key => $value ) {
    $cols_list .= 'row-cols-'.$key.'-'.$value.' ';
}

?>

<div class="listing-box-wrap listing-shortcode location-shortcode listing-grid-shortcode">
    <div class="row <?php echo esc_attr( $cols_list ); ?> row-cols-1 g-<?php echo esc_attr( $data['columns_gap'] ); ?> elementor-addon justify-content-center">
        <?php
            foreach ( $data['locations'] as $item ) {
                $term = get_term( $item['location_name'], 'rtcl_location' );
                if ( $term && !is_wp_error( $term ) ) {
                    $item['title']     = $term->name;
                    $item['count']     = $this->rt_term_post_count( $term->term_id );
                    $item['permalink'] = Link::get_location_page_link( $term );
                } else {
                    $item['permalink'] = '';
                    $item['title'] = esc_html__( 'Please Select a Location and Background image', 'petslist-core' );
                    $item['count'] = 0;
                    $item['display_count'] = $data['display_count'] = false;
                }
                $count_html = number_format_i18n( $item['count'] );
                ?>
                <div class="col">
                    <?php if ( $data['style'] == 1 ) { ?>
                        <div class="location-box-layout-1 common-style">
                            <div class="location-information d-flex justify-content-between align-items-center">
                                <div class="location-count">
                                    <i class="icon-pl-location"></i>
                                    <h3 class="item-title">
                                        <a href="<?php echo esc_url( $item['permalink']); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    </h3>
                                    <?php if ( $data['display_count'] ): ?>
                                    <div class="listing-number">(<?php echo esc_html( $count_html ); ?>)</div>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $data['enable_link'] ): ?>
                                    <div class="btn-box">
                                        <a href="<?php echo esc_url( $item['permalink']); ?>"><i class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } elseif ( $data['style'] == 2 ) { ?>
                        <div class="location-box-layout-2 common-style">
                            <div class="location-information d-flex justify-content-between align-items-center">
                                <?php echo wp_get_attachment_image( $item['location_img']['id'], 'full' ); ?>
                                <div class="state-overlay"></div>
                                <div class="location-count">
                                    <h3 class="item-title">
                                        <a href="<?php echo esc_url( $item['permalink']); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    </h3>
                                    <?php if ( $data['display_count'] ): ?>
                                    <div class="listing-number">(<?php echo esc_html( $count_html ); ?>)</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="location-box-layout-1 common-style">
                            <div class="location-information d-flex justify-content-between align-items-center">
                                <div class="location-count">
                                    <i class="icon-pl-location"></i>
                                    <h3 class="item-title">
                                        <a href="<?php echo esc_url( $item['permalink']); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    </h3>
                                    <?php if ( $data['display_count'] ): ?>
                                    <div class="listing-number">(<?php echo esc_html( $count_html ); ?>)</div>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $data['enable_link'] ): ?>
                                    <div class="btn-box">
                                        <a href="<?php echo esc_url( $item['permalink']); ?>"><i class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
        <?php } ?>
    </div>
</div>