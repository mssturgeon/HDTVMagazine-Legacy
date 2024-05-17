<?
	require('global.php');
	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Active Polls</title>
	<meta name="description" content="All active polls in the HDTV Magazine Forum">
	<meta name="keywords" content="hdtv,hd tv,high definition,high def tv,high definition television,high definition tv,polls,questions">
	<meta name="rating" content="general">
	<? require(BASE_DIR .'/includes/page_header.php');?>
</head>
<body>
	<? include(BASE_DIR .'/includes/body_header-4.php');?>

				<h1>Active Polls</h1>
				<table class="bare" cellpadding="0" cellspacing="0">
				<?
					$result = mQuery("SELECT * FROM phpbb_vote_desc");
					while ($row = mysql_fetch_assoc($result)) {
						// Check to see if user has voted
						$vote = mQuery("SELECT * FROM phpbb_vote_voters WHERE vote_id = {$row['vote_id']} AND vote_user_id = ". $user->data['user_id']);
						$image = (mysql_num_rows($vote) > 0) ? '<img src="/images/i_yes.gif" alt="Voted">' : '&nbsp;';
						echo '<tr>'.
						'	<td style="padding-bottom:10px">'. $image .'</td>'.
						'	<td style="padding-bottom:10px"><a href="/forum/viewtopic.php?t='. $row['topic_id'] .'">'. $row['vote_text'] .'</a></td>'.
						'</tr>';
					}
				?>
				</table>

				<?
					function getPoll($vote_id) {
						$now = time(); // Used to prevent caching

      				echo '<div align="center">'.
         			'	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="250" height="250" id="FusionCharts" viewastext>'.
         			'		<param name=movie value="/charts/FC_2_3_Bar2D.swf?n='. $now .'&amp;dataUrl=/xml/poll_xml.php?vote_id='. $vote_id .'&amp;chartWidth=250&amp;chartHeight=250">'.
         			'		<param name=FlashVars value="">'.
         			'		<param name=quality value="high">'.
						'		<param name="bgcolor" value="#ffffff">'.
         			'		<embed src="/charts/FC_2_3_Bar2D.swf?n='. $now .'&amp;dataUrl=/xml/poll_xml.php?vote_id='. $vote_id .'&amp;chartWidth=250&amp;chartHeight=250" FlashVars="" quality="high" bgcolor="#ffffff" width="250" height="250" name="FusionCharts" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.
         			'	</object><br>'.
         			'	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="250" height="150" id="FusionCharts" viewastext>'.
         			'		<param name=movie value="/charts/FC_2_3_SSGrid.swf?n='. $now .'&amp;dataUrl=/xml/poll-data_xml.php?vote_id='. $vote_id .'&amp;chartWidth=250&amp;chartHeight=150">'.
         			'		<param name=FlashVars value="&alternateRowBgColor=CCCC00&alternateRowBgAlpha=10&listRowDividerColor=FFAF00&listRowDividerAlpha=70">'.
         			'		<param name=quality value="high">'.
						'		<param name="bgcolor" value="#ffffff">'.
         			'		<embed src="/charts/FC_2_3_SSGrid.swf?n='. $now .'&amp;dataUrl=/xml/poll-data_xml.php?vote_id='. $vote_id .'&amp;chartWidth=250&amp;chartHeight=250" FlashVars="" quality="high" bgcolor="#ffffff" width="250" height="250" name="FusionCharts" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'.
         			'	</object>'.
      				'</div>';
					}
					getPoll(1);
				?>

	<? include(BASE_DIR .'/includes/body_footer-4.php');?>
</body>
</html>
