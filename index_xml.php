<?
	require('global.php');
	header('Content-type: application/xml');

	$base_url = 'http://'. SERVER_NAME;

	$qry = "
	SELECT DISTINCT UNIX_TIMESTAMP(entry_created_on) as entry_created_on, entry_title, entry_excerpt, blog_site_url, blog_name, topic_replies, aux.topic_id
	FROM mt_entry e, mt_category c, mt_placement p, mt_blog b, aux_mt_entry aux
	LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
	WHERE e.entry_id = p.placement_entry_id
		AND e.entry_blog_id = b.blog_id
		AND e.entry_id = aux.entry_id
		AND c.category_id = p.placement_category_id
		AND e.entry_status = 2
		AND b.blog_id IN (". INCLUDE_BLOGS_ALL .")
	ORDER BY entry_created_on DESC LIMIT 60";
	$result = mQuery($qry);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$date = gmdate('r', $row['entry_created_on']);
		$y = date('Y', $row['entry_created_on']);
		$m = date('m', $row['entry_created_on']);
		mysql_data_seek($result, 0);
	}

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?=($base_url . PHP_SELF)?>" rel="self" type="application/rss+xml" />
		<title>HDTV Magazine</title>
		<link><?=$base_url?>/</link>
		<description>This is the main content feed for HDTV Magazine.
		It is a combination of our subfeeds for Articles, Columns, Podcasts, Bulletins, Reviews, Interviews, &amp; History.</description>
		<language>en</language>
		<copyright>Copyright <?=date('Y')?></copyright>
		<lastBuildDate><?=$date?></lastBuildDate>
		<generator>HDTV Magazine (cc5961d69d27483f84be7ae59118ecfd)</generator>
		<managingEditor>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</managingEditor>
		<webMaster>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</webMaster>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<?
	while ($row = mysql_fetch_assoc($result)) {
#		$date = gmdate('r', $row[entry_created_on]);
		$date = gmdate('D, d M Y H:i:s +0000', $row[entry_created_on]);
		$y = date('Y', $row['entry_created_on']);
		$m = date('m', $row['entry_created_on']);
		$link = $row['blog_site_url'] ."/$y/$m/". dirify($row['entry_title']) .".php";
		$title = preg_replace('!&[^;\s]+;!','', strip_tags($row['entry_title']));

		# Get # of Comments

		echo '<item>'."\n".
		'	<title>'. htmlspecialchars($title, ENT_COMPAT, 'UTF-8') .'</title>'."\n".
		'	<description>'."\n".
				htmlspecialchars(strip_tags(substr(strip_tags(str_replace(array('[', ']'), array('<', '>'), $row['entry_excerpt'])), 0, 500)), ENT_COMPAT, 'UTF-8') ."...\n".
		'	</description>'."\n".
		'	<slash:comments xmlns:slash="http://purl.org/rss/1.0/modules/slash/">'. $row['topic_replies'] .'</slash:comments>'."\n".
		'	<comments>'. $base_url .'/forum/viewtopic.php?t='. $row['topic_id'] .'</comments>'."\n".
		'	<link>'. htmlspecialchars($link, ENT_COMPAT, 'UTF-8') .'</link>'."\n".
		'	<guid>'. htmlspecialchars($link, ENT_COMPAT, 'UTF-8') .'</guid>'."\n".
		'	<pubDate>'. $date .'</pubDate>'."\n".
		'	<source url="'. $row['blog_site_url'] .'">'. $row['blog_name'] .'</source>'."\n".
		'</item>'."\n";
	}
?>
	</channel>
</rss>
