<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1249";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1249 AND placement_is_primary = 1";
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
	<meta name="keywords" content="home theater, best buzz, insight media, shutter glasses, buzz awards, inch, best, display, Best, pdp, PDP, image, ces, technology, CES, walls, theater, samsung, Samsung, glasses, home, buzz, OLED, panasonic, good" />
	<meta name="description" content="Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version Electronic House magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.
 
And yes we went to CES, but no we didn't see everything.  Insight Media just released their &quot;Best Buzz&quot; awards for CES 2008 and they mentioned a few products we either didn't see or didn't talk much about, so we thought it would be good to go over some of them.  The Best Buzz awards are given by Insight Media at CES, and other trade shows each year.  You can't petition to win - you win by showing a product or technology that gets people talking - something that creates buzz because of its uniqueness, innovation, styling, boldness or is just plain cool.
  
Finally a listener put together a A/V room with components that cost less than $5,000. This is something that you would be proud to show in your home. Take a look for yourself. Listener Joe's $5K Theater" />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media\'s CES 2008 Best Buzz Awards'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #249 - How to Paint Your Home Theater and Insight Media's CES 2008 Best Buzz Awards</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>February  9, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23249%20-%20How%20to%20Paint%20Your%20Home%20Theater%20and%20Insight%20Media%27s%20CES%202008%20Best%20Buzz%20Awards&amp;bodytext=Most%20of%20us%20believe%20that%20what%20color%20you%20choose%20to%20put%20on%20the%20walls%20is%20a%20decision%20that%20should%20be%20left%20firmly%20in%20the%20hands%20of%20the%20aesthetics%20committee.%20%20But%20according%20to%20an%20article%20from%20the%20online%20version%20Electronic%20House%20magazine%2C%20the%20home%20theater%20enthusiast%20may%20want%20to%20weigh%20in%20on%20the%20decision.%20%20It%20seems%20there%20are%20some%20very%20good%20colors%20to%20use%20in%20the%20home%20theater%2C%20and%20some%20very%20bad%20colors.%0A%20%0AAnd%20yes%20we%20went%20to%20CES%2C%20but%20no%20we%20didn%27t%20see%20everything.%20%20Insight%20Media%20just%20released%20their%20%22Best%20Buzz%22%20awards%20for%20CES%202008%20and%20they%20mentioned%20a%20few%20products%20we%20either%20didn%27t%20see%20or%20didn%27t%20talk%20much%20about%2C%20so%20we%20thought%20it%20would%20be%20good%20to%20go%20over%20some%20of%20them.%20%20The%20Best%20Buzz%20awards%20are%20given%20by%20Insight%20Media%20at%20CES%2C%20and%20other%20trade%20shows%20each%20year.%20%20You%20can%27t%20petition%20to%20win%20-%20you%20win%20by%20showing%20a%20product%20or%20technology%20that%20gets%20people%20talking%20-%20something%20that%20creates%20buzz%20because%20of%20its%20uniqueness%2C%20innovation%2C%20styling%2C%20boldness%20or%20is%20just%20plain%20cool.%0A%20%20%0AFinally%20a%20listener%20put%20together%20a%20A%2FV%20room%20with%20components%20that%20cost%20less%20than%20%245%2C000.%20This%20is%20something%20that%20you%20would%20be%20proud%20to%20show%20in%20your%20home.%20Take%20a%20look%20for%20yourself.%20Listener%20Joe%27s%20%245K%20Theater&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
<strong>Today's Show:</strong><br>

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-12.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br><br />
Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version <a href="http://www.electronichouse.com/">Electronic House</a> magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.<br />
 <br />
And yes we went to CES, but no we didn't see everything.  Insight Media just released their "Best Buzz" awards for CES 2008 and they mentioned a few products we either didn't see or didn't talk much about, so we thought it would be good to go over some of them.  The Best Buzz awards are given by <a href="http://www.insightmedia.info/">Insight Media</a> at CES, and other trade shows each year.  You can't petition to win - you win by showing a product or technology that gets people talking - something that creates buzz because of its uniqueness, innovation, styling, boldness or is just plain cool.<br />
  <br />
Finally a listener put together a A/V room with components that cost less than $5,000. This is something that you would be proud to show in your home. Take a look for yourself. <a href="http://www.htguys.com/archive/2008/February12_5K_Theater.html">Listener Joe's $5K Theater</a></p>

<p><strong>Insight Media's CES 2008 Best Buzz Awards</strong><br />
 <br />
<strong>Best Image of the Show</strong><br />
<u>Samsung 14-inch FHD OLED-TV</u></p>

<p><em>Samsung takes the Best Buzz for Best Image at CES with its Full HD (1920 x 1080) display from a 14-inch AM OLED and persistent crowds in the massive Samsung booth agreed.  The whopping 1920 x 1080 pixels in a super slim 14-inch OLED display rendered images in a photograph like quality as yet unmatched by any other.</p>

<p>Pixels were virtually nonexistent on the super thin (2cm) screen and the emissive pedigree of this OLED image gives the soft subtle hues and crisp bright tones that rival a mirror image of reality.  The image quality question, "are we there yet?", gets a resounding YES - now all Samsung has to do is find a way to replicate it in mass quantities - and oh yes...at an affordable price.</em></p>

<p><strong>Best PDP Display</strong><br />
<u>Panasonic 150" PDP TV</u></p>

<p><em>It's almost too easy but we can't avoid it.  The PDP Best Buzz goes to Panasonic's good-looking, crowd-pleasing 150-inch Plasma Display.  Introduced at CES, the 150-inch is now the largest unitary (no tiling) flat-screen display in the world, taking the title from Sharp's 108-inch LCD-TV.</p>

<p>The Panasonic's image quality, as well as size, was impressive.  Full HD on a screen this size wouldn't have been quite good enough, so Panasonic built a panel with 4000x2000 pixels - that's 8 million pixels instead of the approximately 2 million pixels in a Full HD display.</p>

<p>The 150-inch panel is made on a full sheet of glass from Panasonic's current fab, the same-sized glass that Panasonic normally uses to make eight 50-inch PDPs.  Volume production is scheduled for 2009 from the new Amagasaki manufacturing line.</em><br />
 <br />
<strong>Best PDP Technology Demo</strong><br />
<u>Panasonic High Efficiency PDP</u></p>

<p><em>Panasonic wins for its demonstration of a 42-inch prototype PDP with double the efficiency of current products.  Panasonic developed new phosphors and cell design technology for improved discharge, along with a new circuit and drive technology to significantly reduce power consumption.  As a result, the 42-inch prototype has twice the luminance efficiency and provides the same brightness as the existing 42-inch 1080p full HD PDP, while cutting the power consumption by half.  That's impressive, and got the show buzzing.</p>

<p>The double-efficiency technology forms the basis for next-generation PDPs, enabling even thinner profiles, larger screens, brighter images, higher definition and lower power consumption.</em></p>

<p><u>Pioneer's Super Black and Super Thin PDP Demos</u></p>

<p><em>Pioneer has shown there is plenty of life in the old dog with an amazing demo of low black levels on a next-gen KURO plasma monitor.  During the demo in a darkened room, you could see the faint glow of two Plasma monitors - and when the video came on, you realized there were three monitors in the room.   The blacks on this new KURO were so good that objects on the screen appeared to be floating in mid-air, while the colors had plenty of pop.  If SED technology wasn't officially buried yet, this demo did the trick.</p>

<p>Outside the booth, Pioneer also showed a 9mm thick 50-inch 1080p plasma monitor.  That's about 1/3 of an inch!  It was so thin we had trouble getting a clean photo of it.  Image quality was as good as any current-model KURO display, and the carpet around this demo was soaked from all the drooling over this Best Buzz winning display.</em></p>

<p><strong>Best 3D Displays</strong></p>

<p><em>CES created a new awareness of the possibilities for 3D TVs.  Long thought to be many years off, the possibility of creating a real 3D TV market soon, has now dawned on many players.  Of significance at CES was the demonstration of 3D TVs using projection, PDP and LCD technology.  Our congratulations go out to all three of these pioneering trendsetters.</em></p>

<p><u>3D Enabled Laser TV - Mitsubishi</u></p>

<p><em>We choose Mitsubishi for their demonstration of a Laser TV that can operate in 3D mode.  It is based upon DLP technology and active shutter glasses and was demonstrated for the media at a special event for the unveiling of the Laser TV.  Image quality was superb - perhaps the best we have seen, period.</p>

<p>Mitsubishi has not only created a very compelling 3D TV, but it is also trying to create a new TV category - Laser TV.  We think this summer the company will come to market with a 65-inch model that will have an impressive color gamut and great contrast.  For the 3D mode, it uses the same "SmoothPicture" technology on Mitsubishi's other DLP-TVs, which can be easily adapted to display stereoscopic images - once the content is properly formatted over an HDMI input.</em><br />
 <br />
<u>3D PDP-TV - Samsung</u></p>

<p><em>In an effort to differentiate their PDP-TV products from those offered by other companies, Samsung has turned to stereoscopic 3D.  Most of Samsungs' DLP-RPTVs are already 3D enabled, but now, it has extending 3D to PDPs.  This is the first time a major CE company has said it would commercialize a glasses-based stereoscopic PDP-TV.</p>

<p>To produce the 3D effect, Samsung borrows the same checkerboard pattern it uses on DLP-TVs and runs the PDP at 120 frames/sec.  For the left eye image, a checkerboard-sampled version of the image is displayed on the PDP.  This is synchronized with the shutter glasses to allow this image to be seen by the user.  The same is done for the right eye image in the second half of the frame.  Samsung undoubtedly modified the phosphors somewhat to speed up their response, especially in the green so as to lower crosstalk or ghosting between the two images.  This crosstalk is still not as good as its RPTV sets, but acceptable.  Users can buy a $150 3D kit when the sets go on sale in March.</em><br />
 <br />
<u>3D LCD-TV - SpectronIQ 3D</u></p>

<p><em>There was also big news and lots of buzz around the SpectronIQ 3D demonstration of its 3D LCD-TV product - a 46-inch model that will ship this summer.  This is the first time we expect to see a 3D LCD-TV sold in the US through major Big Box stores, which is why this is a big deal.  In addition, it is the first set to include a decoding chip that will allow the display of 3D content from an ordinary DVD, HD DVD or Blu-ray player.  The only rub is that studios will need to press special disks with this encoded 3D version, but it is a big step in creating an easy-to-use consumer 3D TV.</p>

<p>Spectron IQ will use a 3D technology called micro-pol.  It is a line interlaced technique whereby alternate lines contain the left and right eye images that can be seen in each eye using passive polarized glasses (cheaper than active glasses).  Sensio Technologies Inc., of Montreal, Canada, will provide the 3D codec.</em></p>

<p> <br />
<strong>Best OLED Display</strong><br />
<u>31-inch OLED TV from Samsung</u></p>

<p><em>At the massive Samsung CES booth, the company validated the OLED-TV category with a (now you're talking) 31-inch AM OLED display.  The crowds came in droves to see the future of emissive TV with a bright, colorful image that rivals any flat screen TV currently being shipped.</p>

<p>Samsung did a wonderful job of showcasing both the 31-inch and it's smaller 14-inch cousin for the CES crowds.  It was one of the "must-see" exhibits at CES and the reason why we give it the OLED Best Buzz of the show award.</em></p>

<p><strong>Best Innovations</strong><br />
<u>Texas Instruments' DualView Mode</u></p>

<p><em>Texas Instruments' demonstration of the DualView mode on 3D enabled RPTV sets was truly innovative and captivating.  The idea is to create two independent views on the same TV.</p>

<p>The idea leverages the active shutter glasses used in normal 3D mode, but instead of flashing the left and right sides of the glasses to see stereoscopic images, both sides of the glasses open and shut at the same time.  The TV updates at 120 frames per second, in alternate frames running at 60Hz are synchronized to one set of glasses - and one image on the TV, while alternate frames can be viewed with the other set of glasses.  And these images can be different.  This means gamers can get two different views while playing the same game.  This is pretty cool and another novel and innovative use of the 3D display technology.  The quality of the active shutter glasses needs to improve before commercialization can begin,  nonetheless we choose Texas Instruments for their DualView display concept.</em></p>

<p></u>Vudu's HDTV Set Top Box</u></p>

<p><em>VUDU used CES to launch a $399 set-top box that can download HD movies and TV shows over the Internet on a purchase or rental basis.  There is no annual subscription fee, and it will play back in the 1080p/24 format. Expansion storage is also available.  If you can get FHD movies from sources on the Internet, why do you need a Blu-ray or HD DVD player where you pay a lot more to buy the movie?  Food for thought.</em></p>

<p>About Insight Media<br />
Insight Media (www.insightmedia.info) is a leading publishing and consulting firm focused on the display industry. With its core team of world-class display experts, Insight Media tracks the technology, components, products, markets, applications, manufacturing and business aspects of consumer and professional display markets. The company publishes daily and monthly news and analysis as well as in-depth annual technology/market reports. It also hosts industry conferences, provides strategic and tactical consulting services and offers industry education via webinars and on-site seminars. </p>

<p><strong>How to Paint Your Home Theater</strong><br />
Most of us believe that what color you choose to put on the walls is a decision that should be left firmly in the hands of the aesthetics committee.  But according to an article from the online version Electronic House magazine, the home theater enthusiast may want to weigh in on the decision.  It seems there are some very good colors to use in the home theater, and some very bad colors.</p>

<p>Since the point of a home theater is to enjoy movies and HDTV, you need an environment that supports that, not one that distracts from it.  Every display device out there works by beaming colored light at your eyeballs.  Whether it's a TV or a projection screen, it becomes one giant lamp in the front of the room.  But the light doesn't just hit your eyes, it shines on every surface in the room.  If the walls in the room are very reflective, that light will bounce right back at the screen and wash out the picture.  For this reason, dark colors are the best for your home theater.  We all know from studying the color spectrum in grade school that black can loosely be thought of as zero light reflection and white represents complete light reflection.  So obviously the darker your walls, the better your theater will perform.</p>

<p>But it isn't just the color of the walls that matters.  Paint manufacturers have created ways for even black paint to be somewhat reflective by introducing a sheen, or gloss, to the finish.  So ideally you'd have a dark color with no gloss at all.  Those with small children or animals know that the truly matte finishes are very difficult to clean, to a semi-gloss or satin finish is probably a good compromise.  The rule to remember is that your walls should be as dark and as muted as possible.  If the aesthetics committee is asking for white or beige high gloss paint, you may need to step in and offer an opinion.</p>

<p>Don't forget about the ceiling.  It can be just as reflective as the walls themselves.  Having painted a few ceilings myself, this is not a job anyone relishes, but sometimes you have to sacrifice for your passions.  some believe that if you decide to paint the ceiling and the walls different colors, the ceiling should always be the darker of the two colors.  In most cases this is the best rule of thumb.  If the ceiling is lighter than the room, it gives the illusion of being a light source when your watching a movie.  It almost feels like there are lights on even when they aren't.</p>

<p>Of course there's more you can do to the walls to help with audio.  You can rough them up a bit by adding textured finishes.  Smooth walls will reflect sound more than a textured wall.  But if you're really concerned about audio reflection, you'll want to add some sort of fabric to the walls.  The fabric will absorb the sound, while the texture wall will just make it bounce off funny, and thus reduce the amount of reflection that hits your ear.  For fabric options you can mount sound panels, hang tapestries or use thick curtains.  You can even use thick curtains where there aren't windows by painting a faux window, or hanging them over a large mirror.  This give the sound absorption you need and even adds depth and size to the room.  You simply close the when your watching something to eliminate light reflecting from your imaginary window.</p>

<p>Read the full Electronic House article <a href="http://www.electronichouse.com/article/home_theater_colors_choose_carefully/">here</a>.<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>February  9, 2008 12:24 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1249
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
					AND entry_id <> 1249
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_249_-_how_to_paint_your_home_theater_and_insight_medias_ces_2008_best_buzz_awards.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
