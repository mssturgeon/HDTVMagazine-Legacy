<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1538";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1538 AND placement_is_primary = 1";
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
	<meta name="keywords" content="review high, technical review, blu ray, high def, def digest, buy, high, video, High, def, Blu, technical, Def, digest, blu, ray, review, Technical, Buy, Digest, surround, content, able, movies, USB" />
	<meta name="description" content="As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.  But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.  Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #324 - Spooky Blu-ray, can it live forever?</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>October 30, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23324%20-%20Spooky%20Blu-ray%2C%20can%20it%20live%20forever%3F&amp;bodytext=As%20the%20first%20Halloween%20with%20one%20High%20Definition%20movie%20disc%20format%2C%20we%20compiled%20a%20list%20of%20the%20ten%20best%20spooky%20movies%20on%20Blu-ray%2C%20just%20in%20case%20you%20don%27t%20have%20anything%20to%20do%20and%20want%20to%20watch%20something%20scary.%20%20But%20before%20we%20get%20too%20far%20on%20that%2C%20we%20also%20cover%20some%20recent%20reports%20about%20Blu-ray%20being%20a%20temporary%20format%20and%20not%20having%20that%20much%20life%20left.%20%20Who%20knows%2C%20maybe%20next%20year%20we%27ll%20give%20the%20list%20of%20the%20top%20ten%20high%20definition%20downloads%20for%20Halloween.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div align="center" style="height:55px; padding-top:20px"><span style="margin:0 10px"><a href="http://click.linksynergy.com/fs-bin/stat?id=FK62p2waXuc&amp;offerid=78941&amp;type=3&amp;subid=0&amp;tmpid=1826&amp;RD_PARM1=http%253A%252F%252Fphobos.apple.com%252FWebObjects%252FMZStore.woa%252Fwa%252FviewPodcast%253Fid%253D73799860%2526partnerId%253D30" target="_blank"><img src="http://www.htguys.com/images/itunes_subscribe.gif" alt="iTunes"></a></span><span style="margin:0 10px"><a title="" href="zune://subscribe/?HDTV%20and%20Home%20Theater%20Podcast=http://feeds.feedburner.com/HdtvPodcast"><img title="" alt="" src="http://podcast411.com/07img/1click_Zune.gif" border="0"></a></span></div>
<div><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-10-31.mp3">Listen Now - mp3</a></div>
<div><a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a></div>
<div><a href="http://www.htguys.com">Website</a></div><br>

<br><strong>Today's Show:</strong><br>
As the first Halloween with one High Definition movie disc format, we compiled a list of the ten best spooky movies on Blu-ray, just in case you don't have anything to do and want to watch something scary.&nbsp; But before we get too far on that, we also cover some recent reports about Blu-ray being a temporary format and not having that much life left.&nbsp; Who knows, maybe next year we'll give the list of the top ten high definition downloads for Halloween.<br>
<br><strong>Top Ten Blu-ray movies for Halloween</strong><br>
<br><strong>10. Pan's Labyrinth (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000WSLAUO" id="ehh7">Buy now</a>)</strong><br>
Following a bloody civil war, young Ofelia enters a world of
unimaginable cruelty when she moves in with her new stepfather, a
tyrannical military officer. Armed with only her imagination, Ofelia
discovers a mysterious labyrinth and meets a faun who sets her on a
path to saving herself and her ailing mother. But soon, the lines
between fantasy and reality begin to blur, and before Ofelia can turn
back, she finds herself at the center of a ferocious battle between
good and evil.<br>
<ul>

<li>1080p VC-1 video</li>
<li>DTS HD Master Audio 7.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1181/panslabyrinth.html" id="bn2e">review</a> at High Def Digest<br>
</li></ul><strong>9. Zodiac: Director's Cut (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B001HUHBAE" id="gr-q">Buy now</a>)</strong><br>
Closer in spirit to a police procedural than a gory serial-killer flick, David Fincher's <em>Zodiac</em>
provides a sleek, armrest-gripping re-invention of the crime film. It
surveys the investigation of the Zodiac killings that terrorized the
San Francisco Bay area in the late -60-early -70s; Zodiac not only
killed people, but cultivated a Jack the Ripper aura by sending icky
letters to the newspapers and daring readers to solve coded messages.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby Digital 5.1 surround track</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1636/zodiac_nl.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>8. Underworld</strong><strong> (<a title="Buy now" target="_blank" href="http://www.htguys.com/shop.php?id=B000TGJ80I" id="gr-q">Buy now</a>)<br>
</strong>In the Underworld, Vampires are a secret clan of modern aristocratic
sophisticates whose mortal enemies are the Lycans (werewolves), a
shrewd gang of street thugs who prowl the city's underbelly. Noone
knows the origin of their bitter blood feud, but the balance of power
between them turns even bloodier when a beautiful young Vampire warrior
and a newly-turned Lycan with a mysterious past fall in love. Kate
Beckinsale and Scott Speedman star in this modern-day, action-packed
tale of ruthless intrigue and forbidden passion ­ all set against the
dazzling backdrop of a timeless, Gothic metropolis.<br>

<ul>
<li>1080p AVC MPEG-4 video</li>
<li>uncompressed PCM 5.1 surround track<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/996/underworld.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>7. The Orphanage </strong><strong>(<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
In
Newline's The Orphanage a woman discovers dark secrets hidden
within her cherished childhood home.&nbsp; The supernatural drama is the
feature film debut of acclaimed young Spanish director
Juan Antonio Bayona. A superbly atmospheric and emotionally powerful
tale of love, loss and guilt.&nbsp; There are a few gory make-up effects,
but Bayona mostly preys on our fear of the unknown to craft a
first-rate fright fest.<br>

<ul>
<li>1080p VC-1 video</li>
<li>DTS HD Lossless Master Audio 7.1 (Spanish)</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/446/orphanage.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>6. I Am Legend</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
Robert Neville is a brilliant scientist, but even he could not contain
the terrible virus that was unstoppable, incurable, and man-made.
Somehow immune, Neville is now the last human survivor in what is left
of New York City and maybe the world. For three years, Neville has
faithfully sent out daily radio messages, desperate to find any other
survivors who might be out there. But he is not alone. Mutant victims
of the plague, The Infected, lurk in the shadows...watching
Neville's every move...waiting for him to make a fatal mistake.
Perhaps mankind's last, best hope, Neville is driven by only one
remaining mission: to find a way to reverse the effects of the virus
using his own immune blood. But he knows he is outnumbered...and
quickly running out of time.<br>

<ul>
<li>1080p VC-1 video</li>
<li>Dolby TrueHD 5.1 Surround</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1336/iamlegend.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>5. The Descent (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000JJ5F0W" id="ew9t">Buy Now</a>)</strong><br>
On an annual extreme outdoor adventure, six women meet in a remote part
of the Appalachians to explore a cave hidden deep in the woods. Far
below the surface of the earth, disaster strikes when a rock fall
blocks their exit and there's no way out. The women push on, praying
for another exit, but there is something else lurking under the earth.
The friends are now prey, forced to unleash their most primal instincts
in an all-out war against an unspeakable horror - one that attacks
without warning, again and again and again.<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>uncompressed PCM 6.1 surround mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/463/descent.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>4. Disturbia</strong><strong> (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000RO6K80" id="ew9t">Buy Now</a>)</strong><br>
After his father’s accidental death, Kale remains
withdrawn and troubled. When he lashes out at a well-intentioned but
insensitive teacher, he finds himself under a court-ordered house
arrest. His mother continues to cope, working extra shifts to support
herself and her son, as she tries in vain to understand the changes in
his personality. His interests turn outside the windows of his suburban
home toward those of his neighbors, including a mutual attraction to
the new girl next door. Together, they begin to suspect
that another neighbor is a serial killer. Are their suspicions merely
the product of Kale’s cabin fever and vivid imagination? Or have they
unwittingly stumbled across a crime that could cost them their lives?<br>
<ul>
<li>1080p AVC MPEG-4 video</li>

<li>DTS 6.1 Surround-ES and Dolby Digital 5.1 Surround EX</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/939/disturbia.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>3. Monster House</strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000IFRT38" id="ew9t">Buy Now</a>)</strong><br>
Even for a 12-year old, D.J. Walters has a particularly overactive
imagination. He is convinced that his haggard and crabby neighbor
Horace Nebbercracker, who terrorizes all the neighborhood kids, is
responsible for Mrs. Nebbercracker's mysterious disappearance. Any toy
that touches Nebbercracker's property, promptly disappears, swallowed
up by the cavernous house in which Horace lives. D.J. has seen it with
his own eyes! But no one believes him, not even his best friend,
Chowder. What everyone does not know is D.J. is not imagining things.
Everything he's seen is absolutely true and it's about to get much
worse than anything D.J could have imagined.<br>
<ul>
<li>1080p MPEG-2 video</li>

<li>uncompressed PCM 5.1 surround
  mix</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/186/monsterhouse.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>2. Sweeney Todd: The Demon Barber of Fleet Street  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B001BN1ZHW" id="ew9t">Buy Now</a>)</strong><br>
After years of rumors, it turns out that Tim Burton was the perfect visionary to film <em>Sweeney Todd: The Demon Barber of Fleet Street</em>,
Stephen Sondheim's Broadway masterpiece, and the result is a macabre
and moving musical movie as enthralling as anything Burton has ever
done. The show's mix of gothic horror, Grand Guignol, <em>very</em> dark
humor, and witty and beautiful music never was the stuff of traditional
musical comedy, but it's a powerful work, and perhaps the richest of
the late 20th century.<br>

<ul>
<li>1080p VC-1 video</li>
<li>lossless Dolby TrueHD 5.1</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/1603/sweeneytodd2007.html" id="bn2e">review</a> at High Def Digest</li></ul><strong>1. The Shining </strong><strong>  (<a title="Buy Now" target="_blank" href="http://www.htguys.com/shop.php?id=B000UJ48WC" id="ew9t">Buy Now</a>)</strong><br>
?Heeeeere?s Johnny!? In a macabre masterpiece adapted from Stephen
King?s novel, Jack Nicholson falls prey to forces haunting a snowbound
mountain resort with a macabre history. Kubrick's <em>The Shining</em> gets under your skin and chills your bones; it stays with you, inhabits you, haunts you. And there's no place to hide.

<ul>
<li>1080p VC-1 video</li>
<li>uncompressed PCM 5.1 Surround mix<br>
</li>
<li>Technical <a title="review" target="_blank" href="http://bluray.highdefdigest.com/325/shining1980.html" id="bn2e">review</a> at High Def Digest</li></ul><br>
<br><strong>Is Blu Ray a Temporary Platform?</strong>
<div><br>
<strong></strong></div>
<div> We received an email from Brad in Festus MO with a link to a CNET
article that suggests that the Playstation 4 will not have a Blu-ray
drive (<a href="http://news.cnet.com/8301-13506_3-10042820-17.html" target="_blank" title="Why the Playstation 4 won't have Blu-ray">Why the Playstation 4 won't have Blu-ray</a>).
One reason for this assertion is that technology is moving so fast that
there isn't enough time for Blu Ray to take a strong hold before a
better technology makes Blu-ray obsolete. So for today we would like to
discuss this interesting idea.</div>

<div>&nbsp;</div>
<div><strong>Reasons for Blu-ray's Demise:</strong></div>
<div>
<ol>
<li>More
convenient to download HD content then go out and buy or rent. Its safe
to say that in the future we will have more bandwidth than we have now.
Its not unrealistic that we will be able to download HDX quality movies
in less than 30 minutes. We certainly will be able to start watching
within five minutes.</li>
<li>There will be a storage breakthrough that
will give us 25 or 50GB on a USB stick. Just two years ago no one would
have believed that you can store 8 GB on a USB key. Today you can buy a <a href="http://www.htguys.com/shop.php?id=B000TXEE14" target="_blank" title="8GB USB Stick for less than $25">8GB USB Stick for less than $25</a>!
Soon we will have USB 3.0 that not only increases the capacity but also
the data rate. USB 3.0 will have a 4.8 Gbps data rate so copying files
to the drive will not take forever.</li>
<li>Portability. With HD movies
on a stick you will be able to take you movies on the go. We predict
that there will be mobile entertainment systems that will be able to
receive the USB stick and play the contents. Likewise we feel that
future iPods will store HD versions of movies and be able to down
convert on the fly so that legacy devices with A/V inputs will still be
usable.&nbsp;</li>
<li>Studio Support - This is the most pie in the sky!
Studios will realize that doing away with all the packaging will
greatly increase their profit and they will fully support downloadable
content with no restrictions. They will also have two types of content,
free with ads included, and no ads but you have to pay for it.</li>
<li>Interactive
Content - BD live can still work in this scenario. There is no reason
why computers or other players can't access the Internet and provide a
dynamic experience.</li></ol>

<div>&nbsp;</div>
<div><strong>Our hope for the future:</strong></div>
<div>We'd
like to see a HTPC that is more like a DVR. It should be able to
download content but also have a tuner built in. Recorded and
downloaded movies should be transportable to a portable device in full
HD. In effect, the portable device should act like a VHS cassette tape.
If I have rights to the content I should be able to connect it to a
friends device and play&nbsp;the&nbsp;content. For universal access the device
should be able to output AV through RCA cables for playback on older
legacy type of devices. This ends up bringing the
Video&nbsp;Cassette&nbsp;Recorder into the 21st century. DVRs are great, but its
too hard to take your recorded content with you!</div>
<div>&nbsp;</div>
<div>&nbsp;</div></div><br>
<br><br>

		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>October 30, 2008 11:38 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1538
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
					AND entry_id <> 1538
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/10/hdtv_and_home_theater_podcast_324_-_spooky_blu-ray_can_it_live_forever.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
