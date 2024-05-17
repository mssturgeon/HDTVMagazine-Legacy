<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 910";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, user_id, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'The HT Guys'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
/*
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 910 AND placement_is_primary = 1";
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
	<meta name="keywords" content="rear projection, media center, home theater, popcorn hour, gap computer, media, computer, market, Apple, apple, projection, rear, home, great, new, sagetv, HDTV, theater, should, Media, SageTV, while, extender, our, center" />
	<meta name="description" content="We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty. This year, if you don't count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show. Sony has announced their exiting the market because it wasn't profitable. By not announcing anything new, JVC has all but announced that they won't be in the game for very much longer. Samsung announced a hand full of new models and Mitsubishi is is touting their new laser based unit that should be available before the end of the year. So for today's show we give you some tips that will help you decide if buying a Rear Projection TV is right for you.

Also,
Until now the concept of a media center PC, or a computer in your home theater has largely remained in the realm of the IT expert or extreme enthusiast. There hasn't really been a product yet that has bridged the gap between the IT side of your life and the consumer electronics side. We discuss our odds-on favorites to successfully mass market a computer for the Home Theater and &quot;Bridge the Gap&quot; from your computer to your TV." />
	<title>HDTV Magazine Podcasts - HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV</title>
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
		
		$base_url = strleftback(PHP_SELF, '/') . '/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv';
		$title_encoded = rawurlencode(addslashes('HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php";
		if ($author[img] != '' && 9 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="The HT Guys" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HDTV and Home Theater Podcast #243 - The Death of Rear Projection TVs & Bridging the Gap from your Computer to your TV</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>The HT Guys</b><br />
				<?=$author_title?>
				Posted on <b>January 20, 2008</b><br />
				Category: <b><a href="../../<?=$category_page?>?category=General Interest&id=<?=$category_id?>">General Interest</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php&amp;phase=2&amp;title=HDTV%20and%20Home%20Theater%20Podcast%20%23243%20-%20The%20Death%20of%20Rear%20Projection%20TVs%20%26%20Bridging%20the%20Gap%20from%20your%20Computer%20to%20your%20TV&amp;bodytext=We%20witnessed%20the%20death%20of%20CRT%20a%20few%20years%20ago%20when%20we%20searched%20high%20and%20low%20all%20over%20the%20CES%20show%20floor%20for%20somebody%20showing%20anything%20resembling%20a%20tube%20TV%20and%20came%20up%20empty.%20This%20year%2C%20if%20you%20don%27t%20count%20the%20Texas%20Instruments%20DLP%20booth%2C%20we%20saw%20only%20three%20rear%20projection%20sets%20at%20the%20entire%20show.%20Sony%20has%20announced%20their%20exiting%20the%20market%20because%20it%20wasn%27t%20profitable.%20By%20not%20announcing%20anything%20new%2C%20JVC%20has%20all%20but%20announced%20that%20they%20won%27t%20be%20in%20the%20game%20for%20very%20much%20longer.%20Samsung%20announced%20a%20hand%20full%20of%20new%20models%20and%20Mitsubishi%20is%20is%20touting%20their%20new%20laser%20based%20unit%20that%20should%20be%20available%20before%20the%20end%20of%20the%20year.%20So%20for%20today%27s%20show%20we%20give%20you%20some%20tips%20that%20will%20help%20you%20decide%20if%20buying%20a%20Rear%20Projection%20TV%20is%20right%20for%20you.%0A%0AAlso%2C%0AUntil%20now%20the%20concept%20of%20a%20media%20center%20PC%2C%20or%20a%20computer%20in%20your%20home%20theater%20has%20largely%20remained%20in%20the%20realm%20of%20the%20IT%20expert%20or%20extreme%20enthusiast.%20There%20hasn%27t%20really%20been%20a%20product%20yet%20that%20has%20bridged%20the%20gap%20between%20the%20IT%20side%20of%20your%20life%20and%20the%20consumer%20electronics%20side.%20We%20discuss%20our%20odds-on%20favorites%20to%20successfully%20mass%20market%20a%20computer%20for%20the%20Home%20Theater%20and%20%22Bridge%20the%20Gap%22%20from%20your%20computer%20to%20your%20TV.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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

<p><a href="http://media.libsyn.com/media/hdtvpodcast/HDTV-2008-01-22.mp3">Listen Now - mp3</a><br />
<a href="http://feeds.feedburner.com/hdtvpodcast">RSS</a><br />
<a href="http://www.htguys.com">Website</a><br />
<br></p>

<p>We witnessed the death of CRT a few years ago when we searched high and low all over the CES show floor for somebody showing anything resembling a tube TV and came up empty.  This year, if you don't count the Texas Instruments DLP booth, we saw only three rear projection sets at the entire show.  Sony has announced their exiting the market because it wasn't profitable.  By not announcing anything new, JVC has all but announced that they won't be in the game for very much longer.  Samsung announced a hand full of new models and Mitsubishi is is touting their new laser based unit that should be available before the end of the year.   So for today's show we give you some tips that will help you decide if buying a Rear Projection TV is right for you.<br />
 <br />
Also,<br />
Until now the concept of a media center PC, or a computer in your home theater has largely remained in the realm of the IT expert or extreme enthusiast.  There hasn't really been a product yet that has bridged the gap between the IT side of your life and the consumer electronics side. We discuss our odds-on favorites to successfully mass market a computer for the Home Theater and "Bridge the Gap" from your computer to your TV. <br />
<strong><br />
The Death of Rear Projection TVs</strong><br />
So it's down to two: Samsung and Mitsubishi.  Both have always been very solid selections in the micro-display market, so it's not like we're left with two bad choices.  But what is a consumer to do?  Right now, if you're looking to buy a new HDTV, do you pick up a big rear projection unit, even though you know the technology will be extinct by the end of the decade, or do you go a little smaller and buy a plasma or flat LCD to make sure you have something that will be around for a while longer.  Or perhaps, you do the unthinkable and you decide to just wait it out to let the dust settle.  Maybe if it's your second HDTV that's OK, but if you aren't enjoying HDTV, you absolutely must buy something.  We recommend:</p>

<p>1. Consider your budget.  Know how much you can spend.  Use it all.<br />
2. Consider the room.  Do you have the extra depth you need for a rear projection, or is wall amounting a big deal?  How big should the screen itself be?</p>

<p>Those two questions should make the decision on which TV is right for you.  For example, if your budget is around $1500 you're probably looking at a <a href="http://www.htguys.com/shop.php?id=B000N53T82">61" rear projection</a> or a <a href="http://www.htguys.com/shop.php?id=B000O5TFRK">50" plasma</a> (42" if you want to step up to a <a href="http://www.htguys.com/shop.php?id=B000RQ5K2A">Kuro</a>), and <a href="http://www.htguys.com/shop.php?id=B000UN3VT4">42" LCD</a>.  If your budget is $2500 you can scale up to a <a href="http://www.htguys.com/shop.php?id=B000RYUWX4">73" Mitsubishi DLP</a>, get a <a href="http://www.htguys.com/shop.php?id=B000M2P3NU">60" plasma</a> or a <a href="http://www.htguys.com/shop.php?id=B000UN914I">52" LCD</a>.</p>

<p>In our minds, the rear projection sets are still great TVs.  Between the two of us, we own three, and will be getting really good use out of them for years - even after they stop being sold at our store.  While there may be a bunch of consumers who bought early CRT and RP LCDHDTVs who wish they would have waited a little while longer, the technology is far enough along now that you should be able to confidently purchase a micro display TV without any remorse.</p>

<p>Of course, a week or two after we say this one of the remaining companies will probably announce their intention to exit the market, but that's just how it goes some times...<br />
 <br />
 <strong><br />
Bridging the Gap from your Computer to your TV</strong><br />
We've got our eye on a few products or technologies that might be able to go mass market with this concept.  Here are our top 4, moving up in likelihood.</p>

<p>4. <a href="http://sagetv.com/hd_extender.html">SageTV HD Media Extender</a><br />
We've used SageTV in the past and found it to be a great interface for media center functionality on a PC.  The interface is well done, it has an open an active community and can really do a lot of great things.  The media extender has been around for a while, but the HD version is a recent addition.  It provides the same Sage TV front end in your Home Theater or any room in the house while allowing you to run SageTV on a PC in your office or a closet somewhere.  At $199 it's a great price and makes good sense.  It will playback just about anything the SageTV software can handle, including high definition up to 1080p.  The uphill battle for SageTV will be convincing the masses that setting up a media center PC is easy and something they want to do.  It's still largely an IT based market, so they have their work cut out for them before it goes to grandmas house.</p>

<p>3. <a href="http://www.popcornhour.com/onlinestore/">Popcorn Hour A-100</a><br />
The Popcorn Hour A-100, or Networked Media Tank, allows you to "stream or playback your digital media content from a variety of sources, such as your PC, NAS, digital camera, USB mass storage devices (Flash drive, HDD, DVD drive), internal HDD and even directly from the Internet via the Media Service Portal."  It is very similar to other devices on the market, like the stuff from <a href="http://www.mvixusa.com/">Mvix</a> that we've reviewed in the past.  The big difference by all accounts with the Popcorn Hour is that the user interface is actually fairly straight forward and may be, in fact, kitchen ready.  We've requested a demo unit but are yet to hear back.  It only costs $179 (without an internal hard drive), so it's fairly cost effective.  It works as a front end for any media you may have on your office computer and supports pretty much every format imaginable, all the way up to 1080p.  It does take some know-how to get it set up, but doesn't require any specific software on your computer, so it might just do the trick.  Since most people we talk to haven't heard of it before, we guessing they have their marketing work cut out for them.</p>

<p>2. <a href="http://www.apple.com/appletv/">Apple TV</a><br />
So if there's one thing Apple knows how to do it's put together a great user experience.  By dropping the price of the Apple TV to $229 and adding some new functionality, they may have a device that can finally find a home in your family room.  The Apple TV no longer requires a computer, but can download and stream it's own content as a stand alone device.  This is cool, but odds are all your content is somewhere on the network already, or you'll want a couple Apple TV units in various rooms, so it would need to be.  As an Apple product it isn't quite as versatile, file format wise, as the other options on the list, but the user experience is perfect, so it should be grandma approved.  If Apple can convince consumers that their TV device is a natural extension of the iPod (maybe even add an iPod dock, duh), they could sell a ton of them.  If it is perceived as an extension to the Mac product line, they'll be fighting the same uphill battle they are in selling computers.  But they certainly have the market presence and the money to make something happen.</p>

<p>1. <a href="http://www.microsoft.com/windows/products/winfamily/mediacenterextender/default.mspx">Windows Media Center Extenders</a><br />
Going on the assumption that millions of new Vista computers will be sold this year - probably on the order of 15 to 20 times more than their Mac counterparts, the Media Center Extender devices have to be the favorite to make an impression on the consumer electronics market.  Microsoft has tried to convince everyone to put a computer in the family room, but it never worked.  Now they've built that functionality into Vista for your office PC and will sell you an inexpensive extender to allow you to enjoy all the HTPC goodness in your home theater, without needing an actual computer there.  This seems like it might work.  If they pull a play out of Sony's playbook and advertise that you already have all this great functionality in your office (just like the PS3 already has a Blu-ray player), all you need is this little extender to enjoy it on your massive HDTV.  Seems pretty simple.  And to steal another similarity from a different CE war, Apple is the only company making and promoting the Apple TV.  Microsoft has their own XBox 360 and devices from the likes of HP, Samsung, D-link, and Linksys.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>The HT Guys</b>, <b>January 20, 2008 08:37 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
		<td id="left"><!-- Comments -->
			<div><?
				$sql = "
				SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
				FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
				WHERE entry_id = 910
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
					AND entry_id <> 910
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
	<script src="http://feeds.feedburner.com/~s/hdtv?i=http://www.hdtvmagazine.com/podcast/2008/01/hdtv_and_home_theater_podcast_243_-_the_death_of_rear_projection_tvs_bridging_the_gap_from_your_computer_to_your_tv.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
