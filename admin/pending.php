<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) access_denied();

	$debug = isset($_GET['debug']);
	if ($debug) header('Content-type: text/plain');

	$entry_id = isset($_POST['entry_id']) ? $_POST['entry_id'] : '';
	if ($entry_id != '') {
		$debug = isset($_POST['debug']);
		if ($debug) header('Content-Type: text/plain');

		$category_label = isset($_POST['category_label']) ? $_POST['category_label'] : '';
		$entry_created_on = isset($_POST['entry_created_on']) ? $_POST['entry_created_on'] : '';
		$entry_excerpt = isset($_POST['entry_excerpt']) ? $_POST['entry_excerpt'] : '';
		$blog_id = isset($_POST['blog_id']) ? $_POST['blog_id'] : '';
		$author_id = isset($_POST['author_id']) ? $_POST['author_id'] : '';
		$asin = isset($_POST['asin']) ? $_POST['asin'] : '';
		$pg_masterid = isset($_POST['pg_masterid']) ? $_POST['pg_masterid'] : '';

		# Update test article to publish to $blog_id, $author_id and set to Future, and update publish date
		$sql = "UPDATE mt_entry SET entry_blog_id = $blog_id, entry_author_id = $author_id, entry_status = 4, entry_created_on = '$entry_created_on' WHERE entry_id = '$entry_id'";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Delete all other entries from mt_placement for this article (categories)
		$sql = "DELETE FROM mt_placement WHERE placement_entry_id = '$entry_id'";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Update test article to change entry placement category
		$sql = "SELECT category_id FROM mt_category WHERE category_label = '$category_label' AND category_blog_id = $blog_id";
		$result = mQuery($sql);
		if ($debug) {echo "$sql\n";}
		$row_category = mysql_fetch_assoc($result);
		$sql = "REPLACE INTO mt_placement (placement_entry_id, placement_blog_id, placement_category_id, placement_is_primary)
		VALUES ('$entry_id', '$blog_id', $row_category[category_id], 1)";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

		# Update aux_mt_entry to reset notification and forum topic.
		$sql = "UPDATE aux_mt_entry SET notification_sent = 0, thread_created = 0 WHERE entry_id = '$entry_id'";
		if ($debug) {echo "$sql\n";} else {mQuery($sql);}

/*	Commented out because it would send the email before a future item is published
*/
		# Create aux_mt_entry
		# Need to check to make sure that the blog ID is valid and that an excerpt is present.
		$valid_blogs = split(",", INCLUDE_BLOGS_NOTIFY);
		if (in_array($blog_id, $valid_blogs) && $entry_excerpt != '') {
			$sql = "INSERT IGNORE INTO aux_mt_entry (entry_id, blog_id, asin, pg_masterid)
			VALUES ('$entry_id', '$blog_id', '$asin', '$pg_masterid')";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
/**/
#		if (!$debug) js_back("Entry $entry_id published!");
#		exit;
	}
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Pending Articles &amp; Reviews</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="JavaScript" type="text/javascript">
		function publish(id) {
			category_label = document.getElementById("category_label_"+id).value;
			entry_created_on = document.getElementById("entry_created_on_"+id).value;
			entry_excerpt = document.getElementById("entry_excerpt_"+id).value;
			blog_id = document.getElementById("blog_id_"+id).value;
			author_id = document.getElementById("author_id_"+id).value;
			asin = document.getElementById("asin_"+id).value;
			pg_masterid = document.getElementById("pg_masterid_"+id).value;
			if (category_label == '') {
				alert('You must select a category to publish.');
				return;
			}
			document.forms['submitPending'].entry_id.value = id;
			document.forms['submitPending'].category_label.value = category_label;
			document.forms['submitPending'].entry_created_on.value = entry_created_on;
			document.forms['submitPending'].entry_excerpt.value = entry_excerpt;
			document.forms['submitPending'].blog_id.value = blog_id;
			document.forms['submitPending'].author_id.value = author_id;
			document.forms['submitPending'].asin.value = asin;
			document.forms['submitPending'].pg_masterid.value = pg_masterid;
			document.forms['submitPending'].submit();
		}
	</script>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

 	<h1>Pending Articles &amp; Reviews</h1>
	<span style="color:#800; font-weight:bold">Red</span> items are already published to production and can be deleted.<br /><br />
	<div style="border-bottom:1px solid #DDD; clear:both; line-height:2em">
		<div style="float:left; font-weight:bold; width:50%">Title / Excerpt</div>
		<div style="float:left; font-weight:bold; ">&nbsp;</div>
		<div style="float:right; font-weight:bold; width:150px">Publish on</div>
	</div>
	<?
		$sql = "
		SELECT DISTINCT entry_blog_id, e.entry_id, entry_author_id, entry_status, entry_excerpt, entry_created_on, entry_title, author_name, asin, pg_masterid
		FROM mt_author a, mt_entry e
		LEFT JOIN aux_mt_entry aux ON e.entry_id = aux.entry_id
		WHERE entry_author_id = author_id
			AND e.entry_blog_id = 6
		ORDER BY entry_created_on DESC";
		$result = mQuery($sql);
		if ($debug) {echo "$sql\n";}

		$x=1;
		while ($row = mysql_fetch_assoc($result)) {
			$ts = strtotime($row['entry_created_on']);
			$y = date('Y', $ts);
			$m = date('m', $ts);
			$blog_dir = getBlogDir($row['entry_blog_id']);

			$date = getDateString($ts);

			# Get title
			if ($row['entry_status'] == 2) {
				$link = "/$blog_dir/$y/$m/". dirify($row['entry_title']) .".php";
				$title = '<a href="'. $link .'">'. $row['entry_title'] .'</a>';
			} else {
				$title = $row['entry_title'];
			}

			# Check for same title in production
			$sql = "SELECT entry_id FROM mt_entry WHERE entry_title = '". addslashes($row['entry_title']) ."' AND entry_blog_id <> 6";
			$res_dup = mQuery($sql);
			if (mysql_num_rows($res_dup) > 0) {
				$color = 'color:#800; font-weight:bold; ';
			} else {
				$color = '';
			}

			# Get category
			$sql = "
			SELECT category_label
			FROM mt_category c, mt_placement p
			WHERE p.placement_entry_id = $row[entry_id]
				AND c.category_id = p.placement_category_id";
			$res_category = mQuery($sql);
			$row_category = mysql_fetch_assoc($res_category);

			# Set bgcolor
			$bgcolor = ($x % 2 == 0) ? 'CCCCCC' : 'FFFFFF';
			echo '<div style="background-color:#'. $bgcolor .'; '. $color .'border-bottom:1px solid #AAAAAA; clear:both; height:9em; overflow:hidden; padding:3px">'.
				'<div style="float:right; text-align:right; width:150px">'.
					'<input style="width:13em" type="text" id="entry_created_on_'. $row['entry_id'] .'" name="entry_created_on_'. $row['entry_id'] .'" value="'. $row['entry_created_on'] .'"><br />'.
					'<input type="button" class="button_green" value="PUBLISH" onclick="publish('. $row['entry_id'] .')">'.
					'<input type="button" class="button_blue" value="Link Products" onclick="javascript:popOpen(\'/admin/asin-edit.php?entry_id='. $row['entry_id'] .'\', 500, 300);" />'.
					'<input type="hidden" id="entry_excerpt_'. $row['entry_id'] .'" name="entry_excerpt_'. $row['entry_id'] .'" value="'. $row['entry_excerpt'] .'">'.
				'</div>'.
				'<div style="float:left; padding-right:20px; width:40%">'.
					$title .'<br />'.
					'<span style="color:#AAA">'. $row['entry_excerpt'] .'</span>'.
				'</div>'.
				'<div style="float:left;">'."\n".
					'<b>Author: </b>'. getSelectBox("SELECT author_name, author_id FROM mt_author ORDER BY author_name", "author_id_$row[entry_id]", $row['entry_author_id'], 'id="author_id_'. $row['entry_id'] .'"') .'<br />'."\n".
					'<b>Publish in: </b>'. getSelectBox("SELECT blog_name, blog_id FROM mt_blog ORDER BY blog_name", "blog_id_$row[entry_id]", '', 'id="blog_id_'. $row['entry_id'] .'"') .'<br />'."\n".
					'<b>Category: </b>'. getSelectBox("SELECT DISTINCT category_label, category_label FROM mt_category ORDER BY category_label", "category_label_$row[entry_id]", $row_category['category_label'], ' id="category_label_'. $row['entry_id'] .'"') ."<br />\n".
					'<b>ASIN: </b>'.
					'<input style="width:13em" type="text" id="asin_'. $row['entry_id'] .'" name="asin_'. $row['entry_id'] .'" value="'. $row['asin'] .'">'.
					'<br />'."\n".
					'<b>PG MasterID: </b><input style="width:13em" type="text" id="pg_masterid_'. $row['entry_id'] .'" name="pg_masterid_'. $row['entry_id'] .'" value="'. $row['pg_masterid'] .'"><br />'."\n".
				'</div>'.
			"</div>\n";
			$x++;
		}
	?>
	<form name="submitPending" method="POST" action="<?=PHP_SELF?>">
		<?
			if ($debug) echo '<input type="hidden" name="debug" value="">';
		?>
		<input type="hidden" name="entry_id" value="">
		<input type="hidden" name="category_label" value="">
		<input type="hidden" name="entry_created_on" value="">
		<input type="hidden" name="entry_excerpt" value="">
		<input type="hidden" name="blog_id" value="">
		<input type="hidden" name="author_id" value="">
		<input type="hidden" name="asin" value="">
		<input type="hidden" name="pg_masterid" value="">
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
