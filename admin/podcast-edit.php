<?
	require('../global.php');
	
	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action == 'submit') {
		$filepath = PODCAST_DIR .'/'. $_POST[filename];
		$length = filesize($filepath);

		$url = 'http://'. SERVER_NAME . PODCAST_PATH .'/'. $_POST[filename];

		$pubDate = strtotime($_POST[pubDate]);
		$summary = addslashes($_POST[summary]);
		if ($_POST[id] == '') { # New Podcast;
			$sql = "
			INSERT INTO podcast (title, subtitle, author, summary, filename, url, length, type, pubDate, duration, keywords)
			VALUES ('$_POST[title]', '$_POST[subtitle]', '$_POST[author]', '$summary', '$_POST[filename]', '$url', '$length', '$_POST[type]', '$pubDate', '$_POST[duration]', '$_POST[keywords]')";
		} else { # Update podcast
			$sql = "
			UPDATE podcast SET
			title = '$_POST[title]'
			,subtitle = '$_POST[subtitle]'
			,author = '$_POST[author]'
			,summary = '$summary'
			,filename = '$_POST[filename]'
			,url = '$url'
			,length = '$length'
			,type = '$_POST[type]'
			,pubDate = '$pubDate'
			,duration = '$_POST[duration]'
			,keywords = '$_POST[keywords]'
			WHERE id = '$_POST[id]'";
		}
		$db->sql_query($sql);
		js_close('Podcast Added/Updated');
		exit;
	}

	$id = ($_GET[id] == 'undefined') ? '' : $_GET[id];
	if ($id != '') {
		$sql = "SELECT * FROM podcast WHERE id = '$id'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>New Podcast</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body style="margin:10px">
	<form enctype="multipart/form-data" method="post" action="<?=PHP_SELF?>" name="frmNew">
		<input type="hidden" name="action" value="submit">
		<input type="hidden" name="id" value="<?=$id?>">
		<table class="type1b" style="width:100%" align="center"><tr>
			<td class="type1b_header">Add New</td>
		</tr><tr>
			<td>
				<table class="bare" cellpadding="3" cellspacing="0">
					<tr>
						<td class="inputLabel">Title:</td>
						<td><input type="text" class="inputText" name="title" value="<?=@$row[title]?>" size="40" /></td>
					</tr><tr>
						<td class="inputLabel">Sub-Title:</td>
						<td><input type="text" class="inputText" name="subtitle" value="<?=@$row[subtitle]?>" size="40" /></td>
					</tr><tr>
						<td class="inputLabel">Author:</td>
						<td><input type="text" class="inputText" name="author" value="<?=@$row[author]?>" size="30" /></td>
					</tr><tr>
						<td class="inputLabel" style="vertical-align:top">Summary:</td>
						<td>
<textarea name="summary" style="width:100%; height:100px"><?=@$row[summary]?>
</textarea>
						</td>
					</tr><tr>
						<td class="inputLabel">File:</td>
						<td><select name="filename"><option value="">- Select file to associate with this entry -			
							<? // Open the download directory, and load its contents into a drop-down, skipping directories.
								$sql = "SELECT filename FROM podcast";
								$result = $db->sql_query($sql);
								$filenames = array();
								while ($r_fille = $db->sql_fetchrow($result))
									$filenames[] = $r_fille[filename];
								$dir_podcast = PODCAST_DIR;
								if (is_dir($dir_podcast)) {
									if ($dh = opendir($dir_podcast)) {
										while (($file = readdir($dh)) !== false) {
											$filename = $dir_podcast ."/". $file;
											if (is_file($filename)) {
												if (!in_array($filename, $filenames))
													$selected = ($row[filename] == $file) ? 'selected="selected"' : '';
													echo '<option value="'. $file .'" '. $selected .'>'. $file ."\n";
											}
										}
										closedir($dh);
									} else {die("Unable to open directory (". $dir .")");}
								} else {die("Not a directory (". $dir .")");}

								$date = ($row[pubDate] == '') ? time() : $row[pubDate];
							?>
						</td>
					</tr><tr>
						<td class="inputLabel">Type:</td>
						<td><select name="type"><option value="">- Select Type -
							<?
								$types = array('mp3' => 'audio/mpeg', 'm4a' => 'audio/x-m4a', 'mp4' => 'video/mp4', 'm4v' => 'video/x-m4v', 'mov' => 'video/quicktime', 'pdf' => 'application/pdf');
								foreach ($types as $ext => $mime) {
									$selected = ($row[type] == $mime) ? 'selected="selected"' : '';
									echo '<option value="'. $mime .'" '. $selected .'>'. $ext;
								}
							?>
						</select></td>
					</tr><tr>
						<td class="inputLabel">Date:</td>
						<td><input type="text" class="inputText" name="pubDate" value="<?=date('r', $date)?>" size="32" /></td>
					</tr><tr>
						<td class="inputLabel">Duration:</td>
						<td>
							<input type="text" class="inputText" name="Duration" value="<?=@$row[duration]?>" size="10" />
							(hh:mm:ss)
						</td>
					</tr><tr>
						<td class="inputLabel" style="vertical-align:top;">Keywords:</td>
						<td>
							(You may provide up to 12 comma-separated keywords)<br />
<textarea name="keywords" style="width:100%; height:50px"><?=@$row[keywords]?>
</textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr><tr>
			<td class="buttonBar" colspan="2">
				<input type="submit" class="inputButton" value="Save" />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" class="inputButton" value="Cancel" onclick="window.close()" />
			</td>
		</tr></table>
	</form>
</body>
</html>
