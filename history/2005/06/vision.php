<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 94";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 94 AND placement_is_primary = 1";
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
	<meta name="keywords" content="standard television, high resolution, television standards, old standard, new medium, HDTV, hdtv, television, new, our, standard, visual, world, future, vision, business, been, must, good, home, images, high, things, better, enough" />
	<meta name="description" content="A shared vision, offers Peter M. Senge in his book, The Fifth Discipline, changes people's relationship with a company (group as well). It is no longer &quot;their company;&quot; it becomes &quot;our company.&quot; A shared vision is the first step in..." />
	<title>HDTV Magazine Archive &amp; History - 1990 - Vision</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/1990_-_vision';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('1990 - Vision'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php";
		if ($author[img] != '' && 5 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">1990 - Vision</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>June 17, 2005</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category="></a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php&amp;phase=2&amp;title=1990%20-%20Vision&amp;bodytext=A%20shared%20vision%2C%20offers%20Peter%20M.%20Senge%20in%20his%20book%2C%20The%20Fifth%20Discipline%2C%20changes%20people%27s%20relationship%20with%20a%20company%20%28group%20as%20well%29.%20It%20is%20no%20longer%20%22their%20company%3B%22%20it%20becomes%20%22our%20company.%22%20A%20shared%20vision%20is%20the%20first%20step%20in...&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><em>A shared vision, offers Peter M. Senge in his book, The Fifth Discipline, changes people's relationship with a company (group as well). It is no longer "their company;" it becomes "our company." A shared vision is the first step in allowing people who mistrusted each other to begin to work together. It creates a common identity. In fact, an organization's shared sense of purpose, vision, and operating values establish the most basic level of commonality.... </em></p>

<p><em>Shared visions compel courage so naturally that people don't even realize the extent of their courage. Courage is simply doing whatever is needed in pursuit of the vision. In 1961. John Kennedy articulated a vision that had been emerging for many years among leaders within America's space program: to have a man on the moon by the end of the decade. This led to countless acts of courage and daring. </p>

<p>Senge's book reminds us also that a vision with no underlying sense of purpose, no calling, is just a good idea...all "sound and fury, signifying nothing."</em> _Peter M. Senge<br />
_________________________________</p>

<p>Japan gained a new global recognition and respect in the 60s and 70s from their HDTV initiatives. This recognition turned quickly into a nightmare as power struggles between industrialized nations arose in the 80s and 90s. Many unsteady nations sought to subdue the Japanese business aggression now being symbolized by their international lead in HDTV. No one could afford a doubt that HDTV would be anything other than a fantastic hit. </p>

<p>Being no match for the international Shenanigans of the more experienced Europeans what once looked to be a slam dunk for Japan became an international humiliation. They retrenched, worked hard at home again, and in the first years of the new millennium have crawled their way back into global HDTV prominence. </p>

<p>As a result of fights (technical obstructions) against the Japanese initiated standard for production much of the vibrant enthusiasm for HDTV was sucked out of the movement. Many at that time in the 80s thought and even declared that HDTV must fail--die on-the-vine--since it had no obvious economic starting point unless one international production standard could be agreed upon. Many thought that the HDTV consumer attraction could not overcome the established power of the old standards. </p>

<p>Despite setbacks in international diplomacy Japan went on to achieve a fine hour in communications history. It will be recorded that from Japan not only did a technology come alive but that nation perservered with persuasive leadership campaigns that led to the launch of HDTV. From one resolute decision made by Dr. Takio Fujio (then head of the world-class NHK Laboratories) back in the early 1960s the world of television changed for the good...forever. Japan would not let go of a vision with so much good in it and with steadfastness and at times questionable wisdom dedicated many billions of dollars to become ready for its global commercialization.</p>

<p>Once abounding with articles in the 70s and 80s the press abandoned HDTV in the 90s. It was a development that had taking too long, or so it seemed, and patience of the public voice was exhausted. But nothing could be hurried. Instead of remaining focused on the achievement that HDTV was to become the press featured the darker doings of entertainment and communications giants. Attention of press and public gravitated to the ever-green darling of technology-interactive TV. Regardless of the future for that true HDTV is destined to stand above all else as the giant among giants.  </p>

<p>A palatable vision heralding its coming has been trampled upon by those unable to profit quickly from it. In a frenzy of protective functions enemies, both public and secret, have distorted the commercialization with a conscious befuddling of the market. To offer a reminder of the promise of HDTV we have included a speech given in the fall of 1990 by Dale Cripps. The occasion was the closing ceremonies for the Osaka Flower and Industrial Festival--a major Japanese cultural and industrial exposition in Osaka, Japan. The Festival offered many applications and examples of HDTV technology--some industrial, others for the home. The speech was carried on Japanese television and radio, in Japanese newspapers and magazines. It is offered here as a reminder of why HDTV must soon again be the focal point of our communications efforts. HDTV is, according to Mr. Cripps, the only important communications format on the horizon that looks to enjoy a high-level payoff for its developers and for society in general. </p>

<p>Gary Reber, Publisher, Widescreen Review</p>

<p>******************************************</p>

<p>So much has been written and said about HDTV that one might think the world will stop if we must suffer on without it. While it may not be that important it most certainly is a marvelous thing to behold and to have in your business and home. More than anything else on the horizon it has the power to define an era where a new and higher standard of living begins in the home. </p>

<p>The Chinese have an old saying in their ancient book of changes, the I Ching. It declares that "all success is dependent on the effects of mutual attraction." If this remains so, the success of HDTV is guarantied. When, I ask, have you seen a more attractive item heading to market? It is not a painting, a tapestry, a statue, a rose, a tear, nor subtle smile, a wink, a laugh, but it is the most perfect visual representation of these things to reach the home. It is not a baseball game, a polo field, a ship at sea, a wrestling match, or a hockey game, but it brings the presence of these things to you as nothing else can. It is without qualification a brand new medium with even more uses than its fading predecessor--the NTSC, PAL and SECAM television standards. </p>

<p>Its powers of attraction must be great enough to overcome the price resistance of the conversion to it or this development will become an unmitigated disaster. It must remain as HDTV and not follow the temptation to become something else.</p>

<p>If entertainment is too frivolous a reason to rework the television infrastructure consider the changes rushing at us with ever-increasing speeds. A new era seems to be always on the doorstep. That is unsettling for many. Rapid change produces reactionary conditions. Fear of an approaching unknown is venomous. With HDTV in your business and home you remain safe behind this "windshield" fast-approaching change. The fears of the unknown are resolved by presenting it so clearly. Belonging to your own era can further our culture quicker with stability than when in a time of darkness and suffering shadowy communications. In the safe comfort of your home change for the good becomes appealing as it approaches and our adaptation comes with litttle delay. But why is it so different with HDTV over standard television?</p>

<p>I must leave you to answer that question. No person can say with unchallengable authority that HDTV will do any of that better than what existing TV systems do. Each must gain from a personal experience their own insight into the greater potential, the smarter value, the historical meaning of HDTV. Without that experience you are not really part of the movement to the future. Sorry to say that without a renewed vision heralded through HDTV you are not moving into the next era but rather stuck fast to the last one. Five times more visual information is delivered by HDTV each and every second over that in conventional television. When tallied up thatincrease, p over a lifetime can bring more to the individual and our grups than the imagination can presently conceive. What will the cumulative effects be over 20 years? Over a 100 years? And accumulated in the minds of 150 million people? Of 500 million people? Of 5 billion people? At a minimum our polulations will gain a new sense of the world if a new measure of it is presented to them. Having travled the around the world for 30 years I know that there is much more beauty in life than there are ugly scars. But without HDTV all televised imagery of the eart appears course. That courseness reaches us daily without the balance from beauty. Old standard TV is fine for relating the horrors of life but fails to convey the true qualities. With the old we are poorly served as a culture. </p>

<p>How can we judge, then, what economic impact this advance in home communications will have over the next 20 to 100 years? Advertisers will be asking this question soon. My personal belief is that they will find a very powerful tool for both creating and making a commercial impactful. I won't go so far as to call this a banaza maker, though it certainly could be. The social visonaries among us are seeing it as an instrument for expressing the higher nature of man. It undoubtedly works for both.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>How much more is this true for the frustrated artist who produces entertainment programming for standard television? I speak here again of the sublime visual subtleties that artists cannot use in the telling of their stories using stqndard television. The gross functions--car chases and crude violence or broad humor--that is all they have to work with. Sublime things are filtered away like cartoon drawings trying to immitate life. So sublime things are discarded from our mainstream values since no one can profit from producing and distributing them. Many never bother adjusting their old standard TV sets for this reason. What's the point of adjusting it if the program was made with that quality limitation in mind. Because of the all-important financial return from television, motion pictures have been produced with the visual limits of old standard television in mind. Our entire cultural vision has been filtered downward by old television standards. If its not "as seen on TV," its not seen. With HDTV the options for new story telling grow boundlessly with the new enriched visual/aural presentations in the home. HDTV opens new doors to human expression with its extended range of stimuli. It delivers the rose in all its delicate splindor just well as it does the excitement from a violent episode. Standard television does little more for the rose than suggest its shape. The range needed for full emotional communication is delivered with high-definition television. We are going to have better television, period. I think it is safe to say that that means a better life for those willing to acquire it.</p>

<p>What about the defining events of our times? Both acts of progress and destruction are better intepreted with this new medium. What we are, as a people, is presented unerringly by it. That which polishes and that which tarnishes our cultures is sure to be spotted at a greater distance than is possible with any other visual tool in our quiver. I see HDTV lending an accent to the good--a positive contributor to value bulding. I believe that our society will look upon HDTV as it might when discovering properly corrected lenses after fifty+ years of suffering from near-sightedness. Scoundrels take note...the days of your deception and illusions may be numbered as we embrace this new view.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>How much more is this true for the frustrated artist who produces entertainment programming for standard television? I speak here again of the sublime visual subtleties that artists cannot use in the telling of their stories using stqndard television. The gross functions--car chases and crude violence or broad humor--that is all they have to work with. Sublime things are filtered away like cartoon drawings trying to immitate life. So sublime things are discarded from our mainstream values since no one can profit from producing and distributing them. Many never bother adjusting their old standard TV sets for this reason. What's the point of adjusting it if the program was made with that quality limitation in mind. Because of the all-important financial return from television, motion pictures have been produced with the visual limits of old standard television in mind. Our entire cultural vision has been filtered downward by old television standards. If its not "as seen on TV," its not seen. With HDTV the options for new story telling grow boundlessly with the new enriched visual/aural presentations in the home. HDTV opens new doors to human expression with its extended range of stimuli. It delivers the rose in all its delicate splindor just well as it does the excitement from a violent episode. Standard television does little more for the rose than suggest its shape. The range needed for full emotional communication is delivered with high-definition television. We are going to have better television, period. I think it is safe to say that that means a better life for those willing to acquire it.</p>

<p>What about the defining events of our times? Both acts of progress and destruction are better intepreted with this new medium. What we are, as a people, is presented unerringly by it. That which polishes and that which tarnishes our cultures is sure to be spotted at a greater distance than is possible with any other visual tool in our quiver. I see HDTV lending an accent to the good--a positive contributor to value bulding. I believe that our society will look upon HDTV as it might when discovering properly corrected lenses after fifty+ years of suffering from near-sightedness. Scoundrels take note...the days of your deception and illusions may be numbered as we embrace this new view.</p>

<p>This last point truly distinguishes the renewed service promised with HDTV. It does if you believe subtleties play any part in enrichment, in enjoyment, understanding, and better decision making. Standard television filters out most of the informaton we associate with beauty. We are left with a less-attractive conclusion. We have been left looking at boulders rather than the shimmering light from diamonds.</p>

<p>One of the fastest changes coming to the world is the way we communicate in general. The written page is giving way rapidly to the visual icon. This is a form of 'communication compression,' where thesis is added upon thesis---not through tedious articles and books redescribing them--but rather distilled to within a single image representation. </p>

<p>This form of communication is particularly important where plateaus of understanding have been reached. You see this illustrated with familiar international road sign icons telling the "whole story" of what's ahead in just one picture. Further progress in future international cultural values can more easily be built upon with the greater use of such shorthand. In more complex matters both words and images will explain a new theme park concept, or a manufacturing plant to investors. Investments will increasingly result from the images of proposed future values, and these images must be good enough, clear enough, accurate enough so that the responsible managers of capital come rightly to feel confident in making their crucial investments for the environments of tomorrow. Tomorrow is all about vision--both externally, and internally.</p>

<p>Do I claim that the visual image has become the only agent for causing future investment? Of course not. Will high quality images and icons replace the spread sheet? No one says so. They will display it, however, in the clearest and most understandable way known to man. Today the best of business plans are presented with a dazzeling concoction of written documents, standard video, audio, and computer generated other images. With HDTV widely employed an entire business plan may be in the form of a finely honed audio-visual presentation, produced by desktop studios. Photographs, drawings, video images--the mltimedia panapoly--all delivered seemlessly to HDTV monitors and shown tantalizingly to large groups. Consumer-priced HDTV sets can shape the future by powerfully influencing economic decisions. Every other increase in communications facility has by magnitudes bolstered business volume. There is no reason to think that HDTV will do less. Companies, cities, states, and national secutiry has turned to the pictorialization of their future plans, and this will increasingly be done via high-resolution systems. And, when their projects are built, it's a safe bet they will have auditoriums where people can see the documentary about the project presented stunningly in an HDTV format. </p>

<p>It is being more realized today than ever before that the forests of the world are being consumed for our daily newspapers. Some have suggested the high resolution system in the home brings the finest of all solutions for this ecological dilemma. To be able to enjoy one's daily reading and graphic presentations by the television standards employed today is impossible. The present systems simply do not have enough readability. But with HDTV that problem is eliminated. We may have access to publications coming from any corner of the world. We should greet HDTV with unbridled enthusiasm if only considering these hopeful options.</p>

<p>One of the fastest changes coming to the world is the way we communicate in general. The written page is giving way rapidly to the visual icon. This is a form of 'communication compression,' where thesis is added upon thesis---not through tedious articles and books redescribing them--but rather distilled to within a single image representation. </p>

<p>This form of communication is particularly important where plateaus of understanding have been reached. You see this illustrated with familiar international road sign icons telling the "whole story" of what's ahead in just one picture. Further progress in future international cultural values can more easily be built upon with the greater use of such shorthand. In more complex matters both words and images will explain a new theme park concept, or a manufacturing plant to investors. Investments will increasingly result from the images of proposed future values, and these images must be good enough, clear enough, accurate enough so that the responsible managers of capital come rightly to feel confident in making their crucial investments for the environments of tomorrow. Tomorrow is all about vision--both externally, and internally.</p>

<p>Do I claim that the visual image has become the only agent for causing future investment? Of course not. Will high quality images and icons replace the spread sheet? No one says so. They will display it, however, in the clearest and most understandable way known to man. Today the best of business plans are presented with a dazzeling concoction of written documents, standard video, audio, and computer generated other images. With HDTV widely employed an entire business plan may be in the form of a finely honed audio-visual presentation, produced by desktop studios. Photographs, drawings, video images--the mltimedia panapoly--all delivered seemlessly to HDTV monitors and shown tantalizingly to large groups. Consumer-priced HDTV sets can shape the future by powerfully influencing economic decisions. Every other increase in communications facility has by magnitudes bolstered business volume. There is no reason to think that HDTV will do less. Companies, cities, states, and national secutiry has turned to the pictorialization of their future plans, and this will increasingly be done via high-resolution systems. And, when their projects are built, it's a safe bet they will have auditoriums where people can see the documentary about the project presented stunningly in an HDTV format. </p>

<p>It is being more realized today than ever before that the forests of the world are being consumed for our daily newspapers. Some have suggested the high resolution system in the home brings the finest of all solutions for this ecological dilemma. To be able to enjoy one's daily reading and graphic presentations by the television standards employed today is impossible. The present systems simply do not have enough readability. But with HDTV that problem is eliminated. We may have access to publications coming from any corner of the world. We should greet HDTV with unbridled enthusiasm if only considering these hopeful options.</p>

<p><br />
Not all teachers operate at the same level of efficiency. Some are truly outstanding while others merely get by. Perhaps someone here can think of what can be more important in the future than education? I cannot. When I think that our very best teachers have a classroom no more spacious than those of our very poorest, I believe the economy of modern distribution is underused, if not tortuously abused. We have the technology today to extend the best education from every field to all the classrooms and homes in the entire world--served by fully engaging advanced television systems. "Oh, that was tried before," you might say, "and it didn't work then." There are many things that will never work with NTSC. There are things that must be tried with HDTV. If the holding of student attention is achieved with high-definition, the very best teachers can become distributable to every student in the world at a fraction of the cost of employing the poorer ones. And those great teachers will be teaching well beyond their natural life span, preserved eternally in the new medium as fresh as the day they delivered their message. This noble experiment must be made to justify our claim of love for all future generations. There is no better means of creating good will in the future than providing a good education today. </p>

<p>Business will use HDTV. In the United States small business is the majority supplier to the nation's infrastructure. But small business has a difficult time in getting its products and services known to its potential customers. On average only 15% of a small company's market is reached before a product is technically obsolete. Business, especially the manufacturing segment, in the United States can and do benefit from using television to demonstrate their products, or tell of their services to potential customers. But it is an expensive distribution system that uses salespersons or the mails to carry the tapes to the customers. A wider, more cost effective distribution of business-to-business information should come soon under development. A company may reach 15 to 30% of its potential market in a single hour rather than 15% in two years. HDTV is destined for this grade of service if the view prevails that it is not expensive considering the powers of attraction it inherently has.</p>

<p>Medical imaging has been going on in large hospitals for some time. But where the need is greatest is in the outlying clinics. These clinics have not been able to afford the high resolution monitors which are large enough and clear enough to be of life-giving value. They are looking to the commercialization of HDTV to provide them the much needed lower cost higher performing displays. Beyond that, medical education is clearly enhanced by HDTV. Doctors who have experimented with this new medium have said there has never been demonstrated a more superior way to view an operation or display it for their students. Again, financial restraints become the factor in medical imaging. Where the price is lowered, the applications sprout anew.</p>

<p><strong>Tough Start </strong><br />
You have undoubtedly heard that HDTV is having a difficult time getting started. Take heart in the knowledge that every new product of worldwide significance passes through the same difficulty. The printing press was ridiculed as unworthy of the time being spent upon it by its inventor. After all, it was asked by the scribes, who could read in an illiterate world all the books just one press could print? Examine the beginnings of the telephone, radio, and television itself, to see the hardships experienced by those introducing them. In this regard HDTV is quite ordinary--consistent with the past--and all the usual obstructions will surely be overcome. So we must not be overly troubled with the obstructions to introducing anything as great as a brand new era, nor can we afford to be the least bit complacent about anything. As much as history testifies that good things do succeed, there are sad accounts filling libraries which show they can also fail. Complacency and indifference are at the root of most failures of good things.</p>

<p>There are some specifics which must be overcome in order to encourage the rest-of-the-world to advance with HDTV technologies and markets. The religious fervor, so valuable in evangelizing things, went out the HDTV movement the day two world production standards befell its fate. Two incompatible HDTV systems are not the mindset suitable to a new global era. It is a wasteful thing that painfully threatens this great development for humanity. Globally minded investors are not attracted to non-global, technically restricted enterprises. Regionally restricted television in a global era is a discord and can through us back...undermine the eager acceptance of HDTV. If I were king I would say to my engineers, "Fix it please, before the banquet begins." </p>

<p>To insure that HDTV opens up new visual industries we need to "see" a positive vision that says unashamedly that high-resolution systems are good for our cultural advancement. Each person will have to discover the reasons why for themselves. When leaders are aroused by a noble calling that impacts the whole of society, they find new energy, resources, and the resolve to master the situation, and advance like a well equipped army. </p>

<p>It has always looked like the manufacturers would need to fund the beginning and cause the first signals to seed interest in each of the markets. Sony, MEA, and Panasonic have already provided some funding to CBS, ABC, and NBC. One need not think, however, that these manufacturers are the sole economic beneficiaries of HDTV. Manufacturing companies will all change hands, managers, and locations before HDTV's day is done. The contemporary industrialist, governments, broadcasters, and shopkeepers will reap scant returns as compared to the wealth HDTV delivers to the populations of the world. That is absolutely true as far into the future as we dare look. If we don't lose sight of this, we will all act intuitively when we need to. In that we must trust.</p>

<p>Dale E. Cripps</p>

<p> </p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>June 17, 2005 10:47 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 94
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
 				AND entry_id <> 94
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/history/2005/06/1990_-_vision.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
