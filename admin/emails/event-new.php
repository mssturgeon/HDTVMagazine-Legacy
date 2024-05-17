<?
	require('../../global.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$result = mQuery("SELECT title FROM events WHERE id = $id");
	$row = mysql_fetch_assoc($result);
?>
<br>
<br>
A new event has been added to the <a href="<?=FULL_URL_EVENTS?>">HDTV Events</a> page:<br>
<?=FULL_URL_EVENTS?><br>
<br>
<table cellpadding="5" cellspacing="0" style="width:400px;border:1px solid black;background-color:#<?=BG_COLOR?>"><tr><td>
	<? stripslashes($row['title']) ?>
</td></tr></table>
<br>
<br>
Enjoy,<br>
<br>
- Dale &amp; Shane<br>
HDTV Magazine<br>
www.hdtvmagazine.com<br>
