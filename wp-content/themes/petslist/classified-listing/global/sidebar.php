<?php
/**
 * Sidebar
 *
 * @package     ClassifiedListing/Templates
 * @version     1.4.0
 */

use RadiusTheme\Petslist\Options;
use Rtcl\Helpers\Functions;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$sidebar_class = 'listing-sidebar';
if (is_author()) {
    $sidebar_class .= ' sidebar-widget-area';
}
?>
<div class="<?php echo esc_attr($sidebar_class); ?>">
    <?php
    if (Options::$sidebar && is_active_sidebar(Options::$sidebar)) {
        dynamic_sidebar(Options::$sidebar);
    } elseif ((Functions::is_listings() || Functions::is_listing_taxonomy()) && is_active_sidebar('rtcl-archive-sidebar')) {
        dynamic_sidebar('rtcl-archive-sidebar');
    } else if (Functions::is_listing() && is_active_sidebar('rtcl-single-sidebar')) {
        dynamic_sidebar('rtcl-single-sidebar');
    }
    ?>
</div>