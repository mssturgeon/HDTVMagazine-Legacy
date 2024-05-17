<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

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

	require(BASE_DIR .'/includes/doctype.php');
?>
<html><head>
	<title>HDTV Magazine - Email Bounced</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript">
		function send_notification(entry_id) {
			alert('sending notification');
		}

		function create_thread(entry_id) {
			alert('creating thread');
		}

		function send_tweet(entry_id) {
			alert('sending tweet');
		}

		function fb_publish(entry_id) {
			alert('publishing to facebook');
		}
	</script>
</head><body>
<?
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
		AND entry_excerpt <> ''
	ORDER BY entry_created_on";
	if ($debug) {echo "$sql\n";}
	$result = mQuery($sql);

	while ($row = mysql_fetch_assoc($result)) {
		$entry_id = $row['entry_id'];
		$a_email = ($row['notification_sent'] == 0) ? '<a id="email-'. $entry_id .'" href="#" onclick="send_notification('. $entry_id .')">Email</a>' : '<span class="ui-icon ui-icon-check"></span>';
		$a_thread = ($row['thread_created'] == 0) ? '<a id="thread-'. $entry_id .'" href="#" onclick="create_thread('. $entry_id .')">Post Thread</a>' : '<span class="ui-icon ui-icon-check"></span>';
		$a_tweet = ($row['tweet_sent'] == 0) ? '<a id="tweet-'. $entry_id .'" href="#" onclick="send_tweet('. $entry_id .')">Tweet</a>' : '<span class="ui-icon ui-icon-check"></span>';
		$a_publish = ($row['facebook_published'] == 0) ? '<a id="publish-'. $entry_id .'" href="#" onclick="fb_publish('. $entry_id .')">FB Publish</a>' : '<span class="ui-icon ui-icon-check"></span>';

		echo '<div id="entry-'. $entry_id .'" class="content-entry">'.
		'<h2>'. $row['entry_title'] .'</h2>'.
		'<h3>'. date('m/d/y g:ia', strtotime($row['entry_created_on'])) .'</h3>'.
		'Notification Sent: '. $a_email .'<br />'.
		'Thread Created: '. $a_thread .'<br />'.
		'Tweet Sent: '. $a_tweet .'<br />'.
		'Published to FB: '. $a_publish .'<br />'.
		'</div>';
	}
?>
</body></html>