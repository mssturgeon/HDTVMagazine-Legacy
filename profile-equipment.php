<?
	require('global.php');
	require(BASE_DIR .'/profile-overall_header.php');

	if (!$user->data['is_registered']) prompt_login(PHP_SELF);

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - My Hardware</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form action="<?=PHP_SELF?>" method="post" name="frmProfile" id="frmHardware" onsubmit="return validate()">

				<fieldset>
					<legend></legend>
					<div class="infobox"></div>
					<label for="">
					</label>
				</fieldset>

				<div class="btn btn_green"><a href="#" onclick="document.forms[0].submit();">Save Changes</a><span></span></div>
			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>