<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	$show = isset($_GET['showall']) ? '-1,0,1' : '0';
	if ($action == 'submit') {
		for ($x=0; $x < $_POST['number']; $x++) {
			$qry = 'UPDATE amazon_aux SET rank = '. $_POST["rank$x"] ." WHERE ASIN = '". $_POST["asin$x"] ."'";
			mquery($qry);
		}
		js_back();
		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Amazon Admin</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
	?>

				<table class="grid" cellpadding="0" cellspacing="0">
					<form action="<?=PHP_SELF?>" method="post" name="frm">
  						<input type="hidden" name="action" value="submit">
  						<tr>
							<td class="gridHeader">&nbsp;</td>
							<td class="gridHeader">Title</td>
							<td class="gridHeader">Author</td>
							<td class="gridHeader">SalesRank</td>
							<td class="gridHeader">Pages</td>
							<td class="gridHeader">Date</td>
							<td class="gridHeader">Amount</td>
							<td class="gridHeader" align="center" nowrap>Purge/Keep</td>
						</tr>
							<?
								$qry = "SELECT".
								" b.ASIN, Title, Author, SalesRank, NumberOfPages, PublicationDate, ListPriceAmount, SmallImage img, a.rank".
								"	FROM az_books b LEFT JOIN amazon_aux a ON b.ASIN = a.ASIN".
								"	WHERE a.rank IN ($show)".
								" ORDER BY SalesRank";
								$result = mQuery($qry);
								$x = 0;
								echo '<input type="hidden" name="number" value="'. mysql_num_rows($result) .'">';

								while ($row = mysql_fetch_assoc($result)) {
									$url = 'http://www.amazon.com/exec/obidos/external-search?search-type=ss&tag='. AMAZON_ASSOCIATE_ID .'&keyword='. $row['ASIN'] .'&index=books';
									$xml = AMAZON_BASE_URL.'&SubscriptionId='. AMAZON_DEVELOPER_TOKEN .'&AssociateTag='. AMAZON_ASSOCIATE_ID .'&Operation=ItemLookup&ItemId='. $row['ASIN'] .'&ResponseGroup='. AMAZON_RESPONSE_GROUP;
									$img = ($row['img'] == '') ? '/images/no-img.gif' : $row['img'];
									$purge_selected = ($row['rank'] == -1) ? 'SELECTED' : '';
									$none_selected = ($row['rank'] == 0) ? 'SELECTED' : '';
									$keep_selected = ($row['rank'] == 1) ? 'SELECTED' : '';

									echo '<input type="hidden" name="asin'. $x .'" value="'. $row['ASIN'] .'">';
									echo '<tr>'.
									'	<td class="grid" align="center"><img src="'. $img .'" alt="'. $row['Title'] .'"></td>'.
									'	<td class="grid"><a target="_blank" href="'. $url .'">'. $row['Title'] .'</a> (<a target="_blank" href="'. $xml .'">'. $row['ASIN'] .'</a>)</td>'.
									'	<td class="grid">'. $row['Author'] .'</td>'.
									'	<td class="grid" align="right">'. $row['SalesRank'] .'</td>'.
									'	<td class="grid" align="right">'. $row['NumberOfPages'] .'</td>'.
									'	<td class="grid" align="right" nowrap>'. $row['PublicationDate'] .'</td>'.
									'	<td class="grid" align="right">$'. sprintf('%01.2f', $row['ListPriceAmount']/100) .'</td>'.
									'	<td class="grid" align="center" nowrap><select name="rank'. $x++ .'">'.
									'		<option value="-1" '. $purge_selected .'>Purge</option>'.
									'		<option value="0" '. $none_selected .'></option>'.
									'		<option value="1" '. $keep_selected .'>Keep</option>'.
									'	</select></td>'.
									'</tr>';
								}
							?>
  						<tr>
  							<td colspan="8" style="padding-top:10px" align="center"><input type="submit" value="Submit Rankings" class="inputButton"></td>
						</tr>
					</form>
				</table>

	<?include(BASE_DIR .'/includes/body_footer.php');?>

</body>
</html>
