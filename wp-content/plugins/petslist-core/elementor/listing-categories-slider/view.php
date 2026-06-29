<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/locations/view-2.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use Rtcl\Helpers\Link;
use Elementor\Icons_Manager;
use RadiusTheme\Petslist\Listing_Functions;

extract( $data );
$class = $data['display_count'] ? 'rtin-has-count' : '';
$all_categories = $data['categories'];

?>

<div class="carousel-categories">
    <div class="slide-wrap">
        <div class="petslist-core-categories-slider" data-slider-options="<?php echo esc_attr( $data['slider_data'] ); ?>">
            <div class="swiper-wrapper">
                <?php
                    foreach ( $all_categories as $item ) {
                        $term = get_term( $item['category_name'], 'rtcl_category' );
                        if ( $term && !is_wp_error( $term ) ) {
                            $item['term_id']   = $term->term_id;
                            $item['title']     = $term->name;
                            $item['count']     = $this->rt_term_post_count( $term->term_id );
                            $item['permalink'] = Link::get_location_page_link( $term );
                        } else {
                            $item['permalink'] = '';
                            $item['title'] = esc_html__( 'Please Select a Category', 'petslist-core' );
                            $item['count'] = 0;
                            $item['display_count'] = $data['display_count'] = false;
                        }
                        $count_html = number_format_i18n( $item['count'] );

                        $bg_color = '';
                        $term_color = get_term_meta( $term->term_id, 'rt_category_color', true );
                        if ($item['icon_condition'] == 'default_icon' ) {
                            $bg_color = 'style="background-color: #'.esc_attr($term_color).'"';
                        }

                        $bg_shape = $item['category_bg_shape']['id'];
                ?>
                <div class="swiper-slide">
                    <div class="category-list-slider layout-<?php echo esc_attr($data['style']); ?> justify-content-<?php echo esc_attr($data['alignment']); ?>">
                    <div class="category-item">
                        <?php if ($display_icon) { ?>
                        <div class="icon" <?php echo wp_kses_post( $bg_color ); ?>>
                            <?php
                                if ($item['icon_condition'] == 'custom_icon' ) {
                                    if ( $bg_shape ) {
                                        echo wp_get_attachment_image( $item['category_bg_shape']['id'], 'full', '', array( "class" => "cat-shape-bg" ) );
                                    }
                                    Icons_Manager::render_icon( $item['category_icon'], [ 'aria-hidden' => 'true' ] );
                                } else {
                                    echo Listing_Functions::listing_cat_icon( $item['term_id'], $item['icon_type'] ); 
                                }
                            ?>
                        </div>
                        <?php } ?>
                        <div class="content">
                            <a href="<?php echo esc_url( $item['permalink']); ?>" class="category-name"><?php echo esc_html( $item['title'] ); ?></a>
                            <?php if ($display_count) { ?>
                                <p class="item-number">(<?php echo wp_kses_post( $count_html ); ?>)</p>
                            <?php } ?>
                        </div>
                    </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php if ($data['dots']){ ?>
                <div class="swiper-pagination"></div>
            <?php } if ($data['arrows']) { ?>
            <div class="sliderNav">
                <div class="sliderNav_btn swiper-button-prev"></div>
                <div class="sliderNav_btn swiper-button-next"></div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>