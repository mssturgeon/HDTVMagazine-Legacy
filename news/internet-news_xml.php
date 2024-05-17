<?
	require('../global.php');
	header('Content-type: application/xml');

	$base_url = 'http://'. SERVER_NAME;

	$qry = "SELECT * FROM hdtv_rss WHERE rank > 0 ORDER BY pubDate DESC LIMIT 10";
	$result = mQuery($qry);
	$row = mysql_fetch_assoc($result);
	$date = gmdate('r', $row['pubDate']);
	mysql_data_seek($result, 0);

	echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?=($base_url . PHP_SELF)?>" rel="self" type="application/rss+xml" />
		<title>HDTV Magazine - Internet HDTV News</title>
		<link><?=$base_url?>/news/</link>
		<description></description>
		<language>en</language>
		<copyright>Copyright <?=date('Y')?></copyright>
		<lastBuildDate><?=$date?></lastBuildDate>
		<generator>HDTV Magazine</generator>
		<managingEditor>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</managingEditor>
		<webMaster>feedback@hdtvmagazine.com (HDTV Magazine Feedback)</webMaster>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<?
	while ($row = mysql_fetch_assoc($result)) {
		$dirified_title = dirify($row['title']);
		$category = ($row['category_id'] == '') ? 'General Interest' : $row['category_id'];
		$link = $base_url .'/news/story.php?title='. $dirified_title .'&amp;id='. $row['id'];

		echo '<item>'."\n".
		'	<title>'. htmlspecialchars($row['title'], ENT_COMPAT, 'UTF-8') .'</title>'."\n".
		'	<description><!-- ckey="45C01D04" -->'."\n".
				htmlspecialchars(strip_tags($row['description']), ENT_COMPAT, 'UTF-8') ."\n".
		'	</description>'."\n".
		'	<link>'. $link .'</link>'."\n".
		'	<guid>'. $link .'</guid>'."\n".
		'	<category>'. htmlspecialchars($category, ENT_COMPAT, 'UTF-8') .'</category>'."\n".
		'	<pubDate>'. gmdate('r', $row['pubDate']) .'</pubDate>'."\n".
		'	<source url="http://www.hdtvmagazine.com/news/">'. htmlspecialchars($row['source'], ENT_COMPAT, 'UTF-8') .'</source>'."\n".
		'</item>'."\n";
	}
?>
	</channel>
</rss>
