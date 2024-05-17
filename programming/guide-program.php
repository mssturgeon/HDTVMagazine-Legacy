<?
	require('../global.php');
#	if (!access(ACCESS_PREMIUM)) prompt_login(PHP_SELF);

	require_once(BASE_DIR .'/includes/lib_guide.php');

	$debug = isset($_GET['debug']);
	$program_id = $_GET['p'];
	$source_id = $_GET['s'];
	$air_time = $_GET['a'];
	$prog_highlight = "$program_id:$source_id:$air_time";

### If not enough parameters were specified, fail-over to a search page
	if ($program_id == '' || $source_id == '' || $air_time == '') {
		header('Location: /programming/search.php');
		exit();
	}

	$sql = "
	SELECT *
	FROM
		prog_schedule schedule,
		prog_program program LEFT JOIN prog_premise premise ON program.series_id = premise.series_id,
		prog_source source,
		prog_lineup lineup,
		prog_genre genre,
		aux_prog_source a
	WHERE schedule.program_id = program.program_id
		AND schedule.source_id = source.source_id
		AND schedule.source_id = lineup.source_id
		AND schedule.source_id = a.source_id
		AND schedule.program_id = genre.program_id
		AND schedule.source_id = '$source_id'
		AND schedule.program_id = '$program_id'";
	$result = mQuery($sql);
	if ($debug) echo "$sql\n";
	if (mysql_num_rows($result) == 0) {
		js_back('Invalid Request. No matching program found.');
		exit();
	}
	$row = mysql_fetch_assoc($result);

	// Get the display station: Affiliate if ota, name if otherwise
	$network = ($row['affiliation_1'] == '') ? $row['full_name'] : $row['affiliation_1'];

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - <?=($row['title_128'] .' on '. $network)?></title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script language="javascript" type="text/javascript">
		function search(term) {
			opener.location.href = '<?=URL_SEARCH?>?tf_genre_desc='+ term;
		}

		function init() {
			// The purpose of this function is to launch the program detail in a new window if sourced from a frameset
			if (top != self) {
				mw = window.open(self.location.href);
				self.back();
			}
		}
	</script>
</head>
<body onload="init()"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');

	/*
		# Get Sports Logos
		if ($row['tf_title'] == 'NFL Football') {
			$team1 = strleft($row['tf_epi_title'], ' at');
			$team2 = strright($row['tf_epi_title'], 'at ');
			$image1 = '<img src="/images/logos/nfl/'. rawurlencode($team1) .'.gif" alt="'. $team1 .'" />';
			$image2 = '<img src="/images/logos/nfl/'. rawurlencode($team2) .'.gif" alt="'. $team2 .'" />';
		}
	*/
	$breadcrumbs = '&raquo; <a href="/programming/index.php">Programming</a>&nbsp;';
	if ($row['source_type'] == 'Broadcast') {
		$breadcrumbs .= '&raquo; <a href="/programming/broadcast.php">Broadcast</a>&nbsp;'.
		'&raquo; <a href="/programming/broadcast-market.php?dma_name[]='. $row['dma_name'] .'">'. urlencode($row['dma_name']) .' Market</a>&nbsp;';
	}
	$breadcrumbs .= '&raquo; <img src="'. BASE_IMG_HOST .'/images/logos/'. str_replace('[size].', '15.', $row['img']) .'" alt="" align="absmiddle"> '.
	'<a href="/programming/guide-station.php?s='. $row['source_id'] .'">'. $row['full_name'] .'</a>&nbsp;'.
	'&raquo; '. $row['title_128'] .'<br /><br />';

	$other_info[] = ($row['stereo'] == 'Y') ? 'Stereo' : '';
	$other_info[] = ($row['closed_captioned'] == 'Y') ? 'Close Captioned' : '';
	$other_info[] = ($row['sap'] == 'Y') ? 'Close Captioned' : '';
	$other_info[] = ($row['repeatYN'] == 'Y') ? 'Repeat' : '';
	$other_info[] = ($row['bw'] == 'Y') ? 'B/W' : '';
	$other_info[] = ($row['live'] == 'Y') ? 'Live' : '';
	$other_info[] = ($row['new'] == 'Y') ? 'New' : '';
	$other_info[] = ($row['taped'] == 'Y') ? 'Taped' : '';
	$other_info[] = ($row['subtitled'] == 'Y') ? 'Subtitled' : '';
	$other_info[] = ($row['joined_in_progress'] == 'Y') ? 'Joined in progress' : '';
	$other_info[] = ($row['dubbed'] == 'Y') ? 'Dubbed' : '';
	$other_info[] = ($row['subject_to_blackout'] == 'Y') ? 'Subject to blackout' : '';
	$other_info[] = ($row['letter_box'] == 'Y') ? 'Letterbox' : '';
	$other_info[] = ($row['descriptive_video_service'] == 'Y') ? 'Descriptive Video Service' : '';
	$other_info[] = ($row['hdtv'] == 'Y') ? 'HDTV' : '';
	$other_info[] = ($row['syndicated'] == 'Y') ? 'Syndicated' : '';
	$other_info = array_unique($other_info);

	if ($is_sport) echo $image1 .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. $image2 .'<br />';

### Show Credits

### Pull list of zip codes serviced from zipcode table

### Pull list of ratings
	$sql = "SELECT * FROM prog_rating WHERE program_id = '$program_id'";
	$res_rating = mQuery($sql);
	while ($row_rating = mysql_fetch_assoc($res_rating)) {
		$rating_type = str_replace($row_rating['rating_type'], ' ', '');
		$rating_desc['$rating_type'] = "{$row_rating['rating']} ({$row_rating['rating_type']})";
		$rating_img['$rating_type'] = '<img src="'. BASE_IMG_HOST .'/images/programming/rating/'. strtolower($rating_type .'-'. $row_rating['rating'] .'_25.gif') .'" alt=" '. $row_rating['rating'] .' ('. $row_rating['rating_type'] .') " align="right" style="padding-left:5px"/>';
	}
	if ($row['tv_rating'] != '') { # Overrides data from ratings table
		$rating_desc['US TV'] = $row['tv_rating'] .' (US TV)';
		$rating_img['US TV'] = '<img src="'. BASE_IMG_HOST .'/images/programming/rating/'. strtolower('ustv-'. $row['tv_rating'] .'_25.gif') .'" alt=" '. $row['tv_rating'] .' (US TV) " align="right" style="padding-left:5px"/>';
	}
?>

	<h1><?=(implode('', $rating_img) . $row['title_128'])?></h1>

	<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
		<? include(BASE_DIR .'/ads/mrectangle.php');?>
		<br />
		<div align="center">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
	</div>
	<div>
		<?=$breadcrumbs;?>

		<a href="/programming/guide-station.php?s=<?=$row['source_id']?>"><img src="<?=BASE_IMG_HOST?>/images/logos/<?=str_replace('[size].', '120.', $row['img'])?>" alt="<?=$row['full_name']?>" align="right"></a>

		<? if ($row['subtitle'] != '') {?>
			<b>Subtitle:</b> <?=$row['subtitle']?><br />
		<? }?>
		<? if ($row['episode_title'] != '') {?>
			<b>Episode: </b><?=$row['episode_title']?> (<?=$row['episode_number']?>)</br />
		<? }?>
		<b>Description: </b><?=$row['long_description']?>

		<fieldset id="program-information">
			<legend>Program Details</legend>
			<label>Title:</label> <?=$row['title_128']?><br />
			<? if ($row['subtitle'] != '') {?>
				<label>Subtitle:</label> <?=$row['subtitle']?><br />
			<? }?>
			<? if ($row['tv_rating'] != '') {?>
				<label>TV Rating:</label> <?=$row['tv_rating']?><br />
				<label>TV Rating Advisory:</label> <?=$row['tv_advisory']?><br />
			<? }?>
			<label>Genre:</label> <?=$row['genre']?><br />
			<label>Description:</label> <div style="display:table"><?=$row['long_description']?></div><br />
			<label>HDTV Resolution:</label> <?=$row['hdtv_level']?><br />
			<label>Audio Level:</label> <?=$row['audio_level']?><br />
			<? if ($row['episode_title'] != '') {?>
				<label>Episode Title:</label> <?=$row['episode_title']?><br />
				<label>Episode Number:</label> <?=$row['episode_number']?><br />
			<? }?>
			<label>Run time:</label> <?=$row['run_time']?><br />
			<label>Release Year:</label> <?=$row['release_year']?><br />
			<label>Original air date:</label> <?=$row['original_air_date']?><br />
			<label>Event date:</label> <?=$row['event_date']?><br />
			<label>Movie type:</label> <?=$row['movie_type']?><br />
			<label>Star rating:</label> <?=$row['star_rating']?><br />
			<label>Languages:</label> <?=$row['program_languages']?><br />

			<label>Other Information:</label> <?=implode(', ', $other_info)?>
		</fieldset>

		<fieldset id="airing-information">
			<legend>Additional Airings (on this station)</legend>
			<table class="bare" cellspacing="0" cellpadding="0"><?
				// Get Additional Air Dates
				$qry = "
				SELECT DISTINCT program_id, source_id, air_time
				FROM prog_guide
				WHERE program_id = '$program_id'
					AND source_id = '$source_id'
					AND air_time <> '$air_time'";
				$result = mQuery($qry);

				if (mysql_num_rows($result) == 0) echo '<tr><td>&lt;None&gt;</td></tr>';
				while ($air_dates = mysql_fetch_assoc($result)) {
					echo '<tr><td>'.
					'<a href="/programming/guide-program.php?p='. $air_dates['program_id'] .'&amp;s='. $air_dates['source_id'] .'&amp;a='. $air_dates['air_time'] .'">'. gmdate('M j, Y g:ia', $air_dates['air_time'] + $user->data['time_zone_offset']) .'</a>'.
					'</td></tr>';
				}
			?></table>
		</fieldset>

		<fieldset id="airing-information">
			<legend>Additional Airings (on other stations)</legend>
		</fieldset>
		<?
			# Set grid parameters
			$tsSize = 30;
  			$start_display_time = $row['tf_air_time'] - ($row['tf_air_time'] % ($tsSize*MINUTES));

			# Load parameters for the grid based on which user is logged in, if any
			if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
				$user_id = $user->data['user_id'];
				$user_time_offset = $user->data['time_zone_offset'];
				$show_sd = $user->data['opt_showsd'];
				$user_icons = $user->data['icons'];
	  	} else { // Set to Eastern, adjusted for DST
				$user_id = 0;
				$user_time_offset = -18000 + (1*HOURS*date("I"));
				$show_sd = true;
				$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
			}
			?>
			<div style="font-size:16pt;font-weight:bold;"><?=(gmdate('j F, Y	g:ia - ', $start_display_time + $user_time_offset) . gmdate('g:ia', $start_display_time + $user_time_offset + 3*HOURS))?></div>
			<?
#				display_grid($start_display_time, $tsSize, $user_time_offset, 6, $user_id, $show_sd, $user_icons, $prog_highlight);
			?>
	</div>

	<? if (access(ACCESS_ADMIN_ANY)) {?>
		<br />
		<table class="type1b">
			<tr><td colspan="2" class="type1b_header">Data (Admin Only)</td></tr><?
				foreach ($row as $name => $value) {
					echo '<tr><td class="grid" nowrap="nowrap">'. $name .':</td><td class="grid">'. $value .'</td></tr>';
			 	}?>
		</table>
	<?}?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
