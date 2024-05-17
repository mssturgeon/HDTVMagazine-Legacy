<?
	require('../global.php');
	header('Content-type: application/xml');

	$program_id = isset($_GET['p']) ? $_GET['p'] : exit();
	$sql = "SELECT p.program_id, title_128, long_description, rating, release_year
	FROM prog_program p, prog_rating r
	WHERE p.program_id = r.program_id
		AND p.program_id = $program_id";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
?>
<movies>
<movie id="<?=$row['program_id']?>" POS="0" DS="0">
	<title><?=$row['title_128']?></title>
	<synopsis><?=$row['long_description']?></synopsis>
	<details rated="<?=$row['rating']?>" relyear="<?=$row['release_year']?>" genreid="" genrename="" cmurate="" cmpred="" cmwgtr="" />
	<glyph rec="" frnd=""/>
	<starring>
		<person id="" name=""/>
	</starring>
</movie>
</movies>