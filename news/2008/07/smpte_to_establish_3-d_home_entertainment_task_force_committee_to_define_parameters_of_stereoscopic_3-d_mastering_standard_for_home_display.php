<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 1473";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 1473 AND placement_is_primary = 1";
	$res_category = mQuery($sql);
	$row_category = mysql_fetch_assoc($res_category);
	$category_id = $row_category[placement_category_id];
	
	# Still need this to track page views. Used in the Links unit in the footer
	$google_links_channel = $author[channel];

	# Set variables based on entry type. NOTE: Podcasts has its own entry template, so it is not included amongst the choices below.
	switch (7) {
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
	<meta name="keywords" content="task force, entertainment technology, society motion, motion picture, home display, smpte, SMPTE, home, entertainment, standards, content, glasses, technology, digital, force, task, Entertainment, etc, society, new, ETC, Home, Task, motion, Force" />
	<meta name="description" content="&lt;p class=&quot;editorial&quot;&gt;The SMPTE is a standards setting society for the North American television and motion picture industries. While every task force formed within the SMPTE may not lead to a new industry success, a formation of one sends a very strong message that a seriousness for a topic has set in. The engineers that populate the SMPTE are, for the most part, from the manufacturing and content sectors. For those who have experienced 3D on the big screen (by way of the Christie digital projector) know that a great treat is in store for us when it comes home. That is now technically possible and some sets are being sold today as 3D-ready.

In years past every effort to commercialize 3D (something certainly not new in concept nor even technology) ended with a disappointing collapse. The failure has always been blamed on the same thing--those uncomfortable glasses a viewer must wear for essential left eye-right eye image separation. Several attempts have been made to avoid glasses by using complex rear projection screen technology, but that never worked well. A golden marketing opportunity has come to the eye wear industry. The problem for them to solve is the public rejection of the 3D glasses. One way that can be done is by engaging the genius of eyeglass frame makers (like Luxottica) and the mass marketing lens-making and mounting companies (like Benyon and LensCrafters). Together they can produce a mass public appeal to both accept and then acquire comfortable and fashionable &quot;now-essential&quot; 3D eye wear. The acquiring of these fashionable 3D glasses may be quite similar to how one gets their dark glasses--order them to your prescription with a desired frame style. It needs to be sold as another of those things we accept as part of our middle class standard of living. For those with uncorrected vision you may turn to the racks of 3D glasses at your local grocer and, as with reading glasses, choose a pair fit for your face and personality. &quot;Make the glasses friendly and fashionable&quot; is the message being given here and you bring to an end the lethal objection to 3D--uncorrected and uncomfortable glasses. __Dale Cripps&lt;/p&gt;
  

WHITE PLAINS, NY - July 21, 2008 -- The Society of Motion Picture and Television Engineers (SMPTE) is establishing a task force to define the parameters of a stereoscopic 3-D mastering standard for content viewed in the home. Called 3-D Home Display Formats Task Force, the project promises to propel the 3-D home entertainment industry forward by setting the stage for a standard that will..." />
	<title>HDTV Magazine Bulletins - SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php";
		if ($author[img] != '' && 7 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">SMPTE to Establish 3-D Home Entertainment Task Force Committee to Define Parameters of Stereoscopic 3-D Mastering Standard for Home Display</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>July 21, 2008</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php&amp;phase=2&amp;title=SMPTE%20to%20Establish%203-D%20Home%20Entertainment%20Task%20Force%20Committee%20to%20Define%20Parameters%20of%20Stereoscopic%203-D%20Mastering%20Standard%20for%20Home%20Display&amp;bodytext=%3Cp%20class%3D%22editorial%22%3EThe%20SMPTE%20is%20a%20standards%20setting%20society%20for%20the%20North%20American%20television%20and%20motion%20picture%20industries.%20While%20every%20task%20force%20formed%20within%20the%20SMPTE%20may%20not%20lead%20to%20a%20new%20industry%20success%2C%20a%20formation%20of%20one%20sends%20a%20very%20strong%20message%20that%20a%20seriousness%20for%20a%20topic%20has%20set%20in.%20The%20engineers%20that%20populate%20the%20SMPTE%20are%2C%20for%20the%20most%20part%2C%20from%20the%20manufacturing%20and%20content%20sectors.%20For%20those%20who%20have%20experienced%203D%20on%20the%20big%20screen%20%28by%20way%20of%20the%20Christie%20digital%20projector%29%20know%20that%20a%20great%20treat%20is%20in%20store%20for%20us%20when%20it%20comes%20home.%20That%20is%20now%20technically%20possible%20and%20some%20sets%20are%20being%20sold%20today%20as%203D-ready.%0A%0AIn%20years%20past%20every%20effort%20to%20commercialize%203D%20%28something%20certainly%20not%20new%20in%20concept%20nor%20even%20technology%29%20ended%20with%20a%20disappointing%20collapse.%20The%20failure%20has%20always%20been%20blamed%20on%20the%20same%20thing--those%20uncomfortable%20glasses%20a%20viewer%20must%20wear%20for%20essential%20left%20eye-right%20eye%20image%20separation.%20Several%20attempts%20have%20been%20made%20to%20avoid%20glasses%20by%20using%20complex%20rear%20projection%20screen%20technology%2C%20but%20that%20never%20worked%20well.%20A%20golden%20marketing%20opportunity%20has%20come%20to%20the%20eye%20wear%20industry.%20The%20problem%20for%20them%20to%20solve%20is%20the%20public%20rejection%20of%20the%203D%20glasses.%20One%20way%20that%20can%20be%20done%20is%20by%20engaging%20the%20genius%20of%20eyeglass%20frame%20makers%20%28like%20Luxottica%29%20and%20the%20mass%20marketing%20lens-making%20and%20mounting%20companies%20%28like%20Benyon%20and%20LensCrafters%29.%20Together%20they%20can%20produce%20a%20mass%20public%20appeal%20to%20both%20accept%20and%20then%20acquire%20comfortable%20and%20fashionable%20%22now-essential%22%203D%20eye%20wear.%20The%20acquiring%20of%20these%20fashionable%203D%20glasses%20may%20be%20quite%20similar%20to%20how%20one%20gets%20their%20dark%20glasses--order%20them%20to%20your%20prescription%20with%20a%20desired%20frame%20style.%20It%20needs%20to%20be%20sold%20as%20another%20of%20those%20things%20we%20accept%20as%20part%20of%20our%20middle%20class%20standard%20of%20living.%20For%20those%20with%20uncorrected%20vision%20you%20may%20turn%20to%20the%20racks%20of%203D%20glasses%20at%20your%20local%20grocer%20and%2C%20as%20with%20reading%20glasses%2C%20choose%20a%20pair%20fit%20for%20your%20face%20and%20personality.%20%22Make%20the%20glasses%20friendly%20and%20fashionable%22%20is%20the%20message%20being%20given%20here%20and%20you%20bring%20to%20an%20end%20the%20lethal%20objection%20to%203D--uncorrected%20and%20uncomfortable%20glasses.%20__Dale%20Cripps%3C%2Fp%3E%0A%20%20%0A%0AWHITE%20PLAINS%2C%20NY%20-%20July%2021%2C%202008%20--%20The%20Society%20of%20Motion%20Picture%20and%20Television%20Engineers%20%28SMPTE%29%20is%20establishing%20a%20task%20force%20to%20define%20the%20parameters%20of%20a%20stereoscopic%203-D%20mastering%20standard%20for%20content%20viewed%20in%20the%20home.%20Called%203-D%20Home%20Display%20Formats%20Task%20Force%2C%20the%20project%20promises%20to%20propel%20the%203-D%20home%20entertainment%20industry%20forward%20by%20setting%20the%20stage%20for%20a%20standard%20that%20will...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">The SMPTE is a standards setting society for the North American television and motion picture industries. While every task force formed within the SMPTE may not lead to a new industry success, a formation of one sends a very strong message that a seriousness for a topic has set in. The engineers that populate the SMPTE are, for the most part, from the manufacturing and content sectors. For those who have experienced 3D on the big screen (by way of the Christie digital projector) know that a great treat is in store for us when it comes home. That is now technically possible and some sets are being sold today as 3D-ready.

<p>In years past every effort to commercialize 3D (something certainly not new in concept nor even technology) ended with a disappointing collapse. The failure has always been blamed on the same thing--those uncomfortable glasses a viewer must wear for essential left eye-right eye image separation. Several attempts have been made to avoid glasses by using complex rear projection screen technology, but that never worked well. A golden marketing opportunity has come to the eye wear industry. The problem for them to solve is the public rejection of the 3D glasses. One way that can be done is by engaging the genius of eyeglass frame makers (like Luxottica) and the mass marketing lens-making and mounting companies (like Benyon and LensCrafters). Together they can produce a mass public appeal to both accept and then acquire comfortable and fashionable "now-essential" 3D eye wear. The acquiring of these fashionable 3D glasses may be quite similar to how one gets their dark glasses--order them to your prescription with a desired frame style. It needs to be sold as another of those things we accept as part of our middle class standard of living. For those with uncorrected vision you may turn to the racks of 3D glasses at your local grocer and, as with reading glasses, choose a pair fit for your face and personality. "Make the glasses friendly and fashionable" is the message being given here and you bring to an end the lethal objection to 3D--uncorrected and uncomfortable glasses. __Dale Cripps</p></p>

<p>  </p>

<p>WHITE PLAINS, NY - July 21, 2008 -- The Society of Motion Picture and Television Engineers (SMPTE) is establishing a task force to define the parameters of a stereoscopic 3-D mastering standard for content viewed in the home. Called 3-D Home Display Formats Task Force, the project promises to propel the 3-D home entertainment industry forward by setting the stage for a standard that will enable 3-D feature films and other programming to be played on all fixed devices in the home, no matter the delivery channel.  The inaugural meeting of the Task Force is open to entertainment technology professionals interested in participating in the effort, subject to available space (SMPTE membership not required). It takes place on August 19, 2008 and will be hosted by the Entertainment Technology Center (ETC) at the University of Southern California, near downtown Los Angeles.</p>

<p>"Digital technologies have not only paved the way for high quality 3-D in the theaters, they have also opened the door to 3-D in the home," explained SMPTE Engineering Vice President Wendy Aylsworth. "In order to take advantage of this new opportunity, we need to guarantee consumers that they will be able to view the 3-D content they purchase and provide them with 3-D home solutions for all pocketbooks."<br />
 <br />
The 3-D Home Display Formats Task Force will explore the standards that need to be set for 3-D content distributed via broadcast, cable, satellite, packaged media and the Internet and played-out on televisions, computer screens and other tethered displays. After six months, the committee will produce a report that defines the issues and challenges, minimum standards, evaluation criteria and more, which will serve as a working document for SMPTE 3-D standards efforts to follow. </p>

<p><br />
The first 3-D Home Display Formats Task Force gathering will feature demonstrations of 3-D technologies. All technology professionals in content creation and distribution, consumer electronics and entertainment tools and services who are considering joining the group are welcome to attend. Non-members will be asked to pay a small fee for the initial meeting, and ongoing participation in the work requires membership in the SMPTE Standards Community. Register at: www.smpte.org</p>

<p>About the Society of Motion Picture and Television Engineers<br />
The Society of Motion Picture and Television Engineers (SMPTE) is the leading international technical society for the motion imaging industry. As an internationally recognized and accredited standards-setting body, SMPTE develops standards, recommended practices and guidelines and spearheads educational activities to advance engineering and moving imagery. Since its founding in 1916, the Society has established more than 600 standards including the physical dimensions of 35mm film and the SMPTE time code. More recently, it codified the MXF file format to support the exchange of professional AV content and crafted the Digital Cinema Standards, which paved the way for digital movie theaters. Headquartered in New York, SMPTE is comprised of engineers and other technical specialists, IT and new media professionals, filmmakers, manufacturers, educators and consultants in more than 65 countries. They are joined at SMPTE by more than 200 sponsoring corporations, principal players in content creation, production and delivery for all platforms and in entertainment hardware and software. www.smpte.org <http://www.smpte.org> .<br />
 <br />
About the Entertainment Technology Center @ USC<br />
The Entertainment Technology Center @ USC is a non-profit organization within USC's School of Cinematic Arts which brings together the top entertainment, technology and consumer electronic companies to discuss how to understand what next-generation consumers want and then to work towards new entertainment products and services for the future. ETC's Executive Sponsors are Disney, Sony Pictures Entertainment, Twentieth Century Fox, Viacom/Paramount, Warner Bros., along with Alcatel-Lucent, Cisco, Deluxe Entertainment Services Group, Inc., Lucasfilm Ltd, Sharp, TATA Consultancy Services, Thomson and Volkswagen of America.  Additionally, ETC's Anytime/Anywhere Content Lab (AACL) is sponsored by Dolby, LG Electronics and Sandisk.<br />
One of ETC's current initiatives is to map 3-D and identify gaps and opportunities for improvement in acquisition, production, to distribution in theaters, the home and digital devices.  ETC is now conceptualizing the Digital 3-D Lab, as a part of the Anytime/Anywhere Content Lab, which will build on the success of ETC's world-renowned Digital Cinema Laboratory. For more information, email: info@etcenter.org </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>July 21, 2008 09:15 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 1473
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
			
 		<?if (7 <> 7) {
 			# Recent Articles by Author (exclude this one)
 			# Do not show recent articles for Bulletins.
 			$qry = "
 			SELECT entry_id, entry_blog_id, entry_created_on, entry_title, author_id, author_name
 			FROM mt_entry e, mt_author a
 			WHERE entry_blog_id = 7
 				AND entry_id <> 1473
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
 				<h2>About Bulletins</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/news/2008/07/smpte_to_establish_3-d_home_entertainment_task_force_committee_to_define_parameters_of_stereoscopic_3-d_mastering_standard_for_home_display.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
