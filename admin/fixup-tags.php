<?
	header('Content-Type: text/plain');
 
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$sql = "SELECT id, full_description FROM hdtv_rss WHERE full_description LIKE '%&gt;%'";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$description = stripslashes($row[full_description]);
#		echo "$description\n\n";
		$description = html_entity_decode($description);
#		echo "$description\n\n";
		$description = strip_tags($description);
#		echo "$description\n\n";
		$description = addslashes($description);
		
		$sql = "UPDATE hdtv_rss SET full_description = '$description' WHERE id = $row[id]";
		$db->sql_query($sql);
#		echo "$sql\n";
#		exit;
	}
?>
