<?
	require('../global.php');
	require('model-overall_header.php');

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - News: <?=$title?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<h1>News: <?=$title?></h1>
	<div id="eq-mrec"><? include(BASE_DIR .'/ads/mrectangle.php');?></div>
	<?=$breadcrumbs?>

	<? include('model-page_header.php');?>

	<br id="eq_br" />

	<div id="eq-sky"><? include(BASE_DIR .'/ads/skyscraper.php');?></div>

	<div style="display:table">
		<?=getTabHeader($tabs);?>
		<div class="tab-content">
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;"><?
				$search_fields = 'title, description';
				$terms = $row[display_name] .' '. $row[model_name];
				$qry = "
				SELECT title, link, pubDate, source, description, rank,
				MATCH $search_fields AGAINST ('$terms') as search_rank
				FROM hdtv_rss
				WHERE rank >= 0
					AND MATCH $search_fields AGAINST ('$terms')";
				$search_result = mQuery($qry);

				$x = 0;
				while ($row = mysql_fetch_assoc($search_result)) {
					$rating = ($row[rank] == 0) ? '(Unranked)' : '<img src="/images/bluestars_'. $row[rank] .'.0.gif" alt="'. $row[rank] .'" align="absmiddle">';
					echo '<tr>'.
						'<td style="width:75px;padding-top:5px">'. $rating .'</td>'.
						'<td style="padding-top:5px">'.
							'<a style="font-weight:bold" href="javascript:show(\''. $x .'\');">'. stripslashes($row[title]) .'</a>'.
							'<span style="font-weight:normal"> ('. $row[source] .', '. gmdate('n/d/Y g:ia', $row[pubDate] + $user->data['time_zone_offset']) .')</span>'.
						'</td>'.
					'</tr><tr>'.
						'<td style="" colspan="2">'.
							'<span id="'. $x .'" style="display:none;">'.
								strip_tags($row[description], '<br>') .' (<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?'. $row[link] .'">Read Full Article</a>)'.
							'</span></td>'.
					'</tr>';
					$x++;
				}
			?></table>
		</div>
	</div>

	<? if (access(ACCESS_ADMIN_ANY)) {
		include(BASE_DIR .'/includes/lib_admin.php');
		echo getRowInformation($row);
	}?>

	<?
		include(BASE_DIR .'/includes/body_footer-4.php');
		include('model_footer.php');
	?>
</div></body>
</html>
