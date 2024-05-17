<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1497";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1497 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
*/
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# This template is used only for podcasts
#	$rss_link = '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/hdtv-bulletins" />';
	$container = 'article_container';
#	$category_page = 'bulletins-category.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<?require(BASE_DIR .'/includes/common_header.php');?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="generator" content="http://www.movabletype.org/" />
	<meta name="keywords" content="blu ray, neuros osd, usb storage, circuit city, friday predictions, OSD, osd, blu, ray, Blu, video, device, videos, USB, old, network, predictions, usb, less, any, computer, record, mpeg, MPEG, storage" />
	<meta name="description" content="Dennis from Medina OH sent us a link to an article that ran down a list of Black Friday Predictions  at BlackFriday @ GottaDeal.com. We want to do the same thing. The only rules we had were not to read the article prior to making our predictions. The article has predictions about computers, computer peripherals, GPS, and all things electronic. Our predictions will stay in the realm of HDTV and Home Theater.

We also recently received an email asking us for a good solution to archive old family videos that are locked away on VHS tapes. Many options require you to use the hard drive of your computer for the recording process. Today we take a look at a devices that easily connects to your analog sources and records to multiple destinations. The Neuros OSD lets you convert your treasured videos to digital so they can be enjoyed for years to come." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD</title>
	<?=$rss_link?>
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/tracker.php');
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #314 - Black Friday predictions and Neuros OSD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>September 25, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
				<span><img src="/images/email.gif" alt="Email Article" align="absmiddle" /><a href="<?=$email_url?>">Email</a></span>
				<br /><br />
			</td>
		</tr>
	</table>
	<?if ($userdata[subscriptions] & SUB_PODCAST) {} else {
		if ($userdata[session_logged_in]) {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_SUBSCRIPTIONS?>">Modify your subscription profile</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?} else {?>
			<div class="important"><span class="corners-top"><span></span></span>
				<img src="/images/i_inbox.gif" align="left" style="padding-right:10px" /><span class="label">Receive instant notification of new episodes:</span>
				<a href="<?=URL_PROFILE_CREATE?>">Register Now</a> to receive notification of new
				episodes of The HDTV Podcast via email as soon as they are published.
			<span class="corners-bottom"><span></span></span></div>
		<?}
	}?>
	<div>
		<div style="float:left; margin:0 5px 5px 0;"><?
			if ($digg_url == '') {
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23314%20-%20Black%20Friday%20predictions%20and%20Neuros%20OSD&amp;bodytext=Dennis%20from%20Medina%20OH%20sent%20us%20a%20link%20to%20an%20article%20that%20ran%20down%20a%20list%20of%20Black%20Friday%20Predictions%20%20at%20BlackFriday%20%40%20GottaDeal.com.%20We%20want%20to%20do%20the%20same%20thing.%20The%20only%20rules%20we%20had%20were%20not%20to%20read%20the%20article%20prior%20to%20making%20our%20predictions.%20The%20article%20has%20predictions%20about%20computers%2C%20computer%20peripherals%2C%20GPS%2C%20and%20all%20things%20electronic.%20Our%20predictions%20will%20stay%20in%20the%20realm%20of%20HDTV%20and%20Home%20Theater.%0A%0AWe%20also%20recently%20received%20an%20email%20asking%20us%20for%20a%20good%20solution%20to%20archive%20old%20family%20videos%20that%20are%20locked%20away%20on%20VHS%20tapes.%20Many%20options%20require%20you%20to%20use%20the%20hard%20drive%20of%20your%20computer%20for%20the%20recording%20process.%20Today%20we%20take%20a%20look%20at%20a%20devices%20that%20easily%20connects%20to%20your%20analog%20sources%20and%20records%20to%20multiple%20destinations.%20The%20Neuros%20OSD%20lets%20you%20convert%20your%20treasured%20videos%20to%20digital%20so%20they%20can%20be%20enjoyed%20for%20years%20to%20come.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
			} else {
				echo '<script src="http://digg.com/api/diggthis.js"></script>';
			}
		?></div>
		<div style="float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/mrectangle.php');?>
		</div>
		<div style="clear:right; float:right; margin:0 0 5px 5px;">
			<?include(BASE_DIR .'/ads/skyscraper.php');?>
		</div>
		<div id="<?=$container?>">
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="/images/chicklet-itunes.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-09-26.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<strong>Today's Show:</strong><br>
Dennis from Medina OH sent us a link to an article that ran down a list of&nbsp;<a id="l.av" href="http://blackfriday.gottadeal.com/BlackFridayPredictions" target="_blank" title="Black Friday Predictions">Black Friday Predictions</a> &nbsp;at&nbsp;<a id="uj45" href="http://blackfriday.gottadeal.com/" target="_blank" title="BlackFriday @ GottaDeal.com">BlackFriday @ GottaDeal.com</a>.
We want to do the same thing. The only rules we had were not
to read the article prior to making our predictions. The article has
predictions about computers, computer peripherals, GPS, and all things
electronic. Our predictions will stay in the realm of HDTV and Home
Theater.
<div><br>
We
also recently received an email asking us for a good solution to archive old
family videos that are locked away on VHS tapes. Many options require
you to use the hard drive of your computer for the recording process.
Today we take a look at a devices that easily connects to your analog
sources and records to multiple destinations. The Neuros OSD lets you
convert your treasured videos to digital so they can be enjoyed for
years to come.<br>
<br></div>
<div><strong>Black Friday Predictions</strong></div>
<div><strong>Ara:</strong></div>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;"><em>HDTVs <br>
</em></blockquote>

<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<ul>
<li><span style="font-style: normal;">Target and Wal*Mart will have a tier one (SONY, Panasonic, Sharp) 32 inch LCD for less than $500.</span></li>
<li><span style="font-style: normal;">BestBuy and Circuit City will have a 42 inch Plasma for $750</span></li>
<li><span style="font-style: normal;">BestBuy and Circuit City will give away a Blu-ray Player with the purchase of a 60 inch Plasma</span></li>
<li><span style="font-style: normal;">No
one will have a door buster for a rear projection TV. Costco will have
the best bang for the buck deal on one. And that deal is available
today! A&nbsp;<a id="iini" href="http://www.costco.com/Browse/Product.aspx?Prodid=11290083&amp;whse=BC&amp;Ne=4000000&amp;eCat=BC%7C79&amp;N=4001386&amp;Mo=3&amp;No=3&amp;Nr=P_CatalogName:BC&amp;cat=4848&amp;Ns=P_Price%7C1%7C%7CP_SignDesc1&amp;lang=en-US&amp;Sp=C&amp;topnav=" target="_blank" title="65 inch Mitsubishi Medallion Series DLP">65 inch Mitsubishi Medallion Series DLP</a> &nbsp;for $1900.</span></li></ul>
<div><em>Blu-ray&nbsp;</em></div></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>

<ul>
<li><span style="font-style: normal;">Wal*Mart will have a no name Blu-ray player for $150</span></li>
<li><span style="font-style: normal;">You will be able to find a name brand Blu-ray player that supports the full Blu-ray specification for $200.&nbsp;</span></li>
<li><span style="font-style: normal;">Target will have a buy one get one free deal on Blu-ray Movies.</span></li></ul>
<div><em>Receivers</em></div></div></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>
<div>
<ul>
<li><span style="font-style: normal;">The Onkyo 606 will be found for less that $300 at Circuit City</span></li></ul>
<div><span style="font-style: normal;">Remote Control</span></div></div></div></blockquote>

<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<div>
<div>
<div>
<ul>
<li><span style="font-style: normal;">The Harmony One will go for $150 (Online Only)</span></li>
<li><span style="font-style: normal;">The Harmony 880 will go for $50 after Mail in rebate (Online Only)</span></li></ul></div></div></div></blockquote>
<div><strong>Braden:</strong></div>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;"><em>HDTVs <br>
</em></blockquote>
<blockquote style="border: medium none ; margin: 0pt 0pt 0pt 40px; padding: 0px;">
<ul>
<li><span style="font-style: normal;">42" plasma for less than $600<br>

</span></li>
<li><span style="font-style: normal;">40" LCD for less than $600</span><span style="font-style: normal;"></span></li>
<li><span style="font-style: normal;">60" or greater DLP for less than $1300 to beat Tiger's current deal of a <a title="73&quot; DLP for $1999" target="_blank" href="http://www.tigerdirect.com/applications/SearchTools/item-details.asp?EdpNo=3871732&amp;Sku=M402-7308" id="vmh8">73" Mitsubishi DLP for $1999</a> </span></li></ul>
<div><em>Blu-ray&nbsp;</em></div>
<ul>
<li><span style="font-style: normal;">Blu-ray players for $130</span></li>
<li><span style="font-style: normal;">Blu-ray movies for less than $10<br>
</span></li></ul><em>Other</em><br>
<ul>

<li>1 TB External Hard Drive for $100</li>
<li>Brand name HTiB for under $100<br>
</li></ul></blockquote>
<p>&nbsp;</p>
<p><strong><a id="a8qf" href="http://www.neurostechnology.com/" target="_blank" title="Neuros OSD">Neuros OSD</a><span>&nbsp;($175&nbsp;</span><a id="ucvk" href="http://www.htguys.com/shop.php?id=B000HXGIHE" target="_blank" title="Buy Now">Buy Now</a><span>) </span></strong><span><br>
The OSD is a small device that measures&nbsp;14
x 14 x 3.2 cm (5.5 x 5.5 x 1.25 inches) and weighs&nbsp;230g (8oz). Its made
out of plastic and feels sturdy in the hands. The OSD comes with the
necessary cables to connect the analog source and playback equipment.
There is also an included remote which is required to navigate the
OSD's onscreen menus. &nbsp;The main features of&nbsp;the&nbsp;OSD include:</span></p>

<ul style="margin-top: 0px; margin-bottom: 0px;">
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Video Capture</strong>&nbsp;-
Record any analog video in MPEG-4 format to any USB storage device. You
can even record directly to a computer of NAS device over your network.</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Audio Recorder</strong>&nbsp;-
Record analog audio in MP3 or AAC format to any USB storage device.&nbsp;You
can even record directly to a computer of NAS device over your network.</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Video Player</strong>&nbsp;- Play video from any USB storage device and Youtube</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Music Player</strong>&nbsp;- Play music from network or USB Storage</li>
<li style="margin-top: 0px; margin-bottom: 0px;"><strong>Photo/Image Viewer</strong>&nbsp;- View Photos from USB and Network</li></ul>

<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Setup</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">Setup
took five minutes. Connect the inputs, outputs and power. For our tests
we used an old VCR that was laying around the house. It was kept so one
day Ara could convert his old family videos to digital. So for this
review even Ara's wife was excited about the potential.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">Once
the connections were made you set the date and time. Since we also
wanted to record to a network drive we tried to connected to a shared
drive on Ara's video server however, the OSD could not find shared
drives on any of&nbsp;the&nbsp;computers on the network. It may work better with
a Windows box or NAS device but we had no way to know for sure.</div><br>
<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Performance</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">We
converted a bunch of old videos that were shot on VHS and VHS-C. The
results we quite good. Well, as good as 10 year old VHS tapes can be.
So many precious memories starting to degrade away! The digital version
of the video looked every bit as good as the tape version. &nbsp;A thirty
minute tape was reduced to a 565 MPEG-4 video clip. The actual details
are 2.6Mbps, 640x480, 30 FPS, AAC Stereo.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
conversion obviously happens in real time so it will take
a&nbsp;commitment&nbsp;to convert your family's video memories. At least it is a
plug and play proposition.&nbsp;</div>

<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
Neuros OSD also plays back files from your computer or your attached
USB storage. It can play back a wide range of formats including:&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<ul style="margin-top: 0px; margin-bottom: 0px;">
<li style="margin-top: 0px; margin-bottom: 0px;">MPEG-4 SP&nbsp;with&nbsp;MP3&nbsp;audio,&nbsp;30fps&nbsp;up&nbsp;to&nbsp;D1&nbsp;resolution&nbsp;(720x480)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">Quicktime&nbsp;6</li>

<li style="margin-top: 0px; margin-bottom: 0px;">MPEG-4&nbsp;AAC-LC&nbsp;stereo</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MP4&nbsp;format&nbsp;at&nbsp;up&nbsp;to&nbsp;D1&nbsp;resolution</li>
<li style="margin-top: 0px; margin-bottom: 0px;">H.263&nbsp;with&nbsp;MP3&nbsp;audio</li>

<li style="margin-top: 0px; margin-bottom: 0px;">FLV&nbsp;(for&nbsp;Playback&nbsp;of&nbsp;YouTube&nbsp;videos)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">ASF</li>
<li style="margin-top: 0px; margin-bottom: 0px;">AVI&nbsp;(including&nbsp;Divx&nbsp;and&nbsp;Xvid)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MP4</li>

<li style="margin-top: 0px; margin-bottom: 0px;">WMV&nbsp;(up&nbsp;to&nbsp;QVGA)</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MOV</li>
<li style="margin-top: 0px; margin-bottom: 0px;">MPG&nbsp;MPEG</li></ul>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">It had no issues playing back any videos we threw at it.&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>
<div style="margin-top: 0px; margin-bottom: 0px;">Other
than the device not recognizing the shared drives on the Mac the only
other complaint we had was the GUI. While we didn't expect Apple like
flare, it did leave us wanting a better experience.</div>
<div style="margin-top: 0px; margin-bottom: 0px;">&nbsp;</div>

<div style="margin-top: 0px; margin-bottom: 0px;"><strong>Conclusion</strong></div>
<div style="margin-top: 0px; margin-bottom: 0px;">The
Neuros OSD is a simple and&nbsp;convenient&nbsp;way to convert your old VHS tapes
to&nbsp;digital. It was so much fun to see videos of my children growing up
and of my late father. The OSD is the kick in the pants we all need to
go out and digitize our family memories! It's HT Guys tested and wife
approved!</div>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>September 25, 2008 11:47 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1497
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

			<?if (9 <> 7) {
				# Recent Articles by Author (exclude this one)
				# Do not show recent articles for Bulletins.
				$qry = "
				SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
				FROM mt_entry e, mt_author a
				WHERE entry_blog_id = 9
					AND entry_id <> 1497
					AND entry_status = 2
					AND entry_author_id = a.author_id
					AND a.author_name = 'The HT Guys'
				ORDER BY entry_created_on DESC LIMIT 10";
				$result = mQuery($qry);
				
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					echo '<div class="item"><span class="corners-top"><span></span></span>'.
					'<h2><a href="../../author.php?author='. urlencode($row[author_name]) .'&id='. $row[author_id] .'">More from '. $row[author_name] .'</a></h2><ul>';
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
					<h2>About The HT Guys</h2>
					<?=stripslashes($author[bio_short])?>
				<span class="corners-bottom"><span></span></span></div>
			<?}?>
		</td><td id="right">
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
					WHERE entry_blog_id IN (1)
						AND entry_status = 2
						AND entry_author_id = author_id
					GROUP BY author_id, author_name
					ORDER BY num DESC";
					$res_authors = mQuery($qry);
					while ($row_authors = mysql_fetch_assoc($res_authors)) {
						echo '<li><a href="../../../articles/author.php?author='. urlencode($row_authors[author_name]) .'&id='. $row_authors[author_id] .'">'. $row_authors[author_name] .'</a><span class="grey"> ('. $row_authors[num] .')</span></li>';
					}
				?></ul>
			<span class="corners-bottom"><span></span></span></div>

		</td>
	</tr></table>

	<?
		include(BASE_DIR .'/includes/body_footer.php');
	?>
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/09/hdtv_and_home_theater_podcast_314_-_black_friday_predictions_and_neuros_osd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
