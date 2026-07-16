<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.1.0
 */

add_editor_style( 'style-editor.css' );

if ( !isset( $content_width ) ) {
	$content_width = 1240;
}

class Petslist_Main {
	public $theme   = 'petslist';
	public $action  = 'petslist_theme_init';
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'load_textdomain' ) );
		add_action( 'admin_notices',     array( $this, 'plugin_update_notices' ) );
		$this->includes();		
	}
	public function load_textdomain(){
		load_theme_textdomain( $this->theme, get_template_directory() . '/languages' );
	}
	public function includes(){
		do_action( $this->action );

		require_once get_template_directory() . '/lib/lc-helper.php';
		require_once get_template_directory() . '/lib/lc-utility.php';
		require_once get_template_directory() . '/lib/class-tgm-plugin-activation.php';
		require_once get_template_directory() . '/inc/init.php';
		require_once get_template_directory() . '/inc/CustomPages/helpers.php';
		require_once get_template_directory() . '/inc/Customizer/sanitization.php';

	}

	public function plugin_update_notices() {
		$plugins = array();

		if ( defined( 'PETSLIST_CORE' ) ) {
			if ( version_compare( PETSLIST_CORE, '1.2.0', '<' ) ) {
				$plugins[] = 'Petslist Core';
			}
		}

		foreach ( $plugins as $plugin ) {
			$notice = '<div class="error"><p>' . sprintf( __( "Please update plugin <b><i>%s</b></i> to the latest version otherwise some functionalities will not work properly. You can update it from <a href='%s'>here</a>", 'petslist' ), $plugin, menu_page_url( 'petslist-install-plugins', false ) ) . '</p></div>';
			echo wp_kses( $notice, 'alltext_allow' );
		}
	}
}

new Petslist_Main;

/**
 * Dog Directory Module Bootstrap
 */
function petslist_boot_dog_directory() {
    $dir = get_template_directory() . '/inc/DogDirectory/';
    // Load all Dog Directory classes
    foreach ( [
        'Roles',
        'DogCPT',
        'Subscription',
        'Stripe',
        'PayPal',
        'Ajax',
        'Scripts',
        'Shortcodes',
        'Notifications',
        'AccessControl',
        'Admin',
        'Init',
    ] as $class ) {
        $file = $dir . $class . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    // Boot
    \RadiusTheme\Petslist\DogDirectory\Init::instance();
}
add_action( 'after_setup_theme', 'petslist_boot_dog_directory', 20 );