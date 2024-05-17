<?
	require('../global.php');

	$sub_type = SUB_ARTICLES;
	$sub_label = 'Receive instant notification of new articles';
	$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
	$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<? require(BASE_DIR .'/includes/page_header.php'); ?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>HDTV Magazine - Article Unavailable</title>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td class="article_title" colspan="2">Article Currenly Unavalable</td>
		</tr>
	</table>
	<? if ($sub_type > 0 && ($user->data['subscriptions'] & $sub_type) | $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($user->data['is_registered']) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<? include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="article-container">
			<br />
			We apologize for the inconvenience, but the article you have been directed to is temporarily unavailable. If you have elected to receive
			New Article notifications, you will be notified when this article is once again available.<br />
			<br />
			<br />
			Thank you,<br />
			<br />
			- Dale &amp; Shane<br />
			Publishers, HDTV Magazine
		</div>
	</div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
