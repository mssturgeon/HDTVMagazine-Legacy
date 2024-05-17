<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_amazon.php');

	$entry_id = isset($_GET['entry_id']) ? addslashes($_GET['entry_id']) : exit();
	$asin = isset($_GET['asin']) ? addslashes($_GET['asin']) : '';
	$pg_masterid = isset($_GET['pg_masterid']) ? addslashes($_GET['pg_masterid']) : '';

	$action = isset($_GET['action']) ? addslashes($_GET['action']) : '';
	if ($action == 'save') {
		$sql = "UPDATE aux_mt_entry SET ASIN = '$asin', pg_masterid = '$pg_masterid' WHERE entry_id = '$entry_id'";
		mQuery($sql);

		js_close();
	}

	$sql = "SELECT ASIN, pg_masterid FROM aux_mt_entry WHERE entry_id = '$entry_id'";
	$result = mQuery($sql);
	$row_aux = mysql_fetch_assoc($result);

	$sql = "SELECT entry_title FROM mt_entry WHERE entry_id = '$entry_id'";
	$result = mQuery($sql);
	$row_entry = mysql_fetch_assoc($result);

	if ($row_aux['ASIN'] == '') {
		$keyword = str_ireplace('review', '', explode(':', $row_entry['entry_title']));
		if ($debug) print_r($keyword[1]);

		# Get matches in the DV category. If empty, try Electronics
		$asinList = getByKeyword(rawurlencode($keyword[1]), 'DVD');
		$asinList = ($asinList != '') ? $asinList : getByKeyword(rawurlencode($keyword[1]), 'Electronics');
		if ($debug) print_r($asinList);

		$asinField = '<select id="asin" name="asin">';
		foreach($asinList as $asin) {
			$selected = ($row['ASIN'] == $asin['ASIN']) ? 'selected="selected"' : "";
			$asinField .= '<option value="'. $asin['ASIN'] .'" '. $selected .'>'. $asin['Title'] .' ('. $asin['ASIN'] .')</option>';
		}
		$asinField .= '</select>';
	} else {
		$asinField = '<input style="width:13em" type="text" id="asin" name="asin" value="'. $row_aux['ASIN'] .'">';
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Add/Edit ASIN</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head><body>
	<form name="frm" action="<?=PHP_SELF?>" method="get">
		<input type="hidden" name="action" value="save" >
		<input type="hidden" name="entry_id" value="<?=$entry_id?>" >
		<fieldset>
			<legend><?=$row_entry['entry_title']?></legend>
			<label for="asin">Add/Edit ASIN</label>
			<?=$asinField?>
			<br />

			<label for="pg_masterid">Add/Edit MasterID</label>
			<input style="width:13em" type="text" id="pg_masterid" name="pg_masterid" value="<?=$row_aux['pg_masterid']?>">
			<br />

			<div class="btn btn_green"><a href="#" onclick="document.forms[0].submit();">Save Changes</a><span></span></div>
		</fieldset>
	</form>
</body></html>