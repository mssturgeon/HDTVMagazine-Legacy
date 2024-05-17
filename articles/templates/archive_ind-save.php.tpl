<?
	### ARCHIVE_IND-SAVE.PHP.TPL ###
	require_once('/var/www/html/includes/constants.php');
	header('Content-type: application/msword');
	header('Content-Disposition: attachment; filename="<$MTEntryTitle dirify="1"$>.doc"');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<!-- archive_ind-save.php template -->
	<meta http-equiv="Content-Type" content="text/html; charset=<$MTPublishCharset$>" />
	<title>HDTV Magazine <$MTBlogName encode_html="1"$> - <$MTEntryTitle$></title>

	<MTBlogIfCCLicense>
		<$MTCCLicenseRDF$>
	</MTBlogIfCCLicense>

	<style>
		@page {
			size:8.5in 11in;
			margin:1in;
		}
	</style>
</head>
<body>

	<a href="http://www.hdtvmagazine.com/"><img src="<?=IMG_LOGO?>" alt="HDTV Magazine"></a><br>
	<span style="color:#666666;letter-spacing:2.5mm;font-weight:bold">www.hdtvmagazine.com</span><br>
	<br>

	<span style="font-weight:bold;font-size:20pt"><$MTEntryTitle$></span><br />
	By <b><$MTEntryAuthor$></b> on <b><$MTEntryDate format="%B %e, %Y"$></b><br />
	<br />
	<br />
	<$MTEntryBody$>
	<br />
	<br />
	<div align="center">
		<a href="http://creativecommons.org/licenses/by-nd/2.5/"><img alt="Creative Commons License" src="http://creativecommons.org/images/public/somerights20.gif" /></a><br />
		&copy; 1998 - <?=date("Y")?> HDTV Magazine, Ltd.<br />
	</div>

</body>
</html>