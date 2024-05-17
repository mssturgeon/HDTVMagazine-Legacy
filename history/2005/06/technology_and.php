<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 132";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 132 AND placement_is_primary = 1";
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
	<meta name="keywords" content="advanced television, grand alliance, digital hdtv, transition digital, hdtv system, digital, television, HDTV, hdtv, system, systems, transition, broadcasting, service, terrestrial, technology, CBS, cbs, analog, today, channel, transmission, broadcasters, Television, cable" />
	<meta name="description" content="Television broadcasting is technology-past, present, and future Every frame of every program and every syllable of every word broadcast is delivered through a vast complex of ever changing and ever improving technology, and it has always been thus. Today's debates..." />
	<title>HDTV Magazine Archive &amp; History - 1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc.</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc.'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1994 - Technology and the Future of Broadcasting by Dr. Joseph Flaherty, CBS, Inc.</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 26, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php&amp;phase=2&amp;title=1994%20-%20Technology%20and%20the%20Future%20of%20Broadcasting%20by%20Dr.%20Joseph%20Flaherty%2C%20CBS%2C%20Inc.&amp;bodytext=Television%20broadcasting%20is%20technology-past%2C%20present%2C%20and%20future%20Every%20frame%20of%20every%20program%20and%20every%20syllable%20of%20every%20word%20broadcast%20is%20delivered%20through%20a%20vast%20complex%20of%20ever%20changing%20and%20ever%20improving%20technology%2C%20and%20it%20has%20always%20been%20thus.%20Today%27s%20debates...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><strong>Television broadcasting is technology-past, present, and future</strong></p>

<p>Every frame of every program and every syllable of every word broadcast is delivered through a vast complex of ever changing and ever improving technology, and it has always been thus.</p>

<p>Today's debates on digital television and HDTV mirror those of television's earliest days. As the philosopher Santayana observed:</p>

<p>"Those who cannot remember the past are condemned to repeat it."</p>

<p>In 1929, just two years after the opening demonstration of television by the Bell Telephone laboratories on April 7, 1927, the first American book on television was published by Sheldon and Grisewood of New York University. In their concluding chapter on The Future of Television they wrote:</p>

<p>"The chief difficulty at present in that television requires a rather broad band of wave-lengths. Had television come ten years ago this would have presented no difficulty. As matters now stand, however, with a broadcast station crowded into every possible space (of spectrum), the introduction of television will of necessity crowd some of these out. In the meantime, the fact that there is no public demand for television magnifies this difficulty. If the public knew that it wanted television, then television would at least be given a hearing."</p>

<p>Further, in the Fall 1931 edition of Radio Design magazine the writer reports:</p>

<p>"As technical conditions exist now, it is comparatively easy to produce very fine television images, but exceedingly difficult to transmit them by radio. The air is simply crowded to suffocation."</p>

<p>We were at 2 MHz then, but the magazine goes on to report:</p>

<p>"The over-enthusiastic televisionists are making their big mistake in thinking that television will repeat the glamorous history of radio broadcasting, when every sign indicates that it will not and indeed cannot. Conditions now are altogether different from what they were ten years ago. Today we have a Federal Radio Commission, an aggravating patent situation, an overcrowded ether, an overabundance of radio factories, a lot of politicians with radio axes to grind, and, worst of all, a sophisticated buying element spoiled by high quality talking motion pictures. If not for the 'talkies' the present crude televisors might stand a slight chance of success. However, the 'talkies' have entirely erased this possibility. "</p>

<p>Aren't we hearing much of this today about digital HDTV?</p>

<p>You'll be interested to note that one glimmer of sense was provided in a hearing before the Federal Radio Commission by Mr. C. W. Horn, then Manager of the Westinghouse Electric and Manufacturing Company when he told the Commission that:</p>

<p>Engineers have developed all the great inventions and statements made on television other than by engineers are of little value."</p>

<p>How many of you believe that this could be said of many technologies today?</p>

<p>But television was a major changes and change inevitably finds skeptics, even among the most informed.</p>

<p>In 1865, Lord Kelvin, then President of the Royal Society, concluded:</p>

<p>"Heavier-than-air flying machines are impossible."</p>

<p>Today, the impossible doesn't take as long as it used to take, and technology sweeps across our world, daily changing the way we live, but changes involving new technologies frequently arrive to an incredulous audience. In the late 19th century, when electricity was finding its way into everyday life, the sign shown in Fig. 1, was prominently posted in public buildings to reassure a doubting public.</p>

<p>Our technological contributions to this new art notwithstanding, television broadcasting has not always been well received.</p>

<p>Frank Lloyd Wright called it:</p>

<p>"Chewing gum for the eyes."</p>

<p>And television's own humorist, Ernie Kovacs said:</p>

<p>"Television is called a medium because it's neither rare nor well done,"</p>

<p>Nevertheless, the inexorable march of technology advanced the quality, flexibility, and reliability of the television service in revolutionary steps. Mechanical scanning systems with the Nipkow disc gave way to electronic scanning at both the transmitter and receiver with the experiments of campbell-Swinton in 1911. Electronic television was born, and the 30 line image was dubbed "high definitions as has been every subsequent increase in scanning line numbers.</p>

<p>The first practical television service was the British Broadcasting Corporation's 405 line system which went into operation just before the start of World War II. America's 525 line monochrome service, developed by the RCA, was first publicly demonstrated at the 1939 New York World's Fair and went into commercial service after the war in 1947.</p>

<p>Explosive growth followed, and television replaced AM radio as the nations s prime entertainment and news medium.</p>

<p>Nationwide television distribution by coaxial cable and terrestrial microwave systems delivered network television to over 90% of the Country' s viewers.</p>

<p>Color came in 1954 with the adoption of the NTSC color system. This breakthrough was followed in 1356 with the development of video tape, a joint Ampex/CBS development. The 1960's saw the development of electronic video tape editing systems, the CMX off-line editing system, miniature video tape machines, portable cameras, and the hand-hold "CBS Minicam" color camera.</p>

<p>In 1971 these developments gave birth to the CBS-developed Electronic News Gathering system, or ENG, and filmed news came to an end worldwide. Modern news operations, including CNN, could not exist without ENG. Filmed news simply could not support such realtime worldwide news operations.</p>

<p>Orbiting satellites followed by geostationary satellites, replaced the terrestrial distribution networks and offered greater quality at reduced costs. All the US networks are distributed via satellite today, and incoming feeds from remote pick-up sites are transmitted via satellite or fiber line to the network centers.</p>

<p>In the early 1970's digital equipment began to appear to provide functions difficult or impossible to achieve in the analog domain. Digital time base correctors, frame synchronizers, graphic quality character generators, graphic paint boxes, etc. began to form digital islands in an all analog sea.</p>

<p>With the development of digital video tape machines these digital islands began to grow into digital continents, evaporating.</p>

<p>Of all the technological advances that have affected television, none is more fundamental or more far-reaching than the transition to digital techniques for all phases of the television process, In fact, the conversion to digital terrestrial transmission is the last link in the digital chain.</p>

<p>This transition to digital techniques will impact the entire installed base of the television industry. At the consumer level, there are one billion television sets in use worldwide with 200 million in North America.</p>

<p>98% of American homes have a TV; 85% have a VCR; 65% are connected to cable, or have a DBS service; and 33% have a computer. To support this installed base of consumer equipment, 25 million TV sets are sold each year in the US alone. The value of this market is $8.5 billion.</p>

<p>Digital techniques in communications are not, of course, new. In 1623, Sir Francis Bacon, in his treatise: "The Dignity and Advancement of Learning", he proposed to encode the alphabet by a binary Communications system.</p>

<p>He suggested that:</p>

<p>"Provided only that the matter included be five times less than that which includes it, without any condition or limitation, the alphabet can be resolved into two letters only, which by repetition and transposition through five places could represent all the other letters of the alphabet</p>

<p>With his five-bit "byte", he could compress and encode 92 different characters, or the letters of the alphabet.</p>

<p>He concluded:</p>

<p>"The contrivance shows a method of signifying and expressing one's mind to any distance by objects that are either audible or visible, provided only that the objects are but capable of two differences; e.g. fireworks, bells, or cannon."</p>

<p>Thus, today, while it is not done with fireworks, bells, or cannons, the dominant technology issue for terrestrial broadcasters over the next four to eight years is the total conversion from present analog NTSC broadcasting to digital advanced television (ATV) broadcasting, including wide screen (16:9) TV and high definition (HDTV).</p>

<p>CBS recognized the competitive challenge represented by the emergence of HDTV, and has been deeply involved in the technology of advanced television and HDTV ever since. In fact, CBS introduced HDTV into the US in 1981, and since 1989 our publicly expressed ATV goals have been:</p>

<p>1. To ensure that terrestrial broadcasters will be able to deliver a fully competitive digital ATV and HDTV service;</p>

<p>2. To provide sufficient spectrum for terrestrial broadcasters to effect the transition to digital transmission, replicating their present coverage area;</p>

<p>3. To preserve the value of existing TV receivers, and thus the existing TV audience, during the transition to digital television:</p>

<p>4. To provide technical headroom to ensure future competitive parity for terrestrial broadcasting as digital technologies improve.</p>

<p>The first and fourth goals are satisfied to the extent possible by the "Grand Alliance" ATV/HDTV system as recommended to the FCC by the Advisory Committee on Advanced Television Service (ACATS). The Hon. Richard E. Wiley was chairman of the ACATS, and I was the Chairman of the Planning Subcommittee and Co-chairman of the Technical Subgroup responsible for defining the system specifications, approving the system design, and recommending the standard,</p>

<p>The second and third goals were largely assured with the FCC plan to transition the nation to an improved HDTV service via a second 6 MHz simulcast channel during an estimated fifteen-year transition period. However, the debate raised by some broadcasters over using the HDTV channels for multi program "standards TV and/or for data broadcasting caused concern over the "free" assignment of the second transitional channel to existing broadcasters. This, plus the successful PCS spectrum auctions, led to the present Congressional debate over TV spectrum auctions. Today, the assignment of the digital transition channels is in doubt. Naturally, the loss of these transition channels would spell doom for terrestrial broadcasting as we have known it.</p>

<p>The importance of this digital transition for broadcasting, and thus, the importance of the digital channels can be seen in that virtually all other communications media are already digital systems or rapidly becoming so:</p>

<p>Telecommunications, telephone, FAX, and computers are a11 digital systems providing improved quality, reliability, economies, and ever finding new applications. </p>

<p><u>Recorded audio </u>is already digital via the compact disc, totally replacing the analog record.<br />
 <br />
<u>Home receivers</u>, home digital video discs, and home VCR' 9 will be digital in a few years. </p>

<p><u>Direct broadcasting satellites </u>in America were launched as digital services to take advantage of digital compression techniques to multiply their channel count and to enable wide screen HDTV transmission. The Group-W satellite operation in Singapore is an all-digital service.<br />
 <br />
Cable operators in America have completed their digital compression studies, and larger Cable systems are already converting to digital transmission to increase their channel count and to enable wide screen HDTV programming, </p>

<p>Fiber-hated television systems now being developed will also be able to deliver multi-channel high quality digital TV and HDTV programs to a cable-like customer base. </p>

<p>With the potential of over 2O0 digital channels with a wide screen HDTV capability, DBS, cable, and broad-band fiber distribution media have an important economic incentive to become digital delivery systems.</p>

<p>With increased cable, fiber, DBS and home video competition, traditional broadcasters will be under enormous pressure to maintain their competitive position in the landscape of 21st century television and to secure a place on the National Information Infrastructure (NII-) where there are no analog channels.</p>

<p>Only digital technology will provide competitive parity for broadcasters, and only the same digital technology will provide broadcasters the essential interoperability with the digital systems of the NII.</p>

<p>Analog NTSC television, as we know it today, will disappear as higher quality digital TV and HDTV capture a larger-and-larger share of the consumer market, and capture the market they will-cable, DBS, fiber, and home video will see to that. "DirecTV" has already made a substantial start.</p>

<p>In short, terrestrial broadcasters simply must make the transition to digital television, and the only way they can make this transition, with full quality TV and HDTV transmission potential, is to have a second 6 MHz television channel on which to operate the digital TV and HDTV service in parallel with the NTSC service during the analog-to-digital transition period.</p>

<p>To devise an HDTV standard for the US the FCC sought private sector advice and formed the FCC Advisory Committee on Advanced Television Service, or ACATS, in 1987 and charged it to study the problems of the terrestrial broadcasting of HDTV, to test proposed systems, and to make recommendations for a single terrestrial HDTV transmission standard.</p>

<p>System proposals peaked at twenty one, but by 1990 they had shrunk to only nine. Two of these were HDTV simulcast system", and they were both analog designs.</p>

<p>The FCC adopted a simulcast transition plan wherein each existing television station would be assigned a second 6 MHz channel for the digital TV and HDTV service. Following a transition period the NTSC service would be abandoned and the channel returned to the government for reuse.</p>

<p>Work on analog systems was in process, when, in 1990 the major change took place. On June 1, 1990 General Instrument proposed an all-digital HDTV system just four weeks before the ACUTE system submission deadline, and television would forever change, The digital era had begun and analog broadcasting was doomed.</p>

<p>Within nine months four digital HDTV systems had been proposed, and these systems were designed, built, and tested at the Advanced Television Test Center CATTY) in Alexandria, Virginia and at the Advanced Television Evaluation Laboratory in Canada.</p>

<p>While all the systems produced good HDTV pictures in a 6 MHz channel, none of the systems were judged to have performed Sufficiently well to be selected as the single standard at that time. The four digital system proponents began to examine the possibility of combining their systems into a single HDTV system proposal in what has come to be known as the Grand Alliance",</p>

<p>The "Grand Alliances was formed and announced on May 24, 1993 by the four digital HDTV system proponents - AT&T/Zenith, General Instrument, DSRC/Thomson/Philips, and MIT. The initial Grand Alliance technical proposal combined various parts of their previous four separate systems into a single all-digital HDTV transmission system.</p>

<p>A Technical subgroup chaired by Dr. Dorros of Bellcore and myself reviewed, modified, and approved the Grand Alliance system for construction and test.</p>

<p>The "Grand Alliance" system has the following parameters:</p>

<p>- The system supports dual scanning rates of 1080 active lines with 1920 pixels per-line interlace scanned at 59.94 and 60 fields/second and 720 active lines with 1280 pixels-per-line progressively scanned at 59.94 and 60 frames/second. Both scanning formats also operate in the progressive scanning mode at 30 and 24 frames/second.</p>

<p>- The system employs MPEG-2 video compression and transport systems.</p>

<p>- The system uses the Dolby AC-3, 384 Kb/8 audio system.</p>

<p>- The system uses the 8-VSB transmission system, originally developed by Zenith.</p>

<p>The system will support a hierarchy of scanning formats, as shown in Fig. 2, with full HDTV at the highest level and includes "standard TV" and multi- program compressed TV transmission as lower orders of the hierarchy. Based on studies by the consumer equipment industry, it is estimated that this additional flexibility will increase the cost of consumer HDTV receivers and VCRs by only 2% to 5%.</p>

<p>The private sector Advanced Television systems Committee (AC) has documented the full HDTV standard based on the "Grand Alliance" specifications as approved by the ACATS Technical Subgroup, and, as of this April 11, the standard was approved by the ATSC membership by an overwhelming majority. <br />
_______________________________________<br />
About Joseph Flaherty</p>

<p><em>Joseph Flaherty is senior vice president of technology at CBS. In this position, he advises CBS management on issues and strategies related to broadcast technology, and represents CBS nationally and internationally with major manufacturers and on government and industry committees and organizations. Flaherty joined CBS in 1957, and has directed the Engineering and Development Department since 1967—first as general manager, then, since 1977, as vice president and general manager. During his career, he has received many prestigious broadcast industry awards, including several Emmys for technical achievement; the David Sarnoff Gold Medal for progress in television engineering; the NAB Engineering Award; the Progress Medal of the SMPTE; and the International Montreux Achievement Gold Medal. Flaherty also received France's Chevalier de l'Ordre des Arts et des Lettres, and in 1985 was awarded France's highest decoration, the Chevalier de l'[Ordre National de la Legion d'Honneur, by French President François Mitterand. He is a Fellow of the British Institution of Electrical Engineers; the British Royal Television Society; and SMPTE. Flaherty holds a degree in physics and an honorary doctorate of science from Rockhurst College in Kansas City, Missouri.</em></p>

<p><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 26, 2005 01:45 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 132
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
 				AND entry_id <> 132
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1994_-_technology_and_the_future_of_broadcasting_by_dr_joseph_flaherty_cbs_inc.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
