<?
	header('Content-Type: text/plain');

	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	
	$debug = isset($_GET[debug]);

	$sql = "
		SELECT user_id
		FROM user_comments
		WHERE comment like '%complaint%'";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "UPDATE user SET email_spam = 1 WHERE id = $row[user_id]";
	  	if ($debug) {echo "$sql\n";} else {mQuery($sql);}
	}
?>
