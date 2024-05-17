<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 368";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Ed Milbourn'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 368 AND placement_is_primary = 1";
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
	<meta name="keywords" content="cable ready, cable applications, set top, digital cable, interactive cable, cable, Cable, products, FCC, ocap, OCAP, services, fcc, issues, applications, consumers, want, any, ready, standard, bks, Brian, should, device, brian" />
	<meta name="description" content="&lt;em&gt;An interview with Brian Smith on the status of the Cable/CE negotiations to establish a fully open interactive digital Cable Ready standard&lt;/em&gt;


Brian Smith is a both good friend and a former business colleague of mine at RCA/Thomson.  Brian presently is VP of Technology Policy and Standards for Philips N.A. and, of special significance to us, is the Chairman of the Consumer Electronics Association (CEA) Video Division Board of Directors.  The CEA Video Board addresses many CE common issues, chief among them being that of representing the CE industry in the ongoing &quot;Interactive Cable Ready&quot; standard negotiations.  The objective of this standard is to ultimately replace the unidirectional CableCARD cable interface with a system that downloads a plethora of interactive services for digital Cable subscribers.

But developing this standard, based on Cable's OCAP (Open Cable Applications Protocol) system, is proving to be one of the most daunting tasks, both technically and commercially, to have been tackled by both industries.  Brian took time from his busy schedule to give us a comprehensive update relative to several salient aspects of the negotiations:



&lt;strong&gt;ED:  Generally, what is the present state of the negotiations?&lt;/strong&gt;

BKS:  Slow going. There are fundamental business issues on each side which conflict with each other and" />
	<title>HDTV Magazine Articles - Ed's view - The Interactive "Cable Ready" Standard</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/eds_view_-_the_interactive_cable_ready_standard';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Ed\'s view - The Interactive "Cable Ready" Standard'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Ed Milbourn" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Ed's view - The Interactive "Cable Ready" Standard</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Ed Milbourn</b><br />
				<?=$author_title?>
				Posted on <b>May  1, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Politics & Policy">Politics & Policy</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php&amp;phase=2&amp;title=Ed%27s%20view%20-%20The%20Interactive%20%22Cable%20Ready%22%20Standard&amp;bodytext=%3Cem%3EAn%20interview%20with%20Brian%20Smith%20on%20the%20status%20of%20the%20Cable%2FCE%20negotiations%20to%20establish%20a%20fully%20open%20interactive%20digital%20Cable%20Ready%20standard%3C%2Fem%3E%0A%0A%0ABrian%20Smith%20is%20a%20both%20good%20friend%20and%20a%20former%20business%20colleague%20of%20mine%20at%20RCA%2FThomson.%20%20Brian%20presently%20is%20VP%20of%20Technology%20Policy%20and%20Standards%20for%20Philips%20N.A.%20and%2C%20of%20special%20significance%20to%20us%2C%20is%20the%20Chairman%20of%20the%20Consumer%20Electronics%20Association%20%28CEA%29%20Video%20Division%20Board%20of%20Directors.%20%20The%20CEA%20Video%20Board%20addresses%20many%20CE%20common%20issues%2C%20chief%20among%20them%20being%20that%20of%20representing%20the%20CE%20industry%20in%20the%20ongoing%20%22Interactive%20Cable%20Ready%22%20standard%20negotiations.%20%20The%20objective%20of%20this%20standard%20is%20to%20ultimately%20replace%20the%20unidirectional%20CableCARD%20cable%20interface%20with%20a%20system%20that%20downloads%20a%20plethora%20of%20interactive%20services%20for%20digital%20Cable%20subscribers.%0A%0ABut%20developing%20this%20standard%2C%20based%20on%20Cable%27s%20OCAP%20%28Open%20Cable%20Applications%20Protocol%29%20system%2C%20is%20proving%20to%20be%20one%20of%20the%20most%20daunting%20tasks%2C%20both%20technically%20and%20commercially%2C%20to%20have%20been%20tackled%20by%20both%20industries.%20%20Brian%20took%20time%20from%20his%20busy%20schedule%20to%20give%20us%20a%20comprehensive%20update%20relative%20to%20several%20salient%20aspects%20of%20the%20negotiations%3A%0A%0A%0A%0A%3Cstrong%3EED%3A%20%20Generally%2C%20what%20is%20the%20present%20state%20of%20the%20negotiations%3F%3C%2Fstrong%3E%0A%0ABKS%3A%20%20Slow%20going.%20There%20are%20fundamental%20business%20issues%20on%20each%20side%20which%20conflict%20with%20each%20other%20and&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img alt="M=MEDIUMEDMILBOURN.jpg" src="http://www.hdtvmagazine.com/articles/Images/M%3DMEDIUMEDMILBOURN.jpg" width="150" height="150"align="right"/><em>This is an interview with Brian Smith on the status of the Cable/CE negotiations to establish a fully <a href="http://www.opencable.com/">open interactive digital Cable Ready standard</a></em></p>

<p><br />
Brian Smith is both a good friend and a former business colleague of mine at RCA/Thomson.  Brian presently is VP of Technology Policy and Standards for Philips N.A. and, of special significance to us, is the Chairman of the Consumer Electronics Association (CEA) Video Division Board of Directors.  The CEA Video Board addresses many CE common issues, chief among them being that of representing the CE industry in the ongoing "Interactive Cable Ready" standard negotiations.  The objective of this standard is to ultimately replace the unidirectional CableCARD cable interface with a system that downloads a plethora of interactive services for digital Cable subscribers.</p>

<p>But developing this standard, based on Cable's OCAP (Open Cable Applications Protocol) system, is proving to be one of the most daunting tasks, both technically and commercially, to have been tackled by both industries.  Brian took time from his busy schedule to give us a comprehensive update relative to several salient aspects of the negotiations:</p>

<p><br />
<strong>ED:  Generally, what is the present state of the negotiations?</strong></p>

<p>BKS:  Slow going. There are fundamental business issues on each side which conflict with each other and have not yet yielded to mutually satisfactory compromises even after almost 2 ½ years. Furthermore, the landscape continues to change over time with Cable planning new technologies/services (e.g. switched digital) which were not anticipated at the beginning and further complicate things.</p>

<p><strong>ED:  What are the major commercial and technical issues being      addressed?</strong></p>

<p>BKS:   Cable's fundamental business position is that their service is the entire collection of individual services, presented in the way they want them presented with little or no room for CE products to provide any value-added or differentiation. In effect, Cable wants a set-top box buried within the TV.</p>

<p>CE mfrs. need the freedom to innovate and differentiate their products in order to compete with each other in the retail environment. This includes wanting a uniformity of user operation whether the viewer is watching cable, terrestrial broadcast or any other internal source. It is confusing to the consumer to have to "switch gears" in how the product remote control buttons, menus and other functions operate when they are "watching cable". Furthermore there is a history (including current unidirectional plug & play), where CE products can enhance/differentiate while viewing cable content.</p>

<p>OCAP was designed for use in a dedicated set-top box not having any other functionality but accessing cable services. It has a number of technical resource management systems that want to take total control of the device. Obviously in a multifunction product which may be used for viewing other content, modifications to the way OCAP operates are necessary. A joint technical team is working on some modifications.  A major part of the discussions is how far those modifications should go.</p>

<p>Testing is a very large and complicated issue. OCAP is a middleware software specification, but it is not a uniform piece of software code. There can be many OCAP implementations all based on the same written specification. The applications that cable downloads onto the OCAP middleware can be likened to PC applications running on Windows. The combination of many platforms, many different OCAP implementations, many differently configured Cable head-ends and a variety of applications, would make testing everything against everything mathematically daunting. Cable does not want to unduly delay commercial introduction of new applications to enable extended testing, but CE mfrs are concerned about product robustness - which can be summed up as "TVs should not crash". Finding a middleground is a tough task.</p>

<p>Related to the testing issue is "common reliance". The CE side believes that whatever technologies Cable wants CE to use in cable-ready devices, they should use for themselves in their leased products. Whether it is CableCARDS, OCAP or anything else, if Cable must also rely upon it, then any technical issues will be quickly resolved. So far, Cable has not even implemented CableCARD for its own use and has consistently requested implementation delays from the FCC.</p>

<p>There are also Content Protection issues. Although CE is friendly towards the normal array of protections as covered in the unidirectional agreement and embodied in FCC regulations, cable's content providers want to go further. They would like to have the option to totally shut off selected product outputs, selectively reduce the resolution of hi-def content, and phase out analog interfaces. The CE community is concerned about consumers becoming totally confused, disenchanted and - even worse - believing the products are suddenly "broken" if a content provider shuts-off an interface.</p>

<p>Licensing issues also abound. In order to use cable's conditional access system, several licenses are needed from CableLabs, the cable industry consortium, and potentially from some third parties. Cable's proposed licenses for devices that are fully interactive with cable systems go far beyond simple licensing of the technology and its intellectual property (with appropriate rules to protect concerns over theft of service and copyright issues).  CableLabs has drafted these licenses to compel product conformance to the business and future marketing objectives of cable operators, as well - something that many in the CE and IT industry have said is beyond the permissible scope of present FCC regulations.  These regulations protect a consumer's right to attach a lawful competitive device to a network, so long as the device does not harm the network or contribute to theft of service, and limit the imposition of other licensing constraints on the device provider.  </p>

<p>Technological evolution is another sticky area. At some point in the future, Cable will want to introduce new services which may not be able to operate on older products because they lack a certain new technology. Yet consumers will buy a fully-featured integrated bi-directional cable ready TV specifically because they want the full array of services and which they expect to operate for many, many years. How can Cable continue to evolve their services without angering consumers whose TVs are only a couple of years old? The trick for us is to protect consumers' expectations to enjoy the services they anticipated when they bought their TV, even if they may need some ancillary devices down the road. </p>

<p><strong>ED:  What, if any, are the "deal breakers" as seen by each side?</strong></p>

<p>BKS: Cable does not want their services "disaggregated," i.e. allowing the CE device to become, in their view, a filter for the way they present and market services to the consumer.  For example, they do not want their UI to be modified by the CE products, and they want everything on the UI to be available for the consumer to order and pay for.  Cable wants copy protection and output control flexibility that they say is necessary for them to compete with other service providers.  </p>

<p>CE and IT manufacturers believe that customers should have a choice in the blend of capabilities that they pay for in their products.   They don't think that the combination of OCAP middleware and future cable conditional access software should totally control the TV and its access to other services/peripheral devices, or the home network. They don't think CableLabs should be able to unilaterally establish or change the specifications for what constitutes an Integrated cable ready receiver or the test process for approving them.  CE wants all downloaded Cable applications to be thoroughly tested on CE devices for robustness.  </p>

<p><br />
<strong>ED:  Are others besides Cable and CE involved?  Do they have a   vote?</strong></p>

<p><br />
BKS: Following completion of the unidirectional P&P process, when the groups embarked on this next phase, the FCC asked us to include input from other affected industries. There have been a variety of meetings on content protection with the MPAA, individual studios, TV networks, broadcasters, etc.  These have included other MPVDs (e.g. satellite and telcos) and programming networks, which are also affected by the FCC "encoding" rules that protect consumers from excessive application of copy protection, selectable output control, and "downres" technologies, as well as Cable's existing hardware suppliers, component makers, etc.  Under the FCC guidelines, the official "deal" is between CE and Cable, so these other groups do not get a vote in any proposed bilateral "framework" proposal for new regulations - - however when the agreement is put into the FCC open process, then everyone gets an opportunity to comment on it and the FCC may elect to modify it.</p>

<p>It should also be mentioned, that the CE group consists not only of typical CE companies, but there are also important members of the PC community as well.</p>

<p><br />
<strong>ED:  Is there any thought is making the negotiated version of OCAP (or whatever it is now called) an "open" ANSI standard?</strong></p>

<p><br />
BKS:   We expect that whatever the final jointly agreed specification is, it will go through an ANSI open standards organization such as CEA or SCTE. From the CE perspective, we are on record with the FCC as wanting to see the regulations reference very specific versions ("snapshots") of such standards. If there is still disagreement on certain elements of those specific standards, the FCC could elect (and did in the uni-agreement) to specify in the regulations certain changes to the written specs.</p>

<p><br />
<strong>ED:  Is there really any commercial advantage for CE to embrace OCAP?  (i.e. can CE in general make any money on it from the standpoint of a standard retail marketing model?)</strong> </p>

<p>BKS:  Consumers seem to like the services they get from Cable. Many dislike having a separate set-top box in order to get them. This is now becoming even more the case when many TVs don't have "tops" to put STBs on! Multiple remotes, "dueling" volume controls, hugely different UI schemes, and other user control confusion are tremendously frustrating to consumers.  Consumers embraced the very limited degree of cable compatibility that was achieved in the analog world, which allowed them to tune all unscrambled channels with their TV and VCR remote controls.  We believe there is still great potential for unidirectional "CableCARD" products that first came to market in 2004, and allow consumers to do the same for scrambled digital channels, as well.  Over 2 million have entered consumer homes in under 2 years but we are still struggling with CableCARD support issues in the field.  If these can be resolved, and CableCARD installations become just as routine as set-top boxes, I believe that the consumer preference for well-integrated solutions will become apparent.  This is both the promise and the challenge of taking the next step, and building highly reliable products that integrate the OCAP software.  If we can, I think consumers and retailers will love them.  We want to keep giving consumers a choice in what they buy, and in how it works.</p>

<p><br />
<strong>ED:  Concerning the present state of the negotiations, when do you anticipate an agreement (if any).</strong></p>

<p>BKS:  I don't know. There are still many issues unresolved (as described above), however the groups continue to meet to try to work through them.</p>

<p>Here is what we jointly told the FCC in a March 31 written status report that we filed in FCC Docket No. 97-80:  </p>

<p>"The parties continue to meet with and work with each other, and both sides share the belief that this process is valuable and necessary for the successful design and deployment of integrated Digital Cable Ready products.  Since the date of the last status reports, the parties' joint engineering team has continued its work and has made significant progress in how to define how resources in interactive Digital Cable Ready Products (IDCPs) using the OpenCable Application Platform (OCAP) can be shared between cable applications and other applications of the IDCP, in particular how to avoid conflicts in the use of resources within IDCPs by cable applications and other applications.  There remain technical issues to resolve, and consideration of all solutions by the larger group.  There is also an expectation that a joint team will address defining a workable conformance testing program for interactive products and software applications designed to run on them, based upon the framework previously described in earlier status reports.  Other issues that the parties have agreed to discuss include possible updates to future unidirectional products, and means of conveying firmware updates to bi-directional products."</p>

<p><strong>Thanks, Brian!</strong></p>

<p><em>Having dealt for years with Cable/CE issues in a similar position as Brian's, I can certainly empathize and sympathize with Brian and his team as they struggle with this task.  As Cable continues to protect its private business model, which, of course, they have right to do (in spite of the FCC), Cable may be advised to check its backside.  Coming on strong as a viable competitor to Cable are powerful members of the traditional telecommunications industry  - AT&T, BellSouth, and Verizon.  Last month, these entities, along with CEA, announced the start of negotiations to develop device interoperability standards based on IP (Internet Protocol).  Because of the increasing deregulation of telecommunications services and the growing ubiquity of broadband Internet, the adoption of such an IP network/device standard may very well outpace and eventually "trump" Cable.  Stay tuned.</em></p>

<p>Ed</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Ed Milbourn</b>, <b>May  1, 2006 12:03 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 368
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
 			<h2>More on Politics & Policy</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Politics & Policy'
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
 				AND entry_id <> 368
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/05/eds_view_-_the_interactive_cable_ready_standard.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
