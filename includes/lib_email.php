<?
	function getBlogContent($blog_id, $anchor, $title, $img, $rss, $days) {
		global $online_url, $base_url, $base_img_host;
		$yesterday = date('Y-m-d', time() - 24 * HOURS);
		$days = $days - 1;

		$sql = "
		SELECT e.entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name, category_label, topic_replies, aux.topic_id
		FROM mt_entry e, mt_author a, mt_category c, mt_placement p, aux_mt_entry aux
		LEFT JOIN ". TOPICS_TABLE ." t ON (aux.topic_id = t.topic_id)
		WHERE entry_blog_id = $blog_id
			AND entry_status = 2
			AND entry_author_id = a.author_id
			AND e.entry_id = aux.entry_id
			AND e.entry_id = p.placement_entry_id
			AND p.placement_category_id = c.category_id
			AND p.placement_is_primary
			AND entry_created_on BETWEEN '$yesterday' - INTERVAL $days DAY AND '$yesterday'
		ORDER BY entry_created_on DESC";
//			AND entry_created_on > NOW() - INTERVAL $days DAY
		$result = mQuery($sql);
		$num_result = mysql_num_rows($result);
		$content['count'] = $num_result;
		$content['heading'] = '<li><a href="'. $online_url .'#'. $anchor .'">'. $title .' ('. $num_result .')</a></li>';
		if ($num_result > 0) {
			$rss_tag = '<a href="http://feeds.hdtvmagazine.com/hdtv-'. $rss .'"><img src="'. $base_img_host .'/images/livemark.png" alt="RSS" align="right" /></a>';
			$content['item'] = <<<EOT
<a name="$anchor"></a>
<div class="item"><span class="corners-top"><span></span></span>
	<h2>
		$rss_tag
		<span style="width:40px"><img src="$base_img_host/images/{$img}" align="absmiddle" style="padding-right:10px" /></span>
		<a href="$base_url/$anchor/index.php">$title</a>
	</h2>
	<ul>
EOT;
			while ($row = mysql_fetch_assoc($result)) {
				$ts = strtotime($row['entry_created_on']);
				$y = date('Y', $ts);
				$m = date('m', $ts);
				$date = getDateString($ts);
				$link = BASE_URL ."/$anchor/$y/$m/". dirify($row['entry_title']) .".php";

				if ($row['topic_id'] != '') {
					if ($row['topic_replies'] > 0) {
						$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
					} else {
						$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
					}
				}

				$content['item'] .= "<li>\n".
				'<h3><a href="'. $link .'">'. $row['entry_title'] ."</a></h3>\n".
				'<h4 style="float:right;"><img src="'. $base_img_host .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
				'<h4><span style="text-transform:none">By</span> '. $row['author_name'] ." &bull; \n".
				'<span style="color:#800000"> '. $date ."</span> &bull; \n".
				"$row[category_label]</h4></li>\n";
			}
			$content['item'] .= <<<EOT
	</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
		}

		return $content;
	}

	function getForumContent($days) {
		global $online_url, $base_url, $base_img_host;
//		$today = date('Y-m-d', time());
		$yesterday = date('Y-m-d', time() - 24 * HOURS);
		$days = $days - 1;

		$sql = "
		SELECT topic_title, t.topic_id, username as post_author, post_time, post_id, forum_name, t.topic_id, t.topic_replies
		FROM ". TOPICS_TABLE ." t, ". USERS_TABLE ." u, ". POSTS_TABLE ." p, ". FORUMS_TABLE ." f, aux_phpbb_forums af
		WHERE
			t.forum_id = af.forum_id
			AND af.exclude_general = 0
			AND p.poster_id = u.user_id
			AND p.forum_id = f.forum_id
			AND t.topic_id = p.topic_id
			AND t.topic_last_post_id = p.post_id
			AND FROM_UNIXTIME(post_time, '%Y-%m-%d') BETWEEN '$yesterday' - INTERVAL $days DAY AND '$yesterday'
		ORDER BY post_time DESC";
//		echo $sql;
//			AND FROM_UNIXTIME(post_time, '%Y-%m-%d') BETWEEN '$yesterday' - INTERVAL $days DAY AND '$yesterday'
//			AND FROM_UNIXTIME(post_time) > NOW() - INTERVAL $days DAY
		$res_discussions = mQuery($sql);
		$num_discussions = mysql_num_rows($res_discussions);
		$content['count'] = $num_discussions;
		$content['heading'] = '<li><a href="'. $online_url .'#forum">Forum Discussions ('. $num_discussions .')</a></li>';
		if ($num_discussions > 0) {
			$content['item'] = <<<EOT
<a name="forum"></a>
<div class="item"><span class="corners-top"><span></span></span>
	<h2>
		<a href="http://feeds.hdtvmagazine.com/hdtv-forum"><img src="$base_img_host/images/livemark.png" alt="RSS" align="right" /></a>
		<span style="width:40px"><img src="$base_img_host/images/i_forum.gif" align="absmiddle" style="padding-right:10px" /></span>
		<a href="$base_url/forum/index.php">Forum Discussions</a>
	</h2>
	<ul>
EOT;
			while ($row = mysql_fetch_assoc($res_discussions)) {
				$last_post = getDateString($row['post_time']);
				$title = html_entity_decode($row['topic_title']);

				if ($row['topic_id'] != '') {
					if ($row['topic_replies'] > 0) {
						$comments = '<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
					} else {
						$comments = '<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
					}
				}

				$content['item'] .= "<li>\n".
				'<h3><a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">'. $title ."</a></h3>\n".
				'<h4 style="float:right;"><img src="'. $base_img_host .'/images/icon_comments.gif" alt="" /> '. $comments ."</h4>\n".
				'<h4><span style="text-transform:none">By</span> '. $row['post_author'] ." &bull; \n".
				'<span style="color:#800000"> '. $last_post ."</span> &bull; \n".
				"{$row['forum_name']}</h4></li>\n";
			}
			$content['item'] .= <<<EOT
	</ul>
<span class="corners-bottom"><span></span></span></div>
EOT;
		}

		return $content;
	}

	function getOtherNewsContent($days) {
		global $online_url, $base_url, $base_img_host;
		$yesterday = date('Y-m-d', time() - 24 * HOURS);
		$days = $days - 1;

		$sql = "
		SELECT DISTINCT id, pubDate, link, title, source, category_label, r.topic_id, t.topic_replies
		FROM mt_category c, hdtv_rss r
		LEFT JOIN ". TOPICS_TABLE ." t ON r.topic_id = t.topic_id
		WHERE rank > 0
			AND r.category_id = c.category_id
			AND FROM_UNIXTIME(pubDate, '%Y-%m-%d') BETWEEN '$yesterday' - INTERVAL $days DAY AND '$yesterday'
		ORDER BY pubDate DESC";
//			AND FROM_UNIXTIME(pubDate) > NOW() - INTERVAL $days DAY
		$res_news = mQuery($sql);
		$num_news = mysql_num_rows($res_news);
		$content['count'] = $num_news;
		$content['heading'] = '<li><a href="'. $online_url .'#forum">Other News ('. $num_news .')</a></li>';
		if ($num_news > 0) {
			$content['item'] = <<<EOT
<a name="forum"></a>
<div class="item"><span class="corners-top"><span></span></span>
	<h2>
		<a href="http://feeds.hdtvmagazine.com/hdtv-news"><img src="$base_img_host/images/livemark.png" alt="RSS" align="right" /></a>
		<span style="width:40px"><img src="$base_img_host/images/i_news.gif" align="absmiddle" style="padding-right:10px" /></span>
		<a href="$base_url/news/index.php">Other News</a>
	</h2>
	<ul>
EOT;
			while ($row = mysql_fetch_assoc($res_news)) {
				$date = getDateString($row['pubDate']);
				$link = BASE_URL .'/news/story.php?title='. dirify($row['title']) .'&amp;id='. $row[id];

				if ($row['topic_replies'] > 0) {
					$comments = '<img src="'. $base_img_host .'/images/icon_comments.gif" alt="" /> '.
					'<a href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Comments</a> ('. $row['topic_replies'] .')';
				} else {
					if ($row['topic_id'] > 0) {
						$comments = '<img src="'. $base_img_host .'/images/icon_comments.gif" alt="" /> '.
						'<a class="red" href="'. BASE_URL .'/forum/viewtopic.php?t='. $row['topic_id'] .'">Post First Comment</a>';
					} else {
						$comments = '<img src="'. $base_img_host .'/images/icon_comments.gif" alt="" /> '.
						'<a class="red" href="'. BASE_URL .'/news/post_comment.php?id='. $row['id'] .'">Post First Comment</a>';
					}
				}

				$content['item'] .= "<li>\n".
				'<h3><a href="'. $link .'">'. $row['title'] ."</a></h3>\n".
				'<h4 style="float:right;">'. $comments ."</h4>\n".
				'<h4><span style="text-transform:none">By</span> '. $row['source'] ." &bull; \n".
				'<span style="color:#800000"> '. $date ."</span> &bull; \n".
				"{$row['category_label']}</h4></li>\n";
			}
			$content['item'] .= <<<EOT
		</ul>
	<span class="corners-bottom"><span></span></span></div>
EOT;
		}

		return $content;
	}
?>