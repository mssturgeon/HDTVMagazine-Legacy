<?
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$submit_action = isset($_POST['submit_action']) ? $_POST['submit_action'] : '';
	if ($submit_action == 'add') {
		$link = $_POST['link'];
		$title = addslashes($_POST['title']);
		$restrictions = addslashes($_POST['restrictions']);
		$start_date = date('Y-m-d', strtotime($_POST['start_date']));
		$end_date = date('Y-m-d', strtotime($_POST['end_date']));

		$sql = "INSERT INTO offers (title, link, coupon_code, start_date, end_date, restrictions)
		VALUES ('$title', '$link', '$_POST[coupon_code]', '$start_date', '$end_date', '$restrictions')";
		$result = mQuery($sql);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>HDTV Magazine - Manage Offers</title>
	<? require(BASE_DIR .'/includes/common_header.php');?>
	<script type="text/javascript">
		function delete_feed(fid) {
			with (document.forms['frm']) {
				submit_action.value = 'delete';
				feed_id.value = fid;
				submit();
			}
		}
	</script>
</head>
<body>
	<div id="body_container">
		<? include(BASE_DIR .'/includes/body_header.php');?>

		<h1>Manage Offers</h1>
		<table class="bare" cellpadding="0" cellspacing="0"><tr>
			<td style="vertical-align:top;padding-right:10px;">
				<? require('pending-sidemenu.php'); ?>
			</td><td style="vertical-align:top;">
				<div class=""><?
					if ($submit_action == 'delete') {
						echo "Offer id $_POST[id] deleted.";
					}
				?></div>
				<fieldset>
					<legend>Add Offer</legend>
					<form method="post" name="frm" action="<?=PHP_SELF?>">
						<input type="hidden" name="submit_action" value="add" />
						<input type="hidden" name="id" value="" />

						<label for="title">Title:</label>
						<input type="text" size="50" maxsize="256" name="title" id="title" value="" /><br />

						<label for="link">Link:</label>
						<input type="text" size="50" maxsize="256" name="link" id="link" value="" /><br />

						<label for="coupon_code">Coupon Code:</label>
						<input type="text" size="15" maxsize="32" name="coupon_code" id="coupon_code" value="" /><br />

						<label for="start_date">Start Date:</label>
						<input type="text" size="10" name="start_date" id="start_date" value="" /><br />

						<label for="end_date">End Date:</label>
						<input type="text" size="10" name="end_date" id="end_date" value="" /><br />

						<label for="restrictions">Restrictions:</label>
						<input type="text" size="50" maxsize="256" name="restrictions" id="restrictions" value="" /><br />

						<input type="submit" value="Add Offer" />
					</form>
				</fieldset>

				<fieldset>
					<legend>Manage Offers</legend>
					<table class="type1b" id="table" width="100%">
						<tr>
							<td class="type1b_header">ID</td>
							<td class="type1b_header">Title</td>
							<td class="type1b_header">Coupon Code</td>
							<td class="type1b_header">Start Date</td>
							<td class="type1b_header">End Date</td>
						</tr>
						<?
							$sql = "SELECT * FROM offers ORDER BY start_date";
							$result = mQuery($sql);
							while ($row = mysql_fetch_assoc($result)) {
								echo '<tr>'.
								'	<td class="grid" align="right" nowrap>'. $row['id'] .'</td>'.
								'	<td class="grid" nowrap>'.
									'<a target="_blank" href="'. $row['link'] .'">'. stripslashes($row['title']) .'</a>'.
									' [ <a href="">Edit</a> ]'.
									' [ <a class="red" href="javascript:void delete_row('. $row['id'] .');">Delete</a> ]<br />'.
									$row['restrictions'] .
								'	</td>'.
								'	<td class="grid">'. $row['coupon_code'] .'</td>'.
								'	<td class="grid">'. $row['start_date'] .'</td>'.
								'	<td class="grid">'. $row['end_date'] .'</td>'.
								'</tr>';
							}
						?>
					</table>
				</fieldset>
			</td>
		</tr></table>
		<? include(BASE_DIR .'/includes/body_footer.php');?>
	</div>
</body>
</html>
