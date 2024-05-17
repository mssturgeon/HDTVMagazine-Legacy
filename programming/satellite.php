<?
	require('../global.php');
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Satellite HDTV Programming</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<meta name="description" content="Satellite HDTV Programming">
	<meta name="keywords" content="hdtv,hd,high definition,satellite">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1>Satellite HDTV Programming</h1>

			<div style="background-color:#<?=BG_COLOR?>; float:right; padding:5px; width:300px;">
			<h2>Satellite News</h2>
			<?
				$sql = "
				SELECT title, link, pubDate, source, description, rank,
				MATCH title, description AGAINST ('satellite') as search_rank
				FROM hdtv_rss
				WHERE rank >= 0
					AND MATCH title, description AGAINST ('satellite')
				ORDER BY pubDate DESC LIMIT 20";
				$search_result = mQuery($sql);
				while ($row = mysql_fetch_assoc($search_result)) {
					$rating = ($row[rank] == 0) ? '(Unranked)' : '<img src="/images/bluestars_'. $row[rank] .'.0.gif" alt="'. $row[rank] .'" align="absmiddle">';
					echo '<b><a target="_blank" href="/cgi-bin/ntlinktrack.cgi?'. $row[link] .'">'. stripslashes($row[title]) .'</a>'.
					' ('. $row[source] .', '. gmdate('n/d/Y g:ia', $row[pubDate] + $user->data['time_zone_offset']) .')</b> '. strip_tags($row[description], '<br />') .'<br /><br />';
				}
			?>
			</div>

			<h2>Available HDTV Satellite Services</h2>
			<p>
				<div class="networkLogo"><a href="/programming/bell-expressvu.php"><img src="/images/logos/satellite-bell-expressvu_75x40.gif" alt="Bell ExpressVu" /></a></div>
				<div class="networkLogo"><a href="/programming/directv.php"><img src="/images/logos/directv_75x40.gif" alt="DirecTV" /></a></div>
				<div class="networkLogo"><a href="/programming/dish-network.php"><img src="/images/logos/dish-network_75x40.gif" alt="Dish Network" /></a></div>
				<div class="networkLogo"><a href="/programming/sky.php"><img src="/images/logos/satellite-sky_75x40.gif" alt="Sky" /></a></div>
				<div class="networkLogo"><a href="/programming/star-choice.php"><img src="/images/logos/satellite-star-choice_75x40.gif" alt="Star Choice" /></a></div>
				<br clear="left" />
			</p>

			<h2>Satellite Service Comparison</h2>
			Coming soon ...

		</td>
	</tr></table></div>
	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
