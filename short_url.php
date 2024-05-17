<?
	require('global.php');

	$url_id = isset($_GET['url_id']) ? $_GET['url_id'] : '';
	$id = substr($url_id, 1);
	$url_type = substr($url_id, 0, 1);

	switch ($url_type) {
		case 'a':
			$sql = "SELECT entry_blog_id, entry_created_on, entry_title FROM mt_entry WHERE entry_id = '$id'";
			$result = mQuery($sql);
			if (mysql_num_rows($result) == 0) {
				$url = '/';
			} else {
				$row = mysql_fetch_assoc($result);
				$entry = getEntryInfo($row['entry_blog_id']);

				$ts = strtotime($row['entry_created_on']);
				$y = date('Y', $ts);
				$m = date('m', $ts);
				$entry = getEntryInfo($row['entry_blog_id']);
				$url = '/'. $entry['blog_dir'] ."/$y/$m/". dirify($row['entry_title']) .'.php';
			}
			break;
		case 'c':
			$sql = "SELECT url FROM url_shortened WHERE id = '$id'";
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			$url = $row['url'];
			break;
		default:
			$url = '/';
			break;
	}
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
?>