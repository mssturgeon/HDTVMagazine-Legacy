<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Broadcast HDTV Programming</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="Broadcast HDTV Programming">
	<meta name="keywords" content="hdtv,hd,high definition,broadcast">
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Broadcast HDTV Programming</h1>
	&raquo; <a href="/programming/index.php">Programming</a> &raquo; Broadcast

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<p>
		Below you will find all of the markets currently carrying Digital content, either as Standard or High Definition.
		Simply click on your market to get further detail about which broadcast stations and cable networks are available.
	</p>

	<table class="type1b" cellpadding="0" cellspacing="0">
		<tr>
			<td class="type1b_header" style="text-align:left">Market</td>
			<td class="type1b_header" style="text-align:right">DMA Rank</td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/abc_15.gif" alt="ABC"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/cbs_15.gif" alt="CBS"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/cw_15.gif" alt="CW"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/fox_15.gif" alt="FOX"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/ind_15.gif" alt="Independent"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/nbc_15.gif" alt="NBC"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/pbs_15.gif" alt="PBS"></td>
			<td class="type1b_header" style="width:20px;"><img src="/images/logos/uni_15.gif" alt="Univision"></td>
			<td class="type1b_header"><b>Total</b></td>
		</tr>
			<?
				$x = 0;
				$sql = "
				SELECT dma_name, dma_rank, affiliation_1
				FROM prog_source
				WHERE dma_name <> ''
				ORDER BY dma_name";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result)) {
					$dma_name = $row['dma_name'];
					$affil = strtolower(strleft($row['affiliation_1'] .' ', ' '));

					$dma_rank[$dma_name] = $row['dma_rank'];
					$dma_array[$dma_name] += 1;

					$abc[$dma_name] += ($affil == 'abc') ? 1 : 0;
					$cbs[$dma_name] += ($affil == 'cbs') ? 1 : 0;
					$cw[$dma_name] += ($affil == 'cw') ? 1 : 0;
					$fox[$dma_name] += ($affil == 'fox') ? 1 : 0;
#					$ind[$dma_name] += ($affil == 'independent') ? 1 : 0;
					$ind[$dma_name] += ($affil == 'ind') ? 1 : 0;
					$nbc[$dma_name] += ($affil == 'nbc') ? 1 : 0;
#					$paxtv[$dma_name] += ($affil == 'paxtv') ? 1 : 0;
					$pbs[$dma_name] += ($affil == 'pbs') ? 1 : 0;
#					$uni[$dma_name] += ($affil == 'univision') ? 1 : 0;
					$uni[$dma_name] += ($affil == 'uni') ? 1 : 0;
#					$upn[$dma_name] += ($affil == 'upn') ? 1 : 0;
#					$wb[$dma_name] += ($affil == 'wb') ? 1 : 0;
				}

				foreach($dma_array as $dma_name => $total) {
					echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">'.
					'	<td class="grid" style="text-align:left"><a href="broadcast-market.php?dma_name[]='. urlencode($dma_name) .'">'. $dma_name .'</a></td>'.
					'	<td class="grid" style="text-align:right">'. $dma_rank[$dma_name] .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($abc[$dma_name] == 0 ? '-' : $abc[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($cbs[$dma_name] == 0 ? '-' : $cbs[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($cw[$dma_name] == 0 ? '-' : $cw[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($fox[$dma_name] == 0 ? '-' : $fox[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($ind[$dma_name] == 0 ? '-' : $ind[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($nbc[$dma_name] == 0 ? '-' : $nbc[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($pbs[$dma_name] == 0 ? '-' : $pbs[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. ($uni[$dma_name] == 0 ? '-' : $uni[$dma_name]) .'</td>'.
					'	<td class="grid" style="text-align:center">'. $total .'</td>'.
					'</tr>';
				}
			?>
	</table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
