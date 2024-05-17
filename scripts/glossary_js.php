<?
	require_once('/var/www/html/includes/constants.php');
	require_once('/var/www/html/includes/lib_common.php');
	require_once('/var/www/html/includes/lib_mysql.php');
	
	echo "var termArray = new Array();\n";
	echo "var defArray = new Array();\n";
	
	$result = mQuery("SELECT id, term, definition FROM glossary");
	while ($row = mysql_fetch_assoc($result)) {
		echo "termArray[{$row['id']}] = '". addslashes($row['term']) ."';\n";
		echo "defArray[{$row['id']}] = '". addslashes($row['definition']) ."';\n";
	}
?>	

	function ghighlight() {
		alert('here');
		oBlock = document.getElementById('gLink');
		tBlock = oBlock.innerText;
		for (x=0; x<termArray.length; x++) {
			tBlock = tBlock.replace(termArray[x], '<a href="">'+termArray[x]+'</a>');
		}
	}
