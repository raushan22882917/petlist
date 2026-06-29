<?php

namespace Rtcl\Controllers\Ajax;

class Ajax {
	public function __construct() {
		new ListingAdminAjax();
		new AjaxGallery();
		Checkout::getInstance();
		new AjaxCFG();
		new PublicUser();
		new ListingAnalyticsAjax();
		new Import();
		new ImportSources();
		new ImportGoogle();
		new ImportMapping();
		new ImportHistoryAjax();
		new Export();
		new AjaxListingType();
		InlineSearchAjax::init();
		FilterAjax::init();
		FormBuilderAjax::getInstance()->init();
		FormBuilderAdminAjax::getInstance()->init();
		FilterFormAdminAjax::getInstance()->init();
		AjaxSettings::init();
	}
}