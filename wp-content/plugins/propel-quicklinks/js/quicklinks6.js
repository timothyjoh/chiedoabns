jQuery(function() {
	jQuery('#propel-quicklinks').prependTo("#dashboard-widgets-wrap");
	var interval = -1;
	var fixWooLabel = function() {
		var woo = jQuery("h3.hndle.ui-sortable-handle span:contains('WooCommerce Status')");
		if ( woo.length > 0 ) {
			var txt = woo.text();
			woo.text( txt.replace("WooCommerce", "eCommerce") );
			clearInterval(interval);
			return true;
		}
		return false;
	}
	if ( !fixWooLabel() ) {
		interval = setInterval( fixWooLabel, 100 );
	}
});
