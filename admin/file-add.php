<?
	require('../global.php');
	if (!access(ACCESS_ADMIN)) prompt_login(PHP_SELF);
	
	$fp = $_GET['file_path'];
	// If it doesn't begin with '/', add it
	if (substr($fp, 0, 1) != '/') {
		$fp = "/". $fp;
	}
	
	$directory = strleftback($fp, '/');
	$file_name = strrightback($fp, '/');

	$qry = "INSERT INTO admin_ftp_files (file_name, directory) VALUES ('$file_name','$directory')";
	$result = mQuery($qry);

	js_close_reload();
?>
