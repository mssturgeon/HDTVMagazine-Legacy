<?
	require('../global.php');
	require(BASE_DIR .'/includes/lib_guide.php');

	$debug = isset($_GET['debug']);
	$guide_refresh = $user->data['guide_refresh'];
	$refresh_interval = ($guide_refresh > 0) ? rand($guide_refresh-30, $guide_refresh+30) : 0;

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Programming Guide</title>
	<? ($refresh_interval > 0) ? print '<meta http-equiv="refresh" content="'. $refresh_interval .'">'."\n" : print ''?>
	<meta http-equiv="expires" value="-1">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="description" content="HDTV Magazine Programming Guide - Customize your stations, channel labels and receive it daily via email">
	<meta name="keywords" content="hdtv,hdtv programming,hdtv guide,hdtv schedule,hdtv listing,hdtv guides,hdtv schedules,hdtv listings,hdtv programs,hdtv programming guide,hdtv programming schedule,hdtv program,hd guide,hd programming,hd schedule,abc hdtv programming,cbs hdtv programming,digital tv programming,digital tv schedule,digital tv schedules,dtv listings,dtv program,dtv programing,dtv programming,dtv schedule,hd programing,hd programming,hd programs,high definition guide,high definition programing,high definition programming,high definition programs,high definition schedule,nbc hdtv programming,guide,news,sports,hdtv stations">

	<link rel="stylesheet" type="text/css" href="<?=BASE_IMG_HOST?>/min/?b=css&f=guide.css,prototip.css">
	<? require(BASE_DIR .'/includes/page_header.php');?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js"></script>
	<script type="text/javascript" src="<?=BASE_IMG_HOST?>/min/?f=js/prototip/prototip.js"></script>
	<script type="text/javascript">
		// Make sure we're not running in a frameset
		if (top != self) top.location.href = self.location.href;

		// Bind tooltips
		document.observe('dom:loaded', function() {
			$$('div.pPrototip').each(function(element) {
				new Tip(element.parentNode, element.innerHTML, {
					style:'hdtvmagazine',
					title:element.title
				});
			});
		});

		function showTime(t) {
			l = document.location.href.indexOf('?');
			document.location.href = document.location.href.substr(0, l) + '?s='+t;
		}
	</script>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');

		$gmt_time = isset($_GET['s']) ? $_GET['s'] : time();
		if (isset($_GET['primetime'])) {
			$gmt_time = strtotime($user->data['prime_time'] . str_replace(':', '', strright($user->data['time_zone_text'], 'GMT')));
		}

		// Set grid parameters
		$tsSize = 30;

		// This computes time offset based on the users profile settings
		if (access(ACCESS_PREMIUM)) { // Get current time adjusted to user timezone settings
			$user_id = $user->data['user_id'];
			$user_time_offset = $user->data['time_zone_offset'];
			$bufferTime = $gmt_time + 5*MINUTES;
			$start_display_time = $bufferTime - ($bufferTime % ($tsSize*MINUTES));
			$show_sd = $user->data['options'] & OPT_SHOW_SD;
			$user_icons = $user->data['icons'];
		} else { // Set to 8:00pm Eastern, adjusted for DST
			$user_id = 0;
			$user_time_offset = -18000 + (1*HOURS*date("I"));
			$start_display_time = strtotime('Tomorrow 1:00am GMT') - (1*HOURS*date('I'));
			$show_sd = true;
			$user_icons = ICON_SHOW_NEW & ICON_SHOW_DD;
		}

		if (access(ACCESS_PREMIUM)) { //Only show the date/time selections to registered users
			?><div class="item" style="display:table"><span class="corners-top"><span></span></span>
				<span class="label">Programming Menu:</span>
				<a href="/profile-guide.php">Edit Channels</a>
				&bull; <a href="guide.php?primetime">Primetime Tonight</a>
				<!--
				&bull; <a href="guide-movie.php">Movie Guide</a>
				&bull; <a href="sports/index.php">HDTV Sports</a>
				&bull; <a href="stations/stations-by-market.php">Stations by Market</a>
				&bull; <a href="stations/stations-by-provider.php">Stations by Provider</a>
				&bull; <a href="search.php">Search</a>
				-->
			<span class="corners-bottom"><span></span></span></div>

			<!-- Important Notice -->
			<!--div style="width:50%"><div class="important"><span class="corners-top"><span></span></span>
				<img src="<?=BASE_IMG_HOST?>/images/i_maintenance.gif" alt="Maintenance" style="float:left; margin-right:10px;" />
				<div style="display:table"><span class="label">Program Data Outage:</span>
				We are currently experiencing some difficulties with our programming data feed. We are looking into it and will have it available shortly.<br />
				<br />
				Thank you for your patience.<br />
				<br />
				- Dale &amp; Shane<br /></div>
			<span class="corners-bottom"><span></span></span></div></div-->

			<div><table class="bare" cellspacing="2" cellpadding="2" width="100%"><tr>
			<form name="frmTimeSelect" autocomplete="off">
				<td class="inputLabel">Date:</td>
				<td nowrap>
					<select id="date_select" onChange="showTime(this.value)"><?
						$today = gmdate("j", $gmt_time);
						$result = mQuery("SELECT DISTINCT FROM_UNIXTIME(air_time,'%Y-%m-%d') as air_date FROM prog_guide ORDER BY air_date");
						while($row = mysql_fetch_array($result)) {
							$air_date = strtotime($row[air_date] ." ". gmdate("g:ia", $start_display_time) ." GMT");
							$selected = gmdate("j", $air_date) == $today ? 'SELECTED' : '';
							echo '<option value="'. $air_date .'" '. $selected .'>'. gmdate("l, j F, Y", $air_date + $user_time_offset);
						}
					?></select>
				</td>
				<td class="inputLabel">Time:</td>
				<td>
					<select id="time_select" name="grid_start" onChange="showTime(this.value)"><?
						$start = strtotime("Today 12:00am", $gmt_time);
						$increment = 60*MINUTES;
						for ($x = $start; $x < $start + 24*HOURS; $x += $increment) {
							$selected = ($x == floor($start_display_time/$increment) * $increment) ? 'SELECTED' : '';
							echo '<option value="'. $x .'" '. $selected .'>'. gmdate("g:ia", ($x+$user_time_offset)) .' - '. gmdate("g:ia", ($x+$user_time_offset)+($tsSize*$user->data['guide_width']*MINUTES)) ."\n";
						}
					?></select>
				</td>
				<td class="inputLabel" align="left" nowrap><?=$user->data['time_zone_text']?></td>
				<td align="right" width="100%">
					<? echo getProgramLegend();?>
				</td>
			</form>
			</tr></table>
			<? displayGridGuide($start_display_time, $tsSize, $user_time_offset, $user->data['guide_width'], $user_id, $show_sd, $user_icons);?>
			<div style="font-size:7pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>
		<?} else { ?>
			<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="vertical-align:top; padding-right:10px">
				&raquo; <a href="<?=URL_PROG?>">Programming</a>
				&raquo; <b>Guide</b>

				<div class="important" style="display:table"><span class="corners-top"><span></span></span>
					<table class="bare"><tr><td style="vertical-align:top">
						<img src="/images/i_subscribe.gif" alt="Subscribe" style="float:left; margin-right:10px" />
					</td><td>
						<div class="label"><a href="/subscribe/index.php">Subscribe</a> to a Premium Membership and customize your guide:</div><br />
						<ul>
							<li>Add your local network, cable &amp; satellite channels</li>
							<li>Show up to 8 hours at a time
							<li>Rename and assign channel numbers</li>
							<li>Receive a daily listing of HD programming via email</li>
							<li>Hide advertisements</li>
							<li>and more ...</li>
						</ul>
						<a href="/subscribe/index.php">Click here for more details</a>
					</td></tr></table>
				<span class="corners-bottom"><span></span></span></div>
			</td><td style="vertical-align:top; width:300px">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</td></tr></table><br />
			<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="vertical-align:top; padding-right:10px">
				<table class="bare" width="100%"><tr>
					<td style="font-size:16pt;font-weight:bold;padding-top:10px;">East Coast Prime Time</td>
					<td style="font-size:10pt" valign="bottom" align="right"><?=gmdate("j F, Y", $start_display_time + $user_time_offset)?></td>
				</tr></table>
				<? displayGridGuide($start_display_time, $tsSize, $user_time_offset, $user->data['guide_width'], $user_id, $show_sd, $user_icons);?>
				<div style="font-size:7pt">* The double-D symbol is a registered trademark of Dolby Laboratories.</div>
			</td><td id="ad-right" >
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</td></tr></table>
		<?}?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>