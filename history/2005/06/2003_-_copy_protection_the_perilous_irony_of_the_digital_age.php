<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 125";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 125 AND placement_is_primary = 1";
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
	<meta name="keywords" content="content industry, intellectual property, reasonable alternative, honest markets, honest consumer, industry, honest, technology, content, reasonable, music, consumer, dale, property, digital, Dale, copyright, think, movie, get, protection, our, burger, long, intellectual" />
	<meta name="description" content="I wrote an editorial not long ago suggesting that we take a new look at copy protection by re-examining the possibility for adjusting human behavior rather than placing an infinite set of temporary fixes on the problem. In spite of..." />
	<title>HDTV Magazine Archive &amp; History - 2003 - Copy Protection, The Perilous Irony of the Digital Age</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/2003_-_copy_protection_the_perilous_irony_of_the_digital_age';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('2003 - Copy Protection, The Perilous Irony of the Digital Age'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">2003 - Copy Protection, The Perilous Irony of the Digital Age</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php&amp;phase=2&amp;title=2003%20-%20Copy%20Protection%2C%20The%20Perilous%20Irony%20of%20the%20Digital%20Age&amp;bodytext=I%20wrote%20an%20editorial%20not%20long%20ago%20suggesting%20that%20we%20take%20a%20new%20look%20at%20copy%20protection%20by%20re-examining%20the%20possibility%20for%20adjusting%20human%20behavior%20rather%20than%20placing%20an%20infinite%20set%20of%20temporary%20fixes%20on%20the%20problem.%20In%20spite%20of...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>I wrote an editorial not long ago suggesting that we take a new look at copy protection by re-examining the possibility for adjusting human behavior rather than placing an infinite set of temporary fixes on the problem. In spite of hearty guffaws from cynics who think I give humanity more credit then any copyright holder at risk should ever do, I postulated that in the digital world we had best not become the termites that chew up our own digital foundations. My point was not to suggest that we suddenly put blind faith in the present day mind-set and conrresponding behavior but to spark a debate over how we might achieve a reconstructed model behavior that permits a true flowering of the digital age. If we are not yet on the balance beam, I ask myself, how can we get on it? For without any question the way that do we behave will dictate by way of a reaction the entanglements we must endure in hardware and software. That will be imposed upon us to compensate for our lack of apprecation and respect for how the economies work. The idea that whatever is accessible is ours to take without compensation to its developers and owners will stiffle innovation and investment and finally lay waste to the notion of private property. But you say, "How do you expect human nature to adopt a taboo on stealing intellectural property when I can't even get my dog to heel?" </p>

<p>Jim Burger, a noted and well respected attorney in Washington D.C. with a long list of achievements, has picked up the gauntlet that I have tossed before you and provided us with his view which, to my surprise and delight, considers a bit of the human factor in this labyrinth of copyright protection.  </em></p>

<p>Dale Cripps</p>

<p>_____________________________________________</p>

<p><strong>Introduction</strong></p>

<p>Dale and I have exchanged many notes debating DTV issues. Dale and I have had honest disagreements about DTV. Dale's recent editorial however, contains much I can agree with. I also wholeheartedly agree with Jerry Rutledge's response. I hope I have something to contribute to this discussion since we all appear to be on the same side of this issue. First, my usual caveat - these are my own opinions and not necessarily those of my firm or my clients. Second, I believe that intellectual property is our cultural cornerstone and an extremely important economic driver. Indeed, the $800 billion U.S. information technology industry's primary product is intellectual property.</p>

<p>Having said that, I had several recommendations for the content industry. The first, from Doug Adams Hitchhiker's Guide to the Galaxy is "Don't' Panic." Since at least the 1908 introduction of the player piano, the content industry has wrung its hands about every new technology, just as they are doing today with DTV. This was true with introduction of the record player, the radio, the television, the cassette recorder, and VHS recorders, and so on. Each technology turned into a gold mine for the content industry.</p>

<p>The Motion Picture Association of America (MPAA) gets upset when people point to its President's (Jack Valenti) statement to Congress about the VHS in 1983. He said, the VHS "is to the American film producer and the American public as the Boston Strangler is to the woman alone." Pre-recorded VHS movies went on to be one of Hollywood's biggest money earners. Indeed, with the smashing success of DVD, home video is the most profitable part of their business. My good friends in the movie industry point out that Jack was only talking about the record function, not the playback function. Apart from the fact that I can't find such a reference, it was the combination of both that made the VHS so popular and in most of our homes, creating the market for home video.</p>

<p>So, DTV and the digital recorders coming on the market are not a threat but a challenge and an opportunity. As will be the Internet. Movie sharing over the Internet is not a major economic threat today, the time to download a high-quality digital movie is just simply too long. Also, the reliability of the connection is a problem with big files. (I've been trying all week to download a 53 megabyte Canadian government document on my fast corporate network without success. Image trying to download a 2-gigabyte movie?) One hopes, of course, that over time the bandwidth problem will be solved. The question is whether the movie industry will be ready with an honest digital download market when that happens.</p>

<p><strong>The Four Step Plan</strong></p>

<p>I believe there are four steps to dealing with the problem as stated by the content industry. Here they are in order of importance and execution:</p>

<p><br />
* Create Honest Markets <br />
* Education <br />
* Enforcement <br />
* Technical Speed Bumps</p>

<p><br />
Dale's overlay of what I would call ethos -concerns about impact of any cop protection scheme on the consumer and the consumer's ethical standards - are deeply embedded in each of these steps. All are dependent upon honest consumers. Who would not seek to take content without compensating copyright holders as long as they had fair, attractive alternatives and understood what is at stake.</p>

<p><strong>Honest Markets</strong></p>

<p>Today, the download of digital music is widespread. It is difficult, and I would argue fruitless, to remonstrate consumers for trading music on peer-to-peer systems when they have no reasonable alternative. While creating a legitimate system that will appeal to the honest consumer is not easy work, it must be done. The music people are smart people, and the consumer electronics and computer industry would gladly help them create effective, reasonable online music markets. It just hasn't happened yet. One hopes the video industry is further ahead of the curve. Let's hope they will have the honest market ready long before bandwidth becomes a threat. Wouldn't it be nice if a box on top of your set (forget how the bits get there) enabled you to watch the movie you want to watch in "glorious" HD at 8:13 p.m. Saturday night when you and spouse have finished dinner and are ready to watch it? (Rather than the movie the Network wants you to watch at the time they want you to watch it.) (Don't worry Dale, it won't make your guide obsolete!)</p>

<p>The first step in protecting music and movies and rewarding the creators of the intellectual property, is the creation of honest markets. If video distributors could offer those services at reasonable prices, I think like VHS and DVD, they would make lots of money and satisfy honest consumer desires. This sets the stage for consumer education.</p>

<p><strong>Education</strong></p>

<p>I am gravely concerned we are raising a generation of kids with no respect for intellectual property, if they even know what it means. I blame the music industry for this. Earlier this year I was invited to speak about Napster to a group of high school leaders from around the country. I closed the door, said I wasn't from the copyright police. I asked how many routinely downloaded and burned CDs. Every hand flew up. I said, don't you think it’s wrong to take someone's intellectual property without paying for it? The answers I got rocked me, but unfortunately made sense.</p>

<p>They responded with, do you know what you are saying? I might hear a song on the radio station I like. By the way, they said, we know the record industry bribes the radio stations to shove the latest singing Barbie or boy band down our throats. If get it as you say I should, first I have to get my mom or dad to drive me to the mall. Then, assuming CD is there, I have to pay from $16 to $21 for the CD. I get it home and I listen to it. Many times, there's the one good song I wanted and 11-12 pieces of garbage. By the way, we know the record companies screw artists, so we're not taking money from them. Bands only make money touring. I think we've been overcharged long enough. No, I do not feel guilty, they said.</p>

<p>I went on to explain the history of copyright and how they actually owned copyrights. I told them how important copyright is to our culture and economy. Frankly, I think I couldn't convince them. I could not defend current music industry practices, nor give them a reasonable alternative.</p>

<p>Once the content industry provides reasonable alternatives, I am a firm believer in launching a major copyright education campaign. I believe that the computer industry would participate in that effort. (There are, however, a few signs that the music industry is working to make Press Play and Music Net into real alternatives. Today they are not.)</p>

<p><strong>Enforcement</strong></p>

<p>The owners should defend copyrights. I have, however, a difficult time cracking down on ordinary consumers (as opposed to commercial pirates) when we haven't offered an honest market alternative nor educated them. Given information and reasonable alternatives, enforcement will be unnecessary. But there will always be people that want something free and will not turn to legitimate reasonable alternatives. Therefore, I think that enforcement is an important, if third level, element.</p>

<p><strong>Technology "Speed Bumps"</strong></p>

<p>Once honest markets, education, and enforcement are in place, I do not object to the content industry putting technology in their content that helps remind honest consumers to be honest. First, however, it will have little effect if the honest consumer can't be directed to the reasonable alternative. Second, the content industry must sponsor and pay for the technology. Third, technology is not capable of being the first line of defense.</p>

<p>Hackers love hacking as much or more than Dale and many of you love HDTV. Challenging them with "effective" technology is like putting the cookie jar on a higher shelf. You know the kid will climb up and attempt to get the cookies. Thus, all such technology can do is remind the honest consumer that they are about to do something they shouldn't and for which they have a reasonable alternative. But, if the protection technology interferes with legitimate expectations (e.g., prevents you from time shifting a TV program), consumers will revolt and you will push them to illegitimate sources of content.</p>

<p><strong>Conclusion</strong></p>

<p>In the end, I am convinced as I think Dale is that consumers want to do the "right thing." Content owners need to step back, relax, and figure out how to make the new technology work for them. "Locking up" the content never has worked. But reasonable protection has worked when the consumer perceives value in the content.</p>

<p><strong>Jim Burger</strong></p>

<p><em>James Burger is a member of the law firm of Dow Lohnes specializing in representation of technology companies on intellectual property, communications and government policy matters. Mr. Burger joined the firm's Media, Information and Technologies group in January, 1997. Prior to that, Mr. Burger was a Senior Director in Apple Computer's Law Department. During the nine years he was at Apple, Mr. Burger had a variety of assignments, including representing Apple's the Advanced Technology Group, USA Field Sales organizations, and World-Wide Operations and Manufacturing, as well as General Counsel for Europe and Latin America and responsible for world wide government affairs. In addition, from 1991 until 1996, he was Chair of the Information Technology Industry Council's Proprietary Rights Committee. </em><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 03:14 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 125
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
 				AND entry_id <> 125
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/2003_-_copy_protection_the_perilous_irony_of_the_digital_age.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
