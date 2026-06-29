<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace RadiusTheme\Petslist_Core;

$url   = urlencode( get_permalink() );
$title = urlencode( get_the_title() );

$defaults = [
	'facebook'  => [
		'url'  => "http://www.facebook.com/sharer.php?u=$url",
		'icon' => 'fa-facebook-f',
	],
	'twitter'   => [
		'url'  => "https://twitter.com/intent/tweet?source=$url&text=$title:$url",
		'icon' => 'fa-twitter'
	],
	'linkedin'  => [
		'url'  => "http://www.linkedin.com/shareArticle?mini=true&url=$url&title=$title",
		'icon' => 'fa-linkedin-in'
	],
	'pinterest' => [
		'url'  => "http://pinterest.com/pin/create/button/?url=$url&description=$title",
		'icon' => 'fa-pinterest'
	],
];

foreach ( $sharer as $key => $value ) {
	if ( ! $value ) {
		unset( $defaults[ $key ] );
	}
}

$sharers = apply_filters( 'rdtheme_social_sharing_icons', $defaults );

?>

<ul class="social-icon-list d-flex align-items-center">
	<?php foreach ( $sharers as $key => $sharer ): ?>
		<li class="social-<?php echo esc_attr( $key ); ?>">
			<a href="<?php echo esc_url( $sharer['url'] ); ?>" target="_blank">
				<i class="fab <?php echo esc_attr( $sharer['icon'] ); ?>"></i>
			</a>
		</li>
	<?php endforeach; ?>
</ul>