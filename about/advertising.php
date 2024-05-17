<?
	require('../global.php');
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Advertising With Us</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>Advertising with HDTV Magazine</h1>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="vertical-align:top;padding-right:10px;">
			<? require('sidemenu.php');?>
		</td><td style="vertical-align:top;">

			<div align="center"><div class="alertbox" style="text-align:left">
				<a href="https://advertisers.federatedmedia.net/explore/view/hdtv"><img src="http://static.fmpub.net/assets/badges/FM_Tech_Advertise_hz.gif" border="0" width="114" height="75" style="background-color:white; border:1px solid black; margin-right:10px" align="left"></a>
					We have entered into a partnership with Federated Media (FM) to handle the advertising for HDTV Magazine. FM represents some
					of the largest sites on the internet including Sphere, Ars Technica, TechCrunch and BoingBoing ... just to name a few. Please
					<a href="http://www.federatedmedia.net/authors/hdtv">contact Federated Media</a> for pricing and inventory options, and feel
					free to read on for our <a href="#traffic">recent traffic</a>, <a href="#demographics">audience</a>, and current
					<a href="#popularity">search engine popularity</a>.
				<br clear="all" />
			</div></div>

			<div align="center" class="primary_bold_14">The best address <span class="brown">anywhere</span> to advertise your High Definition products and services is HDTV Magazine.</div><br />

			<h2>Why Advertise with HDTV Magazine?</h2>
			<p>
				HDTV is soaring -- the greatest hit in consumer electronics history -- and HDTV Magazine is its voice. We are continually adding attractions based on the information
				and resources demanded by the consumers and our membership. A few of these attractions include:<br />
				<ul class="brownsquare">
					<li>The latest in <a href="<?=URL_NEWS?>">HDTV News</a></li>
					<li><a href="<?=URL_ARTICLES?>">HDTV Articles</a>, our original columns from the authorities who made HDTV happen.</li>
					<li>The <a href="/forum/">HDTV Magazine Forum &amp; HDLibrary</a>, an easy-to-use reference for both basic and detailed information on high definition technologies.</li>
					<li>The <a href="/hdstore/">HDTV Magazine Store</a>: Making Your HDTV Better.</li>
					<li>The Online <a href="<?=URL_GUIDE?>">DTV Program Guide</a> - Subscription-based, Grid-style Guide with 14-day look-ahead.</li>
					<li>And a deep historical perspective from <b><?=(date('Y') - 1984)?> years</b> of covering the story.</li>
				</ul>

			</p>

			<br />
			<a name="traffic"></a>
			<h2>* Our Audience</h2>
			<br />
			<table class="type1b" style="width:550px" cellpadding="0" cellspacing="0">
				<tr>
					<td class="type1b_header">Avg. HDTVs owned:</td>
					<td class="grid">1.5</td>
				</tr><tr>
					<td class="type1b_header">Early adopters:</td>
					<td class="grid">83%</td>
				</tr><tr>
					<td class="type1b_header">Avg. entertainment system investment:</td>
					<td class="grid">$13,000+</td>
				</tr><tr>
					<td class="type1b_header">Avg. annual movie/game purchases:</td>
					<td class="grid">$700+</td>
				</tr><tr>
					<td class="type1b_header">Male/Female:</td>
					<td class="grid">98% Male</td>
				</tr><tr>
					<td class="type1b_header">Key age groups:</td>
					<td class="grid">55 - 64: 30%, 45 - 54: 27%, over 65: 18%</td>
				</tr><tr>
					<td class="type1b_header">Education:</td>
					<td class="grid">68% College Graduate or above</td>
				</tr><tr>
					<td class="type1b_header">Occupations:</td>
					<td class="grid">43% Professional, 14% Managerial/Executive</td>
				</tr><tr>
					<td class="type1b_header">Avg. annual income</td>
					<td class="grid">$116,000+ (USD)</td>
				</tr>
			</table>
			<span style="font-size:8pt;">
				* Based on 1038 respondents, as of 27 August, 2006<br/>
				<!--** For the month of <?=date('F, Y', $last_month);?>-->
			</span><br />
			<br />

			<a name="popularity"></a>
			<h2>Keyword Search Popularity/Ranking</h2>
			<br />
			<div style="width:400px; float:left; padding:0 20px 20px 0">
				<table class="type1b" cellspacing="0">
					<tr>
						<td class="type1b_header">&nbsp;</td>
						<td class="type1b_header">Google</td>
						<td class="type1b_header">Yahoo!</td>
						<td class="type1b_header">MSN</td>
					</tr>
					<?
						// Collect entries and group by keywords
						$timestamp = 0;
						$qry = "
						SELECT keywords, ROUND(AVG(serp),1) avg
						FROM search_rank
						WHERE url = 'hdtvmagazine.com'
						GROUP BY keywords
						ORDER BY avg LIMIT 15";
						$sql = "SELECT keywords FROM search_rank WHERE url = 'hdtvmagazine.com' AND se = 'Google' ORDER BY serp LIMIT 15";
						$result = mQuery($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$sub_result = mQuery("SELECT * FROM search_rank WHERE keywords = '$row[keywords]'");
							while ($sub_row = mysql_fetch_assoc($sub_result)) {
								$row[$sub_row[se]] = ($sub_row[serp] <= 5) ? '<span class="primary_bold">'. $sub_row[serp] .'</span>' : $sub_row[serp];
								$row[$sub_row[se]] = ($row[$sub_row[se]] == 200) ? '> 200' : $row[$sub_row[se]];
								$timestamp = ($sub_row[timestamp] > $timestamp) ? $sub_row[timestamp] : $timestamp;
							}
							echo '<tr>'.
							'	<td class="type1b_header" nowrap>'. $row[keywords] .'</td>'.
							'	<td class="grid" style="text-align:right;">'. $row[Google] .'</td>'.
							'	<td class="grid" style="text-align:right;">'. $row[Yahoo] .'</td>'.
							'	<td class="grid" style="text-align:right;">'. $row[MSN] .'</td>'.
							'</tr>';
						}
					?>
				</table>
				<span style="font-size:8pt;">
					* - Courtesy of <a target="_blank" href="http://www.digitalpoint.com/tools/keywords/" onmouseover="return link_over(this)" onmouseout="return link_out(this)">Digital Point Keyword Tracker</a> (Last Updated: <?=date('M j, Y', $timestamp)?>)
				</span>

			</div>
			Our ranking for various popular search terms in each of these major search engines is shown in the table to the right, with "above the fold" (top 5) rankings
			<span class="primary_bold">highlighted</span>.<br clear="all">

			<br />
			For rates and further details, including pricing and inventory options, please <a href="http://www.federatedmedia.net/authors/hdtv">contact Federated Media</a>
			or <a href="<?=URL_HELP_FEEDBACK?>?category=advertising">send us an email</a> if you have any questions or need clarification.

		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
