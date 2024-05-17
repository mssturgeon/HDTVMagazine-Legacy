<?
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');

	### Loop through new content ###
	$sql = "
	SELECT e.entry_id, entry_blog_id, entry_title, entry_excerpt, entry_created_on, entry_author_id, author_name, user_id
	FROM mt_entry e, mt_author auth, aux_author aa
	WHERE e.entry_author_id = auth.author_id
		AND e.entry_author_id = aa.author_id
		AND e.entry_id = 3679
		AND entry_status = 2
		AND entry_excerpt <> ''";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
  	$entry = getEntryInfo($row['entry_blog_id']);
		$ts = strtotime($row['entry_created_on']);
		$y = date('Y', $ts);
		$m = date('m', $ts);
		$link = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";

		# Get image from link
#		$contents = @file_get_contents($site['url']);
#		$xml = new SimpleXMLElement( $contents );

		$dom = new DOMDocument();
		$dom->loadHTML("<html><body>{$row['entry_excerpt']}</body></html>");
		$imgs = $dom->getElementsByTagName('img');
		if ($imgs->length > 0) {
			$image_src = $imgs->item(0)->getAttribute('src');
			$sql_img_update = "UPDATE aux_mt_entry SET image_src = '$image_src' WHERE entry_id = '{$row['entry_id']}'";
			if ($debug) {echo "$sql_img_update\n";} else {mQuery($sql_img_update);}
		} else {
			$image_src = '';
		}
	}
?>