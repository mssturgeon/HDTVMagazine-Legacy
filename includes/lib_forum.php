<?
/*
	# Should be included in calling script
	define('IN_PHPBB', true);
	$phpbb_root_path = BASE_DIR .'/forum/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	include_once($phpbb_root_path . 'common.' . $phpEx);
*/
	include_once(BASE_DIR .'/forum/includes/functions_posting.php');

/*
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
*/

/*** CONSTANTS ***
USER_NORMAL = 0
USER_INACTIVE = 1
USER_IGNORE = 2
USER_FOUNDER = 3

INACTIVE_REGISTER = 1
INACTIVE_PROFILE = 2
INACTIVE_MANUAL = 3
INACTIVE_REMIND = 4
***/

	function create_topic($forum_id, $subject, $message, $poster_id, $poster_ip, $watch_topic) {
			# Creates a post in phpbb given the forum_id, post_subject, post_text, poster_id, and poster_ip
		global $debug, $user, $auth;

		try {
			# Get login info for poster_id
			if ($poster_id == '') $poster_id = 36589; // Anonymously Submitted
			$sql = "SELECT username, user_password FROM ". USERS_TABLE ." WHERE user_id = $poster_id";
			if ($debug) {echo "$sql\n";}
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			$post_username = $row['username'];
			$post_password = $row['user_password'];

			$user->session_begin();
			$auth->login($post_username, $post_password, false, 1, 0);
			$user->data['user_id'] = $poster_id;
			$auth->acl($user->data);
			$user->setup();

			$subject = utf8_normalize_nfc($subject);
			$message = utf8_normalize_nfc($message);

			$poll = false;
			$uid = $bitfield = $flags = ''; // will be modified by generate_text_for_storage
			$allow_smilies = $allow_urls = $allow_bbcode = true; // false is default for generate_text_for_storage function
			generate_text_for_storage($message, $uid, $bitfield, $flags, $allow_bbcode, $allow_urls, $allow_smilies);

			$data = array(
				// General Posting Settings
				'forum_id' => $forum_id,	// The forum ID in which the post will be placed. (int)
				'topic_id' => 0,									// Post a new topic or in an existing one? Set to 0 to create a new one, if not, specify your topic ID here instead.
				'icon_id' => false,							// The Icon ID in which the post will be displayed with on the viewforum, set to false for icon_id. (int)

				// Defining Post Options
				'enable_bbcode' => true,	// Enable BBcode in this post. (bool)
				'enable_smilies' => true,	// Enabe smilies in this post. (bool)
				'enable_urls' => true,				// Enable self-parsing URL links in this post. (bool)
				'enable_sig' => true,				// Enable the signature of the poster to be displayed in the post. (bool)

				// Message Body
				'message' => $message,										// Your text you wish to have submitted. It should pass through generate_text_for_storage() before this. (string)
				'message_md5' => md5($message),	// The md5 hash of your message

				// Values from generate_text_for_storage()
				'bbcode_bitfield' => $bitfield,		// Value created from the generate_text_for_storage() function.
				'bbcode_uid' => $uid,								// Value created from the generate_text_for_storage() function.

				// Other Options
				'post_edit_locked' => 0,	// Disallow post editing? 1 = Yes, 0 = No
				'topic_title' => $subject,	// Subject/Title of the topic. (string)

				// Email Notification Settings
				'notify_set' => $watch_topic,		// (bool) ??? Set topic notification
				'notify' => true,													// (bool) ??? Allow topic notification
				'post_time' => 0,											// Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
				'forum_name' => '',									// For identifying the name of the forum in a notification email. (string)

				// Indexing
				'enable_indexing' => true,	// Allow indexing the post? (bool)

				// 3.0.6
				'force_approved_state' => true,	// Allow the post to be submitted without going into unapproved queue
			);

	//		$post_url = submit_post('post', $subject, '', POST_NORMAL, &$poll, &$data, false);
			$post_url = submit_post('post', $subject, $post_username, POST_NORMAL, $poll, $data, false);

			$user->session_kill();

			# Update Post with poster_id
	/*
			$sql = "UPDATE phpbb3_posts SET poster_id = '$poster_id' WHERE post_id = ". $data['post_id'];
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	*/

			# Get topic_id
	/*
			$sql = "SELECT topic_id FROM phpbb3_posts WHERE post_id = ". $data['post_id'];
			if ($debug) {echo "$sql\n";}
			$result = mQuery($sql);
			$row = mysql_fetch_assoc($result);
			$topic_id = $row['topic_id'];
	*/

			# Update Topic with poster_id
	/*
			$sql = "UPDATE phpbb3_topics SET topic_poster = '$poster_id' WHERE topic_id = ". $data['topic_id'];
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	*/
			return $data['topic_id'];
		} catch (Exception $e) {
			echo "FAILED: Creating forum topic for entry_id {$row['entry_id']}: ". $e->getMessage();
			return 0;
		}
	}

?>
