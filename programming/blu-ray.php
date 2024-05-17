<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Blu-ray Reference</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<div style="width:100%"><table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="ad-left">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1>Blu-ray Reference</h1>
			<p>
				Welcome to the HDTV Magazine Blu-ray reference page. On this page you will find a wealth of information about Blu-ray.<br />
  				<br clear="all" />Coming soon:<ul class="brownsquare">
  					<li><a href="#technology">General Blu-ray Overview</a></li>
  					<li><a href="#specifics">Format Specifics &amp; Comparisons</a></li>
  					<li><a href="#hardware">Hardware Available</a></li>
  					<li><a href="#titles">Titles Available</a></li>
  					<li><a href="#more">Additional Links &amp; Information</a></li>
  				</ul>
			</p>

			<a name="titles"></a>
			<h2>Titles Available</h2>
				<?
					$sql = "
					SELECT detailpageurl, title, smallimageurl, lowestnewprice, lowestusedprice, averagerating, runningtime, aspectratio
					FROM az_main m, az_attributes a
					WHERE m.asin = a.asin
						AND a.binding = 'Blu-ray'
						AND smallimageurl <> ''
					ORDER BY title";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$rating = ($row[averagerating] == '') ? '' : '<img src="/images/stars_'. $row[averagerating] .'.gif" alt="'. $row[averagerating] .'" align="absmiddle">';
						$used = ($row[lowestusedprice] > 0) ? '	Used: $'. number_format($row[lowestusedprice]/100, 2) .'<br />' : '';
						echo '<div class="block_small">'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'"><img src="'. $row[smallimageurl] .'" alt="'. $row[title] .'" align="left" style="padding-right:2px" /></a>'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'">'. str_replace(array(' [Blu-ray]', ' (Blu-ray)', ' (Blu-Ray)'), '', $row[title]) .'</a> - '. $rating .'<br />'.
						'	New: $'. number_format($row[lowestnewprice]/100, 2) .'<br />'.
						$used .
						$row[runningtime] .'m, '. $row[aspectratio] .'<br />'.
						'</div>';
					}
				?>
			<br clear="all" /><br />
			
		</td>
	</tr></table></div>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
