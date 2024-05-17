<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 558";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 558 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (8) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$container = 'article_container';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_name = 'hdtv-archive';
			break;
		case 6: # Test
			$container = 'article_container';
			$sub_type = 0;
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$container = 'article_container';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			break;
#		case 9: # Podcasts
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$container = 'article_container';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			$sub_desc_logged_in = '<a href="'. URL_PROFILE_SUBSCRIPTIONS .'">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$sub_desc_anon = '<a href="'. URL_PROFILE_CREATE .'">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			$container = 'body_container';
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="via hdmi, blu ray, analog video, full meal, meal deal, DVD, dvd, video, performance, audio, disc, player, via, hdmi, HDMI, features, analog, support, Blu, Toshiba, application, blu, ray, toshiba, players" />
	<meta name="description" content="One of the biggest complaints with the Blu-ray and HD DVD disc format war is the need to buy separate players for each format. If HD and film is your passion you have a difficult choice because not all movies will be released in both formats. A large portion of the potential &quot;high definition disc&quot; consumers are waiting for one of two things before investing their hard-earned dollars in another format: 1) a clear winner in the format war, or 2) a universal player.  When LG unveiled their universal player at the 2007 Consumer Electronics Show (CES)..." />
	<title>HDTV Magazine Reviews - LG BH100 Universal HD DVD/Blu-ray Player</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/lg_bh100_universal_hd_dvdblu-ray_player';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('LG BH100 Universal HD DVD/Blu-ray Player'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php";
		if ($author[img] != '' && 8 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">LG BH100 Universal HD DVD/Blu-ray Player</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>March  8, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD & Blu-ray Players">HD DVD & Blu-ray Players</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/save.gif" alt="Save Article" align="absmiddle" /><a target="_blank" href="<?=$save_url?>">Save</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<span><img src="/images/print.png" alt="Print Article" align="absmiddle" /><a target="_blank" href="<?=$print_url?>">Print</a></span><br />
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($sub_type > 0 && ($userdata[subscriptions] & $sub_type) || $_SERVER[HTTP_USER_AGENT] == 'Googlebot') {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_logged_in?>
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" />
				<span class="label"><?=$sub_label?>:</span>
				<?=$sub_desc_anon?>
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php&amp;phase=2&amp;title=LG%20BH100%20Universal%20HD%20DVD%2FBlu-ray%20Player&amp;bodytext=One%20of%20the%20biggest%20complaints%20with%20the%20Blu-ray%20and%20HD%20DVD%20disc%20format%20war%20is%20the%20need%20to%20buy%20separate%20players%20for%20each%20format.%20If%20HD%20and%20film%20is%20your%20passion%20you%20have%20a%20difficult%20choice%20because%20not%20all%20movies%20will%20be%20released%20in%20both%20formats.%20A%20large%20portion%20of%20the%20potential%20%22high%20definition%20disc%22%20consumers%20are%20waiting%20for%20one%20of%20two%20things%20before%20investing%20their%20hard-earned%20dollars%20in%20another%20format%3A%201%29%20a%20clear%20winner%20in%20the%20format%20war%2C%20or%202%29%20a%20universal%20player.%20%20When%20LG%20unveiled%20their%20universal%20player%20at%20the%202007%20Consumer%20Electronics%20Show%20%28CES%29...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div id="right" style="float:right; margin:0 0 5px 5px; text-align:center;" align="center">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
			<br />
			<div align="center">
				<?include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>
		</div>
		<div id="<?=$container?>">
			<p><img src="/images/products/lg-bh100.jpg" alt="LG BH100" /><br /></p>

<table class="greygrid">
<tr>
<td>&nbsp;</td>
<td class="greygrid"><b>MSRP</b></td>
<td class="greygrid"><b>Street</b></td>
<td class="greygrid"><b>Amazon.com</b></td>
</tr><tr>
<td class="greygrid"><b>Pricing at publication</b></td>
<td class="greygrid">$1,199.00</td>
<td class="greygrid"><a href="/equipment/model.php?man=LG%20Electronics&model=BH100">$1,074.99</a></td>
<td class="greygrid"><a href="http://www.amazon.com/gp/redirect.html?ie=UTF8&location=http%3A%2F%2Fwww.amazon.com%2FLG-BH100-Blu-Ray-disc-player%2Fdp%2FB000NNK9LY%3Fie%3DUTF8%26s%3Delectronics%26qid%3D1173364182%26sr%3D8-1&tag=hdtvmagazine-20&linkCode=ur2&camp=1789&creative=9325">$1,199.00</a></td></tr>
</table>
<br />
Serial# 701KVDT070385<br />
Warranty: 1 year parts, 90 days labor<br /><br /><br /><b>Summary: Some are going to love it and for others it will not be enough</b><br /><br />One of the biggest complaints with the Blu-ray and HD DVD disc format war is the need to buy separate players for each format. If HD and film is your passion you have a difficult choice because not all movies will be released in both formats. A large portion of the potential "high definition disc" consumers are waiting for one of two things before investing their hard-earned dollars in another format: 1) a clear winner in the format war, or 2) a universal player.  When LG unveiled their universal player at the 2007 Consumer Electronics Show (CES) it was an immediate marketing hit that was tempered by some excluded features and also a past history for not meeting video standards. A player was sent to me for testing by <a href="/cgi-bin/ntlinktrack.cgi?http://www.customht.net">Custom HT</a> for 1 week. This allowed just enough time to check operational and main performance issues. Let's see how well LG faired.

<p><strong>Features</strong></p>

<p><a href="/cgi-bin/ntlinktrack.cgi?http://us.lge.com/download/product/file/1000002028/BH100.pdf">LG BH100 Brochure</a></p>

<p>The unit has all the standard connection types. Like others, you do have analog video support up to 1080i for either HD disc format.</p>

<p><strong>Noteworthy Features</strong></p>

<ul>
<li>1080p24 support!</li>
<li>The remote looked nice and felt natural in my hand, although it does not have a back lit feature for the darkened home theater. </li>
<li>The player color theme is black with a black brushed aluminum top and front panel with glossy plastic side panels. </li>
<li>There are five buttons on the top panel for common commands such as power, play and tray open/close that won't be available in a typical rack of equipment. Those same buttons are on the remote so access is not actually required. That does not change the fact that you would have to have the remote in hand to perform those functions when you walk up to the player and for some that may be an aggravation so plan accordingly. </li>
<li>Speaking of looks the marketing division did a great job on form and fashion for this box. If the player is placed in an open environment it provides a look of elegance and charm to your decor.</li>
</ul>

<p><strong>Features Missing</strong></p>

<ul>
<li>The unit does not carry an HD DVD logo because it does not fully support the format. This is obvious when you put an HD DVD in because rather than get the standard FBI warning and MPAA screens you will get a screen telling you to check the jacket instead and then the movie begins to play. You will never see a disc menu and this also means you will not have access to any special features on the disc or the new HD DVD feature of related materials from the internet. The only HD DVD support provided is playback of the movie itself and nothing more.</li>

<p><li>Unlike most upconverting players that allow 480p, it will not allow anything greater than 480i via analog component video for SD DVD. </li></p>

<p><li>There is no aspect ratio feature for SD DVD letterboxed movies.</li></p>

<p><li>There are no video adjustments to correct for internal or external errors.</li></p>

<p><li>While I did not check the manual, I was unable to find a feature or command in the setup menu to check or upgrade the firmware via the ethernet connection. The connection is labeled for service only, yet by hooking up a remote Wireless G adapter, the player did trigger a communications sequence as if it was an Ethernet port.</li><br />
</ul></p>

<p><strong>HD Disc Testing Notification</strong></p>

<p>At this time there are no commercially available calibration discs for these formats, although DVE has an HD DVD version due for release soon. Without one, it is impossible to provide objective results or know in absolute terms the performance level of the product. The following subjective HD disc observations were made viewing selected scenes from <i>X-Men: The Last Stand</i> on Blu-ray and <i>King Kong</i> on HD DVD via a calibrated system and direct comparisons to the Sony PS3 and Toshiba HD-A1 players. Bear in mind that beyond the disc technology and size of bit stream, both formats use the same video and audio codecs so however Blu-ray responds, so should HD DVD and vice versa.</p>

<p><strong>HD Audio</strong></p>

<p>The desired approach is either PCM multichannel via HDMI or the future capability of HD audio bitstreams via HDMI directly to your A/V receiver coming this year, just as we do now with SD audio.</p>

<p>The only way to hear any of the HD audio codecs is via the 5.1 analog audio outputs. This capability and performance was not tested, although from experience it will not differ greatly from other products from which you can choose. Adjustments are minimal, covering only speaker size and quantity. While the spec sheet claims PCM support, it is not referring to HD audio PCM multichannel via HDMI. I suspect the PCM relates to standard stereo 24/96 that can be sent over SD digital audio connections although I did not have time to test that. Stereo 24/96 PCM was supported via HDMI as well as the standard DTS and Dolby Digital SD codecs.</p>

<p><strong>Analog Video Connection</strong></p>

<p>To my surprise, the player limits SD DVD to 480i allowing 480p, 720p and 1080i for HD disc content only. That said, SD DVD at 480i met video standards and the rest appeared to meet video standards with HD disc. Why 480p is not allowed for SD DVD is curious. Limiting SD DVD to 480i could make or break the use of this player since most displays don't do very well upconverting that scan rate, hence the preference for 480p at minimum to give SD DVD performance playback a fighting chance. 480i analog video is a difficult scan rate to objectively evaluate without a native 480i display or scope. The key spec error here was resolution as both the color and luminance bursts showed a prominent roll off in response which softens the image reducing detail. With the test material of real video content the image was clearly off from the reference yet on the other hand there were no obvious artifacts to complain about which is the key concern for most. In today's world, native 480i analog video has little relevance to inspire me to fully test this feature to determine if the errors were from the scaler of the display or from the player. With HD disc the player appeared to perform just as well as the Toshiba set for a 1080i output.</p>

<p><strong>DVI Connection</strong></p>

<p>From a performance standpoint what we really want is HDMI YPbPr because that is what is on the disc. For DVI applications this signal has to be converted to RGB, hence the potential for errors with video standards. The product failed video standards for RGB DVI. While I was able to compensate for this by turning down the contrast and increasing the brightness using a DVE calibration disc for reference, I could not address an obvious clipping at peak white which was missing some steps, nor could I address a color error. The fact that the player had no video adjustments did not help and could be problematic since it will not match products that do provide correct video standards for consumer video DVI. Color is slightly off in this mode with some red push. I did do a performance check with SD DVD, HD DVD and Blu-ray noting nothing unusual in a general sense but that does not change the fact that analog video or HDMI looked better, as they should since they get it right. This type of error would also require just the right scene to show it so I look forward to the DVE HD DVD for just this reason. Since I don't have that, it is reasonable to assume based on the HDMI and analog video tests that this error is carried to the HD disc side as well.</p>

<p><strong>HDMI Connection</strong></p>

<p>The product did meet video standards with the HDMI YPbPr connection using the DVE disc. The color response was ever so slightly off, so while it is not a reference for studio mastering, it was clearly close enough for consumer performance applications and most would be hard pressed to detect an error with a reference. The top of the image showed 5 pixels cropped. One point to cover is 1080p support. In the setup menu your choice is 1080p, no frame rates. The LG output 24 frames providing no means to change it. During the menu and movie the display remained in 1080p24 mode. Per LG, the product supports both 24 and 30 frame output depending on the source as contained on the disc. Since my displays accept 1080p24 I had no way to confirm that nor compatibility with 1080p60 based displays.</p>

<p><strong>SD DVD Performance via HDMI</strong></p>

<p>In the past LG has had some major problems with their upconverting players and I am pleased to say that this one did a great job via HDMI. That said, it is odd that SD DVD is limited to 1080i and you are taking a performance hit due to that with a 1080p capable display. The Oppo DV981 (currently under review) had the edge for those seeking SD DVD performance at close viewing distances with 1080p capable displays.</p>

<p><strong>HD DVD and Blu-ray Performance via HDMI</strong></p>

<p>Both were simply spectacular using a 1080p24 output to a 1080p24 capable display. With a properly designed player, correct video setup and 1X1 pixel mapping with a quality display you have an untouched hi fidelity signal from the disc bit stream to the final imager of your display and spectacular is to be expected. Fascinating was a direct comparison to a Sony PS3 set for 1080p60 yielding comparable results taking me to the land of nitpicky to resolve a difference with the LG getting a slight edge on detail performance and overall clarity. Clearly the Sony is processing the original 1080p24 bitstream for 2/3 film pulldown for a 1080p60 output.</p>

<p><strong>Conclusion</strong></p>

<p>For performance enthusiasts using current technology the "full meal deal" is true 1080p24 output directly from the disc bit stream for HD disc that adheres to video standards, 1080p24 or 1080p60 for SD DVD upconversion, and HD audio multichannel PCM supporting all HD audio codecs via HDMI or an HD audio bit stream via HDMI for a receiver equipped with the HD audio codecs.</p>

<p>The LG appears to do video imaging science with all three disc formats via HDMI which is a significant achievement for LG. Unfortunately the LG will not be providing the "full meal deal" on a number of levels and for some that will be a deal breaker. SD DVD upconversion is limited to 1080i, lacks HD audio bitstream or multichannel PCM support via HDMI and you can only play HD DVD movie content, no special features. With analog component video, you have the SD DVD limitation of 480i. With DVI you have a potential compatibility problem with other DVI sources and other video errors that cannot be corrected.</p>

<p><strong>Putting it in Perspective</strong></p>

<p>The following nearly two plus pages would not be required if the LG had delivered the performance "full meal deal". The perspective would be quite simple. If you can live without the HD DVD special features, buy it! But the LG didn't and this causes the reviewer and potential buyer to spend time evaluating applications where the product can do well. These days that has become so much more complex due to the of myriad scan rates, connection types, differing formats and features directly compared to the performance level desired by reader.</p>

<p>It is difficult to recommend this player with a DVI input application due to the obvious errors and those that cannot be corrected. The clipping of peak white on this player will require a solution from LG if they even want to address it in a world that has gone HDMI as the 1080p performance standard. While it could arguably work, considering there is no other all in one box alternative at this time, there are other options to consider that will be revealed as I continue. If you have a DVI input then 1080p24 and 1080p60 is likely not even a concern in which case any of the lower budget alternatives will suffice.</p>

<p>HD audio via digital bitstream or PCM multichannel is preferred over an analog multichannel input and player D/A conversion. That said, you can get all the benefits of HD audio minus the sonic signature of your analog multichannel input; most will have a sonic signature degrading sound quality. Nonetheless, that is a huge step in the right audio direction with clear sonic benefits over SD DVD. Like all performance issues only you can draw the line and choose chocolate or vanilla HD audio or even wait for strawberry, HD audio bitstream sources and A/V receivers with HD audio codecs.</p>

<p>My upstairs application represents the mass market. It is a family multimedia room using a native 720p display that performs quite well with either HDMI or analog video at about 5 screen heights with a PC stereo audio system for sound. SD DVD via any of the Oppo products creates a perception of near HD video quality. The LG dovetails into this application with ease via HDMI and the only loss I incur is the lack of special features with HD DVD. In this application the LG could replace the Oppo for SD DVD which would be a good thing considering the limited space I have. Is it worth it? If you can see a difference between SD DVD and HD content then it certainly has value and the LG is one choice for getting both HD disc formats to your screen plus good SD DVD upconversion. Via analog component is another matter for SD DVD and in that case the Oppo would have to stay which, for some will defeat the purpose of having it all in one box.</p>

<p>Over a year ago my downstairs application was a native 480p/1080i CRT RP limited to analog video only, representing the legacy market of older HDTV displays, with a variable viewing distance range of 3-5 screen heights. The LG dovetails into this application with ease as well. SD DVD video is another matter since the player is stuck at 480i in this application. For the most part, that is of no concern as SD DVD playback over component was likely resolved years ago using either a good 480p DVD player or external scaling. If not you can pick up good 480p DVD players for under $80. I also have the room for another box so no matter.</p>

<p>Currently the downstairs application is native 720p or 1080p front projection on a 10 foot wide 2.35 aspect screen at 2.8 screen heights using anamorphic zoom representing the high performance market. Top notch video performance is required in this system due to the close viewing distance making artifacts as plain as day. Performance audio is desired. There are three Blu-ray players currently on the market providing the "full meal deal" for about $1000. For this application the LG falls short. Such a system starts at about $10,000 for everything so what's another $1000 for the "full meal deal" using separate HD DVD and Blu-ray players? Surely one of those is going to get SD DVD upconversion right with a 1080p output as well. The more you are spending on the system the more sense separate players make!</p>

<p>The above comment dwells in an HD DVD "full meal deal" fantasy though because there is none. HD DVD has not received the manufacturer support that Blu-ray did from numerous companies providing choice in the market place. At this time the LG is the only HD DVD player providing a 1080p24 output yet lacks digital HD audio support and HD DVD special features. There is the Xbox 360 doing 1080p60 via analog video with no HD audio support whatsoever or SD upconversion. We have 1st generation Toshiba players with both digital and analog HD audio support limited to 1080i for SD or HD and now 2nd generation Toshiba players with both digital and analog HD audio support limited to 1080p60. There is no HD DVD "full meal deal" out there regardless of price! While the rumor mill claims the new Toshiba HDXA2 will get 1080p24 support via firmware upgrade, Toshiba has yet to provide an official press release confirming this, nor provide any official comment to repeated inquiries by Senior Technical Director of HDTV Magazine, Rodolfo La Maestra, during the 2007 CES and again after CES.</p>

<p>Let us not forget though that the difference between my Sony PS3 at 1080p60 versus the LG at 1080p24 was marginal enough that the 2nd generation Toshiba 1080p60 player remains a viable option considering you will get the HD audio and HD DVD features which in my opinion gets you the closest to the "full meal deal". That conclusion is based on proper 60 frame conversion just like the Sony did! Unfortunately, current HD DVD players leave the performance enthusiast with only pros and cons options.</p>

<p>The above perspective dwells in performance and two machines running about $1000 each so what about those simply looking for better at an affordable price? The LG still has some stiff competition. The Sony PS3 tops out at $600, has full Blu-ray support and in March will be upgraded to 1080p24 output and SD DVD upconversion per press releases. Currently the PS3 is the least expensive Blu-ray player available and based on testing so far that upgrade will make the PS3 the least expensive route to a reference Blu-ray player as well while providing so much more as a gaming system and media center. Mate that with a 1st or 2nd generation Toshiba player at about $500. Then there is the Xbox 360 for $500-600 providing the same gaming and media center advantages as a PS3. For $1000-1200 you have both HD disc and HD console gaming formats covered comparable to the hit and miss of the LG at a similar price range with arguably far more benefits. A PS3 and any of the Toshiba players will get you more for your buck as well, again at about the same price. That leads me to speculating on the Toshiba firmware upgrade. If that should follow through then you are looking at a PS3 for about $600 and the Toshiba for $1000 which would give you the HD disc "full meal deal" for only $400 more than the LG! There was also a press release just prior to publishing from Sony that mid 2007 they will be releasing the BDPS300 Blu-ray player providing the "full meal deal" for $600 breaking the $1000 price point for a conventional performance player. If you are game console adverse or wondering how a PS3 is going to fit into a rack or stack of equipment, another $600 option form Sony is on the horizon.</p>

<p>This new ability to upgrade firmware to fix problems or add features for consumer products has both pros and cons. Historically, firmware upgrades have addressed compatibility problems, not performance or features. This is a new world for consumer entertainment products where many folks are bound to make a purchase based on future promises related to features and performance. I cannot stress enough that if you do so, make sure such promises have been made with a public press release and not the rumor mill. A firmware upgrade related to performance or features can make the conclusion of this review inaccurate, such as providing SD DVD 1080p60 output via HDMI, 480p output via analog video, fix the DVI setup or make HD audio via HDMI suddenly work. All of those together would make the BH100 a hit for any application where the owner is willing to forgo the HD DVD special features. That is the pro side of the equation. The con side of the equation, and what concerns me and the crew of performance enthusiasts out there, is manufacturers using this ability as a substitute for spending the time upfront to release a product that does video standards with all intended features already there. Using the LG as an example, if a firmware upgrade can correct video problems and add missing features later then why wasn't that done prior to release? Personally, if it would have supported HD audio via HDMI now I think I would have bought one! The reason I am making such a deal over this is because we have already had this experience with 1st generation Toshiba HD DVD players and now the PS3. Toshiba never provided a public press release of promised upgrades and fixes yet had all the motivation required to do so at the time in this format war, which they did.  That should not be mistaken as something they will voluntarily do on future product and note the DVI RGB still has a black level error that you have to compensate for and this is not expected to ever be fixed. Why PS3 owners who bought in November and December of 2006 have to wait until March 2007 for 1080p24 support and SD DVD upconversion is curious but in this case Sony did provide a public press release. Due to that the PS3 review has been put on hold as both of those features will expand the application of the product.</p>

<p><strong>Final</strong></p>

<p>The LG is pretty and sleek providing better performance than expected in some areas while being limited in others. Products like that still have an application and for the LG BH100 there are a number it can fill. Is HD disc 1080p24 video your priority? LG delivers. Looking for better performance in a convenient and space saving one box solution for three disc players? LG has that sexy box for you. Want both Blu-ray and HD DVD capability for less? LG provides an option. The one application it can't fill is the HD and SD disc total performance package in one box and those seeking it are going to have to wait or give up on the one box solution by choosing other products and alternatives.</p>

<p><strong>1 Week Disclaimer</strong></p>

<p>I had the BH100 for one very busy week only. With other reviews I get 2-3 months to actually live with a product which can easily change a short term conclusion and application perspectives.</p>

<p><strong>Links</strong></p>

<p><a href="/forum/viewtopic.php?t=6873">Toshiba HD-XA2 - will it or will it not support 1080p24?</a></p>

<p><a href="/forum/viewtopic.php?t=6868">What is your "High Definition DVD" position?</a></p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>March  8, 2007 08:24 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 558
 				AND a.topic_id = t.topic_id
 				AND t.topic_id = p.topic_id
 				AND p.post_id = pt.post_id
 			ORDER BY post_time";
 			$result = mQuery($sql);
 			$num_comments = mysql_num_rows($result);
 			
 			if ($num_comments > 0) {
 				# Skip the first one, as it's just the excerpt post.
 				$row = mysql_fetch_assoc($result);
 				$thread_url = URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id];
 				echo '<h2 style="margin-bottom:10px"><a href="'. $thread_url .'">Reader Commentary</a></h2>'.
 				'<div class="item"><span class="corners-top"><span></span></span>'.
 					'<img src="/images/icon_topic.gif" alt="" /><b> See Forum Topic</b>: '.
 					'<a href="'. $thread_url .'">'. $row[topic_title] .'</a> <span class="grey">('. $row[topic_replies] .' replies)</span>'.
 				'<span class="corners-bottom"><span></span></span></div>';
 				
 				$x = 0;
 				while ($row = mysql_fetch_assoc($result)) {
 					if ($x == 10) break;
 					$x++;
 					$comment_url = URL_FORUM_VIEWTOPIC .'?p='. $row[post_id] .'#'. $row[post_id];
 					$text = strip_tags(str_replace('[', '<', str_replace(']', '>', $row[post_text])));
 					if ($row[post_subject] != '') {
 						$subject = $row[post_subject];
 					} else {
 						$subject = "Re: $row[topic_title]";
 					}
 	
 					$class = ($x % 2 == 0) ? 'item' : 'item_odd';
 					echo '<div class="'. $class .'"><span class="corners-top"><span></span></span>'.
 						'<div style="font-size:1.2em; font-weight:bold"><a href="'. $comment_url .'">'. $subject .'</a></div>'.
 						'<b>'. $row[poster_id] .'</b> '. date('M j, g:ia', $row[dt]) .'<br />'.
 						$text .
 					'<span class="corners-bottom"><span></span></span></div>';
 				}
 			}
 			if ($num_comments > $x) {
 				echo '<div align="center" class="important"><span class="corners-top"><span></span></span>'.
 				"Showing only excerpts from $x out of $num_comments, <a href='$thread_url'>Read More</a>".
 				'<span class="corners-bottom"><span></span></span></div>';
 			}
 		?><div class="dottedline"></div></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>More on HD DVD & Blu-ray Players</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'HD DVD & Blu-ray Players'
 				AND e.entry_status = 2
 				AND e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 				AND entry_author_id = a.author_id
 			ORDER BY entry_created_on DESC LIMIT 25";
 			$result = mQuery($sql);
 			while ($row = mysql_fetch_assoc($result)) {
 				$ts = strtotime($row[entry_created_on]);
 				$y = date('Y', $ts);
 				$m = date('m', $ts);
 				$entry = getEntryInfo($row[entry_blog_id]);
 	
 				$entry[date] = getDateString($ts);
 				$entry[link] = "/$entry[blog_dir]/$y/$m/". dirify($row[entry_title]) .".php";
 				$entry[title] = $row[entry_title];
 				$entry[author] = $row[author_name];

 				echo '<li><a href="'. $entry[link] .'">'. $entry[title] .'</a> - <span class="grey">'. $entry[author] .'</span> - '. $entry[date] .'</li>';
 			}
 		?></ul><span class="corners-bottom"><span></span></span></div>
			
 		<?if (8 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 8
 				AND entry_id <> 558
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Richard Fisher'
 			ORDER BY entry_created_on DESC LIMIT 10";
 			$result = mQuery($qry);
 			
 			if (mysql_num_rows($result) > 0) {
 				$row = mysql_fetch_assoc($result);
 				echo '<div class="item"><span class="corners-top"><span></span></span>'.
 				'<h2><a href="/author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
 				mysql_data_seek($result, 0);
 				while ($row = mysql_fetch_assoc($result)) {
 					# Get categories
 					$sql = "
 					SELECT category_label FROM mt_category c, mt_placement p
 					WHERE $row[entry_id] = p.placement_entry_id
 						AND c.category_id = p.placement_category_id";
 					$res_categories = mQuery($sql);
 					$row_categories = mysql_fetch_assoc($res_categories);
 					$category = $row_categories[category_label];

 					$ts = strtotime($row[entry_created_on]);
 					$y = date('Y', $ts);
 					$m = date('m', $ts);
 					$blog_dir = getBlogDir($row[entry_blog_id]);
 					$date = getDateString($ts);
 					$link = "/$blog_dir/$y/$m/". dirify($row[entry_title]) .".php";
 					echo '<li><a href="'. $link .'">'. $row[entry_title] .'</a> - <span class="grey">'. $category .'</span> - '. $date .'</li>';
 				}
 				echo '</ul><span class="corners-bottom"><span></span></span></div>';
				}
			}

 		if ($author[bio_short] != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Richard Fisher</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Reviews</h2>
 				<?=$about?>
 			<span class="corners-bottom"><span></span></span></div>
		<?}?>
		
 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2><a href="/forum/index.php">Other Recent Discussion</h2><ul class="brownsquare"><?
 				$qry = "
 				SELECT topic_title, t.topic_id, username as post_author, post_time, post_id
 				FROM phpbb_topics t, phpbb_users u, phpbb_posts p
 				WHERE
 					t.forum_id NOT IN (". EXCLUDE_FORUMS .")
 					AND p.poster_id = u.user_id
 					AND t.topic_id = p.topic_id
 					AND t.topic_last_post_id = p.post_id
 				ORDER BY post_time DESC LIMIT 10";
 				$result = mQuery($qry);
 				
 				while ($row = mysql_fetch_assoc($result)) {
   					$last_post = date('n/j g:ia T', $row[post_time]);
   					$title = html_entity_decode($row[topic_title]);
 		  					
 					echo '<li><a href="'. FULL_URL_FORUM_VIEWTOPIC .'?t='. $row[topic_id] .'">'. $title .'</a> - <span class="grey">'. $row[post_author] .'</span> - '. $last_post .'</li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Authors</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT author_id, author_name, COUNT(*) num
 				FROM mt_author a, mt_entry e
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_NO_BULLETINS .")
 					AND entry_status = 2
 					AND entry_author_id = author_id
 				GROUP BY author_id, author_name
 				ORDER BY num DESC";
 				$res_authors = mQuery($qry);
 				while ($row_authors = mysql_fetch_assoc($res_authors)) {
 					echo '<li><a href="/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

 		<div class="item"><span class="corners-top"><span></span></span>
 			<h2>Categories</h2>
 			<ul class="brownsquare"><?
 				$qry = "
 				SELECT category_label label, COUNT(*) num
 				FROM mt_entry e, mt_placement p, mt_category c
 				WHERE e.entry_blog_id IN (". INCLUDE_BLOGS_ALL .")
 					AND entry_status = 2
 					AND entry_id = p.placement_entry_id
 					AND p.placement_category_id = c.category_id
 				GROUP BY label
 				ORDER BY label";
 				$result = mQuery($qry);
 				while ($category = mysql_fetch_assoc($result)) {
 					echo '<li><a href="/category.php?category='. urlencode($category[label]) .'">'. $category[label] .'</a><span class="grey"> ('. $category[num] .')</span></li>';
 				}
 			?></ul>
 		<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/03/lg_bh100_universal_hd_dvdblu-ray_player.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
