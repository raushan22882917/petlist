<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

?>

<div class="main-navigation-area <?php echo esc_attr( Options::$menu_alignment ) ?>">
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