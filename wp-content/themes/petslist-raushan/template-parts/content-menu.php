<?php
/**
 * @author  RadiusTheme
 * @since   1.0.0
 * @version 1.0.0
 */

use RadiusTheme\Petslist\Options;

// Use the same header/navbar on every page.
Options::$header_style   = '3';
Options::$has_top_bar    = false;
Options::$has_tr_header  = false;

?>
    <header id="site-header" class="site-header">
		<?php
		if ( Options::$has_top_bar ) {
			get_template_part( 'template-parts/header/header-top' );
		}
		get_template_part( 'template-parts/header/header', '3' );
		?>
    </header>
	<?php get_template_part( 'template-parts/header/header', 'offscreen' ); ?>