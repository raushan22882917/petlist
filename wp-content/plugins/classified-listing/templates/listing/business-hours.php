<?php
/**
 * Business Hours
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 *
 * @var array $business_hours business hours
 * @var int $current_week_day Day of the week (0 on Sunday)
 * @var array $options
 */


use Rtcl\Controllers\BusinessHoursController as BHS;
use Rtcl\Helpers\Utility;
use Rtcl\Resources\Options;

global $wp_locale;
echo '<div class="rtclbh-block">';

if ( !empty( $options['show_open_status'] ) ) {
	if ( BHS::openStatus( $business_hours ) ) {
		printf( '<div class="rtclbh-status rtclbh-status-open">%s</div>', !empty( $options['open_status_text'] ) ? esc_html( $options['open_status_text'] ) : esc_html__( 'We are currently open.', 'classified-listing' ) );
	} else {
		printf( '<div class="rtclbh-status rtclbh-status-closed">%s</div>', !empty( $options['close_status_text'] ) ? esc_html( $options['close_status_text'] ) : esc_html__( 'Sorry, we are currently closed.', 'classified-listing' ) );
	}
}

?>
	<table class="rtclbh">
		<?php if ( $options['header'] ) : ?>
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php echo !empty( $options['open_text'] ) ? esc_html( $options['open_text'] ) : esc_html__( 'Open', 'classified-listing' ); ?></th>
				<th class="rtclbh-separator">&nbsp;</th>
				<th><?php echo !empty( $options['close_text'] ) ? esc_html( $options['close_text'] ) : esc_html__( 'Close', 'classified-listing' ); ?></th>
			</tr>
			</thead>

		<?php endif; ?>

		<?php if ( $options['footer'] ) : ?>

			<tfoot>
			<tr>
				<th>&nbsp;</th>
				<th><?php echo !empty( $options['open_text'] ) ? esc_html( $options['open_text'] ) : esc_html__( 'Open', 'classified-listing' ); ?></th>
				<th class="rtclbh-separator">&nbsp;</th>
				<th><?php echo !empty( $options['close_text'] ) ? esc_html( $options['close_text'] ) : esc_html__( 'Close', 'classified-listing' ); ?></th>
			</tr>
			</tfoot>

		<?php endif; ?>

		<tbody>
		<?php

		foreach ( Options::get_week_days() as $dayKey => $day ) {

			// Display the day as either its initial or abbreviation.
			switch ( $options['day_name'] ) {

				case 'initial' :

					$day = $wp_locale->get_weekday_initial( $day );
					break;

				case 'abbrev' :

					$day = $wp_locale->get_weekday_abbrev( $day );
					break;
			}
			$dayData = !empty( $business_hours[$dayKey] ) ? $business_hours[$dayKey] : '';

			// Show the "Closed" message if there are no open and close hours recorded for the day.
			if ( !BHS::openToday( $dayData ) ) {
				if ( $options['show_closed_day'] ) {
					printf( '<tr class="rtclbh-closed %1$s"><th>%2$s</th><td class="rtclbh-info" colspan="3">%3$s</td></tr>',
						sprintf( 'rtclbh-day-%d%s', absint( $dayKey ), $current_week_day === $dayKey ? ' rtclbh-active' : '' ),
						esc_attr( $day ),
						$current_week_day === $dayKey ? ( !empty( $options['closed_today_text'] ) ? esc_html( $options['closed_today_text'] ) : esc_html__( 'Closed Today', 'classified-listing' ) )
							: ( !empty( $options['closed_24_text'] ) ? esc_html( $options['closed_24_text'] ) : esc_html__( 'Closed', 'classified-listing' ) )
					);
				}

				// Exit this loop.
				continue;
			}


			if ( BHS::isOpenAllDayLong( $dayData ) ) {
				printf( '<tr class="rtclbh-opened %1$s"><th>%2$s</th><td class="rtclbh-info" colspan="3">%3$s</td></tr>',
					sprintf( 'rtclbh-day-%d%s', absint( $dayKey ), $current_week_day === $dayKey ? ' rtclbh-active' : '' ),
					esc_attr( $day ),
					$current_week_day === $dayKey ? ( !empty( $options['open_today_text'] ) ? esc_html( $options['open_today_text'] ) : esc_html__( 'Open Today (24 Hours)', 'classified-listing' ) )
						: ( !empty( $options['open_24_text'] ) ? esc_html( $options['open_24_text'] ) : esc_html__( 'Open (24 Hours)', 'classified-listing' ) )
				);

				// Exit this loop.
				continue;
			}

			$timePeriods = $dayData['times'];
			// If there are open and close hours recorded for the day, loop thru the open periods.
			foreach ( $timePeriods as $periodIndex => $timePeriod ) {

				if ( BHS::openPeriod( $timePeriod ) ) {
					printf( '<tr class="rtclbh-period %1$s" %2$s><th>%3$s</th><td class="rtclbh-open">%4$s</td><td class="rtclbh-separator">%5$s</td><td class="rtclbh-close">%6$s</td></tr>',
						sprintf( 'rtclbh-day-%d%s%s',
							absint( $dayKey ),
							$current_week_day === $dayKey ? ' rtclbh-active' : '',
							$options['highlight_open_period'] && $current_week_day === $dayKey && BHS::isOpen( $timePeriod['start'], $timePeriod['end'] ) ? ' rtclbh-opened' : ''
						),
						'data-count="' . absint( $periodIndex ) . '"',
						$periodIndex == 0 ? esc_attr( $day ) : '&nbsp;',
						esc_html( Utility::formatTime( $timePeriod['start'], null, 'H:i' ) ),
						esc_attr( $options['open_close_separator'] ),
						esc_html( Utility::formatTime( $timePeriod['end'], null, 'H:i' ) )
					);

				}

			}

		}

		?>

		</tbody>
	</table>

<?php

echo '</div>';
