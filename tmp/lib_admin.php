<?
/*********************************************************************
	delete_user
		- Removes user from all pertinent tables
	getRowInformation
		- Loops through given table row and echo's name-value pairs
	shorten_url
		- Uses the bit.ly service to grab a short URL for tweets
	tweet_status
		- Tweets the provided message using the URL provided
	tweet_dm
		- Tweets the given message to the specified screen name
*********************************************************************/
	function delete_user($user_id) {
		global $debug;

		include_once(BASE_DIR .'/forum/includes/functions_user.php');

		$sql = "SELECT id, user_name FROM user WHERE bb_id = '$user_id'";
		if ($debug) {echo "$sql\n";}
		$result = mQuery($sql);

		if (mysql_num_rows($result) == 1) {
			$row = mysql_fetch_assoc($result);
			$id = $row['id'];
			$user_name = $row['user_name'];

			# Copy user account to backup table
			$sql = "INSERT INTO user_archive SELECT * FROM user WHERE id = $id";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}

			# Delete user account
			$sql = "DELETE FROM user WHERE id = $id";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}

			# Remove stations linked to user
			$sql = "DELETE FROM join_user_station WHERE user_id = $id";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}

			# Remove hardware linked to user
			$sql = "DELETE FROM join_user_hardware WHERE user_id = $id";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}

		user_delete('retain', $user_id); // Delete user, retain posts as "Anonymous"

/*** OLD REMOVE USER
		# Remove from phpbb_users table, phpbb_user_group
		$sql = "DELETE FROM phpbb_users WHERE user_id = $user_id";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		$sql = "SELECT g.group_id FROM phpbb_groups g, phpbb_user_group ug WHERE g.group_id = ug.group_id AND group_single_user = 1 AND user_id = $user_id";
		if ($debug) {echo "$sql\n";}
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result);
		$sql = "DELETE FROM phpbb_groups WHERE group_id = '$row[group_id]'";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		$sql = "DELETE FROM phpbb_user_group WHERE user_id = $user_id";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}
***/
	}

	function getRowInformation($row) {
		$rowinfo = '<fieldset id="admin-information"><legend>Data (Admin Only)</legend>';
		foreach ($row as $name => $value) {
			$rowinfo .= "<label>$name: </label>$value<br />";
	 	}
		$rowinfo .= '</fieldset>';
		return $rowinfo;
	}

	function old_shorten_url($long_url) {
		$curl_handle = curl_init();
		$url = 'http://api.bit.ly/shorten?'.
		'login=hdtv'.'&amp;'.
		'apiKey=[REDACTED]'.'&amp;'.
		'version=2.0.1'.'&amp;'.
		'format=xml'.'&amp;'.
		'history=1'.'&amp;'.
		'longUrl='. urlencode($long_url);
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_HEADER, false);
 		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		$xml = new SimpleXMLElement( $result );
		return $xml->results->nodeKeyVal->shortUrl;
	}

	function shorten_url($long_url) {
		$curl_handle = curl_init();
		$url = 'http://api.bit.ly/v3/shorten?'.
		'login=hdtv&'.
		'apiKey=[REDACTED]&'.
		'format=xml&'.
		'longUrl='. urlencode($long_url);

		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_HEADER, false);
 		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		$xml = new SimpleXMLElement( $result );

		return $xml->data->url;
	}

	function tweet_status($message, $long_url) { # Shortens given URL and sends tweet
		global $admindata;
		require_once(BASE_DIR .'/includes/twitteroauth/twitteroauth.php');

		$short_url = shorten_url($long_url);

		# Shorten message
		$max = 140 - strlen($short_url) - 2; # Allow room for a 'period space' between the message and url
		if (strlen($message) > $max) {
			$message = substr($message, 0, $max-2) .'... '. $short_url;
		} else {
			$message = $message .'. '. $short_url;
		}

		$connection = new TwitterOAuth($admindata['twitter_consumer_key'], $admindata['twitter_consumer_secret'], $admindata['twitter_access_token'], $admindata['twitter_access_token_secret']);
		return $connection->post('statuses/update', array('status' => $message));
	}

	function tweet_dm($screen_name, $text) {
		global $admindata;
		require_once(BASE_DIR .'/includes/twitteroauth/twitteroauth.php');

		$connection = new TwitterOAuth($admindata['twitter_consumer_key'], $admindata['twitter_consumer_secret'], $admindata['twitter_access_token'], $admindata['twitter_access_token_secret']);
		return $connection->post('direct_messages/new', array('screen_name' => $screen_name, 'text' => $text));
	}
?>