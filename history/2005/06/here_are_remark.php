<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 126";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 126 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (5) {
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
	<meta name="keywords" content="signal processing, solid state, recording technology, state technology, bit rate, recording, technology, processing, signal, time, possible, bit, our, optical, per, signals, new, future, computer, been, information, may, solid, state, digital" />
	<meta name="description" content="I present here remarks from one of Japan's most celebrated technologist, &quot;Mori&quot; Morizono, Sony (now retired). He spoke on June 11, 1987 (yes, I said 1987) at the Montreux Symposium, Montreux, Switzerland. Only a part of his far-reaching vision has..." />
	<title>HDTV Magazine Archive &amp; History - 1991 -Technical Trends by Mori Morizono, Sony (Retired)</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1991_-technical_trends_by_mori_morizono_sony_retired';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1991 -Technical Trends by Mori Morizono, Sony (Retired)'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1991 -Technical Trends by Mori Morizono, Sony (Retired)</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php&amp;phase=2&amp;title=1991%20-Technical%20Trends%20by%20Mori%20Morizono%2C%20Sony%20%28Retired%29&amp;bodytext=I%20present%20here%20remarks%20from%20one%20of%20Japan%27s%20most%20celebrated%20technologist%2C%20%22Mori%22%20Morizono%2C%20Sony%20%28now%20retired%29.%20He%20spoke%20on%20June%2011%2C%201987%20%28yes%2C%20I%20said%201987%29%20at%20the%20Montreux%20Symposium%2C%20Montreux%2C%20Switzerland.%20Only%20a%20part%20of%20his%20far-reaching%20vision%20has...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>I present here remarks from one of Japan's most celebrated technologist, "Mori" Morizono, Sony (now retired). He spoke on June 11, <strong><em>1987 </em></strong> (yes, I said 19<u>87</u>) at the Montreux Symposium, Montreux, Switzerland. Only a part of his far-reaching vision has so far been realized commercially. There is much more to go. "It is my feeling, he said, "that any limitations we perceive are self-imposed." I decided to place this document on our web site as a reminder of how far ahead our big technology firms can be. You will read in this paper things which are just now becoming mainstream, like perpendicular recording. This is in some ways a proven road map to the future. </p>

<p>I had the good fortune of meeting with Mr. Morizono in Sony's Headquarters in Atsugi, Japan. From the moment I stepped into their imposing granite structure I knew I had entered a different world, one many generations ahead of that which was roaring in the streets. Everything operated like a Swiss clock movement and NOTHING was out of place nor out of time. It was a most memorable experience. Excuse me for that personal departure and let me refocus you now to what I felt was a significant presentation for my development. I am well aware that progress has since come in leaps and bounds and as we identify important addresses I will post them here for permanent reference. I will add to this section that my visit to Sony and Morizono, in particular, was to get a sense of how prepared the manufacturers were then to the producing of commercial grade HDTV monitors and receivers. The assurances were complete and I came back to the United States fully confident that we could make the transition because the way to hardware building was already known. Few consumers realize that the HDTV initiative did NOT come from the  manufacturers. It came independantly from NHK, the national broadcaster of Japan. The manufacturers were very leary of HDTV and only participated in it because of a fear that if they didn't another fierce competitor might and thus steal their thunder (marketshare) should it ever come about. While they were all very nervous over the business prospects for introducing HDTV to the masses some were superbly prepared from the perspective of technology. </em></p>

<p></p>

<p>15TH INTERNATIONAL TELEVISION SYMPOSIUM<br />
MONTREUX SWITZERLAND</p>

<p>JUNE 11, 1987</p>

<p>KEYNOTE ADDRESS OF </p>

<p>MR. MASAHIKO MORIZONO</p>

<p><br />
<strong>"TECHNOLOGICAL TRENDS"</strong></p>

<p><br />
_____________________________________________________________________</p>

<p>"It is my feeling that any limitations we perceive are self-imposed."<br />
_____________________________________________________________________</p>

<p><br />
Mr. Morizono...</p>

<p>I remember attending the Montreux International Symposium for the first time in 1975, and again in 1977 when we participated in the Technical Exhibition with our ENG products. In the 10 years since that time, technoigy has made rapid progress. In particular, Broadcasting equipment technology has enjoyed great advances, especially in the areas of CATV and Satellite Broadcasting, Overall, the technological environment has been steadily changing from analog to digital.</p>

<p>The Symposium Executive Committee has requested me to talk about "Technological Trends". The scope of technnology to be covered at this Symposium is very broad. It would be impossible for me to even begin covering all its aspects at this symposium, so I have decided to limit myself to a few specific examples which I feel illustrate the general "technological trends". Please bear in mind that I will be stating my personal views as a Broadcasting Equipment manufacturer and as the person responsible for all Research and Development at Sony Corporation.</p>

<p>The technological fields which will be covered at this Symposium can be roughly classified as follows-</p>

<p>- telecommunication</p>

<p>- transmission and receiving technology (including satellite broadcasting)</p>

<p>- signal processing</p>

<p>- opto-electronics</p>

<p>- recording technology (including media and devices for recording and playback)</p>

<p>-computer technology (including hardware and software)</p>

<p>-display and sensor technology</p>

<p>-solid-state technology.</p>

<p>I would like to express my opinion on some of those technologies, especially signal processing, recording technology, recording media, and solid-state technology, whose development I believe are the key factor for the future progress of hardware to be found in forthcoming broadcasting equipment.</p>

<p><strong>Solid-State Technology</strong></p>

<p>Let me begin with an overview of the trend Solid-State Technology has been displaying. Solid-State Technology is at the heart of the electronics industry. It is generally accepted that the degree to which we can integrate circuits will determine success or failure in this industry.</p>

<p>Large Scale Integration plays a key role in one of the important trends of solid-state technology, which is the rapid change from analog to digital processing. This change means that a great deal of information must be transferred from analog to digital. In order to accomplish this, faster ways to process information will need to be developed, Along these same lines, signal processing will require advances in large-scale integration technology so that more efficient input/output interfacing from analog to digital can be accomplished,</p>

<p>At present, image/video signals are quantized at 8 bits per samples, at sampling frequencies from 100 MHz to 300 MHz. Audio signals are normally quantized to 16 bits per sample at 48 kHz. In the future, however, 10 to 12 bits per sample quantization at a sampling frequency of 500 MHz to 1GHZ for image/video signals, and a quantization of 20 or more bits per sample for audio signals at frequencies over 100 kHz, will become necessary for higher quality signal processing. As the number of quantization levels increases, circuit integration and operating speed must increase as well if CPU'S, multiplier-adders, memory devices and ASIC's (Application Specific Integrated Circuits) are to be realized-</p>

<p>Increased circuit integration cannot be accomplished without further advances in sub-micron technology. At the moment, our highest level of technology is defined by the µpm design rule. However, a 0.1-0.2 pm design rule is anticipated when SOR (Synchrotron Orbital Radiation) and Excimer Laser Lithography technology become advanced enough for practical use. The possible applications for these Sub-micron technologies are astonishing. For example, a D-RAM of 64 Mbit/chip to 128 Mbit/chip is entirely possible. With the advent of such devices, a solid-state audio recorder is sure to become a reality. If we extend this technology even further into the 3-dimensional realm, the LSI structure may allow us to increase the capacity of the D-RAM tremendously. Naturaily, higher operating speeds and lower power consumption can also be expected.</p>

<p>In order for ultra-high-speed information processing to be accomplished, both the GaAs and the superconductive material technologies are quite important. In the future, the combination of these two technologies may allow us to achieve operating speeds which are 10 to 20 times faster than those presently possible with silicon devices.</p>

<p>Application-specific IC devices have great potential for use in signal processing, which I will touch upon later.</p>

<p>I would also like to mention here the increasing importance of new IC memory devices. Recently there have been some exciting developments which indicate that Photo-chemical Hole-Buuring Memory , Bloch Line Memory and Molecular Memory devices may be possible. Although Photo-chemical Hole-Burning, for example, is still in the research stage, it is anticipated that it will be possible to write and read approximately 103 to 104 bits of information per laser beam spot, utilizing the wavelength selectivity of a material such as Porphiline. Assuming that the laser beam spot size is less than 1 µm2 it will be possible to record and retrieve approximately 100 Gigabits to 1 Terabit of information using only 1 cm2 or 100 million µm2 of active memory area.</p>

<p>The recording data rate of the present 4:2:2 digital VTR is 216 Mbit/sec, and its total number of bits per hour is therefore 216 Mbits/sec x 60 x 60 = 777.6 Gigabits. Thus, according to this rough calculation, it will be possible to record and play back video information equivalent to more than one hour of 4:2:2 digital VTR playing time using only 1 cm2 of a photo-chemical Hole Burning memory chip active area.</p>

<p><strong>Signal Processing</strong></p>

<p>Now I would like to speak about the future of signal processing technology. It is my belief that the effective application of bit rate reduction is one of the most important aspects of signal processing at this time. As you know, bit reduction technology is applied to two areas of signal processing. the transmission of signals, and their recording and playback.</p>

<p>Until now, we have been quite successful in our efforts to process signals at the baseband frequency, and bit rate processing was not needed in past applications. However, it is not economically feasible to transmit high-quality signals in full bandwidth because of restrictions in the frequency band allocation. We will have to develop new bit rate reduction algorithms which can give us higher-quality audio and video signal transmission.</p>

<p>In the recording field, bit rate reduction is extremely important also, since recording density is limited by the physical interface between a device and its recording medium. It is desirable, from an economic point of view, to record a high-quality signal in as small an area as possible with no degradation, impairment or aliasing.</p>

<p>Bit rate reduction has applications in the computer graphics field as well, although the constraints are not the same as in television signal processing, since television signals are generally more correlative than computer graphics signals. DPCM, Cosine Transtorm, K-L Transform, Vector Quantization, and ADRC (Adaptive Dynamic Range Coding) are now available. However, these algorithms are not sufficient to eliminate degradation in computer Graphics. The development of an algorithm which allows us to take full advantage of computer graphics should be a top priority.</p>

<p>Another important aspect of signal processing is picture processing, particularly picture manipulation. Let's take an example. At the present time, it is possible, using expensive and cumbersome hardware, to use signal processing to manipulate or alter an object in a picture which appears during one T.V. field scanning period. We can eliminate that object, change its color or deform its shape. Eventually, as our high speed solid-state device and parallel processing architecure technologies are perfected, this kind of picture manipulation can be accomplished by CPU and DSP (Digital Signal Processing) chips at speeds of 15 GIPS (15 Giga-Instructions Per Second) or faster. Such chips will also allow real-time picture processing to become common-place, and high-quality, special T.V. signal effects, such as Chroma-key, to be implemented with greatly improved picture quality.</p>

<p><strong>Recording</strong></p>

<p>Now, let's consider recording technology. Until recently, magnetic recording has been the only practical recording technology for long-time picture recording and playback in the broadcasting field, and it will continue to be used as the main recording technology for quite some time. As a result, a lot of research has naturally been focused on magnetic recording, resulting in advances such as the development of MP (metal-particle) and ME (metal-evaporated) tapes and a greatly reduced recording area per bit. ME tape technology, for example, has made it possible to record at only 1 bit per 3.5 µm2.</p>

<p>Looking to the future, perpendicular recording promises even higher recording densities. It will definitely be possible, for example, using Co-Cr tape as the recording medium, to achieve a recording density of 1 bit per 1 µm2. Together with bit reduction, and in the case of an NTSC signal, one will then achieve approximately 6 hours of recording and playback at a bit rate of 30 Mbit/sec with good picture quality using 8mm video tape.</p>

<p>Today, magnetic recording is no longer the only available recording technology. The optical disc with Laser diode recording and playback has opened a whole new area of possibilities. As you know, the CD (Compact Disc) has had a great effect on the music recording industry, and is currently being used as a data storage medium in applications such as CD-ROM (Read-Only Memory).</p>

<p>Other types of optical discs are also being used for mass storage. These include the WO (Write-Once) disc, the Dye Polymer disc and the Magneto-optical disc which utilizes the Kerr Effect. The Dye Polymer and Magneto-optical discs make it possible for information to be recorded, read out and erased. The magneto-optical disc has a recording capacity of approximately 700 Mbytes when using both sides of a 5 1/4 " (13cm) disk. If the disk's diameter is extended to 30cm) recording capacity increases to 4 Gigabytes when both sides are usd in the CLV (Constant Linear Velocity) mode. In practical terms, this means that 216 Mbits/sec of 4:2:2 digital video signal can be recorded for approximately 5 minutes,</p>

<p>As impressive as this storage capacity is, it is nevertheless limited at the present time by the 780-830 nm wavelength of the laser diode used, and by the fact that recording is done only with two levels. In the future, it may be possible to shorten the wavelength of the laser diode and to achieve multi-level recording, thereby increasing recording capacity at least 4 times. If this is achieved, recording time for 4:2:2 signals will be more than 20 minutes for a 30cm diameter disk and long-time record/playback, in multi-platter operation, will be possible.</p>

<p>The most important advantage of optical disk recording over magnetic tape recording is its high-speed access capability and very short seek time. This makes it the ideal recording medium for of f-line editing and f inal program assembly. Therefore, while the optical disk will not replace the VTR, it is certain to play an important role in recording, particularly in post-production applications.</p>

<p>Recording technology researchers are currently exploring new recording systems. A new and very promising one is the optical video tape recorder which would use an optical medium and eventually provide a recording density of 100 Mbits/cm2 to 400 Mbits/cm2. The realization of a system such as this will mean that we have entered a new technological era in which a choice must be made between magnetic and optical tape recording and optical disk recording.</p>

<p>Although I have been involved in standardization activities for a long time now, in the so-called "Format Battles", I will have retired by the time the next era begins. I will observe your struggles to apply the new technology with great interest from the sidelines. I will be like an enthusiastic football fan cheering on his team,</p>

<p><strong>Reaching Out</strong></p>

<p>In closing, I would like to take a look at the future of technology from my own personal perspective. To the average scientist, working in the fields of integrated circuitry or computer technology, there may seem to be a limit to what we can achieve, even though, for example, new computer architectures (the so-called non-von-Neuman machines), have been proposed as one possible way to increase our options in those areas. It is my feeling that any limitations we perceive are self-imposed.</p>

<p>It is time to reach beyond what we can immediately perceive as feasible with our present-day technology, and to extend our questioning and experimentation to include the exploration of molecular mechanisms. In particular, the transfer of information via proteins in living organisms is a scientific phenomenen which should excite our interest, Though it may be difficult for us to conceive how this mechanism could be used to our advantage scientifically, we should not dismiss it lightly. While exploring the functions of living matter, we can find clues which may lead to new discoveries. For example, protein information transfer could have a great influence on computer electronics and therefore computer technology. This in turn would influence the broadcasting industry. This is why I believe that bio-electronics may be the key to future technological breakthroughs in the 21st century. I urge you to keep your minds open, and continue to reach out and explore new possibilities. There is no limit to what we can achieve. Thank you for your kind attention.</p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 08:06 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 126
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
 			<h2>More on </h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = ''
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
			
 		<?if (5 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 5
 				AND entry_id <> 126
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Dale Cripps'
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
 				<h2>About Dale Cripps</h2>
 				<?=stripslashes($author[bio_short])?>
 			<span class="corners-bottom"><span></span></span></div>
 		<?}?>
		</td><td id="right">

 		<?if ($about != '') {?>
 			<div class="item"><span class="corners-top"><span></span></span>
 				<h2>About Archive &amp; History</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1991_-technical_trends_by_mori_morizono_sony_retired.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
