<?
	require('../global.php');

	$sql = "SELECT * FROM prog_credits";
	$result = mQuery($sql);
	echo "<table><tr><td>program_id</td><td>credit_id</td><td>credit_type</td><td>first_name</td><td>last_name</td><td>part_name</td><td>sequence_number</td></tr>";
	while ($row = mysql_fetch_assoc($result)) {
		echo "<tr>";
		foreach ($row as $v) {
			echo "<td>$v</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
?>
