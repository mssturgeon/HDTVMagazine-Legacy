<?require('../global.php');?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Program Guide Stations</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta name="description" content="HDTV Magazine Programming ">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">
	<meta name="rating" content="general">
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>

				<table class="table1" align="center">
					<tr><td class="table1Header">Stations Menu</td></tr>
						<?
							$qry = "SELECT * FROM menu WHERE name = 'Stations' ORDER BY priority, label";
							$result = mQuery($qry);
							while ($row = mysql_fetch_assoc($result)) {
								echo '<tr>'.
								'	<td><a target="'. $row['target'] .'" href="'. $row['href'] .'">'. $row['label'] .'</a></td>'.
								'</tr>';
							}
						?>
				</table>

		<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
