<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 109";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 109 AND placement_is_primary = 1";
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
	<meta name="keywords" content="content protection, copy protection, honest people, conditional access, encoding rules, content, protection, HDTV, copy, key, hdtv, data, honest, encryption, scrambling, cable, access, may, receiver, technology, recording, used, algorithm, conditional, digital" />
	<meta name="description" content="In April 1803, President Thomas Jefferson gave Meiwether Lewis (of the Lewis and Clark Expedition fame) a rather sophisticated key-based cipher table. This table was to be used to encrypt messages intended for the President in Washington if those messages would be sent via a foreign carrier, such as a foreign ship, when the expedition reached the Pacific Ocean. But, alas, the cipher table was never used as no ships came while the expedition was camped there. The point of this anecdote is that the concept of encrypted messages for security and content protection reasons is not new. In fact, language encryption has been around as long as man has been literate (about 10,000 years). It has been surmised that encoding words and speech is one of the reasons different languages developed. May be, but one thing is common with all of the various encrypting schemes through the ages - they all have been broken, no matter how sophisticated.
" />
	<title>HDTV Magazine Articles - Ed's View - Keeping Honest People Honest</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_keeping_honest_people_honest';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s View - Keeping Honest People Honest'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's View - Keeping Honest People Honest</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>June 22, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php&amp;phase=2&amp;title=Ed%27s%20View%20-%20Keeping%20Honest%20People%20Honest&amp;bodytext=In%20April%201803%2C%20President%20Thomas%20Jefferson%20gave%20Meiwether%20Lewis%20%28of%20the%20Lewis%20and%20Clark%20Expedition%20fame%29%20a%20rather%20sophisticated%20key-based%20cipher%20table.%20This%20table%20was%20to%20be%20used%20to%20encrypt%20messages%20intended%20for%20the%20President%20in%20Washington%20if%20those%20messages%20would%20be%20sent%20via%20a%20foreign%20carrier%2C%20such%20as%20a%20foreign%20ship%2C%20when%20the%20expedition%20reached%20the%20Pacific%20Ocean.%20But%2C%20alas%2C%20the%20cipher%20table%20was%20never%20used%20as%20no%20ships%20came%20while%20the%20expedition%20was%20camped%20there.%20The%20point%20of%20this%20anecdote%20is%20that%20the%20concept%20of%20encrypted%20messages%20for%20security%20and%20content%20protection%20reasons%20is%20not%20new.%20In%20fact%2C%20language%20encryption%20has%20been%20around%20as%20long%20as%20man%20has%20been%20literate%20%28about%2010%2C000%20years%29.%20It%20has%20been%20surmised%20that%20encoding%20words%20and%20speech%20is%20one%20of%20the%20reasons%20different%20languages%20developed.%20May%20be%2C%20but%20one%20thing%20is%20common%20with%20all%20of%20the%20various%20encrypting%20schemes%20through%20the%20ages%20-%20they%20all%20have%20been%20broken%2C%20no%20matter%20how%20sophisticated.%0A&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p>In April 1803, President Thomas Jefferson gave Meriwether Lewis (of the Lewis and Clark Expedition fame) a rather sophisticated key-based cipher table. This table was to be used to encrypt messages intended for the President in Washington if those messages would be sent via a foreign carrier, such as a foreign ship, when the expedition reached the Pacific Ocean. But, alas, the cipher table was never used as no ships came while the expedition was camped there. The point of this anecdote is that the concept of encrypted messages for security and content protection reasons is not new. In fact, language encryption has been around as long as man has been literate (about 10,000 years). It has been surmised that encoding words and speech is one of the reasons different languages developed. May be, but one thing is common with all of the various encrypting schemes through the ages - they all have been broken, no matter how sophisticated.</p>

<p>Today's encryption systems are unbelievably complex with multiple layers of protection. But no matter what is devised, there is some teenager in West Pump Handle, Iowa, who will break the code - simply because it is there to be broken. "But," you muse, "that would take a supercomputer."  Yes, that is correct. However, those will be available next year from Circuit City, Best Buy, Dell, et al. The point is: about the only thing even the most complex encryption systems can do is to keep honest people honest.</p>

<p>Carrying this logic to its next level, since encryption is used to enable content protection, and since it will be compromised, there is really no ultimate technical solution to the dilemma confounding content protection. But technology can make content protection very difficult to compromise, easier to enforce, and, therefore, a very useful tool in advancing the entertainment and information value of HDTV. Yes, my view is that content protection technology is a good thing, indeed a very necessary element for insuring the economic viability of HDTV.</p>

<p>I will expound on this further, but first let's review, at a high level, some of the salient concepts of modern content protection and associated technology:</p>

<p><em>Content Protection</em> is the overall term given to the process of protecting content from being obtained in any usable form by non-authorized receivers.</p>

<p><em>Encryption</em> is a means to achieve content protection. Encryption involves encoding the sensitive content in some manner known only by the sender and receiver.</p>

<p><em>Conditional Access</em> is the means used to provide the receiver accessibility to the encrypted content he is authorized or entitled to receive. Electronically, at the receiver, the content protection mechanism is analogous to a sophisticated switch that allows the passage of the signal to the decrypting circuitry.  Usually, the "switch" is triggered by a received code tied to the receiver's identification and/or serial number. These codes are called Entitlement Control Messages or ECM's.  </p>

<p>Conditional access is also used to authorize various tiers of services to which the viewer has subscribed. Think of conditional access as an electronic "truck roll" in the early analog cable TV context. After the customer subscribed to the cable service, the cable technician would connect the cable to provide basic service.  The technician my also have removed "traps" at the cable terminal (usually located on a nearby power pole) to allow the reception of higher tiers of service, such as HBO etc. In many instances, this "manual" process continues to be the way conditional Cable provides conditional access, particularly in rural communities. </p>

<p>The conditional access ECM's may or may not be sent along with, or in the same frequency band, as the encrypted content. They also can be transmitted at varying times and in addition with other digital "housekeeping" data.  In the digital context the ECM"s, along with this housekeeping data are usually packaged in a separate "Service Channel."</p>

<p><em>Scrambling </em>is usually the method used to encrypt the digital content. As the name implies, scrambling involves rearrangement of the data in a manner that makes it unintelligible until it is de-scrambled at the receiving end. The scrambling algorithm, or cipher, can be very complex, and may dynamically change to provide added security. But added complexity consumes added bandwidth. Scrambling algorithms, along with the service data, may consume as much as 1/3rd or more of the channel bandwidth. However, from the code-breakers' standpoint, the scrambling algorithm itself is the easiest to break. What is difficult is decoding the key. Without the key mechanism, many honest people would become dishonest.</p>

<p><em>The key or keyword</em> (a.k.a. "the secret") is the most important and critical part of any modern encryption system. One may think of the scrambling algorithm as the "how" and the key as the "what."  </p>

<p><em><strong>Here is a very simple example:</strong></em></p>

<p>Suppose the word "CAT" is scrambled as "DBU." In this case the scrambling algorithm is: Move each letter forward in the alphabet a specific amount. The key is "one."  Therefore, each letter is moved forward one alphabetical position.</p>

<p>The key can be an algorithm itself and can be changed at varying times - once a minute, once a second, etc. However, at the heart of the key algorithm is a "kernel," which is usually a number of absolute value. The kernel can also be changed periodically by what is called a "pseudorandom number table." Regardless of how it is done, both the sender and receiver must know the key. If the key is compromised, the encryption system is comparatively easily broken.  There are numerous military historical instances of cipher keys being stolen, allowing one combatant to successfully decipher messages sent by the other.</p>

<p>But, in the very recent years, key management has become so complex that it is extremely difficult and costly for the casual hacker to break the encrypting code. They, of course, will be broken, but most likely by those with truly dishonest intensions. </p>

<p><em>Copy Protection</em> is the mechanism that allows, disallows or otherwise manages the copying of content on a suitable copying media once the receiver has been authorized access to the content. Copy protection, and therefore copyright protection, is one of the most contentious issues surrounding HDTV. Generally, copy protection works by disallowing or limiting the operation of the recording device. It is insufficient to simply disallow only the recording of decrypted (in-the-clear) content, because once the material is recorded, even though encrypted, it can be examined bit by bit by the code breaker, eventually being broken.  </p>

<p>Mechanisms are being developed, called "Encoding Rules," that will allow the management of recording rights. These Encoding Rules involve codes that allow varying levels of customer recording access, such as "copy never," "copy once," or "copy many times." To signal the receiver that the content is transmitted with encoding rules, a small bit of data called the "broadcast flag" is sent along with the digital program stream. The Encoding Rules concept represents a workable compromise between copy protection and established recording rights.</p>

<p>It is interesting to note that in the several FCC Report and Orders establishing the DTV transition, the issue of copy protection was not addressed. Only after the ability to create an infinite number of perfect replications of the digital material was realized did copy protection become an issue.</p>

<p><em>Link (Interface) Protection </em>refers to the protection of content coupled from a host (e.g. Cable Box) to a client (e.g. Display). It is necessary to protect these links to prevent interception of the in-the-clear data that has been de-scrambled by the host. Two examples of this technology are currently being employed. These are the DTV Link (encrypted IEEE 1594) and the High Definition Multimedia Interface (HDMI). Both use scrambling algorithms with "handshaking" scheme. The handshaking process involves the host and client ends of the link communicating with each other before the data is transferred across the link. This assures the client is entitled to receive data from the host. Handshaking communication virtually eliminates a breach do to the so-called "man-in-the-middle" attack, which is an attempt to intercept the link data stream.</p>

<p><em>Watermarking </em>refers to codes, visible and/or invisible, added to the video program material itself, analogous to network identifier "bugs" we see in the lower corners of the program display. The watermarking codes assist in tracking the source of the displayed material from its origination through the recording device. The recording device itself also may "stamp" a watermark code to the video. Watermarking greatly aids enforcement of copy protection rules by tracking the source of any illegal recordings.</p>

<p>Successful content and copy protection mechanisms are absolutely necessary for the advancement of HDTV. The economic model of television broadcasting is changing significantly. No longer can producers depend on advertising to support the added costs of HDTV production values, quality talent and program creation. Advertising income is becoming increasingly fragmented due to the "narrow-casting" phenomenon of multiple networks.  </p>

<p>Add this to the fact that the movie industry is now deriving more than 50% of its revenues from DVD's (plus video tape and PPV), and it becomes absolutely necessary for our primary creative industry to protect these revenue streams. And as HDTV receivers and HDTV DVD's become increasingly more popular, truly emulating the theater experience, the content providers' revenue will become even more dependent on the prerecorded video streams.</p>

<p>It will not be long until we will be able to download and/or stream HDTV content. This will not be possible without content protection.  Content protecting is the mechanism that will allow us to receive increasingly more diverse, high quality HDTV content. We must do all we can to embrace and support this technology and keep honest people honest. If we do, HDTV will only get better.</p>

<p>Ed<br />
___________________<br />
About Ed Milbourn<br />
After graduating from Purdue University with degrees in Electrical Engineering and Industrial Education in 1961 and 1963 respectively, Ed Milbourn joined the RCA Home Entertainment Division in 1963. During his thirty-eight year career with RCA (later GE and Thomson multimedia), Mr. Milbourn held the positions of Field Service Engineer, Manager of Technical Training and Manager of Sales Training. In 1987, he joined Thomson's Product Management group as Manager of Advanced Television Systems Planning, with responsibilities including Digital Television and High Definition Television Product Management. Mr. Milbourn retired from Thomson multimedia in December 2001, and is now a Consumer Electronics Industry consultant. </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>June 22, 2005 05:36 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 109
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
 				AND entry_id <> 109
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Ed Milbourn'
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
 				<h2>About Ed Milbourn</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2005/06/eds_view_-_keeping_honest_people_honest.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
