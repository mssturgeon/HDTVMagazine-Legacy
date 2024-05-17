<?
	header('Content-Type: text/plain');

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_admin.php');
	require(BASE_DIR .'/includes/facebook/facebook.php');
	$base_url = 'http://www.hdtvmagazine.com';

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	# Initialize Facebook
	$facebook = new Facebook($admindata['facebook_api_key'], $admindata['facebook_app_secret']);

	function publish() {
		global $facebook, $admindata, $base_url;
		$sql = "
		SELECT e.entry_id, entry_blog_id, entry_title, entry_excerpt, entry_created_on, entry_author_id, author_name, user_id,
			notification_sent, thread_created, tweet_sent, facebook_published, enclosure_url
		FROM mt_entry e, aux_mt_entry a, mt_author auth, aux_author aa
		WHERE e.entry_id = a.entry_id
			AND e.entry_author_id = auth.author_id
			AND e.entry_author_id = aa.author_id
			AND e.entry_id = 3447";
		if ($debug) {echo "$sql\n";}
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$entry = getEntryInfo($row['entry_blog_id']);
			$ts = strtotime($row['entry_created_on']);
			$y = date('Y', $ts);
			$m = date('m', $ts);
			$link = "/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";

			# Get categories
			$sql = "
			SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
			WHERE {$row['entry_id']} = p.placement_entry_id
				AND c.category_id = p.placement_category_id
				AND placement_is_primary = 1";
			$res_categories = mQuery($sql);
			while ($row_category = mysql_fetch_assoc($res_categories)) {
				$catlinks['text'] = stripslashes($row_category['category_label']);
				$catlinks['href'] = $base_url .'/category.php?id='. $row_category['category_id'] .'&amp;category='. urlencode(stripslashes($row_category['category_label']));
			}

			$attachment = array(
				'name' => html_entity_decode(strip_tags($row['entry_title']), ENT_QUOTES, 'UTF-8'),
				'href' => $base_url . $link,
				'description' => html_entity_decode(strip_tags($row['entry_excerpt']), ENT_QUOTES, 'UTF-8'),
				'properties' => array(
					'Author' => array(
						'text' => $row['author_name'],
						'href' => $base_url .'/author.php?author='. urlencode($row['author_name']) .'&id='. $row['entry_author_id']
					),
					'Category' => $catlinks,
				)
			);
			if ($row['entry_blog_id'] == 9) { # Podcast
				$attachment['media'] = array(array(
					'type' => 'mp3',
					'src' => $row['enclosure_url'],
					'title' => $row['entry_title'],
					'artist' => $row['author_name'],
					'album' => 'HDTV Magazine Syndicated Podcasts'
				));
			}
#			print_r($attachment);
#			exit;
			$action_links = array(
				array(
					'text' => 'Become a Fan',
					'href' => 'http://www.facebook.com/pages/HDTV-Magazine/'. $admindata['facebook_page_id']
				),
				array(
					'text' => 'Read '. $entry['read_text'],
					'href' => $base_url . $link
				)
			);

			$facebook->api_client->stream_publish('', $attachment, $action_links, null, $admindata['facebook_page_id']);
		}
	}

#	$data = $facebook->api_client->pages_getInfo(45415877375, 'fan_count', '', '');
#	echo $data[0]['fan_count'];

	publish();
?>