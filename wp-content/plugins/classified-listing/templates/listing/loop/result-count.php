<?php
/**
 * Result Count
 *
 * @var int $total
 * @var int $per_page
 * @var int $current
 * @var array $options
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="rtcl-result-count">
	<?php
	if ( 1 === $total ) {
		esc_html_e( 'Showing the single result', 'classified-listing' );
	} elseif ( $total <= $per_page || - 1 === $per_page ) {
		/* translators: %d: total results */
		echo esc_html( sprintf( _n( 'Showing all %d result', 'Showing all %d results', $total, 'classified-listing' ), number_format_i18n( $total ) ) );
	} else {
		$first = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );
		/* translators: 1: first result 2: last result 3: total results */
		echo esc_html( sprintf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'classified-listing' ), $first, $last, $total ) );
	}
	?>
</div>
