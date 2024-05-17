<?
	header('Cache-Control: no-store');
	require('../global.php');
	$today = date('Y-m-d');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV News Bulletins</title>
	<meta name="description" content="HDTV headlines and the stories behind those headlines.">
	<meta name="keywords" content="hdtv,news,bulletins,alerts,archive,hdtv news,hdtv information,news archive">
	<meta name="rating" content="general">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-news" />
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<?include(BASE_DIR .'/includes/body_header-4.php');?>

	<div id="body_container">
		<h1>HDTV News Bulletins</h1>
		<form name="frm" method="get" action="/news/category.php">
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td id="left">
				<div class="anchors">
	      		Related sections:
					<a href="#news">Other HDTV News</a> &bull;
					<a href="#archives">Bulletin Archives</a>
				</div>
				<?
					if ($user->data['subscriptions'] & SUB_BULLETINS) {} else {
						if ($user->data['is_registered']) {?>
							<div class="important"><span class="corners-top"><span></span></span>
								<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of HDTV Bulletins:</span>
								<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of HDTV News Bulletins via email as
								soon as they are published
							<span class="corners-bottom"><span></span></span></div>
						<?} else {?>
							<div class="important"><span class="corners-top"><span></span></span>
								<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of HDTV Bulletins:</span>
								<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of HDTV News Bulletins via email as soon as they are published.
							<span class="corners-bottom"><span></span></span></div>
						<? }
					}

					$qry = "
					SELECT DISTINCT entry_blog_id, e.entry_id, entry_created_on, entry_title, entry_excerpt, author_name, topic_replies, aux.topic_id
					FROM mt_entry e, mt_author a, aux_mt_entry aux, ". TOPICS_TABLE ." t
					WHERE entry_author_id = author_id
						AND e.entry_id = aux.entry_id
						AND aux.topic_id = t.topic_id
						AND e.entry_status = 2
						AND e.entry_blog_id = 7
					ORDER BY entry_created_on DESC LIMIT 20";
					$b_result = mQuery($qry);

					$x = 0;
					$format = 'excerpt';
					while ($row = mysql_fetch_assoc($b_result)) {
						$x++;
						$ts = strtotime($row[entry_created_on]);
						$y = date('Y', $ts);
						$m = date('m', $ts);
						$blog_dir = getBlogDir($row[entry_blog_id]);

						$entry[date] = getDateString($ts);
						$entry[link] = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
						$entry[title] = $row[entry_title];
						$entry[author] = $row[author_name];
						$entry[excerpt] = $row[entry_excerpt];
						$entry[entry_type] = 'Bulletin';
						$entry[topic_replies] = $row[topic_replies];
						$entry[topic_id] = $row[topic_id];

						# Get categories
						$sql = "
						SELECT category_label, category_id FROM mt_category c, mt_placement p
						WHERE $row[entry_id] = p.placement_entry_id
							AND c.category_id = p.placement_category_id
							AND p.placement_is_primary = 1";
						$res_categories = mQuery($sql);
						$entry[catlinks] = array();
						while ($row_category = mysql_fetch_assoc($res_categories)) {
							$entry[catlinks][] = '<a href="/'. $blog_dir .'/bulletins-category.php?id='. $row_category[category_id] .'&category='. urlencode(stripslashes($row_category[category_label])) .'">'. stripslashes($row_category[category_label]) .'</a>';
#							$entry[catlinks][] = stripslashes($row_category[category_label]);
						}

						if ($x == 9) {
							echo '<h2>More Articles &amp; Reviews ...</h2>';
							$format = 'title-only';
						}

						echo getFormattedEntry($entry, $format);
					}
				?>
			</td><td id="right">
		  		<? include(BASE_DIR .'/ads/mrectangle.php');?><br />
				<a name="news"></a>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2><a href="index.php">In Other HDTV News ...</a></h2>
					<ul class="brownsquare"><?
						$sql = "
						SELECT DISTINCT id, pubDate, link, title, description, source, rank, category_label
						FROM hdtv_rss r, mt_category c
						WHERE r.category_id = c.category_id
							AND rank > 0
						ORDER BY pubDate DESC LIMIT 20";
						$n_result = $db->sql_query($sql);

						$x = 1;
						while ($row = $db->sql_fetchrow($n_result)) {
							$ts = $row[pubDate];
							$date = getDateString($ts);
							echo '<li><a href="/news/story.php?title='. dirify($row[title]) .'&amp;id='. $row[id] .'">'. stripslashes($row[title]) .'</a> - <span class="grey">'. $row[source] .'</span> - '. $date .'</li>';
						}
					?></ul>
				<span class="corners-bottom"><span></span></span></div>
				<div align="center"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

				<a name="archives"></a>
   			<div class="item"><span class="corners-top"><span></span></span>
   				<h2>Bulletin Archives</h2>
   				<div style="float:right; width:50%">
   					<b>Previous Years</b>
   					<ul class="brownsquare" style="margin-top:0;"><?
   						$qry = "
   						SELECT YEAR(entry_created_on) y, COUNT(*) num
   						FROM mt_entry
   						WHERE entry_blog_id = 7
   							AND entry_status = 2
   							AND entry_created_on < '$today' - INTERVAL 1 YEAR
   						GROUP BY y ORDER BY entry_created_on DESC";
   						$result = mQuery($qry);
   						while ($row = mysql_fetch_assoc($result)) {
   							echo '<li><a href="bulletins-archive.php?year='. $row[y] .'">'. $row[y] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
   						}
   					?></ul>
   				</div>
   				<b>Last 12 Months</b><ul class="brownsquare" style="margin-top:0;"><?
   					$qry = "
   					SELECT YEAR(entry_created_on) y, DATE_FORMAT(entry_created_on, '%M') m, COUNT(*) num
   					FROM mt_entry
   					WHERE entry_blog_id = 7
   						AND entry_status = 2
   						AND entry_created_on > '$today' - INTERVAL 1 YEAR
   					GROUP BY y,m ORDER BY entry_created_on DESC";
   					$result = mQuery($qry);
   					while ($row = mysql_fetch_assoc($result)) {
   						echo '<li><a href="bulletins-archive.php?month='. $row[m] .'&year='. $row[y] .'">'. $row[m] .'</a><span class="grey"> ('. $row[num] .')</span></li>';
   					}
   				?></ul>
   			<span class="corners-bottom"><span></span></span></div>
			</td>
		</tr></table>
		</form>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
