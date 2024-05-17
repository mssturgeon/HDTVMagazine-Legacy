<?
	require('global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Search</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<div id="cse-search-results"></div>
	<script type="text/javascript">
		var googleSearchIframeName = "cse-search-results";
		var googleSearchFormName = "cse-search-box";
		var googleSearchFrameWidth = 900;
		var googleSearchDomain = "www.google.com";
		var googleSearchPath = "/cse";
	</script>
	<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>