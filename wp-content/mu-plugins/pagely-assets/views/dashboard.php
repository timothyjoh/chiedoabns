
<div class="wrap">
	<h2>Pagely&reg; App Management</h2>
	<p>Thank you for choosing <a href="https://pagely.com">Pagely</a> to manage &amp; and secure your WordPress. Use the page to view and manage a few key settings for your site.</p>
	
    <form action="<?= admin_url('admin.php?page=wp_pagely') ?>" method='post' class="inline-block">
		<?php wp_nonce_field( 'purge_all_cache' );?>
		<input id="submit" class="button button-primary" type="submit" value="Purge All Caches + CDN" name="purge_all_cache">
			<a href="https://support.pagely.com" class="button inline-block" target="_blank">Launch Support Desk</a>

	</form>
	<hr/>
	<div class="wp-list-table widefat plugin-install">
		<div id="the-list">
				<?php $bulletins =  pagely_get_bulletins(true); 
						if (!empty($bulletins)) { ?>
				<div class="plugin-card" style="min-height:220px;">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Pagely&reg; Hosting Status + Notices</h4>
						</div>
						
						
				
						<div class="desc column-description">
							
							<div class="activity-block">
								<ul>			
								<?php foreach ($bulletins as $b) { 	?>
									<li>
										<span style="color:#777777;float:left;margin-right:8px; min-width:150px"><?php echo date(get_option('date_format'),$b->date_added);?></span>
						  
										<?php echo $b->msg; ?>
										
									</li>
								<?php } ?>	
								</ul>
							</div>
								
						</div>
					</div><!-- plugin-card-top -->

				</div><!-- !.plugin-card -->
				<?php } ?>

<?php
foreach(['pagely-app-stats', 'pagely-cache-control', 'pagely-cdn', 'pagely-logs', 'pagely-staging', ] as $card)
{
    $file = __DIR__."/../../$card/card.php";
    if (file_exists($file))
        include $file;
}
?>
				<div class="plugin-card disabled">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Future Module</h4>
						</div>
						
				
						<div class="desc column-description">
							<p>Watch this space as we add and enable new modules.
							</p>
							
						</div>
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<a class="button button" href="" aria-label="">Action</a>	
					</div>
				
				</div><!-- !.plugin-card -->
				

			
			
		</div><!-- !#the-list -->
	</div><!-- !.wp-list-table -->
	
	<p class="credits">Pagely is the World's most scalable Managed WordPress Platform. <a href="https://support.pagely.com" target="_blank">Contact Support</a></p>
	<img class="pagely_logo" src="https://cdnassets.pagely.com/public/2014-logos/pagely-full-gray-180x62.png"/>
</div> <!-- !.wrap -->
