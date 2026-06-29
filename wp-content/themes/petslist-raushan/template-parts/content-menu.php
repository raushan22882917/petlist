<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

$header_style = Options::$header_style ? Options::$header_style : 1;

?>
    <header id="site-header" class="site-header">
		<?php
		if ( Options::$has_top_bar ) {
			get_template_part( 'template-parts/header/header-top' );
		}
		get_template_part( 'template-parts/header/header', $header_style );
		?>
    </header>
	<?php get_template_part( 'template-parts/header/header', 'offscreen' ); ?>