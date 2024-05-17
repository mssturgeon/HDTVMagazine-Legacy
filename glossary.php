<?
	require('global.php');

	$term = isset($_POST['term']) ? mysql_real_escape_string($_POST['term']) : '';
	$definition = isset($_POST['definition']) ? mysql_real_escape_string($_POST['definition']) : '';
	if ($term != '') {
		$id = isset($_POST['id']) ? mysql_real_escape_string($_POST['id']) : '';

		if ($id == '') {
			$sql = "REPLACE INTO glossary (term, definition, created_on, modified_on) VALUES ('". $term ."', '". $definition ."', NOW(), NOW())";
			mQuery($sql);
			js_back;
		} else {
			header('Content-type: text/plain');
			$sql = "UPDATE glossary SET term = '". $term ."', definition = '". $definition ."', modified_on = NOW() WHERE id = $id";
			$db->sql_query($sql);
			exit;
		}
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Glossary of Terms</title>
	<meta name="description" content="HDTV Glossary of terms. Find definitions of many confusing terms, acronyms and HDTV product brands.">
	<meta name="keywords" content="hdtv glossary,terms,definition,hdtv,hd tv,high definition,high def tv,high definition television,high definition tv">
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript">
		function edit(id) {
			document.getElementById("term_"+id).style.display = 'none';
			document.getElementById("definition_"+id).style.display = 'none';
			document.getElementById("edit_"+id).style.display = 'none';
			document.getElementById("term-edit_"+id).style.display = '';
			document.getElementById("definition-edit_"+id).style.display = '';
			document.getElementById("save_"+id).style.display = '';
		}

		function getParameters(frmObj) {
			var params = new Array;

			for (x=0; x<frmObj.elements.length; x++) {
				params[x] = frmObj.elements[x].name +'='+ encodeURI(frmObj.elements[x].value);
			}
			return params.join('&');
		}

		function save(id) {
			document.forms['frm'].id.value = id;
			document.forms['frm'].term.value = document.getElementById("term-edit_"+id).value;
			document.forms['frm'].definition.value = document.getElementById("definition-edit_"+id).value;

			parameters = getParameters(document.forms['frm']);
			return ajax_makeRequest(document.location, parameters);

			document.getElementById("term_"+id).style.display = '';
			document.getElementById("definition_"+id).style.display = '';
			document.getElementById("edit_"+id).style.display = '';

			document.getElementById("term_"+id).innerText = document.forms['frm'].term.value;
			document.getElementById("definition_"+id).innerText = document.forms['frm'].definition.value;
			document.getElementById("term-edit_"+id).style.display = 'none';
			document.getElementById("definition-edit_"+id).style.display = 'none';
			document.getElementById("save_"+id).style.display = 'none';
		}

		function ajax_exec(httpRequest) {
			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					id = httpRequest.responseText;
					n = document.getElementById("number");
					ns = parseInt(n.value) - 1;
					if (ns == 0) {
						document.reload();
					} else {
						n.value = ns.toString();
					}
				} else {
					alert('There was a problem with the request.');
				}
			}
		}
	</script>
	<style>
		.term {font-weight:bold; margin-left:10px}
		.definition {margin-left:25px;}
	</style>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1>HDTV Glossary</h1>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<p>
				The following are some descriptions for terms of video and audio concepts as they relate to digital television
				(<a href="#DTV+%28Digital+Television%29">DTV</a>). It was written and is maintained by our own
				<a href="<?=URL_ABOUT_CONTACT?>?name=lamaestra">Rodolfo La Maestra</a>.
				Please <a href="<?=URL_HELP_FEEDBACK?>">let us know</a> if you have any suggestions for additional terms.
			</p><p>
				Last Updated: May 2007
			</p><div align="center"><div style="font-size:1.5em; line-height:1.5em; font-weight:bold; text-align:center; width:75%"><?
				$qry = "SELECT DISTINCT LEFT(term, 1) letter FROM glossary ORDER BY letter";
				$result = mQuery($qry);
				$letters = array();
				while ($row = mysql_fetch_assoc($result)) {
					$letters[] = $row[letter];
				}
				foreach ($letters as $letter) {
					$index[] = "<a href='#$letter'>$letter</a>";
				}
				echo implode(' | ', $index) .'</div></div>';

				if (access(ACCESS_ADMIN)) {?>
					<form name="frmnew" method="post" action="<?=PHP_SELF?>">
						<input type="hidden" name="id" value="">
						<input type="button" class="button_green" id="savenew" name="save" value="SAVE NEW" onclick="document.forms['frmnew'].submit()" /><br />
						<input type="text" id="term" name="term" value="" class="term">
						<div style="margin-right:30px"><textarea id="definition" name="definition" rows="10" class="definition"></textarea></div>
					</form>
				<? }

				foreach ($letters as $letter) {
					echo "<a name='$letter'></a><h2>$letter</h2>";
					$qry = "SELECT * FROM glossary WHERE LEFT(term, 1) = '$letter' ORDER BY term";
					$result = mQuery($qry);
					while ($row = mysql_fetch_assoc($result)) {
						if (access(ACCESS_ADMIN)) {
							echo '<input type="button" class="button_blue" id="edit_'. $row[id] .'" name="edit" value="EDIT" onclick="edit('. $row[id] .')" />'.
							'<input type="button" class="button_green" id="save_'. $row[id] .'" style="display:none" name="save" value="SAVE" onclick="save('. $row[id] .')" />';
						}

						echo '<a name="'. urlencode($row[term]) .'"></a>'.
						'<div style="float:right;">[ <a href="#'. urlencode($row[term]) .'">Link to term</a> ]</div>'.
						'<h3 class="term" id="term_'. $row[id] .'">'. $row[term] .'</h3>'.
						'<input type="text" id="term-edit_'. $row[id] .'" value="'. $row[term] .'" class="term" style="display:none">'.
						'<div class="definition" id="definition_'. $row[id] .'">'.
							nl2br(stripslashes($row[definition])) .
						'</div>'.
						'<div style="margin-right:30px"><textarea id="definition-edit_'. $row[id] .'" rows="10" class="definition" style="display:none;">'.
							stripslashes($row[definition]) .
						'</textarea></div>'.
						'<div class="dottedline" style="margin:5px 0 5px 25px"></div>';
					}
				}
			?>
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div><br />
			<div align="right">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</td>
	</tr></table>

	<form name="frm" method="post" action="<?=PHP_SELF?>">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="term" value="">
		<input type="hidden" name="definition" value="">
	</form>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
