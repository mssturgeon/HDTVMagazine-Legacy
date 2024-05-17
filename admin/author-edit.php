<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$author_id = isset($_POST['author_id']) ? $_POST['author_id'] : exit;
		$title = addslashes($_POST['title']);
		$bio = nl2br(addslashes($_POST['bio']));
		$bio_short = nl2br(addslashes($_POST['bio_short']));
		$sql = "
		REPLACE INTO aux_author (author_id, title, user_id, channel, viglink_source, viglink_suppress, channel_name, amazon_tracking_id, revshare, img, bio, bio_short, include)
		VALUES ('$author_id', '$title', '{$_POST['user_id']}', '{$_POST['channel']}', '{$_POST['viglink_source']}', '{$_POST['viglink_suppress']}', '{$_POST['channel_name']}', '{$_POST['amazon_tracking_id']}',
			'{$_POST['revshare']}', '{$_POST['img']}', '$bio', '$bio_short', '{$_POST['include']}')";
		$result = mQuery($sql);

		js_redirect('authors.php');
		exit;
	} else {
		$author_id = isset($_GET['author_id']) ? $_GET['author_id'] : '';
		$sql = "
		SELECT m.author_id, m.author_name, a.*
		FROM mt_author m LEFT JOIN aux_author a ON m.author_id = a.author_id
		WHERE m.author_id = '$author_id'";
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result);
	}

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Edit Author (<?=$row['author_name']?>)</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="author_id" value="<?=$author_id?>">

		<fieldset>
			<legend>Author Information</legend>
			<label for="name">Name</label>
			<div id="name"><?=$row['author_name']?></div>
			<br />
			<label for="user_id">User ID</label>
			<input type="text" class="inputText" name="user_id" value="<?=$row['user_id']?>" size="10"><br />
			<br />
			<label for="user_id">Include in Authors Box?</label>
			<input type="text" class="inputText" name="include" value="<?=$row['include']?>" size="10"><br />
			<br />
			<label for="channel">Channel Name</label>
			<input type="text" class="inputText" name="channel_name" value="<?=$row['channel_name']?>" size="25" maxlength="30"><br />
			<br />
			<label for="channel">Channel #</label>
			<input type="text" class="inputText" name="channel" value="<?=$row['channel']?>" size="10"><br />
			<br />
			<label for="amazon_tracking_id">Amazon Tracking ID</label>
			<input type="text" class="inputText" name="amazon_tracking_id" value="<?=$row['amazon_tracking_id']?>" size="20"><br />
			<br />
			<label for="viglink_source">VigLink Source ID</label>
			<input type="text" class="inputText" name="viglink_source" value="<?=$row['viglink_source']?>" size="32"><br />
			<label for="viglink_suppress">Suppress VigLink</label>
			<input type="text" class="inputText" name="viglink_suppress" value="<?=$row['viglink_suppress']?>" size="32"><br />
			<br />
			<label for="channel">Rev-Share</label>
			<input type="text" class="inputText" name="revshare" value="<?=$row['revshare']?>" size="5"><br />
			<br />
			<label for="title">Title</label>
			<input type="text" class="inputText" name="title" value="<?=stripslashes($row['title'])?>" size="50" maxlength="100"><br />
			<br />
			<label for="img">Portrait</label>
			<input type="text" class="inputText" name="img" value="<?=$row['img']?>" size="30" maxlength="30"><br />
			<br />
		</fieldset>

		<fieldset>
			<legend>Bio Information</legend>
			<label for="bio_short">Short Bio</label>
			<textarea name="bio_short" rows="10"><?=stripslashes(preg_replace('/<br\\s*?\/??>/i', "", $row['bio_short']))?></textarea>
			<br />
			<label for="bio">Bio</label>
			<textarea name="bio" rows="20"><?=stripslashes(preg_replace('/<br\\s*?\/??>/i', "", $row['bio']))?></textarea>
		</fieldset>

		<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
	</form>
</body>
</html>
