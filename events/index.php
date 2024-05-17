<?
	require('../global.php');
	$today = date('Y-m-d');

	include(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - HDTV Events</title>
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="ad-left">
			<? include(BASE_DIR .'/ads/skyscraper.php');?>
		</td><td style="padding-left:10px;vertical-align:top">
      	<h1>HDTV Events</h1>

	     	<?
				if ($user->data['subscriptions'] & SUB_EVENTS) {} else {
	     			echo '<div align="center"><div class="alertbox">'.
	     			'	<b>Receive HDTV Event updates via email.</b> <a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive'.
					'	HDTV Event updates as they are posted.'.
	     			'</div></div>';
	     		}
			?>

      	<p>
      		Nothing grows an idea better than a great event or conference about it. Stay tuned with HDTV Magazine for important announcements for upcoming events, seminars, and conferences that will shape your future.
      	</p>

			<? include(BASE_DIR .'/ads/banner_events.php')?>
			<div align="center"><?=$ad[$x]?></div><br>
			<br>

			<h2>Upcoming HDTV Events</h2>
			<table class="type1b" cellpadding="0" cellspacing="0" summary="" style="width:100%">
				<tr>
					<td class="type1b_header">Event</td>
					<td class="type1b_header">Location</td>
					<td class="type1b_header">Start / End</td>
				</tr>
				<?
					$result = mQuery("SELECT * FROM event WHERE rank = 1 AND start_time > '$today' ORDER BY start_time ASC");
					while ($row = mysql_fetch_assoc($result)) {
						$url = ($row[link] == '') ? $row[title] : '<a target="_blank" href="/cgi-bin/ntlinktrack.cgi?'. $row[link] .'" onmouseover="return link_over(this)" onmouseout="return link_out(this)">'. $row[title] .'</a>';
						echo '<tr>'.
						'	<td class="type1b" nowrap>'. $url .'</td>'.
						'	<td class="type1b">'.
								$row[venue_name] .', '.
 								$row[city_name] .', '.
								$row[region_name] .', '.
								$row[country_name] .
						'	</td>'.
						'	<td class="type1b" nowrap>'.
							date('M j, Y', strtotime($row[start_time])) .'<br>'.
							date('M j, Y', strtotime($row[stop_time])) .'</td>'.
						'</tr>';
					}
				?><tr>
				</tr>
			</table>
		</td>
	</tr></table>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</div></body>
</html>
