<div class="wrap">

	<h2>Press<strong>CACHE</strong>&trade;</h2>
	<p>The plugin controls the caching of your WordPress site. Configure as needed.</p>

	
	<hr/>
	<p class="lead warning"><strong>Note:</strong> Page caching is automatically disabled for all logged in users to your site, no need to disable it just for this case.</p>

    <form action="<?= admin_url('admin.php?page=press_cache'); ?>" method='post'>
        <?php wp_nonce_field( 'purge_cache' );?>
        <input id="submit" class="button" type="submit" value="Purge page cache" name="purge_cache">		
    </form>
	<form method="post" action="">
		<table class="form-table">
			<tbody>
			
				<tr valign="top">
					<th scope="row">DEV Mode</th>
					<td>
						<input type="hidden" name="dev_mode" value="0" />
						<label for="enabled">
						<input type="checkbox" name="dev_mode" value="1" <?php if ($config->dev_mode) { echo 'checked="1"'; } ?> />  DEV Mode Enabled</label>
						<p class="description">DEV Mode drops a cookie in your browser session and all caching <i>(This includes caching of assets)</i> will be turned off <strong>just for your browser session</strong>. Dev mode also disables CDN rewriting. This is useful if you are working live on the site and want to see your changes instantly.</p>
					</td>
				</tr>
				
				
				<tr valign="top">
					<th scope="row">Disable All Caching</th>
					<td>
						
						<input type="hidden" name="all_caching_disabled" value="0" />
						<label for="enabled">
						<input type="checkbox" name="all_caching_disabled" value="1" <?php if ($config->all_caching_disabled) { echo 'checked="1"'; } ?> />  All Caching Disabled</label>
						<p class="description">Checking this ON will disable all page caching for your entire site <i>(This does not include caching of assets)</i>, for all visitors. Typically not recommended.</p>
						
					</td>
				</tr>
			
			</tbody>
		</table>
		<input type="hidden" name="action" value="update_pagely_cachecontrol" />
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		<?php wp_nonce_field('update_pagely_cachecontrol'); ?>
	</form>
	
</div>
