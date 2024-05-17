<?
	require('../../global.php');
	if (!access(ACCESS_ADMIN_ANY)) prompt_login(PHP_SELF);

	$debug = isset($_GET['debug']);
	if ($debug) {
		header('Content-Type: text/plain');
	}
	
	$sql = "SELECT user_id, right(comment, length(comment) - 18) list FROM user_comments WHERE comment LIKE 'Unsubscribed from %' ORDER BY list";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$sub_value = array_search($row['list'], $SUB);
		if ($sub_value !== false && $row[user_id] != 1) {
			$sql = "UPDATE user SET subscriptions = subscriptions - $sub_value WHERE id = $row[user_id] AND (subscriptions & $sub_value)";
			if ($debug) {echo "$sql\n";} else {mQuery($sql);}
		}
	}
?>
