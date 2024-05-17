<?
	set_time_limit(0);
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login($_SERVER['PHP_SELF']);

	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$title = $_POST['title'];
		$description = $_POST['description'];
		$start_time = $_POST['start_time'];
		$stop_time = $_POST['stop_time'];
		$venue_name = $_POST['venue_name'];
		$city_name = $_POST['city_name'];
		$region_name = $_POST['region_name'];
		$country_name = $_POST['country_name'];
		$latitude = $_POST['latitude'];
		$longitude = $_POST['longitude'];
		$url = $_POST['url'];

		$sql = "
		REPLACE INTO event
		(id, title, description, link, start_time, stop_time, venue_name, city_name, region_name, region_abbr, country_name, country_abbr, latitude, longitude)
		VALUES ('$id', '$title', '$description', '$url', '$start_time', '$stop_time', '$venue_name', '$city_name', '$region_name', '$region_abbr', '$country_name', '$country_abbr', '$latitude', '$longitude')";
		echo "$sql\n";
		exit();

		# Notify subscribed users
		$result = mQuery("SELECT email_address, last_name, first_name, email_preference FROM user WHERE subscriptions & ". SUB_EVENTS ." AND email_invalid < 3");
		$addr_count = mysql_num_rows($result);
		$every = ceil($addr_count / 100);
		$x = 1;
		while ($row = mysql_fetch_assoc($result)) {
			$name = ($row['first_name'] != '') ? $row['first_name'] : $row['user_name'];
			$to = '<'. $row['email_address'] .'>';
			$content_type = ($row['email_preference'] == EMAIL_PREF_HTML) ? 'text/html' : 'text/plain';
			$message = "Hello $name,". $html_message;

			if ($x % $every == 0) {
				echo round(($x / $addr_count) * 100) .'% done.<br />';
			}
			$x++;
		}
	}

	# Add Tags
	# Add images/banners

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Add Event</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
</head>
<body>
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="save">
		<table class="type1b" style="width:100%">
			<tr>
				<td class="type1b_header" colspan="2">Add Event</td>
			</tr><tr>
				<td class="inputLabel">Title:</td>
				<td><input type="text" class="inputText" name="title" size="40" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel" style="vertical-align:top">Description:</td>
				<td><textarea name="description"></textarea></td>
			</tr><tr>
				<td class="inputLabel">Link:</td>
				<td><input type="text" class="inputText" name="url" size="50" maxlength="255"></td>
			</tr><tr>
				<td class="inputLabel">Start:</td>
				<td><input type="text" class="inputText" name="start_time" size="10"></td>
			</tr><tr>
				<td class="inputLabel">End:</td>
				<td><input type="text" class="inputText" name="stop_time" size="10"></td>
			</tr><tr>
				<td class="inputLabel">Venue Name:</td>
				<td><input type="text" class="inputText" name="venue_name" size="30" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">City:</td>
				<td><input type="text" class="inputText" name="city_name" size="25" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Region:</td>
				<td><?=getSelectBox("SELECT DISTINCT name, id FROM tbl_states ORDER BY name", 'region_name', '', '')?></td>
			</tr><tr>
				<td class="inputLabel">Country:</td>
				<td><input type="text" class="inputText" name="country_name" size="30" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Latatude:</td>
				<td><input type="text" class="inputText" name="latitude" size="5" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Longitude:</td>
				<td><input type="text" class="inputText" name="longitude" size="5" maxlength="10"></td>
			</tr><tr>
				<td class="buttonBar" colspan="2">
					<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="button" name="btnClose" value="&nbsp;Close&nbsp;" onClick="window.close()" class="inputButton">
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
