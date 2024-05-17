<?
	/*
		This script does several things:
			1) Checks for new content and creates a corresponding entry in the aux table
			2) Creates a forum topic for the new content
			3) Sends tweet
			4) Publishes to Facebook
			5) Queues up an email to those who have elected to be notified of new content

			Blog statuses:
			1 - Unpublished
			2 - Published
			4 - Scheduled
	*/

#	$verbose = true;

	if (!in_array('continuous.php', get_included_files())) { // Only initialize these items if we're calling directly (vs including from parent)
		$debug = isset($_GET['debug']);

		if ($debug) header('Content-Type: text/plain');

		# Include necessary libraries for automated scripts
		define('BASE_DIR', '/var/www/html');
		require_once(BASE_DIR .'/includes/constants.php');
		require_once(BASE_DIR .'/includes/lib_common.php');
		require_once(BASE_DIR .'/includes/lib_mysql.php');

		# Include phpbb library for table constants
		define('IN_PHPBB', true);
		$phpbb_root_path = BASE_DIR .'/forum/';
		$phpEx = substr(strrchr(__FILE__, '.'), 1);
		include_once($phpbb_root_path . 'common.' . $phpEx);

		# Load admindata
		$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
		$admindata = mysql_fetch_assoc($result);
	}

	require_once(BASE_DIR .'/includes/lib_image.php');
	require_once(BASE_DIR .'/includes/lib_amazon.php');
	require_once(BASE_DIR .'/includes/lib_facebook.php');
	require_once(BASE_DIR .'/includes/lib_forum.php');

	### Check for new content and create aux entry ###
	$sql = "
	INSERT IGNORE INTO aux_mt_entry
	(entry_id, blog_id, notification_sent, tweet_sent, thread_created, facebook_published)
		SELECT entry_id, entry_blog_id, 0, 0, 0, 0
		FROM mt_entry
		WHERE entry_blog_id IN (". INCLUDE_BLOGS_NOTIFY .")
			AND entry_status = 2
			AND LENGTH(entry_excerpt) > 10";
	if ($debug) {echo "$sql\n";} else {mQuery($sql);}

	### Loop through new content ###
	$sql = "
	SELECT e.entry_id, entry_blog_id, entry_title, entry_excerpt, entry_created_on, entry_author_id, author_name, user_id,
		notification_sent, thread_created, tweet_sent, facebook_published, enclosure_url, image_src, ASIN
	FROM mt_entry e, aux_mt_entry a, mt_author auth, aux_author aa
	WHERE e.entry_id = a.entry_id
		AND e.entry_author_id = auth.author_id
		AND e.entry_author_id = aa.author_id
		AND (notification_sent = 0 OR thread_created = 0 OR tweet_sent = 0 OR facebook_published = 0)
		AND entry_status = 2
		AND entry_excerpt <> ''";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($debug || $verbose) echo "Processing {$row['entry_id']}\n";
  		$entry = getEntryInfo($row['entry_blog_id']);
		$ts = strtotime($row['entry_created_on']);
		$y = date('Y', $ts);
		$m = date('m', $ts);
		$link = BASE_URL ."/$entry[blog_dir]/$y/$m/". dirify($row['entry_title']) .".php";

		# Check to see if an image exists. If not, check for an ASIN and get it from Amazon. Otherwise get image from excerpt if one exists
		if ($row['image_src'] == '') {
			if ($row['ASIN'] != '') {
				$row_amazon = getByASIN($row['ASIN']);
				if ($row_amazon != '') {
					$row['image_src'] = $row_amazon['SmallImageURL'];
					if ($debug || $verbose) echo "found image by ASIN: {$row['image_src']}\n";
					$sql_img_update = "UPDATE aux_mt_entry SET image_src = '{$row['image_src']}' WHERE entry_id = '{$row['entry_id']}'";
					if ($debug) {echo "$sql_img_update\n";} else {mQuery($sql_img_update);}
				}
			} else {
				if ($debug || $verbose) echo "No ASIN, grabbing from excerpt\n";
				$dom = new DOMDocument();
				$dom->loadHTML("<html><body>{$row['entry_excerpt']}</body></html>");
				$imgs = $dom->getElementsByTagName('img');
				if ($imgs->length > 0) {
					$row['image_src'] = $imgs->item(0)->getAttribute('src');
					if ($debug || $verbose) echo "found image in excerpt: {$row['image_src']}\n";
					$sql_img_update = "UPDATE aux_mt_entry SET image_src = '{$row['image_src']}' WHERE entry_id = '{$row['entry_id']}'";
					if ($debug) {echo "$sql_img_update\n";} else {mQuery($sql_img_update);}
				} else {
					if ($debug || $verbose) echo "No image found in excerpt\n";
				}
			}
		}

		# Might not need to adjust because Facebook scales
		$image_src_fb = $row['image_src'];

/*
		# Check image to see if it's greater than 90 pixels in any dimension. If so, resize and save locally for use in Facebook postings (90 pixels max)
		$image_src_fb = '';
		if ($row['image_src'] != '') {
			echo "image_src not blank, checking to see if it needs resizing\n";
			$image = new SimpleImage();
			$image->load($row['image_src']);
			echo "height: {$image->getHeight}\n";
			echo "width: {$image->getWidth}\n";
			if (max($image->getWidth, $image->getHeight) > 90) { # Image needs resizing
				echo "image too big, resizing\n";
				if ($image->getWidth > $image->getHeight) {
					$image->resizeToWidth(90);
				} elseif ($image->getHeight > $image->getWidth) {
					$image->resizeToHeight(90);
				}
				$image_src_fb = "/{$entry['blog_dir']}/images/90". strrchr($row['image_src'], '/');
				echo "saving image: ". BASE_DIR . $image_src_fb ."\n";
				$image->save(BASE_DIR . $image_src_fb);
			} else { # Image size is < 90
				$image_src_fb = "/{$entry['blog_dir']}/images". strrchr($row['image_src'], '/');
				echo "image size is fine: ". BASE_DIR . $image_src_fb ."\n";
			}
		}
*/

		### Create Forum Topic and associated entries ###
		if ($row['thread_created'] == 0) {
/* COMMENT OUT TEMPORARILY TO GET THINGS PUBLISHED - NEED TO FIND OUT WHY IT"S NOT PUBLISHING
			$forum_id = $entry['response_forum'];
			$post_subject = addslashes($row['entry_title']);
			$post_message = addslashes($row['entry_excerpt']) ."\n\n[url=$link]Read {$entry['read_text']}[/url]";
			$poster_id = $row['user_id'];
			print "\nProcessing {$row['entry_id']}\n";
			$topic_id = create_topic($forum_id, $post_subject, $post_message, $poster_id, $poster_ip, true);

			if ($topic_id > 0) { # Update thread_created
				$sql = "UPDATE aux_mt_entry SET thread_created = 1, topic_id = $topic_id WHERE entry_id = $row[entry_id]";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}
			}
*/
		} # END if ($row['thread_created'] == 0)

		if ($row['tweet_sent'] == 0) { ### Send Tweet ###
			if ($debug) echo date('Y/m/d H:i:s') .": Sending Tweet...\n";
			if ($row['entry_blog_id'] != 6) {
				$hashtags = '';
				$tweet_text = html_entity_decode($row['entry_title'], ENT_QUOTES, 'UTF-8');
				$response = tweet_status($tweet_text, $link);
				if ($debug) print_r($response);
			}

			# Update tweet_sent to "sent"
			$sql = "UPDATE aux_mt_entry SET tweet_sent = 1 WHERE entry_id = $row[entry_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		} # END if ($row['tweet_sent'] == 0)

		if ($row['facebook_published'] == 0) { ### Publish to Facebook ###
			if($debug) echo date('Y/m/d H:i:s') .": Publishing to Facebook...\n";
			if ($row['entry_blog_id'] != 6) {
				# Get categories - added to post properties
				$sql = "
				SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
				WHERE {$row['entry_id']} = p.placement_entry_id
					AND c.category_id = p.placement_category_id
					AND placement_is_primary = 1";
				$res_categories = mQuery($sql);
				while ($row_category = mysql_fetch_assoc($res_categories)) {
					$catlinks['text'] = stripslashes($row_category['category_label']);
					$catlinks['href'] = BASE_URL .'/category.php?id='. $row_category['category_id'] .'&amp;category='. urlencode(stripslashes($row_category['category_label']));
				}

				$attachment = array(
					'access_token' => $facebook->getAccessToken(),
#					'message' => 'this is my message',
					'name' => html_entity_decode(strip_tags($row['entry_title']), ENT_QUOTES, 'UTF-8'),
#					'caption' => "Caption of the Post",
					'link' => $link,
					'description' => html_entity_decode(strip_tags($row['entry_excerpt']), ENT_QUOTES, 'UTF-8'),
#					'picture' => 'http://mysite.com/pic.gif',
#					'source' => '',
#					'place' => '',
#					'tags' => '',
					'properties' => array(
						'Author' => array(
							'text' => $row['author_name'],
							'href' => BASE_URL .'/author.php?author='. urlencode($row['author_name']) .'&id='. $row['entry_author_id']
						),
						'Category' => $catlinks,
					),
					'actions' => array(
						array(
							'name' => 'Read '. $entry['read_text'],
							'link' => $link
						)
					)
				);

				if ($image_src_fb != '') {
					$attachment['picture'] = $image_src_fb;
				} else {
					$attachment['picture'] = '';
				}
				#print_r($attachment);

				fbPost('/HDTVMagazine/feed/', $attachment);
#				exit();
			} # END if ($row['entry_blog_id'] != 6)

			# Update to "published"
			$sql = "UPDATE aux_mt_entry SET facebook_published = 1 WHERE entry_id = $row[entry_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		} # END if ($row['facebook_published'] == 0)

		if ($row['notification_sent'] == 0) { ### Send Emails ###
			if ($debug) echo date('Y/m/d H:i:s') .": Queueing Email...\n";
			# Update blog notification to "sent"
			$sql = "UPDATE aux_mt_entry SET notification_sent = 1 WHERE entry_id = $row[entry_id]";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}

			# Compose message, queue email for sending, and tweet
			$message = array();
			$subject = addslashes("New $entry[entry_type]: $row[entry_title]");

			# create a boundary string. It must be unique so we use the MD5 algorithm to generate a random hash
			$boundary = "PHP-alt-". md5(date('r', time()));

			$orig_message = file_get_contents("http://www.hdtvmagazine.com/admin/emails/content-new-multipart.php".
			"?id=". $row['entry_id'] .
			"&boundary=". $boundary);

			if ($debug) {$entry['subscription_type'] = SUB_TEST;}
			$sql = "
			SELECT id, first_name, last_name, user_name, email_address
			FROM user u, ". USERS_TABLE ." pu
			WHERE u.bb_id = pu.user_id
				AND pu.user_inactive_reason = 0
				AND email_address <> ''
				AND email_invalid < 3
				AND email_spam = 0
				AND subscriptions & $entry[subscription_type]";
			if ($debug) {echo "$sql\n";}
			$result = mQuery($sql);

			while ($row_email = mysql_fetch_assoc($result)) {
				# Replace unsub_code and email_address
				$message = str_replace('[[email_address]]', $row_email['email_address'], $orig_message);
				$message = str_replace('[[unsub_code]]', urlencode(base64_encode($row_email['id'] .':'. $entry['subscription_type'])), $message);
				$message = str_replace('[[list_name]]', $SUB[$entry['subscription_type']], $message);
				$message = addslashes($message);

				$name = $row_email['first_name'] .' '. $row_email['last_name'];
				if (trim($name) == '') {
					$to = $row_email['user_name'] .' <'. $row_email['email_address'] .'>';
				} else {
					$to = addslashes($name .' <'. $row_email['email_address'] .'>');
				}

				$sql = "INSERT INTO email_queue (send_to, subject, message, content_type)
				VALUES ('$to', '$subject', '$message', 'multipart/alternative; boundary=\"$boundary\"')";
				if ($debug) {echo "$sql\n";} else {mQuery($sql);}
			}
		} # END if ($row['notification_sent'] == 0)

	} # END while ($row = mysql_fetch_assoc($result))
?>
