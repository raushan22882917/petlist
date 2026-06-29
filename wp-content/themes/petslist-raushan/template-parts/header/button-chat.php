<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

 use RadiusTheme\Petslist\Helper;
 use RadiusTheme\Petslist\Options;
 use Rtcl\Helpers\Link;

?>
<?php if ( Helper::is_chat_enabled() ): ?>    
    <a class="header-chat-icon rtcl-chat-unread-count" title="<?php echo esc_html( Options::$options['header_chat_text'] ); ?>" href="<?php echo esc_url( Link::get_my_account_page_link( 'chat' ) ); ?>">
    <i class="icon-pl-chat"></i>
    <?php echo esc_html( Options::$options['header_chat_text'] ); ?>
</a>
<?php endif; ?>