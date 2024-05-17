<?
	require('../global.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
	if ($sort == 'date') {
		$sort_display = '[<a href="?sort=rank">Rank</a>] [Date]';
		$order_by = 'pubDate';
	} else {
		$sort_display = '[Rank] [<a href="?sort=date">Date</a>]';
		$order_by = 'rank DESC, pubDate';
	}

	$start = isset($_GET['start']) ? $_GET['start'] : 0;
	$count = isset($_GET['count']) ? $_GET['count'] : 10;
	$terms = isset($_GET['terms']) ? $_GET['terms'] : '';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - News Archive</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
		include(BASE_DIR .'/includes/lib_news.php');
	?>

	<h1>HDTV News Archive</h1>

	<table class="bare" width="100%"><tr>
		<td style="vertical-align:top; padding-right:10px; text-align:center;">
			<form name="frmSearchArchive" method="get" action="<?=PHP_SELF?>">
				<input type="hidden" name="action" value="search">
				<input type="hidden" name="count" value="<?=@$count?>">
				<input type="text" name="terms" class="inputText" value="<?=@$terms?>">
				<input type="submit" class="inputButton" value="Search Archive">
			</form><br />

			<table class="type1b" cellpadding="0" cellspacing="0" align="center">
				<tr><td class="type1b_header">&nbsp;</td>
				<?
					for ($x=1; $x<=12; $x++) echo '<td class="type1b_header" style="text-align:center">'. date('M', mktime(0, 0, 0, $x)) .'</td>';
					echo '</tr>';

					$result = mQuery("SELECT FROM_UNIXTIME(pubdate, '%Y') year, COUNT(*) FROM hdtv_rss WHERE rank >= 0 GROUP BY year ORDER BY year DESC");
					while ($row = mysql_fetch_assoc($result)) $item_count[$row['year']] = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

					$result = mQuery("SELECT FROM_UNIXTIME(pubdate, '%Y') year, FROM_UNIXTIME(pubdate, '%m') month, COUNT(*) count FROM hdtv_rss WHERE rank >= 0 GROUP BY year, month ORDER BY year DESC, month");
					while ($row = mysql_fetch_assoc($result)) $item_count[$row['year']][$row['month']-1] = $row['count'];

					foreach ($item_count as $year => $months) {
						echo '<tr><td class="type1b_header" style="text-align:center">'. $year .'</td>';
						foreach ($months as $month => $news_count) {
							if ($news_count == 0) {
								echo '<td class="grid">&nbsp;</td>';
							} else {
								echo '<td class="grid" style="text-align:center"><a href="?y='. $year .'&m='. ($month+1) .'">'. $news_count .'</a></td>';
							}
						}
						echo '</tr>';
					}
				?>
			</table>
		</td><td style="width:300px;">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
		</td>
	</tr></table><br />

	<table class="bare" width="100%"><tr>
		<td style="vertical-align:top; padding-right:10px"><?
			$show_table = false;
			if ($action == 'search') {
				$search_fields = 'title, description';
/*
				$qry = "SELECT title, link, pubDate, source, description, rank, MATCH $search_fields AGAINST ('$terms') as search_rank
				FROM hdtv_rss
				WHERE rank > 0
				ORDER BY search_rank DESC LIMIT $start,$count";
*/
				$qry = "
				SELECT title, link, pubDate, source, description, rank, MATCH $search_fields AGAINST ('$terms') as search_rank
				FROM mt_category c, hdtv_rss r
				LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
				WHERE rank > 0
					AND r.category_id = c.category_id
				ORDER BY search_rank DESC LIMIT $start,$count";
				$search_result = mQuery($qry);
				$total = mysql_num_rows($search_result);
				$show_table = true;
			} elseif ($_GET['y'] != '' && $_GET['m'] != '') {
				$qry = "
				SELECT id, title, link, pubDate, source, description, rank
				FROM hdtv_rss
				WHERE rank > 0
					AND FROM_UNIXTIME(pubdate, '%Y') = $_GET[y]
					AND FROM_UNIXTIME(pubdate, '%m') = $_GET[m]
					ORDER BY pubdate DESC";

				// Get Total Count
				$result = mQuery($qry);
				$total = mysql_num_rows($result);
				$qry .= " LIMIT $start,$count";
				$search_result = mQuery($qry);
				$show_table = true;
				$qurl = 'y='. $_GET['y'] .'&m='. ($_GET['m']);
			}
			if ($show_table) {
				$y = 1;
				if (($start-$count) >=0) $nav .= '<a href="?start='. ($start-$count) .'&count='. $count .'&'. $qurl .'">&lt; Prev</a>&nbsp;&nbsp;&nbsp;';
				for ($x=0; $x<$total; $x+=$count) {
					if ($x == $start) {
						$nav .= $y++ .'&nbsp';
					} else {
						$nav .= '<a href="?start='. $x .'&count='. $count .'&'. $qurl .'">'. $y++ .'</a>&nbsp;';
					}
				}
				if (($start+$count) < $total) $nav .= '&nbsp;&nbsp;<a href="?start='. ($start+$count) .'&count='. $count .'&'. $qurl .'">Next &gt;</a>';
				?>
				<div class="item"><span class="corners-top"><span></span></span>
					<span class="label" style="font-size:12pt">Search Results: </span>
					<span style="float:right; margin-top:3px">
						Page: <?=$nav?>
					</span>
				<span class="corners-bottom"><span></span></span></div>

				<?
					while ($row = mysql_fetch_assoc($search_result)) {
/*
						$rank_text = ($row['rank'] == 0) ? 'unranked' : $row['rank'] .' out of 5';
						echo '<div class="shade_border" style="width:600px; text-align:left">'.
						'	<img src="/images/bluestars_'. $row['rank'] .'.0.gif" alt="'. $rank_text .'" align="right" />'.
						'	<a target="_blank" href="'. $row['link'] .'">'. $row['title'] .'</a><br />'.
							$row['source'] .' - '. gmdate('M j, Y g:ia', $row['pubDate'] + $user->data['time_zone_offset']) .'<br />'.
							strip_tags($row['description']) .
						'</div><br />';
*/
/*
						$sql = "
						SELECT DISTINCT id, pubDate, link, title, description, source, r.category_id, category_label, r.topic_id, topic_replies
						FROM mt_category c, hdtv_rss r
						LEFT JOIN phpbb_topics t ON r.topic_id = t.topic_id
						WHERE rank > 0
							AND r.category_id = c.category_id
						ORDER BY pubDate DESC LIMIT 20";
						$result = $db->sql_query($sql);
*/
						$x = 0;
						while ($row = mysql_fetch_assoc($search_result)) {
							$x++;
							$entry['id'] = $row['id'];
							$entry['date'] = getDateString($row['pubDate']);
							$entry['link'] = 'story.php?title='. dirify($row['title']) .'&amp;id='. $row['id'];
							$entry['title'] = $row['title'];
							$entry['author'] = $row['source'];
							$entry['excerpt'] = strip_tags($row['description']);
	#						$entry[entry_type] = 'Story';
							$entry['topic_replies'] = $row['topic_replies'];
							$entry['topic_id'] = $row['topic_id'];

							$entry['catlinks'] = array();
							$entry['catlinks'][] = '<a href="category.php?id='. $row['category_id'] .'&category='. urlencode(stripslashes($row['category_label'])) .'">'. stripslashes($row['category_label']) .'</a>';

							echo getFormattedStory($entry, 'excerpt');
						}
					}
				?>
			<?}?>
		</td><td class="ad-right" style="vertical-align:top">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
