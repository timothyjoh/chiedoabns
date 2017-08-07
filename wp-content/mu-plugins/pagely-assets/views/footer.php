<script type='text/javascript'>
jQuery(document).ready(function($) {

<?php 
if ( isset($_GET['page']) && $_GET['page'] == 'w3tc_general' && !defined(ALLOW_W3TC) ) {
?>
    $('#w3tc.wrap div.postbox').each(function (index,el) {
        h3content = $(el).children('h3').html();
        //alert($(h3content).html());
        var regmatch = /Database Cache|Object Cache|Varnish|Proxy/i.test(h3content);
        if (regmatch) {
            $(el).children('h3').append(' <em class="hcs_w3tc_notice">- Powered by your Hosting system <a href="#" id="postbox'+index+'" class="hcs_show_postbox">Show me anyways</a></em>');
            $(el).children('div.inside').addClass('postbox'+index).hide();
            $(el).find('input.enabled').click( function() { $(this).attr("disabled", true).val(0); alert('This setting is not needed, please leave it turned off. Your hosting system provides this advanced feature on the system level for you.')});

        }

        // notice
        var pagematch = /Page Cache/i.test(h3content);
        if(pagematch) {
            $(el).children('h3').append(' <em class="hcs_w3tc_notice">- Preferred Setting is On > Memcached.</em>');
        }

        var browsermatch = /Browser/i.test(h3content);
        if(browsermatch) {
            $(el).children('h3').append(' <em class="hcs_w3tc_notice">- Preferred Settings are: On.</em>');
        }


    });

    $('.hcs_show_postbox').live( 'click', function() {
        var whichpb = $(this).attr('id');
        $('div.'+whichpb).slideToggle();
        return false;
    });



<?php }
if ( isset($_GET['page']) && ($_GET['page'] == 'w3tc_dbcache' || $_GET['page'] == 'w3tc_objectcache' ) ) { ?>
$('#w3tc form').hide();
$('#w3tc').append('<p class="hcs_notice"><em>Your sweet WordPress hosting service takes care of this for you.</em><p><p class="hcs_notice">So what! <a href="#" id="hcs_show">Show me this menu anyways</a></p>');
$('#hcs_show').click( function() {$('#w3tc form').fadeIn(); $('.hcs_notice').hide();});

<?php } echo '});</script>';
}

