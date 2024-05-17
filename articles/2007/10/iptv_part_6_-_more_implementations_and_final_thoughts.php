<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 727";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Rodolfo La Maestra'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 727 AND placement_is_primary = 1";
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
	<meta name="keywords" content="iptv part, new jersey, verizon fios, internet video, iptv stb, iptv, IPTV, Verizon, verizon, vod, VOD, content, service, part, video, Internet, tavi, sony, internet, Sony, Part, cable, ces, TAVI, new" />
	<meta name="description" content="This is the concluding article in the IPTV series of articles and covers specific IPTV implementations by Sony, TAVI 030, UTStarcom, Xbox 360, Verizon, and a smattering of other worldwide solutions and final thoughts on the big picture." />
	<title>HDTV Magazine Articles - IPTV Part 6 - More Implementations and Final Thoughts</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/iptv_part_6_-_more_implementations_and_final_thoughts';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('IPTV Part 6 - More Implementations and Final Thoughts'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Rodolfo La Maestra" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">IPTV Part 6 - More Implementations and Final Thoughts</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Rodolfo La Maestra</b><br />
				<?=$author_title?>
				Posted on <b>October 23, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php&amp;phase=2&amp;title=IPTV%20Part%206%20-%20More%20Implementations%20and%20Final%20Thoughts&amp;bodytext=This%20is%20the%20concluding%20article%20in%20the%20IPTV%20series%20of%20articles%20and%20covers%20specific%20IPTV%20implementations%20by%20Sony%2C%20TAVI%20030%2C%20UTStarcom%2C%20Xbox%20360%2C%20Verizon%2C%20and%20a%20smattering%20of%20other%20worldwide%20solutions%20and%20final%20thoughts%20on%20the%20big%20picture.&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<div class="editorial">The following article is the latest in the IPTV series by Rodolfo La Maestra. Other articles in this series are as follows:
<ul>
<li><a href="/articles/2007/09/iptv_part_1_-_read_the_fine_print.php">IPTV Part 1 - Read the Fine Print</a></li>
<li><a href="/articles/2007/09/iptv_part_2_-_the_groups_forums_and_statistics.php">IPTV Part 2 - The Groups, Forums and Statistics</a></li>
<li><a href="/articles/2007/10/iptv_part_3_-_the_methods_and_a_working_technology.php">IPTV Part 3 - The Methods and a Working Technology</a></li>
<li><a href="/articles/2007/10/iptv_part_4_-_the_good_the_bad_and_the_ugly.php">IPTV Part 4 - The Good, the Bad and the Ugly</a></li>
<li><a href="/articles/2007/10/iptv_part_5_-_additional_implementations.php">IPTV Part 5 - Additional Implementations</a></li>
</ul></div>
<br />
<img src="/images/articles/sony-internet-tv.jpg" alt="Sony Internet TV System" align="right" /><u>Sony Internet TV System</u>

<p>Sony announced a new IPTV product at CES 2007, the Bravia Internet Video Link.</p>

<p>The module would seamlessly have Sony's new BRAVIA LCD panels receive Internet video streaming content, including HD, from providers like AOL and Yahoo! which portals support the Video Link.</p>

<p>The content is received without connecting the TV to a PC.</p>

<p>The module was planned to be available in the summer of 2007 and connects to the Bravia TV set with an HDMI cable and to a high-speed data modem with an Ethernet cable.</p>

<p>"The service itself will be free to consumers, as the ad-supported content should benefit from the exposure", said Nick Colsey, Sony's director of product planning for televisions.</p>

<p>At CES Sony demonstrated the module receiving streaming HD content, but Sony commented that "broadband speeds would have to improve significantly before HD over the Internet becomes reality, the appeal of the Internet Video module is based more on convenience than image resolution, it's not just quality, it's about having access".</p>

<p><br />
<u>TAVI 030</u></p>

<p>A surprising entry at CES 2007, and also an innovation award winner, was the TAVI 030 portable IPTV STB. The TAVI 030 is the follow-up to the TAVI 020 model, which brought little fanfare at CES 2006. This year TAVI is including IPTV into this portable solution.</p>

<p>Users can dock the TAVI at home, receive and record IPTV (and RSS delivered content), watch it at home, or simply undock the unit and watch it on the go. The 030 sports a 30GB (or 60GB) hard drive encased in a piano-black clamshell design which can output content at 720p using component connections and 5.1 audio using Toslink. <a href="/cgi-bin/ntlinktrack.cgi?http://www.tavi.com/" target="_blank">www.tavi.com</a></p>

<p><br />
<u>UTStarcom</u></p>

<p>RollinStream Media Console for IPTV<br />
IPTV STB with UWB wireless</p>

<p><img src="/images/products/utstarcom-media-console.jpg" alt="UTStarCom Media Console" align="left" />UTStarcom http://www.UTStar.com/ (IPTV)<br />
and Tzero Technologies http://www.tzerotech.com/site/ (UWB)<br />
as partners introduced their solution of IPTV STBs at the ITU Telecom World 2006 show in Hong Kong.</p>

<p>The STB is capable of over 500 MHz of bandwidth, suitable for simultaneously transmit several streams of uncompressed HD video.</p>

<p>Tzero's UWB is WiMedia Alliance based and is compliant with other WiMedia-compliant devices.</p>

<p><br />
<u>Verizon</u></p>

<p>Verizon currently offers unicast VOD from a library of about 3,000 titles through its Verizon FiOS TV service.</p>

<p>Verizon calls the service "narrowcast IP environment", and is expected to supply FiOS programming to about 1.8 million households by the end of this year.</p>

<p>FiOS TV was quoted as offering an average of 450 linear broadcast channels to the market, 25 of those are in HD. More HD programming is planned in the near future.</p>

<p>Verizon FiOS TV service was also planned for 316 communities in New Jersey, about 2.1 million households equivalent to 70% of the homes in NJ, according to Verizon.</p>

<p>Verizon FiOS TV service was also offered in Massachusetts (Lexington, Tyngsboro and West Newbury) and Delaware (Kent, New Castle, Sussex counties, Bellefonte, Delaware City, Newark, Odessa and Townsend).</p>

<p>"We will soon serve more communities in New Jersey than any of the current cable-TV operators have served in the past three-and-a-half decades," declared Dennis Bone, president of Verizon New Jersey.</p>

<p><br />
<u>Analysis of the Verizon Alternative</u></p>

<p>I contacted Verizon's Providence technical office to obtain details about the system:</p>

<ul><li>There could be up to 7 HD STBs tuning in parallel at the home,
</li><li>The live HDTV service is not running over IP but over a separate coax,
</li><li>VOD runs over IP using a parallel coax connected to a router inside the house,
</li><li>Each VOD request is channeled thru the router, which is connected to the Fiber Optic box installed outside the house,</li><li>The VOD movie request is redirected to the central hub at Verizon,</li><li>The hub downloads the requested movie into a server dedicated for that purpose (for each house I was told), the download takes about 7-10 minutes,</li><li>The video can be viewed instantly and while the movie is being downloaded to the server; Verizon said that the starting is much faster than cable VOD.</li><li>Additional parallel VOD movie requests from other STBs "could" induce image speed delays during the first 7-10 minutes of the Hub/server download, so it is recommended for the additional VOD requests to wait that time for seamless viewing.</li><li>The home network for both TV and VOD IPTV is usually based on coax.</li><li>The compression algorithm used is MPEG-4,</li><li>The system uses Moxi, and they said they were working in newer and better software, to be available soon.</li></ul>

<p><br />
<u>Worldwide IPTV solutions</u></p>

<p>As with every year at CES, 2007 was no exception in having the International IPTV booths showing off their latest hardware solution available today throughout Europe, Japan, and China. These companies offer a variety of STBs, which can play VOD, record streaming content in both SD and HD using the existing network infrastructures in many countries such a Sweden. In general, these solution providers chuckle over how US Television Service providers "squeeze the life" out of their customers through bandwidth caps and compression games.</p>

<p><br />
<u>Xbox 360 IPTV</u></p>

<p>At CES 2007 Microsoft announced that their Xbox 360 would be software upgraded to include the Microsoft TV IPTV Edition feature by the end of 2007, a feature that will enable the console to become an IPTV STB allowing users to voice chat using a headset while watching television, "texting", and voice messaging. These features would all be delivered while enabling fast channel changes, viewing and recording content in SD and HD.</p>

<p><br />
<u>Other Flavors of IPTV</u></p>

<p>Market trends in the USA and Canada also point to several small IPTV offerings, which provide niche programming as streamed Internet content or downloadable through RSS feeds. A few of these lesser known IPTV sources are gaining market share by being available through major video-cast sources such as iTunes, the TiVo RSS reader, and many other RSS aggregators.</p>

<p>Some of these IPTV offerings even consider themselves to be startup networks, such as <a href="/cgi-bin/ntlinktrack.cgi?http://www.Revision3.com/" target="_blank" >www.Revision3.com</a>, which in Internet terms, is a great success commanding audiences as large as 100k or greater. Not surprisingly, large corporate empires such as Sony, Dolby, HD DVD, Microsoft, and Godaddy are seeing this trend and providing ad-sponsored revenue.</p>

<p>Other major players in this arena include Ziff-Davis publishing, which produces high-quality weekly shows such as DL.TV and Crankygeeks.com.</p>

<p><br />
<B>Final Thoughts</B></p>

<p>Just looking at two cases, the contrast between Verizon's IP implementation and the Sasktel described in Part 5 highlights how IPTV could differ to a subscriber depending on the bandwidth and wiring available and how the service was implemented by the IPTV provider. One example of this is the support for parallel HD viewing.</p>

<p>On the other hand, although a cable company or satellite service also have their traditional limitations of cable bandwidth or number of birds/transponders that limit their growth, they provide a subscriber with a model of consistent or close-to-consistent expectations on channel line up, multiple HDTV services, VOD, etc. regardless of the location. As with many consumer electronics, choose the solution that fits you best.</p>

<p>This is the last article in the IPTV series, I hope I have opened the subject sufficiently to make you think and to facilitate your research when you are about to jump into HD IPTV. And remember, all bits are not created equal, especially in the entertainment industry.</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Rodolfo La Maestra</b>, <b>October 23, 2007 07:06 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 727
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
 				AND entry_id <> 727
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/10/iptv_part_6_-_more_implementations_and_final_thoughts.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
