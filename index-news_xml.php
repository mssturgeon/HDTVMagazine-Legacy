<?
	require('global.php');
	header('Content-type: application/xml');

	$three_days_ago = time() - 3*DAYS;
	$qry = "
	SELECT DISTINCT UNIX_TIMESTAMP(entry_created_on) as entry_timestamp, entry_title, entry_keywords,
		blog_site_url
	FROM mt_entry e, mt_category c, mt_placement p, mt_blog b
	WHERE e.entry_id = p.placement_entry_id
		AND e.entry_blog_id = b.blog_id
		AND c.category_id = p.placement_category_id
		AND e.entry_status = 2
		AND b.blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
		AND UNIX_TIMESTAMP(entry_created_on) >= $three_days_ago
	ORDER BY entry_created_on DESC";
	$result = mQuery($qry);
	echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
	<url><?
		while ($row = mysql_fetch_assoc($result)) {
			$date = gmdate('D, d M Y H:i:s +0000', $row['entry_timestamp']);
			$y = date('Y', $row['entry_timestamp']);
			$m = date('m', $row['entry_timestamp']);
			$link = $row['blog_site_url'] ."/$y/$m/". dirify($row['entry_title']) .".php";
			$keywords = $row['entry_keywords'];

			echo '<loc>'. $link .'</loc>'."\n".
			'<news:news>'."\n".
			'	<news:publication_date>'. $date .'</news:publication_date>'."\n".
			'	<news:keywords>'. $keywords .'</news:keywords>'."\n".
			'</news:news>'."\n";
		}
	?></url>
</urlset>