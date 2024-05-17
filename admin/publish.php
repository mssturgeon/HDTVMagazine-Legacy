<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) access_denied();
	
	$action = isset($_POST[action]) ? $_POST[action] : '';
	if ($action = 'submit') {
	
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Publication</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>
	
  	<p>The following is a list of articles currently published in test. Select the articloes you wish to publish, verify the author, fill in the publication
	date, and click the "Schedule for Production" button. This will move those articles from test to production and schedule them at the appropriate time.
	If the time is less than "now", it will be published within the next 1o minutes.</p><p>
	If you see any warnings, please correct them in test and verify. When you then recheck this page, those warnings will have disappeared.
	</p>
	
	<?
		$sql = "SELECT * FROM mt_entry WHERE entry_blog_id = 6 order by entry_created_on";
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo $row[entry_title];
		}
	?>
	
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
