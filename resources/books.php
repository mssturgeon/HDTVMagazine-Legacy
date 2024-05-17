<?
	require('../global.php');
#	require(BASE_DIR .'/includes/lib_amazon.php');

	$start = isset($_GET[start]) ? $_GET[start] : 0;
	$count = isset($_GET[count]) ? $_GET[count] : 10;

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
	<title>HDTV Magazine - HDTV Books</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<?
		// Just to get a count of books in the database
		$result = mQuery("SELECT b.ASIN FROM az_books b, az_books_aux a WHERE b.ASIN = a.ASIN AND LowestNewPriceAmount > 0 AND rank > 0");
		$book_count = mysql_num_rows($result);

		$y = 1;
		if (($start-$count) >=0) $nav .= '<a href="?start='. ($start-$count) .'&count='. $count .'">&lt; Prev</a>&nbsp;&nbsp;&nbsp;';
		for ($x=0; $x<$book_count; $x+=$count) {
			if ($x == $start) {
				$nav .= $y++ .'&nbsp';
			} else {
				$nav .= '<a href="?start='. $x .'&count='. $count .'">'. $y++ .'</a>&nbsp;';
			}
		}
		if (($start+$count) < $book_count) $nav .= '&nbsp;&nbsp;<a href="?start='. ($start+$count) .'&count='. $count .'">Next &gt;</a>';
	?>

	<h1>HDTV Books</h1>
	<p>
		Below you will find a list of HDTV-related publications that we have hand-selected for inclusion on our website. We have partnered with
		<a href="http://www.amazon.com/exec/obidos/redirect?tag=hdtvmagazine-20" target="_blank">Amazon.com</a> to make purchasing of these items as painless as possible.
		Click <a href="/resources/books-complete.php">here</a> for a complete list.
	</p>
	<table class="bare" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
		<tr><td style="text-align:right;padding:2px" colspan="4">Sorted by: <?=$sort_display?></td></tr>
		<tr><td class="booksnavbar" colspan="4"><?=$nav?></td></tr>
		<?
			$qry = "
			SELECT b.ASIN, Title, Author, SalesRank, NumberOfPages, PublicationDate, ListPriceAmount, LowestNewPriceAmount, MediumImage img, AverageRating, TotalReviews
			FROM az_books b, az_books_aux a
			WHERE a.ASIN = b.ASIN
				AND a.rank > 0
				AND LowestNewPriceAmount > 0
				AND $where
			ORDER BY $order_by LIMIT $start,$count";
					$result = mQuery($qry);
			$x=1;
			while ($row = mysql_fetch_assoc($result)) {
				if ($x % 2 == 1) echo '<tr>';
				echo '	<td class="type1b" style="text-align:center"><img height="100" src="'. $row[img] .'" alt="'. $row[Title] .'"></td>'.
				'	<td class="type1b"><table class="bare" cellpadding="0" cellspacing="0">'.
				'		<tr>'.
				'			<td class="inputLabel" style="vertical-align:top">Title:</td>'.
				'			<td>'.
				'				<a href="/resources/book.php?asin='. $row[ASIN] .'">'. $row[Title] .'</a><br>'.
								$row[Author] .'<br />'.
								$row[NumberOfPages] .' pages, '. $row[PublicationDate] .
				'			</td>'.
				'		</tr><tr>'.
				'			<td class="inputLabel" nowrap>List Price:</td>'.
				'			<td><strike>$'. sprintf('%01.2f', $row[ListPriceAmount]/100) .'</strike></td>'.
				'		</tr><tr>'.
				'			<td class="inputLabel" nowrap>Price:</td>'.
				'			<td class="primary_bold">$'. sprintf('%01.2f', $row[LowestNewPriceAmount]/100) .'</td>'.
				'		</tr><tr>'.
				'			<td class="inputLabel" nowrap>Rating:</td>';
				if ($row[TotalReviews] > 0) {
					echo ''.
					'			<td><img src="/images/stars_'. $row[AverageRating] .'.gif" alt="'. $row[AverageRating] .'" /> ('. $row[TotalReviews] .' reviews)</td>';
				} else {
					echo '			<td>(Unrated)</td>';
				}

				if (access(ACCESS_ADMIN)) {
					echo '		</tr><tr>'.
					'			<td class="inputLabel" nowrap>ASIN:</td>'.
					'			<td>'. $row[ASIN] .'</td>';
				}
				echo '		</tr>'.
				'	</table></td>';
				if ($x++ % 2 == 0) echo '</tr>';
					}
				?>
		<tr><td class="booksnavbar" colspan="4"><?=$nav?></td></tr>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
