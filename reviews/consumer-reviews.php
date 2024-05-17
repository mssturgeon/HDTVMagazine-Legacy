<?
	require('../global.php');
	define('URL_REVIEW', '/reviews/review.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>HDTV Magazine Consumer Reviews</title>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
	?>
	
	<table class="bare" cellpadding="0" cellspacing="0" width="100%"><tr>
		<td id="ad-left">
			<div class="sidebar">
				<div id="authors">
					<h2>Reviewers</h2>
					<ul><?
						$qry = "
						SELECT author_id, author_name, COUNT(*) num
						FROM mt_author a, mt_entry e
						WHERE entry_blog_id = 8
							AND entry_status = 2
							AND entry_author_id = author_id
						GROUP BY author_id, author_name
						ORDER BY num DESC";
						$result = mQuery($qry);
						while ($author = mysql_fetch_assoc($result)) {
							echo '<li><a href="reviews-author.php?id='. $author[author_id] .'">'. $author[author_name] .'</a><span class="grey"> ('. $author[num] .')</span>';
						}
					?></ul>
				</div>

				<div id="categories">
					<h2>Categories</h2>
					<ul><?
						$qry = "
						SELECT category_id id, category_label label, COUNT(*) num
						FROM mt_entry e, mt_placement p, mt_category c
						WHERE entry_blog_id = 8
							AND entry_status = 2
							AND entry_id = p.placement_entry_id
							AND p.placement_category_id = c.category_id
						GROUP BY id, label
						ORDER BY num DESC";
						$result = mQuery($qry);
						while ($category = mysql_fetch_assoc($result)) {
							echo '<li><a href="reviews-category.php?id='. $category[id] .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span>';
						}
					?></ul>
				</div>
			</div><br clear="all" />
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="vertical-align:top;">
			<h1>HDTV Consumer Reviews</h1>
			<p>
				Welcome to the HDTV Magazine Consumer Review section. On the pages that follow, you will find various consumer reviews from a variety of sources.
				These sources are included to the right of each title below. Ranking and/or voting information is included where available.
			</p>
			<table class="bare" cellspacing="0" style="width:100%"><tr><td style="vertical-align:top">
			<!--div id="center"-->
				<div class="content">
					<h2>20 Most Recent Reviews</h2>
					<table cellpadding="3" cellspacing="0" width="100%"><?
						$sql = "
						SELECT r.*, a.ASIN, Title, Type, Manufacturer, Model
						FROM reviews r, az_attributes a, az_aux x
						WHERE r.review_product = a.ASIN
							AND a.ASIN = x.ASIN
						ORDER BY review_timestamp DESC LIMIT 20";
						$result = mQuery($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$rating = ($row[review_rating] == '') ? '(Unrated)' : '<img src="/images/stars5-'. $row[review_rating] .'.0.gif" alt="'. $row[review_rating] .'" align="absmiddle" style="padding:0" />';
							echo '<tr>'.
								'<td class="date">'. date('M j, Y', $row[review_timestamp]) .'</td>'.
								'<td>'. $MODEL_TYPE[$row[Type]] .'</td>'.
								'<td style="width:70px">'. $rating .'</td>'.
								'<td nowrap="nowrap">'. $row[Type] .'</td>'.
								'<td>'.
									'<a href="'. URL_REVIEW .'?type='. $MODEL_TYPE[$row[Type]] .'&amp;man='. $row[Manufacturer] .'&amp;model='. $row[Model] .'&amp;id='. $row[review_id] .'">'. $row[Title] .'</a>'.
									'<span class="grey"> ('. $row[review_source] .')</span>'.
								'</td>'.
							'</tr>';
						}
						?>
					</table>
					<div class="shade_border"><a href="http://www.hdtvmagazine.com/reviews/entries-all.php">Show All Reviews</a></div>
				</div>
		
				<br />
				<div class="content">
					<h2>Most Recent Reviews by Source</h2>
					<table cellpadding="1" cellspacing="0"><?
						$sql = "
						SELECT DISTINCT author_id, author_name
						FROM mt_author a, mt_entry e
						WHERE entry_blog_id = 8
							AND entry_status = 2
							AND entry_author_id = a.author_id
							AND e.entry_created_on > CURDATE() - INTERVAL 1 YEAR
						ORDER BY e.entry_created_on DESC";
						$author_result = mQuery($sql);
						while ($author = mysql_fetch_assoc($author_result)) {
							$sql = "
							SELECT entry_created_on, entry_title, entry_basename
							FROM mt_entry
							WHERE entry_blog_id = 8
								AND entry_status = 2
								AND entry_author_id = {$author[author_id]}
							ORDER BY entry_created_on DESC LIMIT 3";
							$result = mQuery($sql);
							if (mysql_num_rows($result) > 0) {
								echo '<tr><td class="primary_bold" style="padding-top:2px;" colspan="2">'. $author[author_name] .'</td></tr>';
							}
							while ($entry = mysql_fetch_assoc($result)) {
								$ts = strtotime($entry[entry_created_on]);
								$y = date('Y', $ts);
								$m = date('m', $ts);
								echo '<tr>'.
								'	<td class="date">'. date('M j, g:ia', $ts) .'</td>'.
								'	<td><a href="'. $y .'/'. $m .'/'. $entry[entry_basename] .'.php">'. $entry[entry_title] .'</a></td>'.
								'</tr>';
							}
						}
					?></table>
				</div><br />
				
				<div class="content">
					<h2>Most Recent Reviews by Category</h2>
					<table cellpadding="1" cellspacing="0"><?
						$sql = "
						SELECT DISTINCT category_label label, placement_category_id cat_id
						FROM mt_entry e, mt_placement p, mt_category c
						WHERE entry_blog_id = 8
							AND entry_blog_id = category_blog_id
							AND entry_status = 2
							AND entry_id = p.placement_entry_id
							AND p.placement_category_id = c.category_id
						ORDER BY e.entry_created_on DESC";
						$res_category = mQuery($sql);
						while ($cat = mysql_fetch_assoc($res_category)) {
							$sql = "
							SELECT entry_created_on, entry_title, entry_basename
							FROM mt_entry e, mt_placement p
							WHERE entry_blog_id = 8
								AND entry_status = 2
								AND entry_id = p.placement_entry_id
								AND p.placement_category_id = $cat[cat_id]
							ORDER BY entry_created_on DESC LIMIT 3";
							$result = mQuery($sql);
							if (mysql_num_rows($result) > 0) {
								echo '<tr><td class="primary_bold" style="padding-top:2px;" colspan="2">'. $cat[label] .'</td></tr>';
							}
							while ($entry = mysql_fetch_assoc($result)) {
								$ts = strtotime($entry[entry_created_on]);
								$y = date('Y', $ts);
								$m = date('m', $ts);
								echo '<tr>'.
								'	<td class="date">'. date('M j, g:ia', $ts) .'</td>'.
								'	<td><a href="'. $y .'/'. $m .'/'. $entry[entry_basename] .'.php">'. $entry[entry_title] .'</a></td>'.
								'</tr>';
							}
						}
					?></table>
				</div>
			</td></tr></table>
		</td>
	</tr></table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
