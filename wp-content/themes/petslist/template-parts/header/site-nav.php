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
        <?php 
        // Force primary menu display
        $primary_menu_id = get_nav_menu_locations()['primary'] ?? 0;
        if ( $primary_menu_id ) {
            wp_nav_menu( array_merge( $nav_menu_args, array( 'menu' => $primary_menu_id ) ) );
        } elseif ( has_nav_menu( 'primary' ) ) { 
            wp_nav_menu( $nav_menu_args );
        } else {
            // Fallback: show hardcoded menu
            $pages = array(
                'Home'      => home_url('/'),
                'About'     => home_url('/about/'),
                'Directory' => home_url('/listings/'),
                'Plan'      => home_url('/dog-directory-plans/'),
                'Contact'   => home_url('/contact/'),
            );
            echo '<ul id="menu" class="menu">';
            foreach ( $pages as $label => $url ) {
                echo '<li class="menu-item"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
            }
            echo '</ul>';
        } ?>
    </div>
</div>