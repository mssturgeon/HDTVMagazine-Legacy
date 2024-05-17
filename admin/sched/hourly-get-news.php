<?
	error_reporting(0);
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain;charset=utf-8');

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_rss.php');

	$old_error_handler = set_error_handler("userErrorHandler");

	$keyword = array('hdtv', 'high definition', 'high-definition', 'hd ', 'hd dvd', 'hd-dvd', 'bluray', 'blu-ray', 'blu ray', 'ps3', 'playstation3', 'playstation 3', 'xbox 360', '3d', '3-d');

	$sql = "SELECT id, xml_link, title FROM feeds WHERE type = 'N' AND status = 1";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($debug) echo "Processing $row[xml_link]\n";
		$x = 0;

#		echo "{$row['xml_link']}\n";
		$contents = @file_get_contents($row['xml_link']);
		if ($contents !== FALSE) {
#			$enc_contents = utf8_encode($contents);
#			$xml = new SimpleXMLElement( $enc_contents );
			$xml = new SimpleXMLElement( $contents );
			$articles = getArticleData($xml);

			foreach ($articles as $article) {
				$x++;
				$title = addslashes(htmlentities($article['title'], ENT_QUOTES, 'UTF-8'));
				if ($debug) echo "\t$x: Processing item: $title\n";

				if ($title != '') { # Don't publish stories with a blank title
					$res_dup = mQuery("SELECT id FROM hdtv_rss WHERE title = '$title' AND NOW() - INTERVAL 14 DAY < FROM_UNIXTIME(pubDate)");
					if (mysql_num_rows($res_dup) == 0) { # Don't publish duplicate stories
						$timestamp = ($article['timestamp'] == '') ? time() : $article['timestamp'];

#						$description = addslashes($article['description']);
#						$content = addslashes(strleft($article['content'], "\n<div class=\"feedflare\">"));
						$description = $article['description'];
						$content = $article['content'];

						# Remove added footer elements
						$kill_footers[] = '<a rel="nofollow" href="http://feeds.wordpress.com/1.0/gocomments/htguys.wordpress.com/';
						$kill_footers[] = '<div class=\"feedflare\">';
						$kill_footers[] = "<img width='1' height='1' src='http://rss.feedsportal.com";
						foreach ($kill_footers as $footer) {
							$description = strleft($description . $footer, $footer);
							$content = strleft($content . $footer, $footer);
						}
						if ($content == '') {
							$content = $description;
							$description = substr(strip_tags($description, '<p><br><a>'), 0, 250);
							$description = strleftback($description, ' ') .' [...]';
						}
						$description = addslashes($description);
						$content = addslashes($content);

						if ($debug) echo "\t\tChecking for keywords\n";
						$found = false;
						foreach ($keyword as $kw) {
							if ((stristr($title, $kw) | stristr($description, $kw)) && !$found) { # Insert Story
								$found = true;

								# The * processing is to remove the Yahoo auto-redirect
								$link = urldecode(trim(strrightback('*'. $article['link'], '*')));
								$source = $row['title'];

								# Need to compute the source
								switch (true) {
								case $article['source'] != '':
#									$source = $item[source];
									break;
								case $dc['publisher'] != '':
#									$source = $item['dc:publisher'];
									break;
								case $row['title'] == 'Yahoo! News':
#									$source = trim(strleftback(strrightback($title, '('), ')'));
									$title = trim(strleftback($title, '('));
									break;
								default:
#									$source = $row[title];
									break;
								}

  							$sql = "INSERT IGNORE INTO hdtv_rss (pubDate, link, title, description, full_description, source, guid, feed_id)
  							VALUES ($timestamp, '$link', '$title', '$description', '$full_description', '$source', '$article[guid]', $row[id])";
								if ($debug) {echo "$sql\n";} else {mQuery($sql);}
								if ($debug) echo "\t\tInserted: $link\n";

								# Update feeds
								$sql = "UPDATE feeds SET last_item = FROM_UNIXTIME($timestamp), num_failed = 0 WHERE id = $row[id]";
								if ($debug) {echo "$sql\n";} else {mQuery($sql);}
							} # if found
						} # foreach
						if ($debug && !$found) echo "\t\tNo keywords found: $title\n";
					} else {
						if ($debug) echo "\t\tDuplicate: $title\n";
					}
				} else {
					if ($debug) {echo "Blank title\n";print_r($item);}
				}
			} # foreach article
		} else { # Couldn't get contents
			echo date('Y/m/d H:i:s') .": Unable to process: $row[xml_link]\n";
			$sql = "UPDATE feeds SET num_failed = num_failed + 1 WHERE id = $row[id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
			$sql = "UPDATE feeds SET status = 0 WHERE num_failed > 10";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}

	// Auto-purge news items older than 14 days
	mQuery("UPDATE hdtv_rss SET rank = -1 WHERE rank = 0 AND pubDate < (UNIX_TIMESTAMP() - ". 14*DAYS .")");

	# Auto-delete purged news items older than 365 days
	mQuery("DELETE FROM hdtv_rss WHERE rank = -1 AND pubDate < (UNIX_TIMESTAMP() - ". 365*DAYS .")");
?>
