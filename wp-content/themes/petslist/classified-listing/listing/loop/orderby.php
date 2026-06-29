<?php
/**
 * Show options for ordering
 *
 * @version     1.5.5
 *
 * @var array  $catalog_orderby_options
 * @var string $orderby
 */

use Rtcl\Helpers\Functions;
use RadiusTheme\Petslist\Helper;
use RadiusTheme\Petslist\Options;

if (!defined('ABSPATH')) {
    exit;
}
if (empty($catalog_orderby_options)) {
    return;
}

if (Options::$options['listing_archive_filter_type'] == 'custom') { ?>
    <div class="listing-archive-custom-search-filter-wrap">
        <button class="advanced-btn" type="button"><?php esc_html_e( 'Filter', 'petslist' ); ?></button>
    </div>
    <div class="advanced-search-box" id="advanced-search">
        <?php Helper::get_custom_listing_template( 'listing-search-filter' ); ?>
    </div>
<?php } else { ?>
    <span class="sort-by-text"><?php esc_html_e( 'Sort by:', 'petslist' ); ?></span>
    <form class="rtcl-ordering" method="get">
        <select name="orderby" class="orderby" aria-label="<?php esc_attr_e('Listing order', 'petslist'); ?>">
            <?php foreach ($catalog_orderby_options as $id => $name) : ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected($orderby, $id); ?>><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="paged" value="1"/>
        <?php Functions::query_string_form_fields(null, ['orderby', 'submit', 'paged']); ?>
    </form>
<?php } ?>