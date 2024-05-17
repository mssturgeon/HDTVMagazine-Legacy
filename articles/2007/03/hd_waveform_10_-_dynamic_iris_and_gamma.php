<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 565";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Richard Fisher'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 565 AND placement_is_primary = 1";
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
	<meta name="keywords" content="dynamic range, light output, contrast ratio, peak white, gamma ire, gamma, iris, ire, IRE, dynamic, image, black, light, response, video, contrast, output, system, does, peak, range, pattern, ratio, using, imaging" />
	<meta name="description" content="Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature degrades overall image performance, why it sells product and why, for some technologies, it may be needed to be competitive.

&lt;B&gt;Iris&lt;/B&gt;
One way to improve dynamic range and measured contrast ratio is to employ an iris. An iris typically decreases..." />
	<title>HDTV Magazine Articles - HD Waveform 10 - Dynamic Iris and Gamma</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/hd_waveform_10_-_dynamic_iris_and_gamma';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('HD Waveform 10 - Dynamic Iris and Gamma'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Richard Fisher" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">HD Waveform 10 - Dynamic Iris and Gamma</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Richard Fisher</b><br />
				<?=$author_title?>
				Posted on <b>March 26, 2007</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Technology">Technology</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php&amp;phase=2&amp;title=HD%20Waveform%2010%20-%20Dynamic%20Iris%20and%20Gamma&amp;bodytext=Over%20the%20last%203%20years%20manufacturers%20have%20been%20busy%20improving%20their%20marketing%20specs%20to%20the%20mass%20market%20for%20contrast%20ratios%20by%20using%20an%20iris%20and%20gamma%20technique%20since%20better%20numbers%20creates%20the%20illusion%20of%20purchasing%20better%20performance.%20The%20purpose%20of%20this%20article%20is%20to%20put%20that%20into%20perspective%20so%20that%20the%20performance%20enthusiast%20will%20understand%20why%20this%20feature%20degrades%20overall%20image%20performance%2C%20why%20it%20sells%20product%20and%20why%2C%20for%20some%20technologies%2C%20it%20may%20be%20needed%20to%20be%20competitive.%0A%0A%3CB%3EIris%3C%2FB%3E%0AOne%20way%20to%20improve%20dynamic%20range%20and%20measured%20contrast%20ratio%20is%20to%20employ%20an%20iris.%20An%20iris%20typically%20decreases...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p class="editorial">"HD Waveform" is a series of articles published over the past few years and originally made available only to subscribers of HDTV Magazine. It is authored by Richard Fisher and was born out of 20+ years in the industry and discussion among HDTV Magazine membership concerning faithful reporting. HD Waveform goes into great detail, providing conclusions based in science and is therefore suitable for both consumers and professionals alike. In many cases these conclusions will seem at odds with what many are hearing "on the street" and from marketers. Waveform is for those who care about quality, performance and reasonable scientific conclusions. Feel free to read the rest of <a href="/forum/viewforum.php?f=103">The HD Waveform Series</a>, as originally published.</p>

<p>Over the last 3 years manufacturers have been busy improving their marketing specs to the mass market for contrast ratios by using an iris and gamma technique since better numbers creates the illusion of purchasing better performance. The purpose of this article is to put that into perspective so that the performance enthusiast will understand why this feature does not meet video standards, why it sells product and why, for some technologies, it may be needed to be competitive.</p>

<p>To shorten the article some terms have embedded links, blue, for a full definition of the term.</p>

<p><B>Iris</B><br />
One way to improve dynamic range and measured contrast ratio is to employ an iris. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=86">An iris</a> typically decreases the light going to the panels or imager, or can also be on the output side reducing the light entering the lens after the imager. A manual iris allows the end user to employ a true brightness adjustment for the best blacks in their application. Reducing light output to the lens also improves the intrafield contrast ratio or actual available dynamic range when you have a mix of bright and dark within the same frame. Many higher end front projectors employ a manual iris. The disadvantage is that no matter where you set it, you have increased or decreased the light output linearly; Blacks may look blacker but peak white also drops in output as well. What if we had an iris that could change its setting on the fly based on image content such that it closes up during dark scenes for better blacks and opens up during bright scenes for peak light output, thereby creating a wider light output capability? Hook up a motor to a high speed iris mechanism and viola, you have an auto iris. While a good start, that alone cannot change the natural dynamic range of the technology. It only changes light output and one has to be very careful of when and how fast it changes or the viewer will catch this process in motion which is often times referred to as breathing.</p>

<p><B>Gamma</B><br />
What if you could change brightness and contrast levels, gamma, on the fly and better yet do it at multiple specific points in the video signal based on image content? Viola, you have the ability to create the perception of more dynamic range. <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=77">Gamma</a> is the difference in response between two levels and for video we use the industry standard of 2.2. Video gamma is also a non linear response determined with an exponential equation rather than simple multiplication. A video signal is broken up into IRE levels where 0 IRE is peak black and 100 IRE is peak white. In terms of overall gamma, if it is less than 2.2 the image becomes flat, dull or washed-out and if it is more than 2.2 the image becomes dynamic, bright or aggressive. When manipulating gamma you have two brick walls, peak white and peak black which you cannot go beyond. As an example that means you cannot increase the gamma response from 60-100 IRE without decreasing the gamma from 0-59 IRE. You cannot increase the gamma from 40-70 IRE without decreasing gamma from 0-39 IRE and/or from 71-100 IRE. You have to rob Paul to pay Peter; there is no other way when playing the gamma game on the input signal or you will induce clipping errors that will be quite visible. It is possible to overcome this brick wall of a video signal by changing the gamma response at the imaging device but then you face the brick wall of what the device is capable of and that is typically maxed out anyway when using a <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/viewtopic.php?t=76">D65 color temperature</a> as a reference. This is a rare occurrence and bears little merit for discussion</p>

<p><B>Black is an Illusion</B><br />
When it comes to imaging, black is the absence of light and therefore has perceptual qualities related to optical illusion. We can take a display with poor blacks and with the right pattern, such as a full field 0 IRE pattern (black), make that aspect quite prominent. We can also improve our perception of that level of black by simply introducing a 50 IRE window in the middle of the 0 IRE full raster pattern. I can make the black seem even blacker by increasing that window to 100IRE providing the greatest amount of light difference. By providing that comparison to your eyes the black appears blacker, you perceive a greater dynamic range, but it is an optical illusion! Technically, measurement would show the far more likely result that light from the window is leaking into the black area which not only reduces your black level but can also hide subtle levels of black. This is called intrafield contrast ratio putting yet another spin on the optical illusion of black!</p>

<p><B>Putting All 3 Together</B><br />
The ultimate goal for this system is to create a perceptual increase in dynamic image. The best explanation uses two image extremes. Using a dark scene with no real peak white, the iris closes up improving real black level. The gamma is then expanded from say 0-50 IRE reducing the gamma from 50-100IRE. This will create a more dynamic presentation for the dark image and blacks will be perceived as even blacker since those areas that do have light have been increased, creating a greater dynamic difference; the optical illusion of black. Using a bright scene with no real peak black the iris opens up improving real light output. The gamma is then expanded from say 50-100 IRE reducing the gamma from 0-50 IRE. This will also create a more dynamic presentation and blacks will be perceived as even blacker since those areas that do not have light have been decreased creating a greater dynamic difference; the optical illusion of black. In no way does this simplistic example represent the far more complex nature of this system and its implementation, but hopefully you have an elementary understanding of how this is done and why it can make a mess of things.</p>

<p><B>Objective Measurements</B><br />
Does it work? Why of course! It does create the desired perception for the viewer of better dynamic range and allows the manufacturer to claim greater contrast ratios on their specs. Close up the iris for the peak black measurement and open it up for peak white measurement and you can only get a larger number than one without an iris. From a current Panasonic PTAE1000 review after ISF calibration:</p>

<p>With the Dynamic Iris OFF at 96 lamp hours I obtained 367fl at 100IRE and .522fl for 0IRE yielding a contrast ratio of 703:1.</p>

<p>With Dynamic Iris ON I obtained 715fl at 100IRE and .503fl for 0IRE yielding a contrast ratio of 1421:1.</p>

<p>If we go check the specs at Projector Central Panasonic is claiming a contrast ratio of 11000! This is defined as full on, full off. Historically this is defined as the projector using a 100IRE window or raster with all three colors maxed out for the 100IRE reading, not even remotely close to D65, nor really viewable, and then the projector turned off! Obviously that has nothing to do with actual imaging and is a flawed test, yet they are doing it. Nearly all of the manufacturers are following this testing procedure to remain on equal footing in the market. The human eye is capable of about 800:1 for any given scene whether on screen or in real life. Does a real contrast ratio of 11000:1 have any value? The point is this is nothing but specification marketing shenanigans that tell the consumer or imaging professional little about actual performance.</p>

<p>Getting back to the magic trick of a Dynamic iris, the ON number is very impressive in the real world of peak white and black contrast ratios! This test implies nearly a doubling in contrast ratio yet this is not the perceptual experience you will have. All an iris can change is the light output to the imager, not the native dynamic range of the technology. What really puts this in perspective is switching the iris on and off with paused images. With the right image you can see a difference in light output yet with most the difference is quite subtle. Ultimately the biggest difference you see with this test is a change of the gamma response within the image rather than simple light output.</p>

<p>If the end user does not know what the image should look like, no references, they likely will not detect that it is wrong either hence the acceptance and popularity in the mass market. No matter what, it will never provide an accurate image due to the artifacts of an incorrect gamma response. The first two gamma plots of a window and full field pattern represent the calibrated response with iris turned off as the light output changes from 0-100 IRE in 10 IRE steps. The dotted line presents the desired response curve so please ignore the average gamma calculation.</p>

<p>Full Field Pattern / Window Pattern<br />
<img src="/images/articles/waveform/patterns.jpg" alt="Full Field Pattern / Window Pattern" /></p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisOFFwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisOFFfull.jpg" alt="Full Field Pattern" /></p>

<p>As side note, you may have noticed that these two plots are not exactly the same and that, too, is an error and an unexpected error at that because as a lamp based display there is no reason for the light output to change. This error is directly related to the design of the product and it appears gamma shifting is still taking place for whatever reason. This was also reflected in the calibration of this projector. It was a moving target rarely providing an identical response when retested for the same parameter. The forthcoming review covers this in depth.</p>

<p>The two gamma plots that follow are with the iris on showing the system manipulating gamma based on image content. The dotted line presents the desired response curve.</p>

<p>Window Pattern<br />
<img src="/images/articles/waveform/irisONwindow.jpg" alt="Window Pattern" /></p>

<p>Full Field Pattern<br />
<img src="/images/articles/waveform/irisONfull.jpg" alt="Full Field Pattern" /></p>

<p>The goal of this article has been achieved. Testing clearly shows the system in action creating a non-linear response and also changing that response based on image content. What these tests do not reveal is where the system would be manipulating gamma within a variety of far more complex real world images. Unfortunately such depth is beyond the time and resources of this reviewer as well as article length so instead I provide my subjective observations.</p>

<p>When first implemented these systems clearly showed problems either from breathing of the iris or poorly implemented gamma manipulation. While products employing these systems for 2007 have greatly advanced eliminating obvious errors, you can't rob Paul to pay Peter without that image appearing flat in some aspect when it should not. If you have a reference of what the image should look like you will also recognize that such systems clearly change the response; it does not look the same. For the Panasonic, the net effect on the image was subtle for the most part providing a difference that perceptually did appear to improve dynamic range. Testing with common video sources there were no obvious image artifacts to be seen tipping its hand to the viewer but those sources also allow far more leniency with errors. That was not the case with a PC source which required I turn off the auto iris to get a proper image with some content due to a flattening of the upper IRE response. No matter what you may perceive using these products they will not do imaging science with the system turned on; the image is artificial and does not represent the original; it does not meet video industry standards. Someone who masters video or is involved with video setup for mass distribution should not be using a display with such a system that does not allow it to be turned off.</p>

<p><B>Turning it Off</B><br />
If the system has been employed simply to improve sales and marketing specs then turning it off is of no concern. As a system, in most cases you cannot turn off just one or the other feature but the <a href="/cgi-bin/ntlinktrack.cgi?http://www.isfforum.com/">ISF community</a> has figured out a way on some displays to turn off the gamma manipulation and manually adjust the iris for better blacks for a particular application. Some projectors employ a manual iris for that very purpose which comes with the side benefit of better intrafield contrast ratios but that does little to change the natural dynamic range of the imaging technology taking me to the next point.</p>

<p>It could be argued that transmissive LCD needs this process to create a competitive dynamic image and there may be others. I have yet to review any of the reflective LCD products yet based on what I am reading from other reviewers some versions appear to have similar properties. The problem here is natural dynamic range of the technology without any slight of hand. Transmissive LCD clearly suffers from this dilemma and if using CRT as our reference none of the microdisplay technologies qualify. On the other hand if we use film as our reference point for black current DLP microdisplay technology since 2006 has come quite close to that level of response with no gimmicks required.</p>

<p>A point of contention with this system occurs when making direct comparisons of performance. If I am building a DLP projector that meets video standards and setup a demo comparing it to transmissive LCD the main comparison is going to be how my projector looks without such a system and how the LCD looks with it turned off providing a direct comparison of the natural dynamic range of both technologies based on a calibrated response that meets video standards. Many have claimed such comparisons to be unfair yet scientifically speaking it is not only fair but required to compare apples to apples. Naturally the LCD will suffer. If the LCD is allowed to use the system then ultimately you will get two different images with the LCD potentially perceived as more dynamic and if both are pleasing to the eye the only argument left is accuracy, video standards and your perception. Choose!</p>

<p><B>In Perspective</B><br />
If you are looking for performance that meets video imaging standards the measurements, observations and science conclusively show that the use of a dynamic iris and gamma manipulation is clearly not the path of high fidelity imaging.</p>

<p>These kinds of imaging antics and tricks have been going on for decades and are nearly always related to a performance flaw of the technology, implementation and design or cost cutting. Using such tricks to yield better marketing and sales specs for a technology that performs adequately to begin with is old hat as well. Whether or not you want a product that forces or needs such tricks is your call.</p>

<p><B>Links</B><br />
<a href="/forum/viewtopic.php?t=3787">Contrast Ratio</a></p>

<p><a href="/forum/viewforum.php?f=103">Waveform Series</a><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Richard Fisher</b>, <b>March 26, 2007 11:28 AM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 565
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
 				AND entry_id <> 565
 				AND entry_status = 2
 				AND entry_author_id = a.author_id
 				AND a.author_name = 'Richard Fisher'
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
 				<h2>About Richard Fisher</h2>
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2007/03/hd_waveform_10_-_dynamic_iris_and_gamma.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
