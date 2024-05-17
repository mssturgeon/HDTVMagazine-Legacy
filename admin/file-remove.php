<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	
	// Remove selected rows
	$qry = "DELETE FROM admin_ftp_files WHERE id IN (". $_GET['ids'] .")";
	$result = mQuery($qry);

	echo "Deleted ". mysql_affected_rows() ." records<br>";
	js_close_reload();
?>
