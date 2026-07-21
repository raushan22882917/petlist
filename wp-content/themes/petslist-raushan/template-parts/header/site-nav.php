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

<?php
$current_path = strtok( $_SERVER['REQUEST_URI'] ?? '', '?' );
$nav_items = [
    '/'                     => __( 'Home', 'petslist' ),
    '/about/'                => __( 'About', 'petslist' ),
    '/dog-directory/'       => __( 'Directory', 'petslist' ),
    '/dog-directory-plans/' => __( 'Plan', 'petslist' ),
    '/contact/'              => __( 'Contact', 'petslist' ),
];
?>
<div class="main-navigation-area <?php echo esc_attr( $petslist_menu_alignment ) ?>">
    <div id="main-navigation" class="main-navigation">
        <nav>
            <ul id="menu" class="menu">
                <?php foreach ( $nav_items as $path => $label ) : 
                    $url = home_url( $path );
                    $is_active = ( $path === '/' ) 
                        ? ( is_front_page() || $current_path === '/' || $current_path === '/dog/' )
                        : ( str_contains( $current_path, rtrim( $path, '/' ) ) );
                    $class = 'menu-item' . ( $is_active ? ' current-menu-item' : '' );
                ?>
                <li class="<?php echo esc_attr( $class ); ?>"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>