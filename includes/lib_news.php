<?
	/***************************************
	Included in news pages.
	- getFormattedStory
		Given an id from MovableType, builds and returns an entry listing based on the requested format type (excerpt, or title-only).
	***************************************/

	$base_url = 'http://'. SERVER_NAME;

	echo '<script type="text/javascript">'.
		'function submitForm(id) {'.
			'document.post_comment.id.value = id;'.
			'document.post_comment.submit();'.
		'}'.
	'</script>'.
	'<form name="post_comment" method="post" action="'. $base_url .'/news/post_comment.php">'.
		'<input type="hidden" name="id" value="">'.
		'<!--input type="hidden" name="debug" value=""-->'.
	'</form>';

	function getFormattedStory($entry, $format) {
		/* Given an id from MovableType, builds and returns an entry listing based on the requested format type (excerpt, or title-only). */
		global $base_url;

		# Start DIV
		$formatted = '<div class="entry" onmouseover="this.style.backgroundColor = \'#ecf3f7\';"'.
		' onmouseout="this.style.backgroundColor = \'transparent\';">'.
		'<span class="corners-top"><span></span></span>';

		if ($entry[topic_replies] > 0) {
			$comments = '<img src="'. $base_url .'/images/icon_comments.gif" alt="" /> '.
			'<a href="'. $base_url .'/forum/viewtopic.php?t='. $entry[topic_id] .'">Comments</a> ('. $entry[topic_replies] .')';
		} else {
			if ($entry[topic_id] > 0) {
				$comments = '<img src="'. $base_url .'/images/icon_comments.gif" alt="" /> '.
				'<a class="red" href="'. $base_url .'/forum/viewtopic.php?t='. $entry[topic_id] .'">Post First Comment</a>';
			} else {
				$comments = '<img src="'. $base_url .'/images/icon_comments.gif" alt="" /> '.
				'<a class="red" href="javascript:submitForm('. $entry[id] .');" onmouseover="window.status=\'Post First Comment\';return true;" onmouseout="window.status=\'\';return true;">Post First Comment</a>';
			}
		}

		switch (true) {
		case $format == 'full':
			$formatted .= '<h4>'. implode(' &bull; ', $entry[catlinks]) .'</h4>'.
			'<h2><a href="'. stripslashes($entry[link]) .'">'. stripslashes($entry[title]) .'</a></h2>'.
			'<h4><span style="text-transform:none">By</span> '. $entry[author] .' &bull; <span style="color:#800000"> '. $entry[date] .'</span></h4>'.
			'<!--h4>Published in: </h4-->'.
			'<div class="entry_text">'. nl2br(stripslashes($entry[excerpt])) .'</div>'.
			'<div class="footer">'.
				'<a href="'. stripslashes($entry[link]) .'">Read Original Story</a> &bull; '.
				$comments .
 				'<!--img src="'. $base_url .'/images/icon_recommend.gif" alt="" /> <a href="">Recommend</a> ()-->'.
			'</div>';
 			break;
		case $format == 'excerpt':
			# Limit long excerpts
//			if (strlen($entry['excerpt']) > 1000) $entry['excerpt'] = trim(substr($entry['excerpt'], 0, 1000) .' ...');
			if (strlen($entry['excerpt']) > 500) $entry['excerpt'] = trim(substr(strip_tags($entry['excerpt']), 0, 500) .' ...');

			if ($entry[catlinks] != '') $catlinks =  '<h4>'. implode(' &bull; ', $entry[catlinks]) .'</h4>';

			$formatted .= $catlinks .
			'<h2><a href="'. stripslashes($entry[link]) .'">'. stripslashes($entry[title]) .'</a></h2>'.
			'<h4><span style="text-transform:none">By</span> '. $entry[author] .' &bull; <span style="color:#800000"> '. $entry[date] .'</span></h4>'.
			'<!--h4>Published in: </h4-->'.
			'<div class="entry_text">'. str_replace(". ", ". \n", nl2br(stripslashes($entry[excerpt]))) .'</div>'.
			'<div class="footer">'.
				'<a href="'. stripslashes($entry[link]) .'">Read Story</a> &bull; '.
				$comments .
 				'<!--img src="'. $base_url .'/images/icon_recommend.gif" alt="" /> <a href="">Recommend</a> ()-->'.
			'</div>';
			break;
		case $format == 'title-only':
			if ($entry[catlinks] != '') $catlinks =  ' &bull; '. implode(' &bull; ', $entry[catlinks]);

			$formatted .= '<h3><a href="'. stripslashes($entry[link]) .'">'. stripslashes($entry[title]) .'</a></h3>'.
			'<h4 style="float:right;">'. $comments .'</h4>'.
			'<h4>'.
				'<span style="text-transform:none">By</span> '. $entry[author] .' &bull; '.
				'<span style="color:#800000"> '. $entry[date] .'</span>'.
				$catlinks .
			'</h4>'.
			'<!--h4>Published in: </h4-->';
			break;
		}

		$formatted .= '<span class="corners-bottom"><span></span></span></div>'.
		'<div class="dottedline"></div>';

		return $formatted;
	}

?>
