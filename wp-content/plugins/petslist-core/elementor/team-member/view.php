<?php
/**
 * This file can be overridden by copying it to yourtheme/elementor-custom/pricing-table/view-2.php
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use Elementor\Icons_Manager;

extract( $data );

if ( ! empty( $data['link']['url'] ) ) {
    $this->add_link_attributes( 'link', $data['link'] );
}

?>
<div class="team-card">
    <div class="team-img-wrapper">
        <?php echo wp_get_attachment_image( $data['picture']['id'], 'full' ); ?>
    </div>
    <div class="team-content">
        <?php if (is_array($data['social_lists'])) { ?>
            <ul class="social-list d-flex align-items-center flex-wrap">
                <?php foreach ( $data['social_lists'] as $value ) { 
                        if ( ! empty( $data['social_link']['url'] ) ) {
                            $this->add_link_attributes( 'social_link', $data['social_link'] );
                        }
                    ?>
                    <li class="social-item">
                        <a <?php echo $this->get_render_attribute_string( 'social_link' ); ?> class="footer-social-link facebook circle-radius d-flex justify-content-center align-items-center ">
                            <?php Icons_Manager::render_icon( $value['social_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <h3 class="title">
            <?php if ( ! empty( $data['link']['url'] ) ) { ?>
                <a <?php echo $this->get_render_attribute_string( 'link' ); ?> class="name">
                    <?php echo esc_html( $name ); ?>
                </a>
            <?php } else {
                echo esc_html( $name );
            }
            ?>
        </h3>
        <?php if ( ! empty( $designation ) ) { ?>
            <p class="designation para-text"><?php echo esc_html( $designation ); ?></p>
        <?php } ?>
    </div>
</div>