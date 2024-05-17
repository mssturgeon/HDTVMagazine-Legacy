<?
	set_time_limit(0);
	require('../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);
	
	$action = isset($_POST['action']) ? $_POST['action'] : '';
	if ($action == 'save') {
		$qry = "INSERT INTO events (".
		"title, start, duration, venue_name, venue_city, venue_state, contact_name, contact_phone, contact_email, url".
		") VALUES (".
		"'{$_POST['title']}', '". strtotime($_POST['start']) ."', '". $_POST['duration'] ."', '{$_POST['venue_name']}', '{$_POST['venue_city']}', '{$_POST['venue_state']}', '{$_POST['contact_name']}', '{$_POST['contact_phone']}', '{$_POST['contact_email']}', '{$_POST['url']}'".
		")";
		$result = mQuery($qry);
		$id = mysql_insert_id();
		$subject = 'Upcoming HDTV Event';
      $html_message = file_get_contents(FULL_URL_ADMIN_EMAILS ."/event-new.php?id=$id");
		
//      $text_message = file_get_contents(FULL_URL_ADMIN_EMAIL .'event-new.txt');
		
		// Notify subscribed users
		$result = mQuery("SELECT email_address, last_name, first_name, email_preference FROM user WHERE subscriptions & ". SUB_EVENTS ." AND email_invalid < 3");
		$addr_count = mysql_num_rows($result);
		$every = ceil($addr_count / 100);
		$x = 1;
		while ($row = mysql_fetch_assoc($result)) {
			$name = ($row['first_name'] != '') ? $row['first_name'] : $row['user_name'];
			$to = '<'. $row['email_address'] .'>';
			$content_type = ($row['email_preference'] == EMAIL_PREF_HTML) ? 'text/html' : 'text/plain';
			$message = "Hello $name,". $html_message;
			
//  			mail_send($to, $subject, $message, $content_type);

			if ($x % $every == 0) {
				echo round(($x / $addr_count) * 100) .'% done.<br />';
			}
			$x++;
		}
		
//		js_close_reload();
//		exit;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>HDTV Magazine - Add Event</title>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<script language="javascript" type="text/javascript">
		var cal_start;
		var cal_end;

		function init() {
			cal_start = new calendar2(frm.start);
//			cal_end = new calendar2(frm.end);
			frm.title.focus()
		}
	</script>
	<script language="javascript" type="text/javascript" src="/scripts/calendar.js"></script>
</head>
<body onload="init()">
	<?
		require(BASE_DIR .'/includes/tracker.php');
	?>
	<form name="frm" action="<?=PHP_SELF?>" method="post">
		<input type="hidden" name="action" value="save">
		<table class="table1" style="width:100%">
			<tr>
				<td class="table1Header" colspan="2">Add Event</td>
			</tr><tr>
				<td class="inputLabel">Title:</td>
				<td><input type="text" class="inputText" name="title" value="" size="40" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Start:</td>
				<td>
					<input type="text" class="inputText" name="start" value="" style="background-color:<?=BG_COLOR?>" size="10" READONLY>
					<a href="javascript:cal_start.popup();"><img src="/images/calendar/cal.gif" width="16" height="16" border="0" alt="Calendar" align="absmiddle"></a>
				</td>
			</tr><!--tr>
				<td class="inputLabel">End:</td>
				<td>
					<input type="text" class="inputText" name="end" value="" style="background-color:<?=BG_COLOR?>" size="10" READONLY>
					<a href="javascript:cal_end.popup();"><img src="/images/calendar/cal.gif" width="16" height="16" border="0" alt="Calendar" align="absmiddle"></a>
				</td>
			</tr--><tr>
				<td class="inputLabel">Duration:</td>
				<td><input type="text" class="inputText" name="duration" value="" size="3" maxlength="2"> days</td>
			</tr><tr>
				<td class="inputLabel">Venue Name:</td>
				<td><input type="text" class="inputText" name="venue_name" value="" size="30" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Venue City:</td>
				<td><input type="text" class="inputText" name="venue_city" value="" size="25" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Venue State:</td>
				<td><?=getSelectBox("SELECT DISTINCT name, id FROM tbl_states ORDER BY name", 'venue_state', '', '')?></td>
			</tr><tr>
				<td class="inputLabel">Contact Name:</td>
				<td><input type="text" class="inputText" name="contact_name" value="" size="30" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">Contact Phone:</td>
				<td><input type="text" class="inputText" name="contact_phone" value=""></td>
			</tr><tr>
				<td class="inputLabel">Contact Email:</td>
				<td><input type="text" class="inputText" name="contact_email" value="" size="40" maxlength="100"></td>
			</tr><tr>
				<td class="inputLabel">URL:</td>
				<td><input type="text" class="inputText" name="url" value="" size="50"></td>
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
