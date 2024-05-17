<?
	require('../global.php');
	require('model-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - eBay Listings: <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>eBay Listings: <?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content"><?
			$query = rawurlencode("$man $model");
			echo '<script language="JavaScript" src="http://lapi.ebay.com/ws/eBayISAPI.dll?EKServer'.
			'&ai=m%60%60gfatv%7C%7D'.
			'&bdrcolor='. BORDER_COLOR .
			'&cid=0'.
			'&eksize=1'.
			'&encode=UTF-8'.
			'&endcolor=FF0000'.
			'&endtime=y'.
			'&fbgcolor=EFEFEF'.
			'&fntcolor=000000'.
			'&fs=3'.
			'&hdrcolor=FFFFCC'.
			'&hdrimage=10'.
			'&hdrsrch=n'.
			'&height=200'.
			'&img=y'.
			'&lnkcolor='. PRIMARY_COLOR .
			'&logo=12'.
			'&num=10'.
			'&numbid=y'.
			'&paypal=n'.
			'&popup=y'.
			'&prvd=9'.
			'&query='. $query .
			'&r0=3'.
			'&shipcost=y'.
			'&sid='. $query .
			'&siteid=0'.
			'&sort=MetaEndSort'.
			'&sortby=endtime'.
			'&sortdir=asc'.
			'&srchdesc=y'.
			'&tbgcolor=FFFFFF'.
			'&title='. $query .
			'&tlecolor='. PRIMARY_COLOR .
			'&tlefs=0'.
			'&tlfcolor=FFFFFF'.
			'&toolid=10004'.
			'&track=5335981120'.
			'&width=600"></script>';
		?></div>
	</div>

	<? if (access(ACCESS_ADMIN_ANY)) {
		include(BASE_DIR .'/includes/lib_admin.php');
		echo getRowInformation($row);
	}?>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
		include('model_footer.php');
	?>
</div></body>
</html>
