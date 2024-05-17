<?
	$debug = isset($_GET['debug']);
	if ($debug) header('Content-Type: text/plain;charset=utf-8');

	define('BASE_DIR', '/var/www/html');
	require_once(BASE_DIR .'/includes/constants.php');
	require_once(BASE_DIR .'/includes/lib_common.php');
	require_once(BASE_DIR .'/includes/lib_mysql.php');
	require_once(BASE_DIR .'/includes/lib_rss.php');

	function getCategories($title, $description, $blog_id) {
		global $debug;
		$new_categories = array();
		$text = "$title\n$description";

		$sql = "SELECT MATCH(category_description) AGAINST ('$text') AS relevance, category_label, category_id
		FROM mt_category
		WHERE category_blog_id = '$blog_id'
			AND MATCH(category_description) AGAINST ('$text') > 3
		ORDER BY relevance DESC";
		$result = mQuery($sql);
/*
		if ($debug) {echo "TITLE: $title\n";}
		if ($debug) {echo "SCORE: {$row['category_label']} - {$row['relevance']}.\n";}
*/
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				if (!in_array($row['category_id'], $new_categories)) $new_categories[] = $row['category_id'];
			}
		} else { # No match found, assign default category
			$sql = "SELECT category_id, category_label FROM mt_category WHERE category_blog_id = '$blog_id' AND category_label = 'General Interest'";
			if ($debug) {echo "$sql\n";}
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			$new_categories[] = $row['category_id'];
		}

		return $new_categories;
	}

	$syn_sites = array();
	$syn_site = array();

	# HDTV Almanac
	$syn_site['url'] = 'http://hdtvprofessor.com/HDTVAlmanac/?feed=rss2';
	$syn_site['author_id'] = 29; # Alfred Poor
	$syn_site['publish_to'] = 10; # Columns
	$syn_site['title_prefix'] = 'HDTV Almanac - ';
	$syn_sites[] = $syn_site;

	# HDTV Expert
	$authorID['Pete Putman'] = 30;
	$authorID['Ken Werner'] = 32;
	$syn_site['url'] = 'http://www.hdtvexpert.com/?feed=rss2';
	$syn_site['author_id'] = 30; # Pete Putman
#	$syn_site['publish_to'] = 6; # Test
	$syn_site['publish_to'] = 10; # Columns
	$syn_site['title_prefix'] = 'HDTV Expert - ';
	$syn_sites[] = $syn_site;

	# HDTV Podcast
#	$syn_site['url'] = 'http://feeds.feedburner.com/hdtvpodcast';
#	$syn_site['url'] = 'http://www.htguys.com/podcasts/rss.xml';
	$syn_site['url'] = 'http://htguys.wordpress.com/feed/';
	$syn_site['author_id'] = 21; # The HT Guys
#	$syn_site['publish_to'] = 6; # Test
	$syn_site['publish_to'] = 9; # Podcasts
	$syn_site['title_prefix'] = 'HDTV and Home Theater Podcast - ';
	$syn_sites[] = $syn_site;

	foreach ($syn_sites as $site) {
#		$xml = new SimpleXMLElement( file_get_contents($site['url']) );
		if ($debug) {echo "{$site['url']}\n";}
		$contents = @file_get_contents($site['url']);
#		$enc_contents = utf8_encode($contents);
		$xml = new SimpleXMLElement( $contents );
		$articles = getArticleData($xml);

		foreach ($articles as $article) {
			# Check to see if already logged
			$sql = "SELECT guid FROM syndication_log WHERE guid = '{$article['guid']}' AND posted = 1";
			if ($debug) {echo "$sql\n";}
			$result = mQuery($sql);
			if (mysql_num_rows($result) == 0 || $debug) { # Not yet syndicated or in debug mode
				# Update as logged
				$sql = "REPLACE INTO syndication_log (guid, posted) VALUES ('{$article['guid']}', 1)";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}

				# Check blog, entry, log, objecttag, placement, session, template and log as "synched"
				$entry_created_on = date('Y-m-d H:i:s', $article['timestamp']);
				$title = $site['title_prefix'] . addslashes(htmlentities($article['title'], ENT_QUOTES, 'UTF-8'));
				$description = $article['description']; # Excerpt

				# If creator is specified, use it
				if ($debug) print_r($article);
				if (array_key_exists($article['creator'], $authorID)) $site['author_id'] = $authorID[$article['creator']];

#				$content = $article['content']; # Main Content
				$content = getContent($article);

				# Remove added footer elements
				$kill_footers[] = '<a rel="nofollow" href="http://feeds.wordpress.com/1.0/gocomments/htguys.wordpress.com/';
				$kill_footers[] = '<img alt="" border="0" src="http://stats.wordpress.com/b.gif';
				$kill_footers[] = '<div class=\"feedflare\">';
				$kill_footers[] = '<a href="http://getinboundwriter.com/wordpress/"><img src="http://hdtvprofessor.com/HDTVAlmanac/wp-content/plugins/inboundwriter/images/h_blue.png" alt="Optimized with InboundWriter"class="aligncenter" style="border:0;clear:both;"/></a>';
				foreach ($kill_footers as $footer) {
					$description = strleft($description . $footer, $footer);
					$content = strleft($content . $footer, $footer);
				}
				if ($content == '') {
					$content = $description;
					$description = substr(strip_tags($description, '<p><br><a>'), 0, 250);
					$description = strleftback($description, ' ') .' [...]';
				}

				# Testing Encoding - 6/26/11
#				$description = addslashes($description); # Expert
#				$description = htmlentities($description, ENT_QUOTES, 'UTF-8');
				$description = htmlentities($description, ENT_QUOTES, 'ISO-8859-1');

				$content = addslashes($content);

				# Update mt_entry
				$sql = "	INSERT INTO mt_entry (entry_blog_id, entry_status, entry_author_id, entry_title, entry_excerpt, entry_text, entry_created_on)
				VALUES ({$site['publish_to']}, 4, '{$site['author_id']}', '$title', '$description', '$content', '$entry_created_on')";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}
				$entry_id = mysql_insert_id();

				# Update aux_mt_entry
#				$sql = "INSERT IGNORE INTO aux_mt_entry (entry_id, blog_id, notification_sent, thread_created, syn_link, enclosure_url, enclosure_type)
#				VALUES ($entry_id, {$site['publish_to']}, 0, 0, '{$article['link']}', '{$article['enclosure_url']}', '{$article['enclosure_type']}')";
				$sql = "INSERT IGNORE INTO aux_mt_entry (entry_id, blog_id, syn_link, enclosure_url, enclosure_type)
				VALUES ($entry_id, {$site['publish_to']}, '{$article['link']}', '{$article['enclosure_url']}', '{$article['enclosure_type']}')";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}

				# Update mt_blog
				$sql = "UPDATE mt_blog
				SET blog_children_modified_on = '$entry_created_on'
				WHERE blog_id = {$site['publish_to']}
					AND blog_children_modified_on < '$entry_created_on'";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}

				# Update mt_log
				# Add log entry for auto-created post if desired

				# Update mt_objecttag
				# Add tags to auto-created post if desired

				# Update mt_placement
#				$categories = matchCategories($article['category'], $site['publish_to']);
				$categories = getCategories($title, $description, $site['publish_to']);
				$is_primary = 1;
				foreach ($categories as $category_id) {
					$sql = "INSERT INTO mt_placement (placement_entry_id, placement_blog_id, placement_category_id, placement_is_primary)
					VALUES ($entry_id, '{$site['publish_to']}', $category_id, $is_primary)";
					if ($debug) {echo "$sql\n";} else {mQuery($sql);}
					$is_primary = 0; # Only indicate the first category as pirmary
				} # categories
			} # Not yet syndicated
		} # articles
	} # syn_sites
?>