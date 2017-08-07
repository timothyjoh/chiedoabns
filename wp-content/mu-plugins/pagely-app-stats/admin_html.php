<?php
$cdnHitRate = 0;
if ($cdn_summary->hit > 0)
    $cdnHitRate = round(($cdn_summary->cache_hit/$cdn_summary->hit * 100), 2);

function chart($name, $label, $data)
{
    $series = [$label];
    $x = ['x'];
    foreach($data as $key => $row)
    {
        //$x[] = date('M-d', $row[0]/1000);
        $x[] = date('Y-m-d', $row[0]/1000);
        $series[] = (float)$row[1];
    }
    $chartData = [
        'x' => 'x',
        'columns' => [$x, $series],
        'type' => 'bar',
    ];

    foreach($data as $key => $row)
    {
        $data[$key][0] = date('Y-m-d', $row[0]/1000);
    }
    $tableData = json_encode($data);
    $chartData = json_encode($chartData);

    echo "<div class='epoch category20c' id='$name' style='height: 300px'></div>
        <table width='100%' cellpadding='0' cellspacing='0' border='0' class='display' id='table$name'></table>
        <script>
        jQuery(document).ready(function() { 
            var {$name}Count = 0;
            var chart = c3.generate({
                bindto: '#$name',
                data: $chartData,
                axis: {
                    x: {
                        type: 'timeseries',
                        tick: {
                            fit: true,
                            format: '%b %e'
                        }
                    },
                },
                grid: {
                    y: {
                        show: true
                    },
                },
                zoom: {
                    enabled: true
                },
                color: {
					 	pattern: ['#5a92d5']
		  				}
            });

            jQuery('#table$name').dataTable( {
                    data: {$tableData},
                    'bFilter': false,
                    'columns': [
                        {'title': 'Date', 'type': 'date'},
                        {'title': '$label'},
                    ]
            });
        });
        </script>\n";
}
?>
<style>
.plugin-card {
}
</style>
<div class="wrap">

	<h2>Usage and Performance Stats</h2>
	<p>The plugin displays usage/traffic data for this Pagely hosted site.</p>
	
	<hr/>

	<div class="wp-list-table widefat plugin-install">
		<div id="the-list">
			
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Bandwidth</h4>
						</div>
							<div class="graph_outer">
									<?php chart('gbandwidth', 'Bandwidth', $app_stats_period['graph_bandwidth']);?>
							</div>
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
							<small><i class="fa fa-clock-o"></i> Last 30 days <i class="fa fa-file-o"></i> GB</small>
					</div>
				
				</div><!-- !.plugin-card -->
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Raw Requests</h4>
						</div>
						
						
				
						
							<div class="graph_outer">
									<?php chart('grequests', 'Requests', $app_stats_period['graph_requests']);?>
							</div>
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
							<small><i class="fa fa-clock-o"></i> Last 30 days <em class="pull-right">Cached+Non-Cached Requests not served by the CDN</em></small>
					</div>
				
				</div><!-- !.plugin-card -->

				
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Press<strong>CDN&trade;</strong> </h4>
						</div>
						

				
						
						<div class="graph_outer">
                                <?php chart('gcdn', 'CDN Bandwidth', $app_stats_period['graph_cdn']);?>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days <i class="fa fa-file-o"></i> GB</small>
                        <strong class="pull-right">Hit Rate: <?=$cdnHitRate; ?>%</strong>

				   </div>
				
				</div><!-- !.plugin-card -->
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Disk Usage</h4>
						</div>
						

				
						
						<div class="graph_outer">
                            <?php chart('gdisk', 'Disk Usage', $app_stats_period['graph_file']);?>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days <i class="fa fa-file-o"></i> GB</small>

				   </div>
				
				</div><!-- !.plugin-card -->
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Database Size</h4>
						</div>
						

				
						
						<div class="graph_outer">
                            <?php chart('gdb', 'Database Size', $app_stats_period['graph_db']);?>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days <i class="fa fa-file-o"></i> GB</small>

				   </div>
				
				</div><!-- !.plugin-card -->

				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Database Tables</h4>
						</div>
						

				
						
						<div class="graph_outer">
							<div class="sparkline" data-sparkline-type="bar">
                                <?php chart('gtables', 'Database Tables', $app_stats_period['graph_tables']);?>
							</div>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days</small>

				   </div>
				
				</div><!-- !.plugin-card -->
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>WordPress Users</h4>
						</div>
						

				
						
						<div class="graph_outer">
                            <?php chart('gwpusers', 'WP Users', $app_stats_period['graph_users']);?>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days <em class="pull-right">Unless you are enrolling subscribers/members, this should be flat.</em></small>

				   </div>
				
				</div><!-- !.plugin-card -->
				
				<div class="plugin-card">	
					<div class="plugin-card-top">
						
						<div class="name column-name">
							<h4>Comments</h4>
						</div>
						

				
						
						<div class="graph_outer">
                            <?php chart('gcomments', 'Comments', $app_stats_period['graph_comments']);?>
						</div>							
						
					</div><!-- plugin-card-top -->
					
					<div class="plugin-card-bottom">
						<small><i class="fa fa-clock-o"></i> Last 30 days <em class="pull-right">Includes Comments marked as Spam</em></small>

				   </div>
				
				</div><!-- !.plugin-card -->
		</div><!-- !#the-list -->
	</div><!-- !.wp-list-table -->
	
</div>
