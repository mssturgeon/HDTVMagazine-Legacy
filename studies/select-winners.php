<?
	require('../global.php');
	header('Content-Type: text/plain');
	
	### Modify here to set the QID and Number of winners to select
	$QID = '68892327';
	$winners = 5;
	
	if (!access(ACCESS_ADMIN_ANY)) access_denied();
	
	$num_rows = 0;
	$sql = "SELECT ResponseText FROM survey_rt WHERE QID = $QID";
	$result = mQuery($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$email_addresses[] = $row[ResponseText];
		$num_rows++;
	}
	echo "$num_rows (". mysql_num_rows($result) .")\n";
	
	$already_chosen = array();
	for ($x=0; $x<$winners; $x++) {
		$r = rand(0, $num_rows - 1);
		if (!in_array($r, $already_chosen)) {
			echo "$email_addresses[$r]\n";
			$already_chosen[] = $r;
		} else {
			$x--;
		}
	}
?>
