<?
	header('Cache-Control: no-store');
	require('../global.php');
	if (!access(ACCESS_ADMIN_NEWS)) prompt_login(PHP_SELF);

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	if ($id != '') {
		header('Content-type: text/plain');

		$sql = "
		UPDATE hdtv_rss
		SET rank = $_GET[rank],
		category_id = '$_GET[category_id]'
		WHERE id = $id";
		$db->sql_query($sql);

		$sql = "
		UPDATE feeds
		SET last_item_accepted = NOW()
		WHERE id = '$_GET[feed_id]'";
		$db->sql_query($sql);

		echo $id;
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - News Admin</title>
	<? require(BASE_DIR .'/includes/common_header.php');?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8/scriptaculous.js"></script>
	<script language="JavaScript" type="text/javascript">
		function rank(id, rankval) {
			category_id = document.getElementById("category_id_"+id).value;
			feed_id = document.getElementById("feed_id_"+id).value;
//			return ajax_makeRequest(document.location +'?id='+ id +'&feed_id='+ feed_id +'&rank='+ rankval +'&category_id='+ category_id, '');
			return ajaxSendRequest(document.location +'?id='+ id +'&feed_id='+ feed_id +'&rank='+ rankval +'&category_id='+ category_id, 'rankNews');
		}

		function rankNews() {
			if (ajaxReq.readyState == 4) {
				if (ajaxReq.status == 200) {
					id = ajaxReq.responseText;
					Effect.Fade('div_'+id);
					Effect.BlindUp('divbu_'+id);
					n = document.getElementById("number");
					ns = parseInt(n.value) - 1;
					if (ns == 0) {
						document.location.reload();
					} else {
						n.value = ns.toString();
					}
				} else {
					alert('There was a problem with the request.');
				}
			}
		}
	</script>
</head>
<body>
	<div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
	?>

	<h1>HDTV News Admin</h1>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="vertical-align:top;padding-right:10px;">
			<? require('pending-sidemenu.php'); ?>
		</td><td style="vertical-align:top;">
			<form action="<?=PHP_SELF?>" method="post" name="frm">
				<input type="hidden" name="action" value="submit">
				<?
					$sql = "
					SELECT id, feed_id, category_id, link, title, source, pubDate, description, rank
					FROM hdtv_rss
					WHERE rank = 0
						OR (rank > 0 AND category_id = 0)
					ORDER BY pubDate ASC LIMIT 50";
					$res_news = $db->sql_query($sql);
					if (mysql_num_rows($res_news) == 0) {
						echo 'No stories to review.';
					} else {
						echo '<input type="hidden" name="number" id="number" value="'. mysql_num_rows($res_news) .'">';

						$x = 0;
						while ($row = $db->sql_fetchrow($res_news)) {
							echo '<input type="hidden" id="feed_id_'. $row['id'] .'" name="feed_id_'. $row['id'] .'" value="'. $row['feed_id'] .'">';
							$class = ($x % 2 == 0) ? 'item' : 'item_odd';
							echo '<div id="divbu_'. $row['id'] .'"><div class="'. $class .'" id="div_'. $row['id'] .'"><span class="corners-top"><span></span></span>'.
								'<input style="float:right" type="button" class="button_red" name="reject" value="REJECT" onclick="rank('. $row['id'] .', -1)" />';
							if ($row[rank] > 0) echo '<div style="color:#800000; font-weight:bold">This story has already been accepted, but needs a category</div>';
							echo '<a target="_blank" href="'. $row['link'] .'">'. stripslashes($row['title']) .'</a> - '.
								stripslashes($row['source']) .'</b> - '. gmdate('M j, Y g:ia', $row['pubDate'] + $user->data['time_zone_offset']) .'<br>'.
								strip_tags(stripslashes($row['description'])) .'<br />'.
								'<b>Category:</b> '. getSelectBox("SELECT category_label, category_id FROM mt_category WHERE category_blog_id = 1 ORDER BY category_label", "category_id_". $row[id], $row[category_id], ' id="category_id_'. $row[id] .'"') ."<br />\n".
								'<b>Tags:</b> <input type="text" name="tags" value="" />'.
								'<input type="button" class="button_green" name="accept" value="ACCEPT" onclick="rank('. $row['id'] .', 1)" />'.
							'<span class="corners-bottom"><span></span></span></div></div>';
							$x++;
						}
					}
				?>
			</form>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer.php'); ?>
	</div>
</body>
</html>
