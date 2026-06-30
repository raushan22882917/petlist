<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

// Force a consistent centered menu on every page (demo navbar layout),
// ignoring per-page layout meta. Override with the filter if needed.
$petslist_menu_alignment = apply_filters( 'petslist_menu_alignment', 'menu-center', Options::$menu_alignment );

?>

<div class="main-navigation-area <?php echo esc_attr( $petslist_menu_alignment ) ?>">
    <div id="main-navigation" class="main-navigation">
        <nav>
            <ul id="menu" class="menu">
                <li class="menu-item"><a href="<?php echo esc_url( home_url('/') ); ?>">Home</a></li>
                <li class="menu-item"><a href="<?php echo esc_url( home_url('/about/') ); ?>">About</a></li>
                <li class="menu-item"><a href="<?php echo esc_url( home_url('/dog-directory/') ); ?>">Directory</a></li>
                <li class="menu-item"><a href="<?php echo esc_url( home_url('/dog-directory-plans/') ); ?>">Plan</a></li>
                <li class="menu-item"><a href="<?php echo esc_url( home_url('/contact/') ); ?>">Contact</a></li>
            </ul>
        </nav>
    </div>
</div>