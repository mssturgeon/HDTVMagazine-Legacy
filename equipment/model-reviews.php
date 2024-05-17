<?
	require('../global.php');
	require('model-overall_header.php');

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Reviews: <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>Reviews: <?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;"><tr class="header">
				<td>Review Date</td>
				<td>Rating</td>
				<td>Helpfuless</td>
				<td>Summary</td>
			</tr>
			<?
				$sql = "
				SELECT Date, Rating, HelpfulVotes, TotalVotes, Summary, (HelpfulVotes/TotalVotes) avg
				FROM az_reviews
				WHERE ASIN = '$asin'
					AND Summary <> ''
				ORDER BY Date DESC";
				$cust_reviews = mQuery($sql);

				while ($review = mysql_fetch_assoc($cust_reviews)) {
					$img_rating = ($review['Rating'] == 0) ? '(Unrated)' : '<img src="/images/stars5-'. $review['Rating'] .'.0.gif" alt="'. $review['Rating'] .'" align="absmiddle">';
					$img_helpful = ($review['TotalVotes'] == 0) ? '' : number_format($review['HelpfulVotes'] / $review['TotalVotes'], 0) .'%';
/*					echo '<tr>'.
					'	<td>'. $review['Date'] .'</td>'.
					'	<td>'. $img_rating .'</td>'.
					'	<td>'. $review['HelpfulVotes'] .' / '. $review['TotalVotes'] .'</td>'.
					'	<td><a href="">'. $review['Summary'] .'</a></td>'.
					'</tr>'."\n";
*/
					echo '<div style="color:#777777; padding:2px">'.
					$img_rating .
					$img_helpful .
					'<a href="/equipment/review.php?a='. $review['ASIN'] .'&date='. rawurlencode($review['Date']) .'&summary='. rawurlencode($review['Summary']) .'">'. $review['Summary'] .'</a>'.
					'</div>';
				}
			?></table>
		</div>
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