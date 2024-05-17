<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_admin.php');

	if (!access(ACCESS_ADMIN_ANY)) access_denied();

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	if ($action == 'test_shorten') {
		header('Content-type: text/plain');
/*
		$sql = "SELECT COUNT(*) num FROM url_shortened";
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result);
		echo 'http://hdtvmagazine.com/c'. ($row['num']+1);
		*/
		echo shorten_url($_GET['target_url']);
		exit;
	} elseif ($action == 'send') {
		$to = isset($_GET['to']) ? $_GET['to'] : '';
		$target_url = isset($_GET['target_url']) ? $_GET['target_url'] : '';
		$text = isset($_GET['text']) ? $_GET['text'] : '';
#		$sql = "INSERT INTO url_shortened (url) VALUES ('$target_url')";
#		$result = mQuery($sql);
#		$num = mysql_insert_id();
#		$message = $text .'. http://hdtvmagazine.com/c'. $num;
#		$message = $_GET['tweet_text'];

		$tweet_text = send_tweet($text, $target_url, 'HDTVMagazine', 'tkbbdi');
		if ($tweet_text != '') {
			echo 'Sent the following message successfully: '. $tweet_text;
		} else {
			echo 'Failure!';
		}

/*
		$curl_handle = curl_init();
		// $url = 'http://twitter.com/statuses/update.json';
		curl_setopt($curl_handle, CURLOPT_URL, "http://twitter.com/statuses/update.xml");
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$message");
#		curl_setopt($curl_handle, CURLOPT_USERPWD, "nagios_hdtv:1080i720p");
		curl_setopt($curl_handle, CURLOPT_USERPWD, "HDTVMagazine:tkbbdi");
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		// check for success or failure
		if (empty($buffer)) {
			echo 'Failure!';
		} else {
			echo 'Sent the following message successfully: '. $message;
		}
*/
		exit;
	}

	$to = '@ShaneSturgeon';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Send Tweet</title>
	<? require(BASE_DIR .'/includes/common_header.php'); ?>
	<script type="text/javascript">
		function getTitle(httpRequest) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					var text = document.getElementById("text");
					var re_title = new RegExp("<title>[\n\r\s]*(.*)[\n\r\s]*</title>", "gmi");
					content = httpRequest.responseText;
					title = re_title.exec(content);
					text.value = title[1];
				} else {
					alert('There was a problem with the request.');
				}
			}
		}

		function getSURL(httpRequest) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					url = httpRequest.responseText;
					var text = document.getElementById("text");
					var tweet_text = document.getElementById("tweet_text");
					var characters = document.getElementById("characters");
					tweet_text.value = text.value +'. '+ url;
					characters.innerHTML = tweet_text.value.length;
				} else {
					alert('There was a problem with the request.');
				}
			}
		}
	</script>
</head>
<body>

	<div align="center"><form name="frm" action="<?=PHP_SELF?>" method="get" onSubmit="return validate()">
		<input type="hidden" name="action" value="send" />
		<fieldset style="width:75%">
			<legend>Send Tweet</legend>
			<label for="to">To: </label>
			<?=$to?><br /><br />

			<label for="target_url">Target URL: </label>
			<input type="text" class="inputText" name="target_url" id="target_url" value="" size="75"
				onblur="createAJAXRequest(this.value, '', 'getTitle(httpRequest)')" /><br /><br />

			<label for="text">Text: </label>
			<input type="text" class="inputText" name="text" id="text" value="" size="75" maxlength="110"
				onblur="createAJAXRequest(document.location +'?action=test_shorten&amp;target_url='+ document.forms[0].target_url.value, '', 'getSURL(httpRequest)')" /><br /><br />

			<label for="tweet_text">Tweet Text: </label>
			<input type="text" class="inputText" name="tweet_text" id="tweet_text" value="" size="75" readonly="readonly" /><br />
			<span id="characters"></span> characters<br />

		</fieldset>

		<input type="submit" class="inputButton" name="btnSend" value="Send">
		<input type="button" class="inputButton" name="btnCancel" value="Cancel" onClick="window.close()">
	</form></div>

</body>
</html>