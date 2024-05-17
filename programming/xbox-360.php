<?
	require('../global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Xbox 360 Reference</title>
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
			<h1>Xbox 360 Reference</h1>
			<p>
				Welcome to the HDTV Magazine Xbox 360 reference page. On this page you will find a wealth of information about the Xbox 360.<br />
  				<br clear="all" />Coming soon:<ul>
  					<li><a href="#technology">General Xbox 360 Overview</a></li>
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
					SELECT detailpageurl, title, smallimageurl, lowestnewprice, lowestusedprice, averagerating, releasedate
					FROM az_main m, az_attributes a
					WHERE m.asin = a.asin
						AND a.binding = 'Video Game'
						AND a.platform = 'Xbox 360'
						AND smallimageurl <> ''
					ORDER BY title";
					$result = mQuery($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$rating = ($row[averagerating] == '') ? '' : '<img src="/images/stars_'. $row[averagerating] .'.gif" alt="'. $row[averagerating] .'" align="absmiddle">';
						$used = ($row[lowestusedprice] > 0) ? '	Used: $'. number_format($row[lowestusedprice]/100, 2) .'<br />' : '';
						$release_date = (strtotime($row[releasedate]) > time()) ? 'Release Date: '. $row[releasedate] .'<br />' : '';
						echo '<div class="block_small">'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'"><img src="'. $row[smallimageurl] .'" alt="'. $row[title] .'" align="left" /></a>'.
						'	<a target="_blank" href="'. $row[detailpageurl] .'">'. $row[title] .'</a> - '. $rating .'<br />'.
						'	New: $'. number_format($row[lowestnewprice]/100, 2) .'<br />'.
						$used .
						$release_date .
						'</div>';
					}
				?>
			<br clear="all" /><br />
			
		</td>
	</tr></table></div>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
