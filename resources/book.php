<?
	require('../global.php');
#	require(BASE_DIR .'/includes/lib_amazon.php');

	# Load admindata
	$result = mQuery("SELECT * FROM admin_settings WHERE id = 1");
	$admindata = mysql_fetch_assoc($result);

	$asin = isset($_GET[asin]) ? $_GET[asin] : '';
	$qry = "
	SELECT *
	FROM az_books
	WHERE ASIN = '$asin'";
	$result = mQuery($qry);
	$row = mysql_fetch_assoc($result);

	$url = 'http://www.amazon.com/exec/obidos/external-search?search-type=ss&tag='. $admindata['amazon_associates_id'] .'&keyword='. $row[ASIN] .'&index=books';

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine Books - <?=$row[Title]?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<h1 style="margin-bottom:3px;"><?=$row[Title]?></h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td style="text-align:center;padding:10px 0px;vertical-align:top;">
			<img src="<?=$row[MediumImage]?>" alt="<?=$row[Title]?>">
		</td>
			<td style="padding:10px 0px;vertical-align:top;">
			<table class="bare" cellpadding="0" cellspacing="0">
				<tr>
					<td class="inputLabel" style="vertical-align:top">Title:</td>
					<td>
						<a target="_blank" href="<?=$url?>"><?=$row[Title]?></a><br>
						<?=$row[Author]?><br>
						<?=$row[NumberOfPages]?> pages, <?=$row[PublicationDate]?>
					</td>
				</tr><tr>
					<td class="inputLabel" nowrap>List Price:</td>
					<td><strike>$<?=sprintf('%01.2f', $row[ListPriceAmount]/100);?></strike></td>
				</tr><tr>
					<td class="inputLabel" nowrap>Price:</td>
					<td class="primary_bold">$<?=sprintf('%01.2f', $row[LowestNewPriceAmount]/100);?></td>
				</tr><tr>
					<td class="inputLabel" nowrap>Avg. Rating:</td>
					<?
						if ($row[TotalReviews] > 0) {
							echo '<td><img src="/images/stars_'. $row[AverageRating] .'.gif" alt="'. $row[AverageRating] .'" align="middle"> ('. $row[TotalReviews] .' reviews)</td>';
						} else {
							echo '<td>(Unrated)</td>';
						}
					?>
				</tr><tr>
					<td>&nbsp;</td>
					<td><a target="_blank" href="<?=$url?>"><img src="/images/buy_amazon.gif" alt="Buy from Amazon"></a></td>
				</tr>
			</table>
		</td>
	</tr></table>

	<fieldset>
		<legend>Product Details</legend>
		<b>Paperback:</b> <?=$row[NumberOfPages]?> pages<br>
		<b>Publisher:</b> <?=$row[Publisher]?>; <?=$row[Edition]?> edition (<?=$row[PublicationDate]?>)<br>
		<b>ISBN:</b> <?=$row[ISBN]?><br>
		<b>Average Customer Review:</b> <?=$row[AverageRating]?> based on <?=$row[TotalReviews]?> reviews.<br>
		<b>Amazon.com Sales Rank:</b> #<?=$row[SalesRank]?> in Books<br>
	</fieldset>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
