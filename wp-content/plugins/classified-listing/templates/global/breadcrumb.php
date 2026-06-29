<?php
/**
 * Listings breadcrumb
 *
 * @package     ClassifiedListing/Templates
 * @version     1.5.4
 *
 * @var string $wrap_before
 * @var string $before
 * @var string $after
 * @var string $delimiter
 * @var string $wrap_after
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!empty($breadcrumb)) {

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $wrap_before;

    foreach ($breadcrumb as $key => $crumb) {

	    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $before;

        if (!empty($crumb[1]) && sizeof($breadcrumb) !== $key + 1) {
            echo '<a href="' . esc_url($crumb[1]) . '">' . esc_html($crumb[0]) . '</a>';
        } else {
            echo esc_html($crumb[0]);
        }

	    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $after;

        if (sizeof($breadcrumb) !== $key + 1) {
	        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $delimiter;
        }
    }

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $wrap_after;

}
