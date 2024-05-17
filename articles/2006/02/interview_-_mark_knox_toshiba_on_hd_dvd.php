<?
	### ARCHIVE_IND.PHP.TPL ###
	require('/var/www/html/global.php');
	
	# Get Digg URL, if one exists
	$sql = "SELECT digg_url FROM aux_mt_entry WHERE entry_id = 340";
	$result = mQuery($sql);
	$row = mysql_fetch_assoc($result);
	$digg_url = urldecode($row[digg_url]);
	
	# Author Title & Bio
	$res_author = mQuery("SELECT title, channel, img, bio_short FROM aux_author au, mt_author a WHERE au.author_id = a.author_id AND a.author_name = 'Dale Cripps'");
	$author = mysql_fetch_assoc($res_author);
	$author_title = ($author[title] == '') ? '' : "$author[title]<br />";
	
	# Get category ID
	$sql = "SELECT placement_category_id FROM mt_placement WHERE placement_entry_id = 340 AND placement_is_primary = 1";
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
	<meta name="keywords" content="mark knox, hdtv magazine, high def, def dvd, blu ray, DVD, dvd, HDTV, hdtv, Mark, mark, Knox, knox, magazine, Magazine, high, def, movie, new, people, going, hollywood, Hollywood, those, get" />
	<meta name="description" content="Just around the corner is the long-awaited launch of the HD DVD, one of two competing high-definition formats for the DVD optical disk. The stakes could not be higher for the movie business, less so for the manufacturers, and a hair pulling nightmare for the ones asked to finally pay for it all - the consumers.  

I interviewed Mark Knox last week. You will find below my lead-in. Mark has the task of explaining to you, as well as the motion picture industry, why the Toshiba-backed HD DVD is the right choice.

The current backdrop for this launch ... 
The movie business needs a smashing success using a new distribution format to restore expansion and youthful vigor to all parts of the business. They are presently plagued (in good economic times too) by a sagging box office returns and a flat-to-declining packaged goods business. I will not speak of the gamming side of entertainment here for while some ownership is common it is not entirely integrated with the movie culture.

The &quot;collapse&quot; of the box office over the last three years appears more than just a low ebb in a business cycle. Those explaining it away claim that... " />
	<title>HDTV Magazine Articles - Interview - Mark Knox, Toshiba on HD DVD</title>
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://feeds.hdtvmagazine.com/<?=$feed_name?>" />
	<script type="text/javascript">
		var digg_url = '<?=$digg_url?>';
	</script>
</head>
<body><div id="body_container">
	<?
		include(BASE_DIR .'/includes/body_header.php');
		include(BASE_DIR .'/ads/leaderboard.php');
		
		$base_url = strleftback(PHP_SELF, '/') . '/interview_-_mark_knox_toshiba_on_hd_dvd';
		$print_url = $base_url .'-print.php';
		$save_url = $base_url .'-save.php';
		$title_encoded = rawurlencode(addslashes('Interview - Mark Knox, Toshiba on HD DVD'));
		$email_url = "mailto:?subject=HDTV Magazine: $title_encoded&amp;body=http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php";
		if ($author[img] != '' && 1 != 7) {
			$img = '<img src="/images/portraits/'. $author[img] .'" alt="Dale Cripps" />';
		} else {$img = '';}
	?>

	<!-- Article Header -->
	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%;">
		<tr>
			<td rowspan="2" style="padding-right:5px; vertical-align:top"><?=$img?></td>
			<td class="article_title" colspan="2">Interview - Mark Knox, Toshiba on HD DVD</td>
		</tr><tr>
			<td id="article_byline" nowrap="nowrap">
				By <b>Dale Cripps</b><br />
				<?=$author_title?>
				Posted on <b>February 22, 2006</b><br />
				Category: <b><a href="/category.php?id=<?=$category_id?>&category=Marketplace">Marketplace</a></b><br />
			</td><td id="article_links">
				<!--span><img src="/images/digg.png" alt="Digg Article" align="absmiddle" /><a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php&amp;phase=2">Digg</a></span-->
				<span><img src="/images/newsvine.gif" alt="Add to Newsvine" align="absmiddle" /><a href="javascript:addToNewsvine('http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php', '<?=$title_encoded?>')">Newsvine</a></span>
				<span><img src="/images/delicious.gif" alt="Add to Del.icio.us" align="absmiddle" /><a target="_blank" href="http://del.icio.us/post?url=http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php&amp;title=<?=$title_encoded?>">Del.icio.us</a></span>
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
				echo '<a target="_blank" href="http://digg.com/submit?url=http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php&amp;phase=2&amp;title=Interview%20-%20Mark%20Knox%2C%20Toshiba%20on%20HD%20DVD&amp;bodytext=Just%20around%20the%20corner%20is%20the%20long-awaited%20launch%20of%20the%20HD%20DVD%2C%20one%20of%20two%20competing%20high-definition%20formats%20for%20the%20DVD%20optical%20disk.%20The%20stakes%20could%20not%20be%20higher%20for%20the%20movie%20business%2C%20less%20so%20for%20the%20manufacturers%2C%20and%20a%20hair%20pulling%20nightmare%20for%20the%20ones%20asked%20to%20finally%20pay%20for%20it%20all%20-%20the%20consumers.%20%20%0A%0AI%20interviewed%20Mark%20Knox%20last%20week.%20You%20will%20find%20below%20my%20lead-in.%20Mark%20has%20the%20task%20of%20explaining%20to%20you%2C%20as%20well%20as%20the%20motion%20picture%20industry%2C%20why%20the%20Toshiba-backed%20HD%20DVD%20is%20the%20right%20choice.%0A%0AThe%20current%20backdrop%20for%20this%20launch%20...%20%0AThe%20movie%20business%20needs%20a%20smashing%20success%20using%20a%20new%20distribution%20format%20to%20restore%20expansion%20and%20youthful%20vigor%20to%20all%20parts%20of%20the%20business.%20They%20are%20presently%20plagued%20%28in%20good%20economic%20times%20too%29%20by%20a%20sagging%20box%20office%20returns%20and%20a%20flat-to-declining%20packaged%20goods%20business.%20I%20will%20not%20speak%20of%20the%20gamming%20side%20of%20entertainment%20here%20for%20while%20some%20ownership%20is%20common%20it%20is%20not%20entirely%20integrated%20with%20the%20movie%20culture.%0A%0AThe%20%22collapse%22%20of%20the%20box%20office%20over%20the%20last%20three%20years%20appears%20more%20than%20just%20a%20low%20ebb%20in%20a%20business%20cycle.%20Those%20explaining%20it%20away%20claim%20that...%20&amp;topic=television"><img src="/images/digg-this.gif" alt="Digg This" style="padding:0"></a>';
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
			<p><img alt="J.jpg" src="http://www.hdtvmagazine.com/articles/Image/J.jpg" width="18" height="24"align="left"/>ust around the corner is the long-awaited launch of the HD DVD, one of two competing high-definition formats for the DVD optical disk. The stakes could not be higher for the movie business, less so for the manufacturers, and a hair pulling nightmare for the one's asked to finally pay for it all - the consumers.  <br />
<img alt="dalesmile.jpg" src="http://www.hdtvmagazine.com/articles/Images/dalesmile.jpg" width="88" height="128"align="right"/><br />
I interviewed Mark Knox last week. You will find the interview below my lead-in. Mark has the task of explaining to you, as well as to the motion picture industry, why the Toshiba-backed HD DVD is the right choice.</p>

<p><strong>The current backdrop for this launch ... </strong><br />
The movie business needs a smashing success using a new distribution format to restore expansion and youthful vigor to all parts of the business. They are presently plagued (in good economic times too) by sagging box office returns and a flat-to-declining packaged goods business. I will not speak of the gaming side of entertainment here, for while some ownership is common, it is not entirely integrated with the movie culture.</p>

<p>The "collapse" of the box office over the last three years appears more than just a low ebb in a business cycle. Those explaining it away claim with fading conviction that even with these declines there is nothing fundamentally wrong with the business. People still love movies just as much as they ever have and the sky is not falling. It is only a matter of getting back in synch with the public's mood. But 'sorry celluloid' has plagued the big screen before in the post-TV era without sending the box office so steeply into decline. </p>

<p><strong>Why now? </strong></p>

<p>For one ... it's getting old. The film business recently celebrated its 100th birthday. While it papers its walls with pretty young things, it is no longer moved by its dynamic founders with their strong personalities, creative zeal, penetrating insight, risk-taking capacities, and legendary decisiveness. It is managed today by the tight fisted, well-calculated disciplines of Corporate America where shareholder interest is first and foremost in mind, well above the ethereal domains of creativity. Some think that is good, for they will have to turn events over to the more creative young people in Hollywood and stand aside as the business evolves. Even before the corporate takeovers, special effects were used to overcome weak script choices ad nausea. Computer graphics, while often artful (and we can admire and appreciate that), produce little adrenalin unless used in an interactive game environment. The War of The (yawn) Worlds didn't war, King Kong died from a mouse move, and Godzilla had digital clones.</p>

<p>But even more significant to this part of the story is the fact that the ticket buying public of a few years back is now a digital citizen of the international electronic frontier. Minute-by-minute, day-after-day, a huge proportion of them live a significant part of their free time online and order down what they want NOW from a list of categories not even Hollywood has the guts to exploit. There are addiction rehab centers for those hooked by the adrenalin rushes.  (Not one center is to be found for traditional movie goers--the makers of movies, yes). It is evident that modern alternatives, such as Xbox and Play Station, have drained theaters of patrons and left DVD titles to linger on the shelves until discounted. One would think that a more worrisome fact than any is how the theater itself seems out-of-date to the cyber generation. While my analogy may be a stretch, it is like Vaudeville was to the TV generation. How can Hollywood hope to compete with an online passport to the world? Perhaps with digital projection and game controllers in every seat, a hint of what the future will be in movie theaters is revealed.</p>

<p><strong>Can Be Tough Sledding...</strong></p>

<p>While acknowledged as an important element in the next evolutionary chapter of Hollywood, the high-def DVD has tough sledding ahead before benefits can accrue. The biggest hurdle is the growing consumer reaction to high-def DVD studio policy. A backlash turning into an out-and-out resentment is festering, first, and most widely, over the fact that two high-def DVD formats are being rolled out at about the same time. "Why introduce TWO at the same time? Are you <em>craaaaazy</em>?" The consumers are up in arms about this confusing conundrum. </p>

<p>The second is the copy protection measures mandated by the studios. Those measures, or at least what the public thinks those measures are, have enraged the community. Groups are sounding as if they are ready to march on Washington and picket and lobby anyone and everyone against the "draconian" digital rights management measures incorporated in the AACS specification. Blogs and print publications fan the flames of this anger every day. The concern is so great in Hollywood that many fear this consumer reaction will mean that the high-def format--either one--will fail to reach the level of general use. That would set plans back in Hollywood a serious notch.</p>

<p><em>"But how in the world could it fail?" you ask. "Everyone is and has been anticipating it for years. We all love HDTV. So what's behind this question?"</em></p>

<p>From the perspective of comparative picture qualities, the high-def DVD is only modestly more compelling than the standard DVD version. OK, let me hastily qualify that statement since we are all biased towards HDTV. HDTV is what we want, but this market is more democratic than doing just what we want.</p>

<p>No less a major player in the high-def movement than David Niles in New York (the FIRST person to own any HDTV production equipment back in 1986) said years ago that DVD quality for movies is quite enough and the addition of HDTV does very little for the experience. If that isn't enough to jar you then consider the new scaling DVD players coming out at a fraction of the cost of high-def models. The image improvements to be made from a well mastered DVD serve to narrow the image quality distinctions even further. For years Yves Faroudja demonstrated just how much more video information is extractable and deliverable to the screen by using superior components and processing algorithms. </p>

<p>Of course, picture assessment is heavily, and I repeat <em>heavily</em>, dependant upon your viewing distance and the display performance itself. But if the spread between the new and the old format is perceived to be too slight, it will not have the market power to overtake the old standard. The CE landscape is strewn with failures that did not pass the 10 JND test (Just Noticeable Difference-units on a scale used in marketing assessments). </p>

<p>The Super VHS, while better by most all accounts, was still not enough better to overcome the price/performance value proposition of standard VHS, at least in the view of the all-important general public. As a consumer format it died for lack of sufficient comparative distinction. Beyond or besides picture quality, the ten 'Just Noticeable Differences' which is counted on for carrying the new high-def DVDs to victory comes in a large measure from the new set of viewer/users options. Those are first invisible to the customer reviewing the device in a retail setting. The features, which may prove essential perceived values in producing enough market tension to overwhelm standard DVDs will require some behavior changes before they can even be recognized as values. Nor will they be evenly assessed for some have no association with the present feature set on the present DVDs. They watch a movie and it goes back in the jewel case until next viewing. When it comes to behavior mofifications of consumers I always hear the echo of the late, great Howard Miller, former chief engineer for PBS and earlier with Westinghouse. As a young man Howard drove the Westinghouse interactive cable campaign. That was back in the 70s. It failed <em>miserably</em>. When asked why it failed so badly (it was not due to the technology) Howard replied, "Never trust a business plan that for its success requires your customers to change their behavior." </p>

<p>Getting high-def DVD player features known will be a major challenge to the retail sector. When Philips introduced Digital Video Interactive (DVI) in the mid 90s, retailers were trained extensively and long and expensive infomercials were also produced and shown repeatedly for months on end. In all the retail stores I visited during that rollout, none of the sales staff were paying the slightest attention to this new "consumer electronics wonder" and customers passing by had pushed so many buttons trying to get it to do something that they locked up the system, making it totally unusable for anyone else passing by. The president of Philips bet his career on the success of that format. He lost. </p>

<p>I learned that the reason the sales people paid no attention to the DVI was due to its rich features. Had it just played a movie they would have had no problem in selling it. But it took far too long to explain the use of the features. With the attention-span of the customer being so short in such an environment if he/she didn't quickly recognize a feature that they already wanted, interest was lost and they moved on. Salesmen quickly determined that it was not worth the time they had to spend on educating anyone and abandoned it--an abandonment which cost Philips hundreds of millions of dollars. Of course, since both of the new high-def DVD formats are backwards compatible, one easy solution to selling it is to stop making standard DVD players and let attrition take care the penetration problem and make all new programs available only on hybrid disks that play both the HD version and the SD version. That would take a whole lot of agreement...and we see how agreements go in this field.</p>

<p>Another very real dark cloud hanging over the success of either high-def DVD format is the sad fact that most of the early adopters of HD equipment have been pushed out of the first high-def DVD market by copyright measures that can (at the option of the studio) stop the player from sending full HD quality to the display on component cables. </p>

<p>Those vocal 5 or 6 million early supporters so impacted led all of the rest to HDTV and are now being severely discounted, causing them to voice anger directed to the studios. 6 million analog component-only sets can, but don't have to, be limited to playing SDTV. The only mitigating circumstance here is that most early adopters of means quickly become addicted to HDTV and so have acquired a second and third later vintage HDMI-equipped set located somewhere in the house. But this has put another clamp on things because the owners of the newer 1080p displays now want 1080p signals out of the player and HDMI does not yet have that as a mandatory inclusion and Toshiba, for one, won't make a 1080p out until that standard is codified and is enforced as mandatory. </p>

<p>High-def DVD will, of course, succeed where huge corporations manage their destiny; the Blu-ray will go into the Sony PlayStation 3, while the HD DVD is destined for Microsoft's Xbox 360. The computer industry also wants a cheap new data storage scheme added soon. They already have sample lots from both camps and will soon go to market when more powerful processors are available. But what we are talking about here is a mass market which will meet enough conditions to succeed in holding the attention of all concerned. </p>

<p><br />
<strong>There are other problems impacting the launch....</strong></p>

<p>As a movie lover, I want to see Hollywood succeed and, as an HDTV owner, I want one of these high-def DVD formats to succeed. I used to care which one, but now I really don't. But Hollywood's rebirth is actually problematic. Any strategist will tell you that there is danger when a business reaches maturity (or old age) for no matter what is done it will fail because so much weighty legacy conditions hold it back from a total reinvention of itself. When it reaches its apex when decline is inevitable it begins to adapt with a new animation to what it perceives the future to be and it grows expansive in what it does in order to keep attention focused upon it for as long as possible. But it has too many old brittle bones to carry and so exhausts itself in the try. Historians will tell you that every movement, no matter how deeply rooted, that ceases to expand will turn and collapse in upon itself and finally disappear down a hole like the white rabbit. </p>

<p>That is why the stakes in high-def DVD authoring, distribution, and consumer acceptance are so high. The movie business needs to grow and live creatively again, even like a child's spurting through anxious adolescence, and it has to do it not only by adaptation to the future but by being part of the invention of the future where their place is carved out with their own hand. In effect, it has to die (shed the past entirely) and be born again. HDTV is the second coming of television just as it is proving to be for the motion picture business. The high-def DVD is new blood in the veins of Hollywood. Hollywood needs a new distribution model and that is enabled by a new class of technology in the hands of its customers--you and me. This same technology--the high-def DVD--will give Hollywood (and all other content providers attracting our attention) the room to invent and introduce to a huge paying audience new kinds of products which the talent of the world has been unleashed to invent. </p>

<p>But is all of this important enough to Hollywood executives that they will mend their fences with us citizens who, in the end, have our finger on the "Go, Pause, and Stop" buttons, and get us in harness to pull this high-def DVD format to victory? The way it sits now is that we, Hollywood's customers/partners, are going to suffer from the format wars. Some of us are going to lose money and time (who knows what else) by buying the wrong thing. Both camps have very compelling stories and we should never blame ourselves for miscalculating. But this lack of industry accord is far too amateurish for the management team of such a huge and significant public servant as is the entertainment field working in the 21st century. "Let the healthy competition be with the manufacturers and not formats," you will say, "We don't want to decide for Hollywood! That's their job. They have the power and should do it. Our job is to enjoy their products and pay a fair price for them. We can't make policy decisions for an industry we actually know very little about." </p>

<p>All-in-all, the Hollywood studios do understand that they have created a very serious PR problem with the copyright protection measures and there is a huge amount of misinformation floating around about fair use. It is a dilemma which can cost them hundreds of millions of dollars in lost revenues if, in our anger, we boycott or retreat from their products. It looks like a conundrum with no remedy except that which is in their hands...and the switch is in their hands. And if we are good citizens ourselves and don't mess around with infringements on legal copyrights they nor anyone have a need to throw that switch nor even have one. _DC</p>

<p><strong>And now we hear from ... </strong></p>

<p>Mark Knox ... who was called in by Toshiba, the developer of the HD DVD, as chief spokesperson for their March '06 launch. His duty is to explain and sell the system to the world. You will learn in this interview how HD DVD intends to lead the market and then we will have a serious discussion about digital rights management-the divisive producing so much anger in early adopters of HDTV.</p>

<p>In coming days we will interview the Blu-ray spokesperson to see how the grass grows on their side of the fence. But now ... </p>

<p> ... THE INTERVIEW with Mark Knox<br />
By Dale Cripps</p>

<p>Mark Knox is an independent consultant and writer from Union County New Jersey. He is a 25 year veteran of the Electronics Industry who <img alt="mark_knox_single.jpg" src="http://www.hdtvmagazine.com/articles/Images/mark_knox_single.jpg" width="146" height="207"align="left"/>has been instrumental in the launch of many new product categories, including the first true Digital Pianos, Dolby Surround Sound, CD recording, Satellite Television, DVD, HDTV itself, and now HD DVD. He has been consistently active in the Consumer Electronics Alliance, having served on various product Division Boards as well as the Technology and Standards groups. In 1996, he received the "Digi" award from Wideband Magazine, recognizing his contributions to the Digital Products industry. Mark lives in New Jersey in a very old house with a very new wireless network along with his wife Kat and five other felines. He frequently enjoys watching terrestrial and cable HDTV, listening to high performance Audio and serenading his wife and the other cats with his guitar and keyboards.</p>

<p>I opened by noting that <a href="http://www.netflix.com/PressRoom?hnjr=8">NetFlix had made a commitment to support HD DVD</a>.</p>

<p><strong>HDTV Magazine:</strong>  We see that you have Netflix in your corner.</p>

<p><strong>Mark Knox:</strong> A few of their technology people hung out at the HD DVD promotion booth at the CES (January 2006) peppering me with very detailed technical questions.</p>

<p><strong>HDTV Magazine:</strong> What were the more penetrating questions?</p>

<p><strong>Mark Knox:</strong> Their primary interest was in durability. They ship DVDs all over the country. They needed to know what the reliability of the HD DVD format is when mailed, punched, sat on, dropped, heated and cooled, etc.</p>

<p><strong>HDTV Magazine:</strong> How did you respond?</p>

<p><strong>Mark Knox:</strong> I said the basic core materials are exactly the same as with the DVDs they ship now. The only difference is how big the bumps are in the middle of the disk. So, they should hold up at the same rate in the same way as do DVD disks today.<br />
 <br />
<strong>HDTV Magazine:</strong> Would that be any different than with Blu-ray?</p>

<p><strong>Mark Knox:</strong> It is more a matter of what Blu-ray will need to do to reach that same level of reliability. I am not going to claim that Blu-ray disks are going to scratch much more easily than ours. I understand they found the world's most expensive "Armoral" to protect the skinny layer. That may help. Our engineers were afraid of two materials bonded together with one thin and one thick. When they heat or cool they will not expand and contract at the same rate, which means that the disks could warp. Even if that warp were imperceptible to our eyes, it is not a small matter for the Blue Rey pickup tolerances.  I won't pretend that they can't fix those issues. They will have to do homework which the HD DVD does not have to do.</p>

<p> <strong>HDTV Magazine:</strong> Do you believe that HD DVD is heading to the winner's circle? If so why?</p>

<p><strong>Mark Knox:</strong> We have a long way to go before there is a winner's circle. We are sitting at the post position waiting for their race car to arrive. If it were a boxing match I would ask if I could put my warm-up jacket on until they arrive. I won't, by any stretch, however, claim that the game is over.  </p>

<p><strong>HDTV Magazine:</strong> Do you see your "first-to-market" as being a significant advantage to gaining a commanding lead? </p>

<p><strong>Mark Knox:</strong> I think it is an advantage. How significant will depend on the answers to some of the open questions about our competitors. If PS 3 really does ship in the Spring and can play Blu-ray movies our market advantage is less than if PS3 ships without a commitment to play Blu-ray movies. It would be a greater advantage for us if f PS3 doesn't ship until the fall.</p>

<p><strong>HDTV Magazine:</strong> Do the consumers stand to get hurt in this powerful contest?</p>

<p><strong>Mark Knox:</strong> The greatest damage is from the fact that we didn't reach one conclusion as we did with the DVD.  Unfortunately, a large number of consumers and publications have said not to plunk your money down until the dust settles. That is going to mean many consumers now enjoying high-definition from satellite or from cable are not going to watch their favorite movies until that dust settles. That could be a long time. That would be one disadvantage to the consumer. </p>

<p>I think one advantage we enjoy is that we are asking the consumer to (only) take a $500 gamble. We are saying, "Buy a (HD DVD) player for $500. A long list of movies insures that you will have content to play with this player. You will definitely be able to play all of your old movies (standard DVD) using it". </p>

<p>For many titles, Warner, Universal and others have said they are going to release hybrid discs for new releases. When you buy that movie (hybrid) you can also play it in your old DVD players. So, I think there is a big advantage in that all we are asking them to do is plunk down $500. That is not really much money considering what a new DVD player cost in 1997. That was a pretty pricey proposition.</p>

<p><strong>HDTV Magazine:</strong> I recall that the first VCRs were $2400. </p>

<p><strong>Mark Knox:</strong> I remember thinking what a hot screaming deal I got when I bought my Panasonic 6 motor, four headed monster for $800.</p>

<p><strong>HDTV Magazine:</strong> Let's go back to some of the basic concerns we have. For the general population-the mass audience--is there enough difference between a well mastered DVD when compared to an HD DVD disk of tomorrow? </p>

<p><strong>Mark Knox:</strong> Yes, for two fundamental reasons. </p>

<p>One - the picture quality itself: One idiosyncrasy that I have noticed when talking to general consumers and relatives is that those who own an HDTV begin to dislike the standard definition feeds. Even though the TV is scaling the standard def up to some approximating high-def, most consumers immediately recognize that the picture doesn't look as look as a high def source.  </p>

<p>We have produced demo content soon to be available where one piece of content was simulated digitally to show the difference between HD and SD. Instead of dividing the screen in the middle we moved the division left and right across the screen so one can look at exactly the same content in SDTV and HDTV. </p>

<p><strong>HDTV Magazine:</strong> Does this hold up when the observer is not at the prescribed viewing distance for HDTV? </p>

<p><strong>Mark Knox:</strong> Yes it does. Obviously, one of the issues with it is distance and screen size and resolution. When you are below a certain screen size, the consumers won't be able to see the difference between the two resolutions.</p>

<p><strong>HDTV Magazine:</strong> What is that screen size (where this makes a difference)?</p>

<p><strong>Mark Knox:</strong> Toshiba engineers ran a series of research projects on the screen size issue to determine what the sweet spots are. The other issue is that consumers typically "over-screen" for the size of their rooms. They have to have a bigger TV than Joe has. When below 20 inches, most people cannot tell you which is the SDTV and the HDTV. The Consumer Electronics Association forecast that from 2004 through 2009 for HDTV only (Direct View, CRT, any form of Micro Display, Direct View LCD, Plasma at 720p or greater) an accumulative 100 million units will be installed. Even if we never sold one unit of HDTV before 2003 some 97% of households would have high-definition at home by 2009. The break point based on today's technologies is that anything below 20" is not going to be HDTV. Beyond that depends upon the manufacturers. By the time you get to 30", with the exception of low priced Plasma, everything will be HDTV. </p>

<p><strong>HDTV Magazine:</strong> NHK, the original developers of HDTV, claimed that you don't get a real payoff until you reach 60 inches. </p>

<p><strong>Mark Knox:</strong> Our engineers determined that once you get to 25" people begin to recognize the difference between standard def and high-def, whether that is 720p or 1080i. Once you get to 36" a lot of consumers claim to see a difference in the quality between 720p and 1080i. Once you get to 40 inches there is a delta between the quality of 720p and 1080i, but 720p doesn't really take a nose dive until you get to that 60 inch point.  That may be what NHK is referring to. </p>

<p><strong>HDTV Magazine:</strong> The good news from the LCD display camp is that adding pixels is inconsequential to cost.  So, it would appear that we are going to have more 1080p (in LCD) and as the new fabs come online (with their improved materials processing and handling) they will be bigger and bigger. </p>

<p><em><strong>Copy Protection ...</strong></em> </p>

<p><em>According to the most recent estimates available from the International Federation of Phonographic Industries (IFPI), the movie industry lost more than $4.5 billion worldwide to physical piracy in 2003.</em> _MPAA.org web site.</p>

<p><strong>HDTV Magazine:</strong> One of the rancorous questions our people keep asking is about digital rights management and the likelihood of their component connections being down-graded. How do you respond to this? </p>

<p><strong>Mark Knox:</strong> AACS made an official announcement recently of an agreement. Being engineers they assigned an unfriendly acronym to this agreement. It is called an Image Constraint Token. </p>

<p>It is the same concept as the Broadcast Flag. But the important detail here is that it is not mandatory for any content owners to turn that flag on. </p>

<p><strong>HDTV Magazine:</strong> Yet it is the content owners who wish this control to be incorporated. How does that square with what you are just saying?</p>

<p><strong>Mark Knox:</strong> It is some of the content owner's wishes to control content to the analog outputs. An important thing to recognize is this: Some significant content owners who participated in those AACS negotiations offered, in return for the key handed to them by the manufacturers, greater flexibility in things like managed copy.</p>

<p><strong>HDTV Magazine:</strong> What is it and how does 'managed copy' work?</p>

<p><strong>Mark Knox:</strong> Managed copy in HD DVD means that every single disk must offer to the consumer the ability to make a legal encrypted copy of his/her disk in another location, most typically a media center PC. </p>

<p><strong>HDTV Magazine:</strong> How is that controlled?</p>

<p><strong>Mark Knox:</strong> Let's say you put a copy on your media center PC. That copy is still encrypted. In order to decrypt it you need a number of keys. One is the content key which is stored along with the (original) media. Another is a key which is unique to that PC derived from some unique element of that PC.</p>

<p><strong>HDTV Magazine:</strong> What about my summer house?</p>

<p><strong>Mark Knox:</strong> Several answers depending on your scenario. </p>

<p>If you have a portable device you can connect it to that same PC and use the managed copy to create a mobile copy (which you can play back on that mobile device). You can do that as long as that mobile device is able to "hand shake" with your media center PC (so they can hand the keys off). If that data file with the encrypted video were to be given to some other entity which won't connect directly the playback device to your computer (needed to successfully acquire all of the necessary keys), then you cannot make the copy. </p>

<p><strong>HDTV Magazine:</strong> And do you think the public is going to earn their PhD in advanced cryptology with all of this?</p>

<p><strong>Mark Knox:</strong>I think the process-the pain for the consumer to perform some of these operations-is going down. I agree that it is far from zero, but it is going down.  </p>

<p><strong>HDTV Magazine:</strong> Undoubtedly so, but ignorance doesn't vanish quickly.</p>

<p><strong>Mark Knox:</strong> We could facetiously say that the acronym for DRM stands for 'Deal Required by Movie Makers.' To some degree we are constrained. We have to recognize that the majority of content owners selling high-def movies are American owned companies and the majority of those selling hardware are not!  It is not like we can look to Congress to pursue the Hollywood community to drop their request. But it is also true that not all of the Hollywood content providers are going to throw that switch.  </p>

<p><strong>HDTV Magazine:</strong> Do you have any 'enlightened" views as to what the importance of copy protection and controls is to the over-all economy of Hollywood? </p>

<p><em><strong>The MPAA posts on its web site their definition of a pirate:</strong>Anyone who sells, acquires, copies or distributes copyrighted materials without permission is called a pirate. Downloading a movie without paying for it is no different than walking into a store and stealing a DVD off the shelf. Motion Picture Piracy is committed in many ways, including via the Internet through downloadable files, selling pirated DVDs on the street or capturing and redistributing live broadcasts or performances without a license on the Internet. Downloading movies and music without the authorization of copyright holders is a growing international problem that presents serious challenges for the movie industry and has serious legal consequences. </p>

<p>People often download movies on the Internet because they believe they are anonymous and will not be held responsible for their actions.  They are wrong.  The illegal downloading and swapping of movie files is a serious crime.  Pirates and their affiliates can and will be tracked for engaging in Internet piracy.</em></p>

<p><strong>Mark Knox:</strong> There is no question that robust digital rights management (DRM) is a requirement from many members of the Hollywood community prior to their submitting content.</p>

<p><strong>HDTV Magazine:</strong> But is this a paranoid reaction or is there hard evidential reality behind it?</p>

<p><strong>Mark Knox:</strong> I will say that some, though not all, understand that the occasional guy making a copy for his personal use is not where their problem lies. Their issue is vast quantities of pirated copies being made and sold. The fact that you can see brand new movies on tropical islands using a projector displaying on a sheet hanging from the palm trees cannot be ignored. That is the issue they are very concerned about, hence the whole digital cinema initiative (the idea where movies would never go to any packaged media or film but would be distributed by a secured network right to the theater.) </p>

<p><strong>HDTV Magazine:</strong> I talked to MPAA representatives a few years ago. At that time the copy protection measures were said to be for discouraging ordinary people who without a constraint might copy a movie for their children (away at school, for example), or a neighbor, or friends at work. Those in turn, went the scenario, would feel free for doing the same until a viral-like epidemic had occurred. That would leave the movie so widely distributed that any residual values enjoyed by today's stair step marketing structure would be lost.  It was not, as I understand it then, the fear of dedicated and organized pirates that drove this particular initiative. That view may have changed since then. Here is what the MPAA now posts on its web site: </p>

<p><em><strong>WHAT IS OPTICAL DISC PIRACY?</strong></em></p>

<p><em>Optical disc piracy is the illegal manufacturing, sale, distribution or trading of copies of motion pictures in digital disc formats including DVD, DVD-R, CD, CD-R and VCD. These illegal hard goods are sold on web sites, online auction sites, via e-mail solicitation and by street vendors and flea markets around the world. Much like downloadable media, the pirated motion pictures in hard goods format are typically poor-quality video-camera recordings.  </p>

<p>While the majority of pirated optical disc products seized by law enforcement worldwide are made on advanced commercial replication lines, the low cost of disc burning hardware and blank discs has led to the proliferation of DVD-R and CD-R burner labs.</em></p>

<p><br />
<strong>The MPAA continues...</strong><br />
<em>Organized crime networks incorporate the use of threats, violence, intimidation and corruption to establish and maintain control. When you engage in piracy, you may be supporting these networked criminals.</em></p>

<p><u>Editor's Note:</u> How is the public to resist a gun to the head by organized crime and how is organized crime not going to have the keys to unlock any content they want to use in any way they see fit? The key to stopping this particular criminal activity is to see that the citizens of the world are led to not buy any pirated products. A movie is not crack cocaine and an addiction to the singular benefit of receiving a lower cost for an early release is easy to overcome. Those measures which will benignly discourage the public's interest and participation in this crime are the right measures to take. _DC</p>

<p><strong>Mark Knox:</strong> They do have legal recourse where piracy is concerned and there is a lot of forensic help built into the system to help the studios. And yes, you are right. They are concerned about mom and dad and sis and brother-in-law and that home (copying) syndrome. That is one reason the hybrid disc is such a popular concept with the studios.</p>

<p><strong>HDTV Magazine:</strong> Can you elaborate on the Hybrid disk?</p>

<p><strong>Mark Knox:</strong> There are two flavors. There is the one disk that contains both the standard DVD version and the high-def version on the same physical media. There is the single layer version that has one DVD layer and one HDTV layer on one side with the label on the other side. Then there is a double sided version which has two DVD layers on one side and two HD DVD layers on the other side. At a meeting in Hollywood, one of the replicators asked if it would not be cheaper to make two disks?  Two studio executives responded in near-unison saying, "It might be cheaper, but it's not better. We don't want them to give the standard version to their sister". So, with all on one disk you don't undermine the potential sale of yet another copy. </p>

<p>The sad reality is this: If we were not willing to acquiesce to the request coming from some of the studios to give them this ability to "plug the analog hole" the odds are that those studios would not be delivering content to the format. I do think that there are market forces in place, and you represent one of them, to impact these decisions. If two movies of equal standing to consumers come to market and one of them has the flag turned on and the other doesn't ... what is the response of the consumer (and editors like yourself) going to be to that fact? Even if some studios are adamant and throw the switch on the early releases, the market is going to have a say as what subsequently they do. </p>

<p>As far as the consumer's awareness is concerned there will be "language" in the player noting that the flag is "on".</p>

<p><strong>HDTV Magazine:</strong> Are you saying to the public that the component out to my early model HDTV will have an on-screen notification which says that the program has been down-converted even though I may have paid a premium for an HDTV disk?.</p>

<p><strong>Mark Knox:</strong> I fully understand the public's concerns. In this case the studios are deciding to disenfranchise a fairly large number of HDTV television owners (estimated at 6 million who are without HDMI or DVI connections).  </p>

<p>Unlike in 1997, when we launched the standard DVD, the industry's business structure was quite different than it is today. Many of the studios were independent then and you could appeal to the most senior management of the studio and need to go no further. That is not the case today. The senior studio people have to answer to one who is now over their heads and that one is not a Hollywood entity but rather a corporate one. That tends to place restrictions on things. </p>

<p>I do think the reality is that some studios will throw the switch (for down converting) and they will get beaten up in the press and by consumers. Because of the Internet, the power of the consumers is no small matter. I think many of those studios will ultimately change their tune.</p>

<p><strong>HDTV Magazine:</strong>  In his final days at the MPAA Jack Valenti spoke out on something we had urged, which is to recognize that society has at least some responsibility in producing or eliminating the need for these protective measures. No bank is without its steel vault and no one questions why. As the good guys we know that a certain percentile of our fellow citizens violate rules and so prisons are built. The collective price for their crimes is in the billions of dollars and more locally enforces endless inconveniences upon all of us. They are regrettable but understandable consequence of the way things have become in our social system. But when it comes to software all of that understanding seems to vanish. </p>

<p><strong>Mark Knox:</strong>The sad thing is that there are a lot of very knowledgeable people who are capable of making very compelling arguments, but they restricted by their management in delivering those messages in full. It is a fact that the theft of intellectual property is a fundamental problem in the United States. It is a huge challenge, but the pedestrian reality for this case in hand is, "Yes, I am sorry. There is going to be some case where you have an older HDTV and you will not be able to watch your movie in high def as much as you might like to."</p>

<p><strong>HDTV Magazine:</strong> And those are going to be the most vocal people in the nation who were the early adopters of HDTV technology and who are now screaming at the top of their lungs that they faultlessly pioneered the field only to now be abandoned and discarded while their HD investments are destined to operate undiminished for years to come. </p>

<p><strong>Mark Knox:</strong> And, I might add, they tend to be your most ardent readers. </p>

<p><strong>HDTV Magazine:</strong> There are also people who are now wanting to buy 1080p monitors with 1080p inputs but they are finding that not so easy. Why?.</p>

<p><strong>Mark Knox:</strong> There are more TVs with P on the front than connectors that can receive P in the back. It is an issue of standards that are evolving. Here is what I understand as of today (I am told I will learn more on an upcoming trip to Japan). We will develop documentation in Japan that we can deliver to you later explaining all of this issue. But here is what I now understand and then I will explain to you what I don't understand but hope to learn.  </p>

<p>The current approved standard for HDMI is Ver 1.1. Within that version there are several mentions of some frame rates for 1080p, but they are listed as an optional specification. What that means is that if you are building a product you are allowed to support 1080p, but only at certain frame rates. But you are not required to support 1080p.</p>

<p><strong>HDTV Magazine:</strong> What is required?</p>

<p><strong>Mark Knox:</strong> That would be 1080i and below. Then there is a similar story when it comes to the higher resolution and lossless audio codecs. In Toshiba's case there is a very clear set of internal regulations for hardware. When they release a product it can be based only upon an existing mandatory specification. It is a very easy matter for you to allow the player to scale to whatever is native on the disk to 1080p at 24, 30 or 60 fps. But, if you do that there is no guarantee that the receiving device is going to know what to do with that data since with the current Ver 1.1 of HDMI there is no requirement that any device has to support any of those formats. For that reason, and the fact that Ver 1.1 will be the only certified standard for at least some months-well after we launch hardware in March-we are not willing to put 1080p output over the HDMI connector. </p>

<p>The other issue, where my own CPU speed begins to fail me, is with respect to the telecine process used. The raw data format for HD is very different than what was used for DVD. Even though DVD generally was captured in high-def and then processed down to standard def, the new telecine format is very different.  It is related to the fact that you have more than one codec to play with. I am promised an education on exactly what those data formats are when in Japan. The telecine people have been involved with HDTV for some time now and are a bit ahead of where we are. They are dealing with both MPEG 2 and MPEG 4 or VC1. Because of the technology behind that there was the desire to change the telecine process. The raw data format is, in turn, deciding what native format will be on the disk. That in turn is deciding what we will do when we get to the player. I have only dangerous bits of information so far.</p>

<p><strong>HDTV Magazine:</strong> We have on our forums some very knowledgeable and influential people. They have made it known that they will not make this move to upgrade to any HD DVD format until they can be assured of 1080p end-to-end. </p>

<p><strong>Mark Knox:</strong> I understand that completely. That is why the current plan from the HD DVD Promotion Group is to send me to all of the engineers who understand everything perfectly. When I am at full knowledge I will develop a document and distribute to you and the others in the press. It will say clearly, "This is the way it works from end to end."  </p>

<p><strong>HDTV Magazine:</strong> It looks to me as if we are about to introduce with the high def DVD a completely new set of confusing conditions to further confound the public on HDTV. What is being done in your circle to minimize this confusion, if anything?</p>

<p><strong>Mark Knox:</strong> There are a couple of initiatives. One, if it doesn't sound too bold or optimistic, is that under inspiration from other members of the HD DVD group, Toshiba decided they needed someone to be a dedicated spokesperson with no other distracting responsibilities for the format. That is why you and I are having this conversation.</p>

<p>Second, is the member companies, and most obviously, Toshiba, have invested a lot of time and resources in material which is being developed as well as web site assets that are dedicated specifically to HD DVD. When a consumer goes to the Toshiba web site right they can get some preliminary information. That will be flushed out quite a bit. Another set of materials consist of "white papers" which will be released soon. I have made the final translations. That document will also be a primer on the technology. </p>

<p>But I think the most important force that will be assisting the consumers is our retail partners. They have been pushing us on how to best present the answers to the nagging questions. Whether it's the guys at Amazon, Crutchfields, or Best Buy, or the Tweeter Group, all recognize that it is going to be consumer confusion. </p>

<p>One of the fundamentals to the HD DVD approach is that because the format allows multiple codecs but multiple video streams because the HD DVD platform requires multiple video memories. It means that you can do things in parallel process that will make operation of the content easier than it is now in a traditional DVD.</p>

<p><strong>HDTV Magazine:</strong> Can you provide an illustration?</p>

<p><strong>Mark Knox:</strong> The most popular extra feature that is put on a DVD today is the director and actor comments. You are watching the move and you see a particular scene and say, "Gee, I wonder what actor thought about that scene? In order to get to that information on today's DVD for the most part you have to stop the disk and navigate to an upper menu, find that special feature, presuming it's on the same disk, and navigate back. On HD DVD you will push a button. The movie will continue to play and you will be able to select from a menu that is translucent right on top of your movie the director's comments. That function will recognize where it is in the movie and will not only drop the film audio down, but not out, and you can now hear the actor talking about the scene and you will be able to see his face as well. That is an option because you have two video streams. </p>

<p>Another interesting benefit of this technology is all of us on occasion will use closed captions. A phone rings, something is going on. You don't want to stop it. But closed caption is just written language and yet there are two or three people talking. How do you figure out who said what? With HD DVD you can have little images so when a voice comes up there is a face of an actor telling you that this or that actor is the one speaking at any given moment. There is a whole plethora of other features which are easier to implement and easier to operate because the platform is capable of doing all of this stuff at the same time. There are actually three levels of video and three levels of audio. All must be processed in real time. </p>

<p><strong>HDTV Magazine:</strong> From the standpoint of CPU power is this player groundbreaking?</p>

<p><strong>Mark Knox:</strong> It is a powerful machine. Even through there have been sample HD drives for both desktop and notebooks available to OEMs for weeks, there have not been many announcing finished products with drives installed. The horsepower of the PC has to be sufficient to handle the computations. In the case of the Toshiba player, it is not only some really high power CPUs and massive amounts of memory, there is a whole bunch of hardware that is there to do the dedicated decoding. It gets even more complex when you figure that the main video feed might be MPEG 4 and the sub-video feed could be VC1. You have to mix them together. One set of audio could be Dolby Digital. Another could de DTS. You have to decode them box, mix them together and then re-encode and deliver them to multiple outputs.</p>

<p><strong>HDTV Magazine:</strong> How distant is the HD DVD away from being a game console? </p>

<p><strong>Mark Knox:</strong> In terms of its pixel pushing power it is no slouch. It definitely is not a Xbox 360 nor a PS3, however. The reasons get involved with respect to the internal architecture. The key point is that the HD DVD engine uses most of the horsepower in the machine to accomplish all of the encoding, decoding, recoding of the content. There is still enough remaining to integrate a bunch of different things. From the beginning the platform was designed to not only be powerful, but to be efficient enough so that you can do cool things without writing miles of code. You have some very creative people within the Holy Trinity - GDMX, Technicolor, and Delux. Some of those people are authoring and pressing Xbox 360 and PS3 games. Additionally, digital studios has already installed a server farm to support the Internet-enhancements for some of the PS3 games. They have also authored for HD DVD. I am no gaming expert but I think there are a lot of creative people who have the contacts and resources and you will see in the next couple of years some really interesting content. It is going to blur the line between what is a video game and what is a film. I think that is going to occur first on the package media side.</p>

<p><strong>HDTV Magazine:</strong> Are there people who are presently developing programming that take full advantage of all of the horse power of HD DVD? </p>

<p><strong>Mark Knox:</strong> Yes, there have been many in that mode for some time. Supporting companies are insinuating themselves into the (movie making) process before anyone gets to film. They are gathering additional elements before, during, and after the shooting of the movie. They capture things about the movie being made in order to get "gems of stuff". They seem to get more involved in the entire making of the film. I can't pretend to understand all plans but I do understand that after the product is delivered they have a lot of "stuff" to be added on the DVD.</p>

<p>Recently when in Japan, I went to Memortek, the largest disc replicator there. I asked to see the mastering process. I was not allowed. At that time of my request I was in a clean room with very tight fitting clean suit on. In still another glass enclosure in side of this one there stood another class of clean room. All of the windows were covered with paper with Xbox 360 logos on it. If those fellas at Memortek are inundated in Xbox 360 logos I must presume that there are a lot of people learning about both sides--movies and games. I think you will see a shift where you no longer buy a movie, watch it, and it's done with. There will be more compelling things for you to do with that content which will be interesting to you for a greater period of time.</p>

<p>It is in the studio's interest. If this addition is really compelling they can look to value added merchandise. </p>

<p><strong>HDTV Magazine:</strong> A lot of people say that the stakes between you and Blu-ray are really high. What are those stakes?</p>

<p><strong>Mark Knox:</strong> For Toshiba this is just one of many initiatives. Toshiba fully recognizes that packaged media is only one element of consumer electronics. As one of the major manufacturers of hard disk drives - both big high capacity for desktops and little ones for notebooks, and even smaller ones for things like iPod--there is an era coming where people will never buy a piece of plastic to get packated contente. So yes, stakes are high for both parties but from Toshiba's perspective there are many other projects which we are working on in parallel. The biggest stakeholders are the Hollywood people. Unlike with DVD, where they only had to make one thing, they must face hard decision over release of their movie. Do they do it in both formats? "Which one can I expect to sell more." </p>

<p><strong>HDTV Magazine:</strong> Is that a big problem for them or an accounting problem?</p>

<p><strong>Mark Knox:</strong> This where we look to you guys to ferret out answers. We would not get answers directly from them. We suspect that even today the complexity of making a single layer 25 Gigabyte disk is higher than is being let on. But we don't have hard numbers to back that up.</p>

<p>One thing is clear. Warner's, among others, petitioned Blu-ray to adopt iHD as the interactive platform in addition to BBJ. Why did they do that? Because the GDMX, which is the Warner's authoring arm, is not looking forward to dealing with BBJ. The iHD has been working closely with us and with Microsoft in order to get a handle on interactive authoring. But even allowing that iHD will be easier to program it is still not going to be easy. Why? It is because of all of the flexibility available to authors. That is the chief reason. On one hand they are fat and happy because they can do all kinds of thing which the original DVD did not support, but on the other hand they have to implement them. </p>

<p>There are a series of projects under the DVD Forums technical coordination group where the authoring teams work with the studios, especially Disney. Disney was deeply involved with interactivity from the start. These teams are presently making a list of the most common types of interactivity and are putting the best engineers to work for implementing the examples. They will put that code out duty free to anyone who is authoring disks. With this free code you have a "cook book" for implementing interactivity which enables you, for example, to put a talking head on top of existing video </p>

<p><strong>HDTV Magazine:</strong> Are these studios amenable to this kind of free sharing?</p>

<p><strong>Mark Knox:</strong> I think so. These are very competitive studios, of course. We have conference calls where with us they are willing to say things which they are not when competitors are on the line. One thing very true, however, is that the guys doing the work are not allied to only one studio. People from Delux Digital Studios and the folks at Technicolor have multiple studios as their customers. They are more than happy to take advantage of some of this shared work. </p>

<p>The second point about authoring and its complexity is that Toshiba and NEC and Memortek, along with many other companies, have voluntarily committed LOTS of resources to help these contractors get this stuff done, whether that be encoding tools for the new codecs or advanced authoring platforms. We have a lot of engineers who are already frequent flyers, and it's only February. We set up a permanent office in Burbank so when a studio has a question they call up and say, "Hey, this is the issue. What can I do?" As often as not our guy says, "I will be right over"?</p>

<p><br />
<em><strong>More tomorrow....</strong></em><br />
</p>
		</div>
	</div>
	<p class="posted">Posted by <b>Dale Cripps</b>, <b>February 22, 2006 10:59 PM</b></p>

	<table class="bare" cellpadding="0" cellspacing="0" style="width:100%"><tr>
  <td id="left"><!-- Comments -->
 		<div><?
 			$sql = "
 			SELECT p.topic_id, p.post_id, post_time dt, post_subject, topic_replies, topic_title, LEFT(post_text ,255) post_text
 			FROM aux_mt_entry a, phpbb_topics t, phpbb_posts p, phpbb_posts_text pt
 			WHERE entry_id = 340
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
 			<h2>More on Marketplace</h2><ul><?
 			$sql = "
 			SELECT DISTINCT entry_blog_id, entry_created_on, entry_title, author_name
 			FROM mt_entry e, mt_author a, mt_placement p, mt_category c
 			WHERE entry_author_id = author_id
 				AND e.entry_id = p.placement_entry_id
 				AND p.placement_category_id = c.category_id
 				AND category_label = 'Marketplace'
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
 				AND entry_id <> 340
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
	<script src="http://feeds.feedburner.com/~s/<?=$feed_name?>?i=http://www.hdtvmagazine.com/articles/2006/02/interview_-_mark_knox_toshiba_on_hd_dvd.php" type="text/javascript" charset="utf-8"></script>
</div></body>
</html>
