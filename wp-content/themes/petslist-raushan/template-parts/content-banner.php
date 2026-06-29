<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

?>
<?php
if ( Options::$has_breadcrumb ):
	do_action( 'petslist_breadcrumb' );
endif;