			<div style="float:left">
				<h2>Broadcast Markets/Stations</h2>
				<table class="type1b" cellpadding="0" cellspacing="0">
					<tr>
						<td class="type1b_header" style="text-align:left">Market</td>
						<td class="type1b_header" style="text-align:right">DMA Rank</td>
						<td class="type1b_header">Channels</td>
					</tr><?
						$x = 0;
						$sql = "
						SELECT dma_name, dma_rank
						FROM prog_source
						WHERE dma_name <> ''
							AND affiliation_1 LIKE '$network%'
						ORDER BY dma_name";
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result)) {
							$dma_name = $row['dma_name'];

							$dma_rank[$dma_name] = $row['dma_rank'];
							$dma_array[$dma_name] += 1;
							$net[$dma_name] += 1;
						}

						foreach($dma_array as $dma_name => $total) {
							echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">'.
							'	<td class="grid" style="text-align:left"><a href="broadcast-market.php?dma_name[]='. urlencode($dma_name) .'">'. $dma_name .'</a></td>'.
							'	<td class="grid" style="text-align:right">'. $dma_rank[$dma_name] .'</td>'.
							'	<td class="grid" style="text-align:center">'. ($net[$dma_name] == 0 ? '-' : $net[$dma_name]) .'</td>'.
							'</tr>';
						}
					?>
				</table>
			</div>
		</td>
	</tr></table></div>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
