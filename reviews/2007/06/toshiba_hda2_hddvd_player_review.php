<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');

	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author['title'] == '') ? '' : "{$author['title']}<br />";
	$author_headshot = ($author['img'] == '') ? '' : '<img src="'. BASE_IMG_HOST .'/images/portraits/'. $author['img'] .'" alt="The HT Guys" height="100" width="100"/>';

	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 617 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category['placement_category_id'];

	# Get number of comments
	$sql = "SELECT t.topic_id, topic_replies
	FROM aux_mt_entry a, phpbb_topics t
	WHERE entry_id = 617
		AND a.topic_id = t.topic_id";
	$res_comments = mQuery($sql);
	$row_comments = mysql_fetch_assoc($res_comments);
	if ($row_comments['topic_replies'] > 0) {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a href="/forum/viewtopic.php?t='. $row_comments['topic_id'] .'">'. $row_comments['topic_replies'] .' Comments</a></li>';
	} else {
		$comments = '<li class="li_horizontal"><img src="'. BASE_IMG_URL .'/images/icon_comments.gif" alt="" height="14" width="16" /> '.
		'<a class="red" href="/forum/viewtopic.php?t='. $row_comments['topic_id'] .'">Post First Comment</a></li>';
	}

	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author['channel'];

	# Set defaults which may be overridden by blog type below
	$container = 'article_container';
	$meta = <<<EOT
		<meta name="medium_type" content="blog" />
		<link rel="image_src" href="" />
EOT;
	$v_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_vertical"><a class="DiggThisButton" rel="external"
		href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/06/toshiba_hda2_hddvd_player_review.php&amp;title=Toshiba HD-A2 HD-DVD Player Review"></a></li>
</ul></div>
EOT;
/*
	<li class="li_vertical" id="tm_li"></li>
	<li class="li_vertical"><a name="fb_share" type="box_count" href="http://www.facebook.com/sharer.php"></a></li>
*/

	$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
</ul></div>
EOT;
/*
	<li class="li_horizontal"><a class="DiggThisButton" rel="external"
		href="http://digg.com/submit?url=http://www.hdtvmagazine.com/reviews/2007/06/toshiba_hda2_hddvd_player_review.php&amp;title=Toshiba HD-A2 HD-DVD Player Review"><img src="http://digg.com/img/diggThisCompact.png" height="18" width="120" alt="DiggThis" /></a></li>
*/
	switch (8) {
		case 1: # Articles
			$feed_name = 'hdtv-articles';
			$sub_type = SUB_ARTICLES;
			$sub_label = 'Receive instant notification of new articles';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Articles via email as soon as they are published.';
			}
			break;
		case 4: # Interviews
			$feed_name = 'hdtv-interviews';
			break;
		case 5: # History
			$feed_link = '<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/hdtv-archive" />';
			break;
		case 6: # Test
			$sub_type = 0;
			$sub_label = 'Receive instant notification of "Stuff"';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of "Stuff" via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of "Stuff" via email as soon as they are published.';
			}
			break;
		case 7: # Bulletins
			$google_links_channel = ''; # Don't count bulletins
			$feed_name = 'hdtv-news';
			$container = 'bulletin_container';
			$author_headshot = '';
			$sub_type = SUB_BULLETINS;
			$sub_label = 'Receive instant notification of HDTV Bulletins';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of HDTV Bulletins via email as soon as they are published.';
			}
			$meta = <<<EOT
	<meta name="medium_type" content="news" />
	<link rel="image_src" href="" />
EOT;
			break;
		case 8: # Reviews
			$feed_name = 'hdtv-reviews';
			$sub_type = SUB_REVIEWS;
			$sub_label = 'Receive instant notification of new reviews';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Reviews via email as soon as they are published.';
			}
			break;
		case 9: # Podcasts
			# Get enclosure info
			$sql = "SELECT enclosure_url, enclosure_type FROM aux_mt_entry WHERE entry_id = 617";
			$res_enclosure = mQuery($sql);
			$row_enclosure = mysql_fetch_assoc($res_enclosure);
			$enclosure_url = $row_enclosure['enclosure_url'];

			$podcast_chicklets = '<span><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="'. BASE_IMG_HOST .'/images/chicklet-itunes.gif" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="absmiddle" height="15" width="80"></a></span>'.
				'<span><a href="<?=$enclosure_url?>"><img src="'. BASE_IMG_HOST .'/images/chicklet-mp3-podcast.gif" alt="Download Toshiba HD-A2 HD-DVD Player Review" height="15" width="85"/></a></span>';
			$meta = <<<EOT
	<meta name="medium_type" content="audio" />
	<meta name="audio_type" content="audio/mpeg" />
	<meta name="audio_title" content="Toshiba HD-A2 HD-DVD Player Review" />
	<meta name="audio_artist" content="Ara Derderian &amp; Braden Russell" />
	<meta name="audio_album" content="The HDTV and Home Theater Podcast" />
	<link rel="image_src" href="http://www.htguys.com/storage/thumbnails/3382196-3373802-thumbnail.jpg" />
	<link rel="audio_src" href="$enclosure_url" />
EOT;

			$sub_type = SUB_PODCAST;
			$sub_label = 'Receive instant notification of new episodes';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new episodes of The HDTV Podcast via email as soon as they are published.';
			}
			$itunes_chicklet = BASE_IMG_HOST .'/images/chicklet-itunes.gif';
			# Need a better check here if we add other podcasts
			$contents = @file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=hdtvpodcast');
			$xml = new SimpleXMLElement( $contents );

			$h_buttons = <<<EOT
<div id="dd_right"><ul>
	<li class="li_horizontal">$comments</li>
	<li class="li_horizontal" id="tm_li"></li>
	<li class="li_horizontal"><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php"></a></li>
	<li class="li_horizontal">
		<span style="line-height:16px;vertical-align:middle;">{$xml->feed->entry['circulation']}
			<a href="http://click.linksynergy.com/fs-bin/click?id=FK62p2waXuc&subid=&offerid=146261.1&type=10&tmpid=1826&RD_PARM1=http%3A%2F%2Fphobos.apple.com%2FWebObjects%2FMZStore.woa%2Fwa%2FviewPodcast%3Fid%3D73799860" target="_blank"
				><img src="$itunes_chicklet" alt="Subscribe to the HDTV and Home Theater Podcast in iTunes" align="top" height="15" width="80"></a>
		</span>
	</li>
</ul></div>
EOT;

			break;
		case 10: # Columns
			$feed_name = 'hdtv-columns';
			$sub_type = SUB_COLUMNS;
			$sub_label = 'Receive instant notification of new columns';
			if ($userdata['session_logged_in']) {
				$sub_desc = '<a href="/profile-subscriptions.php">Modify your subscription profile</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			} else {
				$sub_desc = '<a href="/profile-create.php">Register Now</a> to receive notification of new HDTV Magazine Columns via email as soon as they are published.';
			}
			$about = 'HDTV Magazine Columns are written by various personalities within the HDTV industry. They are typically shorter than our standard <a href="/articles">Article</a> and quite often express the opinion of the author(s). And of course, opinions expressed by these authors are not necessarily those of HDTV Magazine.';
			break;
		default:
			break;
	}

	require(BASE_DIR .'/includes/doctype.php');
?>
<html>
<head>
	<title>HDTV Magazine - Toshiba HD-A2 HD-DVD Player Review</title>
	<meta name="keywords" content="dvd player, dvd players, multi channel, sale prices, home theater, dvd, player, buy, audio, get, good, menus, movies, toshiba, players, theater, video, even, better, right, great, watching, hdmi, conversion, little" />
	<meta name="description" content="You can find some great deals on the Toshiba HD-DVD players right now, especially the HD-A2.  They've had the 5 free movie promotion going for a while, and it seems like the price just keeps getting lower.  Is the player so bad that they need to practically give it away to get people to buy it?  Why would Toshiba go to such lengths to get these into your home theater?  The answer, of course, is the format war.  In the end, whoever can sell the most movies wins.  And for someone to buy or rent a movie, they have to have a player to watch it on.  If more people have HD-DVD players, more will buy HD-DVD movies and the rest will be history." />
	<meta name="title" content="Toshiba HD-A2 HD-DVD Player Review" />
	<?=$meta?>

	<? require(BASE_DIR .'/includes/page_header.php');?>

	<link rel="alternate" type="application/rss+xml" title="HDTV Magazine Reviews Feed" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />

	<script type="text/javascript">
		function init() {
			document.getElementById("tm_li").innerHTML = getTMButton('http://www.hdtvmagazine.com/reviews/2007/06/toshiba_hda2_hddvd_player_review.php', 'compact', '<?=$admindata['twitter_username']?>', 'bit.ly');
		}

		function tweetMemeButton() {
			if (document.getElementById("tm_li")) {
				var iframeCode = '';
				iframeCode += '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(document.URL) +'&amp;style=normal&amp;source=SEOmofo&amp;service=bit.ly" scrolling="no" frameborder="0" width="50" height="61">';
				document.getElementById("tm_li").innerHTML = iframeCode;
			}
		}
		function getTMButton(url, style, source, service) {
			if (style == 'compact') {w = 70;h = 20;} else {w = 50;h = 61;}
			return '<iframe src="http://api.tweetmeme.com/button.js?url='+ escape(url) +'&amp;style='+ style +'&amp;source='+ source +'&amp;service='+ service +'" scrolling="no" frameborder="0" width="'+ w +'" height="'+ h +'">';
		}
	</script>
	<style>
		#dd_right {float:right;padding:2px;text-align:right;}
		#dd_right ul {padding:0;margin:0;}
		#dd_right ul li {list-style-image:none;list-style-position:outside;padding:4px;margin:0;outline:0 none;background-color:transparent;border:0 none;list-style-type:none;background-image:none;}
		#dd_right .li_horizontal {align:right;display:inline;float:left;font-weight:bold;margin-top:2px;padding:0 10px}
		#dd_right .li_vertical {display:block;list-style-type:none;}
/*		#dd_right img {border:none !important;}*/
		a.stbar.chicklet img {border:0;height:16px;width:16px;margin-right:3px;vertical-align:middle;}
		a.stbar.chicklet {height:16px;line-height:16px;}
	</style>
</head>
<body onload="init();"><div id="body_container">
	<? include(BASE_DIR .'/includes/body_header-4.php');?>
	<table class="bare" cellpadding="0" cellspacing="0"><tr>
		<td id="left">
			<!-- Subscription box -->
			<? if ($sub_type > 0 && ($userdata['subscriptions'] & $sub_type)) {} else {?>
				<div class="important"><span class="corners-top"><span></span></span>
					<img src="<?=BASE_IMG_HOST?>/images/i_inbox.gif" alt="" align="left" height="31" width="38" style="float:left; padding-right:10px" />
					<span class="label"><?=$sub_label?>:</span>
					<?=$sub_desc?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>

			<!-- Article Header -->
			<table class="bare" cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
					<td id="article_headshot" rowspan="3"><?=$author_headshot?></td>
					<td>
						<table class="bare" cellspacing="0" style="width:100%"><tr>
							<td id="article_title" colspan="2"><a href="http://www.hdtvmagazine.com/reviews/2007/06/toshiba_hda2_hddvd_player_review.php">Toshiba HD-A2 HD-DVD Player Review</a></td>
						</tr><tr>
							<td id="article_byline">
								by <b>The HT Guys</b> on <b>June 22, 2007</b>
							</td><td id="article_category">
								Category: <b><a href="/category.php?id=<?=$category_id?>&category=HD DVD Players">HD DVD Players</a></b>
							</td>
						</tr><tr colspan="2">
							<td id="article_buttons" colspan="2"><?=$h_buttons?></td>
						</tr></table>
					</td>
				</tr>
			</table>

			<!-- Main Article Body -->
			<div id="<?=$container?>">
				<?=$v_buttons?>
				<center><a href="/cgi-bin/ntlinktrack.cgi?http://www.htguys.com/"><img src="/images/hdtv-podcast_227x100.gif" alt="The HDTV Podcast"></a><br /><b>This review is featured in the latest podcast from The HT Guys</b><br /><a href="http://www.htguys.com/archive/2007/June22.html">http://www.htguys.com/archive/2007/June22.html</a></center>
<br />

<p>You can find some great deals on the Toshiba HD-DVD players right now, especially the <a href="http://www.tacp.toshiba.com/dvd/product.asp?model=hd-a2">HD-A2</a> (<a href="http://www.htguys.com/shop.php?id=B000IJV4BC">Buy Now</a>).  They've had the 5 free movie promotion going for a while, and it seems like the price just keeps getting lower.  Is the player so bad that they need to practically give it away to get people to buy it?  Why would Toshiba go to such lengths to get these into your home theater?  The answer, of course, is the format war.  In the end, whoever can sell the most movies wins.  And for someone to buy or rent a movie, they have to have a player to watch it on.  If more people have HD-DVD players, more will buy HD-DVD movies and the rest will be history.</p>

<p>As far as the specs go, the HD-A2 supports:<ul><li>HD Output at 720p and 1080i</li><li>SD Upconversion to 480p, 720p and 1080i</li><li>High-performance SHARC® DSP Audio processor</li><li>Dolby® Digital Plus 5.1ch</li><li>Dolby® TrueHD 5.1ch</li><li>DTS® HD (core only)</li><li>Persistent storage</li><li>HDMI™</li><li>Ethernet Port</li></ul></p>

<p>It probably goes without saying that the pure audio visual experience with the HD-A2 is awesome.  We tested it on a JVC HD-61FH97 (<a href="http://www.htguys.com/shop.php?id=B000HE8JAC">Buy now</a>) for video and ran it through a Denon AVR-3806 (<a href="http://www.htguys.com/shop.php?id=B000BO0LQI">Buy now</a>) with Klipsch speakers for audio.  The player provides absolutely the best home theater experience we've had to date.  It was even better than Discovery HD Theater - which is hard to say, but completely true.  The picture quality is stunning and the audio is unbelievable.  We tried King Kong, Aeon Flux, Batman Begins, The Phantom of the Opera, The Entire Matrix Trilogy, you get the picture.  We just couldn't stop watching the thing.</p>

<p>The HD-A2 is the least capable of the current crop of HD-DVD players available from Toshiba.  Both of the other two models, the <a href="http://www.tacp.toshiba.com/dvd/product.asp?model=hd-a20">HD-A20</a> (<a href="http://www.htguys.com/shop.php?id=B000MKC34E">Buy now</a>) and the <a href="http://www.tacp.toshiba.com/dvd/product.asp?model=hd-xa2">HD-XA2</a> (<a href="http://www.htguys.com/shop.php?id=B000M6XKEK">Buy now</a>), support 1080p video.  In fact the HD-A20 is nearly identical to the HD-A2, it just adds 1080p for an extra $100 more on the MSRP.  So the odds that a firmware upgrade will ever be available for the HD-A2 to allow 1080p are pretty slim.  How would you explain that to someone who bought an HD-A20?  The HD-XA2 also comes with HDMI 1.3, better video processing, and gold plated input jacks.  But the HD-A2 is the one that's getting all the hot sale prices, so it appears to be the most popular right now.  But if you shop around, you might find a great deal on the HD-A20.  For example, right now it's only about $25 more than the HD-A2 at the HT Guys store (as of 6/22).</p>

<p>The audio is nothing short of amazing.  Perhaps it was just wishful listening, but we were blown away with how good the multi channel audio from the HDMI connection sounded.  Surround sound tracks have never been that immersive or detailed.  The player lacks multi channel analog outputs, so if you don't have an HDMI capable receiver you won't get the full listening experience.  That's another feature only available on the HD-XA2.  But if you love good audio, like we do, and you like to hear it loud, the HD-A2 doesn't disappoint.</p>

<p>So we've established that HD-DVD viewing is spectacular.  We aren't making any judgments about it versus Blu-ray or anything else, we're simply evaluating this player.  The next thing to check was up-conversion.  In that aspect it did very, very well.  It didn't get a perfect score on the HQV benchmark, but that should be fine.  It seemed to do a very solid job with the movies we watched.  In a very unscientific test, the OPPO 981 probably did a little better with the up-conversion, but they were very close.  All of the usual HT Guys test movies and test scenes came out very good, and they looked better using the up-conversion on the HD-A2 than not using it.  As an upconverting DVD player it scores near the top of the players we've seen.</p>

<p>The player is a little slow to boot up, but not that bad.  And while movies look and sound great, the menus seem to be a bit sluggish.  In fact that's really our only complaint about the player itself.  The setup and configuration menus are easy to use and understand and actually respond very well and firmware updates are very simple (if you have an Ethernet connection handy).  The only problem was with the interactive menus on the discs themselves.  For example, sometimes scrolling through the chapter selection would get a little behind the remote clicks, so even after you stopped clicking the menus would continue to move, making it very difficult to stop on the chapter you really wanted.  It was almost like spinning the big wheel on the Price is Right.  But once our patience kicked in, we were able to master the menus like pros.</p>

<p>Overall the HD-A2 is a great way to jump into the next generation DVD game.  It's only $100 more than the Xbox 360 add on drive (or even less if you're watching the sale prices) and will always be a very good up-converting DVD player, even if the HD-DVD format eventually goes away or merges somehow into a consolidated format.  The audio and video quality are amazing.  If you like watching Discovery HD Theater simply because it looks so darn good, you've got to get an HD-DVD player.</p>
				<p class="posted">
					Posted by <b>The HT Guys</b>, <b>June 22, 2007  8:00 AM</b>
					<span style="float:right">
						<a id="ck_email" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/email.gif" /></a>
						<a id="ck_facebook" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/facebook.gif" /></a>
						<a id="ck_twitter" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/twitter.gif" /></a>
						<a id="ck_sharethis" class="stbar chicklet" href="javascript:void(0);"><img src="http://w.sharethis.com/chicklets/sharethis.gif" /></a>
					</span>
				</p>
			</div>

			<!-- Comments -->
			<?=getComments(617)?>
			<div class="dottedline"></div>

			<? if (8 != 7) echo getBoxMoreFromAuthor('The HT Guys', 617)?>

			<?=getBoxMoreFromCategory($category_id)?>

			<? if ($author['bio_short'] != '') {?>
				<div class="item"><span class="corners-top"><span></span></span>
					<h2>About The HT Guys</h2>
					<?=stripslashes($author['bio_short'])?>
				<span class="corners-bottom"><span></span></span></div>
			<? }?>
		</td><td id="right">
			<div align="center" style="margin:5px 0;">
				<? include(BASE_DIR .'/ads/mrectangle.php');?>
			</div>
			<br />

			<div align="right">
				<? include(BASE_DIR .'/ads/skyscraper.php');?>
			</div>

			<?=getBoxAuthors()?>

			<?=getBoxCategories()?>

			<?=getBoxDiscussions()?>
		</td>
	</tr></table><br />

	<? include(BASE_DIR .'/includes/body_footer.php');?>
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/reviews/2007/06/toshiba_hda2_hddvd_player_review.php" type="text/javascript" charset="utf-8"></script>
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script src="http://digg.com/api/diggthis.js"></script>
	<script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=3da06545-0753-46cb-8739-3ffcef208c1f&amp;type=website&amp;post_services=email%2Ctwitter%2Cdigg%2Cfacebook%2Cmyspace%2Csms%2Cdelicious%2Cstumbleupon%2Cgoogle_bmarks%2Clinkedin%2Cwindows_live%2Creddit%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine%2Cxanga&amp;linkfg=%23003F87&amp;button=false"></script>
	<script type="text/javascript">
		var shared_object = SHARETHIS.addEntry({title: document.title,url: document.location.href});

		shared_object.attachButton(document.getElementById("ck_sharethis"));
		shared_object.attachChicklet("email", document.getElementById("ck_email"));
		shared_object.attachChicklet("facebook", document.getElementById("ck_facebook"));
		shared_object.attachChicklet("twitter", document.getElementById("ck_twitter"));
	</script>
</div></body>
</html>
