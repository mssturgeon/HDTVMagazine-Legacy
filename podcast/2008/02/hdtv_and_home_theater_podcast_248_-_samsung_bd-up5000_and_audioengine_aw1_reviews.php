<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1248";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1248 AND placement_is_primary = 1";
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
	<meta name="keywords" content="blu ray, usb port, high def, power adapter, mini jack, audio, wireless, player, USB, DVD, receiver, ray, dvd, usb, sender, adapter, Blu, Ray, audioengine, Audioengine, blu, speakers, seen, good, both" />
	<meta name="description" content="Listener Mike sent in a review of the Samsung BD-UP5000 combo Blu-ray and HD-DVD player.  We review the Audioengine AW1 Wireless Audio Adapter.  And the top 10 things we really wish could have been in high definition." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #248 - Samsung BD-UP5000 and Audioengine AW1 reviews</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>February  8, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23248%20-%20Samsung%20BD-UP5000%20and%20Audioengine%20AW1%20reviews&amp;bodytext=Listener%20Mike%20sent%20in%20a%20review%20of%20the%20Samsung%20BD-UP5000%20combo%20Blu-ray%20and%20HD-DVD%20player.%20%20We%20review%20the%20Audioengine%20AW1%20Wireless%20Audio%20Adapter.%20%20And%20the%20top%2010%20things%20we%20really%20wish%20could%20have%20been%20in%20high%20definition.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-02-08.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br></p>

<p><strong>Samsung <a target="blank" href="http://www.samsung.com/us/consumer/detail/detail.do?group=mp3audiovideo&type=blu_ray&subtype=duohdplayer&model_cd=BD-UP5000/XAA">BD-UP5000</a></strong></p>

<p>Listener Mike provided an excellent review of the Samsung BD-UP5000 combo Bl-ray and HD-DVD player.  Here is his conclusion on the unit.</p>

<p>The Samsung BD-UP5000 is one of the few high definition disc players that can play both Blu-Ray and HD-DVD discs.  Its MSRP is $999 but is usually available for $799 or somewhat less.  I have owned one now for almost a month.  Ironically I got it about two hours before the Warner Blu-Ray exclusive decision became public.<br />
The BD-UP5000 has some very good things going for it and a few caveats.<br />
 <br />
<strong>The pluses:&nbsp;</strong><br><ul><li>It does play HD-DVD and Blu-Ray discs very well. &nbsp;You only need one player to play both formats plus all your standard DVDs. &nbsp;I have played a number of both types and they all looked great. &nbsp;It supports all of the HD-DVD features and all of the pre-Profile 1.1 features of Blu-Ray.</li><li>Great picture quality for HD-DVD, Blu-Ray and standard DVD. &nbsp;The unit has the Reon processor for video processing and looks as good as any player on the market, better than most of them. &nbsp;Even when fed to component, all discs types look great, even without the Reon operating. &nbsp;The standard DVD performance is as good as any I have seen.</li><li>It is one of the few players with 7.1 analog audio out connections which is important to someone with an older surround processor, like me.</li><li>It loads high-def discs reasonably quickly, at least compared to other players.</li><li>It has a very simple, easy-to-understand user interface</li><li>It has a built-in Ethernet connection and is Blu-Ray Profile 1.1 ready (but not yet enabled)</li><li>The unit is very sleek looking with its piano black finish and blue lighting</li><li>The remote is good and sleek looking, though not illuminated.<br></li></ul><strong>The minuses:</strong><br><br />
<ul><li>There are a handful of high-def discs that don't play or don't play correctly, even after one firmware update. &nbsp;More firmware updates are promised.</li><li>Some people have reported video and audio dropouts with standard and high-def DVDs. &nbsp;I haven't seen this problem but it might indicate unit-to-unit variations.</li><li>It only handles Dolby TrueHD in stereo and DTS-MA not at all at this time. &nbsp;Future updates are supposed to come that will address this for both internal and external decoding but they are rumored as not happening until May.</li><li>Profile 1.1 has not yet been enabled. &nbsp;Profile 2.0 cannot be done with this player because there is not enough onboard memory and no way to add external memory</li><li>Response to the remote can be very slow sometimes</li><li>4:3 standard DVDs are always stretched if you are upscaling them. &nbsp;To watch 4:3 properly you have to switch to 480i or 480p.</li><li>Some might consider the price a little steep though it is pretty comparable to buying one each of a high-end HD-DVD and Blu-Ray player.<br><br />
</li></ul>Finally, there are a huge number of rumors floating around about this player and Samsung's commitment to it.  There are many who doubt that the promised audio updates will come or that there may be chip problems that prevent the updates.  If you're a doubter or worrier, this is probably not the player for you.  This doesn't bother me since I am happy with what it does right now and the future updates will only be pluses.</p>

<p>Overall I am glad I bought this player as a one-box solution to watching all currently available discs, without having to wait for Blu-Ray replacements for HD-DVD exclusive titles.  The video performance is spectacular and the audio issues are minor for my purposes.</p>

<p><strong>Ten things we wish we could have seen in HD</strong></p>

<p>Now that almost everything is being shot in HD, great moments will forever be captured with great detail. For this feature we thought it would be good to go back in time an identify 10 events that would have been great to see in HD. The only caveat is that the event had to have been seen on TV or captured on film. Please feel free to send us your moments.</p>

<p>   <strong>1. The Moon Landing</strong> - I remember watching this as a child at my uncle's house. They had a 26 inch RCA color TV. The reason we went there was that we didn't have a color TV in 1969. Imagine how wonderful it would have looked in HD! The contrast in the moon lends itself to high def!<br />
   <strong>2. The Falling of the Berlin Wall</strong> - It was like a party. HD would have captured the expressions on everyone's face and help us all experience what the Berliners were experiencing.<br />
   <strong>3. The Babe Ruth Call Shot</strong> - Legend has it that Babe Ruth called a Home run in the fifth inning of game 3 of the World Series against the Chicago Cubs. On film you can see the Babe pointing but its not clear what he was pointing at. HD would have ended this debate once and for all.<br />
   <strong>4. The last episode of M*A*S*H</strong> - Still the most watched television show in history. Ara watched it on a 19 inch TV in his college dorm room. An episode that big needed to be seen HD!<br />
   <strong>5. The Ali Frasier fight</strong> - The thrilla in Manila. Back then there was no pay per view. You had to go someplace that had closed circuit TV. I am sure the quality was no where near as good as boxing on HBO!<br />
   <strong>6. The Wright Brothers first flight</strong> - We've all seen the film a thousand times but did we really experience what was going on? Not really.<br />
   <strong>7. Elvis Presley's Ed Sullivan appearance</strong> - Of course we would want to see him filmed without censorship.<br />
   <strong>8. The US Olympic Team beating the USSR in the Olympics</strong> - It was 1980 and Ara and five of his buddies were huddled around a TV at a buddy's house. Twenty six inches and no surround. How did we ever live back then?<br />
   <strong>9. Hindenburg explodes above Lakehurst New Jersey</strong> - Not to be gruesome but we have all seen this one a few times in our life.<br />
  <strong>10. Woodstock</strong> - How amazing would that concert have been on MHD?  Think about it, Jimi Hendrix, Janis Joplin, Crosby, Stills, Nash & Young, Creedence Clearwater Revival, The Who, Santana, Grateful Dead, Joe Cocker, Jefferson Airplane...</p>

<p><br />
<strong>Audioengine <a target="_blank" href="http://audioengineusa.com/store/product_info.php?manufacturers_id=&products_id=82&osCsid=d3c93326469e27713475477989f4590a"> AW1</a> Wireless Audio Adapter</strong></p>

<p>If you own an iPod you have probably wanted to send the music to a remote set of speakers. There are various products on the market that make that possible. Today we review what we feel is the easiest to use wireless adapter on the market. The Audioengine AW1 Wireless Adapter consists of two pieces, a Sender and a Receiver. The Sender will transmit audio from any mp3 device via a 3.5" mini jack or a computer's USB port. It works with both Macs and PCs! The device is small, about the size of a USB key drive. Both the sender and the receiver have a 3.5 inch connection, one for the device with the audio and the other to connect to the speakers. All cables are included, even an adapter to accept RCA outputs from your receiver. More on this later. </p>

<p>The range of the adapter is 100 feet. We did not go the entire 100 feet in our testing but were able to roam comfortably around our palatial estate with no dropouts. That was about thirty feet in a straight line through multiple floors and walls. The audio was crystal clear and we did not hear any interference. The engineers at Audioengine developed a proprietary audio protocol running over 802.11, which is how they get such high interference tolerance against WLAN, Bluetooth, cordless phones, microwave ovens, etc.</p>

<p>Both Sender and Receiver get power from the USB bus. If you wish to use the device with an mp3 player you need to use the included USB AC Power Adapter. This ties you to a wall socket, which is OK if you control your music from one spot. The only issue we have is that the sender is not battery powered. They are working on a battery powered sender but no time frame has been established for the product.</p>

<p>When using the Sender on a computer it gets power and audio from the USB Port. When using the Sender with an mp3 player you will need to connect the included mini jack cable to the mp3 player's headphone output. If you use the receiver with the Audioengine A5 (<a target="blank" href="http://www.htguys.com/archive/2007/September04.html">See our Review</a>), the connection is as easy as 1 2 3. First connect the mini jack cable to the A5 input on the top of the speaker, then connect the other end of the cable to the output of the receiver, and finally plug the receiver into the USB port on the top of the A5. If you want to use a speaker set other than the A5s you will need to buy an additional power adapter. Audioengine will sell you a USB charger for $20, but any USB charger will work (iPod, phone, etc.).</p>

<p>The entire installation took us about 15 minutes. It would have been shorter but we had to read the instructions to figure out how to get audio out of the Mac USB port. Took us two minutes to find in the manual and 15 seconds to do. There is also the ability to have one sender transmit to multiple receivers so you can do whole house audio quite easily.</p>

<p><strong>Main Use Configurations:</strong><ul><li>PC to Remote Speakers - More than likely you will use this with a laptop. Wireless speakers are really not needed in most computer situations.</li><li>mp3 to Remote Speakers/Receiver - Very typical situation. Just be aware that if you are not using Audioengine A5 speakers or if you are sending the audio to a receiver you will need an additional power adapter</li><li>Receiver to A5s as wireless surrounds/subwoofer - This is an easy way to turn your Audio Engine A5s into wireless surround speakers. If you don't have A5s you can use any other pair of powered speakers as long as you add an additional power adapter. Going the A5 route will run you about $475 for an very good wireless surround system. May seem like a lot but what will it cost to wire the room?</li></ul></p>

<p>We have reviewed a few wireless systems on the show. They all worked pretty well but every now and then interference from a cordless phone or wireless router would ruin the experience. The Audioengine AW1 Wireless Adapter is the best wireless audio we have heard to date. Its a snap to install and use. Combined with the A5s, its combo that's hard to beat!<br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>February  8, 2008 05:28 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 1248
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
					AND entry_id <> 1248
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/02/hdtv_and_home_theater_podcast_248_-_samsung_bd-up5000_and_audioengine_aw1_reviews.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
