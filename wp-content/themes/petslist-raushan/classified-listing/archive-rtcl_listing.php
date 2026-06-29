<?php
/**
 * @package ClassifiedListing/Templates
 * @version 1.5.4
 */

use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;
use Rtcl\Helpers\Functions;

defined( 'ABSPATH' ) || exit;

get_header( 'listing' );
?>
<div class="listing-archive-wrapper content-area">
	<div class="container">
		<div class="row">

			<?php if ( Options::$layout == 'left-sidebar' ) { ?>
				<div class="<?php Helper::the_listing_sidebar_class(); ?>">
					<?php 
						if ( Helper::has_sidebar() ) {
							/**
							 * Hook: rtcl_sidebar.
							 *
							 * @hooked rtcl_get_sidebar - 10
							 */
							do_action( 'rtcl_sidebar' );
						}
					?>
				</div>
			<?php } ?>	
		
			<div class="<?php Helper::the_listing_layout_class(); ?>">
				<?php 
				/**
				 * Hook: rtcl_before_main_content.
				 *
				 * @hooked rtcl_output_content_wrapper - 10 (outputs opening divs for the content)
				 */
				do_action( 'rtcl_before_main_content' );
				
				if ( Options::$options['listing_archive_title'] ): ?>
					<header class="rtcl-listing-header">
						<?php if ( apply_filters( 'rtcl_show_page_title', true ) ) : ?>
							<h1 class="rtcl-listings-header-title page-title">
								<?php 
									if ( Options::$options['listing_archive_title_text'] ) { 
										echo Options::$options['listing_archive_title_text'];
									} else {
										Functions::page_title(); 
									}
								?>
							</h1>
						<?php endif; ?>

						<?php
						/**
						 * Hook: rtcl_archive_description.
						 *
						 * @hooked TemplateHooks::taxonomy_archive_description - 10
						 * @hooked TemplateHooks::listing_archive_description - 10
						 */
						do_action( 'rtcl_archive_description' );
						?>
					</header>
				<?php endif; 

				if ( rtcl()->wp_query()->have_posts() ) {

					/**
					 * Hook: rtcl_before_listing_loop.
					 *
					 * @hooked TemplateHooks::output_all_notices() - 10
					 * @hooked TemplateHooks::listings_actions - 20
					 *
					 */
					do_action( 'rtcl_before_listing_loop' );


					Functions::listing_loop_start();

					/**
					 * Prepend listings
					 */
					do_action( 'rtcl_listing_loop_prepend_data' );

					while ( rtcl()->wp_query()->have_posts() ) : rtcl()->wp_query()->the_post();

						/**
						 * Hook: rtcl_listing_loop.
						 */
						do_action( 'rtcl_listing_loop' );

						Functions::get_template_part( 'content', 'listing' );

					endwhile;

					Functions::listing_loop_end();

					/**
					 * Hook: rtcl_after_listing_loop.
					 *
					 * @hooked TemplateHook::pagination() - 10
					 */
					do_action( 'rtcl_after_listing_loop' );
				} else {

					/**
					 * Prepend listings
					 */
					ob_start();
					do_action( 'rtcl_listing_loop_prepend_data' );
					$listing_loop_prepend_data = ob_get_clean();
					if ( $listing_loop_prepend_data ) {
						Functions::listing_loop_start();
						echo wp_kses_post( $listing_loop_prepend_data );
						Functions::listing_loop_end();
					}

					/**
					 * Hook: rtl_no_listings_found.
					 *
					 * @hooked no_listings_found - 10
					 */
					do_action( 'rtcl_no_listings_found' );
				}

				/**
				 * Hook: rtcl_after_main_content.
				 *
				 * @hooked rtcl_output_content_wrapper_end - 10 (outputs closing divs for the content)
				 */
				do_action( 'rtcl_after_main_content' );

				?>
			</div>

			<?php if ( Options::$layout == 'right-sidebar' ) { ?>
				<div class="<?php Helper::the_listing_sidebar_class(); ?>">
					<?php 
						if ( Helper::has_sidebar() ) {
							/**
							 * Hook: rtcl_sidebar.
							 *
							 * @hooked rtcl_get_sidebar - 10
							 */
							do_action( 'rtcl_sidebar' );
						}
					?>
				</div>
			<?php } ?>	
		</div>
	</div>
</div>
<?php
get_footer( 'listing' );