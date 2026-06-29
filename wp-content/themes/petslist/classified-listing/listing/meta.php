<?php
/**
 * Listing meta
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

 use Rtcl\Helpers\Functions;
 use RadiusTheme\Petslist\Listing_Functions;

if ( ! $listing ) {
	global $listing;
}

if ( empty( $listing ) ) {
	return;
}

if ( isset( $_GET['view'] ) && in_array( $_GET['view'], [ 'grid', 'list' ], true ) ) {
	$view = esc_attr( $_GET['view'] );
} else {
	$view = Functions::get_option_item( 'rtcl_general_settings', 'default_view', 'list' );
}

if ($view == 'list') {
?>
	<ul class="rtcl-listing-meta-data">
		<?php if ( $listing->can_show_user() ) : ?>
			<li class="author">
				<i class="icon-pl-account"></i>
				<?php esc_html_e( 'by ', 'petslist' ); ?>
				<?php if ( $listing->can_add_user_link() && ! is_author() ) : ?>
					<a href="<?php echo esc_url( $listing->get_the_author_url() ); ?>"><?php $listing->the_author(); ?></a>
				<?php else : ?>
					<?php $listing->the_author(); ?>
				<?php endif; ?>
				<?php do_action( 'rtcl_after_author_meta', $listing->get_owner_id() ); ?>
			</li>
		<?php endif; ?>
		<?php
		if ( $listing->has_location() ) :
			?>
			<li class="rt-location">
				<i class="icon-pl-location"></i>
				<?php $listing->the_locations( true, true ); ?>
			</li>
		<?php endif; ?>
		<?php if ( $listing->can_show_views() ) : ?>
			<li class="rt-views">
				<i class="icon-pl-eye"></i>
				<?php echo sprintf( _n( '%s view', '%s views', $listing->get_view_counts(), 'petslist' ), number_format_i18n( $listing->get_view_counts() ) ); ?>
			</li>
		<?php endif; ?>
		<?php if ( $listing->can_show_category() ) : 
			if ( $view == 'grid' ) {
		?>
			<li class="rt-category">
				<i class="fa-solid fa-tags"></i>
				<?php Listing_Functions::petslist_listing_categories(); ?>
			</li>
		<?php } endif; ?>
		<?php if ( $listing->can_show_date() ) : ?>
			<li class="rt-date-time">
				<i class="icon-pl-clock"></i>
				<?php Listing_Functions::petslist_the_time(get_the_ID()); ?>
			</li>
		<?php endif; ?>
	</ul>
<?php } else { 
	
	if ( ! $listing->can_show_user() && ! $listing->can_show_location() && ! $listing->can_show_views() && ! $listing->can_show_category() ) {
		return;
	}
	
	?>
	<ul class="rtcl-listing-meta-data">
		<?php if ( $listing->can_show_user() ) : ?>
			<li class="author">
				<i class="icon-pl-account"></i>
				<?php esc_html_e( 'by ', 'petslist' ); ?>
				<?php if ( $listing->can_add_user_link() && ! is_author() ) : ?>
					<a href="<?php echo esc_url( $listing->get_the_author_url() ); ?>"><?php $listing->the_author(); ?></a>
				<?php else : ?>
					<?php $listing->the_author(); ?>
				<?php endif; ?>
				<?php do_action( 'rtcl_after_author_meta', $listing->get_owner_id() ); ?>
			</li>
		<?php endif; ?>
		<?php
		if ( $listing->has_location() && $listing->can_show_location() ) :
			?>
			<li class="rt-location">
				<i class="icon-pl-location"></i>
				<?php $listing->the_locations( true, true ); ?>
			</li>
		<?php endif; ?>
		<?php if ( $listing->can_show_views() ) : ?>
			<li class="rt-views">
				<i class="icon-pl-eye"></i>
				<?php echo sprintf( _n( '%s view', '%s views', $listing->get_view_counts(), 'petslist' ), number_format_i18n( $listing->get_view_counts() ) ); ?>
			</li>
		<?php endif; ?>
		<?php if ( $listing->can_show_category() ) : 
			if ( $view == 'grid' ) {
		?>
			<li class="rt-category">
				<i class="icon-pl-tag"></i>
				<?php Listing_Functions::petslist_listing_categories(); ?>
			</li>
		<?php } endif; ?>
		<?php if ( $listing->can_show_date() ) : ?>
			<li class="rt-date-time">
				<i class="icon-pl-clock"></i>
				<?php Listing_Functions::petslist_the_time(get_the_ID()); ?>
			</li>
		<?php endif; ?>
	</ul>
<?php } ?>