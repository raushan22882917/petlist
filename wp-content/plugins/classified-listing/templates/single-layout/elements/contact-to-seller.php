<?php
/**
 *
 * @package ClassifiedListing/Templates
 * @version 5.2.0
 */
use Rtcl\Controllers\Hooks\TemplateHooks;

defined( 'ABSPATH' ) || exit;
global $listing;

//TemplateHooks::add_chat_link($listing);
TemplateHooks::seller_email($listing);