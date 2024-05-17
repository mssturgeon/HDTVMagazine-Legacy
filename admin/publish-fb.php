<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	if ($action == 'send') {
		require(BASE_DIR .'/includes/fb/php/facebook.php');
		$facebook = new Facebook('[REDACTED]','[REDACTED]');
		$fb_user = $facebook->require_login();
		$message = $_GET['message'];
		$attachment = array(
			'name' => $_GET['name'],
			'href' => $_GET['href'],
			'caption' => $_GET['caption'],
			'description' => $_GET['description'],
			'properties' => array(
				'category' => array(
					'text' => $_GET['category_text'],
					'href' => $_GET['category_href']),
					'ratings' => '5 stars'),
				'media' => array(
					array(
						'type' => 'image',
						'src' => $_GET['media_src'],
						'href' => $_GET['media_href']
					)
				),
			'latitude' => '41.4',
			'longitude' => '2.19'
		);
		$action_links = array(
			array(
				'text' => $_GET['action_text'],
				'href' => $_GET['action_href']
			)
		);
		$target_id = '45415877375';
		$uid = '45415877375';
		$facebook->api_client->stream_publish($message, $attachment, $action_links, '', $uid);

/*
		if (send_facebook($message, 'HDTVMagazine', 'tkbbdi')) {
			echo 'Sent the following message successfully: '. $message;
		} else {
			echo 'Failure!';
		}
*/
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Publish to Facebook</title>
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
</head>
<body>

	<div align="center"><form name="frm" action="<?=PHP_SELF?>" method="get">
		<input type="hidden" name="action" value="send" />
		<fieldset style="width:75%">
			<legend>Publish to Facebook</legend>

			<label for="message">Message: </label>
			<input type="text" class="inputText" name="message" id="message" value="" size="75" /><br /><br />

			<label for="href">URL: </label>
			<input type="text" class="inputText" name="href" id="href" value="" size="75" maxlength="110" /><br /><br />

		</fieldset>

		<input type="submit" class="inputButton" name="btnSend" value="Send">
		<input type="button" class="inputButton" name="btnCancel" value="Cancel" onClick="window.close()">
	</form></div>

</body>
</html>