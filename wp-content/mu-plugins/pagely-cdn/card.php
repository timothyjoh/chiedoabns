<?php $cdn_config = Pagely_CDNConfig::instance(pagely_cdn_network_wide()); 
    //print_r($cdn_config);
    //echo $cdn_config->cdn_url;
    //echo $cdn_config->enabled;
?>

<div class="plugin-card <?php echo $cdn_config->enabled == 0 ? 'config_warning' : '';?>">	
    <div class="plugin-card-top">
        
        <div class="name column-name">
            <h4>Press<strong>CDN</strong>&trade; <span><?php echo $cdn_config->enabled == 0 ? '<i class="fa fa-ban"></i> Inactive' : '<i class="fa fa-check-circle-o"></i> Active';?></span></h4>
        </div>
        

        <div class="desc column-description">
            <p>The Press<strong>CDN</strong> service is standard on all new Pagely plans and accelerates the content delivery of static assets: .css, .js, and images.</p>
            <span class="text-muted">
            HTTP CDN Endpoint: <?php echo $cdn_config->cdn_url == '' ? "Not Set" : $cdn_config->cdn_url;?><br>
            HTTPS CDN Endpoint: <?php echo $cdn_config->https_cdn_url == '' ? "Not Set" : $cdn_config->https_cdn_url;?><br>
            </span>
        </div>
    </div><!-- plugin-card-top -->
    
    <div class="plugin-card-bottom">
        <a class="button button-primary" href="<?= admin_url('admin.php?page=press_cdn'); ?>" aria-label="">Configure</a>
        <form action="<?= admin_url('admin.php?page=wp_pagely'); ?>" method='post' class="pull-right">
            <?php wp_nonce_field( 'purge_cdn' );?>
            <input id="submit" class="button" type="submit" value="Purge CDN" name="purge_cdn">		
        </form>	
    </div>

</div><!-- !.plugin-card -->

