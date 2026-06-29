<?php
/**
 * 
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

extract( $data );
$align = $data['align'];
$heading_tag_html = sprintf( '<%1$s %2$s class="heading-title">%3$s</%1$s>', $data['heading_tag'], $this->get_render_attribute_string( 'title' ), $data['title'] );

?>
<div class="section-heading" style="--title-shape-bg: url(<?php echo esc_url( $data['title_shape']['url'] ); ?>);">
    <?php echo $heading_tag_html; ?>
    <?php echo $data['desc']; ?>
</div>