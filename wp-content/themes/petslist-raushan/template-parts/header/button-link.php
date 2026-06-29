<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use RadiusTheme\Petslist\Options;
 use RadiusTheme\Petslist\Helper;
?>
<?php if ( Options::$options['header_btn'] && Options::$options['header_btn_txt'] ): ?>
    <div class="header-btn-area">
        <a class="button-style-1 btn-anim" href="<?php echo esc_url( Options::$options['header_btn_url'] ); ?>">
            <?php echo Helper::plus_icon(); ?>
            <span><?php echo esc_html( Options::$options['header_btn_txt'] ); ?></span>
        </a>
    </div>
<?php endif; ?>

