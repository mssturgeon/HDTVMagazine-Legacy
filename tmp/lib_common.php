<?
	/*******************************************************************************************************************
	Function Declarations

	access($access_type)
		- Verifies proper access
	access_denied()
		- Redirects to the access denied page
	convert_high_ascii
		- Converts hex values to ascii characters
	dirify
		- Used to convert article titles to compatible URL's for the publishing software (MovableType)
	do_login()
		- Performs site login command
	getBoxAuthors
		- Gets box with links to authors pages and number of stories in parenthesis
	getBoxCategories
		- Gets box with links to category pages and number of stories in parenthesis
	getBoxDiscussions
		-
	getSelectBox($qry, $fieldName, $defaultValues, $otherProperties)
		- Builds a SELECT box based on the $qry provided
	getCRBox($qry, $fieldType, $fieldName, $defaultValues, $wrap, $otherProperties)
		- Builds a check/radio box based on $qry provided
	getRandomPassword()
		- Returns random password
	get_Rand_string($hash)
		- Gets a random 8-character string, optionally encoded using md5
	getBlogDir
		- Given a Blod ID, returns the base directory relative to root
	getDateString
		- Given a timestamp, returns a date in "minutes ago", "hours ago", or mmm dd, hh:mmp
	js_alert($text)
		- Opens Javascript alert box
	js_back($text)
		- Opens a javascript alert box if $text is provided and navigates back a page
	js_close()
		- Closes current window
	js_close_reload()
		- Closes current window and refreshes opener window
	js_redirect($url)
		- Redirects page to provided URL
	js_replace($url)
		- Replaced current page with provided URL
	prompt_login($url)
		- Prompts for login and then returns the user to the provided URL if successful
	strleft($s1, $s2)
		- Find's the first occurence of s2 in s1 and returns the string to the left
	strleftback($s1, $s2)
		- Find's the last occurence of s2 in s1 and returns the string to the left
	strright($s1, $s2)
		- Find's the first occurence of s2 in s1 and returns the string to the right
	strrightback($s1, $s2)
		- Find's the last occurence of s2 in s1 and returns the string to the right
	userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
		- Custom error handler

	*******************************************************************************************************************/

	date_default_timezone_set('America/Los_Angeles');

	function access($access_type) {
		global $user;
		return ($user->data['access'] & $access_type);
	}

	function access_denied() {
		js_replace(URL_ACCESS_DENIED);
	}

	function convert_high_ascii($s) {
		$HighASCII = array(
			"!\xc0!" => 'A',	 # A`
			"!\xe0!" => 'a',	 # a`
			"!\xc1!" => 'A',	 # A'
			"!\xe1!" => 'a',	 # a'
			"!\xc2!" => 'A',	 # A^
			"!\xe2!" => 'a',	 # a^
			"!\xc4!" => 'Ae',	# A:
			"!\xe4!" => 'ae',	# a:
			"!\xc3!" => 'A',	 # A~
			"!\xe3!" => 'a',	 # a~
			"!\xc8!" => 'E',	 # E`
			"!\xe8!" => 'e',	 # e`
			"!\xc9!" => 'E',	 # E'
			"!\xe9!" => 'e',	 # e'
			"!\xca!" => 'E',	 # E^
			"!\xea!" => 'e',	 # e^
			"!\xcb!" => 'Ee',	# E:
			"!\xeb!" => 'ee',	# e:
			"!\xcc!" => 'I',	 # I`
			"!\xec!" => 'i',	 # i`
			"!\xcd!" => 'I',	 # I'
			"!\xed!" => 'i',	 # i'
			"!\xce!" => 'I',	 # I^
			"!\xee!" => 'i',	 # i^
			"!\xcf!" => 'Ie',	# I:
			"!\xef!" => 'ie',	# i:
			"!\xd2!" => 'O',	 # O`
			"!\xf2!" => 'o',	 # o`
			"!\xd3!" => 'O',	 # O'
			"!\xf3!" => 'o',	 # o'
			"!\xd4!" => 'O',	 # O^
			"!\xf4!" => 'o',	 # o^
			"!\xd6!" => 'Oe',	# O:
			"!\xf6!" => 'oe',	# o:
			"!\xd5!" => 'O',	 # O~
			"!\xf5!" => 'o',	 # o~
			"!\xd8!" => 'Oe',	# O/
			"!\xf8!" => 'oe',	# o/
			"!\xd9!" => 'U',	 # U`
			"!\xf9!" => 'u',	 # u`
			"!\xda!" => 'U',	 # U'
			"!\xfa!" => 'u',	 # u'
			"!\xdb!" => 'U',	 # U^
			"!\xfb!" => 'u',	 # u^
			"!\xdc!" => 'Ue',	# U:
			"!\xfc!" => 'ue',	# u:
			"!\xc7!" => 'C',	 # ,C
			"!\xe7!" => 'c',	 # ,c
			"!\xd1!" => 'N',	 # N~
			"!\xf1!" => 'n',	 # n~
			"!\xdf!" => 'ss'
		);
		$find = array_keys($HighASCII);
		$replace = array_values($HighASCII);
		$s = preg_replace($find,$replace,$s);
		return $s;
	}

	function dirify($s) {
		## convert high-ASCII chars to 7bit.
		$s = convert_high_ascii($s);

		## lower-case.
		$s = strtolower($s);

		## remove HTML tags.
		$s = strip_tags($s);

		## remove HTML entities.
		$s = preg_replace('!&[^;\s]+;!', '', $s);

		## remove non-word/space chars.
		$s = preg_replace('![^\w\s]!', '', $s);

		## remove non-word/space/dash chars.
#		$s = preg_replace('![^\w\s-]!', '', $s);

		## change space chars to underscores.
#		$s = preg_replace('!\s+!','_',$s);

		## change space chars to dashes.
		$s = preg_replace('!\s+!','-',$s);

		return $s;
	}

	function getBlogDir($id) {
		switch ($id) {
		case 1:
			return 'articles';
			break;
		case 6:
			return 'test';
			break;
		case 7:
			return 'news';
			break;
		case 8:
			return 'reviews';
			break;
		case 9:
			return 'podcast';
			break;
		case 10:
			return 'columns';
			break;
		default:
			return '';
			break;
		}
	}

	function getBoxAuthors() {
		# Articles by Author, excluding Bulletins
		$sql = "
		SELECT a.author_id, author_name, COUNT(*) num
		FROM mt_author a, mt_entry e, aux_author aux
		WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
			AND entry_status = 2
			AND entry_author_id = a.author_id
			AND entry_author_id = aux.author_id
			AND aux.include = 1
		GROUP BY a.author_id, author_name
		ORDER BY num DESC";
		$res_authors = mQuery($sql);
		while ($row_authors = mysql_fetch_assoc($res_authors)) {
			$list_items .= '<li><a href="/author.php?author='. urlencode($row_authors['author_name']) .'&id='. $row_authors['author_id'] .'">'. $row_authors['author_name'] .'</a><span class="grey"> ('. $row_authors['num'] .')</span></li>';
		}
 	return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2>Authors</h2>
	<ul class="brownsquare">$list_items</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
	}

	function getBoxCategories () {
		$sql = "
		SELECT category_label label, COUNT(*) num
		FROM mt_entry e, mt_placement p, mt_category c
		WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
			AND entry_status = 2
			AND entry_id = p.placement_entry_id
			AND p.placement_category_id = c.category_id
		GROUP BY label
		ORDER BY label";
		$result = mQuery($sql);
		while ($category = mysql_fetch_assoc($result)) {
			$list_items .= '<li><a href="/category.php?category='. urlencode($category['label']) .'">'. $category['label'] .'</a><span class="grey"> ('. $category['num'] .')</span></li>';
		}
 	return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2>Categories</h2>
	<ul class="brownsquare">$list_items</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
	}

	function getBoxDiscussions() {
		# Recent discussions
		$sql = "
		SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
		FROM ". TOPICS_TABLE ." t, ". USERS_TABLE ." u, ". POSTS_TABLE ." p, aux_phpbb_forums af
		WHERE
			t.forum_id = af.forum_id
			AND af.exclude_general = 0
			AND p.poster_id = u.user_id
			AND t.topic_id = p.topic_id
			AND t.topic_last_post_id = p.post_id
		ORDER BY post_time DESC LIMIT 10";
		$result = mQuery($sql);

		while ($row = mysql_fetch_assoc($result)) {
			$last_post = date('n/j g:ia T', $row['post_time']);
			$title = html_entity_decode($row['topic_title']);
			$list_items .= '<li><a href="/forum/viewtopic.php?t='. $row['topic_id'] .'">'. $title .'</a> - <span class="grey">'. $row['post_author'] .'</span> - '. $last_post .'</li>';
		}
 		return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2><a href="/forum/index.php">Other Recent Discussion</h2>
	<ul class="brownsquare">$list_items</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
	}

	function getBoxMoreFromCategory($placement_category_id) {
		if ($placement_category_id == '') {
	 		return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2>No Category Assigned</h2>
<span class="corners-bottom"><span></span></span></div>
EOT;
		} else {
			$sql = "
			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name, category_label
			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
			WHERE entry_author_id = author_id
				AND e.entry_id = p.placement_entry_id
				AND p.placement_category_id = c.category_id
				AND p.placement_category_id = $placement_category_id
				AND e.entry_status = 2
				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
				AND entry_author_id = a.author_id
			ORDER BY entry_created_on DESC LIMIT 10";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$category_label = $row['category_label'];
				$category_label_encoded = urlencode($category_label);
				$ts = strtotime($row['entry_created_on']);
				$y = date('Y', $ts);
				$m = date('m', $ts);
				$entry = getEntryInfo($row['entry_blog_id']);

				$entry['date'] = getDateString($ts);
				$entry['link'] = "/{$entry['blog_dir']}/$y/$m/". dirify($row['entry_title']) .".php";
				$entry['title'] = $row['entry_title'];
				$entry['author'] = $row['author_name'];

				$list_items .= '<li><a href="'. $entry['link'] .'">'. $entry['title'] .'</a> - <span class="grey">'. $entry['author'] .'</span> - '. $entry['date'] .'</li>';
			}
			return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2><a href="/category.php?id={$placement_category_id}&category={$category_label_encoded}">More in Category: $category_label</a></h2>
	<ul class="brownsquare">$list_items</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
		}
	}

	function getBoxMoreFromAuthor($author_name, $current_entry_id) {
		# Recent Articles by Author (exclude this one), excluding Bulletins.
		$author_name_encoded = urlencode($author_name);

		$sql = "
		SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
		FROM mt_entry e, mt_author a
		WHERE entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
			AND entry_status = 2
			AND entry_author_id = a.author_id
			AND a.author_name = '$author_name'
		ORDER BY entry_created_on DESC LIMIT 10";
		$result = mQuery($sql);

		if (mysql_num_rows($result) > 5) {
			while ($row = mysql_fetch_assoc($result)) {
				$author_id = $row['author_id'];
				# Get categories
				$sql = "
				SELECT category_label FROM mt_category c, mt_placement p
				WHERE {$row['entry_id']} = p.placement_entry_id
					AND c.category_id = p.placement_category_id";
				$res_categories = mQuery($sql);
				$row_categories = mysql_fetch_assoc($res_categories);
				$category = $row_categories['category_label'];

				$ts = strtotime($row['entry_created_on']);
				$y = date('Y', $ts);
				$m = date('m', $ts);
				$blog_dir = getBlogDir($row['entry_blog_id']);
				$date = getDateString($ts);
				$link = "/$blog_dir/$y/$m/". dirify($row['entry_title']) .".php";
				if ($row['entry_id'] == $current_entry_id) {
					$list_items .= '<li><b><span class="red">&raquo;</span> '. $row['entry_title'] .' - <span class="grey">'. $category .'</span> - '. $date .'</b></li>';
				} else {
					$list_items .= '<li><a href="'. $link .'">'. $row['entry_title'] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
				}
			}
		}
 		return <<<EOT
<div class="item"><span class="corners-top"><span></span></span>
	<h2><a href="/author.php?author={$author_name_encoded}&id={$author_id}">More from $author_name</a></h2>
	<ul class="brownsquare">$list_items</ul>
	<b><span class="red">&raquo</span> - Currently Reading</b>
<span class="corners-bottom"><span></span></span></div>
EOT;
	}

	function getCatLinksByEntryId($entry_id) {
		$sql = "SELECT c.category_label, c.category_id FROM mt_category c, mt_placement p
		WHERE $entry_id = p.placement_entry_id
			AND c.category_id = p.placement_category_id";
		$res_categories = mQuery($sql);
		while ($row_category = mysql_fetch_assoc($res_categories)) {
			$catlinks[] = '<a href="/category.php?id='. $row_category['category_id'] .'&category='. urlencode(stripslashes($row_category['category_label'])) .'">'. stripslashes($row_category[category_label]) .'</a>';
		}

		return $catlinks;
	}

	function getComments($entry_id) {
		$sql = "
		SELECT p.topic_id, p.post_id, post_time, post_subject, topic_replies, topic_title, username, LEFT(post_text, 1000) post_text
		FROM aux_mt_entry a, ". TOPICS_TABLE ." t, ". POSTS_TABLE ." p, ". USERS_TABLE ." u
		WHERE entry_id = $entry_id
			AND a.topic_id = t.topic_id
			AND t.topic_id = p.topic_id
			AND p.poster_id = u.user_id
		ORDER BY post_time";
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result); # Skip the first one, as it's just the excerpt post.
		$thread_url = '/forum/viewtopic.php?t='. $row['topic_id'];
		$comments = '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>';

		$num_comments = mysql_num_rows($result);
		if ($num_comments == 1) {
			$comments .= '<div class="item"><span class="corners-top"><span></span></span>'.
				'<img src="'. BASE_IMG_HOST .'/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
				'<a href="'. $thread_url .'">'. $row['topic_title'] .'</a> <span class="grey">('. $row['topic_replies'] .' replies)</span>'.
			'<span class="corners-bottom"><span></span></span></div>';
		} else {
			$x = 0;
			while ($row = mysql_fetch_assoc($result)) {
				if ($x++ == 10) break;
				$comment_url = '/forum/viewtopic.php?p='. $row['post_id'] .'#'. $row['post_id'];
				$text = nl2br(strip_tags(str_replace('[', '<', str_replace(']', '>', $row['post_text']))));
				if ($row['post_subject'] != '') {
					$subject = $row['post_subject'];
				} else {
					$subject = "Re: {$row['topic_title']}";
				}

				$class = ($x % 2 == 0) ? 'item' : 'item_odd';
				$comments .= '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
					'<div style="float:right"><a href="/forum/posting.php?mode=quote&amp;p='. $row['post_id'] .'"><img src="'. BASE_IMG_HOST .'/forum/templates/HDTVMagazine4/images/lang_english/icon_reply.gif" alt="Reply" title="Reply" width="53" height="17"></a></div>'.
					'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
					'<b>'. $row['username'] .'</b> &bull; '. date('M j, g:ia', $row['post_time']) .'<br />'.
					$text .'...<br /><div align="right"><a href="'. $comment_url .'">Read More</a></div>'.
				'<span class="corners-bottom"><span></span></span></div>';
			}
		}
/*
		if ($num_comments > $x) {
			$comments .= '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
			"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
			'<span class="corners-bottom"><span></span></span></div>';
		}
*/
		return $comments;
	}

	function getDateString($ts) {
		$ago = time() - $ts;
		switch (true) {
		case ($ago < 1*HOURS):
			return floor($ago / MINUTES) .' minutes ago';
			break;
		case ($ago < 24*HOURS):
			return floor($ago / HOURS) .' hours ago';
			break;
		case ($ago < 365*DAYS):
			return gmdate('M j, g:ia', $ts);
			break;
		default:
			return gmdate('M j Y, g:ia', $ts);
			break;
		}
	}

	function getEntryInfo($id) {
		switch ($id) {
		case 1:
			$entry['blog_dir'] = 'articles';
			$entry['entry_type'] = 'Article';
			$entry['read_text'] = 'Article';
			$entry['subscription_type'] = SUB_ARTICLES;
			$entry['response_forum'] = 22;
			break;
		case 4:
			$entry['blog_dir'] =  'history/interviews';
			$entry['entry_type'] = 'Interview';
			$entry['read_text'] = 'Interview';
			$entry['response_forum'] = 25;
			break;
		case 5:
			$entry['blog_dir'] =  'history';
			$entry['entry_type'] = 'Article';
			$entry['read_text'] = 'Article';
			$entry['response_forum'] = 24;
			break;
		case 6:
			$entry['blog_dir'] =  'test';
			$entry['entry_type'] = 'Test';
			$entry['read_text'] = '{Content}';
			$entry['response_forum'] = 144;
			$entry['subscription_type'] = SUB_TEST;
			break;
		case 7:
			$entry['blog_dir'] =  'news';
			$entry['entry_type'] = 'Bulletin';
			$entry['read_text'] = 'Bulletin';
			$entry['subscription_type'] = SUB_BULLETINS;
			$entry['response_forum'] = 23;
			break;
		case 8:
			$entry['blog_dir'] =  'reviews';
			$entry['entry_type'] = 'Review';
			$entry['read_text'] = 'Review';
			$entry['subscription_type'] = SUB_REVIEWS;
			$entry['response_forum'] = 114;
			break;
		case 9:
			$entry['blog_dir'] =  'podcast';
			$entry['entry_type'] = 'Podcast';
			$entry['read_text'] = 'Show Notes';
			$entry['subscription_type'] = SUB_PODCAST;
			$entry['response_forum'] = 116;
			break;
		case 10:
			$entry['blog_dir'] =  'columns';
			$entry['entry_type'] = 'Column';
			$entry['read_text'] = 'Column';
			$entry['subscription_type'] = SUB_COLUMNS;
			$entry['response_forum'] = 117;
			break;
		default:
			$entry['blog_dir'] =  '';
			$entry['entry_type'] = 'Unknown';
			$entry['read_text'] = 'More';
			break;
		}
		return $entry;
	}

	function getFormattedEntry($entry, $format) {
		/* Given an id from MovableType, builds and returns an entry listing based on the requested format type (excerpt, or title-only). */

		# Start DIV
/*
		$formatted = '<div class="entry" onmouseover="this.style.backgroundColor = \'#ecf3f7\';"'.
		' onmouseout="this.style.backgroundColor = \'transparent\';">'.
		'<span class="corners-top"><span></span></span>';
*/
		$formatted = '<div class="entry"><span class="corners-top"><span></span></span>';

		if ($entry['topic_id'] != '') {
			if ($entry['topic_replies'] > 0) {
				$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
			'<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $entry['topic_id'] .'">Comments</a> ('. $entry['topic_replies'] .')';
			} else {
				$comments = '<img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
			'<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $entry['topic_id'] .'">Post First Comment</a>';
			}
		}

		switch (true) {
		case $format == 'excerpt':
			# Limit long excerpts
			if (strlen($entry['excerpt']) > 1000) $entry['excerpt'] = trim(substr($entry['excerpt'], 0, 1000) .' ...');

			// Add image_src
			if ($entry['image_src'] != '') $entry['excerpt'] = '<img class="thumb" src="'. $entry['image_src'] .'" alt="" />'. $entry['excerpt'];

/*
			echo '<script type="text/javascript">'."\n".
				'digg_url = \''. BASE_URL . $entry['link'] .'\';'."\n".
				'digg_title = \''. urlencode($entry['title']) .'\';'."\n".
				'digg_bodytext = \''. urlencode($entry['excerpt']) .'\';'."\n".
				'tweetmeme_style = \'compact\';'."\n".
				'tweetmeme_source = \'HDTVMagazine\';'."\n".
				'tweetmeme_url = \''. BASE_URL . $entry['link'] .'\';'."\n".
			'</script>'."\n".
			'<span style="float:right"><script src="http://digg.com/api/diggthis.js"></script></span>'.
			'<span style="float:right"><script src="http://tweetmeme.com/i/scripts/button.js"></script></span>';
*/

			if ($entry['catlinks'] != '') $catlinks =  '<h4>'. implode(' &bull; ', $entry['catlinks']) .'</h4>';

			$formatted .= $catlinks .
			'<h2><a href="'. stripslashes($entry['link']) .'">'. stripslashes($entry['title']) .'</a></h2>'.
			'<h4><span style="text-transform:none">By</span> '. $entry['author'] .' &bull; <span style="color:#800000"> '. $entry['date'] .'</span></h4>'.
			'<!--h4>Published in: </h4-->'.
			'<div class="entry_text">'. str_replace(". ", ". \n", nl2br(stripslashes($entry['excerpt']))) .'</div>'.
			'<div class="footer">'.
   			'<a href="'. stripslashes($entry['link']) .'">Read '. $entry['read_text'] .'</a> &bull; '.
   			$comments .
 				'<!--img src="'. BASE_IMG_URL .'/images/icon_recommend.gif" alt="" /> <a href="">Recommend</a> ()-->'.
			'</div>';
			break;
		case $format == 'title-only':
			if ($entry['catlinks'] != '') $catlinks =  ' &bull; '. implode(' &bull; ', $entry['catlinks']);

			$formatted .= '<h3><a href="'. stripslashes($entry['link']) .'">'. stripslashes($entry['title']) .'</a></h3>'.
			'<h4 style="float:right;">'. $comments .'</h4>'.
			'<h4>'.
				'<span style="text-transform:none">By</span> '. $entry['author'] .' &bull; '.
				'<span style="color:#800000"> '. $entry['date'] .'</span>'.
				$catlinks .
			'</h4>'.
			'<!--h4>Published in: </h4-->';
			break;
		}

		$formatted .= '<span class="corners-bottom"><span></span></span></div>'.
		'<div class="dottedline"></div>';

		return "$formatted\n";
	}

	function getRandomPassword() {
	  $salt = "abcdefghjkmnpqrstuvwxyz23456789";
	  srand((double)microtime()*1000000);
	  	$i = 0;
	  	while ($i <= 7) {
		 		$num = rand() % 33;
		 		$tmp = substr($salt, $num, 1);
		 		$pass = $pass . $tmp;
		 		$i++;
	  	}
	  	return $pass;
	}

	function getSelfAd($productid = '') {
		# This function gets an ad for the specified product id from the store. If non is supplied, then it picks one from the
		# featured items.

		if ($productid == '') {
			$sql = "SELECT productid FROM xcart_featured_products";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) $ids[] = $row[productid];
			$productid = $ids[rand(0, count($ids) - 1)];
		}

		$sql = "
		SELECT p.product, descr, categoryid, image_path
		FROM xcart_products p, xcart_products_categories c, xcart_images_T i
		WHERE p.productid = '$productid'
			AND p.productid = c.productid
			AND p.productid = i.id";
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result);
		$url = 'http://'. SERVER_NAME .'/hdstore/product.php?productid='. $productid .'&amp;cat='. $row[categoryid] .'&amp;page=1';
		return '<div class="item">'.
		'<h2><a href="'. $url .'">'. htmlspecialchars($row[product]) .'</a></h2>'.
		'<p>'.
		'	<a href="'. $url .'"><img src="http://'. SERVER_NAME .'/hdstore/'. $row[image_path] .'" alt="'. $row[product] .'" align="left" style="padding-right:3px" /></a>'.
		$row[descr] .
		'</p>'.
		'<a href="'. $url .'">Find out more....</a></div>';
	}

	function getRandString($hash) {
		$chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

		$max_chars = count($chars) - 1;
		srand( (double) microtime()*1000000);

		$rand_str = '';
		for($i = 0; $i < 8; $i++) {
			$rand_str = ( $i == 0 ) ? $chars[rand(0, $max_chars)] : $rand_str . $chars[rand(0, $max_chars)];
		}

		return ( $hash ) ? md5($rand_str) : $rand_str;
	}

	//--------------------------------------------------------------------------------
	// Syntax:	getSelectBox( SQL, fieldName, defaultValues, otherProperties )
	//
	// Purpose:	This procedure takes as input an SQL query, a field name, default value(s) for the field, and an allowMultiple flag. It returns an HTML
	//			select element based on these inputs. The query is assumed to return two columns: value and name, which correspond to the select box value
	//			and displayed name, respectively.
	//
	// Elements:
	//		SQL							Any valid SQL query that returns two columns: name and value.
	//		fieldName				Indicates the select elements 'name'
	//		defaultValues		If defined, will be the default values for the select box.
	//		otherProperties	Allows inclusion of MULTIPLE, READONLY, etc.
	//
	// Returns:	This procedure does not directly return a value, but instead outputs HTML code to create a select box.
	//--------------------------------------------------------------------------------
	function getSelectBox($qry, $fieldName, $defaultValues, $otherProperties) {
		// By adding a comma to the end of the defaultValues string, this will ensure that we don't unintentionally match a substring of one of the default
		// values.  For example: defaultValues = "5,10": checking against Instr("1") would return 3 (incorrect), but Inst(",1,") would return 0 (correct).
		$defaultValues = ','. trim($defaultValues) .',';
		$selected = '';

		$result = mQuery ($qry);

		$return = "<select name=\"$fieldName\" $otherProperties><option value=\"\"></option>\n";
		while (list ($name, $value) = mysql_fetch_row ($result)) {
			$selected = '';
			if (strpos($defaultValues, ",". $value .",") !== false) $selected = ' selected="selected"';
			$return .= '	<option value="'. htmlentities($value) .'"'. $selected .'>'. htmlentities($name) .'</option>'."\n";
		}
		$return .= '</select>';

		return $return;
	}

	//--------------------------------------------------------------------------------
	// Syntax:	getCRBox( SQL, fieldType, fieldName, defaultValues, wrap, otherProperties)
	//
	// Purpose:	This procedure takes as input an SQL query, a field type, a field
	//			name, default value(s) for the field, and wrap number. It returns an
	//			HTML input element based on these inputs. The query is assumed to
	//			return two columns: Value and Name, which correspond to the input box
	//			value and displayed name, respectively.
	//
	// Elements:	SQL
	//				Any valid SQL query that returns two columns: Name and Value.
	//			fieldType
	//				One of 'radio' or 'checkbox'
	//			fieldName
	//				Indicates the input elements 'name'
	//			defaultValues
	//				If defined, will be the default values for the input box.
	//			wrap
	//				Indicates the number of columns in which to split the input
	//				element.
	//			otherProperties
	//				Allows inclusion of MULTIPLE, READONLY, etc.
	//
	// Returns:	This procedure does not directly return a value, but instead outputs
	//			HTML code to create an input box.
	//--------------------------------------------------------------------------------
	function getCRBox($qry, $fieldType, $fieldName, $defaultValues, $wrap, $otherProperties) {

		// If otherProperties contains the string %%default%%, then replace with the defaultValues.  This is used
		// to provide a means of weeding out inactive options for table data.
		if (strlen($defaultValues) == 0) {
			$qry = str_replace('%%default%%', 'null', $qry);
		} else {
			$qry = str_replace('%%default%%', $defaultValues, $qry);
		}

		$result = mQuery ($qry);

		// By adding a comma to the end of the defaultValues string, this will ensure that we don't unintentionally match a substring of one of the default
		// values.  For example: defaultValues = "5,10": checking against Instr("1") would return 3 (incorrect), but Inst(",1,") would return 0 (correct).
		$defaultValues = ','. trim($defaultValues) .',';

		// Check for width
		if (strpos($otherProperties, 'width="100%"') > 0) $width = ' width="100%"';

		$count = 1;
		$return = '<table'. $width .' cellpadding="0" cellspacing="0" border="0"><tr>';
		while (list ($name, $value) = mysql_fetch_row ($result)) {
			$checked = "";
			if (strpos($defaultValues, ','. $value .',') !== false) $checked = 'CHECKED';

			$return .= '<td nowrap><input type="'. $fieldType .'" name="'. $fieldName .'" value="'. $value .'" '. $checked .' '. $otherProperties .'>'. $name .'</td>';

			if ($count++ % $wrap == 0) $return .= '</tr>';
		}
		$return .= '</table>';

		return $return;
	}

	function getHTMLMessage($to, $message, $unsub_code = '') {
		global $admindata;

		$unsub_html = ($unsub_code == '') ? 'This is a one-time email and you have not been added to any list.' : "To unsubscribe, click <a href=\"". FULL_URL_UNSUBSCRIBE ."?u=$unsub_code\">here</a>.<br>\n<br>\n";

		$m = "<html>\n".
		"<body>\n".
			$message ."<br><br>\n".
			"----------------------------------------<br>\n".
			"This email was sent to: <b>". htmlentities($to) ."</b><br>\n".
			"<br>\n".
			"You are receiving this email because you are a registered member of HDTV Magazine and you have chosen to receive these types of updates per <a href=\"". FULL_URL_PROFILE ."\">your profile</a>.<br>\n".
			"<br>\n".
			"For best viewing of future emails, please add ". $admindata['email_reply_address'] ." to your Safe Senders List or Address Book.<br>\n".
			"<br>\n".
			"This email was sent by: <b>". $admindata['company_name'] ."</b><br>\n".
			$admindata['street_address'] ."<br>\n".
			$admindata['city'] .", ". $admindata['state_province'] .", ". $admindata['zip_postal_code'] ." ". $admindata['country'] ."<br>\n".
			"<br>\n".
			$unsub_html .
			"<br>\n".
		"</body>\n".
		"</html>\n";

		return $m;
	}

	function getTextMessage($to, $message, $unsub_code = '') {
		global $admindata;

		$unsub_text = ($unsub_code == '') ? 'This is a one-time email and you have not been added to any list.' : 'Unsubscribe: '. FULL_URL_UNSUBSCRIBE ."?u=$unsub_code\n";

		$m = "$message\n\n".
		"----------------------------------------\n".
		"This email was sent to: $to\n".
		"\n".
		"You are receiving this email because you are a registered member of HDTV Magazine and you have chosen to receive these types of updates per your profile:\n".
		FULL_URL_PROFILE ."\n".
		"\n".
		"For best viewing of future emails, please add ". $admindata[email_reply_address] ." to your Safe Senders List or Address Book.\n".
		"\n".
		"This email was sent by: ". $admindata[company_name] ."\n".
		$admindata[street_address] ."\n".
		$admindata[city] .", ". $admindata[state_province] .", ". $admindata[zip_postal_code] ." ". $admindata[country] ."\n".
		"\n".
		$unsub_text .
		"\n";

		return $m;
	}

	function getTabHeader($tabs) {
		$tab_header = '<div id="tabs"><ul>';
		foreach ($tabs as $label => $url) {
			if (basename($_SERVER[SCRIPT_NAME]) == strleft(basename($url) .'?', '?')) {
				$tab_header .= "<li><a class='tab-selected' href='#' title='$label'><span>$label</span></a></li>";
			} else {
				$tab_header .= "<li><a href='$url' title='$label'><span>$label</span></a></li>";
			}
		}
		$tab_header .= '</ul></div>';

		return $tab_header;
	}

	function js_alert($text) {
		echo '<html><body><script language="javascript" type="text/javascript">'.
		'	alert(\''. $text .'\');'.
		'</script></body></html>';
	}

	function js_back($text = '') {
		echo '<html><body><script language="javascript" type="text/javascript">';

		if ($text != '') echo 'alert(\''. $text .'\');';

		echo '	history.back();'.
		'</script></body></html>';
	}

	function js_close() {
		echo '<html><body><script language="javascript" type="text/javascript">'.
		'	window.close();'.
		'</script></body></html>';
		exit;
	}

	function js_close_reload() {
		echo '<html><body><script language="javascript" type="text/javascript">'.
		'	opener.location.reload();'.
		'	window.close();'.
		'</script></body></html>';
		exit;
	}

	function js_redirect($url = '/index.php', $alert_text = '') {
		echo '<html><body><script language="javascript" type="text/javascript">';

		if ($alert_text != '') echo 'alert(\''. $alert_text .'\');';

		echo 'location.href = \''. $url .'\';'.
		'</script></body></html>';
		exit;
	}

	function js_replace($url) {
		echo '<html><body><script language="javascript1.1" type="text/javascript">'.
		'		location.replace(\''. $url .'\');'.
		'</script></body></html>';
		exit;
	}

	function prompt_login($url = '') {
		// If the user is logged in, redirect to the Access Denied page, otherwise prompt for login
		if ($user->data['user_id'] > 0) {
			access_denied();
		} else {
			js_replace('/login.php?redirect='. $url);
		}
	}

	function strleft($s1, $s2) {
		$p = strpos($s1, $s2);
		if ($p === false) {
			return '';
		} else {
			return substr($s1, 0, $p);
		}
	}

	function strright($s1, $s2) {
		$p = strpos($s1, $s2);
		if ($p === false) {
			return '';
		} else {
			return substr($s1, $p + strlen($s2));
		}
	}

	function strleftback($s1, $s2) {
		$p = strrpos($s1, $s2);
		if ($p === false) {
			return '';
		} else {
			return substr($s1, 0, $p);
		}
	}

	function strrightback($s1, $s2) {
		$p = strrpos($s1, $s2);
		if ($p === false) {
			return '';
		} else {
			return substr($s1, $p+strlen($s2));
		}
	}

	// user defined error handling function
	function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
		// timestamp for the error entry
		$dt = date("r");

		$errortype = array (
						E_ERROR			 => "Error",
						E_WARNING		  => "Warning",
						E_PARSE			 => "Parsing Error",
						E_NOTICE			 => "Notice",
						E_CORE_ERROR		=> "Core Error",
						E_CORE_WARNING	 => "Core Warning",
						E_COMPILE_ERROR  => "Compile Error",
						E_COMPILE_WARNING => "Compile Warning",
						E_USER_ERROR		=> "User Error",
						E_USER_WARNING	 => "User Warning",
						E_USER_NOTICE	 => "User Notice",
						E_STRICT			 => "Runtime Notice"
		);

		// set of errors for which a var trace will be saved
		$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

		$err = "<errorentry>\n";
		$err .= "\t<datetime>" . $dt . "</datetime>\n";
		$err .= "\t<errornum>" . $errno . "</errornum>\n";
		$err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
		$err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
		$err .= "\t<scriptname>" . $filename . "</scriptname>\n";
		$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

		if (in_array($errno, $user_errors)) {
			 $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
		}
		$err .= "</errorentry>\n\n";

		// save to the error log, and e-mail me if there is a critical user error
//		error_log($err, 3, "/usr/local/php4/error.log");
		if ($errno == E_USER_ERROR) {
			 mail("shane@hdtvmagazine.com", "Critical User Error", $err);
		}
	}
?>
