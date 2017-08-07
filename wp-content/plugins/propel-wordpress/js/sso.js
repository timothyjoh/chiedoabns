jQuery( document ).ready( function() {
   jQuery("#sso_enabled_cb").on("click", function() {
      if ( jQuery(this).is(':checked') ) {
         jQuery(".sso-setting").removeAttr("disabled");
      } else {
         jQuery(".sso-setting").attr("disabled","disabled");
      }
   });
   
})