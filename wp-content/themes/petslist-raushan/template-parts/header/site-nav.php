<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

$nav_menu_args  = Helper::nav_menu_args();

?>

<div class="main-navigation-area <?php echo esc_attr( Options::$menu_alignment ) ?>">
    <div id="main-navigation" class="main-navigation">
        <?php if ( has_nav_menu( 'primary' ) ) { 
            wp_nav_menu( $nav_menu_args );
        } else {
            if ( is_user_logged_in() ) {
                echo '<ul id="menu" class="menu fallbackcd-menu-item"><li class="menu-item"><a class="fallbackcd" href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Add a menu', 'petslist' ) . '</a></li></ul>';
            }
        } ?>
    </div>
</div>