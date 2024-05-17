<?
	require('../global.php');
#	require(BASE_DIR .'/includes/lib_amazon.php');

	$where = '1';
	$sort = isset($_GET[sort]) ? $_GET[sort] : 'rank';
	switch ($sort) {
		case 'rank':
			$sort_display = '<b>Sales Rank</b> [<a href="?sort=rating">Rating</a>] [<a href="?sort=price">Price</a>]';
			$order_by = 'SalesRank';
			$where = 'SalesRank > 0';
			break;
		case 'rating':
			$sort_display = '[<a href="?sort=rank">Sales Rank</a>] <b>Rating</b> [<a href="?sort=price">Price</a>]';
			$order_by = 'AverageRating DESC, TotalReviews DESC';
			break;
		case 'price':
			$sort_display = '[<a href="?sort=rank">Sales Rank</a>] [<a href="?sort=rank">Rating</a>] <b>Price</b>';
			$order_by = 'LowestNewPriceAmount';
			break;
	}
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Book List (Complete)</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<h1>HDTV Book List (Complete)</h1>

	<p>
		Below you will find the complete list of HDTV-related publications that we have hand-selected for inclusion on our website. We have partnered with
		<a href="http://www.amazon.com/exec/obidos/redirect?tag=hdtvmagazine-20" target="_blank">Amazon.com</a> to make purchasing of these items as painless as possible.
	</p>

	<div class="shade_border" style="text-align:right" colspan="4">Sorted by: <?=$sort_display?></div><br />
	<table class="bare" cellpadding="2" cellspacing="0" width="100%" style="border-collapse:collapse">
		<?
			$qry = "
			SELECT b.ASIN, Title, Author, NumberOfPages, PublicationDate, ListPriceAmount, LowestNewPriceAmount, AverageRating, TotalReviews
			FROM az_books b, az_books_aux a
			WHERE a.ASIN = b.ASIN
				AND a.rank > 0
				AND LowestNewPriceAmount > 0
				AND $where
			ORDER BY $order_by";
			$result = mQuery($qry);
			$x=1;
			while ($row = mysql_fetch_assoc($result)) {
				echo '<tr onmouseover="hover_over(this)" onmouseout="hover_out(this)">';
				if ($row[TotalReviews] > 0) {
					echo '			<td valign="top" align="center"><img src="/images/stars_'. $row[AverageRating] .'.gif" alt="'. $row[AverageRating] .'" align="middle"></td>';
				} else {
					echo '			<td valign="top" align="center">(Unrated)</td>';
				}
				echo ''.
						'	<td>'.
				'		<a href="/resources/book.php?asin='. $row[ASIN] .'">'. $row[Title] .'</a>, '.
						$row[Author] .', '. $row[NumberOfPages] .' pages, '. $row[PublicationDate] .
				'	</td><td valign="top" align="right">'.
				'		<strike>$'. sprintf('%01.2f', $row[ListPriceAmount]/100) .'</strike>'.
				'	</td><td valign="top" align="right">'.
				'		<span class="primary_bold">$'. sprintf('%01.2f', $row[LowestNewPriceAmount]/100) .'</span>'.
				'	</td>'.
				'</tr>';
					}
				?>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
