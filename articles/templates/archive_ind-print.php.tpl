<?
	### ARCHIVE_IND-PRINT.PHP.TPL ###
	require('/var/www/html/global.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=<$MTPublishCharset$>" />
	<meta name="robots" content="noindex">
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
<body style="background-image:none" onload="window.print()">
	<?
		include(BASE_DIR .'/includes/tracker.php');
	?>

	<a href="/"><img src="/images/hdtvmagazine.gif" alt="HDTV Magazine"></a><br>
	<span style="color:#666666;letter-spacing:2.5mm;font-weight:bold">www.hdtvmagazine.com</span><br>
	<br>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;margin-bottom:20px;">
		<tr>
			<td class="article_title">
				<$MTEntryTitle$>
			</td>
		</tr><tr>
			<td style="text-align:left;color:#666666;padding:3px;" nowrap>
				By <b><$MTEntryAuthor$></b> on <b><$MTEntryDate format="%B %e, %Y"$></b>
			</td>
		</tr>
	</table>

	<$MTEntryBody$>
	<br />
	<br />

	<div align="center">
		<img alt="Creative Commons License" src="http://creativecommons.org/images/public/somerights20.gif" /><br />
		&copy; 1998 - <?=date("Y")?> HDTV Magazine, Ltd.<br />
	</div>

</body>
</html>
