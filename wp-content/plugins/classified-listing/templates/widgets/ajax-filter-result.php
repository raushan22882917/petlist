<?php
/**
 * @var array      $data
 * @var AjaxFilterResult $object
 */

use Rtcl\Helpers\Functions;
use Rtcl\Widgets\AjaxFilterResult;

?>
<div class="rtcl-ajax-filter-result-wrap rtcl-listings-wrapper">
	<?php
	Functions::get_template( 'listing/loop/actions' );
	?>
	<div <?php Functions::listing_loop_start_class() ?>></div>
</div>