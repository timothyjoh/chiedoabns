<?php
// Admin Managment Page

$test_path = parse_url(admin_url('/images/wordpress-logo.svg'), PHP_URL_PATH);
?>
<div class="wrap">
	<h2>PressCDN&trade; (The Pagely&reg; CDN) </h2>
	<p>This plugin uses content rewriting of the final output to update local asset links to point to a CDN</p>

	<div class="cdn_test">
		<p><strong>CDN Verification:</strong> If you are properly configured you will see two WordPress Logos below.</p>
		
		<div class="cdn_image">
			<h4>CDN Test</h4>
			<img id="test_img" src="<?php echo esc_html($config->cdn_url); echo $test_path; ?>" />
			<p>If this image is broken, check the <strong>CDN URL</strong></p>
		</div>
		<div class="cdn_image">
			<h4>HTTPS CDN Test</h4>
			<img id="test_img2" src="<?php echo esc_html($config->https_cdn_url); echo $test_path?>" />
			<p>If this image is broken, check the <strong>HTTPS CDN URL</strong></p>
		</div>
				
	</div>

	<hr/>
	<form method="post" action="">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row">Enable</th>
				<td>
					<input type="hidden" name="enabled" value="0" />
					<label for="enabled">
					<input type="checkbox" name="enabled" value="1" <?php if ($config->enabled) { echo 'checked="1"'; } ?> />  CDN Functionality Enabled
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cdn_url">CDN URL</label></th>
				<td>
					<input type="text" name="cdn_url" value="<?php echo esc_html($config->cdn_url); ?>" size="80" class="regular-text code" id="cdn_url" />
					<p class="description">The new URL to be used in place of <?php echo(esc_html(get_option('siteurl'))); ?> for rewriting. No trailing <code>/</code> please. E.g. <code><?php echo(esc_html($example_cdn_uri)); ?></code>.
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="https_cdn_url"><b>HTTPS</b> CDN URL</label></th>
				<td>
					<input type="text" name="https_cdn_url" value="<?php echo  esc_html($config->https_cdn_url); ?>" size="80" class="regular-text code" id="https_cdn_url" />
					<p class="description">The new URL to be used in place of <?php echo  esc_html(get_option('siteurl')); ?> for rewriting. No trailing <code>/</code> please. E.g. <code><?php echo  esc_html($example_https_cdn_uri); ?></code>.
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Rewrite Relative Urls</th>
				<td>
					<input type="hidden" name="rootrelative" value="0" />
					<label for="rootrelative">
						<input type="checkbox" name="rootrelative" <?php if ($config->rootrelative) { echo 'checked="1" '; } ?>value="1" />  Enable Relative URL Rewriting</label>
						<p class="description">Rewrite relative paths like<code><em>/</em>wp-content/xyz.png</code> <strong>Tip:</strong> You typically want this ON.
						</p>
					
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="include_dirs">Include Directories</label></th>
				<td>
					<textarea name="include_dirs" cols="60" rows="5"><?php echo  esc_html($config->display('include_dirs')); ?></textarea>
					<p class="description"><strong>Include</strong> assets in these directories. See <a href="#matching_rules">matching rules</a> for the format.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="exclude">Exclude Directories</label></th>
				<td>
					<textarea name="exclude" cols="60" rows="5"><?php echo  esc_html($config->display('exclude')); ?></textarea>
					<p class="description"><strong>Ignore</strong> assets in these directories. See <a href="#matching_rules">matching rules</a> for the format.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="exclude_ext">Exclude Extensions</label></th>
				<td>
					<textarea name="exclude_ext" cols="60" rows="5"><?php echo  esc_html($config->display('exclude_ext')); ?></textarea>
					<p class="description">Files ending in these extensions will be ignored when rewriting. A leading <code>.</code> is not needed for example <code>flv</code></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="exclude_override">Override Exclusions</label></th>
				<td>
					<textarea name="exclude_override" cols="60" rows="5"><?php echo  esc_html($config->display('exclude_override')); ?></textarea>
					<p class="description">Serve these by CDN even if they are excluded above. See <a href="#matching_rules">matching rules</a> for the format.</p>
				</td>
			</tr>

            <?php if ($wpapi_is_installed) { ?>
            <tr valign="top">
				<th scope="row">WP-API Json Api Support</label></th>
                </th>
                <td>Replacements in WP-API json results are matched using <a href="http://jmespath.org/">JMESPath</a>, you can use the tester on the website to verify your match rules.</td>
            </tr>
            <tr valign="top">
				<th scope="row"><label for="api_url_paths">Match rules for fields containing only urls</label></th>
				<td>
					<textarea name="api_url_paths" cols="60" rows="5"><?php echo  esc_html($config->display('api_url_paths')); ?></textarea>
					<p class="description">JMESPath match rules, 1 rule per line</p>
				</td>
			</tr>
            <tr>
				<th scope="row"><label for="api_url_paths">Match rules for fields containing html</label></th>
				<td>
					<textarea name="api_html_paths" cols="60" rows="5"><?php echo  esc_html($config->display('api_html_paths')); ?></textarea>
					<p class="description">JMESPath match rules, 1 rule per line</p>
				</td>
			</tr>
            <?php } ?>
		</tbody>
	</table>
	
	<input type="hidden" name="action" value="update_pagely_cdn" />
	<input type="hidden" name="zone_id" value="<?php echo $config->zone_id;?>" />
	
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	<?php wp_nonce_field('pagely_cdn_options'); ?>
	
	</form>

	<h3 id="matching_rules">Matching Rules</h3>
	<p>The default rule is "Contains". Example an exclude of <code>foo</code> will match <code>/test/foo/bar</code> or <code>/foo</code>.</p>
	<p>If you begin any rule with <code>/</code> it will be a prefix match.  For example <code>/foo</code> won't match <code>/test/foo/bar</code> but will only match <code>/foo</code></p>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#cdn_url').change(function() {
        $('#test_img').attr('src', $('#cdn_url').val()+"<?php echo $test_path; ?>");
		$('#test_img').removeClass('error');
	});
	$('#https_cdn_url').change(function() {
        $('#test_img2').attr('src', $('#https_cdn_url').val()+"<?php echo $test_path; ?>");
		$('#test_img2').removeClass('error');
	});
	$('img').error(function(){
        $(this).parent('.cdn_image').addClass('cdn_error');
	});

});
</script>
