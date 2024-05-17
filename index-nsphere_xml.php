<?
	$key = $_GET['key'];
	if ($key != '4SvdMj9m') exit; // Must specify key to access

	require('global.php');
	header('Content-type: application/xml');

	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$sql_limit = ($limit == 0) ? "" : "LIMIT ". $limit;

	$base_url = 'http://'. SERVER_NAME;

	$copyright = "Copyright 2005 - ". date('Y') ." HDTV Magazine, Ltd.";

	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<data>
	<copyright><?=$copyright?></copyright>
	<?
		$sql = "
		SELECT DISTINCT UNIX_TIMESTAMP(entry_created_on) as entry_created_on, entry_blog_id, e.entry_id, entry_title, entry_text, entry_excerpt, author_name
		FROM mt_entry e, mt_category c, mt_placement p, mt_blog b, aux_mt_entry aux, mt_author a
		WHERE e.entry_id = p.placement_entry_id
			AND e.entry_author_id = a.author_id
			AND e.entry_blog_id = b.blog_id
			AND e.entry_id = aux.entry_id
			AND c.category_id = p.placement_category_id
			AND e.entry_status = 2
			AND b.blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
		ORDER BY entry_created_on DESC $sql_limit";
//		echo "$sql\n";
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
//			$ts = strtotime($row['entry_created_on']);
			$ts = $row['entry_created_on'];
			$y = date('Y', $ts);
			$m = date('m', $ts);
			$entry = getEntryInfo($row['entry_blog_id']);

			$entry_id = $row['entry_id'];
			$entry_date = getDateString($ts);
			$entry_link = "$base_url/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";
			$entry_title = htmlspecialchars($row['entry_title'], ENT_COMPAT, 'UTF-8');
			$entry_author = $row['author_name'];
			$entry_excerpt = $row['entry_excerpt'];
			$entry_text = htmlspecialchars($row['entry_text'], ENT_COMPAT, 'iso-8859-1');
//			mb_convert_encoding($row['entry_text'], "LATIN1", "UTF-8");
//			$entry_text = $row['entry_text'];

echo <<<EOT
<article>
	<url>$entry_link</url>
	<id>$entry_id</id>
	<bodytext><![CDATA[
		$entry_text
	]]></bodytext>
	<lead>$entry_excerpt</lead>
	<title>$entry_title</title>
	<pubdate>$entry_date</pubdate>
	<authorname>$entry_author</authorname>
	<keywords>$entry_categories</keywords>
</article>
EOT;
		}
	?>
</data>