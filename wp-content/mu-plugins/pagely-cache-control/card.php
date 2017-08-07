<?php
$config = PagelyCacheControl::instance()->config();
?>
<div class="plugin-card <?php echo $config->all_caching_disabled == 1 ? 'config_warning' : '';?>">
    <div class="plugin-card-top">

        <div class="name column-name">
        <h4>Press<strong>CACHE</strong>&trade; <span><?php echo $config->all_caching_disabled == 1 ? '<i class="fa fa-ban"></i> Inactive' : '<i class="fa fa-check-circle-o"></i> Active'; ?></span></h4>
        </div>



        <div class="desc column-description">
        <p>The Press<strong>CACHE</strong> service manages various functions dealing with accelerating your site such as: <strong>Page Caching</strong> <?php /*<strong>Object Caching</strong>,*/ ?> and <strong>DEV Mode</strong>.</p>

            <?php if ($config->dev_mode) { ?><span class="txt-green"><i class="fa fa-check-circle-o"></i> Developer Mode Active</span><br><?php } ?>
            <?php if ($config->all_caching_disabled) { ?><span class="txt-red"><i class="fa fa-ban"></i> <strong>Warning:</strong> All Page Caching is OFF</span><?php } ?>
        </div>
    </div><!-- plugin-card-top -->

    <div class="plugin-card-bottom">
        <a class="button button-primary" href="<?= admin_url('admin.php?page=press_cache') ?>" aria-label="">Configure</a> 
        <form action="<?= admin_url('admin.php?page=wp_pagely'); ?>" method='post' class="pull-right" style="margin-left: 5px">
            <?php wp_nonce_field( 'purge_cache' );?>
            <input id="submit" class="button" type="submit" value="Purge Page Cache" name="purge_cache">		
        </form>	
    </div>

</div><!-- !.plugin-card -->

