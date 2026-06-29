<?php
/**
 * @package Petslist/Templates
 * @version 1.0
 */

use RadiusTheme\Petslist\Options;
use RadiusTheme\Petslist\Helper;

$style = Options::$options['listing_archive_style'];

Helper::get_custom_listing_template( 'archive/grid/grid-'.$style );

?>
