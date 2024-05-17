<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$submit_action = isset($_GET['submit_action']) ? $_GET['submit_action'] : '';
	if ($submit_action == 'add') {
		$url = trim($_GET['url']);
		$title = addslashes($_GET['feed_title']);
		$link = $_GET['feed_link'];
		$description = addslashes($_GET['feed_desc']);
		$sql = "INSERT IGNORE INTO feeds (type, xml_link, title, link, description)
		VALUES ('{$_GET['type']}', '$url', '$title', '$link', '$description')";
		$result = mQuery($sql);
	} elseif ($submit_action == 'lookup') {
		require_once(BASE_DIR .'/includes/lib_rss.php');
		$url = trim(urldecode($_GET['url']));
		if ($url != '') {
			$contents = @file_get_contents($url);
			if ($contents !== FALSE) {
				$enc_contents = utf8_encode($contents);
				$xml = new SimpleXMLElement( $enc_contents );
				$channel = getChannelData($xml);
				$title =  addslashes($channel['title']);
				$link = $channel['link'];
				$description = ($channel['description'] == '') ? $title : addslashes($channel['description']);
				echo "{title:'$title', link:'$link', description:'$description'}";
			} else {
				echo "Sorry: It's not possible to reach RSS file $url\n<br />";
			}
		} else {
			echo 'URL is blank';
		}
		exit;
	} elseif ($submit_action == 'delete') {
		$sql = "DELETE FROM feeds WHERE id = '{$_GET['feed_id']}'";
		$result = mQuery($sql);
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Manage Feeds</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<style>
		.inactive {background-color:#cccccc; font-style:italic; color:#808080}
	</style>
	<script type="text/javascript">
		function delete_feed(fid) {
			with (document.forms['frm']) {
				submit_action.value = 'delete';
				feed_id.value = fid;
				submit();
			}
		}

		function getTitle(url) {
			ajaxURL = document.location.href +'?submit_action=lookup&url='+ escape(url);
//			alert(ajaxURL);
			ajaxSendRequest(ajaxURL, 'updateTitle');
		}

		function updateTitle() {
			if (ajaxReq.readyState == 4) {
				if (ajaxReq.status == 200) {
//					alert(ajaxReq.responseText);
					var oReq = eval('('+ ajaxReq.responseText +')');
					alert(oReq.title);
					alert(oReq.link);
					alert(oReq.description);
					document.forms['frm'].feed_title.value = oReq.title;
					document.forms['frm'].feed_link.value = oReq.link;
					document.forms['frm'].feed_desc.value = oReq.description;
				} else {
					alert('Error: HTTP Status '+ajaxReq.status+'. There was a problem with the request.');
					// Redirect to feedback form?
				}
			}
		}
	</script>
</head>
<body onload="document.forms['frm'].elements['url'].focus()">
	<div id="body_container">

		<h1>Manage RSS Feeds</h1>
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td style="vertical-align:top;padding-right:10px;">
				<? require('pending-sidemenu.php'); ?>
			</td><td style="vertical-align:top;">
				<div class=""><?
					if ($submit_action = 'delete') {
						echo "Feed id {$_GET['feed_id']} deleted.";
					}
				?></div>
				<fieldset>
					<legend>Add Feed</legend>
					<form method="get" name="frm" action="<?=PHP_SELF?>">
						<input type="hidden" name="submit_action" value="add" />
						<input type="hidden" name="feed_id" value="" />

						<label for="url">Feed URL:</label>
						<input type="text" size="50" name="url" id="url" value="" onblur="getTitle(this.value)" />
						<!--textarea name="url"></textarea--><br />

						<label for="url">Feed Title:</label>
						<input type="text" size="50" name="feed_title" id="feed_title" value="" /><br />

						<label for="url">Feed Link:</label>
						<input type="text" size="50" name="feed_link" id="feed_link" value="" /><br />

						<label for="url">Feed Description:</label>
						<textarea name="feed_desc"></textarea><br />

						<label for="type">Type</label>
						<select name="type" id="type">
							<option value="N">News
							<option value="R">Reviews
						</select><br />

						<input type="submit" value="Add Feed" />
					</form>
				</fieldset>

				<fieldset>
					<legend>Manage Feeds</legend>
					<table class="type1b" id="table" width="100%">
						<tr>
							<td class="type1b_header">ID</td>
							<td class="type1b_header">Type</td>
							<td class="type1b_header">Title</td>
							<td class="type1b_header">Last Published</td>
							<td class="type1b_header">Last Accepted</td>
						</tr>
						<?
							$sql = "SELECT * FROM feeds ORDER BY title";
							$result = mQuery($sql);
							while ($row = mysql_fetch_assoc($result)) {
								$inactive = ($row['status'] == 0) ? 'class="inactive"' : '';
								echo '<tr '. $inactive .'>'.
								'	<td class="grid" align="right" nowrap>'. $row['id'] .'</td>'.
								'	<td class="grid" align="center" nowrap>'. $row['type'] .'</td>'.
								'	<td class="grid" nowrap>'.
									'<a target="_blank" href="'. $row['xml_link'] .'">'. stripslashes($row['title']) .'</a>'.
									' [ <a target="_blank" href="'. $row['link'] .'">Website</a> ]'.
									' [ Edit ]'.
									' [ <a class="red" href="javascript:void delete_feed('. $row['id'] .');">Delete</a> ]'.
								'	</td><td class="grid">'. $row['last_item'] .'</td>'.
								'	<td class="grid">'. $row['last_item_accepted'] .'</td>'.
								'</tr>';
							}
						?>
					</table>
				</fieldset>
			</td>
		</tr></table>
	</div>
</body>
</html>