<?
	require('../global.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - </title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />

	<? require(BASE_DIR .'/includes/page_header-new.php');?>
</head>
<body>

<div id="header">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
</div>

<div class="colmask holygrail">
	<div class="colmid">
		<div class="colleft">
			<div class="col1wrap">
				<div class="col1">
					<!-- Column 1 start -->
					<p>Center Column</p>
					<iframe src="http://astore.amazon.com/hdtvmagazine-20" width="100%" height="4000" frameborder="0" scrolling="no"></iframe>
					<!-- Column 1 end -->
				</div>
			</div>
			<div class="col2">
				<!-- Column 2 start -->
				Left Column
				<!-- Column 2 end -->
			</div>
			<div class="col3">
				<!-- Column 3 start -->
				Right Column
				<!-- Column 3 end -->
			</div>
		</div>
	</div>
</div>

<div id="footer">
	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div>

</body>
</html>