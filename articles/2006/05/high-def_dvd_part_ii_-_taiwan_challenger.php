<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 385";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 385 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (1) {
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
	<meta name="keywords" content="def dvd, red laser, blue laser, dvd forum, fvd format, FVD, fvd, DVD, dvd, Taiwan, taiwan, laser, format, players, player, def, Laser, movies, disc, part, red, content, video, Def, discs" />
	<meta name="description" content="Taiwan's Forward Versatile Disc (FVD)

Over the past couple of years, I have written on these reports (as well as in the pages of DVDetc and HDTVetc Magazines) about the four Hi-def DVD formats in the China/Taiwan market, three from China (EVD, HVD, and HDV), and one from Taiwan (FVD).

On this opportunity, I met at CES 2006 with Mr. Job Liu, Managing Director of POSO (Power Source Group Limited), who was representing the FVD format at CES. Mr. Liu introduced also Margaret Fan, General Manager of Idar Electronics Co., a company involved with the FVD players and format.

They showed the player, the movies, ..." />
	<title>HDTV Magazine Articles - High-Def DVD Part II - Taiwan Challenger</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/high-def_dvd_part_ii_-_taiwan_challenger';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('High-Def DVD Part II - Taiwan Challenger'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">High-Def DVD Part II - Taiwan Challenger</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>May 30, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php&amp;phase=2&amp;title=High-Def%20DVD%20Part%20II%20-%20Taiwan%20Challenger&amp;bodytext=Taiwan%27s%20Forward%20Versatile%20Disc%20%28FVD%29%0A%0AOver%20the%20past%20couple%20of%20years%2C%20I%20have%20written%20on%20these%20reports%20%28as%20well%20as%20in%20the%20pages%20of%20DVDetc%20and%20HDTVetc%20Magazines%29%20about%20the%20four%20Hi-def%20DVD%20formats%20in%20the%20China%2FTaiwan%20market%2C%20three%20from%20China%20%28EVD%2C%20HVD%2C%20and%20HDV%29%2C%20and%20one%20from%20Taiwan%20%28FVD%29.%0A%0AOn%20this%20opportunity%2C%20I%20met%20at%20CES%202006%20with%20Mr.%20Job%20Liu%2C%20Managing%20Director%20of%20POSO%20%28Power%20Source%20Group%20Limited%29%2C%20who%20was%20representing%20the%20FVD%20format%20at%20CES.%20Mr.%20Liu%20introduced%20also%20Margaret%20Fan%2C%20General%20Manager%20of%20Idar%20Electronics%20Co.%2C%20a%20company%20involved%20with%20the%20FVD%20players%20and%20format.%0A%0AThey%20showed%20the%20player%2C%20the%20movies%2C%20...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<blockquote>This article is the second in a series.<br>
<br>
Other articles in this series:<br>
Part 1: <a href="/articles/2006/05/hi-def_dvd_-_blue_laser_well_what_else_is_out_there.php">Hi-Def DVD? - Blue laser? Well, what else is out there?</a></blockquote>
<br>
<br>
This article is a small excerpt on the full coverage of Hi-Def DVD in a section included within my "State of HDTV Technology, 2006 Review". The article continues with the subject of Part I "Well, what else is out there?" covering another HD red laser format, this one coming from Taiwan.

<p><br />
<center><b>Taiwan's Forward Versatile Disc (FVD)</b></center></p>

<p>Over the past couple of years, I have written on these reports (as well as in the pages of DVDetc and HDTVetc Magazines) about the four Hi-def DVD formats in the China/Taiwan market, three from China (EVD, HVD, and HDV), and one from Taiwan (FVD).<img src="/images/articles/fvd-player.jpg" alt="FVD player" align="right"></p>

<p>On this opportunity, I met at CES 2006 with Mr. Job Liu, Managing Director of POSO (Power Source Group Limited), who was representing the FVD format at CES. Mr. Liu introduced also Margaret Fan, General Manager of Idar Electronics Co., a company involved with the FVD players and format.</p>

<p>They showed the player, the movies, the FVD format efforts, and we discussed about specifications and technical capabilities of the format, discs, and players. At the end of our long meeting, I was offered if I wanted to take the player with me after the show. It took me by surprise, I declined politely, but I certainly accepted an FVD disc demo as my after show teaser.</p>

<p>FVD discs and players are already available for sale. The player MSRP is $250, and FVD movies were quoted as about $6 per disc, although I have not seen an official price list as I did with Chinese EVD companies the year before.</p>

<p>FVD is a red laser solution that supports FVD-video and WMV-9 HD video codecs, and WMA, LPCM and ITRI-Audio codecs. The disc can store 135 minutes of HD full-length movies in 720p/24/30 (SL), or in 1080i60/p24 (DL, or 3 hrs TL), in addition to 720x480 and 320x240 regular video resolution at 60i.</p>

<p>The FVD player is suited with DVI/HDMI and component analog connections, optical and coax for 5.1 or 2-channel audio, peak bit rate 15Mbps. According to the company at CES the player is able to output 1080i over component analog because the format uses its own content protection system (ITRI-AES, Innovative Technologies Research Institute - Advanced Encryption Standard).</p>

<p>There is a PC playback software version (Super FVD, in beta now) that allows the use of existing DVD-ROM drives for HD FVD playback of movies without any additional hardware. There is no need for Microsoft's Media Center in the PC because as mentioned above the format uses its own content protection system, no DRM.</p>

<p><img width=254 height=141 src="/images/articles/fvd-discs.jpg" alt="FVD Discs" align="left">There are 29 companies and ITRI in the AOSRA (Taiwan Advanced Optical Storage Research Alliance), an organization founded in January 2002.</p>

<p>There are several FVD disc producers like RiTek Corporation, Prodisc Technology Inc., U-Tech Media Corporation, Leaddata, Infodisc, Giga Storage, Optodisc, Nan-Ya, and CMC Magnetics Co. (their discs are pictured above on the left). Players are being manufactured by TATUNG, BenQ, LITE-ON, Actima, Mustek, PROTOP, Arima, MSI, QSINC, Ultima, and A-DATA. Chip-set manufacturers include VOS, ALI, MTK, SUNPLUS, and CHEERTEK. Video software content includes Newsoft, Deltamac, and Cine-Asia Entertainment.</p>

<p><img src="/images/articles/sniper.jpg" alt="Sniper" align="right"></p>

<p><img src="/images/articles/fvd-press.jpg" alt="FVD Press" align="left">In addition to the existing titles, and WM9, fourteen FVD films were planned to be released soon: Air Panic, Avalanche, City of Fear, Death Train, Edges of the Lord, The Order, Us Seals, The Confession, Earthquake, Fire, Volcano, A Wobot Christmas, The Opponent, Diary of a City Priest, Buried Lies, Combustion, Malie.</p>

<p><span stype="float:right"><table><tr><td align="center"><br />
Idar Electronics Co. below<br><img src="/images/articles/idar-electronics-co.jpg" alt="Idar Electronics Co." align="left"><br />
</td></tr></table></span><br />
FVD was introduced on April 5, 2004 in Taipei Taiwan. The first FVD players were made available in May 2005 for $175 with 10 free movies in Taiwan.<br />
<br clear=all><br />
With a sales promotion in Europe and the US, the global volume was estimated to reach 100,000 players in 2005, 3 million in 2006, and 5 million in 2007. Later, Taiwan's Kolin offered in November 2005 an initial sales promotion period for their first KVD-1080 player with an HDMI cable included, and three 1080i FVD movie discs, all for $240.</p>

<p>Content is available mainly from independent studios but the alliance is doing efforts to expand to 100 the initial offering of titles by including other major studios.</p>

<table border=1 cellpadding=0 width="94%"
 style='border:outset #CCCCCC 1.0pt'>
 <tr>
  <td width="99%" colspan=5 style='width:99.28%;border:inset #CCCCCC 1.0pt;
  background:#000066;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>Comparison of
  Formats</span></strong>
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <p class=MsoNormal style='line-height:22.0pt'><span style='font-family:s\04E9&#2;;
  color:black'>&nbsp;
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>DVD</span></strong>
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>FVD</span></strong>
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>HD DVD</span></strong>
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span
  style='color:white'>BD</span></strong>
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Leading Organization</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>DVD Forum
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='color:black'>Taiwan</span><span
  style='color:black'>'s  </span><span
  style='font-family:Arial;color:black'>AOSRA
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>DVD Forum
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span
  style='color:black'>Blu-ray Association
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Physical Capacity (single side)</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 4.7GB (DL) 8.5GB
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 5.4/6GB (DL) 9.8/11GB<br>
  (TL) 15GB
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 15GB
  <span style='
  color:black'>(DL) 30GB
  <span style='
  color:black'>(TL) 45GB
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>(SL) 25GB
  <span style='
  color:black'>(DL) 50GB
  <span style='
  color:black'>(4L) 100GB(TDK)
  </td>
 </tr>
 <tr>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <strong><span style='
  color:white'>Laser</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Red Laser (650nm)
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Red Laser (650nm)
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Blue Laser (405nm)
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt'>
  <span style='
  color:black'>Blue Laser (405nm)
  </td>
 </tr>
 <tr style='height:37.35pt'>
  <td width="19%" style='width:19.32%;border:inset #CCCCCC 1.0pt;background:
  #2D7BC6;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <strong><span style='
  color:white'>Resolution</span></strong>
  </td>
  <td width="16%" style='width:16.34%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>720x480i60
  </td>
  <td width="21%" style='width:21.64%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p24<br>
  1920x1080i60
  <span style='
  color:black'>1920x1080p24
  </td>
  <td width="14%" style='width:14.84%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p<br>
  1920x1080i/p
  </td>
  <td width="25%" style='width:25.72%;border:inset #CCCCCC 1.0pt;background:
  #C5E0FB;padding:3.75pt 3.75pt 3.75pt 3.75pt;height:37.35pt'>
  <span style='
  color:black'>1280x720p<br>
  1920x1080i60
  <span style='color:black'>1920x1080p24
  </td>
 </tr>
</table>

<p><i><span style='text-align:center"'>Chart sourced from FDV with my additions/corrections</span></i></p>

<p>Stay tuned to my next article on this series, Hi-Def DVD Part III, the Blue battle.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>May 30, 2006 10:31 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 385
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
 			<h2>More on Technology</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Technology'
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
			
 		<?if (1 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 1
 				AND entry_id <> 385
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Rodolfo La Maestra'
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
 				<h2>About Rodolfo La Maestra</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Articles</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/high-def_dvd_part_ii_-_taiwan_challenger.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
