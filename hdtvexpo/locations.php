<?
	require('global.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	
	if ($_POST[action] == 'submit') {
		$sql = "
		REPLACE INTO expo_locations (name, email, zip)
		VALUES ('$_POST[name]', '$_POST[email]', '$_POST[zip]')";
		mQuery($sql);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>The HDTV Expo - High Definition Consumer Education</title>
	<?include(BASE_DIR .'/includes/common_header.php')?>
</head>
<body>
	<?include(BASE_DIR .'/includes/body_header.php')?>
	<div style="font-size:12pt; font-weight:bold; text-align:center">Find out When The HDTV Expo will be in your City</div>
	<br />
	The HDTV Expo is a traveling roadshow that's being hosted in markets both large and small all around the U.S.
	Completing the information requested will entitle you to FREE entry into both the exhibits and classes.<br />
	<br />
	Make plans to attend the HDTV Expo in your city.  Stay in touch with the leading source for facts about the
	digital TV changes and the best single source for facts about Digital and HDTV.  Knowledge is power!
	<br />
	<br />
	<?if ($_POST[action] == 'submit') {?>
		<div style="background-color:#CCCCCC; padding:3px;">Thank you, you will be notified when we have dates planned for a location near you.</div>
	<?} else {?>
		<form method="post"  action="locations.php"><table class="bare">
			<input type="hidden" name="action" value="submit">
			<tr>
				<td>Name:</td>
				<td><input type="text" name="name" />
			</tr><tr>
				<td>Email:</td>
				<td><input type="text" name="email" size="30" />
			</tr><tr>
				<td>Zip code:</td>
				<td><input type="text" name="zip" size="10" />
			</tr><tr>
				<td colspan="2" style="text-align:center">
					<input type="submit" value="Submit" />
				</td>
			</tr>
		</table></form>
	<?}?>
	<br />
	<b>* Your privacy is important to us.  Therefore we promise not to sell, trade, or broker your information.</b>
	<?include(BASE_DIR .'/includes/body_footer.php');?>
</body>
</html>
