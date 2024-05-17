<?
	require('../global.php');
	$debug = isset($_GET[debug]);

	$category = isset($_GET[category]) ? rawurldecode($_GET[category]) : '';
	$sort = isset($_GET[sort]) ? $_GET[sort] : 'model';
	switch ($sort) {
		case 'model':
			$sort_display = '<b>Model Name</b> [<a href="?category='. $category .'&sort=price">Price</a>]';
			$order_by = 'LowestNewPrice';
			break;
		case 'price':
			$sort_display = '[<a href="?category='. $category .'&sort=model">Model Name</a>] <b>Price</b>';
			$order_by = 'Model';
			break;
		default:
			js_replace('/equipment/index.php');
			exit;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Equipment - <?=$category?>s</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="JavaScript" type="text/javascript">
		function show_images() {
			link = document.getElementById('show');
			link.style.display = 'none';

			imgs = document.getElementsByName('img');
			for (x=0;x<imgs.length;x++) {
				imgs[x].style.display = 'inline';
			}

			link = document.getElementById('hide');
			link.style.display = 'inline';
		}

		function hide_images() {
			link = document.getElementById('hide');
			link.style.display = 'none';

			imgs = document.getElementsByName('img');
			for (x=0;x<imgs.length;x++) {
				imgs[x].style.display = 'none';
			}

			link = document.getElementById('show');
			link.style.display = 'inline';
		}
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header-4.php');
	?>

	<h1><?=$category?>s</h1>

	<span style="text-align:right;width:100%">Sorted by: <?=$sort_display?></span><br /><br />
	<table class="type1b" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="type1b_header" style="text-align:center;font-weight:normal">
				<a id="show" href="javascript:show_images()">Show Images</a>
				<a id="hide" style="display:none" href="javascript:hide_images()">Hide Images</a>
			</td>
			<td class="type1b_header">Manufacturer</td>
			<td class="type1b_header">Model</td>
			<td class="type1b_header">Title</td>
			<td class="type1b_header">Rating</td>
			<td class="type1b_header">List</td>
			<td class="type1b_header">New</td>
			<td class="type1b_header">Used</td>
			<td class="type1b_header">Warranty</td>
		</tr>
		<?
			$sql = "
			SELECT *
			FROM az_main m, az_attributes a, az_aux aux
			WHERE aux.type = '$category'
				AND m.ASIN = a.ASIN
				AND m.ASIN = aux.ASIN
			ORDER BY $order_by";
			if ($debug) echo "$sql<br />";
			$result = mQuery($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$url = URL_EQUIPMENT_MODEL .'?man='. rawurlencode($row[alias]) .'&model='. rawurlencode($row[model_name]);
				$img_amazon = "<a target='_blank' href='$row[DetailPageURL]'><img name='img' style='display:none' src='$row[SmallImageURL]' alt='$row[Title]'></a>";
				$img_rating = ($row[AverageRating] == 0) ? '(Unrated)' : '<img src="/images/stars5-'. $row[AverageRating] .'.gif" alt="'. $row[AverageRating] .'" align="absmiddle">';

				echo '<tr>'.
				'	<td class="grid" style="text-align:center">'. $img_amazon .'</td>'.
				'	<td class="grid">'. $row[Manufacturer] .'</td>'.
				'	<td class="grid">'. $row[Model] .'</td>'.
				'	<td class="grid">'. $row[Title] .'</td>'.
				'	<td class="grid">'. $img_rating .'</td>'.
				'	<td class="grid">'. sprintf('$%01.2f', $row[ListPrice]/100) .'</td>'.
				'	<td class="grid">'. sprintf('$%01.2f', $row[LowestNewPrice]/100) .'</td>'.
				'	<td class="grid">'. sprintf('$%01.2f', $row[LowestUsedPrice]/100) .'</td>'.
				'	<td class="grid">'. $row[Warranty] .'</td>'.
				"</tr>\n";
			}
		?>
	</table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
