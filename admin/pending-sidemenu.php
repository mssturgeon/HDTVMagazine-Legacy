<?
	$sql = "	SELECT id FROM hdtv_rss WHERE rank = 0 OR (rank > 0 AND category_id = 0)";
	$res_news = $db->sql_query($sql);
	$news_pending = mysql_num_rows($res_news);
?>
<table class="box1">
	<tr><td nowrap><a href="/admin/news.php">News (<?=$news_pending?>)</a></td></tr>
	<tr><td nowrap><a href="/admin/news.php">Reviews (<?=$reviews_pending?>)</a></td></tr>
	<tr><td nowrap><a href="/admin/news.php">Products (<?=$products_pending?>)</a></td></tr>
	<tr><td><hr /></td></tr>
	<tr><td nowrap><a href="/admin/feeds.php">Manage Feeds</a></td></tr>
</table>
