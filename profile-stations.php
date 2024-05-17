<?
	require('global.php');
	require(BASE_DIR .'/profile-overall_header.php');

	if (!$user->data['is_registered']) prompt_login(PHP_SELF);

	$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
	if ($action == 'remove') {
		$source_id = $_POST['source_id'];
		$sql = "DELETE FROM j_user_source WHERE source_id = '$source_id' AND user_id = ". $user->data['user_id'];
		mQuery($sql);
		exit;
	} elseif ($action == 'reorder') {
		$stations = $_POST['stations'];
		$x = 1;
		foreach ($stations as $source_id) {
			$sql = "UPDATE j_user_source SET sort = '$x' WHERE source_id = '$source_id' AND user_id = ". $user->data['user_id'];
			mQuery($sql);
			$x++;
		}
		exit;
	} elseif ($action == 'choices') {
		$station = $_GET['station'];
		echo '<ul>';
		$sql = "
		SELECT source_id, full_name, short_name
		FROM prog_source
		WHERE MATCH (full_name, short_name, city) AGAINST ('$station')";
		$result = mQuery($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo '<li>'.
			$row['full_name'] .' ('. $row['short_name'] .')<span class="informal"><br />'.
			'<source id="'. $row['source_id'] .'" /></span>'.
			'</li>';
		}
		echo '</ul>';
		exit;
	} elseif ($action == 'add') {
		$source_id = $_POST['source_id'];

		# Insert new station in j_user_source
		$sql = "
		INSERT IGNORE INTO j_user_source (user_id, source_id, sort, label, channel)
		SELECT ". $user->data['user_id'] ." as user_id, source_id, 0, short_name as label, virtual_channel_number as channel
		FROM prog_source
		WHERE source_id = '$source_id'";
		mQuery($sql);

		# Query inserted station to generate html code to add to list
		$sql = "SELECT short_name as label, virtual_channel_number as channel, s.source_id, img, full_name, affiliation_1
		FROM prog_source s, aux_prog_source a
		WHERE s.source_id = a.source_id
			AND s.source_id = '$source_id'";
		$result = mQuery($sql);
		$row = mysql_fetch_assoc($result);
		echo '<div id="station_'. $row['source_id'] .'" class="item" style="margin:0 0 2px 0"><span class="corners-top"><span></span></span>'.
			'<table class="bare" cellspacing="0" width="100%"><tr>'.
				'<td style="width:20px"><img alt="" src="/images/logos/'. str_replace('[size].', '15.', $row['img']) .'"></td>'.
				'<td style="width:150px"><input style="margin-left:0;width:95%" type="text" name="label_'. $row['source_id'] .'" value="'. $row['label'] .'"></td>'.
				'<td style="width:40px"><input style="margin-left:0;width:90%" type="text" name="channel_'. $row['source_id'] .'" value="'. $row['channel'] .'"></td>'.
				'<td><a target="_blank" href="/programming/guide-station.php?id='. $row['source_id'] .'">'. $row['full_name'] .'</a></td>'.
				'<td style="width:25px">'. $row['affiliation_1'] .'</td>'.
				'<td style="width:40px; text-align:right"><img style="cursor:pointer" onclick="removeRequest('. $row['source_id'] .')" alt="" src="/images/delete-icon.png"></td>'.
			'</tr></table>'.
			'<span class="corners-bottom"><span></span></span></div>'."\n";
			exit;
	} elseif ($action == 'save') {
		foreach ($_POST['source_ids'] as $source_id) {
			$sql = "
			UPDATE j_user_source SET
				channel = '". $_POST["channel_$source_id"] ."',
				label = '". $_POST["label_$source_id"] ."'
			WHERE source_id = '$source_id'
				AND user_id = ". $user->data['user_id'];
			mQuery($sql);
		}
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Profile - My Stations</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8/scriptaculous.js"></script>
	<script language="javascript" type="text/javascript">
		function removeRequest(source_id) {
			document.getElementById('sortable_updating').style.display = '';
			return createAJAXRequest(document.location, 'action=remove&source_id='+source_id, 'remove(httpRequest, '+source_id+')');
		}

		function reorderRequest(oSortableContainer) {
			document.getElementById('sortable_updating').style.display = '';
			stations = Sortable.serialize(oSortableContainer, {tag: 'div', name: 'stations'});
			return createAJAXRequest(document.location, 'action=reorder&'+stations, 'reorder(httpRequest)');
		}

		function addStationRequest() {
			document.getElementById('sortable_updating').style.display = '';
			source_id = document.getElementById('add_source_id').value;
			return createAJAXRequest(document.location, 'action=add&source_id='+source_id, 'add(httpRequest, '+source_id+')');
		}

		function remove(httpRequest, source_id) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					document.getElementById('sortable_updating').style.display = 'none';
					if (httpRequest.responseText == '') {
						Effect.Fade('station_'+source_id);
						Effect.BlindUp('station_'+source_id);
					} else {alert('Reorder request failed:\n\n'+httpRequest.responseText);}
				} else {
					alert('Error: HTTP Status '+httpRequest.status+'. There was a problem with the request.');
					// Redirect to feedback form?
				}
			}
		}

		function reorder(httpRequest) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					document.getElementById('sortable_updating').style.display = 'none';
					if (httpRequest.responseText == '') {
					} else {alert('Reorder request failed:\n\n'+httpRequest.responseText);	}
				} else {
					alert('Error: HTTP Status '+httpRequest.status+'. There was a problem with the request.');
					// Redirect to feedback form?
				}
			}
		}

		function add(httpRequest, source_id) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					document.getElementById('sortable_updating').style.display = 'none';
					if (httpRequest.responseText != '') {
						oSortableContainer = document.getElementById('sortable_container');
						oSortableContainer.innerHTML = httpRequest.responseText + oSortableContainer.innerHTML;
						Sortable.destroy('sortable_container');
						Sortable.create('sortable_container', {tag: 'div', only: 'item', scroll: window, onUpdate: reorderRequest});
						Effect.BlindDown('station_'+source_id, {duration:0.5});
						Effect.Appear('station_'+source_id, {duration:0.5});
						document.getElementById('station').value = '';
					} else {alert('Reorder request failed');}
				} else {
					alert('Error: HTTP Status '+httpRequest.status+'. There was a problem with the request.');
					// Redirect to feedback form?
				}
			}
		}

		function selectStation(field, li) {
			field.blur();
			var source = li.getElementsByTagName('source');
			source_id = source[0].getAttribute('id');
			document.getElementById('add_source_id').value = source_id;
		}

		function init() {
			Sortable.create('sortable_container', {tag: 'div', only: 'item', scroll: window, onUpdate: reorderRequest});
			new Ajax.Autocompleter('station', 'autocomplete_choices', document.location +'?action=choices', {
				method: 'get',
				paramName: 'station',
				minChars: 2,
				indicator: 'spinner',
				afterUpdateElement: selectStation
			});
			document.getElementById('sortable_loading').style.display = 'none';
		}
	</script>
</head>
<body id="body_container" onload="init()">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');

		if ($action == 'save') {
			echo '<div class="alert_green" id="saved" align="center"><div>Stations saved successfully!</div></div>';
		} else {
			echo '<div id="saved"></div>';
		}

	?>

	<div align="center"><div id="tab-container">
		<?=getTabHeader($tabs);?>
		<div class="tab-content" style="padding:10px">
			<form name="frmPreferredStations" action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<input type="hidden" name="action" value="save">

				<fieldset>
					<legend>Add Stations</legend>
					<div class="infobox" style="width:30%">Start typing the name, call sign or city of the station you would like to add.</div>
					<label for="station">Station Name/City:</label>
					<input type="text" id="station" style="width:25em" name="station" value="" />
					<input type="hidden" name="add_source_id" id="add_source_id" value="" />
					<input type="button" value="Add" onclick="addStationRequest()" />
					<span id="spinner" style="display:none; padding-left:10px"><img src="/images/loading-small.gif" alt="Working..." /?></span>
					<div id="autocomplete_choices" class="autocomplete" style="display:none"></div>
				</fieldset>

				<fieldset>
					<legend>Selected Stations</legend>
					<b>To edit</b> the label or assigned channel number, make the appropriate changes below and click Save.
					<b>To re-order</b> your stations, simply drag-and-drop into new sort order.
					<b>To remove</b> a station from your lineup, click the remove icon (<img alt=" X " src="/images/delete-icon.png" style="height:1.1em" align="absmiddle" />).<br /><br />

					<div style="padding-left:5px"><table style="width:100%"><tr>
						<td style="width:20px">&nbsp;</td>
						<td style="width:150px"><b>Label</b></td>
						<td style="width:40px"><b>Ch.</b></td>
						<td><b>Full Station Name</b></td>
						<td style="width:25px" align="right"><b>Affiliate</b></td>
						<td style="width:40px">&nbsp;</td>
					</tr></table></div>

					<div id="sortable_container">
						<div id="sortable_loading">
							<div align="center">
								<img alt="Please wait." src="/images/loading-big.gif" /><br />
								Loading...
							</div>
						</div>
						<div id="sortable_updating" style="display:none">
							<div align="center">
								<img alt="Please wait." src="/images/loading-big.gif" /><br />
								Updating...
							</div>
						</div>	<?
							$sql = "SELECT label, channel, s.source_id, img, full_name, affiliation_1
							FROM prog_source s, j_user_source j, aux_prog_source a
							WHERE s.source_id = j.source_id
								AND user_id = ". $user->data['user_id'] ."
								AND s.source_id = a.source_id
							ORDER BY sort, channel*1, label ASC";
							$result = mQuery($sql);

							while ($row = mysql_fetch_assoc($result)) {
								echo '<div id="station_'. $row['source_id'] .'" class="item" style="margin:0 0 2px 0"><span class="corners-top"><span></span></span>'.
								'<table class="bare" cellspacing="0" width="100%"><tr>'.
									'<td style="width:20px"><img alt="" src="/images/logos/'. str_replace('[size].', '15.', $row['img']) .'"></td>'.
									'<td style="width:150px"><input style="margin-left:0;width:95%" type="text" name="label_'. $row['source_id'] .'" value="'. $row['label'] .'"></td>'.
									'<td style="width:40px"><input style="margin-left:0;width:90%" type="text" name="channel_'. $row['source_id'] .'" value="'. $row['channel'] .'"></td>'.
									'<td><a target="_blank" href="/programming/guide-station.php?id='. $row['source_id'] .'">'. $row['full_name'] .'</a></td>'.
									'<td style="width:25px">'. $row['affiliation_1'] .'</td>'.
									'<td style="width:40px; text-align:right"><img style="cursor:pointer" onclick="removeRequest('. $row['source_id'] .')" alt="" src="/images/delete-icon.png"></td>'.
								'</tr></table>'.
								'<input type="hidden" name="source_ids[]" value="'. $row['source_id'] .'" />'.
								'<span class="corners-bottom"><span></span></span></div>'."\n";
							}
							if (mysql_num_rows($result) == 0) {
								echo '<div>Your preferred stations list is empty.</div>';
							}
						?>
					</div>
				</fieldset>

				<input type="submit" name="btnSubmit" value="&nbsp;Save&nbsp;" class="inputButton">
			</form>
		</div>
	</div></div>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
